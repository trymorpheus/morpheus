# Refactoring Patterns

This document describes the refactoring patterns applied in v3.4.0 and recommended for future development.

## Overview

DynamicCRUD v3.4.0 introduced significant code refactoring in FormGenerator and ListGenerator classes, focusing on:
- **Components Integration** - Using UI Components library for consistent design
- **Method Extraction** - Breaking down large methods into smaller, focused ones
- **Code Reduction** - Eliminating duplication and simplifying logic
- **Better Organization** - Clear separation of concerns

## Pattern 1: Components Integration

### Before (v3.3)
```php
// Inline HTML generation
$html = '<button type="submit">Guardar</button>';
$html .= '<table class="list-table">';
// ... more inline HTML
```

### After (v3.4)
```php
use DynamicCRUD\UI\Components;

// Use Components library
$html = Components::button('Guardar', 'primary', ['type' => 'submit']);
$html = Components::table($headers, $rows, ['striped' => true]);
```

**Benefits:**
- Consistent styling across the application
- Easier to maintain and update
- Automatic XSS protection
- Responsive design built-in

## Pattern 2: Method Extraction

### Before (v3.3)
```php
public function render(): string
{
    $html = '';
    
    // Theme rendering
    if ($this->themeManager) {
        $html .= $this->themeManager->renderCSSVariables();
        $html .= $this->themeManager->renderBranding();
    }
    
    // Styles
    $html .= $this->renderStyles();
    
    // Form opening
    $enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
    $html .= '<form method="POST"' . $enctype . '>';
    $html .= $this->renderCsrfField();
    
    // ... 50+ more lines
    
    return $html;
}
```

### After (v3.4)
```php
public function render(): string
{
    $html = $this->renderTheme();
    $html .= $this->renderStyles();
    $html .= $this->renderAssets();
    $html .= $this->renderFormOpen();
    $html .= $this->renderFormFields();
    $html .= $this->renderSubmitButton();
    $html .= '</form>';
    $html .= $this->renderWorkflowButtons();
    
    return $html;
}

private function renderTheme(): string { /* ... */ }
private function renderFormOpen(): string { /* ... */ }
private function renderFormFields(): string { /* ... */ }
private function renderSubmitButton(): string { /* ... */ }
```

**Benefits:**
- Main method is now 15 lines instead of 70
- Each method has a single responsibility
- Easier to test individual components
- Better code readability

## Pattern 3: CSS Variables for Theming

### Before (v3.3)
```php
$css = 'button { background: #667eea; }';
```

### After (v3.4)
```php
$css = 'button { background: var(--primary-color, #667eea); }';
```

**Benefits:**
- Dynamic theming support
- Easy color customization
- Consistent with Components library
- Fallback values for compatibility

## Pattern 4: Eliminating Duplication

### Before (v3.3)
```php
public function render(): string
{
    // ... 70 lines of code
}

private function renderTabbedForm(): string
{
    // ... 60 lines of duplicated code
}
```

### After (v3.4)
```php
public function render(): string
{
    if ($this->tableMetadata && $this->tableMetadata->getFormLayout() === 'tabs') {
        return $html . $this->renderTabbedForm();
    }
    
    $html .= $this->renderFormOpen();
    $html .= $this->renderFormFields();
    // ...
}

private function renderTabbedForm(): string
{
    // Reuses renderFormOpen(), renderFormFields(), etc.
    $html = $this->renderFormOpen();
    $html .= Components::tabs($tabsData, $tabsContent);
    // ...
}
```

**Benefits:**
- No code duplication
- Single source of truth
- Easier to maintain
- Consistent behavior

## Pattern 5: Simplified Conditionals

### Before (v3.3)
```php
if ($this->tableMetadata?->hasCardView()) {
    $html .= $this->renderCards($data['records']);
} else {
    $html .= $this->renderTable($data['records']);
}
```

### After (v3.4)
```php
private function renderContent(array $records): string
{
    if ($this->tableMetadata?->hasCardView()) {
        return $this->renderCards($records);
    }
    return $this->renderTableWithComponents($records);
}
```

**Benefits:**
- Cleaner code
- Early returns reduce nesting
- Easier to understand logic

## Pattern 6: Array Transformations

### Before (v3.3)
```php
$headers = [];
foreach ($columns as $col) {
    $headers[] = ucfirst(str_replace('_', ' ', $col));
}
```

### After (v3.4)
```php
$headers = array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $columns);
```

**Benefits:**
- More concise
- Functional programming style
- Easier to read

## Pattern 7: Guard Clauses and Early Returns

### Before (v3.4)
```php
private function validateField(array $column, $value): void
{
    $name = $column['name'];
    
    if ($column['metadata']['hidden'] ?? false) {
        return;
    }
    
    if (!$column['is_nullable'] && ($value === null || $value === '')) {
        $this->errors[$name][] = "Required";
        return;
    }
    
    if ($value === null || $value === '') {
        return;
    }
    
    // ... validation logic
}
```

### After (v3.5)
```php
private function validateField(array $column, $value): void
{
    if ($this->isHiddenField($column)) {
        return;
    }
    
    if ($this->isRequiredAndEmpty($column, $value)) {
        $this->addRequiredError($column['name']);
        return;
    }
    
    if ($this->isEmpty($value)) {
        return;
    }
    
    $this->validateType($column, $value);
    $this->validateLength($column, $value);
    $this->validateMetadata($column, $value);
}

private function isHiddenField(array $column): bool
{
    return $column['metadata']['hidden'] ?? false;
}

private function isRequiredAndEmpty(array $column, $value): bool
{
    return !$column['is_nullable'] && $this->isEmpty($value);
}

private function isEmpty($value): bool
{
    return $value === null || $value === '';
}
```

