#!/bin/bash

# DynamicCRUD v4.0.0 Release Script
# This script prepares and tags the v4.0.0 release

set -e  # Exit on error

echo "=========================================="
echo "DynamicCRUD v4.0.0 Release Preparation"
echo "=========================================="
echo ""

# 1. Verify we're on main branch
echo "1. Checking current branch..."
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "❌ Error: Not on main branch (current: $CURRENT_BRANCH)"
    echo "Please switch to main branch: git checkout main"
    exit 1
fi
echo "✓ On main branch"
echo ""

# 2. Verify working directory is clean
echo "2. Checking working directory..."
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ Error: Working directory is not clean"
    echo "Please commit or stash your changes first"
    git status --short
    exit 1
fi
echo "✓ Working directory is clean"
echo ""

# 3. Pull latest changes
echo "3. Pulling latest changes..."
git pull origin main
echo "✓ Up to date with remote"
echo ""

# 4. Run tests
echo "4. Running test suite..."
php vendor/phpunit/phpunit/phpunit --testdox
if [ $? -ne 0 ]; then
    echo "❌ Error: Tests failed"
    exit 1
fi
echo "✓ All tests passing"
echo ""

# 5. Verify version numbers
echo "5. Verifying version numbers..."
COMPOSER_VERSION=$(grep '"version"' composer.json | cut -d'"' -f4)
if [ "$COMPOSER_VERSION" != "4.0.0" ]; then
    echo "❌ Error: composer.json version is $COMPOSER_VERSION (expected 4.0.0)"
    exit 1
fi
echo "✓ composer.json version: $COMPOSER_VERSION"
echo ""

# 6. Create git tag
echo "6. Creating git tag v4.0.0..."
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

See RELEASE_NOTES_v4.0.0.md for full details."

echo "✓ Tag v4.0.0 created"
echo ""

# 7. Show tag info
echo "7. Tag information:"
git show v4.0.0 --no-patch
echo ""

# 8. Instructions for pushing
echo "=========================================="
echo "✅ Release v4.0.0 is ready!"
echo "=========================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Push the tag to GitHub:"
echo "   git push origin v4.0.0"
echo ""
echo "2. Create GitHub Release:"
echo "   - Go to: https://github.com/mcarbonell/DynamicCRUD/releases/new"
echo "   - Tag: v4.0.0"
echo "   - Title: DynamicCRUD v4.0.0 - Universal CMS Foundation"
echo "   - Description: Copy from RELEASE_NOTES_v4.0.0.md"
echo "   - Mark as 'Latest release'"
echo ""
echo "3. Update Packagist:"
echo "   - Packagist will auto-update from GitHub tag"
echo "   - Verify at: https://packagist.org/packages/dynamiccrud/dynamiccrud"
echo ""
echo "4. Announce the release:"
echo "   - Post ANNOUNCEMENT_v4.0.0.md to GitHub Discussions"
echo "   - Share on social media"
echo "   - Update project website"
echo ""
echo "=========================================="
echo "🎉 Congratulations on v4.0.0!"
echo "=========================================="
