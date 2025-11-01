# 🐛 Registro de Bugs - DynamicCRUD

## 🔴 Bugs Abiertos

--

## ✅ Bugs Resueltos

### BUG-001: Token CSRF inválido en products.php
**Estado**: ✅ RESUELTO  
**Prioridad**: ALTA  
**Fecha detección**: 2025-01-31  
**Fecha resolución**: 2025-11-01  
**Afecta a**: Fase 3 - Subida de archivos

**Descripción**:
Al enviar el formulario en `examples/products.php`, siempre aparecía el error "Token CSRF inválido", aunque el token se generaba correctamente.

**Síntomas**:
- El formulario en `debug_csrf.php` mostraba que el token en POST era diferente al token en SESSION
- Cuando se cargaba la página, se generaba un token (ej: `653e467e...`)
- Cuando se enviaba el formulario, el POST contenía un token diferente (ej: `a49b2037...`)
- Después de `renderForm()`, se generaba un NUEVO token (ej: `5bd23ec6...`)

**Evidencia del debug**:
```
Session Data: [csrf_token] => 653e467ecb13257d0c2c4d6b6e91c026c5cd55d38851be6e7d85786dfcf0162d
POST Data: [csrf_token] => a49b2037e9fe49ac66c8540460858d3c77a69b9c0ec4c8816d6ac52c19a5a450
¿Coinciden?: ❌ NO
Token en HTML después de renderForm: 5bd23ec6c2801c742126bca8c0d6fb6103ac2dc83117253f594457624defdf66
```

**Archivos involucrados**:
- `src/SecurityModule.php` - Genera y valida tokens
- `src/FormGenerator.php` - Renderiza campo hidden con token
- `src/CRUDHandler.php` - Valida token en handleSubmission()
- `examples/products.php` - Página que fallaba

**Intentos de solución**:
1. ✅ Cambiar nombre de `_csrf_token` a `csrf_token` en FormGenerator
2. ✅ Cambiar nombre de `_csrf_token` a `csrf_token` en SecurityModule
3. ✅ Cambiar nombre de `_csrf_token` a `csrf_token` en CRUDHandler
4. ✅ Añadir `session_start()` al inicio de products.php
5. ✅ Limpiar token antiguo `_csrf_token` de la sesión
6. ✅ Implementar patrón POST-Redirect-GET para evitar regenerar token
7. ❌ Ninguna solución había funcionado

**Solución**:
Modificar `src/SecurityModule.php` en la función `generateCsrfToken()` para que solo genere un nuevo token si no existe uno en la sesión. Esto evita la sobrescritura prematura del token y asegura que el token enviado en el formulario coincida con el de la sesión.

**Archivos modificados**:
- `src/SecurityModule.php`

**Observaciones**:
- El mismo código funciona correctamente en `debug_products.php` y `test_token.php`
- El problema estaba en el flujo de generación/validación del token
- Causa raíz: El token se regeneraba entre la carga del formulario y el envío del POST

---

### BUG-002: Extensión fileinfo no habilitada
**Estado**: ✅ RESUELTO  
**Prioridad**: ALTA  
**Fecha detección**: 2025-01-31  
**Fecha resolución**: 2025-01-31  
**Afecta a**: Fase 3 - Subida de archivos

**Descripción**:
Error fatal al intentar subir archivos: `Call to undefined function DynamicCRUD\finfo_open()`

**Causa raíz**:
La extensión `fileinfo` de PHP estaba comentada en `php.ini`

**Solución**:
Descomentar la línea en `C:\Program Files\php\php.ini`:
```ini
;extension=fileinfo  →  extension=fileinfo
```
Reiniciar el servidor web.

**Archivos modificados**:
- `C:\Program Files\php\php.ini` (línea 922)

---

### BUG-003: Ruta de archivo almacenada con path absoluto
**Estado**: ✅ RESUELTO  
**Prioridad**: MEDIA  
**Fecha detección**: 2025-01-31  
**Fecha resolución**: 2025-01-31  
**Afecta a**: Fase 3 - Subida de archivos

**Descripción**:
Al subir un archivo, se guardaba en la base de datos la ruta absoluta completa:
```
C:\Users\mrcm_\Local\proj\dynamicCRUD\src/../examples/uploads/690574d914ddf_1761965273.png
```

**Causa raíz**:
`FileUploadHandler::handleUpload()` retornaba `$destination` que contenía la ruta completa del sistema.

**Solución**:
Modificar el return para devolver solo la ruta relativa:
```php
return 'uploads/' . $filename;
```

**Archivos modificados**:
- `src/FileUploadHandler.php` (línea 64)

---

### BUG-004: Campos nullable guardaban cadenas vacías en lugar de NULL
**Estado**: ✅ RESUELTO  
**Prioridad**: MEDIA  
**Fecha detección**: Fase 2  
**Fecha resolución**: Fase 2  
**Afecta a**: Fase 2 - Manejo de NULL

**Descripción**:
Los campos opcionales (nullable) guardaban `''` (cadena vacía) en lugar de `NULL` cuando el usuario no ingresaba valor.

**Causa raíz**:
`SecurityModule::sanitizeInput()` no convertía cadenas vacías a NULL para campos nullable.

**Solución**:
Añadir lógica en `sanitizeInput()`:
```php
if ($value === '' && $this->isNullable($column, $schema)) {
    $value = null;
}
```

Y usar `PDO::PARAM_NULL` en los bindings:
```php
$stmt->bindValue(":$key", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
```

**Archivos modificados**:
- `src/SecurityModule.php`
- `src/CRUDHandler.php`

---

### BUG-005: display_errors deshabilitado en php.ini
**Estado**: ✅ RESUELTO  
**Prioridad**: BAJA  
**Fecha detección**: 2025-01-31  
**Fecha resolución**: 2025-01-31  
**Afecta a**: Debugging general

**Descripción**:
Los errores PHP no se mostraban en el navegador, dificultando el debugging.

**Causa raíz**:
`display_errors = Off` en `php.ini` (configuración de producción)

**Solución**:
Cambiar temporalmente en `php.ini`:
```ini
display_errors = On
```

O añadir en scripts de debug:
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

**Archivos modificados**:
- Scripts de debug individuales

---

## 📊 Estadísticas

- **Total bugs detectados**: 5
- **Bugs resueltos**: 5 (100%)
- **Bugs abiertos**: 0 (0%)
- **Bugs críticos abiertos**: 0

---

## 🔍 Notas para Debugging

### Herramientas creadas:
1. `examples/debug_products.php` - Debug básico de POST/FILES/SESSION
2. `examples/test_token.php` - Test de coincidencia de tokens
3. `examples/debug_csrf.php` - Debug completo del flujo CSRF

### Comandos útiles:
```bash
# Ver extensiones PHP cargadas
php -m

# Verificar configuración PHP
php -i | findstr fileinfo

# Buscar en php.ini
findstr /n "fileinfo" "C:\Program Files\php\php.ini"
```

### Patrones de debugging:
```php
// Mostrar estado de sesión
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Comparar tokens
$tokenPost = $_POST['csrf_token'] ?? 'NO_EXISTE';
$tokenSession = $_SESSION['csrf_token'] ?? 'NO_EXISTE';
var_dump($tokenPost === $tokenSession);
```

---

**Última actualización**: 2025-11-01  
**Mantenido por**: Equipo DynamicCRUD