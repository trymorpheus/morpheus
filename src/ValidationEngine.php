<?php

namespace DynamicCRUD;

use DynamicCRUD\I18n\Translator;

class ValidationEngine
{
    private array $schema;
    private array $errors = [];
    private ?Translator $translator = null;

    public function __construct(array $schema, ?Translator $translator = null)
    {
        $this->schema = $schema;
        $this->translator = $translator;
    }

    public function validate(array $data): bool
    {
        $this->errors = [];
        
        foreach ($this->schema['columns'] as $column) {
            if ($column['is_primary']) continue;
            
            $value = $data[$column['name']] ?? null;
            $this->validateField($column, $value);
        }
        
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateField(array $column, $value): void
    {
        $name = $column['name'];
        
        if ($column['metadata']['hidden'] ?? false) {
            return;
        }
        
        if (!$column['is_nullable'] && ($value === null || $value === '')) {
            $message = $this->translator 
                ? $this->translator->t('validation.required', ['field' => $name])
                : "El campo {$name} es requerido";
            $this->errors[$name][] = $message;
            return;
        }
        
        if ($value === null || $value === '') {
            return;
        }
        
        $this->validateType($column, $value);
        $this->validateLength($column, $value);
        $this->validateMetadata($column, $value);
    }

    private function validateType(array $column, $value): void
    {
        $name = $column['name'];
        
        switch ($column['sql_type']) {
            case 'int':
            case 'bigint':
            case 'smallint':
            case 'tinyint':
                if (!filter_var($value, FILTER_VALIDATE_INT)) {
                    $message = $this->translator
                        ? $this->translator->t('validation.number', ['field' => $name])
                        : "El campo {$name} debe ser un número entero";
                    $this->errors[$name][] = $message;
                }
                break;
                
            case 'decimal':
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    $message = $this->translator
                        ? $this->translator->t('validation.number', ['field' => $name])
                        : "El campo {$name} debe ser un número";
                    $this->errors[$name][] = $message;
                }
                break;
                
            case 'date':
            case 'datetime':
            case 'timestamp':
                if (!strtotime($value)) {
                    $this->errors[$name][] = "El campo {$name} debe ser una fecha válida";
                }
                break;
        }
    }

    private function validateLength(array $column, $value): void
    {
        if (!$column['max_length']) return;
        
        $name = $column['name'];
        $length = strlen($value);
        
        if ($length > $column['max_length']) {
            $message = $this->translator
                ? $this->translator->t('validation.maxlength', ['field' => $name, 'maxlength' => $column['max_length']])
                : sprintf("El campo %s no puede exceder %d caracteres", $name, $column['max_length']);
            $this->errors[$name][] = $message;
        }
    }

    private function validateMetadata(array $column, $value): void
    {
        $name = $column['name'];
        $type = $column['metadata']['type'] ?? null;
        
        if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $message = $this->translator
                ? $this->translator->t('validation.email', ['field' => $name])
                : "El campo {$name} debe ser un email válido";
            $this->errors[$name][] = $message;
        }
        
        if ($type === 'url') {
            if (!preg_match('/^https?:\/\//i', $value)) {
                $message = $this->translator
                    ? $this->translator->t('validation.url', ['field' => $name])
                    : "El campo {$name} debe comenzar con http:// o https://";
                $this->errors[$name][] = $message;
            } elseif (!filter_var($value, FILTER_VALIDATE_URL)) {
                $message = $this->translator
                    ? $this->translator->t('validation.url', ['field' => $name])
                    : "El campo {$name} debe ser una URL válida";
                $this->errors[$name][] = $message;
            }
        }
        
        // Validar min/max para números
        if (is_numeric($value)) {
            if (isset($column['metadata']['min']) && $value < $column['metadata']['min']) {
                $message = $this->translator
                    ? $this->translator->t('validation.min', ['field' => $name, 'min' => $column['metadata']['min']])
                    : "El campo {$name} debe ser mayor o igual a {$column['metadata']['min']}";
                $this->errors[$name][] = $message;
            }
            
            if (isset($column['metadata']['max']) && $value > $column['metadata']['max']) {
                $message = $this->translator
                    ? $this->translator->t('validation.max', ['field' => $name, 'max' => $column['metadata']['max']])
                    : "El campo {$name} debe ser menor o igual a {$column['metadata']['max']}";
                $this->errors[$name][] = $message;
            }
        }
        
        // Validar minlength
        if (isset($column['metadata']['minlength']) && strlen($value) < $column['metadata']['minlength']) {
            $message = $this->translator
                ? $this->translator->t('validation.minlength', ['field' => $name, 'minlength' => $column['metadata']['minlength']])
                : "El campo {$name} debe tener al menos {$column['metadata']['minlength']} caracteres";
            $this->errors[$name][] = $message;
        }
    }
}
