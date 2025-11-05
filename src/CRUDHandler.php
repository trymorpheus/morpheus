<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;
use DynamicCRUD\I18n\Translator;
use DynamicCRUD\Security\PermissionManager;
use DynamicCRUD\Workflow\WorkflowEngine;

class CRUDHandler
{
    private PDO $pdo;
    private string $table;
    private SchemaAnalyzer $analyzer;
    private SecurityModule $security;
    private FileUploadHandler $fileHandler;
    private array $schema;
    private array $hooks = [];
    private array $manyToManyRelations = [];
    private ?AuditLogger $auditLogger = null;
    private array $virtualFields = [];
    private ?Translator $translator = null;
    private $tableMetadata = null;
    private ?PermissionManager $permissionManager = null;
    private ?NotificationManager $notificationManager = null;
    private ?WorkflowEngine $workflowEngine = null;

    public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null, ?string $uploadDir = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->analyzer = new SchemaAnalyzer($pdo, $cache);
        $this->security = new SecurityModule();
        
        if ($uploadDir === null) {
            $uploadDir = __DIR__ . '/../examples/uploads';
        }
        
        $this->fileHandler = new FileUploadHandler($uploadDir);
        $this->schema = $this->analyzer->getTableSchema($table);
        $this->tableMetadata = new Metadata\TableMetadata($pdo, $table);
        
