# Template System Guide

DynamicCRUD includes a Blade-like template engine for customizing form rendering and creating reusable layouts.

## Features

- **Blade-like syntax** - Familiar directives (@if, @foreach, {{ }})
- **Automatic escaping** - XSS protection by default
- **Layout inheritance** - @extends, @section, @yield
- **Partials** - @include for reusable components
- **File caching** - Compiled templates cached for performance
- **No dependencies** - Pure PHP implementation

---

## Quick Start

### 1. Basic Usage

```php
<?php
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Template\BladeTemplate;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'password');

// Create template engine
$engine = new BladeTemplate(
    __DIR__ . '/templates',  // Template directory
    __DIR__ . '/cache'       // Cache directory
);

// Use with DynamicCRUD
$crud = new DynamicCRUD($pdo, 'users');
$crud->setTemplateEngine($engine);
```

### 2. Render a Template

```php
// From string
$html = $engine->render('Hello, {{ $name }}!', ['name' => 'World']);

// From file
$html = $engine->renderFile('welcome.blade.php', ['user' => $user]);
```

---

## Syntax Reference

### Variables

```blade
{{-- Escaped output (safe) --}}
{{ $name }}
{{ $user->email }}
{{ $data['key'] }}

{{-- Raw output (unescaped) --}}
{!! $html !!}
{!! $content !!}
```

### Conditionals

```blade
@if ($user->isAdmin())
    <p>Welcome, Admin!</p>
@elseif ($user->isModerator())
    <p>Welcome, Moderator!</p>
@else
    <p>Welcome, User!</p>
@endif
```

### Loops

```blade
{{-- Foreach loop --}}
@foreach ($users as $user)
    <li>{{ $user->name }}</li>
@endforeach

{{-- For loop --}}
@for ($i = 0; $i < 10; $i++)
    <p>Item {{ $i }}</p>
@endfor
```

### Layout Inheritance

**Layout file** (`layouts/app.blade.php`):
```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

**Page file** (`pages/home.blade.php`):
```blade
@extends('layouts/app')

@section('title')
    Home Page
@endsection

@section('content')
    <h1>Welcome!</h1>
@endsection
```

### Partials

```blade
{{-- Include a partial --}}
@include('partials/header')

<main>
    Content here
</main>

@include('partials/footer')
```

---

## Form Templates

### Custom Input Template

Create `templates/forms/input.blade.php`:

```blade
<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}</label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        value="{{ $value }}"
        @if ($required) required @endif
        class="form-control"
    >
    
    @if ($error)
        <div class="error">{{ $error }}</div>
    @endif
</div>
```

### Use in PHP

```php
$html = $engine->renderFile('forms/input.blade.php', [
    'name' => 'email',
    'type' => 'email',
    'label' => 'Email Address',
    'value' => $user->email ?? '',
    'required' => true,
    'error' => $errors['email'] ?? null
]);
```

---

## Advanced Features

### Nested Conditions

```blade
@if ($user)
    @if ($user->isActive())
        <p>Active user</p>
    @else
        <p>Inactive user</p>
    @endif
@endif
```

### Array Access

```blade
{{ $data['key'] }}
{{ $user['profile']['name'] }}
```

### Object Properties

```blade
{{ $user->name }}
{{ $post->author->email }}
```

### Missing Variables

```blade
{{-- Returns empty string if variable doesn't exist --}}
{{ $missing }}

{{-- Use null coalescing --}}
{{ $name ?? 'Guest' }}
```

---

## Integration with DynamicCRUD

### Custom Form Template

```php
$crud = new DynamicCRUD($pdo, 'users');
$crud->setTemplateEngine($engine);

// FormGenerator will use templates if available
echo $crud->renderForm();
```

### Override Default Templates

Create your own templates in `templates/forms/`:
- `form.blade.php` - Main form wrapper
- `input.blade.php` - Text inputs
- `select.blade.php` - Dropdowns
- `textarea.blade.php` - Text areas
- `checkbox.blade.php` - Checkboxes

---

## Performance

### Caching

Templates are automatically compiled and cached:

```php
$engine = new BladeTemplate(
    __DIR__ . '/templates',
    __DIR__ . '/cache'  // Compiled templates stored here
);
```

### Clear Cache

```php
// Delete all files in cache directory
array_map('unlink', glob(__DIR__ . '/cache/*.php'));
```

---

## Security

### Automatic Escaping

`{{ }}` automatically escapes HTML:

```blade
{{ $userInput }}  {{-- Safe: <script> becomes &lt;script&gt; --}}
```

### Raw Output

Use `{!! !!}` only for trusted content:

```blade
{!! $trustedHtml !!}  {{-- Dangerous if $trustedHtml contains user input --}}
```

---

## Examples

### Login Form

```blade
<form method="POST" action="/login">
    <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">
    
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ $email }}" required>
        @if ($errors['email'])
            <div class="error">{{ $errors['email'] }}</div>
        @endif
    </div>
    
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
        @if ($errors['password'])
            <div class="error">{{ $errors['password'] }}</div>
        @endif
    </div>
    
    <button type="submit">Login</button>
</form>
```

### User List

```blade
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if ($user->isActive())
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

---

## API Reference

### BladeTemplate

```php
class BladeTemplate implements TemplateEngine
{
    public function __construct(string $templatePath, string $cachePath);
    public function render(string $template, array $data = []): string;
    public function renderFile(string $path, array $data = []): string;
    public function exists(string $template): bool;
}
```

### Methods

- `render(string $template, array $data)` - Compile and render template string
- `renderFile(string $path, array $data)` - Render template from file
- `exists(string $template)` - Check if template file exists

---

## Limitations

- No `@while` loops (use `@for` instead)
- No `@switch` statements (use `@if/@elseif/@else`)
- No `@php` blocks (use regular PHP in templates)
- Layout inheritance is basic (no nested extends)

---

## See Also

- [Customization Guide](CUSTOMIZATION.md) - Metadata options
- [Hooks System](HOOKS.md) - Lifecycle events
- [Virtual Fields](VIRTUAL_FIELDS.md) - Custom form fields
- [Internationalization](I18N.md) - Multi-language support
