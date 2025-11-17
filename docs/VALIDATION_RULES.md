# Validation Rules Guide

**Version:** 2.2.0  
**Feature:** Advanced validation and business rules via table metadata

---

## 📋 Overview

Morpheus v2.2 introduces **advanced validation rules** configured entirely through table metadata. Define complex validation logic without writing PHP code.

### Key Features

- ✅ **unique_together** - Composite unique constraints
- ✅ **required_if** - Conditional required fields
- ✅ **conditional** - Dynamic min/max validation
- ✅ **max_records_per_user** - Record limits per user
- ✅ **require_approval** - Approval workflows

---

## 🎯 Validation Rules

### 1. Unique Together

Ensures a combination of fields is unique across the table.

```json
{
  "validation_rules": {
    "unique_together": [
      ["email", "company_id"],
      ["sku", "category"]
    ]
  }
}
```

**Use Cases:**
- Prevent duplicate emails within the same company
- Unique SKUs per category
- Composite primary keys

**Example:**
```sql
ALTER TABLE vr_products COMMENT = '{
  "validation_rules": {
    "unique_together": [
      ["sku", "category"]
    ]
  }
}';
```

**Error Message:** `"La combinación de sku, category ya existe"`

---

### 2. Required If

Makes a field required based on another field's value.

```json
{
  "validation_rules": {
    "required_if": {
      "phone": {"status": "active"},
      "address": {"type": "physical"},
      "vat_number": {"country": "ES"}
    }
  }
}
```

**Use Cases:**
- Phone required for active users
- Address required for physical products
- VAT number required for EU countries

**Example:**
```sql
ALTER TABLE users COMMENT = '{
  "validation_rules": {
    "required_if": {
      "phone": {"status": "active"},
      "company_name": {"type": "business"}
    }
  }
}';
```

**Error Message:** `"El campo phone es obligatorio"`

---

### 3. Conditional Validation

Applies min/max constraints conditionally.

```json
{
  "validation_rules": {
    "conditional": {
      "discount": {
        "condition": "price > 100",
        "max": 50
      },
      "shipping_cost": {
        "condition": "weight > 10",
        "min": 5
      }
    }
  }
}
```

**Use Cases:**
- Maximum discount for expensive products
- Minimum shipping cost for heavy items
- Dynamic validation based on other fields

**Example:**
```sql
ALTER TABLE vr_products COMMENT = '{
  "validation_rules": {
    "conditional": {
      "discount": {
        "condition": "price > 100",
        "max": 50
      }
    }
  }
}';
```

**Supported Operators:** `>`, `<`, `>=`, `<=`, `==`, `!=`, `+`, `-`, `*`, `/`

**Error Messages:**
- `"El campo discount debe ser al menos {min}"`
- `"El campo discount no puede ser mayor que {max}"`

---

## 💼 Business Rules

### 1. Max Records Per User

Limits the number of records a user can create.

```json
{
  "business_rules": {
    "max_records_per_user": 100,
    "owner_field": "user_id"
  }
}
```

**Use Cases:**
- Free plan limits (5 projects, 10 posts)
- Prevent spam/abuse
- Quota management

**Example:**
```sql
ALTER TABLE subscriptions COMMENT = '{
  "business_rules": {
    "max_records_per_user": 5,
    "owner_field": "user_id"
  }
}';
```

**Error Message:** `"Has alcanzado el límite de 5 registros"`

---

### 2. Require Approval

Implements approval workflows.

```json
{
  "business_rules": {
    "require_approval": true,
    "approval_field": "approved_at",
    "approval_roles": ["admin", "supervisor"]
  }
}
```

**Use Cases:**
- Content moderation
- Financial transactions
- User registrations

**Example:**
```sql
ALTER TABLE posts COMMENT = '{
  "business_rules": {
    "require_approval": true,
    "approval_field": "approved_at",
    "approval_roles": ["admin", "editor"]
  }
}';
```

**Behavior:**
- `approved_at` is set to `NULL` on creation
- Only specified roles can approve
- Approved records have timestamp in `approved_at`

---

## 🔧 Complete Configuration Example

```sql
CREATE TABLE vr_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT DEFAULT 10,
    status ENUM('draft', 'active', 'inactive') DEFAULT 'draft',
    user_id INT NOT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Products",
    "icon": "📦",
    "validation_rules": {
        "unique_together": [
            ["sku", "category"]
        ],
        "required_if": {
            "min_stock": {"status": "active"}
        },
        "conditional": {
            "discount": {
                "condition": "price > 100",
                "max": 50
            }
        }
    },
    "business_rules": {
        "max_records_per_user": 100,
        "owner_field": "user_id",
        "require_approval": true,
        "approval_field": "approved_at",
        "approval_roles": ["admin", "manager"]
    }
}';
```

---

## 💻 PHP Usage

### Basic Usage

```php
use Morpheus\DynamicCRUD;

$crud = new Morpheus($pdo, 'vr_products');

// Validation rules are applied automatically
$result = $crud->handleSubmission();

if (!$result['success']) {
    // Display errors
    foreach ($result['errors'] as $field => $error) {
        echo "$field: $error\n";
    }
}
```

### With Authentication

