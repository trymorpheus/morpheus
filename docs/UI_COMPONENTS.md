# UI Components Library

Complete guide to using DynamicCRUD's UI Components Library for building beautiful, accessible user interfaces.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Components](#components)
  - [Alerts](#alerts)
  - [Badges](#badges)
  - [Buttons](#buttons)
  - [Cards](#cards)
  - [Modals](#modals)
  - [Tabs](#tabs)
  - [Accordion](#accordion)
  - [Tables](#tables)
  - [Navigation](#navigation)
  - [Data Display](#data-display)
- [Theming](#theming)
- [Accessibility](#accessibility)
- [Best Practices](#best-practices)

---

## Overview

The UI Components Library provides 15 reusable, accessible, and beautiful components for building modern web applications. All components are:

- ✅ **Zero Dependencies** - Pure PHP, no external libraries required
- ✅ **Accessible** - ARIA labels, semantic HTML, keyboard navigation
- ✅ **Responsive** - Mobile-first design that adapts to all screen sizes
- ✅ **Themeable** - Customize colors to match your brand
- ✅ **XSS Protected** - Automatic HTML escaping for security
- ✅ **Modern Design** - Clean, professional styling

---

## Installation

Components are included with DynamicCRUD. No additional installation required.

```bash
composer require trymorpheus/morpheus
```

---

## Quick Start

```php
<?php
require 'vendor/autoload.php';

use Morpheus\UI\Components;

// Optional: Set custom theme
Components::setTheme([
    'primary' => '#667eea',
    'success' => '#48bb78'
]);

// Use components
echo Components::alert('Welcome!', 'success');
echo Components::button('Click Me', 'primary');
echo Components::card('Title', '<p>Content here</p>');
```

---

## Components

### Alerts

Display important messages to users with different severity levels.

#### Basic Usage

```php
// Success alert
echo Components::alert('Changes saved successfully!', 'success');

// Error alert
echo Components::alert('An error occurred.', 'danger');

// Warning alert
echo Components::alert('Please review your input.', 'warning');

// Info alert
echo Components::alert('This is informational.', 'info');
```

#### Non-dismissible Alert

```php
echo Components::alert('Important notice', 'warning', false);
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$message` | string | required | Alert message text |
| `$type` | string | `'info'` | Alert type: `success`, `danger`, `warning`, `info` |
| `$dismissible` | bool | `true` | Show close button |

---

### Badges

Small labels for status indicators, counts, and tags.

#### Basic Usage

```php
echo Components::badge('New', 'primary');
echo Components::badge('Active', 'success');
echo Components::badge('Pending', 'warning');
echo Components::badge('Error', 'danger');
```

#### Use Cases

```php
// Status indicators
echo 'Status: ' . Components::badge('Active', 'success');

// Counts
echo 'Messages ' . Components::badge('5', 'primary');

// Tags
echo Components::badge('PHP', 'info') . ' ' . Components::badge('MySQL', 'info');
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$text` | string | required | Badge text |
| `$type` | string | `'primary'` | Badge color type |

---

### Buttons

Styled buttons with different types, sizes, and actions.

#### Basic Usage

```php
// Standard button
echo Components::button('Click Me', 'primary');

// Different types
echo Components::button('Success', 'success');
echo Components::button('Danger', 'danger');
echo Components::button('Warning', 'warning');
```

#### Button Sizes

```php
echo Components::button('Small', 'primary', ['size' => 'small']);
echo Components::button('Medium', 'primary', ['size' => 'medium']);
echo Components::button('Large', 'primary', ['size' => 'large']);
```

#### Button as Link

```php
echo Components::button('Go to Page', 'primary', ['href' => '/page']);
```

#### Button with JavaScript

```php
echo Components::button('Delete', 'danger', [
    'onclick' => 'if(confirm("Are you sure?")) deleteItem()'
]);
```

#### Button Group

```php
echo Components::buttonGroup([
    ['text' => 'Edit', 'type' => 'primary', 'onclick' => 'edit()'],
    ['text' => 'Delete', 'type' => 'danger', 'onclick' => 'delete()'],
    ['text' => 'View', 'type' => 'info', 'onclick' => 'view()']
]);
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$text` | string | required | Button text |
| `$type` | string | `'primary'` | Button color type |
| `$options` | array | `[]` | Additional options (size, href, onclick) |

---

### Cards

Container component for grouping related content.

#### Basic Card

```php
echo Components::card(
    'Card Title',
    '<p>This is the card content.</p>'
);
```

#### Card with Footer

```php
echo Components::card(
    'User Profile',
    '<p>Name: John Doe</p><p>Email: john@example.com</p>',
    Components::button('Edit', 'primary') . ' ' . Components::button('Delete', 'danger')
);
```

#### Card Options

```php
echo Components::card(
    'Custom Card',
    '<p>Content</p>',
    null,
    ['width' => '500px', 'header_bg' => '#667eea']
);
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$title` | string | required | Card title |
| `$content` | string | required | Card content (HTML) |
| `$footer` | string\|null | `null` | Card footer (HTML) |
| `$options` | array | `[]` | width, header_bg |

---

### Modals

Modal dialogs for confirmations, forms, and detailed content.

#### Basic Modal

```php
// Define modal
echo Components::modal(
    'confirm-modal',
    'Confirm Action',
    '<p>Are you sure you want to proceed?</p>'
);

// Button to open modal
echo Components::button('Open Modal', 'primary', [
    'onclick' => "document.getElementById('confirm-modal').style.display='block'"
]);
```

#### Modal with Custom Buttons

```php
echo Components::modal(
    'delete-modal',
    'Delete Item',
    '<p>This action cannot be undone.</p>',
    [
        'primary_button' => 'Delete',
        'close_button' => 'Cancel',
        'width' => '500px'
    ]
);
```

#### Modal without Footer

```php
echo Components::modal(
    'info-modal',
    'Information',
    '<p>Read-only information here.</p>',
    ['show_footer' => false]
);
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$id` | string | required | Unique modal ID |
| `$title` | string | required | Modal title |
| `$content` | string | required | Modal content (HTML) |
| `$options` | array | `[]` | width, show_footer, primary_button, close_button |

---

### Tabs

Organize content into tabbed sections.

#### Basic Tabs

```php
echo Components::tabs([
    'profile' => [
        'title' => 'Profile',
        'content' => '<h3>Profile Information</h3><p>User details here.</p>'
    ],
    'settings' => [
        'title' => 'Settings',
        'content' => '<h3>Account Settings</h3><p>Configuration options.</p>'
    ],
    'notifications' => [
        'title' => 'Notifications',
        'content' => '<h3>Notification Preferences</h3><p>Email settings.</p>'
    ]
], 'user-tabs');
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$tabs` | array | required | Array of tabs with title and content |
| `$id` | string | `'tabs'` | Unique tabs group ID |

---

### Accordion

Collapsible content sections for FAQs and expandable content.

#### Basic Accordion

```php
echo Components::accordion([
    ['title' => 'What is DynamicCRUD?', 'content' => '<p>A PHP library for CRUD operations.</p>'],
    ['title' => 'How do I install it?', 'content' => '<p>Run: composer require trymorpheus/morpheus</p>'],
    ['title' => 'Is it free?', 'content' => '<p>Yes, it\'s open-source under MIT license.</p>']
], 'faq-accordion');
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$items` | array | required | Array of items with title and content |
| `$id` | string | `'accordion'` | Unique accordion ID |

---

### Tables

Styled data tables with striped rows and hover effects.

#### Basic Table

```php
echo Components::table(
    ['Name', 'Email', 'Role'],
    [
        ['John Doe', 'john@example.com', 'Admin'],
        ['Jane Smith', 'jane@example.com', 'User'],
        ['Bob Johnson', 'bob@example.com', 'Manager']
    ]
);
```

#### Table with Badges

```php
echo Components::table(
    ['Name', 'Status', 'Actions'],
    [
        ['John', Components::badge('Active', 'success'), Components::button('Edit', 'primary', ['size' => 'small'])],
        ['Jane', Components::badge('Inactive', 'secondary'), Components::button('Edit', 'primary', ['size' => 'small'])]
    ]
);
```

#### Table Options

```php
echo Components::table(
    ['Col1', 'Col2'],
    [['Data1', 'Data2']],
    ['striped' => false, 'hover' => false]
);
```

#### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$headers` | array | required | Table header labels |
| `$rows` | array | required | Table rows (2D array) |
| `$options` | array | `[]` | striped, hover |

---

### Navigation

#### Breadcrumb

Navigation breadcrumb trail showing current location.

```php
echo Components::breadcrumb([
    ['text' => 'Home', 'href' => '/'],
    ['text' => 'Products', 'href' => '/products'],
    ['text' => 'Electronics', 'href' => '/products/electronics'],
    'Laptop' // Current page (no link)
]);
```

#### Pagination

Page navigation for lists and search results.

```php
// Current page 3 of 10
echo Components::pagination(3, 10, '?page=');

// Custom base URL
echo Components::pagination(5, 20, '/products?category=electronics&page=');
```

#### Dropdown Menu

Dropdown menu with links.

```php
echo Components::dropdown('Actions', [
    ['text' => 'Edit', 'href' => '?action=edit&id=1'],
    ['text' => 'Delete', 'href' => '?action=delete&id=1'],
    ['text' => 'Archive', 'href' => '?action=archive&id=1']
], ['type' => 'primary']);
```

---

### Data Display

#### Stat Cards

Display statistics with optional trend indicators.

```php
// With trend
echo Components::statCard('Total Users', '1,234', 'up', '+12%');
echo Components::statCard('Revenue', '$45,678', 'down', '-3.2%');

// Without trend
echo Components::statCard('Active Sessions', '156');
```

#### Progress Bar

Visual progress indicator.

```php
echo Components::progressBar(75, 'Upload Progress');
echo Components::progressBar(100, 'Complete!');
echo Components::progressBar(45); // No label
```

#### Toast Notifications

Temporary notifications that auto-dismiss.

```php
echo Components::toast('Saved successfully!', 'success', 3000);
echo Components::toast('Error occurred!', 'danger', 5000);
echo Components::toast('Processing...', 'info', 2000);
```

---

## Theming

Customize component colors to match your brand identity.

### Set Theme

```php
use Morpheus\UI\Components;

Components::setTheme([
    'primary' => '#667eea',
    'secondary' => '#718096',
    'success' => '#48bb78',
    'danger' => '#f56565',
    'warning' => '#ed8936',
    'info' => '#4299e1',
    'light' => '#f7fafc',
    'dark' => '#2d3748'
]);
```

### Theme Integration

```php
// Set theme once at application start
Components::setTheme(['primary' => '#ff6b6b']);

// All components use the theme
echo Components::button('Primary Button', 'primary'); // Uses #ff6b6b
echo Components::badge('New', 'primary'); // Uses #ff6b6b
```

---

## Accessibility

All components follow accessibility best practices:

### Semantic HTML
- Proper HTML5 elements (`<nav>`, `<button>`, `<table>`)
- Meaningful structure and hierarchy

### ARIA Labels
```php
// Alerts include role="alert"
Components::alert('Message', 'info');

// Navigation includes aria-label
Components::breadcrumb([...]);
Components::pagination(1, 10);
```

### Keyboard Navigation
- All interactive elements are keyboard accessible
- Tab navigation works correctly
- Enter/Space activate buttons

### Screen Readers
- Descriptive labels and text
- Proper heading hierarchy
- Alternative text where needed

---

## Best Practices

### 1. Combine Components

Build rich UIs by combining components:

```php
echo Components::card(
    'User Management',
    Components::table(
        ['Name', 'Status', 'Actions'],
        [
            ['John', Components::badge('Active', 'success'), Components::buttonGroup([...])],
            ['Jane', Components::badge('Inactive', 'secondary'), Components::buttonGroup([...])]
        ]
    ),
    Components::pagination(1, 5)
);
```

### 2. Consistent Theming

Set theme once at application start:

```php
// config.php
Components::setTheme([
    'primary' => '#your-brand-color',
    'success' => '#your-success-color'
]);
```

### 3. XSS Protection

Components automatically escape HTML. For trusted HTML, use raw output:

```php
// User input (escaped automatically)
echo Components::alert($userInput, 'info');

// Trusted HTML (already safe)
$safeHtml = '<strong>Important:</strong> Read this';
echo Components::card('Notice', $safeHtml);
```

### 4. Responsive Design

Components are mobile-first. Test on different screen sizes:

```php
// Cards stack on mobile
echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">';
echo Components::card('Card 1', 'Content');
echo Components::card('Card 2', 'Content');
echo '</div>';
```

### 5. Performance

Components generate inline styles. For production, consider:

- Caching rendered components
- Minifying HTML output
- Using CDN for assets

---

## Integration Examples

### With DynamicCRUD Forms

```php
$crud = new Morpheus($pdo, 'users');

echo Components::card(
    'Create User',
    $crud->renderForm(),
    Components::button('Cancel', 'secondary', ['href' => '/users'])
);
```

### With List Views

```php
$crud = new Morpheus($pdo, 'products');

echo Components::card(
    'Products',
    $crud->renderList(),
    Components::button('Add Product', 'primary', ['href' => '?action=create'])
);
```

### With Admin Panel

```php
use Morpheus\Admin\AdminPanel;

$admin = new AdminPanel($pdo);
$admin->addTable('users');

// Components work seamlessly
echo $admin->render();
```

---

## Browser Support

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## Examples

See complete examples in `examples/20-ui-components/`

```bash
php -S localhost:8000 -t examples/20-ui-components
```

Visit: http://localhost:8000

---

## Troubleshooting

### Components Not Styled

Ensure you're outputting the HTML:

```php
echo Components::button('Click', 'primary'); // ✅ Correct
Components::button('Click', 'primary'); // ❌ Wrong (no echo)
```

### Theme Not Applied

Set theme before using components:

```php
Components::setTheme(['primary' => '#ff0000']);
echo Components::button('Test', 'primary'); // Uses #ff0000
```

### JavaScript Not Working

Ensure you're including the full component output (includes `<script>` tags):

```php
echo Components::tabs([...]); // Includes JavaScript
echo Components::modal('id', 'Title', 'Content'); // Includes JavaScript
```

---

## Contributing

Found a bug or want to add a component? See [CONTRIBUTING.md](../CONTRIBUTING.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
