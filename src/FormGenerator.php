<?php

namespace DynamicCRUD;

use PDO;

class FormGenerator
{
    private array $schema;
    private array $data;
    private string $csrfToken;
    private ?PDO $pdo;
    private ?CRUDHandler $handler;

    public function __construct(array $schema, array $data = [], string $csrfToken = '', ?PDO $pdo = null, ?CRUDHandler $handler = null)
    {
        $this->schema = $schema;
        $this->data = $data;
        $this->csrfToken = $csrfToken;
        $this->pdo = $pdo;
        $this->handler = $handler;
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
        
        // Renderizar campos virtuales
        if ($this->handler) {
            $html .= $this->renderVirtualFields() . "\n";
        }
        
        // Renderizar campos M:N
        if ($this->handler) {
            $html .= $this->renderManyToManyFields() . "\n";
        }
        
        $submitLabel = 'Guardar';
        if ($this->handler && $this->handler->getTranslator()) {
            $submitLabel = $this->handler->getTranslator()->t('form.submit');
        }
        $html .= sprintf('<button type="submit">%s</button>', htmlspecialchars($submitLabel)) . "\n";
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
        $tooltip = $column['metadata']['tooltip'] ?? null;
        
        $html = '<div class="form-group">' . "\n";
        $html .= sprintf('  <label for="%s">%s', $column['name'], htmlspecialchars($label));
        
        if ($tooltip) {
            $html .= sprintf(
                ' <span class="tooltip" tabindex="0"><span class="tooltip-icon" aria-label="Ayuda">?</span><span class="tooltip-text" role="tooltip">%s</span></span>',
                htmlspecialchars($tooltip)
            );
        }
        
        $html .= '</label>' . "\n";
        $html .= '  ' . $this->renderInput($column, $value) . "\n";
        $html .= '</div>';
        
        return $html;
    }

