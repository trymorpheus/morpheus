<?php

namespace DynamicCRUD;

use DynamicCRUD\Cache\CacheStrategy;
use DynamicCRUD\I18n\Translator;
use DynamicCRUD\Template\TemplateEngine;
use DynamicCRUD\Metadata\TableMetadata;
use DynamicCRUD\Security\PermissionManager;
use PDO;

class DynamicCRUD
{
    private PDO $pdo;
    private string $table;
    private CRUDHandler $handler;

    private ?CacheStrategy $cache;
    private ?string $uploadDir;
    private ?Translator $translator = null;
    private ?TemplateEngine $templateEngine = null;
    private TableMetadata $tableMetadata;
    private array $schema;
    private PermissionManager $permissionManager;

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
        $this->tableMetadata = new TableMetadata($pdo, $table);
        
        $metadata = [
            'permissions' => $this->tableMetadata->getPermissions(),
            'row_level_security' => $this->tableMetadata->getRowLevelSecurity()
        ];
        $this->permissionManager = new PermissionManager($pdo, $table, $metadata);
        
        $this->handler = new CRUDHandler($pdo, $table, $cache, $uploadDir);
        $this->handler->setTranslator($this->translator);
        $this->handler->setPermissionManager($this->permissionManager);
        
        $analyzer = new SchemaAnalyzer($pdo, $cache);
        $this->schema = $analyzer->getTableSchema($table);
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
            $this->schema,
            $data,
            $csrfToken,
            $this->pdo,
            $this->handler
        );
        $generator->setTableMetadata($this->tableMetadata);
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

    public function renderList(array $options = []): string
    {
        $generator = new ListGenerator($this->pdo, $this->table, $this->schema, $this->tableMetadata, $this->permissionManager);
        return $generator->render($options);
    }
    
    public function getTableMetadata(): TableMetadata
    {
        return $this->tableMetadata;
    }
    
    public function getHandler(): CRUDHandler
    {
        return $this->handler;
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
    
    public function delete(int $id): bool
    {
        return $this->handler->delete($id);
    }
    
    public function list(array $options = []): array
    {
        return $this->handler->list($options);
    }
    
    public function getAuditHistory(int $recordId): array
    {
        return $this->handler->getAuditHistory($recordId);
    }
    
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getPermissionManager(): PermissionManager
    {
        return $this->permissionManager;
    }

    public function setCurrentUser(?int $userId, ?string $role): self
    {
        $this->permissionManager->setCurrentUser($userId, $role);
        return $this;
    }
}
