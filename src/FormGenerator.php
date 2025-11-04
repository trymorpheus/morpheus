<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\I18n\Translator;
use DynamicCRUD\Template\TemplateEngine;
use DynamicCRUD\UI\Components;

class FormGenerator
{
    private array $schema;
    private array $data;
    private string $csrfToken;
    private ?PDO $pdo;
    private ?CRUDHandler $handler;

    private ?Translator $translator = null;
    private ?TemplateEngine $templateEngine = null;
    private $tableMetadata = null;
    private ?ThemeManager $themeManager = null;
    
    public function __construct(array $schema, array $data = [], string $csrfToken = '', ?PDO $pdo = null, ?CRUDHandler $handler = null)
    {
        $this->schema = $schema;
        $this->data = $data;
        $this->csrfToken = $csrfToken;
        $this->pdo = $pdo;
        $this->handler = $handler;
    }
    
    public function setTranslator(Translator $translator): self
    {
        $this->translator = $translator;
        return $this;
    }
    
    public function setTemplateEngine(TemplateEngine $engine): self
    {
        $this->templateEngine = $engine;
        return $this;
    }
    
    public function setTableMetadata($metadata): self
    {
        $this->tableMetadata = $metadata;
        return $this;
    }
    
    public function setThemeManager(ThemeManager $themeManager): self
    {
        $this->themeManager = $themeManager;
        return $this;
    }

    public function render(): string
    {
        $html = $this->renderTheme();
        $html .= $this->renderStyles() . "\n";
        $html .= $this->renderAssets() . "\n";
        
        // Check if we should use tabbed layout
        if ($this->tableMetadata && $this->tableMetadata->getFormLayout() === 'tabs') {
            return $html . $this->renderTabbedForm();
        }
        
        $html .= $this->renderFormOpen();
        $html .= $this->renderFormFields();
        $html .= $this->renderSubmitButton();
        $html .= '</form>' . "\n";
        $html .= $this->renderWorkflowButtons();
        
        return $html;
    }
    
    private function renderTabbedForm(): string
    {
        $tabs = $this->tableMetadata->getTabs();
        if (empty($tabs)) {
            return $this->renderFormOpen() . $this->renderFormFields() . $this->renderSubmitButton() . '</form>' . "\n" . $this->renderWorkflowButtons();
        }
        
        $tabsData = [];
        
        foreach ($tabs as $tab) {
            $content = '';
            foreach ($this->schema['columns'] as $column) {
                if ($column['is_primary'] || ($column['metadata']['hidden'] ?? false)) continue;
                if (!in_array($column['name'], $tab['fields'])) continue;
                $content .= $this->renderField($column) . "\n";
            }
            $tabsData[] = [
                'id' => $tab['name'],
                'title' => $tab['name'],
                'content' => $content
            ];
        }
        
        $html = $this->renderFormOpen();
        $html .= Components::tabs($tabsData, 'form-tabs');
        
        if ($this->handler) {
            $html .= $this->renderVirtualFields() . "\n";
            $html .= $this->renderManyToManyFields() . "\n";
        }
        
        $html .= $this->renderSubmitButton();
        $html .= '</form>' . "\n";
        $html .= $this->renderWorkflowButtons();
        
        return $html;
    }

    private function renderTheme(): string
    {
        if (!$this->themeManager) {
            return '';
        }
        return $this->themeManager->renderCSSVariables() . $this->themeManager->renderBranding();
    }
    
    private function renderFormOpen(): string
    {
        $enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
        $html = '<form method="POST" class="dynamic-crud-form"' . $enctype . '>' . "\n";
        $html .= sprintf('<input type="hidden" name="csrf_token" value="%s">', htmlspecialchars($this->csrfToken)) . "\n";
        
        $pk = $this->schema['primary_key'];
        if (!empty($this->data[$pk])) {
            $html .= sprintf('<input type="hidden" name="id" value="%s">', htmlspecialchars($this->data[$pk])) . "\n";
        }
        
        return $html;
    }
    
