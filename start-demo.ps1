[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = $PSScriptRoot
$databasePath = Join-Path $projectRoot 'database\database.sqlite'
$locationPushed = $false
$environmentKeys = @(
    'DB_CONNECTION',
    'DB_DATABASE',
    'DB_URL',
    'SESSION_DRIVER',
    'CACHE_STORE',
    'QUEUE_CONNECTION'
)
$previousEnvironment = @{}

foreach ($key in $environmentKeys) {
    $previousEnvironment[$key] = [Environment]::GetEnvironmentVariable($key, 'Process')
}

try {
    Push-Location -LiteralPath $projectRoot
    $locationPushed = $true
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = $databasePath
    $env:DB_URL = ''
    $env:SESSION_DRIVER = 'file'
    $env:CACHE_STORE = 'file'
    $env:QUEUE_CONNECTION = 'sync'

    if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'vendor\autoload.php'))) {
        throw 'Composer dependencies are missing. Run composer install first.'
    }

    if (-not (Test-Path -LiteralPath $databasePath)) {
        New-Item -ItemType File -Path $databasePath | Out-Null
    }

    & php artisan config:clear --no-ansi
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not clear cached Laravel configuration.'
    }

    & php artisan migrate --seed --force --no-ansi
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not initialize the local demo database.'
    }

    Write-Host 'TrackingAid demo is running at http://127.0.0.1:7000 (Ctrl+C to stop).'
    & php artisan serve --host=127.0.0.1 --port=7000
    if ($LASTEXITCODE -ne 0) {
        throw 'The local demo server stopped with an error.'
    }
}
finally {
    foreach ($key in $environmentKeys) {
        [Environment]::SetEnvironmentVariable($key, $previousEnvironment[$key], 'Process')
    }
    if ($locationPushed) {
        Pop-Location
    }
}
