# Customization Guide - DynamicCRUD

This guide explains how to customize and extend DynamicCRUD to fit your specific needs.

## Table of Contents

1. [JSON Metadata](#json-metadata)
2. [Validation Customization](#validation-customization)
3. [Style Customization](#style-customization)
4. [JavaScript Customization](#javascript-customization)
5. [File Uploads](#file-uploads)
6. [Advanced Examples](#advanced-examples)

---

## JSON Metadata

Metadata is defined in database column comments using JSON format.

### Available Properties

```json
{
  "type": "email|url|color|tel|password|search|time|week|month|range|file|number|text",
  "label": "Visible field label",
  "placeholder": "Placeholder text",
  "tooltip": "Help text that appears on hover",
  "min": 0,
  "max": 100,
  "step": "0.01",
  "minlength": 3,
  "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}",
  "readonly": true,
  "hidden": true,
  "autocomplete": "email",
  "display_column": "name",
  "accept": "image/*",
  "allowed_mimes": ["image/jpeg", "image/png"],
  "max_size": 2097152
}
```

### New HTML5 Input Types

- `color` - Color picker
- `tel` - Telephone number
- `password` - Password field
- `search` - Search input
- `time` - Time picker
- `week` - Week picker
- `month` - Month picker
- `range` - Slider input

### Examples by Field Type

#### Email Field with Placeholder
```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "autocomplete": "email", "tooltip": "We will use this email to contact you"}';
```

#### Color Picker
```sql
ALTER TABLE settings 
MODIFY COLUMN brand_color VARCHAR(7) 
COMMENT '{"type": "color", "label": "Brand Color", "placeholder": "#000000"}';
```

#### Phone Number with Pattern
```sql
ALTER TABLE contacts 
MODIFY COLUMN phone VARCHAR(20) 
COMMENT '{"type": "tel", "label": "Phone", "placeholder": "555-123-4567", "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}"}';
```

#### Range Slider
```sql
ALTER TABLE surveys 
MODIFY COLUMN satisfaction INT 
COMMENT '{"type": "range", "label": "Satisfaction", "min": 0, "max": 100, "step": 10}';
```

#### URL Field
```sql
ALTER TABLE websites 
MODIFY COLUMN url VARCHAR(255) 
COMMENT '{"type": "url", "label": "Website", "tooltip": "Must start with http:// or https://"}';
```

#### Numeric Field with Range
```sql
ALTER TABLE products 
MODIFY COLUMN price DECIMAL(10,2) 
COMMENT '{"type": "number", "min": 0.01, "max": 9999.99, "label": "Price"}';
```

#### Text Field with Minimum Length
```sql
ALTER TABLE posts 
MODIFY COLUMN title VARCHAR(200) 
COMMENT '{"label": "Title", "minlength": 5, "tooltip": "Minimum 5 characters"}';
```

#### Hidden Field
```sql
ALTER TABLE users 
MODIFY COLUMN created_at TIMESTAMP 
COMMENT '{"hidden": true}';
```

#### File/Image Field
```sql
ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) 
COMMENT '{
  "type": "file",
  "label": "Product Image",
  "accept": "image/*",
  "allowed_mimes": ["image/jpeg", "image/png", "image/gif", "image/webp"],
  "max_size": 5242880,
  "tooltip": "Upload an image (max. 5MB)"
}';
```

#### Foreign Key with Display Column
```sql
ALTER TABLE posts 
MODIFY COLUMN category_id INT 
COMMENT '{"display_column": "name", "label": "Category"}';
```

---

## Validation Customization

### Client-Side Validation (JavaScript)

The `dynamiccrud.js` file provides automatic validation. You can extend it:

```javascript
// Add custom validation
class MyCustomValidator extends DynamicCRUDValidator {
    validateField(field) {
        // Call base validation
        const isValid = super.validateField(field);
        
        // Add custom validation
        if (field.name === 'username') {
            const value = field.value.trim();
            if (value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                this.showError(field, 'Only letters, numbers and underscores');
                return false;
            }
        }
        
        return isValid;
    }
}

// Use custom validator
new MyCustomValidator();
```

### Server-Side Validation (PHP)

For complex validations, extend `ValidationEngine`:

```php
class CustomValidationEngine extends \DynamicCRUD\ValidationEngine
{
    protected function validateMetadata(array $column, $value): void
    {
        parent::validateMetadata($column, $value);
        
        // Custom validation
        if ($column['name'] === 'username') {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                $this->errors[$column['name']][] = 
                    'Username can only contain letters, numbers and underscores';
            }
        }
    }
}
```

---

## Style Customization

### Override CSS Styles

Create your own CSS file after loading `dynamiccrud.css`:

```html
<link rel="stylesheet" href="assets/dynamiccrud.css">
<link rel="stylesheet" href="assets/my-custom-styles.css">
```

```css
/* my-custom-styles.css */

/* Change button color */
.form-group button[type="submit"] {
    background: #28a745;
}

.form-group button[type="submit"]:hover {
    background: #218838;
}

/* Customize error messages */
.field-error {
    color: #e74c3c;
    font-weight: bold;
}

/* Change style of inputs with errors */
.form-group input.error {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}
```

---

## JavaScript Customization

### Custom Events

You can listen to form events:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.dynamic-crud-form');
    
    // Before submit
    form.addEventListener('submit', (e) => {
        console.log('Form submitting...');
    });
    
    // When a field changes
    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('change', (e) => {
            console.log(`Field ${e.target.name} changed to: ${e.target.value}`);
        });
    });
});
```

---

## File Uploads

### Basic Configuration

```php
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$cache = new FileCacheStrategy();

// Specify custom uploads directory
$crud = new DynamicCRUD($pdo, 'products', $cache, __DIR__ . '/my-uploads');
```

### File Metadata

```sql
ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) 
COMMENT '{
  "type": "file",
  "accept": "image/*",
  "allowed_mimes": ["image/jpeg", "image/png"],
  "max_size": 2097152
}';
```

---

## Advanced Examples

### Custom Error Handling

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if (!$result['success']) {
        error_log('Form error: ' . json_encode($result));
        $_SESSION['flash_error'] = $result['error'];
        header('Location: form.php?errors=' . urlencode(json_encode($result['errors'])));
        exit;
    }
}
```

---

## Best Practices

1. **Always use cache** in production to avoid repeated queries to `INFORMATION_SCHEMA`
2. **Clear cache** after schema changes
3. **Validate on client AND server** - never trust JavaScript alone
4. **Use HTTPS** when handling file uploads
5. **Limit file sizes** both in PHP (`upload_max_filesize`) and metadata
6. **Sanitize filenames** - the system does this automatically with `uniqid()`
7. **Implement CSRF** - the system includes it by default, don't disable it

---

**Last updated**: 2025-01-31  
**Version**: Phase 3
