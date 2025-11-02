# Many-to-Many Relationships - DynamicCRUD

Many-to-many (M:N) relationships allow a record from one table to be related to multiple records from another table, and vice versa.

## Database Structure

An M:N relationship requires three tables:

1. **Main table** (e.g., `posts`)
2. **Related table** (e.g., `tags`)
3. **Pivot table** (e.g., `posts_tags`)

### Example: Posts and Tags

```sql
-- Main table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT
);

-- Related table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Pivot table (M:N relationship)
CREATE TABLE posts_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

## Basic Usage

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Define M:N relationship
$crud->addManyToMany(
    'tags',           // Field name in form
    'posts_tags',     // Pivot table
    'post_id',        // Local key (posts.id)
    'tag_id',         // Foreign key (tags.id)
    'tags'            // Related table
);

$crud->handleSubmission();
```

## Form Rendering

The system automatically generates a `<select multiple>` for the relationship:

```html
<div class="form-group">
  <label for="tags">Tags</label>
  <select name="tags[]" id="tags" multiple size="5">
    <option value="1">PHP</option>
    <option value="2" selected>JavaScript</option>
    <option value="3" selected>MySQL</option>
  </select>
  <small>Hold Ctrl (Cmd on Mac) to select multiple</small>
</div>
```

## Automatic Synchronization

When the form is saved, the system:

1. Deletes all existing relationships for the record
2. Inserts the newly selected relationships
3. Everything within a transaction (rollback on error)

```php
// Internally, the system executes:
DELETE FROM posts_tags WHERE post_id = ?;
INSERT INTO posts_tags (post_id, tag_id) VALUES (?, ?);
// ... for each selected tag
```

## Multiple M:N Relationships

You can define multiple M:N relationships on the same table:

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Relationship with tags
$crud->addManyToMany('tags', 'posts_tags', 'post_id', 'tag_id', 'tags');

// Relationship with categories
$crud->addManyToMany('categories', 'posts_categories', 'post_id', 'category_id', 'categories');

// Relationship with authors
$crud->addManyToMany('authors', 'posts_authors', 'post_id', 'author_id', 'users');
```

## Querying M:N Relationships

To display relationships in a list:

```php
$stmt = $pdo->query('
    SELECT p.id, p.title,
           GROUP_CONCAT(t.name SEPARATOR ", ") as tags
    FROM posts p
    LEFT JOIN posts_tags pt ON p.id = pt.post_id
    LEFT JOIN tags t ON pt.tag_id = t.id
    GROUP BY p.id
');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post) {
    echo $post['title'] . ' - Tags: ' . $post['tags'];
}
```

## Select Customization

### Change display column

By default, the system looks for a `name` or `title` column. If your table uses another column:

```php
// Option 1: Rename column in query (recommended)
// The system automatically looks for 'name' or 'title'

// Option 2: Modify FormGenerator (advanced)
// See docs/CUSTOMIZATION.md
```

### Style the select

```css
select[multiple] {
    min-height: 150px;
    padding: 8px;
}

select[multiple] option {
    padding: 5px;
    margin: 2px 0;
}

select[multiple] option:checked {
    background: #007bff;
    color: white;
}
```

## Validation

### Validate at least one tag is selected

```php
$crud->afterValidate(function($data) {
    if (empty($_POST['tags'])) {
        throw new \Exception('You must select at least one tag');
    }
    return $data;
});
```

### Limit number of tags

```php
$crud->afterValidate(function($data) {
    if (isset($_POST['tags']) && count($_POST['tags']) > 5) {
        throw new \Exception('Maximum 5 tags allowed');
    }
    return $data;
});
```

## Hooks with M:N Relationships

Hooks execute BEFORE synchronizing M:N relationships:

```php
$crud->afterSave(function($id, $data) {
    // M:N relationships are already synchronized here
    $tags = $_POST['tags'] ?? [];
    error_log("Post $id saved with " . count($tags) . " tags");
});
```

## Common Use Cases

### 1. Blog: Posts with Tags

```php
$crud = new DynamicCRUD($pdo, 'posts');
$crud->addManyToMany('tags', 'posts_tags', 'post_id', 'tag_id', 'tags');
```

### 2. E-commerce: Products with Categories

```php
$crud = new DynamicCRUD($pdo, 'products');
$crud->addManyToMany('categories', 'product_categories', 'product_id', 'category_id', 'categories');
```

### 3. Project Management: Tasks with Users

```php
$crud = new DynamicCRUD($pdo, 'tasks');
$crud->addManyToMany('assigned_users', 'task_users', 'task_id', 'user_id', 'users');
```

### 4. Education: Students with Courses

```php
$crud = new DynamicCRUD($pdo, 'students');
$crud->addManyToMany('courses', 'student_courses', 'student_id', 'course_id', 'courses');
```

## Limitations

1. **Display column**: Must be named `name` or `title`
2. **Primary key**: Must be named `id` in both tables
3. **Basic UI**: Only `<select multiple>`, no checkboxes or advanced search
4. **No additional metadata**: Cannot save extra data in pivot table (e.g., assignment date)

## Future Improvements (Phase 5)

- [ ] Support for checkboxes instead of select multiple
- [ ] Search/filtering of options
- [ ] Support for custom columns in pivot table
- [ ] Drag & drop to order relationships
- [ ] Configurable selection limit in UI

## Complete Example

See `examples/many_to_many_demo.php` for a complete working example.

---

**Last updated**: 2025-01-31  
**Version**: Phase 4
