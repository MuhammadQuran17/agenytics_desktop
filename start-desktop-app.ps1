# Launches the Agenytics desktop app in dev mode with everything it needs:
# - the persistent Playwright MCP browser server (started if not already running)
# - the PHPRC override that disables OPcache for the bundled static PHP binary
#   (Windows ASLR breaks its cached opcode handlers otherwise)
# - a clean environment (clears ELECTRON_RUN_AS_NODE if inherited from a parent
#   terminal, which would otherwise make Electron boot as plain Node)

$ErrorActionPreference = "Stop"
Set-Location "c:\Users\Amirxon\ai_agent_starter_kit"

Remove-Item Env:\ELECTRON_RUN_AS_NODE -ErrorAction SilentlyContinue
$env:PHPRC = "c:\Users\Amirxon\ai_agent_starter_kit\storage\app\nativephp-php.ini"

$mcpRunning = Get-NetTCPConnection -LocalPort 8931 -State Listen -ErrorAction SilentlyContinue
if (-not $mcpRunning) {
    Write-Host "Starting Playwright MCP server..."
    $env:PLAYWRIGHT_MCP_PING_TIMEOUT_MS = "0"
    Start-Process -FilePath "node" `
        -ArgumentList "node_modules/@playwright/mcp/cli.js", "--port", "8931", "--shared-browser-context" `
        -WorkingDirectory "c:\Users\Amirxon\ai_agent_starter_kit" `
        -WindowStyle Hidden
    Start-Sleep -Seconds 2
}

php artisan native:run --no-interaction
