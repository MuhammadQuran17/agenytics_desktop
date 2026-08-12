# Starts the persistent Playwright MCP HTTP server used by the Agenytics
# desktop/web app's BrowserAgent, if it isn't already running.
# PLAYWRIGHT_MCP_PING_TIMEOUT_MS=0 disables the server's client heartbeat
# check, which otherwise closes the browser a few seconds after opening
# because our PHP HTTP client never answers server-initiated pings.

$alreadyRunning = Get-NetTCPConnection -LocalPort 8931 -State Listen -ErrorAction SilentlyContinue
if ($alreadyRunning) {
    exit 0
}

$env:PLAYWRIGHT_MCP_PING_TIMEOUT_MS = "0"
Start-Process -FilePath "node" `
    -ArgumentList "node_modules/@playwright/mcp/cli.js", "--port", "8931", "--shared-browser-context" `
    -WorkingDirectory $PSScriptRoot `
    -WindowStyle Hidden
