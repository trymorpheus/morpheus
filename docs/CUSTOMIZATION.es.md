# Guía de Personalización - DynamicCRUD

Esta guía explica cómo personalizar y extender DynamicCRUD para adaptarlo a tus necesidades específicas.

## Tabla de Contenidos

1. [Metadatos JSON](#metadatos-json)
2. [Personalización de Validación](#personalización-de-validación)
3. [Personalización de Estilos](#personalización-de-estilos)
4. [Personalización de JavaScript](#personalización-de-javascript)
5. [Subida de Archivos](#subida-de-archivos)
6. [Ejemplos Avanzados](#ejemplos-avanzados)

---

## Metadatos JSON

Los metadatos se definen en los comentarios de las columnas de la base de datos usando formato JSON.

### Propiedades Disponibles

```json
{
  "type": "email|url|file|number|text",
  "label": "Etiqueta visible del campo",
  "tooltip": "Texto de ayuda que aparece al pasar el cursor",
  "min": 0,
  "max": 100,
  "minlength": 3,
  "hidden": true,
  "display_column": "name",
  "accept": "image/*",
  "allowed_mimes": ["image/jpeg", "image/png"],
  "max_size": 2097152
}
```

### Ejemplos por Tipo de Campo

#### Campo de Email
```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Correo Electrónico", "tooltip": "Usaremos este email para contactarte"}';
```

#### Campo de URL
```sql
ALTER TABLE websites 
MODIFY COLUMN url VARCHAR(255) 
COMMENT '{"type": "url", "label": "Sitio Web", "tooltip": "Debe comenzar con http:// o https://"}';
```

#### Campo Numérico con Rango
```sql
ALTER TABLE products 
MODIFY COLUMN price DECIMAL(10,2) 
COMMENT '{"type": "number", "min": 0.01, "max": 9999.99, "label": "Precio"}';
```

#### Campo de Texto con Longitud Mínima
```sql
ALTER TABLE posts 
MODIFY COLUMN title VARCHAR(200) 
COMMENT '{"label": "Título", "minlength": 5, "tooltip": "Mínimo 5 caracteres"}';
```

#### Campo Oculto
```sql
ALTER TABLE users 
MODIFY COLUMN created_at TIMESTAMP 
COMMENT '{"hidden": true}';
```

#### Campo de Archivo/Imagen
```sql
ALTER TABLE products 
MODIFY COLUMN image VARCHAR(255) 
COMMENT '{
  "type": "file",
  "label": "Imagen del Producto",
  "accept": "image/*",
  "allowed_mimes": ["image/jpeg", "image/png", "image/gif", "image/webp"],
  "max_size": 5242880,
  "tooltip": "Sube una imagen (máx. 5MB)"
}';
```

#### Clave Foránea con Columna de Visualización
```sql
ALTER TABLE posts 
MODIFY COLUMN category_id INT 
COMMENT '{"display_column": "name", "label": "Categoría"}';
```

---

## Personalización de Validación

### Validación en el Cliente (JavaScript)

El archivo `dynamiccrud.js` proporciona validación automática. Puedes extenderlo:

```javascript
// Añadir validación personalizada
class MyCustomValidator extends DynamicCRUDValidator {
    validateField(field) {
        // Llamar a la validación base
        const isValid = super.validateField(field);
        
        // Añadir validación personalizada
        if (field.name === 'username') {
            const value = field.value.trim();
            if (value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                this.showError(field, 'Solo letras, números y guiones bajos');
                return false;
            }
        }
        
        return isValid;
    }
}

// Usar el validador personalizado
new MyCustomValidator();
```

### Validación en el Servidor (PHP)

Para validaciones complejas, extiende `ValidationEngine`:

```php
class CustomValidationEngine extends \DynamicCRUD\ValidationEngine
{
    protected function validateMetadata(array $column, $value): void
    {
        parent::validateMetadata($column, $value);
        
        // Validación personalizada
        if ($column['name'] === 'username') {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                $this->errors[$column['name']][] = 
                    'El username solo puede contener letras, números y guiones bajos';
            }
        }
    }
}
```

---

## Personalización de Estilos

### Sobrescribir Estilos CSS

Crea tu propio archivo CSS después de cargar `dynamiccrud.css`:

```html
<link rel="stylesheet" href="assets/dynamiccrud.css">
<link rel="stylesheet" href="assets/my-custom-styles.css">
```

```css
/* my-custom-styles.css */

/* Cambiar color del botón */
.form-group button[type="submit"] {
    background: #28a745;
}

.form-group button[type="submit"]:hover {
    background: #218838;
}

/* Personalizar mensajes de error */
.field-error {
    color: #e74c3c;
    font-weight: bold;
}

/* Cambiar estilo de inputs con error */
.form-group input.error {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}
```

---

## Personalización de JavaScript

### Eventos Personalizados

Puedes escuchar eventos del formulario:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.dynamic-crud-form');
    
    // Antes de enviar
    form.addEventListener('submit', (e) => {
        console.log('Formulario enviándose...');
    });
    
    // Cuando un campo cambia
    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('change', (e) => {
            console.log(`Campo ${e.target.name} cambió a: ${e.target.value}`);
        });
    });
});
```

---

## Subida de Archivos

### Configuración Básica

```php
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$cache = new FileCacheStrategy();

// Especificar directorio de uploads personalizado
$crud = new DynamicCRUD($pdo, 'products', $cache, __DIR__ . '/my-uploads');
```

### Metadatos para Archivos

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

## Ejemplos Avanzados

### Manejo de Errores Personalizado

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if (!$result['success']) {
        error_log('Error en formulario: ' . json_encode($result));
        $_SESSION['flash_error'] = $result['error'];
        header('Location: form.php?errors=' . urlencode(json_encode($result['errors'])));
        exit;
    }
}
```

---

## Mejores Prácticas

1. **Siempre usa caché** en producción para evitar consultas repetidas a `INFORMATION_SCHEMA`
2. **Limpia la caché** después de cambios en el esquema de la base de datos
3. **Valida en cliente Y servidor** - nunca confíes solo en JavaScript
4. **Usa HTTPS** cuando manejes archivos subidos
5. **Limita el tamaño de archivos** tanto en PHP (`upload_max_filesize`) como en metadatos
6. **Sanitiza nombres de archivos** - el sistema lo hace automáticamente con `uniqid()`
7. **Implementa CSRF** - el sistema lo incluye por defecto, no lo desactives

---

**Última actualización**: 2025-01-31  
**Versión**: Fase 3
