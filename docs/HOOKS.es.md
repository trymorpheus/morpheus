# Sistema de Hooks/Eventos - DynamicCRUD

El sistema de hooks permite ejecutar código personalizado en puntos clave del ciclo de vida CRUD.

## Hooks Disponibles

### Hooks de Validación
- **beforeValidate**: Antes de validar los datos
- **afterValidate**: Después de validar los datos

### Hooks de Guardado
- **beforeSave**: Antes de guardar (CREATE o UPDATE)
- **afterSave**: Después de guardar (CREATE o UPDATE)

### Hooks de Creación
- **beforeCreate**: Antes de crear un nuevo registro
- **afterCreate**: Después de crear un nuevo registro

### Hooks de Actualización
- **beforeUpdate**: Antes de actualizar un registro existente
- **afterUpdate**: Después de actualizar un registro existente

### Hooks de Eliminación
- **beforeDelete**: Antes de eliminar un registro
- **afterDelete**: Después de eliminar un registro

## Uso Básico

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Registrar un hook
$crud->beforeSave(function($data) {
    // Modificar datos antes de guardar
    $data['slug'] = slugify($data['title']);
    return $data;
});

$crud->handleSubmission();
```

## Ejemplos Prácticos

### 1. Generar Slug Automáticamente

```php
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    }
    return $data;
});
```

### 2. Añadir Timestamps Automáticos

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

### 3. Validación Cruzada

```php
$crud->afterValidate(function($data) {
    // Si status es 'published', añadir fecha de publicación
    if ($data['status'] === 'published' && empty($data['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    // Validar que fecha_fin > fecha_inicio
    if (isset($data['fecha_inicio']) && isset($data['fecha_fin'])) {
        if (strtotime($data['fecha_fin']) < strtotime($data['fecha_inicio'])) {
            throw new \Exception('La fecha de fin debe ser posterior a la fecha de inicio');
        }
    }
    
    return $data;
});
```

### 4. Enviar Email de Notificación

```php
$crud->afterCreate(function($id, $data) {
    // Enviar email de bienvenida
    mail(
        $data['email'],
        'Bienvenido',
        "Tu cuenta ha sido creada con ID: $id"
    );
});
```

### 5. Logging y Auditoría

```php
$crud->afterCreate(function($id, $data) {
    error_log("✓ Registro creado - ID: $id - Usuario: {$_SESSION['user_id']}");
});

$crud->afterUpdate(function($id, $data) {
    error_log("✓ Registro actualizado - ID: $id - Usuario: {$_SESSION['user_id']}");
});

$crud->beforeDelete(function($id) use ($pdo) {
    // Guardar en tabla de auditoría antes de eliminar
    $stmt = $pdo->prepare("INSERT INTO audit_log (action, table_name, record_id, user_id, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute(['DELETE', 'posts', $id, $_SESSION['user_id'] ?? null]);
});
```

### 6. Sincronizar con Servicio Externo

```php
$crud->afterSave(function($id, $data) {
    // Sincronizar con API externa
    $client = new GuzzleHttp\Client();
    $client->post('https://api.example.com/sync', [
        'json' => [
            'id' => $id,
            'data' => $data
        ]
    ]);
});
```

### 7. Limpiar Caché

```php
$crud->afterSave(function($id, $data) {
    // Invalidar caché después de guardar
    $cache = new FileCacheStrategy();
    $cache->invalidate("post_$id");
    $cache->invalidate("posts_list");
});
```

### 8. Procesar Imagen

```php
$crud->afterCreate(function($id, $data) {
    if (isset($data['image'])) {
        // Crear thumbnail
        $image = imagecreatefromjpeg($data['image']);
        $thumbnail = imagescale($image, 200);
        imagejpeg($thumbnail, "uploads/thumbnails/{$id}.jpg");
    }
});
```

## Múltiples Callbacks

Puedes registrar múltiples callbacks para el mismo hook:

```php
$crud->beforeSave(function($data) {
    $data['slug'] = slugify($data['title']);
    return $data;
});

$crud->beforeSave(function($data) {
    $data['search_text'] = strip_tags($data['content']);
    return $data;
});

// Ambos se ejecutarán en orden
```

## API Fluida

Los métodos son encadenables:

```php
$crud
    ->beforeSave(function($data) { /* ... */ return $data; })
    ->afterCreate(function($id, $data) { /* ... */ })
    ->afterUpdate(function($id, $data) { /* ... */ })
    ->handleSubmission();
```

## Parámetros de los Hooks

### beforeValidate, afterValidate, beforeSave, beforeCreate
```php
function($data): array
```
- **$data**: Array con los datos del formulario
- **Return**: Array con los datos (posiblemente modificados)

### afterSave, afterCreate, afterUpdate
```php
function($id, $data): void
```
- **$id**: ID del registro guardado
- **$data**: Array con los datos guardados
- **Return**: No es necesario retornar nada

### beforeUpdate
```php
function($data, $id): array
```
- **$data**: Array con los datos a actualizar
- **$id**: ID del registro a actualizar
- **Return**: Array con los datos (posiblemente modificados)

### beforeDelete, afterDelete
```php
function($id): void
```
- **$id**: ID del registro a eliminar
- **Return**: No es necesario retornar nada

## Manejo de Errores

Los hooks se ejecutan dentro de transacciones. Si un hook lanza una excepción, se hace rollback automático:

```php
$crud->beforeSave(function($data) {
    if ($data['price'] < 0) {
        throw new \Exception('El precio no puede ser negativo');
    }
    return $data;
});

// Si se lanza la excepción, no se guarda nada en la BD
```

## Mejores Prácticas

1. **Mantén los hooks simples**: Cada hook debe hacer una sola cosa
2. **Retorna siempre los datos**: En hooks "before", siempre retorna `$data`
3. **No modifiques la BD directamente**: Usa los datos retornados
4. **Maneja errores apropiadamente**: Lanza excepciones para cancelar operaciones
5. **Documenta tus hooks**: Comenta qué hace cada hook
6. **Evita hooks pesados**: Operaciones lentas pueden afectar el rendimiento

## Limitaciones

- Los hooks no se ejecutan en operaciones `list()` (solo lectura)
- Los hooks se ejecutan en orden de registro
- No hay forma de "cancelar" un hook una vez registrado
- Los hooks comparten el mismo contexto de transacción

---

**Última actualización**: 2025-01-31  
**Versión**: Fase 4
