<?php

namespace DynamicCRUD;

class ValidationEngine
{
    private array $schema;
    private array $errors = [];

    public function __construct(array $schema)
    {
        $this->schema = $schema;
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
            $this->errors[$name][] = "El campo {$name} es requerido";
            return;
        }
        
        if ($value === null || $value === '') return;
        
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
                    $this->errors[$name][] = "El campo {$name} debe ser un número entero";
                }
                break;
                
            case 'decimal':
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    $this->errors[$name][] = "El campo {$name} debe ser un número";
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
            $this->errors[$name][] = sprintf(
                "El campo %s no puede exceder %d caracteres",
                $name,
                $column['max_length']
            );
        }
    }

    private function validateMetadata(array $column, $value): void
    {
        $name = $column['name'];
        $type = $column['metadata']['type'] ?? null;
        
        if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$name][] = "El campo {$name} debe ser un email válido";
        }
        
        if ($type === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$name][] = "El campo {$name} debe ser una URL válida";
        }
    }
}
