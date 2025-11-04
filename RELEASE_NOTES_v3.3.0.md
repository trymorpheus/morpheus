# Release Notes v3.3.0

## 🎨 UI Components Library

DynamicCRUD v3.3.0 introduces a complete UI Components Library with 15 reusable, accessible, and beautiful components for building modern web applications.

### ✨ New Features

#### UI Components Library
- **15 Reusable Components** - Alert, Badge, Button, Card, Modal, Tabs, Accordion, Table, Breadcrumb, Pagination, Dropdown, StatCard, ProgressBar, Toast, ButtonGroup
- **Themeable** - Customize colors with `Components::setTheme()`
- **Accessible** - ARIA labels, semantic HTML, keyboard navigation
- **Responsive** - Mobile-first design that adapts to all screen sizes
- **Zero Dependencies** - Pure PHP, no external libraries required
- **XSS Protected** - Automatic HTML escaping for security
- **Modern Design** - Clean, professional styling with CSS animations

#### AdminPanel Integration
- Refactored AdminPanel to use Components library
- Improved code maintainability and consistency
- Better visual consistency across the application

### 📦 Installation

```bash
composer require dynamiccrud/dynamiccrud
```

### 🚀 Quick Start

```php
use DynamicCRUD\UI\Components;

// Set custom theme
Components::setTheme(['primary' => '#667eea']);

// Use components
echo Components::alert('Success!', 'success');
echo Components::badge('New', 'primary');
echo Components::button('Click Me', 'primary');
echo Components::card('Title', '<p>Content</p>');
echo Components::modal('id', 'Title', 'Content');
echo Components::tabs([...]);
echo Components::table(['Name', 'Email'], [[...]]);
echo Components::pagination(3, 10);
```

### 📚 Documentation

- **Complete Guide**: [docs/UI_COMPONENTS.md](docs/UI_COMPONENTS.md)
- **Live Example**: [examples/20-ui-components/](examples/20-ui-components/)
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)

### 🧪 Testing

- **367 tests** (100% passing)
- **745 assertions**
- **90% code coverage**
- **26 new tests** for Components

### 📊 Component List

1. **alert()** - Dismissible alerts (success, danger, warning, info)
2. **badge()** - Status badges with custom colors
3. **button()** - Buttons with 3 sizes and link support
4. **buttonGroup()** - Grouped action buttons
5. **card()** - Content cards with header/footer
6. **modal()** - Modal dialogs with custom buttons
7. **tabs()** - Interactive tabbed content
8. **accordion()** - Collapsible content sections
9. **table()** - Styled tables with striped/hover
10. **breadcrumb()** - Navigation breadcrumbs
11. **pagination()** - Page navigation
12. **dropdown()** - Dropdown menus
13. **statCard()** - Statistics cards with trends
14. **progressBar()** - Visual progress indicators
15. **toast()** - Auto-dismissing notifications

### 🎯 Use Cases

- **Admin Panels** - Build professional admin interfaces
- **Dashboards** - Create data visualization dashboards
- **Forms** - Enhance form layouts with cards and alerts
- **Lists** - Add pagination and tables to data lists
- **Notifications** - Show user feedback with toasts and alerts
- **Navigation** - Implement breadcrumbs and tabs

### 🔄 Migration from v3.2

No breaking changes. Components are additive and optional.

To use Components in your existing code:

```php
use DynamicCRUD\UI\Components;

// Replace inline HTML with Components
echo Components::alert('Message', 'success');
echo Components::button('Action', 'primary');
```

### 🐛 Bug Fixes

- None in this release

### ⚠️ Breaking Changes

- None

### 📈 Stats

- **39 PHP classes** (~14,000 lines)
- **38 working examples**
- **22 technical documents**
- **367 automated tests** (100% passing)
- **90% code coverage**

### 🙏 Credits

**Creator & Project Lead**: Mario Raúl Carbonell Martínez  
**Development**: Amazon Q, Gemini 2.5 Pro

### 📄 License

MIT License - see [LICENSE](LICENSE) file for details

---

## Previous Releases

- [v3.2.0 - Workflow Engine](https://github.com/mcarbonell/DynamicCRUD/releases/tag/v3.2.0)
- [v3.1.0 - Admin Panel Generator](https://github.com/mcarbonell/DynamicCRUD/releases/tag/v3.1.0)
- [v3.0.0 - REST API Generator](https://github.com/mcarbonell/DynamicCRUD/releases/tag/v3.0.0)

---

**Full Changelog**: https://github.com/mcarbonell/DynamicCRUD/compare/v3.2.0...v3.3.0
