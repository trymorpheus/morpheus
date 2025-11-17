# Example 25: Theme System

Complete theme system with 3 pre-built themes and theme switcher.

## Features

- **3 Pre-built Themes**
  - Minimal - Clean and simple
  - Modern - Gradients and animations
  - Classic - Traditional with sidebar

- **Theme Switcher** - Change themes with one click
- **Theme Configuration** - Colors, fonts, layout options
- **Full Integration** - Works with FrontendRenderer

## Setup

```bash
php setup.php
```

## Usage

### View Theme Showcase

```bash
php -S localhost:8000
```

Visit: http://localhost:8000/examples/25-themes/

### Switch Themes

Click on any theme card to activate it. The blog demo will use the active theme.

### Programmatic Usage

```php
use Morpheus\Theme\ThemeManager;
use Morpheus\Theme\Themes\ModernTheme;
use Morpheus\Frontend\FrontendRenderer;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

// Initialize ThemeManager
$themesDir = __DIR__ . '/../../themes';
$themeManager = new ThemeManager($pdo, $themesDir);

// Register and activate theme
$themeManager->register('modern', new ModernTheme($themesDir));
$themeManager->activate('modern');

// Use with FrontendRenderer
$renderer = new FrontendRenderer($pdo, 'blog', null, null, '', $themeManager);
echo $renderer->renderHome();
```

## Theme Features

### Minimal Theme
- System fonts
- White background
- Simple and fast
- Mobile-first

### Modern Theme
- Gradient backgrounds
- Smooth animations
- Dark mode support
- Card-based layout

### Classic Theme
- Serif fonts (Georgia)
- Sidebar layout
- Traditional design
- Warm colors

## Creating Custom Themes

See [docs/THEMES.md](../../docs/THEMES.md) for complete guide.

## Files

- `index.php` - Theme showcase and switcher
- `demo.php` - Blog demo with active theme
- `setup.php` - Installation script
- `README.md` - This file
