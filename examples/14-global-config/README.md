# Global Config Examples

Centralized configuration storage for application-wide settings.

## Examples

### 1. basic-usage.php
Interactive web interface for managing global configuration.

**Features:**
- Application settings (name, version, company)
- Theme settings (colors, dark mode)
- View all configuration
- Clear all configuration

**Usage:**
```bash
php -S localhost:8000 -t examples/14-global-config
# Open: http://localhost:8000/basic-usage.php
```

## CLI Usage

```bash
# Set configuration
php bin/morpheus config:set application.name "My App"
php bin/morpheus config:set theme '{"primary_color":"#667eea"}'

# Get configuration
php bin/morpheus config:get application.name

# List all configuration
php bin/morpheus config:list

# Delete configuration
php bin/morpheus config:delete old.setting
```

## PHP Usage

```php
use Morpheus\GlobalMetadata;

$config = new GlobalMetadata($pdo);

// Set values
$config->set('app.name', 'My App');
$config->set('theme', ['primary_color' => '#667eea']);

// Get values
$appName = $config->get('app.name');
$theme = $config->get('theme');

// Check existence
if ($config->has('theme')) {
    // ...
}

// Get all
$all = $config->all();

// Delete
$config->delete('old.key');

// Clear all
$config->clear();
```

## Use Cases

1. **Application Branding** - Logo, name, company info
2. **Theme Configuration** - Colors, fonts, dark mode
3. **Feature Flags** - Enable/disable features
4. **Integration Settings** - API keys, webhooks
5. **Email Configuration** - SMTP settings
6. **Multi-tenant Settings** - Tenant-specific config

## Storage

Configuration is stored in `_dynamiccrud_config` table:

```sql
CREATE TABLE _dynamiccrud_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value JSON NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Documentation

See [docs/GLOBAL_METADATA.md](../../docs/GLOBAL_METADATA.md) for complete documentation.
