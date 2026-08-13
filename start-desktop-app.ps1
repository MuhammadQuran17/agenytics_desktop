# Launches the Agenytics desktop app in dev mode with everything it needs:
# - the persistent Playwright MCP browser server (started if not already running)
# - a php.ini dropped next to the bundled static PHP binary that disables OPcache
#   (Windows ASLR breaks its cached opcode handlers otherwise). PHP picks up an
#   ini file sitting next to its own exe automatically, no PHPRC env var needed
#   (a system-wide PHPRC would also override the regular Herd PHP CLI's ini).
# - a clean environment (clears ELECTRON_RUN_AS_NODE if inherited from a parent
#   terminal, which would otherwise make Electron boot as plain Node)

$ErrorActionPreference = "Stop"
$projectRoot = $PSScriptRoot
Set-Location $projectRoot

Remove-Item Env:\ELECTRON_RUN_AS_NODE -ErrorAction SilentlyContinue

$bundledPhpDir = Join-Path $projectRoot "vendor\nativephp\desktop\resources\build\php"
if (Test-Path $bundledPhpDir) {
    Copy-Item -Path (Join-Path $projectRoot "storage\app\nativephp-php.ini") -Destination (Join-Path $bundledPhpDir "php.ini") -Force
}

$mcpRunning = Get-NetTCPConnection -LocalPort 8931 -State Listen -ErrorAction SilentlyContinue
if (-not $mcpRunning) {
    Write-Host "Starting Playwright MCP server..."
    $env:PLAYWRIGHT_MCP_PING_TIMEOUT_MS = "0"
    Start-Process -FilePath "node" `
        -ArgumentList "node_modules/@playwright/mcp/cli.js", "--port", "8931", "--shared-browser-context" `
        -WorkingDirectory $projectRoot `
        -WindowStyle Hidden
    Start-Sleep -Seconds 2
}

php artisan native:run --no-interaction
