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
        
        foreach ($this->getNonPrimaryColumns() as $column) {
            $value = $data[$column['name']] ?? null;
            $this->validateField($column, $value);
        }
        
        return empty($this->errors);
    }

    private function getNonPrimaryColumns(): array
    {
        return array_filter(
            $this->schema['columns'],
            fn($col) => !$col['is_primary']
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateField(array $column, $value): void
    {
        if ($this->isHiddenField($column)) {
            return;
        }
        
        if ($this->isRequiredAndEmpty($column, $value)) {
            $this->addRequiredError($column['name']);
            return;
        }
        
        if ($this->isEmpty($value)) {
            return;
        }
        
        $this->validateType($column, $value);
        $this->validateLength($column, $value);
        $this->validateMetadata($column, $value);
    }

    private function isHiddenField(array $column): bool
    {
        return $column['metadata']['hidden'] ?? false;
    }

    private function isRequiredAndEmpty(array $column, $value): bool
    {
        return !$column['is_nullable'] && $this->isEmpty($value);
    }

    private function isEmpty($value): bool
    {
        return $value === null || $value === '';
    }

    private function addRequiredError(string $fieldName): void
    {
        $message = $this->translator 
            ? $this->translator->t('validation.required', ['field' => $fieldName])
            : "El campo {$fieldName} es requerido";
        $this->errors[$fieldName][] = $message;
    }

    private function validateType(array $column, $value): void
    {
        $sqlType = $column['sql_type'];
        $fieldName = $column['name'];
        
        if (in_array($sqlType, ['int', 'bigint', 'smallint', 'tinyint'])) {
            $this->validateInteger($fieldName, $value);
        } elseif (in_array($sqlType, ['decimal', 'float', 'double'])) {
            $this->validateNumeric($fieldName, $value);
        } elseif (in_array($sqlType, ['date', 'datetime', 'timestamp'])) {
            $this->validateDate($fieldName, $value);
        }
    }

    private function validateInteger(string $fieldName, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($fieldName, 'validation.number', "El campo {$fieldName} debe ser un número entero");
        }
    }

    private function validateNumeric(string $fieldName, $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($fieldName, 'validation.number', "El campo {$fieldName} debe ser un número");
        }
    }

    private function validateDate(string $fieldName, $value): void
    {
        if (!strtotime($value)) {
            $this->errors[$fieldName][] = "El campo {$fieldName} debe ser una fecha válida";
        }
    }

    private function addError(string $fieldName, string $translationKey, string $fallback): void
    {
        $message = $this->translator
            ? $this->translator->t($translationKey, ['field' => $fieldName])
            : $fallback;
        $this->errors[$fieldName][] = $message;
    }

    private function validateLength(array $column, $value): void
    {
        if (!$column['max_length']) return;
        
        if (strlen($value) > $column['max_length']) {
            $this->addMaxLengthError($column['name'], $column['max_length']);
        }
    }

    private function addMaxLengthError(string $fieldName, int $maxLength): void
    {
        $message = $this->translator
            ? $this->translator->t('validation.maxlength', ['field' => $fieldName, 'maxlength' => $maxLength])
            : sprintf("El campo %s no puede exceder %d caracteres", $fieldName, $maxLength);
        $this->errors[$fieldName][] = $message;
    }

    private function validateMetadata(array $column, $value): void
    {
        $type = $column['metadata']['type'] ?? null;
        
        if ($type === 'email') {
            $this->validateEmail($column['name'], $value);
        }
        
        if ($type === 'url') {
            $this->validateUrl($column['name'], $value);
        }
        
        if (is_numeric($value)) {
            $this->validateMinMax($column, $value);
        }
        
        $this->validateMinLength($column, $value);
    }

    private function validateEmail(string $fieldName, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($fieldName, 'validation.email', "El campo {$fieldName} debe ser un email válido");
        }
    }

    private function validateUrl(string $fieldName, $value): void
    {
        if (!preg_match('/^https?:\/\//i', $value)) {
            $this->addError($fieldName, 'validation.url', "El campo {$fieldName} debe comenzar con http:// o https://");
        } elseif (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($fieldName, 'validation.url', "El campo {$fieldName} debe ser una URL válida");
        }
    }

    private function validateMinMax(array $column, $value): void
    {
        $name = $column['name'];
        
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

    private function validateMinLength(array $column, $value): void
    {
        if (!isset($column['metadata']['minlength'])) return;
        
        if (strlen($value) < $column['metadata']['minlength']) {
            $message = $this->translator
                ? $this->translator->t('validation.minlength', ['field' => $column['name'], 'minlength' => $column['metadata']['minlength']])
                : "El campo {$column['name']} debe tener al menos {$column['metadata']['minlength']} caracteres";
            $this->errors[$column['name']][] = $message;
        }
    }
}
