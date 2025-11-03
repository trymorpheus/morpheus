# Hooks/Events System - DynamicCRUD

The hooks system allows you to execute custom code at key points in the CRUD lifecycle.

## Available Hooks

### Validation Hooks
- **beforeValidate**: Before validating data
- **afterValidate**: After validating data

### Save Hooks
- **beforeSave**: Before saving (CREATE or UPDATE)
- **afterSave**: After saving (CREATE or UPDATE)

### Creation Hooks
- **beforeCreate**: Before creating a new record
- **afterCreate**: After creating a new record

### Update Hooks
- **beforeUpdate**: Before updating an existing record
- **afterUpdate**: After updating an existing record

### Deletion Hooks
- **beforeDelete**: Before deleting a record
- **afterDelete**: After deleting a record

## Basic Usage

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Register a hook
$crud->beforeSave(function($data) {
    // Modify data before saving
    $data['slug'] = slugify($data['title']);
    return $data;
});

$crud->handleSubmission();
```

## Practical Examples

### 1. Auto-Generate Slug

```php
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    }
    return $data;
});
```

### 2. Add Automatic Timestamps

```php
$crud->beforeCreate(function($data) {
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $_SESSION['user_id'] ?? null;
    return $data;
});

$crud->beforeUpdate(function($data, $id) {
    $data['updated_at'] = date('Y-m-d H:i:s');
    $data['updated_by'] = $_SESSION['user_id'] ?? null;
    return $data;
});
```

### 3. Cross-Field Validation

```php
$crud->afterValidate(function($data) {
    // If status is 'published', add publication date
    if ($data['status'] === 'published' && empty($data['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    // Validate that end_date > start_date
    if (isset($data['start_date']) && isset($data['end_date'])) {
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            throw new \Exception('End date must be after start date');
        }
    }
    
    return $data;
});
```

### 4. Send Email Notification

```php
$crud->afterCreate(function($id, $data) {
    // Send welcome email
    mail(
        $data['email'],
        'Welcome',
        "Your account has been created with ID: $id"
    );
});
```

### 5. Logging and Auditing

```php
$crud->afterCreate(function($id, $data) {
    error_log("✓ Record created - ID: $id - User: {$_SESSION['user_id']}");
});

$crud->afterUpdate(function($id, $data) {
    error_log("✓ Record updated - ID: $id - User: {$_SESSION['user_id']}");
});

$crud->beforeDelete(function($id) use ($pdo) {
    // Save to audit table before deleting
    $stmt = $pdo->prepare("INSERT INTO audit_log (action, table_name, record_id, user_id, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute(['DELETE', 'posts', $id, $_SESSION['user_id'] ?? null]);
});
```

### 6. Sync with External Service

```php
$crud->afterSave(function($id, $data) {
    // Sync with external API
    $client = new GuzzleHttp\Client();
    $client->post('https://api.example.com/sync', [
        'json' => [
            'id' => $id,
            'data' => $data
        ]
    ]);
});
```

### 7. Clear Cache

```php
$crud->afterSave(function($id, $data) {
    // Invalidate cache after saving
    $cache = new FileCacheStrategy();
    $cache->invalidate("post_$id");
    $cache->invalidate("posts_list");
});
```

### 8. Process Image

```php
$crud->afterCreate(function($id, $data) {
    if (isset($data['image'])) {
        // Create thumbnail
        $image = imagecreatefromjpeg($data['image']);
        $thumbnail = imagescale($image, 200);
        imagejpeg($thumbnail, "uploads/thumbnails/{$id}.jpg");
    }
});
```

## Multiple Callbacks

You can register multiple callbacks for the same hook:

```php
$crud->beforeSave(function($data) {
    $data['slug'] = slugify($data['title']);
    return $data;
});

$crud->beforeSave(function($data) {
    $data['search_text'] = strip_tags($data['content']);
    return $data;
});

// Both will execute in order
```

## Fluent API

Methods are chainable:

```php
$crud
    ->beforeSave(function($data) { /* ... */ return $data; })
    ->afterCreate(function($id, $data) { /* ... */ })
    ->afterUpdate(function($id, $data) { /* ... */ })
    ->handleSubmission();
```

## Hook Parameters

### beforeValidate, afterValidate, beforeSave, beforeCreate
```php
function($data): array
```
- **$data**: Array with form data
- **Return**: Array with data (possibly modified)

### afterSave, afterCreate, afterUpdate
```php
function($id, $data): void
```
- **$id**: ID of saved record
- **$data**: Array with saved data
- **Return**: No return needed

### beforeUpdate
```php
function($data, $id): array
```
- **$data**: Array with data to update
- **$id**: ID of record to update
- **Return**: Array with data (possibly modified)

### beforeDelete, afterDelete
```php
function($id): void
```
- **$id**: ID of record to delete
- **Return**: No return needed

## Error Handling

Hooks execute within transactions. If a hook throws an exception, automatic rollback occurs:

```php
$crud->beforeSave(function($data) {
    if ($data['price'] < 0) {
        throw new \Exception('Price cannot be negative');
    }
    return $data;
});

// If exception is thrown, nothing is saved to DB
```

## Best Practices

1. **Keep hooks simple**: Each hook should do one thing
2. **Always return data**: In "before" hooks, always return `$data`
3. **Don't modify DB directly**: Use returned data
4. **Handle errors appropriately**: Throw exceptions to cancel operations
5. **Document your hooks**: Comment what each hook does
6. **Avoid heavy hooks**: Slow operations can affect performance

## Limitations

- Hooks don't execute on `list()` operations (read-only)
- Hooks execute in registration order
- No way to "unregister" a hook once registered
- Hooks share the same transaction context
