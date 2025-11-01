# Fase 3: Validación Cliente y Archivos - Checklist

## 🎯 Objetivo
Mejorar experiencia de usuario con validación JavaScript en tiempo real y soporte para subida de archivos.

## 📋 Tareas

### 1. Validación Cliente (JavaScript)
- [ ] Generación automática de reglas JS desde metadatos
- [ ] Validación en tiempo real (onblur/oninput)
- [ ] Mensajes de error dinámicos en el formulario
- [ ] Validación asíncrona para unicidad de campos
- [ ] Integración con HTML5 Constraint Validation API
- [ ] Deshabilitar submit mientras valida

### 2. Subida de Archivos
- [ ] Metadato "file" para detectar campos de archivo
- [ ] Generación de `<input type="file">`
- [ ] Validación de tipos MIME permitidos
- [ ] Validación de tamaño máximo
- [ ] Almacenamiento en directorio configurable
- [ ] Guardado de ruta en campo VARCHAR
- [ ] Preview de imágenes antes de subir
- [ ] Manejo de errores de subida

### 3. Mejoras UX
- [ ] Indicadores de carga (spinners)
- [ ] Mensajes de éxito/error mejorados
- [ ] Accesibilidad (ARIA labels, roles)
- [ ] Navegación por teclado
- [ ] Tooltips informativos

### 4. Documentación
- [ ] Ejemplos de validación cliente
- [ ] Ejemplos de subida de archivos
- [ ] Guía de personalización JS
- [ ] Tests para validación cliente

## 🔄 En Progreso

(Se actualizará conforme avancemos)

## ✅ Completado

(Se actualizará conforme avancemos)

## 📝 Notas de Implementación

### Decisiones Técnicas Fase 3
1. **JavaScript**: Vanilla JS sin dependencias (jQuery-free)
2. **Validación**: Progressive enhancement (funciona sin JS)
3. **Archivos**: Almacenamiento local por defecto
4. **MIME**: Whitelist configurable por campo

### Compatibilidad
- Navegadores modernos (ES6+)
- Fallback a validación servidor si JS deshabilitado

## 🎯 Criterios de Éxito Fase 3
- [ ] Validación cliente funciona en tiempo real
- [ ] Subida de archivos funciona con validación
- [ ] Formularios accesibles (WCAG 2.1 AA)
- [ ] Ejemplos funcionales con archivos
- [ ] Tests cubren validación cliente
- [ ] Documentación completa

## ⏱️ Tiempo Estimado
- **Estimado**: 2-3 semanas
- **Real**: [Por completar]

---

**Última actualización**: Inicio Fase 3
**Estado**: 📋 PLANIFICADA
