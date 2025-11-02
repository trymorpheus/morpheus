<?php

namespace DynamicCRUD;

use DynamicCRUD\Cache\CacheStrategy;
use DynamicCRUD\I18n\Translator;
use DynamicCRUD\Template\TemplateEngine;
use PDO;

class DynamicCRUD
{
    private PDO $pdo;
    private string $table;
    private CRUDHandler $handler;
    private ListGenerator $listGenerator;
    private ?CacheStrategy $cache;
    private ?string $uploadDir;
    private ?Translator $translator = null;
    private ?TemplateEngine $templateEngine = null;

    public function __construct(
        PDO $pdo, 
        string $table, 
        ?CacheStrategy $cache = null, 
        ?string $uploadDir = null,
        ?string $locale = null,
        ?TemplateEngine $templateEngine = null
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->cache = $cache;
        $this->uploadDir = $uploadDir;
        $this->templateEngine = $templateEngine;
        
        $this->translator = new Translator($locale);
        
        $this->handler = new CRUDHandler($pdo, $table, $cache, $uploadDir);
        $this->handler->setTranslator($this->translator);
        
        $analyzer = new SchemaAnalyzer($pdo, $cache);
        $schema = $analyzer->getTableSchema($table);
        
        $this->listGenerator = new ListGenerator($pdo, $schema);
    }

    public function renderForm(?int $id = null): string
    {
        $data = [];
        if ($id !== null) {
            $pk = $this->handler->getSchema()['primary_key'] ?? 'id';
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$pk} = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }
        
        $security = new SecurityModule();
        $csrfToken = $security->generateCsrfToken();
        
        $generator = new FormGenerator(
            $this->handler->getSchema(),
            $data,
            $csrfToken,
            $this->pdo,
            $this->handler
        );
        $generator->setTranslator($this->translator);
        if ($this->templateEngine) {
            $generator->setTemplateEngine($this->templateEngine);
        }
        
        return $generator->render();
    }

    public function handleSubmission(): array
    {
        return $this->handler->handleSubmission($_POST, $_FILES);
    }

    public function renderList(int $page = 1, int $perPage = 10, array $filters = [], ?string $sortBy = null, string $sortDir = 'ASC'): string
    {
        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'filters' => $filters,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir
        ];
        
        $result = $this->listGenerator->list($options);
        
        $html = $this->listGenerator->renderTable($result['data']);
        $html .= $this->listGenerator->renderPagination($result['pagination']);
        
        return $html;
    }

    public function addManyToMany(
        string $fieldName,
        string $pivotTable,
        string $localKey,
        string $foreignKey,
        string $relatedTable,
        string $displayColumn = 'name',
        string $ui_type = 'checkboxes'
    ): self {
        $this->handler->addManyToMany($fieldName, $pivotTable, $localKey, $foreignKey, $relatedTable, $ui_type);
        return $this;
    }

    public function addHook(string $event, callable $callback): self
    {
        $this->handler->on($event, $callback);
        return $this;
    }

    public function beforeValidate(callable $callback): self
    {
        return $this->addHook('beforeValidate', $callback);
    }

    public function afterValidate(callable $callback): self
    {
        return $this->addHook('afterValidate', $callback);
    }

    public function beforeSave(callable $callback): self
    {
        return $this->addHook('beforeSave', $callback);
    }

    public function afterSave(callable $callback): self
    {
        return $this->addHook('afterSave', $callback);
    }

    public function beforeCreate(callable $callback): self
    {
        return $this->addHook('beforeCreate', $callback);
    }

    public function afterCreate(callable $callback): self
    {
        return $this->addHook('afterCreate', $callback);
    }

    public function beforeUpdate(callable $callback): self
    {
        return $this->addHook('beforeUpdate', $callback);
    }

    public function afterUpdate(callable $callback): self
    {
        return $this->addHook('afterUpdate', $callback);
    }

    public function beforeDelete(callable $callback): self
    {
        return $this->addHook('beforeDelete', $callback);
    }

    public function afterDelete(callable $callback): self
    {
        return $this->addHook('afterDelete', $callback);
    }

    public function enableAudit(int $userId): self
    {
        $auditLogger = new AuditLogger($this->pdo);
        $auditLogger->setUserId($userId);
        $this->handler->setAuditLogger($auditLogger);
        return $this;
    }

    public function addVirtualField(VirtualField $field): self
    {
        $this->handler->addVirtualField($field);
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->translator = new Translator($locale);
        $this->handler->setTranslator($this->translator);
        return $this;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    public function setTemplateEngine(TemplateEngine $engine): self
    {
        $this->templateEngine = $engine;
        return $this;
    }

    public function getTemplateEngine(): ?TemplateEngine
    {
        return $this->templateEngine;
    }
}
