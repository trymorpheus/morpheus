# UI Components Library

Reusable, accessible, and beautiful UI components for building modern web applications.

## Features

- ✅ **15 Components** - Alert, Badge, Button, Card, Modal, Tabs, Accordion, Table, and more
- 🎨 **Themeable** - Customize colors to match your brand
- ♿ **Accessible** - ARIA labels and keyboard navigation
- 📱 **Responsive** - Mobile-first design
- 🚀 **Zero Dependencies** - Pure PHP, no external libraries
- 💅 **Modern Design** - Clean, professional styling

## Available Components

### Alerts
Display important messages to users with different severity levels.

```php
Components::alert('Success message!', 'success');
Components::alert('Error occurred!', 'danger');
Components::alert('Warning!', 'warning');
Components::alert('Info message', 'info', false); // Non-dismissible
```

### Badges
Small labels for status indicators and counts.

```php
Components::badge('New', 'primary');
Components::badge('Active', 'success');
Components::badge('Pending', 'warning');
```

### Buttons
Styled buttons with different types and sizes.

```php
Components::button('Click Me', 'primary');
Components::button('Small', 'success', ['size' => 'small']);
Components::button('Link', 'primary', ['href' => '/page']);
```

### Button Group
Group related buttons together.

```php
Components::buttonGroup([
    ['text' => 'Edit', 'type' => 'primary', 'onclick' => 'edit()'],
    ['text' => 'Delete', 'type' => 'danger', 'onclick' => 'delete()']
]);
```

### Cards
Container for content with optional header and footer.

```php
Components::card('Title', '<p>Content here</p>');
Components::card('Title', '<p>Content</p>', '<button>Action</button>');
```

### Stat Cards
Display statistics with optional trend indicators.

```php
Components::statCard('Total Users', '1,234', 'up', '+12%');
Components::statCard('Revenue', '$45,678', 'down', '-3.2%');
```

### Progress Bar
Visual progress indicator.

```php
Components::progressBar(75, 'Upload Progress');
Components::progressBar(100, 'Complete!');
```

### Breadcrumb
Navigation breadcrumb trail.

```php
Components::breadcrumb([
    ['text' => 'Home', 'href' => '/'],
    ['text' => 'Products', 'href' => '/products'],
    'Current Page'
]);
```

### Pagination
Page navigation for lists.

```php
Components::pagination(3, 10, '?page=');
```

### Tabs
Organize content into tabbed sections.

```php
Components::tabs([
    'tab1' => ['title' => 'Tab 1', 'content' => '<p>Content 1</p>'],
    'tab2' => ['title' => 'Tab 2', 'content' => '<p>Content 2</p>']
]);
```

### Accordion
Collapsible content sections.

```php
Components::accordion([
    ['title' => 'Question 1', 'content' => '<p>Answer 1</p>'],
    ['title' => 'Question 2', 'content' => '<p>Answer 2</p>']
]);
```

### Table
Styled data table with striped rows and hover effects.

```php
Components::table(
    ['Name', 'Email', 'Status'],
    [
        ['John Doe', 'john@example.com', 'Active'],
        ['Jane Smith', 'jane@example.com', 'Inactive']
    ],
    ['striped' => true, 'hover' => true]
);
```

### Dropdown
Dropdown menu with links.

```php
Components::dropdown('Actions', [
    ['text' => 'Edit', 'href' => '#edit'],
    ['text' => 'Delete', 'href' => '#delete']
]);
```

### Modal
Modal dialog with header, content, and footer.

```php
Components::modal(
    'my-modal',
    'Confirm Action',
    '<p>Are you sure?</p>',
    ['primary_button' => 'Confirm', 'close_button' => 'Cancel']
);

// Show modal
echo Components::button('Open', 'primary', [
    'onclick' => "document.getElementById('my-modal').style.display='block'"
]);
```

### Toast
Temporary notification that auto-dismisses.

```php
Components::toast('Saved successfully!', 'success', 3000);
```

## Theming

Customize component colors to match your brand:

```php
use Morpheus\UI\Components;

Components::setTheme([
    'primary' => '#667eea',
    'secondary' => '#718096',
    'success' => '#48bb78',
    'danger' => '#f56565',
    'warning' => '#ed8936',
    'info' => '#4299e1'
]);
```

## Integration with DynamicCRUD

Components work seamlessly with DynamicCRUD features:

```php
// Use in custom forms
$form = $crud->renderForm();
$form .= Components::alert('Fill all required fields', 'info');

// Use in list views
$list = $crud->renderList();
$list .= Components::pagination($page, $totalPages);

// Use in admin panels
echo Components::card(
    'Users',
    $crud->renderList(),
    Components::button('Add User', 'primary', ['href' => '?action=create'])
);
```

## Accessibility

All components include:
- Semantic HTML
- ARIA labels and roles
- Keyboard navigation support
- Screen reader friendly

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## Examples

Run the example:

```bash
php -S localhost:8000 -t examples/20-ui-components
```

Visit: http://localhost:8000

## Use Cases

- **Admin Panels** - Build professional admin interfaces
- **Dashboards** - Create data visualization dashboards
- **Forms** - Enhance form layouts with cards and alerts
- **Lists** - Add pagination and tables to data lists
- **Notifications** - Show user feedback with toasts and alerts
- **Navigation** - Implement breadcrumbs and tabs

## Tips

1. **Combine Components** - Mix components for rich UIs
2. **Custom Styling** - Override styles with inline CSS or classes
3. **JavaScript Integration** - Add custom JS for interactions
4. **Responsive Design** - Components adapt to screen sizes
5. **Theme Consistency** - Set theme once, use everywhere
