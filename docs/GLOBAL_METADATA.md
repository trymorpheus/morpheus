# Global Metadata Guide

**Version:** 2.8.0  
**Status:** Foundation Release

---

## 🎯 Overview

Global Metadata provides **centralized configuration storage** for application-wide settings. Unlike table/column metadata (which configures individual tables), global metadata configures the entire application.

### Key Concept

```
Table Metadata → Configures individual tables
Global Metadata → Configures the entire application
```

---

## 📦 Installation

Global metadata is automatically available in v2.8+. The configuration table `_dynamiccrud_config` is created automatically on first use.

---

## 🚀 Quick Start

### CLI Usage

```bash
# Set configuration
php bin/morpheus config:set application.name "My App"
php bin/morpheus config:set theme '{"primary_color":"#667eea"}'

# Get configuration
php bin/morpheus config:get application.name

# List all configuration
php bin/morpheus config:list

# Delete configuration
php bin/morpheus config:delete application.name
```

### PHP Usage

```php
use Morpheus\GlobalMetadata;

$config = new GlobalMetadata($pdo);

// Set values
$config->set('application.name', 'My App');
$config->set('theme', [
    'primary_color' => '#667eea',
    'secondary_color' => '#764ba2'
]);

// Get values
$appName = $config->get('application.name');
$theme = $config->get('theme');
$default = $config->get('nonexistent', 'default value');

// Check existence
if ($config->has('theme')) {
    // ...
}

// Delete
$config->delete('old.setting');

// Get all
$all = $config->all();

// Clear all
$config->clear();
```

---

## 💾 Storage

Configuration is stored in the `_dynamiccrud_config` table:

```sql
CREATE TABLE _dynamiccrud_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value JSON NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
);
```

### Example Data

```sql
SELECT * FROM _dynamiccrud_config;

+----+------------------+------------------------------------------+---------------------+
| id | config_key       | config_value                             | updated_at          |
+----+------------------+------------------------------------------+---------------------+
|  1 | application.name | "My Application"                         | 2025-01-15 10:30:00 |
|  2 | theme            | {"primary_color":"#667eea"}              | 2025-01-15 10:31:00 |
+----+------------------+------------------------------------------+---------------------+
```

---

## 🎨 Use Cases

### 1. Application Branding

```php
$config->set('application', [
    'name' => 'My ERP System',
    'version' => '2.1.0',
    'logo' => '/assets/logo.png',
    'company' => 'My Company Ltd',
    'support_email' => 'support@mycompany.com'
]);
```

### 2. Theme Configuration

```php
$config->set('theme', [
    'primary_color' => '#667eea',
    'secondary_color' => '#764ba2',
    'font_family' => 'Inter, sans-serif',
    'dark_mode' => [
        'enabled' => true,
        'default' => false
    ]
]);
```

### 3. Feature Flags

```php
$config->set('features', [
    'api_enabled' => true,
    'webhooks_enabled' => true,
    'export_enabled' => true,
    'notifications_enabled' => false
]);

// Check feature
if ($config->get('features')['api_enabled']) {
    // Enable API routes
}
```

### 4. Integration Settings

```php
$config->set('integrations', [
    'stripe' => [
        'enabled' => true,
        'public_key' => 'pk_test_...',
        'webhook_secret' => 'whsec_...'
    ],
    'sendgrid' => [
        'enabled' => true,
        'api_key' => 'SG...'
    ]
]);
```

---

## 🔧 Advanced Usage

### Namespacing

Use dot notation for hierarchical configuration:

```php
$config->set('email.smtp.host', 'smtp.gmail.com');
$config->set('email.smtp.port', 587);
$config->set('email.from.address', 'noreply@example.com');
$config->set('email.from.name', 'My App');
```

### Caching

GlobalMetadata automatically caches values in memory:

```php
// First call hits database
$value1 = $config->get('app.name');

// Second call uses cache (no database query)
$value2 = $config->get('app.name');
```

### JSON Values

Values are automatically encoded/decoded as JSON:

```php
// Set complex structure
$config->set('navigation', [
    'menu' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Users', 'url' => '/users']
    ]
]);

// Retrieved as array
$nav = $config->get('navigation');
echo $nav['menu'][0]['label']; // "Dashboard"
```

---

## 🛠️ CLI Commands

### config:set

Set a configuration value.

```bash
# String value
php bin/morpheus config:set app.name "My App"

# JSON value
php bin/morpheus config:set theme '{"color":"#667eea"}'

# Nested structure
php bin/morpheus config:set email.smtp.host "smtp.gmail.com"
```

### config:get

Get a configuration value.

```bash
php bin/morpheus config:get app.name
# Output: "My App"

php bin/morpheus config:get theme
# Output: {"color":"#667eea"}
```

### config:list

List all configuration.

```bash
php bin/morpheus config:list
# Output:
# 📋 Global Configuration:
#
#   app.name:
#     "My App"
#
#   theme:
#     {
#         "color": "#667eea"
#     }
```

### config:delete

Delete a configuration key.

```bash
php bin/morpheus config:delete old.setting
# Output: ✅ Config deleted: old.setting
```

---

## 🔒 Security Considerations

### Sensitive Data

**DO NOT** store sensitive data (passwords, API keys) in plain text:

```php
// ❌ BAD
$config->set('stripe.secret_key', 'sk_live_...');

// ✅ GOOD - Use environment variables
$stripeKey = getenv('STRIPE_SECRET_KEY');
$config->set('stripe.enabled', true);
```

### Access Control

Restrict access to config commands in production:

```php
// Only allow admins to modify config
if (!$user->isAdmin()) {
    throw new \Exception('Unauthorized');
}

$config->set('app.name', $_POST['name']);
```

---

## 📊 Performance

### Benchmarks

- **set()**: ~1ms (INSERT/UPDATE)
- **get()**: ~0.5ms (first call), ~0.01ms (cached)
- **all()**: ~2ms (100 keys)

### Optimization Tips

1. **Use caching**: Values are cached automatically
2. **Batch reads**: Use `all()` instead of multiple `get()` calls
3. **Index keys**: The `config_key` column is indexed

---

## 🚀 Roadmap

### v2.8 (Current)
- ✅ Basic get/set/delete operations
- ✅ CLI commands
- ✅ JSON storage
- ✅ Automatic caching

### v2.9 (Planned)
- Theme integration with FormGenerator
- Logo/branding injection
- Navigation builder

### v3.0 (Future)
- Security policies
- Feature flags UI
- Multi-environment support

---

## 📚 Examples

See `examples/14-global-config/` for complete examples:
- Basic configuration
- Theme customization
- Feature flags
- Integration settings

---

## 🤝 Contributing

Global Metadata is part of DynamicCRUD core. See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