    private function renderInput(array $column, $value): string
    {
        if ($this->isForeignKey($column)) {
            return $this->renderForeignKeySelect($column, $value);
        }
        
        if ($this->isEnumField($column)) {
            return $this->renderEnumSelect($column, $value);
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
        
        // Tipos especiales desde metadata
        if ($metadata === 'email') return 'email';
        if ($metadata === 'url') return 'url';
        if ($metadata === 'color') return 'color';
        if ($metadata === 'tel') return 'tel';
        if ($metadata === 'password') return 'password';
        if ($metadata === 'search') return 'search';
        if ($metadata === 'time') return 'time';
        if ($metadata === 'week') return 'week';
        if ($metadata === 'month') return 'month';
        if ($metadata === 'range') return 'range';
        
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
            $attrs[] = 'aria-required="true"';
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
        
        // Placeholder desde metadatos
        if (isset($column['metadata']['placeholder'])) {
            $attrs[] = sprintf('placeholder="%s"', htmlspecialchars($column['metadata']['placeholder']));
        }
        
        // Pattern desde metadatos
        if (isset($column['metadata']['pattern'])) {
            $attrs[] = sprintf('pattern="%s"', htmlspecialchars($column['metadata']['pattern']));
        }
        
        // Step desde metadatos (para number/range)
        if (isset($column['metadata']['step'])) {
            $attrs[] = sprintf('step="%s"', $column['metadata']['step']);
        }
        
        // Readonly desde metadatos
        if (isset($column['metadata']['readonly']) && $column['metadata']['readonly']) {
            $attrs[] = 'readonly';
        }
        
        // Autocomplete desde metadatos
        if (isset($column['metadata']['autocomplete'])) {
            $attrs[] = sprintf('autocomplete="%s"', htmlspecialchars($column['metadata']['autocomplete']));
        }
        
        return $attrs ? ' ' . implode(' ', $attrs) : '';
    }

    private function isForeignKey(array $column): bool
    {
        return isset($this->schema['foreign_keys'][$column['name']]);
    }
    
    private function isEnumField(array $column): bool
    {
        return $column['sql_type'] === 'enum' && !empty($column['enum_values']);
    }
    
    private function renderEnumSelect(array $column, $value): string
    {
        $html = sprintf('<select name="%s" id="%s"%s>',
            $column['name'],
            $column['name'],
            $column['is_nullable'] ? '' : ' required'
        );
        
        if ($column['is_nullable']) {
            $selectLabel = '-- Seleccionar --';
            if ($this->handler && $this->handler->getTranslator()) {
                $selectLabel = $this->handler->getTranslator()->t('common.select');
            }
            $html .= sprintf('<option value="">%s</option>', htmlspecialchars($selectLabel));
        }
        
        foreach ($column['enum_values'] as $enumValue) {
            $selected = $value == $enumValue ? ' selected' : '';
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($enumValue),
                $selected,
                htmlspecialchars(ucfirst($enumValue))
            );
        }
        
        $html .= '</select>';
        return $html;
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
            $selectLabel = '-- Seleccionar --';
            if ($this->handler && $this->handler->getTranslator()) {
                $selectLabel = $this->handler->getTranslator()->t('common.select');
            }
            $html .= sprintf('<option value="">%s</option>', htmlspecialchars($selectLabel));
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
        $html = '<link rel="stylesheet" href="assets/dynamiccrud.css">';
        
        // Add translations for JavaScript
        if ($this->handler && $this->handler->getTranslator()) {
            $t = $this->handler->getTranslator();
            $translations = [
                'required' => $t->t('validation.required', ['field' => '']),
                'email' => $t->t('validation.email', ['field' => '']),
                'url' => $t->t('validation.url', ['field' => '']),
                'number' => $t->t('validation.number', ['field' => '']),
                'min' => $t->t('validation.min', ['field' => '', 'min' => '']),
                'max' => $t->t('validation.max', ['field' => '', 'max' => '']),
                'minlength' => $t->t('validation.minlength', ['field' => '', 'minlength' => '']),
                'maxlength' => $t->t('validation.maxlength', ['field' => '', 'maxlength' => '']),
            ];
            $html .= '<script>window.DynamicCRUDTranslations = ' . json_encode($translations) . ';</script>';
        }
        
        $html .= '<script src="assets/dynamiccrud.js" defer></script>';
        
        // Add M:N assets if needed
        if ($this->handler && !empty($this->handler->getManyToManyRelations())) {
            $html .= '<link rel="stylesheet" href="assets/manytomany.css">';
            $html .= '<script src="assets/manytomany.js" defer></script>';
        }
        
        return $html;
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
    
    private function renderManyToManyFields(): string
    {
        $html = '';
        $relations = $this->handler->getManyToManyRelations();
        
        foreach ($relations as $fieldName => $relation) {
            $pk = $this->schema['primary_key'];
            $recordId = $this->data[$pk] ?? null;
            
            // Obtener valores seleccionados
            $selectedValues = [];
            if ($recordId) {
                $selectedValues = $this->handler->getManyToManyValues($recordId, $fieldName);
            }
            
            // Obtener opciones disponibles
            $options = $this->getManyToManyOptions($relation['related_table']);
            
            $label = ucfirst(str_replace('_', ' ', $fieldName));
            
            // Check if advanced UI is requested
            $useAdvancedUI = ($relation['ui_type'] ?? 'checkboxes') === 'checkboxes';
            
            if ($useAdvancedUI) {
                $html .= $this->renderManyToManyCheckboxes($fieldName, $label, $options, $selectedValues);
            } else {
                $html .= $this->renderManyToManySelect($fieldName, $label, $options, $selectedValues);
            }
        }
        
        return $html;
    }
    
    private function renderManyToManyCheckboxes(string $fieldName, string $label, array $options, array $selectedValues): string
    {
        $translator = $this->handler ? $this->handler->getTranslator() : null;
        $selectAllLabel = $translator ? $translator->t('m2n.select_all') : 'Seleccionar visibles';
        $clearAllLabel = $translator ? $translator->t('m2n.clear_all') : 'Limpiar todo';
        $searchPlaceholder = $translator ? $translator->t('m2n.search') : 'Buscar...';
        
        $html = '<div class="form-group">' . "\n";
        $html .= sprintf('  <label>%s</label>', htmlspecialchars($label)) . "\n";
        $html .= '  <div class="m2m-container">' . "\n";
        $html .= '    <div class="m2m-actions">' . "\n";
        $html .= sprintf('      <button type="button" class="m2m-select-all">%s</button>', htmlspecialchars($selectAllLabel)) . "\n";
        $html .= sprintf('      <button type="button" class="m2m-clear-all">%s</button>', htmlspecialchars($clearAllLabel)) . "\n";
        $html .= '    </div>' . "\n";
        $html .= sprintf('    <input type="text" class="m2m-search" placeholder="%s">', htmlspecialchars($searchPlaceholder)) . "\n";
        $html .= '    <div class="m2m-options">' . "\n";
        
        foreach ($options as $option) {
            $checked = in_array($option['value'], $selectedValues) ? ' checked' : '';
            $html .= '      <div class="m2m-option">' . "\n";
            $html .= sprintf(
                '        <input type="checkbox" name="%s[]" id="%s_%s" value="%s"%s>' . "\n",
                $fieldName,
                $fieldName,
                $option['value'],
                htmlspecialchars($option['value']),
                $checked
            );
            $html .= sprintf(
                '        <label for="%s_%s">%s</label>' . "\n",
                $fieldName,
                $option['value'],
                htmlspecialchars($option['label'])
            );
            $html .= '      </div>' . "\n";
        }
        
        $html .= '    </div>' . "\n";
        
        $statsTemplate = ':count de :total seleccionados';
        if ($translator) {
            $statsTemplate = $translator->t('m2n.selected');
        }
        $html .= sprintf('    <div class="m2m-stats" data-template="%s"></div>', htmlspecialchars($statsTemplate)) . "\n";
        $html .= '  </div>' . "\n";
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderManyToManySelect(string $fieldName, string $label, array $options, array $selectedValues): string
    {
        $html = '<div class="form-group">' . "\n";
        $html .= sprintf('  <label for="%s">%s</label>', $fieldName, htmlspecialchars($label)) . "\n";
        $html .= sprintf('  <select name="%s[]" id="%s" multiple size="5" style="height: auto;">', $fieldName, $fieldName) . "\n";
        
        foreach ($options as $option) {
            $selected = in_array($option['value'], $selectedValues) ? ' selected' : '';
            $html .= sprintf(
                '    <option value="%s"%s>%s</option>',
                htmlspecialchars($option['value']),
                $selected,
                htmlspecialchars($option['label'])
            ) . "\n";
        }
        
        $html .= '  </select>' . "\n";
        
        $hintLabel = 'Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples';
        if ($this->handler && $this->handler->getTranslator()) {
            $hintLabel = $this->handler->getTranslator()->t('m2n.hint');
        }
        $html .= sprintf('  <small style="color: #666; display: block; margin-top: 4px;">%s</small>', htmlspecialchars($hintLabel)) . "\n";
        $html .= '</div>';
        
        return $html;
    }
    
    private function getManyToManyOptions(string $table): array
    {
        if (!$this->pdo) {
            return [];
        }
        
        // Intentar detectar columna de nombre
        $nameColumn = 'name';
        $sql = sprintf("SELECT id as value, %s as label FROM %s ORDER BY %s", $nameColumn, $table, $nameColumn);
        
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Si falla, intentar con 'title'
            try {
                $sql = sprintf("SELECT id as value, title as label FROM %s ORDER BY title", $table);
                $stmt = $this->pdo->query($sql);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                return [];
            }
        }
    }
    
    private function renderVirtualFields(): string
    {
        $html = '';
        $virtualFields = $this->handler->getVirtualFields();
        
        foreach ($virtualFields as $field) {
            $html .= $this->renderVirtualField($field) . "\n";
        }
        
        return $html;
    }
    
    private function renderVirtualField(VirtualField $field): string
    {
        $name = $field->getName();
        $type = $field->getType();
        $label = $field->getLabel();
        $required = $field->isRequired();
        $attributes = $field->getAttributes();
        
        $html = '<div class="form-group">' . "\n";
        $html .= sprintf('  <label for="%s">%s', $name, htmlspecialchars($label));
        
        if (isset($attributes['tooltip'])) {
            $html .= sprintf(
                ' <span class="tooltip" tabindex="0"><span class="tooltip-icon" aria-label="Ayuda">?</span><span class="tooltip-text" role="tooltip">%s</span></span>',
                htmlspecialchars($attributes['tooltip'])
            );
        }
        
        $html .= '</label>' . "\n";
        
        $attrs = [];
        if ($required) {
            $attrs[] = 'required';
            $attrs[] = 'aria-required="true"';
        }
        
        if (isset($attributes['placeholder'])) {
            $attrs[] = sprintf('placeholder="%s"', htmlspecialchars($attributes['placeholder']));
        }
        
        if (isset($attributes['pattern'])) {
            $attrs[] = sprintf('pattern="%s"', htmlspecialchars($attributes['pattern']));
        }
        
        if (isset($attributes['minlength'])) {
            $attrs[] = sprintf('minlength="%d"', $attributes['minlength']);
        }
        
        if (isset($attributes['maxlength'])) {
            $attrs[] = sprintf('maxlength="%d"', $attributes['maxlength']);
        }
        
        $attrString = $attrs ? ' ' . implode(' ', $attrs) : '';
        
        if ($type === 'checkbox') {
            $html .= sprintf(
                '  <input type="checkbox" name="%s" id="%s" value="1"%s>',
                $name,
                $name,
                $attrString
            ) . "\n";
        } else {
            $html .= sprintf(
                '  <input type="%s" name="%s" id="%s"%s>',
                $type,
                $name,
                $name,
                $attrString
            ) . "\n";
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