**Benefits:**
- Self-documenting code
- Reusable validation logic
- Easier to test
- Reduced cognitive load

## Pattern 8: Extract Type-Specific Validators

### Before (v3.4)
```php
private function validateType(array $column, $value): void
{
    $name = $column['name'];
    
    switch ($column['sql_type']) {
        case 'int':
        case 'bigint':
            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                $this->errors[$name][] = "Must be integer";
            }
            break;
        case 'decimal':
        case 'float':
            if (!is_numeric($value)) {
                $this->errors[$name][] = "Must be number";
            }
            break;
        // ... more cases
    }
}
```

### After (v3.5)
```php
private function validateType(array $column, $value): void
{
    $sqlType = $column['sql_type'];
    $fieldName = $column['name'];
    
    if (in_array($sqlType, ['int', 'bigint', 'smallint', 'tinyint'])) {
        $this->validateInteger($fieldName, $value);
    } elseif (in_array($sqlType, ['decimal', 'float', 'double'])) {
        $this->validateNumeric($fieldName, $value);
    } elseif (in_array($sqlType, ['date', 'datetime', 'timestamp'])) {
        $this->validateDate($fieldName, $value);
    }
}

private function validateInteger(string $fieldName, $value): void
{
    if (!filter_var($value, FILTER_VALIDATE_INT)) {
        $this->addError($fieldName, 'validation.number', "Must be integer");
    }
}

private function validateNumeric(string $fieldName, $value): void
{
    if (!is_numeric($value)) {
        $this->addError($fieldName, 'validation.number', "Must be number");
    }
}
```

**Benefits:**
- Each validator has single responsibility
- Easy to add new types
- Testable in isolation
- Consistent error handling

## Pattern 9: Nullsafe Operator for Cache

### Before (v3.4)
```php
public function getTableSchema(string $table): array
{
    $cacheKey = "schema_{$table}";
    
    if ($this->cache) {
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    $schema = $this->adapter->getTableSchema($table);
    
    if ($this->cache) {
        $this->cache->set($cacheKey, $schema, $this->cacheTtl);
    }
    
    return $schema;
}
```

### After (v3.5)
```php
public function getTableSchema(string $table): array
{
    $cacheKey = $this->getCacheKey($table);
    
    $cached = $this->getCachedSchema($cacheKey);
    if ($cached !== null) {
        return $cached;
    }
    
    $schema = $this->adapter->getTableSchema($table);
    $this->cacheSchema($cacheKey, $schema);
    
    return $schema;
}

private function getCacheKey(string $table): string
{
    return "schema_{$table}";
}

private function getCachedSchema(string $cacheKey): ?array
{
    return $this->cache?->get($cacheKey);
}

private function cacheSchema(string $cacheKey, array $schema): void
{
    $this->cache?->set($cacheKey, $schema, $this->cacheTtl);
}
```

**Benefits:**
- Nullsafe operator (?->) eliminates if checks
- Extracted methods clarify intent
- Single source of truth for cache key
- Easier to modify cache strategy

## Pattern 10: Inline Styles for Components

### Before (v3.3)
```php
$html = '<a href="?id=' . $id . '" class="action-edit">Editar</a>';
// Requires CSS class definition elsewhere
```

### After (v3.4)
```php
$html = sprintf(
    '<a href="?id=%s" style="color: #667eea; text-decoration: none;">Editar</a>',
    $id
);
```

**Benefits:**
- Self-contained styling
- No external CSS dependencies
- Easier to customize per instance

## Applying These Patterns

### When to Extract Methods

Extract a method when:
1. A block of code has a clear, single purpose
2. The code is repeated in multiple places
3. The method is getting too long (>50 lines)
4. You need to test a specific piece of logic

### When to Use Components

Use Components library when:
1. Rendering UI elements (buttons, tables, alerts)
2. You need consistent styling
3. You want responsive design
4. You need accessibility features

### When to Use CSS Variables

Use CSS variables when:
1. Colors might change based on theme
2. You want to support customization
3. You need fallback values
4. You're integrating with ThemeManager

## Code Review Checklist

Before committing refactored code:

- [ ] All tests pass
- [ ] No breaking changes
- [ ] Methods have single responsibility
- [ ] Code duplication eliminated
- [ ] Components used where appropriate
- [ ] CSS variables for themeable colors
- [ ] Inline documentation updated
- [ ] Performance not degraded

## Examples

See these files for refactoring examples:
- `src/FormGenerator.php` - Complete refactoring with Components
- `src/ListGenerator.php` - Table and pagination with Components
- `src/Admin/AdminPanel.php` - Components integration

## Completed Refactorings

### v3.5.0 - Core Classes
1. **CRUDHandler.php** ✅ - Extracted 16 methods from handleSubmission() (~250 to ~30 lines)
2. **ValidationEngine.php** ✅ - Extracted 13 validation methods (~170 to ~220 lines with better organization)
3. **SchemaAnalyzer.php** ✅ - Improved cache management with 3 extracted methods

### v3.4.0 - UI Classes
1. **FormGenerator.php** ✅ - Extracted 8 methods, Components integration (~70 to ~15 lines)
2. **ListGenerator.php** ✅ - Extracted 5 methods, Components integration (~350 to ~280 lines)
3. **AdminPanel.php** ✅ - Components integration for consistent UI

## Future Refactoring Candidates

Classes that could benefit from similar refactoring:
1. **FileUploadHandler.php** - Simplify upload logic, extract validation
2. **NotificationManager.php** - Extract email/webhook logic
3. **WorkflowEngine.php** - Simplify transition logic
4. **AuditLogger.php** - Extract formatting logic

## Resources

- [Components Documentation](UI_COMPONENTS.md)
- [Best Practices](BEST_PRACTICES.md)
- [Contributing Guidelines](../CONTRIBUTING.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
