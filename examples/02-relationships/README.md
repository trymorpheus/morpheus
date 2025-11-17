# 02. Relationships

Master database relationships with DynamicCRUD.

## Examples

### Foreign Keys (`foreign-keys.php`)
Automatic dropdown generation for foreign key relationships.

**Zero configuration needed!** DynamicCRUD reads your database constraints.

```php
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderForm(); // category_id becomes a dropdown automatically!
```

### Many-to-Many (`many-to-many.php`)
Handle M:N relationships with multi-select UI.

```php
$crud = new Morpheus($pdo, 'posts');

$crud->addManyToMany(
    'tags',        // Field name
    'post_tags',   // Pivot table
    'post_id',     // Local key
    'tag_id',      // Foreign key
    'tags'         // Related table
);

$crud->handleSubmission(); // Automatically syncs pivot table!
```

## Database Schema

```sql
-- Foreign Key Example
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Many-to-Many Example
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE post_tags (
    post_id INT,
    tag_id INT,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

## Features

✅ **Automatic FK Detection** - Reads database constraints  
✅ **Custom Display Columns** - Show any field in dropdowns  
✅ **Nullable Support** - Empty option for optional FKs  
✅ **M:N Sync** - Automatic pivot table management  
✅ **Transaction Safety** - Rollback on errors  
✅ **Advanced UI** - Checkboxes with search for M:N  

## Next Steps

- [Customization Examples](../03-customization/) - Metadata and file uploads
- [Advanced Features](../04-advanced/) - Hooks and virtual fields
