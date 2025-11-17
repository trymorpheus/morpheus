# 05. Production Features

Enterprise-ready features for production applications.

## Examples

### Internationalization (`i18n.php`)
Multi-language support with auto-detection.

**Features:**
- 3 languages: English, Spanish, French
- Auto-detection: URL → Session → Browser
- Translated forms and validation messages
- Client + server-side translations

```php
// Auto-detect
$crud = new Morpheus($pdo, 'users');

// Force language
$crud = new Morpheus($pdo, 'users', locale: 'es');
```

### Template System (`templates.php`)
Blade-like template engine for custom layouts.

**Features:**
- Layout inheritance (@extends, @section, @yield)
- Partials (@include)
- Control structures (@if, @foreach)
- Automatic escaping ({{ }} vs {!! !!})
- File caching

```php
$engine = new BladeTemplate($templateDir, $cacheDir);
$crud->setTemplateEngine($engine);

echo $engine->render('@extends("layout") @section("content") ... @endsection', $data);
```

### Audit Logging (`audit.php`)
Track all database changes.

**Features:**
- Logs create, update, delete operations
- Stores old and new values as JSON
- Tracks user ID and IP address
- Automatic via enableAudit()

```php
$crud->enableAudit($userId);
$crud->handleSubmission(); // Automatically logged!
```

## Next Steps

- [Database Examples](../06-databases/) - MySQL and PostgreSQL specific features
