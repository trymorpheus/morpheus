# Theme Integration Example

Apply global theme configuration to forms automatically.

## Features

- ✅ CSS variables from Global Config
- ✅ Dynamic colors (primary, secondary)
- ✅ Custom fonts
- ✅ Branding (logo, app name)
- ✅ White-label ready
- ✅ Zero code changes needed

## Setup

```bash
# Start server
php -S localhost:8000 -t examples/16-theme-integration

# Open browser
http://localhost:8000
```

## Usage

### 1. Configure Theme

```php
$config = new GlobalMetadata($pdo);
$config->set('theme', [
    'primary_color' => '#667eea',
    'secondary_color' => '#764ba2',
    'font_family' => 'Inter, system-ui, sans-serif'
]);

$config->set('application', [
    'name' => 'My App',
    'logo' => '/assets/logo.png'
]);
```

### 2. Enable Global Config

```php
$crud = new Morpheus($pdo, 'users');
$crud->enableGlobalConfig(); // That's it!

echo $crud->renderForm(); // Theme applied automatically!
```

## Theme Options

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `primary_color` | string | Primary brand color | `#667eea` |
| `secondary_color` | string | Secondary brand color | `#764ba2` |
| `background_color` | string | Background color | `#ffffff` |
| `text_color` | string | Text color | `#333333` |
| `font_family` | string | Font family | `system-ui, sans-serif` |
| `border_radius` | string | Border radius | `8px` |

## Application Options

| Option | Type | Description |
|--------|------|-------------|
| `name` | string | Application name |
| `version` | string | Version number |
| `logo` | string | Logo URL |
| `company` | string | Company name |

## How It Works

1. `enableGlobalConfig()` creates ThemeManager
2. ThemeManager reads theme from `_dynamiccrud_config`
3. CSS variables injected into forms
4. Hardcoded colors replaced with `var(--primary-color)`
5. Branding rendered at top of forms

## Use Cases

1. **White-label SaaS** - Different theme per tenant
2. **Multi-brand** - Different brands, same codebase
3. **Dark mode** - Switch themes dynamically
4. **Corporate branding** - Match company colors
5. **Accessibility** - High contrast themes

## Example: Multi-tenant

```php
// Tenant 1
$config->set('theme', [
    'primary_color' => '#ff6b6b',
    'secondary_color' => '#ee5a6f'
]);

// Tenant 2
$config->set('theme', [
    'primary_color' => '#4ecdc4',
    'secondary_color' => '#44a08d'
]);

// Same code, different themes!
$crud->enableGlobalConfig();
echo $crud->renderForm();
```

## CSS Variables Generated

```css
:root {
  --primary-color: #667eea;
  --secondary-color: #764ba2;
  --background-color: #ffffff;
  --text-color: #333333;
  --font-family: system-ui, sans-serif;
  --border-radius: 8px;
}
```

All form styles use these variables automatically!
