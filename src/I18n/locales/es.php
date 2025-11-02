<?php

return [
    // Form labels
    'form.submit' => 'Enviar',
    'form.save' => 'Guardar',
    'form.cancel' => 'Cancelar',
    'form.required' => 'Campo requerido',
    'form.optional' => 'Opcional',
    
    // Validation messages
    'validation.required' => 'El campo {field} es requerido',
    'validation.email' => 'El campo {field} debe ser un email válido',
    'validation.url' => 'El campo {field} debe ser una URL válida',
    'validation.number' => 'El campo {field} debe ser un número',
    'validation.min' => 'El campo {field} debe ser al menos {min}',
    'validation.max' => 'El campo {field} no debe exceder {max}',
    'validation.minlength' => 'El campo {field} debe tener al menos {minlength} caracteres',
    'validation.maxlength' => 'El campo {field} no debe exceder {maxlength} caracteres',
    'validation.pattern' => 'El formato del campo {field} es inválido',
    'validation.enum' => 'El valor seleccionado para {field} es inválido',
    'validation.foreign_key' => 'El valor seleccionado para {field} no existe',
    
    // Error messages
    'error.csrf_invalid' => 'Token CSRF inválido',
    'error.validation_failed' => 'Validación fallida',
    'error.database' => 'Error de base de datos: {message}',
    'error.file_upload' => 'Error al subir archivo: {message}',
    'error.file_size' => 'El tamaño del archivo excede el máximo permitido ({max})',
    'error.file_type' => 'Tipo de archivo no permitido',
    'error.not_found' => 'Registro no encontrado',
    
    // Success messages
    'success.created' => 'Registro creado exitosamente',
    'success.updated' => 'Registro actualizado exitosamente',
    'success.deleted' => 'Registro eliminado exitosamente',
    
    // M:N UI
    'm2n.select_all' => 'Seleccionar visibles',
    'm2n.clear_all' => 'Limpiar todo',
    'm2n.search' => 'Buscar...',
    'm2n.selected' => '{count} de {total} seleccionados',
    'm2n.hint' => 'Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples',
    'm2m.selected' => '{count} seleccionados',
    'm2m.no_results' => 'No se encontraron resultados',
    
    // Common
    'common.yes' => 'Sí',
    'common.no' => 'No',
    'common.save' => 'Guardar',
    'common.delete' => 'Eliminar',
    'common.edit' => 'Editar',
    'common.cancel' => 'Cancelar',
    'common.select' => '-- Seleccionar --',
    'common.loading' => 'Cargando...',
    'common.confirm_delete' => '¿Estás seguro de que quieres eliminar este registro?',
];