```php
$crud = new Morpheus($pdo, 'vr_products');
$crud->enableAuthentication();

// Business rules use current user ID automatically
$result = $crud->handleSubmission();
```

### Manual Validation

```php
use Morpheus\ValidationRulesEngine;
use Morpheus\Metadata\TableMetadata;

$metadata = new TableMetadata($pdo, 'vr_products');
$rules = $metadata->getAllRules();

$engine = new ValidationRulesEngine($pdo, 'vr_products', $rules);

// Validate data
$errors = $engine->validate($data, $id);

// Validate business rules
$businessErrors = $engine->validateBusinessRules($data, $userId);
```

---

## 🧪 Testing

### Test Unique Together

```php
// Create first record
$data1 = ['sku' => 'SKU-001', 'category' => 'electronics'];
$result1 = $crud->handleSubmission(); // ✅ Success

// Try duplicate
$data2 = ['sku' => 'SKU-001', 'category' => 'electronics'];
$result2 = $crud->handleSubmission(); // ❌ Error

// Different category
$data3 = ['sku' => 'SKU-001', 'category' => 'furniture'];
$result3 = $crud->handleSubmission(); // ✅ Success
```

### Test Required If

```php
// Draft status, no min_stock
$data1 = ['status' => 'draft'];
$result1 = $crud->handleSubmission(); // ✅ Success

// Active status, no min_stock
$data2 = ['status' => 'active'];
$result2 = $crud->handleSubmission(); // ❌ Error

// Active status, with min_stock
$data3 = ['status' => 'active', 'min_stock' => 10];
$result3 = $crud->handleSubmission(); // ✅ Success
```

### Test Conditional

```php
// Low price, high discount
$data1 = ['price' => 50, 'discount' => 60];
$result1 = $crud->handleSubmission(); // ✅ Success (condition not met)

// High price, high discount
$data2 = ['price' => 150, 'discount' => 60];
$result2 = $crud->handleSubmission(); // ❌ Error (exceeds max)

// High price, valid discount
$data3 = ['price' => 150, 'discount' => 40];
$result3 = $crud->handleSubmission(); // ✅ Success
```

---

## 🎨 Error Handling

### Error Response Format

```php
[
    'success' => false,
    'errors' => [
        'sku' => 'La combinación de sku, category ya existe',
        'min_stock' => 'El campo min_stock es obligatorio',
        'discount' => 'El campo discount no puede ser mayor que 50',
        '_global' => 'Has alcanzado el límite de 100 registros'
    ]
]
```

### Display Errors in Form

```php
<?php if (isset($result) && !$result['success']): ?>
    <div class="alert alert-danger">
        <?php if (isset($result['error'])): ?>
            <?= htmlspecialchars($result['error']) ?>
        <?php endif; ?>
        
        <?php if (isset($result['errors'])): ?>
            <ul>
                <?php foreach ($result['errors'] as $field => $error): ?>
                    <li>
                        <strong><?= htmlspecialchars($field) ?>:</strong>
                        <?= htmlspecialchars($error) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>
```

---

## 🌍 Internationalization

Error messages support i18n:

```php
$crud = new Morpheus($pdo, 'vr_products', locale: 'es');
$result = $crud->handleSubmission();

// Errors in Spanish
// "El campo min_stock es obligatorio"
```

Add custom translations in `src/I18n/locales/`:

```json
{
  "validation": {
    "unique_together": "La combinación de {fields} ya existe",
    "required_if": "El campo {field} es obligatorio",
    "conditional_max": "El campo {field} no puede ser mayor que {max}",
    "max_records": "Has alcanzado el límite de {max} registros"
  }
}
```

---

## 🔗 Integration with Other Features

### With RBAC

```php
$crud = new Morpheus($pdo, 'vr_products');
$crud->enableAuthentication();

// Business rules use current user automatically
// Permissions checked before validation
$result = $crud->handleSubmission();
```

### With Hooks

```php
$crud->beforeValidate(function($data) {
    // Modify data before validation
    return $data;
});

$crud->afterValidate(function($data) {
    // Additional custom validation
    return $data;
});
```

### With Audit Logging

```php
$crud->enableAudit($userId);

// Validation errors are logged
$result = $crud->handleSubmission();
```

---

## 📊 Performance

- **Validation order:** Basic → Virtual Fields → Advanced Rules → Business Rules
- **Database queries:** 1-2 queries per rule (cached within transaction)
- **Transaction safety:** All validations run inside transaction
- **Rollback:** Automatic rollback on any validation failure

---

## 🚀 Best Practices

1. **Order matters:** Place most common validations first
2. **Combine rules:** Use multiple rules together for complex logic
3. **Clear messages:** Provide helpful error messages
4. **Test thoroughly:** Test all edge cases
5. **Use transactions:** Always enabled by default

---

## 📚 Examples

See working examples in `examples/10-validation-rules/`:

- `unique-together.php` - Composite unique constraints
- `required-if.php` - Conditional required fields
- `conditional.php` - Dynamic min/max validation
- `business-rules.php` - Record limits and approval

---

## 🔮 Future Enhancements

Planned for v2.3:

- [ ] Custom validation functions
- [ ] Regex patterns in conditional
- [ ] Cross-table validation
- [ ] Async validation
- [ ] Client-side validation generation

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
