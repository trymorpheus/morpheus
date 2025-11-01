<?php

namespace DynamicCRUD;

use PDO;

class FormGenerator
{
    private array $schema;
    private array $data;
    private string $csrfToken;
    private ?PDO $pdo;

    public function __construct(array $schema, array $data = [], string $csrfToken = '', ?PDO $pdo = null)
    {
        $this->schema = $schema;
        $this->data = $data;
        $this->csrfToken = $csrfToken;
        $this->pdo = $pdo;
    }

    public function render(): string
    {
        $html = $this->renderAssets() . "\n";
        $enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
        $html .= '<form method="POST" class="dynamic-crud-form"' . $enctype . '>' . "\n";
        $html .= $this->renderCsrfField() . "\n";
        
        $pk = $this->schema['primary_key'];
        if (!empty($this->data[$pk])) {
            $html .= sprintf('<input type="hidden" name="id" value="%s">', htmlspecialchars($this->data[$pk])) . "\n";
        }
        
        foreach ($this->schema['columns'] as $column) {
            if ($column['is_primary']) continue;
            if ($column['metadata']['hidden'] ?? false) continue;
            $html .= $this->renderField($column) . "\n";
        }
        
        $html .= '<button type="submit">Guardar</button>' . "\n";
        $html .= '</form>' . "\n";
        
        return $html;
    }

    private function renderCsrfField(): string
    {
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($this->csrfToken)
        );
    }

    private function renderField(array $column): string
    {
        $label = $column['metadata']['label'] ?? ucfirst($column['name']);
        $value = $this->data[$column['name']] ?? $column['default_value'] ?? '';
        
        $html = '<div class="form-group">' . "\n";
        $html .= sprintf('  <label for="%s">%s</label>', $column['name'], htmlspecialchars($label)) . "\n";
        $html .= '  ' . $this->renderInput($column, $value) . "\n";
        $html .= '</div>';
        
        return $html;
    }

    private function renderInput(array $column, $value): string
    {
        if ($this->isForeignKey($column)) {
            return $this->renderForeignKeySelect($column, $value);
        }
        
        if ($this->isFileField($column)) {
            return $this->renderFileInput($column, $value);
        }
        
        $type = $this->getInputType($column);
        $attributes = $this->getInputAttributes($column);
        
        if ($type === 'textarea') {
            return sprintf(
                '<textarea name="%s" id="%s"%s>%s</textarea>',
                $column['name'],
                $column['name'],
                $attributes,
                htmlspecialchars($value)
            );
        }
        
        $validationAttrs = $this->getValidationAttributes($column);
        
        return sprintf(
            '<input type="%s" name="%s" id="%s" value="%s"%s%s>',
            $type,
            $column['name'],
            $column['name'],
            htmlspecialchars($value),
            $attributes,
            $validationAttrs
        );
    }

    private function getInputType(array $column): string
    {
        $metadata = $column['metadata']['type'] ?? null;
        
        if ($metadata === 'email') return 'email';
        if ($metadata === 'url') return 'url';
        
        return match($column['sql_type']) {
            'int', 'bigint', 'smallint', 'tinyint' => 'number',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime-local',
            'text', 'longtext', 'mediumtext' => 'textarea',
            default => 'text'
        };
    }

    private function getInputAttributes(array $column): string
    {
        $attrs = [];
        
        if (!$column['is_nullable']) {
            $attrs[] = 'required';
        }
        
        if ($column['max_length']) {
            $attrs[] = sprintf('maxlength="%d"', $column['max_length']);
        }
        
        if ($column['sql_type'] === 'int') {
            $attrs[] = 'step="1"';
        }
        
        // Min/Max desde metadatos
        if (isset($column['metadata']['min'])) {
            $attrs[] = sprintf('min="%s"', $column['metadata']['min']);
        }
        
        if (isset($column['metadata']['max'])) {
            $attrs[] = sprintf('max="%s"', $column['metadata']['max']);
        }
        
        // Minlength desde metadatos
        if (isset($column['metadata']['minlength'])) {
            $attrs[] = sprintf('minlength="%d"', $column['metadata']['minlength']);
        }
        
        return $attrs ? ' ' . implode(' ', $attrs) : '';
    }

    private function isForeignKey(array $column): bool
    {
        return isset($this->schema['foreign_keys'][$column['name']]);
    }

    private function renderForeignKeySelect(array $column, $value): string
    {
        $fk = $this->schema['foreign_keys'][$column['name']];
        $displayColumn = $column['metadata']['display_column'] ?? 'name';
        
        $options = $this->getForeignKeyOptions($fk['table'], $fk['column'], $displayColumn);
        
        $html = sprintf('<select name="%s" id="%s"%s>',
            $column['name'],
            $column['name'],
            $column['is_nullable'] ? '' : ' required'
        );
        
        if ($column['is_nullable']) {
            $html .= '<option value="">-- Seleccionar --</option>';
        }
        
        foreach ($options as $option) {
            $selected = $value == $option['value'] ? ' selected' : '';
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($option['value']),
                $selected,
                htmlspecialchars($option['label'])
            );
        }
        
        $html .= '</select>';
        return $html;
    }

    private function getForeignKeyOptions(string $table, string $valueColumn, string $displayColumn): array
    {
        if (!$this->pdo) {
            return [];
        }
        
        $sql = sprintf(
            "SELECT %s as value, %s as label FROM %s ORDER BY %s",
            $valueColumn,
            $displayColumn,
            $table,
            $displayColumn
        );
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function renderAssets(): string
    {
        return '<link rel="stylesheet" href="assets/dynamiccrud.css">' .
               '<script src="assets/dynamiccrud.js" defer></script>';
    }

    private function getValidationAttributes(array $column): string
    {
        $rules = [];
        
        if (!empty($column['metadata']['validators'])) {
            $rules['validators'] = $column['metadata']['validators'];
        }
        
        if (!empty($column['metadata']['type'])) {
            $rules['type'] = $column['metadata']['type'];
        }
        
        if (empty($rules)) {
            return '';
        }
        
        return sprintf(' data-validation=\'%s\'', htmlspecialchars(json_encode($rules)));
    }

    private function hasFileFields(): bool
    {
        foreach ($this->schema['columns'] as $column) {
            if ($this->isFileField($column)) {
                return true;
            }
        }
        return false;
    }

    private function isFileField(array $column): bool
    {
        return ($column['metadata']['type'] ?? null) === 'file';
    }

    private function renderFileInput(array $column, $value): string
    {
        $accept = $column['metadata']['accept'] ?? '';
        $acceptAttr = $accept ? sprintf(' accept="%s"', htmlspecialchars($accept)) : '';
        $requiredAttr = (!$column['is_nullable'] && !$value) ? ' required' : '';
        
        $html = sprintf(
            '<input type="file" name="%s" id="%s"%s%s>',
            $column['name'],
            $column['name'],
            $requiredAttr,
            $acceptAttr
        );
        
        if ($value) {
            $html .= sprintf(
                '<div class="file-info">Archivo actual: <a href="%s" target="_blank">%s</a></div>',
                htmlspecialchars($value),
                htmlspecialchars(basename($value))
            );
            
            if ($this->isImage($value)) {
                $html .= sprintf(
                    '<div class="file-preview"><img src="%s" alt="Preview"></div>',
                    htmlspecialchars($value)
                );
            }
        }
        
        $html .= '<div class="file-preview-new"></div>';
        
        return $html;
    }

    private function isImage(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
