# Relaciones Muchos a Muchos - DynamicCRUD

Las relaciones muchos a muchos (M:N) permiten que un registro de una tabla esté relacionado con múltiples registros de otra tabla, y viceversa.

## Estructura de Base de Datos

Una relación M:N requiere tres tablas:

1. **Tabla principal** (ej: `posts`)
2. **Tabla relacionada** (ej: `tags`)
3. **Tabla pivote** (ej: `posts_tags`)

### Ejemplo: Posts y Tags

```sql
-- Tabla principal
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT
);

-- Tabla relacionada
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla pivote (relación M:N)
CREATE TABLE posts_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

## Uso Básico

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Definir relación M:N
$crud->addManyToMany(
    'tags',           // Nombre del campo en el formulario
    'posts_tags',     // Tabla pivote
    'post_id',        // Clave local (posts.id)
    'tag_id',         // Clave foránea (tags.id)
    'tags'            // Tabla relacionada
);

$crud->handleSubmission();
```

## Renderizado del Formulario

El sistema genera automáticamente un `<select multiple>` para la relación:

```html
<div class="form-group">
  <label for="tags">Tags</label>
  <select name="tags[]" id="tags" multiple size="5">
    <option value="1">PHP</option>
    <option value="2" selected>JavaScript</option>
    <option value="3" selected>MySQL</option>
  </select>
  <small>Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples</small>
</div>
```

## Sincronización Automática

Cuando se guarda el formulario, el sistema:

1. Elimina todas las relaciones existentes del registro
2. Inserta las nuevas relaciones seleccionadas
3. Todo dentro de una transacción (rollback si hay error)

```php
// Internamente, el sistema ejecuta:
DELETE FROM posts_tags WHERE post_id = ?;
INSERT INTO posts_tags (post_id, tag_id) VALUES (?, ?);
// ... para cada tag seleccionado
```

## Múltiples Relaciones M:N

Puedes definir múltiples relaciones M:N en la misma tabla:

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Relación con tags
$crud->addManyToMany('tags', 'posts_tags', 'post_id', 'tag_id', 'tags');

// Relación con categorías
$crud->addManyToMany('categories', 'posts_categories', 'post_id', 'category_id', 'categories');

// Relación con autores
$crud->addManyToMany('authors', 'posts_authors', 'post_id', 'author_id', 'users');
```

## Consultar Relaciones M:N

Para mostrar las relaciones en una lista:

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

## Personalización del Select

### Cambiar la columna de visualización

Por defecto, el sistema busca una columna `name` o `title`. Si tu tabla usa otra columna:

```php
// Opción 1: Renombrar la columna en la consulta (recomendado)
// El sistema busca automáticamente 'name' o 'title'

// Opción 2: Modificar FormGenerator (avanzado)
// Ver docs/CUSTOMIZATION.md
```

### Estilizar el select

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

## Validación

### Validar que al menos un tag esté seleccionado

```php
$crud->afterValidate(function($data) {
    if (empty($_POST['tags'])) {
        throw new \Exception('Debes seleccionar al menos un tag');
    }
    return $data;
});
```

### Limitar número de tags

```php
$crud->afterValidate(function($data) {
    if (isset($_POST['tags']) && count($_POST['tags']) > 5) {
        throw new \Exception('Máximo 5 tags permitidos');
    }
    return $data;
});
```

## Hooks con Relaciones M:N

Los hooks se ejecutan ANTES de sincronizar las relaciones M:N:

```php
$crud->afterSave(function($id, $data) {
    // Las relaciones M:N ya están sincronizadas aquí
    $tags = $_POST['tags'] ?? [];
    error_log("Post $id guardado con " . count($tags) . " tags");
});
```

## Casos de Uso Comunes

### 1. Blog: Posts con Tags

```php
$crud = new DynamicCRUD($pdo, 'posts');
$crud->addManyToMany('tags', 'posts_tags', 'post_id', 'tag_id', 'tags');
```

### 2. E-commerce: Productos con Categorías

```php
$crud = new DynamicCRUD($pdo, 'products');
$crud->addManyToMany('categories', 'product_categories', 'product_id', 'category_id', 'categories');
```

### 3. Gestión de Proyectos: Tareas con Usuarios

```php
$crud = new DynamicCRUD($pdo, 'tasks');
$crud->addManyToMany('assigned_users', 'task_users', 'task_id', 'user_id', 'users');
```

### 4. Educación: Estudiantes con Cursos

```php
$crud = new DynamicCRUD($pdo, 'students');
$crud->addManyToMany('courses', 'student_courses', 'student_id', 'course_id', 'courses');
```

## Limitaciones

1. **Columna de visualización**: Debe llamarse `name` o `title`
2. **Clave primaria**: Debe llamarse `id` en ambas tablas
3. **UI básica**: Solo `<select multiple>`, no hay checkboxes o búsqueda avanzada
4. **Sin metadatos adicionales**: No se pueden guardar datos extra en la tabla pivote (ej: fecha de asignación)

## Mejoras Futuras (Fase 5)

- [ ] Soporte para checkboxes en lugar de select multiple
- [ ] Búsqueda/filtrado de opciones
- [ ] Soporte para columnas personalizadas en tabla pivote
- [ ] Drag & drop para ordenar relaciones
- [ ] Límite de selecciones configurable en UI

## Ejemplo Completo

Ver `examples/many_to_many_demo.php` para un ejemplo funcional completo.
