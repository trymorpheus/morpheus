# Git Release Commands for v4.0.0

## Quick Reference

```bash
# 1. Verify everything is ready
git status
git log --oneline -5

# 2. Create and push tag
git tag -a v4.0.0 -m "Release v4.0.0 - Universal CMS Foundation"
git push origin v4.0.0

# 3. Verify tag
git show v4.0.0
```

---

## Detailed Step-by-Step

### 1. Pre-Release Verification

```bash
# Check current branch
git branch --show-current
# Should output: main

# Check working directory status
git status
# Should output: nothing to commit, working tree clean

# Check recent commits
git log --oneline -10

# Verify remote
git remote -v
# Should show: origin https://github.com/mcarbonell/DynamicCRUD.git

# Pull latest changes
git pull origin main
```

### 2. Create Annotated Tag

```bash
# Create tag with detailed message
git tag -a v4.0.0 -m "Release v4.0.0 - Universal CMS Foundation

Major Features:
- Blog Content Type (WordPress alternative)
- Theme System (3 built-in themes)
- One-Click Installer (web + CLI)
- Media Library (file management + image editing)
- Comment System (nested comments + moderation)
- WordPress Migration Tool

Performance:
- 40-60x faster than WordPress
- <100ms page load times

Statistics:
- 58 PHP classes (~19,500 lines)
- 478 tests (100% passing)
- 1070 assertions
- 90% code coverage

Breaking Changes:
- None (100% backward compatible)

See RELEASE_NOTES_v4.0.0.md for full details."
```

### 3. Verify Tag Locally

```bash
# List all tags
git tag -l

# Show tag details
git show v4.0.0

# Show tag message only
git tag -l -n99 v4.0.0

# Verify tag points to correct commit
git rev-parse v4.0.0
git rev-parse HEAD
# Both should match
```

### 4. Push Tag to GitHub

```bash
# Push tag to remote
git push origin v4.0.0

# Verify tag on remote
git ls-remote --tags origin
```

### 5. Verify GitHub Actions

```bash
# Check GitHub Actions status
# Go to: https://github.com/mcarbonell/DynamicCRUD/actions

# Wait for CI/CD pipeline to complete
# All checks should pass
```

---

## Alternative: Using Release Script

### Linux/macOS

```bash
# Make script executable
chmod +x release_v4.0.0.sh

# Run release script
./release_v4.0.0.sh

# Follow on-screen instructions
```

### Windows (PowerShell)

```powershell
# Run release script
powershell -ExecutionPolicy Bypass -File release_v4.0.0.ps1

# Follow on-screen instructions
```

---

## Rollback Commands (If Needed)

### Delete Local Tag

```bash
# Delete local tag
git tag -d v4.0.0

# Verify deletion
git tag -l
```

### Delete Remote Tag

```bash
# Delete remote tag
git push origin :refs/tags/v4.0.0

# Or using --delete flag
git push origin --delete v4.0.0

# Verify deletion
git ls-remote --tags origin
```

### Recreate Tag (After Fix)

```bash
# Create new tag
git tag -a v4.0.0 -m "Release v4.0.0 - Universal CMS Foundation (Fixed)"

# Force push tag
git push origin v4.0.0 --force
```

---

## GitHub Release Creation

### Via Web Interface

1. Go to: https://github.com/mcarbonell/DynamicCRUD/releases/new

2. Fill in details:
   - **Tag:** v4.0.0
   - **Title:** DynamicCRUD v4.0.0 - Universal CMS Foundation
   - **Description:** Copy from RELEASE_NOTES_v4.0.0.md

3. Options:
   - [x] Set as the latest release
   - [ ] Set as a pre-release

4. Click "Publish release"

### Via GitHub CLI (gh)

```bash
# Install GitHub CLI if needed
# https://cli.github.com/

# Create release
gh release create v4.0.0 \
  --title "DynamicCRUD v4.0.0 - Universal CMS Foundation" \
  --notes-file RELEASE_NOTES_v4.0.0.md \
  --latest

# Verify release
gh release view v4.0.0
```

