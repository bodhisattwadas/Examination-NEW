# generate-demo-screenshots.ps1
# Run this script to automatically take screenshots of all main pages for demo purposes.
#
# Prerequisites:
# 1. Start the Laravel server: php artisan serve
# 2. Make sure you are logged in as admin (or temporarily comment auth middleware for demo)
# 3. Run this script from PowerShell in the project root.

$ErrorActionPreference = "Continue"

# Configuration
$BaseUrl = "http://127.0.0.1:8000"
$OutputFolder = "demo-screenshots"
$ChromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$WindowSize = "1366,768"

# Create output folder if it doesn't exist
if (-not (Test-Path $OutputFolder)) {
    New-Item -ItemType Directory -Path $OutputFolder | Out-Null
    Write-Host "Created folder: $OutputFolder" -ForegroundColor Green
}

# Define all important pages
$Pages = @(
    @{ Name = "01-login";                  Url = "$BaseUrl/login" },
    @{ Name = "02-dashboard";              Url = "$BaseUrl/dashboard" },
    @{ Name = "03-staff-list";             Url = "$BaseUrl/staff" },
    @{ Name = "04-add-staff";              Url = "$BaseUrl/staff/create" },
    @{ Name = "05-staff-upload";           Url = "$BaseUrl/staff/upload" },
    @{ Name = "06-assign-duty";            Url = "$BaseUrl/duty/create" },
    @{ Name = "07-reports";                Url = "$BaseUrl/report" },
    @{ Name = "08-configuration";          Url = "$BaseUrl/config/exam-times" }
)

Write-Host "`nStarting to capture screenshots..." -ForegroundColor Cyan
Write-Host "Make sure the Laravel server is running (php artisan serve)`n" -ForegroundColor Yellow

foreach ($page in $Pages) {
    $outputPath = Join-Path $OutputFolder "$($page.Name).png"
    
    Write-Host "Capturing: $($page.Name) -> $($page.Url)" -ForegroundColor White
    
    try {
        & $ChromePath `
            --headless `
            --disable-gpu `
            --window-size=$WindowSize `
            --screenshot="$outputPath" `
            "$($page.Url)" 2>&1 | Out-Null

        if (Test-Path $outputPath) {
            Write-Host "  ✓ Saved: $outputPath" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Failed to save screenshot" -ForegroundColor Red
        }
    } catch {
        Write-Host "  ✗ Error capturing $($page.Name): $_" -ForegroundColor Red
    }
    
    Start-Sleep -Milliseconds 800
}

Write-Host "`nScreenshots generation completed!" -ForegroundColor Green
Write-Host "All images saved in: $OutputFolder`n" -ForegroundColor Cyan

# List generated files
Get-ChildItem $OutputFolder -Filter "*.png" | Sort-Object Name | Format-Table Name, Length, LastWriteTime -AutoSize