        // Initialize notifications if configured
        if ($this->tableMetadata->hasNotifications()) {
            $this->notificationManager = new NotificationManager();
        }
    }

    public function renderForm(?int $id = null): string
    {
        $data = [];
        
        if ($id !== null) {
            $data = $this->findById($id);
        }
        
        $csrfToken = $this->security->generateCsrfToken();
        $generator = new FormGenerator($this->schema, $data, $csrfToken, $this->pdo, $this);
        
        return $generator->render();
    }

    public function handleSubmission(): array
    {
        if ($workflowResult = $this->handleWorkflowTransition()) {
            return $workflowResult;
        }
        
        if ($error = $this->validateCsrfToken()) {
            return $error;
        }
        
        $isUpdate = isset($_POST['id']) && $_POST['id'];
        
        if ($error = $this->checkPermissions($isUpdate)) {
            return $error;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $data = $this->prepareData();
            $virtualData = $this->extractVirtualData();
            
            if ($fileError = $this->handleFileUploads($data)) {
                return $fileError;
            }
            
            if ($validationError = $this->validateData($data, $virtualData, $isUpdate)) {
                $this->pdo->rollBack();
                return $validationError;
            }
            
            $data = $this->applyAutomaticBehaviors($data, $isUpdate);
            $data = $this->executeHook('beforeSave', $data);
            
            $id = $isUpdate ? $this->performUpdate((int)$_POST['id'], $data) : $this->performCreate($data);
            
            $this->executeHook('afterSave', $id, $data);
            $this->syncManyToManyRelations($id);
            
            $this->pdo->commit();
            return ['success' => true, 'id' => $id];
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function handleWorkflowTransition(): ?array
    {
        if (!isset($_POST['workflow_transition']) || !isset($_POST['workflow_id'])) {
            return null;
        }
        
        if (!$this->workflowEngine) {
            return ['success' => false, 'error' => 'Workflow not enabled'];
        }
        
        $user = null;
        if ($this->permissionManager) {
            $userId = $this->permissionManager->getCurrentUserId();
            $role = $this->permissionManager->getCurrentRole();
            if ($userId) {
                $user = ['id' => $userId, 'role' => $role];
            }
        }
        
        return $this->workflowEngine->transition(
            (int)$_POST['workflow_id'],
            $_POST['workflow_transition'],
            $user
        );
    }
    
    private function validateCsrfToken(): ?array
    {
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$this->security->validateCsrfToken($csrfToken)) {
            $error = $this->translator ? $this->translator->t('error.csrf_invalid') : 'Token CSRF inválido';
            return ['success' => false, 'error' => $error];
        }
        
        return null;
    }
    
    private function checkPermissions(bool $isUpdate): ?array
    {
        if (!$this->permissionManager) {
            return null;
        }
        
        if ($isUpdate) {
            $record = $this->findById((int)$_POST['id']);
            if (!$this->permissionManager->canUpdate($record)) {
                return $this->permissionDeniedError();
            }
        } else {
            if (!$this->permissionManager->canCreate()) {
                return $this->permissionDeniedError();
            }
        }
        
        return null;
    }
    
    private function permissionDeniedError(): array
    {
        $error = $this->translator ? $this->translator->t('error.permission_denied') : 'No tienes permiso para realizar esta acción';
        return ['success' => false, 'error' => $error];
    }
    
    private function prepareData(): array
    {
        $allowedColumns = array_map(
            fn($col) => $col['name'],
            array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
        );
        
        return $this->security->sanitizeInput($_POST, $allowedColumns, $this->schema);
    }
    
    private function extractVirtualData(): array
    {
        $virtualData = [];
        foreach ($this->virtualFields as $virtualField) {
            $fieldName = $virtualField->getName();
            if (isset($_POST[$fieldName])) {
                $virtualData[$fieldName] = $_POST[$fieldName];
            }
        }
        return $virtualData;
    }
    
    private function handleFileUploads(array &$data): ?array
    {
        foreach ($this->schema['columns'] as $column) {
            $fieldType = $column['metadata']['type'] ?? null;
            
            if ($fieldType === 'multiple_files') {
                if ($error = $this->handleMultipleFiles($column, $data)) {
                    return $error;
                }
            } elseif ($fieldType === 'file') {
                if ($error = $this->handleSingleFile($column, $data)) {
                    return $error;
                }
            }
        }
        
        return null;
    }
    
    private function handleMultipleFiles(array $column, array &$data): ?array
    {
        try {
            $uploadedFiles = $this->fileHandler->handleMultipleUploads($column['name'], $column['metadata']);
            $existingFiles = $_POST[$column['name'] . '_existing'] ?? [];
            $allFiles = array_merge($existingFiles, $uploadedFiles);
            $data[$column['name']] = !empty($allFiles) ? json_encode($allFiles) : null;
            return null;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function handleSingleFile(array $column, array &$data): ?array
    {
        try {
            $filePath = $this->fileHandler->handleUpload($column['name'], $column['metadata']);
            if ($filePath) {
                $data[$column['name']] = $filePath;
            } elseif (!$column['is_nullable'] && empty($data[$column['name']])) {
                unset($data[$column['name']]);
            } else {
                unset($data[$column['name']]);
            }
            return null;
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function validateData(array &$data, array $virtualData, bool $isUpdate): ?array
    {
        $data = $this->executeHook('beforeValidate', $data);
        
        $validator = new ValidationEngine($this->schema, $this->translator);
        
        if (!$validator->validate($data)) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }
        
        if ($virtualErrors = $this->validateVirtualFields($virtualData, $data)) {
            return ['success' => false, 'errors' => $virtualErrors];
        }
        
        $data = $this->executeHook('afterValidate', $data);
        
        if ($ruleErrors = $this->validateAdvancedRules($data, $isUpdate)) {
            return ['success' => false, 'errors' => $ruleErrors];
        }
        
        return null;
    }
    
    private function validateVirtualFields(array $virtualData, array $data): array
    {
        $errors = [];
        foreach ($this->virtualFields as $virtualField) {
            $fieldName = $virtualField->getName();
            $value = $virtualData[$fieldName] ?? '';
            $allData = array_merge($data, $virtualData);
            
            if (!$virtualField->validate($value, $allData)) {
                $errors[$fieldName] = $virtualField->getErrorMessage();
            }
        }
        return $errors;
    }
    
    private function validateAdvancedRules(array $data, bool $isUpdate): array
    {
        if (!$this->tableMetadata || !$this->tableMetadata->hasValidationRules()) {
            return [];
        }
        
        $rulesEngine = new ValidationRulesEngine(
            $this->pdo,
            $this->table,
            $this->tableMetadata->getAllRules()
        );
        
        $ruleErrors = $rulesEngine->validate($data, $isUpdate ? (int)$_POST['id'] : null);
        if (!empty($ruleErrors)) {
            return $ruleErrors;
        }
        
        $userId = $this->getCurrentUserId();
        return $rulesEngine->validateBusinessRules($data, $userId);
    }
    
    private function getCurrentUserId(): ?int
    {
        if ($this->permissionManager) {
            return $this->permissionManager->getCurrentUserId();
        }
        
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }
        
        return null;
    }
    
    private function performUpdate(int $id, array $data): int
    {
        $data = $this->executeHook('beforeUpdate', $data, $id);
        $oldValues = $this->auditLogger ? $this->findById($id) : [];
        
        $id = $this->update($id, $data);
        
        if ($this->auditLogger) {
            $this->auditLogger->logUpdate($this->table, $id, $oldValues, $data);
        }
        
        $this->executeHook('afterUpdate', $id, $data);
        $this->sendNotifications('on_update', $data, $id);
        
        return $id;
    }
    
    private function performCreate(array $data): int
    {
        $data = $this->executeHook('beforeCreate', $data);
        $id = $this->save($data);
        
        if ($this->auditLogger) {
            $this->auditLogger->logCreate($this->table, $id, $data);
        }
        
        $this->executeHook('afterCreate', $id, $data);
        $this->sendNotifications('on_create', $data, $id);
        
        return $id;
    }
    
    private function sendNotifications(string $event, array $data, int $id): void
    {
        if (!$this->notificationManager) {
            return;
        }
        
        $config = $this->tableMetadata->getNotificationConfig();
        
        if (isset($config['notifications'][$event])) {
            $this->notificationManager->sendEmailNotifications($config['notifications'][$event], $data, $id);
        }
        
        if (isset($config['webhooks'])) {
            $this->notificationManager->triggerWebhooks($config['webhooks'], $event, $data, $id);
        }
    }

    private function save(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        }
        
        $stmt->execute();
        
        return (int) $this->pdo->lastInsertId();
    }

    private function update(int $id, array $data): int
    {
        $pk = $this->schema['primary_key'];
        $sets = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $sets),
            $pk
        );
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $id;
    }

    private function findById(int $id, bool $withTrashed = false): array
    {
        $pk = $this->schema['primary_key'];
        
        $sql = sprintf("SELECT * FROM %s WHERE %s = :id", $this->table, $pk);
        
        // Exclude soft deleted records unless withTrashed is true
        if (!$withTrashed && $this->tableMetadata && $this->tableMetadata->hasSoftDeletes()) {
            $column = $this->tableMetadata->getSoftDeleteColumn();
            $sql .= sprintf(" AND %s IS NULL", $column);
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function list(array $options = []): array
    {
        $listGenerator = new ListGenerator($this->pdo, $this->schema);
        return $listGenerator->list($options);
    }

    public function delete(int $id): bool
    {
        // Check permissions
        if ($this->permissionManager) {
            $record = $this->findById($id);
            if (!$this->permissionManager->canDelete($record)) {
                throw new \Exception($this->translator ? $this->translator->t('error.permission_denied') : 'No tienes permiso para realizar esta acción');
            }
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Hook: beforeDelete
            $this->executeHook('beforeDelete', $id);
            
            // Auditoría: guardar valores antes de eliminar
            $oldValues = $this->auditLogger ? $this->findById($id) : [];
            
            $pk = $this->schema['primary_key'];
            
            // Check if soft deletes enabled
            if ($this->tableMetadata && $this->tableMetadata->hasSoftDeletes()) {
                $column = $this->tableMetadata->getSoftDeleteColumn();
                $sql = sprintf("UPDATE %s SET %s = :deleted_at WHERE %s = :id", $this->table, $column, $pk);
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute(['deleted_at' => date('Y-m-d H:i:s'), 'id' => $id]);
            } else {
                $sql = sprintf("DELETE FROM %s WHERE %s = :id", $this->table, $pk);
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute(['id' => $id]);
            }
            
            // Auditoría: registrar DELETE
            if ($this->auditLogger && $result) {
                $this->auditLogger->logDelete($this->table, $id, $oldValues);
            }
            
            // Hook: afterDelete
            $this->executeHook('afterDelete', $id);
            
            // Notifications
            if ($this->notificationManager) {
                $config = $this->tableMetadata->getNotificationConfig();
                if (isset($config['notifications']['on_delete'])) {
                    $this->notificationManager->sendEmailNotifications($config['notifications']['on_delete'], $oldValues, $id);
                }
                if (isset($config['webhooks'])) {
                    $this->notificationManager->triggerWebhooks($config['webhooks'], 'on_delete', $oldValues, $id);
                }
            }
            
            $this->pdo->commit();
            return $result;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function restore(int $id): bool
    {
        if (!$this->tableMetadata || !$this->tableMetadata->hasSoftDeletes()) {
            throw new \Exception('Soft deletes not enabled for this table');
        }
        
        $pk = $this->schema['primary_key'];
        $column = $this->tableMetadata->getSoftDeleteColumn();
        
        $sql = sprintf("UPDATE %s SET %s = NULL WHERE %s = :id", $this->table, $column, $pk);
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
    
    public function forceDelete(int $id): bool
    {
        // Check permissions
        if ($this->permissionManager) {
            $record = $this->findById($id, true);
            if (!$this->permissionManager->canDelete($record)) {
                throw new \Exception($this->translator ? $this->translator->t('error.permission_denied') : 'No tienes permiso para realizar esta acción');
            }
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $oldValues = $this->auditLogger ? $this->findById($id, true) : [];
            
            $pk = $this->schema['primary_key'];
            $sql = sprintf("DELETE FROM %s WHERE %s = :id", $this->table, $pk);
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            if ($this->auditLogger && $result) {
                $this->auditLogger->logDelete($this->table, $id, $oldValues);
            }
            
            $this->pdo->commit();
            return $result;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // Métodos para Hooks/Eventos
    
    public function on(string $event, callable $callback): self
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }
        
        $this->hooks[$event][] = $callback;
        return $this;
    }
    
    public function beforeValidate(callable $callback): self
    {
        return $this->on('beforeValidate', $callback);
    }
    
    public function afterValidate(callable $callback): self
    {
        return $this->on('afterValidate', $callback);
    }
    
    public function beforeSave(callable $callback): self
    {
        return $this->on('beforeSave', $callback);
    }
    
    public function afterSave(callable $callback): self
    {
        return $this->on('afterSave', $callback);
    }
    
    public function beforeCreate(callable $callback): self
    {
        return $this->on('beforeCreate', $callback);
    }
    
    public function afterCreate(callable $callback): self
    {
        return $this->on('afterCreate', $callback);
    }
    
    public function beforeUpdate(callable $callback): self
    {
        return $this->on('beforeUpdate', $callback);
    }
    
    public function afterUpdate(callable $callback): self
    {
        return $this->on('afterUpdate', $callback);
    }
    
    public function beforeDelete(callable $callback): self
    {
        return $this->on('beforeDelete', $callback);
    }
    
    public function afterDelete(callable $callback): self
    {
        return $this->on('afterDelete', $callback);
    }
    
    private function executeHook(string $event, ...$args)
    {
        if (!isset($this->hooks[$event])) {
            return $args[0] ?? null;
        }
        
        $result = $args[0] ?? null;
        
        foreach ($this->hooks[$event] as $callback) {
            $result = $callback(...$args) ?? $result;
        }
        
        return $result;
    }
    
    // Métodos para Relaciones Muchos a Muchos
    
    public function addManyToMany(string $fieldName, string $pivotTable, string $localKey, string $foreignKey, string $relatedTable, string $uiType = 'checkboxes'): self
    {
        $this->manyToManyRelations[$fieldName] = [
            'pivot_table' => $pivotTable,
            'local_key' => $localKey,
            'foreign_key' => $foreignKey,
            'related_table' => $relatedTable,
            'ui_type' => $uiType
        ];
        
        return $this;
    }
    
    public function getManyToManyRelations(): array
    {
        return $this->manyToManyRelations;
    }
    
    public function getManyToManyValues(int $id, string $fieldName): array
    {
        if (!isset($this->manyToManyRelations[$fieldName])) {
            return [];
        }
        
        $relation = $this->manyToManyRelations[$fieldName];
        
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s = :id",
            $relation['foreign_key'],
            $relation['pivot_table'],
            $relation['local_key']
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function syncManyToManyRelations(int $id): void
    {
        foreach ($this->manyToManyRelations as $fieldName => $relation) {
            if (!isset($_POST[$fieldName])) {
                continue;
            }
            
            $selectedIds = is_array($_POST[$fieldName]) ? $_POST[$fieldName] : [];
            
            // Eliminar relaciones existentes
            $deleteSql = sprintf(
                "DELETE FROM %s WHERE %s = :id",
                $relation['pivot_table'],
                $relation['local_key']
            );
            
            $stmt = $this->pdo->prepare($deleteSql);
            $stmt->execute(['id' => $id]);
            
            // Insertar nuevas relaciones
            if (!empty($selectedIds)) {
                $insertSql = sprintf(
                    "INSERT INTO %s (%s, %s) VALUES (:local_id, :foreign_id)",
                    $relation['pivot_table'],
                    $relation['local_key'],
                    $relation['foreign_key']
                );
                
                $stmt = $this->pdo->prepare($insertSql);
                
                foreach ($selectedIds as $foreignId) {
                    $stmt->execute([
                        'local_id' => $id,
                        'foreign_id' => $foreignId
                    ]);
                }
            }
        }
    }
    
    // Métodos para Auditoría
    
    public function enableAudit(?int $userId = null): self
    {
        $this->auditLogger = new AuditLogger($this->pdo);
        
        if ($userId !== null) {
            $this->auditLogger->setUserId($userId);
        }
        
        return $this;
    }
    
    public function getAuditHistory(int $recordId): array
    {
        if (!$this->auditLogger) {
            return [];
        }
        
        return $this->auditLogger->getHistory($this->table, $recordId);
    }
    
    // Métodos para Campos Virtuales
    
    public function addVirtualField(VirtualField $field): self
    {
        $this->virtualFields[] = $field;
        return $this;
    }
    
    public function getVirtualFields(): array
    {
        return $this->virtualFields;
    }
    
    // Métodos para i18n
    
    public function setTranslator(Translator $translator): self
    {
        $this->translator = $translator;
        return $this;
    }
    
    public function getTranslator(): ?Translator
    {
        return $this->translator;
    }
    
    public function getSchema(): array
    {
        return $this->schema;
    }
    
    public function setAuditLogger(AuditLogger $logger): self
    {
        $this->auditLogger = $logger;
        return $this;
    }
    
    public function getTableMetadata()
    {
        return $this->tableMetadata;
    }
    
    private function applyAutomaticBehaviors(array $data, bool $isUpdate): array
    {
        if (!$this->tableMetadata) {
            return $data;
        }
        
        // Timestamps
        if ($this->tableMetadata->hasTimestamps()) {
            $timestamps = $this->tableMetadata->getTimestampFields();
            $existingColumns = array_column($this->schema['columns'], 'name');
            
            if (!$isUpdate && isset($timestamps['created_at']) && in_array($timestamps['created_at'], $existingColumns)) {
                $data[$timestamps['created_at']] = date('Y-m-d H:i:s');
            }
            
            if (isset($timestamps['updated_at']) && in_array($timestamps['updated_at'], $existingColumns)) {
                $data[$timestamps['updated_at']] = date('Y-m-d H:i:s');
            }
        }
        
        // Sluggable
        if ($this->tableMetadata->isSluggable()) {
            $config = $this->tableMetadata->getSluggableConfig();
            $source = $config['source'] ?? 'title';
            $target = $config['target'] ?? 'slug';
            $separator = $config['separator'] ?? '-';
            $lowercase = $config['lowercase'] ?? true;
            $unique = $config['unique'] ?? true;
            
            if (isset($data[$source]) && empty($data[$target])) {
                $slug = $data[$source];
                $slug = preg_replace('/[^\w\s-]/u', '', $slug);
                $slug = preg_replace('/[\s_]+/', $separator, $slug);
                $slug = trim($slug, $separator);
                
                if ($lowercase) {
                    $slug = strtolower($slug);
                }
                
                if ($unique) {
                    $slug = $this->makeSlugUnique($slug, $target, $isUpdate ? (int)$_POST['id'] : null);
                }
                
                $data[$target] = $slug;
            }
        }
        
        return $data;
    }
    
    private function makeSlugUnique(string $slug, string $column, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $column, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    private function slugExists(string $slug, string $column, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$column} = :slug";
        
        if ($excludeId !== null) {
            $pk = $this->schema['primary_key'];
            $sql .= " AND {$pk} != :id";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        
        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    
    public function setPermissionManager(PermissionManager $permissionManager): self
    {
        $this->permissionManager = $permissionManager;
        return $this;
    }
    
    public function getPermissionManager(): ?PermissionManager
    {
        return $this->permissionManager;
    }
    
    public function setWorkflowEngine(WorkflowEngine $workflowEngine): self
    {
        $this->workflowEngine = $workflowEngine;
        return $this;
    }
    
    public function getWorkflowEngine(): ?WorkflowEngine
    {
        return $this->workflowEngine;
    }
}
