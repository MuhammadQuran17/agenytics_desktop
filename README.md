<div align="center"><img src="public/images/agenytics.png" align="center" width="260"></div>

<h1 align="center">Agenytics Desktop</h1>

<p align="center">AI agent chat, packaged as a native Windows desktop app.</p>

![Dashboard](public/images/dashboard.png)

This is a fork of [Agenytics](https://github.com/MuhammadQuran17/agenytics) (Laravel + Inertia + Vue). Two things changed from the original starter kit: the chat no longer talks to n8n, it talks to Google Gemini directly through the [Neuron AI](https://neuron-ai.dev/) PHP framework, and the app runs as a Windows desktop app via [NativePHP](https://nativephp.com/), not just in a browser. Stripe billing and the Feedback/Roadmap module got removed too — didn't need them here.

## Quick start — running the desktop app

Just want the app open on your machine? Do these in order, nothing else.

**0. Install first, if you don't have them:** [PHP 8.4+](https://windows.php.net/download/), [Composer](https://getcomposer.org/download/), [Node.js 20+](https://nodejs.org/), Git.

**1. Get the code and install everything:**
```bash
git clone <this-repo-url>
cd ai_agent_starter_kit
composer install
npm install
```

**2. Create your `.env` file:**
```bash
cp .env.example .env
php artisan key:generate
```

**3. Add a Gemini API key.** Open `.env`, find these lines, and fill in your key ([get one free here](https://aistudio.google.com/apikey)):
```
GEMINI_KEY=your-key-from-aistudio.google.com/apikey
```
(No key yet? Set `IS_FAKE_RESPONSES_ENABLED=true` instead and the app will use fake responses so you can still click around.)

**4. Create the database:**
```bash
touch database/database.sqlite
php artisan migrate
```

**5. Install the browser the AI will use:**
```bash
npx playwright install chromium
```

**6. Start the browser-automation server** (do this every time, before step 7 — the app talks to it over `localhost:8931`):
```bash
powershell -File start-playwright-mcp-server.ps1
```

**7. Open the app.** Pick one:
```bash
composer native:dev   # opens as a desktop window (Electron)
composer dev           # opens in your browser at http://localhost:8000
```

That's it — the chat should be working. Want to build a shareable `.exe` installer instead of just running it locally? See [Running it as a desktop app](#running-it-as-a-desktop-app) below.

---

The rest of this README explains *how* the app works internally — useful if you're changing code, not required just to run it.

## AI chat

The chat job (`ProcessAiChatMessage`) calls `NeuronAiAgent`, which just hands the message off to a Neuron `Agent` class (`app/Neuron/Agents/BrowserAgent.php`) set up with Gemini as the provider. The response comes back wrapped in the same block format the UI already expects (text, table, chart, mermaid, etc.) — see `config/ai_responses.php` for what that looks like.

This branch also gives the agent real browser access through [Playwright MCP](https://github.com/microsoft/playwright-mcp), so it can actually open pages and read them instead of just answering from what it already knows. It's kept off `main` for now since it needs Node.js at runtime and the desktop build doesn't bundle that.

### Setting up browser access

Setup is covered in [Quick start](#quick-start--running-the-desktop-app) (steps 5–6: install Chromium, start `start-playwright-mcp-server.ps1`). The rest of this section is *why* it's built that way, not more setup steps.

The agent doesn't spawn a browser per request — it talks to one persistent Playwright MCP server over HTTP, so the browser stays open across chat messages instead of closing after each answer. (Running the desktop app instead? `start-desktop-app.ps1` starts the MCP server itself if it isn't already running.)

`BrowserAgent` connects to it at `PLAYWRIGHT_MCP_URL` (defaults to `http://localhost:8931/mcp`, see `config/services.php`). Two things had to be handled to make the persistent-browser part actually work:
- The MCP server pings its HTTP client every few seconds and closes the browser if it doesn't get an answer back. Our client doesn't answer pings, so `start-playwright-mcp-server.ps1` sets `PLAYWRIGHT_MCP_PING_TIMEOUT_MS=0` to turn that off.
- The model has a `browser_close` tool available by default and will happily call it once it's done answering. `BrowserAgent::tools()` excludes `browser_close` and `browser_tabs` so it can't close the shared session out from under the next request.

### The MCP client/server split

MCP (Model Context Protocol) always has two separate sides talking over a wire — a **server** that exposes tools, and a **client** that calls them. It's easy to assume both live in the same place because they're both "the MCP stuff," but here they're in two different languages, two different processes, two different dependency managers:

- **Server — `@playwright/mcp` (Node.js, npm, `dependencies`).** This is Microsoft's own package. It drives a real Chromium instance and exposes that as MCP tools (`browser_navigate`, `browser_click`, `browser_type`, ...). We run it ourselves as a long-lived process:
  ```
  node node_modules/@playwright/mcp/cli.js --port 8931 --shared-browser-context
  ```
  That's it — a plain HTTP server on `localhost:8931`, nothing Laravel- or Electron-specific about it. It lives in `dependencies`, not `devDependencies`: the app needs it running at runtime for browsing to work at all, even though `native:build` doesn't currently bundle this repo's `node_modules` into the desktop `.exe` either way (so today the split has no build-pipeline effect) — `dependencies` is still the honest place for it, and the one that keeps working if that ever changes or something starts npm-pruning dev packages.

- **Client — `NeuronAI\MCP\McpConnector` (PHP, Composer, `neuron-core/neuron-laravel`).** This is what `BrowserAgent::tools()` uses:
  ```php
  McpConnector::make(['url' => config('services.playwright_mcp.url')])
      ->exclude(['browser_close', 'browser_tabs'])
      ->tools();
  ```
  It's the thing that actually speaks the MCP protocol to the server over HTTP — it fetches the server's tool list, hands those tools to the Neuron `Agent`/Gemini as regular function-calling tools, and forwards each call the model makes to the server, then returns the result. There's no npm package involved on this side at all; it ships as part of the Neuron AI Composer package.

**The round trip**, when the model decides it needs to look at a page: Gemini picks a tool (e.g. `browser_navigate`) → Neuron's `Agent` hands that call to `McpConnector` (the client) → `McpConnector` sends it as an MCP request over HTTP to `localhost:8931` → the Playwright MCP server (the server) drives the real Chromium tab and sends the result back → `McpConnector` returns it to the `Agent` → Gemini sees the tool result and decides what to do next (call another tool, or answer). `ToolProgressMiddleware` (see below) taps into this loop on the client side to log what's happening for the chat UI.

**Current limitation:** the server process isn't started by the app itself — a developer machine gets it via `start-playwright-mcp-server.ps1` (run manually, or auto-run at login through a Windows Startup shortcut) or via `start-desktop-app.ps1`. Since it's outside anything `native:build` packages, a fresh install on someone else's machine has nothing listening on `:8931` unless that machine sets up the same script/shortcut — browsing will fail until it does. Making the desktop app spawn and manage that process itself (e.g. from `NativeAppServiceProvider`) would close that gap.

### Live progress in the chat UI

While the agent is browsing, the chat shows what it's doing ("Opening youtube.com...") instead of just a spinner. This needs the chat job to actually run in the background rather than inline, so `QUEUE_CONNECTION` has to be `database` (not `sync`) — `.env.example` on this branch already sets that. `composer dev` and the desktop app both start a queue worker for you, so there's nothing extra to run.

`ToolProgressMiddleware` (`app/Neuron/Middleware`) hooks into Neuron's tool-call events and writes a short description to the chat's `chat_histories` row before each tool runs; `/chat/status` returns it while the job is still processing.

## Running it locally

Setup steps are all in [Quick start](#quick-start--running-the-desktop-app) above. Once that's done, `composer dev` (browser, `http://localhost:8000`) and `composer native:dev` (Electron window) both start the server, queue, logs and Vite together — pick whichever you're testing.

## Running it as a desktop app

For everyday running, `composer native:dev` from Quick start is all you need. To build a real, shareable installer:

```bash
php artisan native:build win
```

That produces `nativephp/electron/dist/Agenytics-x.x.x-setup.exe`.

Ran into two Windows-only issues getting this working, noting them here in case someone else hits the same thing:
- Electron would crash on boot with a weird `BrowserWindow` export error — turned out to be `ELECTRON_RUN_AS_NODE` being set in the environment (VS Code's terminal does this), which makes Electron run as plain Node instead of itself.
- The bundled PHP binary would fail with an OPcache/ASLR error on startup. Windows relocates the binary each run, which breaks OPcache's cached pointers for a statically-linked build. Fixed by pointing `PHPRC` at an ini file that disables opcache for it.

## Stack

Laravel 12, Inertia v2, Vue 3, SQLite, Tailwind v4, Neuron AI + Gemini, NativePHP.

## License

MIT — see [LICENSE](LICENSE.md).
