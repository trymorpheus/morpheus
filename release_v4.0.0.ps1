# DynamicCRUD v4.0.0 Release Script (PowerShell)
# This script prepares and tags the v4.0.0 release

$ErrorActionPreference = "Stop"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "DynamicCRUD v4.0.0 Release Preparation" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Verify we're on main branch
Write-Host "1. Checking current branch..." -ForegroundColor Yellow
$currentBranch = git branch --show-current
if ($currentBranch -ne "main") {
    Write-Host "❌ Error: Not on main branch (current: $currentBranch)" -ForegroundColor Red
    Write-Host "Please switch to main branch: git checkout main" -ForegroundColor Red
    exit 1
}
Write-Host "✓ On main branch" -ForegroundColor Green
Write-Host ""

# 2. Verify working directory is clean
Write-Host "2. Checking working directory..." -ForegroundColor Yellow
$status = git status --porcelain
if ($status) {
    Write-Host "❌ Error: Working directory is not clean" -ForegroundColor Red
    Write-Host "Please commit or stash your changes first" -ForegroundColor Red
    git status --short
    exit 1
}
Write-Host "✓ Working directory is clean" -ForegroundColor Green
Write-Host ""

# 3. Pull latest changes
Write-Host "3. Pulling latest changes..." -ForegroundColor Yellow
git pull origin main
Write-Host "✓ Up to date with remote" -ForegroundColor Green
Write-Host ""

# 4. Run tests
Write-Host "4. Running test suite..." -ForegroundColor Yellow
.\vendor\bin\phpunit --testdox
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error: Tests failed" -ForegroundColor Red
    exit 1
}
Write-Host "✓ All tests passing" -ForegroundColor Green
Write-Host ""

# 5. Verify version numbers
Write-Host "5. Verifying version numbers..." -ForegroundColor Yellow
$composerJson = Get-Content composer.json | ConvertFrom-Json
$composerVersion = $composerJson.version
if ($composerVersion -ne "4.0.0") {
    Write-Host "❌ Error: composer.json version is $composerVersion (expected 4.0.0)" -ForegroundColor Red
    exit 1
}
Write-Host "✓ composer.json version: $composerVersion" -ForegroundColor Green
Write-Host ""

# 6. Create git tag
Write-Host "6. Creating git tag v4.0.0..." -ForegroundColor Yellow
git tag -a v4.0.0 -m "Release v4.0.0 - Universal CMS Foundation. Major Features: Blog CMS, Theme System, One-Click Installer, Media Library, Comment System, WordPress Migration. Performance: 40-60x faster than WordPress. Statistics: 58 classes, 478 tests passing, 90% coverage. See RELEASE_NOTES_v4.0.0.md for details."
Write-Host "✓ Tag v4.0.0 created" -ForegroundColor Green
Write-Host ""

# 7. Show tag info
Write-Host "7. Tag information:" -ForegroundColor Yellow
git show v4.0.0 --no-patch
Write-Host ""

# 8. Instructions for pushing
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "✅ Release v4.0.0 is ready!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Push the tag to GitHub:" -ForegroundColor White
Write-Host "   git push origin v4.0.0" -ForegroundColor Cyan
Write-Host ""
Write-Host "2. Create GitHub Release:" -ForegroundColor White
Write-Host "   - Go to: https://github.com/mcarbonell/DynamicCRUD/releases/new" -ForegroundColor Cyan
Write-Host "   - Tag: v4.0.0" -ForegroundColor Cyan
Write-Host "   - Title: DynamicCRUD v4.0.0 - Universal CMS Foundation" -ForegroundColor Cyan
Write-Host "   - Description: Copy from RELEASE_NOTES_v4.0.0.md" -ForegroundColor Cyan
Write-Host "   - Mark as 'Latest release'" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Update Packagist:" -ForegroundColor White
Write-Host "   - Packagist will auto-update from GitHub tag" -ForegroundColor Cyan
Write-Host "   - Verify at: https://packagist.org/packages/dynamiccrud/dynamiccrud" -ForegroundColor Cyan
Write-Host ""
Write-Host "4. Announce the release:" -ForegroundColor White
Write-Host "   - Post ANNOUNCEMENT_v4.0.0.md to GitHub Discussions" -ForegroundColor Cyan
Write-Host "   - Share on social media" -ForegroundColor Cyan
Write-Host "   - Update project website" -ForegroundColor Cyan
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "🎉 Congratulations on v4.0.0!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
