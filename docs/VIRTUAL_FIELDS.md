# Virtual Fields - DynamicCRUD

## 📖 Overview

**Virtual Fields** are form fields that appear in the UI but are **not stored in the database**. They are useful for validation, user interaction, and data processing without requiring database columns.

---

## 🎯 Use Cases

### Common Scenarios

1. **Password Confirmation** - Validate that user typed password correctly
2. **Terms Acceptance** - Require checkbox for terms and conditions
3. **Captcha Validation** - Anti-spam verification
4. **Calculated Fields** - Fields processed but not stored
5. **Temporary Data** - Data needed for processing only

---

## 🚀 Quick Start

### Basic Example: Password Confirmation

```php
use Morpheus\DynamicCRUD;
use Morpheus\VirtualField;

$crud = new Morpheus($pdo, 'users');

// Add password_confirmation virtual field
$passwordConfirmation = new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: function($value, $allData) {
        return isset($allData['password']) && $value === $allData['password'];
    },
    attributes: [
        'placeholder' => 'Repeat your password',
        'minlength' => 8,
        'tooltip' => 'Must match the password above',
        'error_message' => 'Passwords do not match'
    ]
);

$crud->addVirtualField($passwordConfirmation);

// Hash password before saving
$crud->beforeSave(function($data) {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $data;
});
```

---

## 📝 VirtualField Constructor

### Signature

```php
public function __construct(
    string $name,              // Field name (required)
    string $type = 'text',     // Input type (default: 'text')
    string $label = '',        // Field label (default: auto-generated)
    bool $required = false,    // Is required? (default: false)
    ?callable $validator = null, // Custom validator (default: null)
    array $attributes = []     // Additional attributes (default: [])
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | ✅ Yes | Field name (used in form and validation) |
| `type` | string | No | Input type: text, password, email, checkbox, etc. |
| `label` | string | No | Field label (auto-generated from name if empty) |
| `required` | bool | No | Whether field is required |
| `validator` | callable | No | Custom validation function |
| `attributes` | array | No | Additional attributes (see below) |

---

## 🔧 Supported Attributes

| Attribute | Type | Description | Example |
|-----------|------|-------------|---------|
| `placeholder` | string | Placeholder text | `'Enter your email'` |
| `tooltip` | string | Help text tooltip | `'Must be at least 8 characters'` |
| `minlength` | int | Minimum length | `8` |
| `maxlength` | int | Maximum length | `50` |
| `pattern` | string | Regex pattern | `'[0-9]{3}-[0-9]{4}'` |
| `error_message` | string | Custom error message | `'Passwords do not match'` |

---

## ✅ Custom Validators

### Validator Function Signature

```php
function(mixed $value, array $allData): bool
```

- **$value**: The value of the virtual field
- **$allData**: All form data (including other fields)
- **Returns**: `true` if valid, `false` if invalid

### Examples

#### Password Match Validator

```php
validator: function($value, $allData) {
    return isset($allData['password']) && $value === $allData['password'];
}
```

#### Checkbox Acceptance Validator

```php
validator: function($value, $allData) {
    return $value === '1';
}
```

#### Email Domain Validator

```php
validator: function($value, $allData) {
    return str_ends_with($value, '@company.com');
}
```

#### Age Verification Validator

```php
validator: function($value, $allData) {
    $birthdate = new DateTime($allData['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;
    return $age >= 18;
}
```

---

## 📚 Complete Examples

### Example 1: User Registration with Password Confirmation

```php
$crud = new Morpheus($pdo, 'users');

// Password confirmation
$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: fn($value, $data) => $value === ($data['password'] ?? ''),
    attributes: [
        'placeholder' => 'Repeat password',
        'minlength' => 8,
        'error_message' => 'Passwords do not match'
    ]
));

// Terms acceptance
$crud->addVirtualField(new VirtualField(
    name: 'terms_accepted',
    type: 'checkbox',
    label: 'I accept the terms and conditions',
    required: true,
    validator: fn($value, $data) => $value === '1',
    attributes: [
        'error_message' => 'You must accept the terms'
    ]
));

// Hash password before saving
$crud->beforeSave(function($data) {
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    return $data;
});
```

### Example 2: Email Confirmation

```php
$crud = new Morpheus($pdo, 'contacts');