    private function renderFormFields(): string
    {
        $html = '';
        
        foreach ($this->schema['columns'] as $column) {
            if ($column['is_primary']) continue;
            if ($column['metadata']['hidden'] ?? false) continue;
            $html .= $this->renderField($column) . "\n";
        }
        
        if ($this->handler) {
            $html .= $this->renderVirtualFields() . "\n";
            $html .= $this->renderManyToManyFields() . "\n";
        }
        
        return $html;
    }
    
    private function renderSubmitButton(): string
    {
        $submitLabel = 'Guardar';
        if ($this->handler && $this->handler->getTranslator()) {
            $submitLabel = $this->handler->getTranslator()->t('form.submit');
        }
        return sprintf('<button type="submit" style="padding: 12px 30px; font-size: 16px; font-weight: 600; color: white; background-color: var(--primary-color, #667eea); border: none; border-radius: 4px; cursor: pointer; transition: opacity 0.2s;">%s</button>' . "\n", htmlspecialchars($submitLabel));
    }
    
    private function renderWorkflowButtons(): string
    {
        if (!$this->handler || !$this->handler->getWorkflowEngine()) {
            return '';
        }
        
        $pk = $this->schema['primary_key'];
        $recordId = $this->data[$pk] ?? null;
        if (!$recordId) {
            return '';
        }
        
        $user = null;
        if ($this->handler->getPermissionManager()) {
            $userId = $this->handler->getPermissionManager()->getCurrentUserId();
            $role = $this->handler->getPermissionManager()->getCurrentRole();
            if ($userId) {
                $user = ['id' => $userId, 'role' => $role];
            }
        }
        
        return $this->handler->getWorkflowEngine()->renderTransitionButtons($recordId, $user) . "\n";
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

    private function renderStyles(): string
    {
        $css = '<style>
        .dynamic-crud-form { max-width: 800px; margin: 20px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary-color, #667eea); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .tooltip { position: relative; display: inline-block; margin-left: 5px; }
        .tooltip-icon { display: inline-block; width: 16px; height: 16px; background: var(--primary-color, #667eea); color: white; border-radius: 50%; text-align: center; line-height: 16px; font-size: 12px; cursor: help; }
        .tooltip-text { visibility: hidden; position: absolute; z-index: 1; bottom: 125%; left: 50%; margin-left: -100px; width: 200px; background: #333; color: white; text-align: center; padding: 8px; border-radius: 4px; font-size: 12px; font-weight: normal; }
        .tooltip:hover .tooltip-text, .tooltip:focus .tooltip-text { visibility: visible; }
        .file-info { margin-top: 8px; font-size: 14px; color: #666; }
        .file-preview img { max-width: 200px; margin-top: 10px; border-radius: 4px; }
        .m2m-container { border: 1px solid #ddd; border-radius: 4px; padding: 15px; background: #f9f9f9; }
        .m2m-actions { margin-bottom: 10px; display: flex; gap: 10px; }
        .m2m-search { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .m2m-options { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: white; padding: 10px; }
        .m2m-option { padding: 6px; display: flex; align-items: center; gap: 8px; }
        .m2m-option:hover { background: #f5f5f5; }
        .m2m-option input[type="checkbox"] { margin: 0; }
        .m2m-option label { margin: 0; cursor: pointer; flex: 1; }
        .m2m-stats { margin-top: 10px; font-size: 13px; color: #666; text-align: right; }
        .multiple-files-container { border: 2px dashed #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9; }
        .multiple-files-container input[type="file"] { display: none; }
        .file-drop-zone { text-align: center; padding: 40px 20px; cursor: pointer; color: #666; font-size: 14px; transition: all 0.3s; }
        .file-drop-zone:hover, .file-drop-zone.drag-over { background: #e8f0fe; border-color: var(--primary-color, #667eea); color: var(--primary-color, #667eea); }
        .file-previews { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .file-preview-item { position: relative; border: 1px solid #ddd; border-radius: 4px; padding: 10px; background: white; }
        .file-preview-item img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; }
        .file-preview-item .file-name { font-size: 12px; margin-top: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-preview-item .remove-preview { position: absolute; top: 5px; right: 5px; background: var(--danger-color, #e53e3e); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; line-height: 1; }
        .existing-files { margin-top: 20px; }
        .existing-files h4 { margin-bottom: 10px; font-size: 14px; color: #333; }
        .existing-file { display: flex; align-items: center; gap: 10px; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px; }
        .existing-file img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
        .existing-file a { flex: 1; color: var(--primary-color, #667eea); text-decoration: none; }
        .existing-file a:hover { text-decoration: underline; }
        .existing-file .remove-file { background: var(--danger-color, #e53e3e); color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 18px; line-height: 1; }
        .app-branding { text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: var(--border-radius); color: white; }
        .app-logo img { max-width: 150px; margin-bottom: 10px; }
        .app-name { font-size: 24px; font-weight: 700; }
        </style>';
        
        if ($this->themeManager) {
            $css = $this->themeManager->applyThemeToStyles($css);
        }
        
        return $css;
    }
    
    private function renderAssets(): string
    {
        return $this->renderTranslations() . $this->renderJavaScript();
    }
    
    private function renderTranslations(): string
    {
        if (!$this->handler || !$this->handler->getTranslator()) {
            return '';
        }
        
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
        return '<script>window.DynamicCRUDTranslations = ' . json_encode($translations) . ';</script>';
    }
    
    private function renderJavaScript(): string
    {
        return '<script>' . $this->getMultipleFileUploadJS() . '</script>';
    }
    
    private function getMultipleFileUploadJS(): string
    {
        return '
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".multiple-files-container").forEach(container => {
                const input = container.querySelector("input[type=file]");
                const dropZone = container.querySelector(".file-drop-zone");
                const previews = container.querySelector(".file-previews");
                const maxFiles = parseInt(input.dataset.maxFiles || 10);
                let selectedFiles = [];
                
                dropZone.addEventListener("click", () => input.click());
                dropZone.addEventListener("dragover", (e) => { e.preventDefault(); dropZone.classList.add("drag-over"); });
                dropZone.addEventListener("dragleave", () => dropZone.classList.remove("drag-over"));
                dropZone.addEventListener("drop", (e) => { e.preventDefault(); dropZone.classList.remove("drag-over"); handleFiles(e.dataTransfer.files); });
                input.addEventListener("change", (e) => handleFiles(e.target.files));
                
                function handleFiles(files) {
                    if (selectedFiles.length + files.length > maxFiles) { alert("Máximo " + maxFiles + " archivos permitidos"); return; }
                    Array.from(files).forEach(file => { selectedFiles.push(file); showPreview(file); });
                    updateFileInput();
                }
                
                function showPreview(file) {
                    const div = document.createElement("div");
                    div.className = "file-preview-item";
                    if (file.type.startsWith("image/")) {
                        const img = document.createElement("img");
                        img.src = URL.createObjectURL(file);
                        div.appendChild(img);
                    }
                    const name = document.createElement("div");
                    name.className = "file-name";
                    name.textContent = file.name;
                    div.appendChild(name);
                    const removeBtn = document.createElement("button");
                    removeBtn.className = "remove-preview";
                    removeBtn.textContent = "×";
                    removeBtn.type = "button";
                    removeBtn.onclick = () => {
                        const index = selectedFiles.indexOf(file);
                        if (index > -1) { selectedFiles.splice(index, 1); div.remove(); updateFileInput(); }
                    };
                    div.appendChild(removeBtn);
                    previews.appendChild(div);
                }
                
                function updateFileInput() {
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    input.files = dt.files;
                }
                
                container.querySelectorAll(".remove-file").forEach(btn => {
                    btn.addEventListener("click", function() {
                        if (confirm("¿Eliminar este archivo?")) this.closest(".existing-file").remove();
                    });
                });
            });
        });';
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
        $type = $column['metadata']['type'] ?? null;
        return $type === 'file' || $type === 'multiple_files';
    }

    private function isMultipleFileField(array $column): bool
    {
        return ($column['metadata']['type'] ?? null) === 'multiple_files';
    }

    private function renderFileInput(array $column, $value): string
    {
        if ($this->isMultipleFileField($column)) {
            return $this->renderMultipleFileInput($column, $value);
        }

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

    private function renderMultipleFileInput(array $column, $value): string
    {
        $accept = $column['metadata']['accept'] ?? '';
        $maxFiles = $column['metadata']['max_files'] ?? 10;
        $acceptAttr = $accept ? sprintf(' accept="%s"', htmlspecialchars($accept)) : '';
        
        $html = '<div class="multiple-files-container">' . "\n";
        $html .= sprintf(
            '  <input type="file" name="%s[]" id="%s" multiple%s data-max-files="%d">',
            $column['name'],
            $column['name'],
            $acceptAttr,
            $maxFiles
        ) . "\n";
        $html .= sprintf('  <div class="file-drop-zone">Arrastra archivos aquí o haz clic para seleccionar (máx. %d)</div>', $maxFiles) . "\n";
        $html .= '  <div class="file-previews"></div>' . "\n";
        
        // Existing files
        if ($value) {
            $files = is_string($value) ? json_decode($value, true) : $value;
            if (is_array($files) && !empty($files)) {
                $html .= '  <div class="existing-files">' . "\n";
                $html .= '    <h4>Archivos actuales:</h4>' . "\n";
                foreach ($files as $index => $file) {
                    $html .= '    <div class="existing-file">' . "\n";
                    if ($this->isImage($file)) {
                        $html .= sprintf('      <img src="%s" alt="File %d">', htmlspecialchars($file), $index + 1) . "\n";
                    }
                    $html .= sprintf('      <a href="%s" target="_blank">%s</a>', htmlspecialchars($file), htmlspecialchars(basename($file))) . "\n";
                    $html .= sprintf('      <button type="button" class="remove-file" data-file="%s">×</button>', htmlspecialchars($file)) . "\n";
                    $html .= sprintf('      <input type="hidden" name="%s_existing[]" value="%s">', $column['name'], htmlspecialchars($file)) . "\n";
                    $html .= '    </div>' . "\n";
                }
                $html .= '  </div>' . "\n";
            }
        }
        
        $html .= '</div>';
        
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
        $statsTemplate = $translator ? $translator->t('m2n.selected') : ':count de :total seleccionados';
        
        $checkboxes = '';
        foreach ($options as $option) {
            $checked = in_array($option['value'], $selectedValues) ? ' checked' : '';
            $checkboxes .= sprintf(
                '<div class="m2m-option"><input type="checkbox" name="%s[]" id="%s_%s" value="%s"%s><label for="%s_%s">%s</label></div>',
                $fieldName, $fieldName, $option['value'], htmlspecialchars($option['value']), $checked,
                $fieldName, $option['value'], htmlspecialchars($option['label'])
            ) . "\n";
        }
        
        $selectBtn = sprintf('<button type="button" class="m2m-select-all" style="padding: 6px 12px; font-size: 13px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">%s</button>', htmlspecialchars($selectAllLabel));
        $clearBtn = sprintf('<button type="button" class="m2m-clear-all" style="padding: 6px 12px; font-size: 13px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">%s</button>', htmlspecialchars($clearAllLabel));
        
        $content = sprintf(
            '<div class="m2m-actions">%s%s</div><input type="text" class="m2m-search" placeholder="%s"><div class="m2m-options">%s</div><div class="m2m-stats" data-template="%s"></div>',
            $selectBtn,
            $clearBtn,
            htmlspecialchars($searchPlaceholder),
            $checkboxes,
            htmlspecialchars($statsTemplate)
        );
        
        return '<div class="form-group"><label>' . htmlspecialchars($label) . '</label><div class="m2m-container">' . $content . '</div></div>';
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
