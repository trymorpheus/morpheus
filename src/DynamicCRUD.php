<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;

class DynamicCRUD
{
    private CRUDHandler $handler;

    public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
    {
        $this->handler = new CRUDHandler($pdo, $table, $cache);
    }

    public function renderForm(?int $id = null): string
    {
        return $this->handler->renderForm($id);
    }

    public function handleSubmission(): array
    {
        return $this->handler->handleSubmission();
    }

    public function list(array $options = []): array
    {
        return $this->handler->list($options);
    }

    public function delete(int $id): bool
    {
        return $this->handler->delete($id);
    }
    
    // Métodos de Hooks/Eventos
    
    public function on(string $event, callable $callback): self
    {
        $this->handler->on($event, $callback);
        return $this;
    }
    
    public function beforeValidate(callable $callback): self
    {
        $this->handler->beforeValidate($callback);
        return $this;
    }
    
    public function afterValidate(callable $callback): self
    {
        $this->handler->afterValidate($callback);
        return $this;
    }
    
    public function beforeSave(callable $callback): self
    {
        $this->handler->beforeSave($callback);
        return $this;
    }
    
    public function afterSave(callable $callback): self
    {
        $this->handler->afterSave($callback);
        return $this;
    }
    
    public function beforeCreate(callable $callback): self
    {
        $this->handler->beforeCreate($callback);
        return $this;
    }
    
    public function afterCreate(callable $callback): self
    {
        $this->handler->afterCreate($callback);
        return $this;
    }
    
    public function beforeUpdate(callable $callback): self
    {
        $this->handler->beforeUpdate($callback);
        return $this;
    }
    
    public function afterUpdate(callable $callback): self
    {
        $this->handler->afterUpdate($callback);
        return $this;
    }
    
    public function beforeDelete(callable $callback): self
    {
        $this->handler->beforeDelete($callback);
        return $this;
    }
    
    public function afterDelete(callable $callback): self
    {
        $this->handler->afterDelete($callback);
        return $this;
    }
    
    // Métodos para Relaciones Muchos a Muchos
    
    public function addManyToMany(string $fieldName, string $pivotTable, string $localKey, string $foreignKey, string $relatedTable): self
    {
        $this->handler->addManyToMany($fieldName, $pivotTable, $localKey, $foreignKey, $relatedTable);
        return $this;
    }
    
    // Métodos para Auditoría
    
    public function enableAudit(?int $userId = null): self
    {
        $this->handler->enableAudit($userId);
        return $this;
    }
    
    public function getAuditHistory(int $recordId): array
    {
        return $this->handler->getAuditHistory($recordId);
    }
}
