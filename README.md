<div align="center"><img src="public/images/agenytics.png" align="center" width="260"></div>

<h1 align="center">Agenytics Desktop</h1>

<p align="center">AI agent chat, packaged as a native Windows desktop app.</p>

![Dashboard](public/images/dashboard.png)

This is a fork of [Agenytics](https://github.com/MuhammadQuran17/agenytics) (Laravel + Inertia + Vue). Two things changed from the original starter kit: the chat no longer talks to n8n, it talks to Google Gemini directly through the [Neuron AI](https://neuron-ai.dev/) PHP framework, and the app runs as a Windows desktop app via [NativePHP](https://nativephp.com/), not just in a browser. Stripe billing and the Feedback/Roadmap module got removed too — didn't need them here.

## AI chat

The chat job (`ProcessAiChatMessage`) calls `NeuronAiAgent`, which just hands the message off to a Neuron `Agent` class (`app/Neuron/Agents/BrowserAgent.php`) set up with Gemini as the provider. The response comes back wrapped in the same block format the UI already expects (text, table, chart, mermaid, etc.) — see `config/ai_responses.php` for what that looks like.

There's also a version of this with real browser access (Playwright MCP, so the agent can actually open pages and read them) — it works, but it's sitting on the `with-playwright-mcp` branch instead of `main` for now since it needs Node.js at runtime and the desktop build doesn't bundle that yet.

## Running it locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Add to `.env`:

```
NEURON_AI_PROVIDER=gemini
GEMINI_KEY=your-key-from-aistudio.google.com/apikey
GEMINI_MODEL=gemini-flash-latest
IS_FAKE_RESPONSES_ENABLED=false
```

(leave `IS_FAKE_RESPONSES_ENABLED=true` if you just want to click around without a Gemini key)

```bash
composer dev
```

That starts the server, queue, logs and Vite together — go to `http://localhost:8000`.

## Running it as a desktop app

```bash
composer native:dev
```

Same app, opens in an Electron window instead of the browser. For an actual installer:

```bash
php artisan native:build win
```

Ran into two Windows-only issues getting this working, noting them here in case someone else hits the same thing:
- Electron would crash on boot with a weird `BrowserWindow` export error — turned out to be `ELECTRON_RUN_AS_NODE` being set in the environment (VS Code's terminal does this), which makes Electron run as plain Node instead of itself.
- The bundled PHP binary would fail with an OPcache/ASLR error on startup. Windows relocates the binary each run, which breaks OPcache's cached pointers for a statically-linked build. Fixed by pointing `PHPRC` at an ini file that disables opcache for it.

## Stack

Laravel 12, Inertia v2, Vue 3, SQLite, Tailwind v4, Neuron AI + Gemini, NativePHP.

## License

MIT — see [LICENSE](LICENSE.md).
