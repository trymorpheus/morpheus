# Theme System Guide

**Version:** 4.0.0  
**Status:** Production Ready

## 📋 Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Architecture](#architecture)
- [Built-in Themes](#built-in-themes)
- [Theme Configuration](#theme-configuration)
- [Creating Custom Themes](#creating-custom-themes)
- [Theme Manager API](#theme-manager-api)
- [Integration](#integration)
- [Best Practices](#best-practices)
- [Examples](#examples)

---

## Overview

The Theme System allows you to customize the look and feel of your CMS without touching code. It provides a pluggable architecture where themes can be registered, activated, and switched dynamically.

**Key Features:**
- 🎨 **3 Built-in Themes** - Minimal, Modern, Classic
- 🔄 **Hot Switching** - Change themes without data loss
- 📦 **Self-contained** - Each theme includes templates, styles, and config
- 🎯 **Simple API** - Register, activate, render
- 🔌 **Pluggable** - Create custom themes easily
- 💾 **Database Storage** - Active theme persisted in database

---

## Quick Start

### 1. Basic Usage

```php
use DynamicCRUD\Theme\ThemeManager;
use DynamicCRUD\Theme\Themes\MinimalTheme;
use DynamicCRUD\Theme\Themes\ModernTheme;
use DynamicCRUD\Theme\Themes\ClassicTheme;
use DynamicCRUD\Frontend\FrontendRenderer;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

// Initialize ThemeManager
$themesDir = __DIR__ . '/themes';
$themeManager = new ThemeManager($pdo, $themesDir);

// Register themes
$themeManager->register('minimal', new MinimalTheme($themesDir));
$themeManager->register('modern', new ModernTheme($themesDir));
$themeManager->register('classic', new ClassicTheme($themesDir));

// Activate a theme
$themeManager->activate('modern');

// Use with FrontendRenderer
$renderer = new FrontendRenderer($pdo, 'blog', null, null, '', $themeManager);
echo $renderer->renderHome();
```

### 2. Theme Switching

```php
// Switch theme
if (isset($_GET['theme'])) {
    $themeManager->activate($_GET['theme']);
    header('Location: index.php');
    exit;
}

// Get active theme
$activeTheme = $themeManager->getActive();
echo $activeTheme->getName(); // "Modern"
```

---

## Architecture

### Components

```
Theme System
├── Theme (Interface)
│   └── Contract for all themes
├── AbstractTheme (Base Class)
│   └── Common functionality
├── ThemeManager (Manager)
│   └── Lifecycle management
└── Built-in Themes
    ├── MinimalTheme
    ├── ModernTheme
    └── ClassicTheme
```

### Theme Interface

```php
interface Theme
{
    public function getName(): string;
    public function getDescription(): string;
    public function getVersion(): string;
    public function getAuthor(): string;
    public function getScreenshot(): string;
    
    public function getConfig(): array;
    public function getTemplates(): array;
    public function getAssets(): array;
    
    public function render(string $template, array $data): string;
}
```

### Theme Structure

```
themes/
└── minimal/
    ├── config.php          # Theme configuration
    ├── screenshot.png      # Theme preview image
    ├── templates/          # PHP templates
    │   ├── layout.php      # Main layout wrapper
    │   ├── home.php        # Homepage template
    │   ├── single.php      # Single post template
    │   ├── archive.php     # Archive template
    │   ├── category.php    # Category template
    │   ├── tag.php         # Tag template
    │   ├── search.php      # Search results template
    │   └── 404.php         # 404 page template
    └── assets/             # Static assets
        └── style.css       # Theme styles
```

---

## Built-in Themes

### 1. Minimal Theme

**Description:** Clean, simple design focused on content. Fast loading and mobile-first.

**Features:**
- ✅ Minimal design
- ✅ System fonts
- ✅ Fast loading (<50ms)
- ✅ Mobile-first
- ❌ No dark mode
- ❌ No animations
- ❌ No sidebar

**Colors:**
- Primary: `#0066cc`
- Background: `#ffffff`
- Text: `#333333`

**Best For:** Blogs, documentation, content-focused sites

### 2. Modern Theme

**Description:** Modern theme with gradients and animations. Includes dark mode support.

**Features:**
- ✅ Gradient backgrounds
- ✅ Card-based layout
- ✅ Smooth animations
- ✅ Dark mode support
- ✅ Modern fonts (Inter)
- ❌ No sidebar

**Colors:**
- Primary: `#667eea`
- Secondary: `#764ba2`
- Background: `#f5f7fa`
- Text: `#333333`

**Best For:** Modern blogs, portfolios, startups

### 3. Classic Theme

**Description:** Traditional blog design with sidebar layout and serif fonts.

**Features:**
- ✅ Sidebar layout
- ✅ Serif fonts (Georgia)
- ✅ Warm colors
- ✅ Traditional design
- ❌ No dark mode
- ❌ No animations

**Colors:**
- Primary: `#8b4513`
- Background: `#f5f5dc`
- Text: `#333333`

**Best For:** Traditional blogs, magazines, news sites

---

## Theme Configuration

Each theme has a `config.php` file with the following structure:

```php
// themes/modern/config.php
return [
    'name' => 'Modern',
    'description' => 'Modern theme with gradients and animations',
    'version' => '1.0.0',
    'author' => 'DynamicCRUD',
    'screenshot' => 'screenshot.png',
    
    'colors' => [
        'primary' => '#667eea',
        'secondary' => '#764ba2',
        'background' => '#f5f7fa',
        'text' => '#333333',
        'link' => '#667eea'
    ],
    
    'fonts' => [
        'heading' => 'Inter, sans-serif',
        'body' => 'Inter, sans-serif'
    ],
    
    'layout' => [
        'container_width' => '1200px',
        'sidebar' => false,
        'header_style' => 'fixed'
    ],
    
    'features' => [
        'dark_mode' => true,
        'animations' => true,
        'breadcrumbs' => true,
        'social_share' => true
    ]
];
```

---

## Creating Custom Themes

### Step 1: Create Theme Class

```php
namespace DynamicCRUD\Theme\Themes;

use DynamicCRUD\Theme\AbstractTheme;

class MyCustomTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'My Custom Theme';
    }
    
    public function getDescription(): string
    {
        return 'A beautiful custom theme for my site';
    }
}
```

### Step 2: Create Directory Structure

```bash
mkdir -p themes/mycustom/templates
mkdir -p themes/mycustom/assets
```

### Step 3: Create Configuration

```php
// themes/mycustom/config.php
return [
    'name' => 'My Custom Theme',
    'description' => 'A beautiful custom theme',
    'version' => '1.0.0',
    'author' => 'Your Name',
    'screenshot' => 'screenshot.png',
    
    'colors' => [
        'primary' => '#ff6b6b',
        'background' => '#ffffff',
        'text' => '#333333'
    ],
    
    'fonts' => [
        'heading' => 'Montserrat, sans-serif',
        'body' => 'Open Sans, sans-serif'
    ],
    
    'layout' => [
        'container_width' => '1140px',
        'sidebar' => false
    ],
    
    'features' => [
        'dark_mode' => false,
        'animations' => true
    ]
];
```

### Step 4: Create Layout Template

```php
// themes/mycustom/templates/layout.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'My Site') ?></title>
    <?php if (isset($theme_styles) && $theme_styles): ?>
    <style><?= $theme_styles ?></style>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/">My Site</a></h1>
            <nav>
                <a href="/">Home</a>
                <a href="/blog">Blog</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <?= $content ?>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> My Site</p>
        </div>
    </footer>
</body>
</html>
```

### Step 5: Create Content Templates

```php
// themes/mycustom/templates/home.php
<div class="posts">
    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article>
                <h2><a href="/blog/<?= htmlspecialchars($post['slug']) ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a></h2>
                <div class="meta">
                    <?= htmlspecialchars($post['published_at'] ?? '') ?>
                </div>
                <p><?= htmlspecialchars(substr(strip_tags($post['content'] ?? ''), 0, 200)) ?>...</p>
                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>">Read more →</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
```

### Step 6: Create Styles

```css
/* themes/mycustom/assets/style.css */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Open Sans', sans-serif;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 20px;
}

header {
    background: #ff6b6b;
    color: white;
    padding: 20px 0;
}

header h1 a {
    color: white;
    text-decoration: none;
}

nav a {
    color: white;
    margin-left: 20px;
    text-decoration: none;
}

article {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 1px solid #eee;
}
```

### Step 7: Register and Activate

```php
use DynamicCRUD\Theme\Themes\MyCustomTheme;

$themeManager->register('mycustom', new MyCustomTheme($themesDir));
$themeManager->activate('mycustom');
```

---

## Theme Manager API

### Registration

```php
// Register a theme
$themeManager->register('theme-name', $themeInstance);

// Get available themes
$themes = $themeManager->getAvailable();
// Returns: [
//     'minimal' => ['name' => 'Minimal', 'description' => '...', ...],
//     'modern' => ['name' => 'Modern', 'description' => '...', ...],
// ]
```

### Activation

```php
// Activate a theme
$success = $themeManager->activate('modern');

// Deactivate all themes
$success = $themeManager->deactivate();

// Get active theme
$theme = $themeManager->getActive();
if ($theme) {
    echo $theme->getName();
}
```

### Theme Information

```php
// Get theme info
$info = $themeManager->getThemeInfo('modern');
// Returns: [
//     'name' => 'Modern',
//     'description' => '...',
//     'version' => '1.0.0',
//     'author' => 'DynamicCRUD',
//     'config' => [...],
//     'templates' => ['home', 'single', ...],
//     'assets' => ['css' => ['style.css'], 'js' => []]
// ]

// Check if theme is installed
$installed = $themeManager->isInstalled('modern');
```

### Configuration

```php
// Get config value
$primaryColor = $themeManager->getConfig('colors.primary');

// Get all config
$config = $themeManager->getConfig();

// Set config value
$themeManager->setConfig('colors.primary', '#ff0000');
```

### Rendering

```php
// Render template with active theme
$html = $themeManager->render('home', [
    'title' => 'Home',
    'posts' => $posts
]);
```

---

## Integration

### With FrontendRenderer

```php
$themeManager = new ThemeManager($pdo, __DIR__ . '/themes');
$themeManager->register('modern', new ModernTheme(__DIR__ . '/themes'));
$themeManager->activate('modern');

$renderer = new FrontendRenderer(
    $pdo,
    'blog',
    null,
    null,
    '',
    $themeManager  // Pass theme manager
);

echo $renderer->renderHome();
```

### With DynamicCRUD

```php
// Themes are primarily for frontend rendering
// DynamicCRUD forms use their own styling
$crud = new DynamicCRUD($pdo, 'posts');
echo $crud->renderForm();

// For frontend, use FrontendRenderer with ThemeManager
$renderer = new FrontendRenderer($pdo, 'blog', null, null, '', $themeManager);
echo $renderer->renderSingle($slug);
```

---

## Best Practices

### 1. Theme Naming

- Use lowercase names: `minimal`, `modern`, `classic`
- Use hyphens for multi-word names: `my-custom-theme`
- Keep names short and descriptive

### 2. Directory Structure

- Always include `config.php`
- Always include `templates/layout.php`
- Include all 8 templates (home, single, archive, category, tag, search, 404)
- Keep assets in `assets/` directory

### 3. Template Variables

Always check if variables exist before using:

```php
<?= htmlspecialchars($title ?? 'Default Title') ?>
```

### 4. Inline Styles

Themes use inline styles for portability:

```php
<?php if (isset($theme_styles) && $theme_styles): ?>
<style><?= $theme_styles ?></style>
<?php endif; ?>
```

### 5. Escaping Output

Always escape user-generated content:

```php
<?= htmlspecialchars($post['title']) ?>
```

### 6. Responsive Design

Always include viewport meta tag:

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

### 7. Performance

- Keep CSS minimal
- Avoid external dependencies
- Use system fonts when possible
- Optimize images

---

## Examples

### Example 1: Theme Showcase

See `examples/25-themes/` for a complete theme showcase with:
- Theme switcher UI
- Live preview
- Theme comparison
- Active theme display

### Example 2: Blog with Themes

```php
// index.php
$themeManager = new ThemeManager($pdo, __DIR__ . '/themes');
$themeManager->register('minimal', new MinimalTheme(__DIR__ . '/themes'));
$themeManager->register('modern', new ModernTheme(__DIR__ . '/themes'));
$themeManager->register('classic', new ClassicTheme(__DIR__ . '/themes'));

// Handle theme switching
if (isset($_GET['theme'])) {
    $themeManager->activate($_GET['theme']);
    header('Location: index.php');
    exit;
}

// Activate default
if (!$themeManager->getActive()) {
    $themeManager->activate('minimal');
}

// Render blog
$renderer = new FrontendRenderer($pdo, 'blog', null, null, '', $themeManager);
echo $renderer->renderHome();
```

### Example 3: Custom Theme

```php
// Create custom theme
class MyTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'My Theme';
    }
    
    public function getDescription(): string
    {
        return 'My custom theme';
    }
}

// Register and use
$themeManager->register('mytheme', new MyTheme($themesDir));
$themeManager->activate('mytheme');
```

---

## Database Schema

Themes are stored in the `_themes` table:

```sql
CREATE TABLE _themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    active BOOLEAN DEFAULT FALSE,
    config JSON,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT '{"display_name":"Themes","icon":"🎨"}';
```

---

## Troubleshooting

### Theme Not Loading

**Problem:** Theme is activated but styles not showing

**Solution:** Check that `theme_styles` variable is passed to layout:

```php
<?php if (isset($theme_styles) && $theme_styles): ?>
<style><?= $theme_styles ?></style>
<?php endif; ?>
```

### Template Not Found

**Problem:** "Template Not Found" error

**Solution:** Ensure template file exists in `themes/{theme}/templates/{template}.php`

### Theme Not Activating

**Problem:** `activate()` returns false

**Solution:** Check that theme is registered first:

```php
$themeManager->register('theme-name', $themeInstance);
$themeManager->activate('theme-name');
```

---

## Related Documentation

- [Frontend Rendering Guide](FRONTEND_RENDERING.md)
- [Content Types Guide](CONTENT_TYPES.md)
- [SEO Guide](SEO.md)
- [Template System Guide](TEMPLATES.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
