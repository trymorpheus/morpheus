<?php

return [
    // Form labels
    'form.submit' => 'Submit',
    'form.save' => 'Save',
    'form.cancel' => 'Cancel',
    'form.required' => 'Required field',
    'form.optional' => 'Optional',
    
    // Validation messages
    'validation.required' => 'The {field} field is required',
    'validation.email' => 'The {field} field must be a valid email',
    'validation.url' => 'The {field} field must be a valid URL',
    'validation.number' => 'The {field} field must be a number',
    'validation.min' => 'The {field} field must be at least {min}',
    'validation.max' => 'The {field} field must be at most {max}',
    'validation.minlength' => 'The {field} field must be at least {minlength} characters',
    'validation.maxlength' => 'The {field} field cannot exceed {maxlength} characters',
    'validation.pattern' => 'The {field} format is invalid',
    'validation.enum' => 'The selected {field} is invalid',
    'validation.foreign_key' => 'The selected {field} does not exist',
    
    // Error messages
    'error.csrf_invalid' => 'Invalid CSRF token',
    'error.validation_failed' => 'Validation failed',
    'error.database' => 'Database error: {message}',
    'error.file_upload' => 'File upload error: {message}',
    'error.file_size' => 'File size exceeds the maximum allowed ({max})',
    'error.file_type' => 'File type not allowed',
    'error.not_found' => 'Record not found',
    
    // Success messages
    'success.created' => 'Record created successfully',
    'success.updated' => 'Record updated successfully',
    'success.deleted' => 'Record deleted successfully',
    
    // M:N UI
    'm2n.select_all' => 'Select visible',
    'm2n.clear_all' => 'Clear all',
    'm2n.search' => 'Search...',
    'm2n.selected' => '{count} of {total} selected',
    'm2n.hint' => 'Hold Ctrl (Cmd on Mac) to select multiple',
    'm2m.selected' => '{count} selected',
    'm2m.no_results' => 'No results found',
    
    // Common
    'common.yes' => 'Yes',
    'common.no' => 'No',
    'common.save' => 'Save',
    'common.delete' => 'Delete',
    'common.edit' => 'Edit',
    'common.cancel' => 'Cancel',
    'common.select' => '-- Select --',
    'common.loading' => 'Loading...',
    'common.confirm_delete' => 'Are you sure you want to delete this record?',
];
