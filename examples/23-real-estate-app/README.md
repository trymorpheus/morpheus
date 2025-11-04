# 🏢 Barcelona Locales - Aplicación Inmobiliaria Completa

Aplicación completa de gestión de locales comerciales construida con DynamicCRUD.

## 🎯 Características

### Panel de Administración (`/admin/`)
- ✅ Gestión completa de locales comerciales
- ✅ Formulario con tabs organizados (Info, Precios, Características, Fotos)
- ✅ Múltiples fotos por local (drag & drop)
- ✅ Gestión de consultas de clientes
- ✅ Dashboard con estadísticas
- ✅ Branding personalizado

### Portal Público (`/public/`)
- ✅ Catálogo de locales disponibles
- ✅ Filtros por barrio, precio y metros
- ✅ Diseño responsive y profesional
- ✅ Página de detalle con galería de fotos
- ✅ Formulario de contacto integrado
- ✅ Destacados y badges

## 🚀 Instalación

### 1. Crear base de datos
```bash
mysql -u root -p test < setup.sql
```

### 2. Acceder a la aplicación

**Panel de Administración:**
```
http://localhost:8000/examples/23-real-estate-app/admin/
```

**Portal Público:**
```
http://localhost:8000/examples/23-real-estate-app/public/
```

## 📊 Estructura de Datos

### Tabla: `locales`
- Información básica (título, descripción, dirección, barrio)
- Precios (compra, reforma, venta)
- Características (m², baños, escaparate, altura techo, salida humos)
- Fotos múltiples (JSON array)
- Estados (comprado, en_reforma, en_venta, vendido)
- Configuración (destacado, visible_web)

### Tabla: `consultas`
- Datos del cliente (nombre, email, teléfono)
- Local de interés (FK)
- Mensaje
- Estado (nueva, contactado, visita_programada, cerrada)

## 🎨 Personalización

### Branding
Los colores y estilos están configurados en:
- Naranja (#d97706) como color principal
- Diseño moderno y profesional
- Responsive para móvil

### Modificar datos
Edita `setup.sql` para cambiar:
- Barrios disponibles
- Locales de ejemplo
- Precios y características

## 💡 Funcionalidades Destacadas

### 1. Múltiples Fotos
- Drag & drop para subir fotos
- Galería en página de detalle
- Thumbnails navegables

### 2. Filtros Avanzados
- Por barrio
- Por precio máximo
- Por metros mínimos
- Combinables

### 3. Gestión de Consultas
- Formulario público integrado
- Gestión en panel admin
- Estados de seguimiento

### 4. Locales Destacados
- Badge especial en catálogo
- Borde destacado
- Aparecen primero en listado

## 🔧 Extensiones Posibles

### Fáciles de agregar:
- ✅ Workflow de estados (usar WorkflowEngine)
- ✅ Notificaciones por email (ya integrado)
- ✅ Exportar catálogo a PDF
- ✅ Multi-idioma (Catalán, Inglés)
- ✅ REST API para integraciones
- ✅ Analytics de visitas

### Con DynamicCRUD:
```php
// Agregar workflow
$crud->enableWorkflow([
    'field' => 'estado',
    'states' => ['comprado', 'en_reforma', 'en_venta', 'vendido'],
    'transitions' => [
        'iniciar_reforma' => ['from' => 'comprado', 'to' => 'en_reforma'],
        'poner_en_venta' => ['from' => 'en_reforma', 'to' => 'en_venta'],
        'marcar_vendido' => ['from' => 'en_venta', 'to' => 'vendido']
    ]
]);

// Agregar notificaciones
// Configurar en metadata de tabla 'consultas'
```

## 📱 Responsive

La aplicación es completamente responsive:
- Desktop: Grid de 3 columnas
- Tablet: Grid de 2 columnas
- Móvil: 1 columna

## 🎓 Aprendizaje

Este ejemplo demuestra:
- ✅ Aplicación completa en producción
- ✅ Panel admin + Portal público
- ✅ Múltiples fotos por registro
- ✅ Filtros y búsqueda
- ✅ Formularios integrados
- ✅ Diseño profesional
- ✅ Metadata avanzada

## 🚀 Tiempo de Desarrollo

**Con DynamicCRUD:** ~45 minutos
**Sin DynamicCRUD:** ~2-3 semanas

## 📞 Soporte

Para más información sobre DynamicCRUD:
- [Documentación](../../docs/)
- [Más Ejemplos](../)
- [GitHub](https://github.com/mcarbonell/DynamicCRUD)

---

**Construido con ❤️ usando DynamicCRUD**