$crud->addVirtualField(new VirtualField(
    name: 'email_confirmation',
    type: 'email',
    label: 'Confirm Email',
    required: true,
    validator: function($value, $allData) {
        return isset($allData['email']) && 
               strtolower($value) === strtolower($allData['email']);
    },
    attributes: [
        'placeholder' => 'Repeat your email',
        'tooltip' => 'Must match the email above',
        'error_message' => 'Email addresses do not match'
    ]
));
```

### Example 3: Age Verification Checkbox

```php
$crud = new Morpheus($pdo, 'users');

$crud->addVirtualField(new VirtualField(
    name: 'age_verified',
    type: 'checkbox',
    label: 'I confirm I am 18 years or older',
    required: true,
    validator: fn($value, $data) => $value === '1',
    attributes: [
        'error_message' => 'You must be 18 or older to register'
    ]
));
```

### Example 4: Captcha Validation

```php
$crud = new Morpheus($pdo, 'contacts');

$crud->addVirtualField(new VirtualField(
    name: 'captcha',
    type: 'text',
    label: 'What is 5 + 3?',
    required: true,
    validator: fn($value, $data) => $value === '8',
    attributes: [
        'placeholder' => 'Enter the answer',
        'error_message' => 'Incorrect answer. Please try again.'
    ]
));
```

---

## 🔄 Integration with Hooks

Virtual fields work seamlessly with the hooks system:

```php
$crud = new Morpheus($pdo, 'users');

// Add virtual field
$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: fn($value, $data) => $value === ($data['password'] ?? '')
));

// Hook: Process password before saving
$crud->beforeSave(function($data) {
    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $data;
});

// Hook: Send welcome email after creation
$crud->afterCreate(function($id, $data) {
    mail($data['email'], 'Welcome!', 'Your account has been created.');
});
```

---

## ⚠️ Important Notes

### Virtual Fields Are NOT Saved

Virtual fields are **never stored in the database**. They are only used for:
- Form rendering
- Validation
- Processing logic

### Validation Order

1. **Database fields** validated first (ValidationEngine)
2. **Virtual fields** validated second (VirtualField validators)
3. If any validation fails, transaction is rolled back

### Error Messages

Virtual fields can have custom error messages:

```php
attributes: [
    'error_message' => 'Custom error message here'
]
```

Default error message: `"El campo {label} no es válido"`

---

## 🧪 Testing

Virtual fields are fully tested:

```bash
vendor/bin/phpunit tests/VirtualFieldTest.php
```

**Test coverage**:
- ✅ Field creation
- ✅ Required validation
- ✅ Custom validators
- ✅ Checkbox validation
- ✅ Attributes
- ✅ Error messages
- ✅ Optional fields with validators

---

## 🎨 Styling

Virtual fields use the same CSS classes as regular fields:

```css
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input[type="checkbox"] {
    margin-right: 8px;
}
```

---

## 🔮 Future Enhancements

Planned for future versions:

- [ ] Conditional virtual fields (show/hide based on other fields)
- [ ] Async validators (AJAX validation)
- [ ] Virtual field groups
- [ ] File upload virtual fields (temporary uploads)
- [ ] Multi-step form support

---

## 📖 API Reference

### DynamicCRUD Methods

```php
// Add a virtual field
public function addVirtualField(VirtualField $field): self
```

### VirtualField Methods

```php
// Get field name
public function getName(): string

// Get input type
public function getType(): string

// Get field label
public function getLabel(): string

// Check if required
public function isRequired(): bool

// Get attributes
public function getAttributes(): array

// Validate field value
public function validate($value, array $allData): bool

// Get error message
public function getErrorMessage(): string
```

---

## 💡 Best Practices

1. **Use descriptive names**: `password_confirmation` not `pwd_conf`
2. **Provide clear labels**: Help users understand the field purpose
3. **Add tooltips**: Explain validation requirements
4. **Custom error messages**: Be specific about what went wrong
5. **Combine with hooks**: Process data after validation
6. **Test validators**: Ensure validation logic is correct

---

## 🤝 Contributing

Found a bug or have a feature request? Please open an issue on GitHub.

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