---

## Packagist Update

### Automatic Update

Packagist will automatically detect the new tag and update the package.

**Verify at:** https://packagist.org/packages/dynamiccrud/dynamiccrud

### Manual Update (If Needed)

1. Go to: https://packagist.org/packages/dynamiccrud/dynamiccrud
2. Click "Update" button
3. Wait for update to complete
4. Verify version 4.0.0 appears

---

## Post-Release Verification

### 1. Verify Tag on GitHub

```bash
# Check tags page
# https://github.com/mcarbonell/DynamicCRUD/tags

# Should show v4.0.0 with release notes
```

### 2. Verify Release on GitHub

```bash
# Check releases page
# https://github.com/mcarbonell/DynamicCRUD/releases

# Should show v4.0.0 as "Latest"
```

### 3. Verify Packagist

```bash
# Check Packagist page
# https://packagist.org/packages/dynamiccrud/dynamiccrud

# Should show version 4.0.0
# Download stats should update
```

### 4. Test Installation

```bash
# Create test directory
mkdir test-v4
cd test-v4

# Install via Composer
composer require dynamiccrud/dynamiccrud:^4.0

# Verify version
composer show dynamiccrud/dynamiccrud
# Should show version 4.0.0
```

---

## Troubleshooting

### Tag Already Exists

```bash
# Error: tag 'v4.0.0' already exists

# Solution 1: Delete and recreate
git tag -d v4.0.0
git tag -a v4.0.0 -m "Release v4.0.0"

# Solution 2: Use different tag name
git tag -a v4.0.1 -m "Release v4.0.1"
```

### Push Rejected

```bash
# Error: ! [rejected] v4.0.0 -> v4.0.0 (already exists)

# Solution: Force push (use with caution!)
git push origin v4.0.0 --force
```

### GitHub Actions Failing

```bash
# Check logs at:
# https://github.com/mcarbonell/DynamicCRUD/actions

# Common fixes:
# 1. Re-run failed jobs
# 2. Check test failures
# 3. Verify PHP version compatibility
```

### Packagist Not Updating

```bash
# Solution 1: Manual update
# Go to Packagist and click "Update"

# Solution 2: Wait 5-10 minutes
# Packagist checks for updates periodically

# Solution 3: Check webhook
# Verify GitHub webhook is configured
```

---

## Best Practices

### Before Tagging

1. ✅ All tests passing
2. ✅ Code coverage >90%
3. ✅ Documentation updated
4. ✅ CHANGELOG updated
5. ✅ Version numbers updated
6. ✅ Working directory clean
7. ✅ On main branch
8. ✅ Pulled latest changes

### Tag Message Format

```
Release vX.Y.Z - Codename

Major Features:
- Feature 1
- Feature 2

Performance:
- Metric 1
- Metric 2

Statistics:
- Stat 1
- Stat 2

Breaking Changes:
- Change 1 (or "None")

See RELEASE_NOTES_vX.Y.Z.md for full details.
```

### After Tagging

1. ✅ Verify tag locally
2. ✅ Push to GitHub
3. ✅ Verify GitHub Actions
4. ✅ Create GitHub release
5. ✅ Verify Packagist
6. ✅ Test installation
7. ✅ Post announcements

---

## Quick Commands Summary

```bash
# Complete release in 5 commands
git status                                    # 1. Verify clean
git tag -a v4.0.0 -m "Release v4.0.0"        # 2. Create tag
git push origin v4.0.0                        # 3. Push tag
git show v4.0.0                               # 4. Verify tag
# 5. Create GitHub release (web interface)
```

---

## Contact & Support

**Issues:** https://github.com/mcarbonell/DynamicCRUD/issues  
**Discussions:** https://github.com/mcarbonell/DynamicCRUD/discussions

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
