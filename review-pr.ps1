# PowerShell script to review UI enhancement PR
# Usage: .\review-pr.ps1 -PRBranchName "branch-name"

param(
    [Parameter(Mandatory=$true)]
    [string]$PRBranchName
)

Write-Host "🔍 Reviewing PR: $PRBranchName" -ForegroundColor Cyan
Write-Host ""

# Fetch latest changes
Write-Host "📥 Fetching latest changes..." -ForegroundColor Yellow
git fetch origin $PRBranchName

# Create review branch
$reviewBranch = "review-$PRBranchName"
Write-Host "🌿 Creating review branch: $reviewBranch" -ForegroundColor Yellow
git checkout -b $reviewBranch origin/$PRBranchName

# Show changed files
Write-Host ""
Write-Host "📄 Changed Files:" -ForegroundColor Green
git diff main...HEAD --name-status

Write-Host ""
Write-Host "🚨 Checking for backend changes..." -ForegroundColor Red
$backendChanges = git diff main...HEAD --name-only | Where-Object { $_ -match "app/Http/Controllers|app/Models|routes/api|database/migrations" }

if ($backendChanges) {
    Write-Host "⚠️  WARNING: Backend files were modified!" -ForegroundColor Red
    Write-Host "Review these files carefully:" -ForegroundColor Yellow
    $backendChanges | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
} else {
    Write-Host "✅ No backend files modified - Safe to proceed" -ForegroundColor Green
}

Write-Host ""
Write-Host "📊 Summary of changes:" -ForegroundColor Cyan
$viewFiles = git diff main...HEAD --name-only | Where-Object { $_ -match "resources/views" }
$cssFiles = git diff main...HEAD --name-only | Where-Object { $_ -match "resources/css" }
$jsFiles = git diff main...HEAD --name-only | Where-Object { $_ -match "resources/js" }
$routeFiles = git diff main...HEAD --name-only | Where-Object { $_ -match "routes/" }

Write-Host "  View files: $($viewFiles.Count)" -ForegroundColor White
Write-Host "  CSS files: $($cssFiles.Count)" -ForegroundColor White
Write-Host "  JS files: $($jsFiles.Count)" -ForegroundColor White
Write-Host "  Route files: $($routeFiles.Count)" -ForegroundColor White

Write-Host ""
Write-Host "✅ Review complete! Check the changes above." -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Review the diff: git diff main...HEAD" -ForegroundColor White
Write-Host "  2. Test locally: php artisan serve" -ForegroundColor White
Write-Host "  3. If satisfied, merge: git checkout main; git merge $reviewBranch" -ForegroundColor White

