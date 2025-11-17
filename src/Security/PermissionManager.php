<?php

namespace Morpheus\Security;

use PDO;

class PermissionManager
{
    private PDO $pdo;
    private string $table;
    private array $permissions;
    private ?array $rowLevelSecurity;
    private ?string $currentUserRole = null;
    private ?int $currentUserId = null;

    public function __construct(PDO $pdo, string $table, array $metadata = [])
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->permissions = $metadata['permissions'] ?? [];
        $this->rowLevelSecurity = $metadata['row_level_security'] ?? null;
        
        $this->initializeCurrentUser();
    }

    private function initializeCurrentUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->currentUserRole = $_SESSION['user_role'] ?? 'guest';
        $this->currentUserId = $_SESSION['user_id'] ?? null;
    }

    public function setCurrentUser(?int $userId, ?string $role): void
    {
        $this->currentUserId = $userId;
        $this->currentUserRole = $role ?? 'guest';
        
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
    }

    public function getCurrentUserId(): ?int
    {
        return $this->currentUserId;
    }

    public function getCurrentUserRole(): string
    {
        return $this->currentUserRole ?? 'guest';
    }
    
    public function getCurrentRole(): string
    {
        return $this->getCurrentUserRole();
    }

    public function can(string $action, ?array $record = null): bool
    {
        $hasTablePermission = $this->hasTablePermission($action);
        
        // If has table permission, accept (no need to check row-level)
        if ($hasTablePermission) {
            return true;
        }
        
        // If no table permission, check if row-level security allows it
        if ($record !== null && $this->rowLevelSecurity !== null && ($this->rowLevelSecurity['enabled'] ?? false)) {
            return $this->hasRowPermission($action, $record);
        }
        
        return false;
    }

    private function hasTablePermission(string $action): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        $allowedRoles = $this->permissions[$action] ?? [];

        if (in_array('*', $allowedRoles)) {
            return true;
        }

        return in_array($this->currentUserRole, $allowedRoles);
    }

    private function hasRowPermission(string $action, array $record): bool
    {
        if (!$this->rowLevelSecurity['enabled'] ?? false) {
            return true;
        }

        $ownerField = $this->rowLevelSecurity['owner_field'] ?? 'user_id';
        $recordOwnerId = $record[$ownerField] ?? null;

        if ($recordOwnerId === null) {
            return true;
        }

        $isOwner = $this->currentUserId !== null && $recordOwnerId == $this->currentUserId;

        if (!$isOwner) {
            return false;
        }

        if ($action === 'update' || $action === 'edit') {
            return $this->rowLevelSecurity['owner_can_edit'] ?? true;
        }

        if ($action === 'delete') {
            return $this->rowLevelSecurity['owner_can_delete'] ?? false;
        }

        return true;
    }

    public function canCreate(): bool
    {
        return $this->can('create');
    }

    public function canRead(?array $record = null): bool
    {
        return $this->can('read', $record);
    }

    public function canUpdate(?array $record = null): bool
    {
        return $this->can('update', $record);
    }

    public function canDelete(?array $record = null): bool
    {
        return $this->can('delete', $record);
    }

    public function filterRecordsByPermission(array $records): array
    {
        if ($this->rowLevelSecurity === null || !($this->rowLevelSecurity['enabled'] ?? false)) {
            return $records;
        }

        return array_filter($records, fn($record) => $this->canRead($record));
    }

    public function applyRowLevelFilter(string $sql, array $params = []): array
    {
        if ($this->rowLevelSecurity === null || !($this->rowLevelSecurity['enabled'] ?? false)) {
            return ['sql' => $sql, 'params' => $params];
        }

        if ($this->hasTablePermission('read')) {
            return ['sql' => $sql, 'params' => $params];
        }

        $ownerField = $this->rowLevelSecurity['owner_field'] ?? 'user_id';
        
        if (stripos($sql, 'WHERE') !== false) {
            $sql .= " AND {$ownerField} = :__rls_user_id";
        } else {
            $sql .= " WHERE {$ownerField} = :__rls_user_id";
        }

        $params['__rls_user_id'] = $this->currentUserId;

        return ['sql' => $sql, 'params' => $params];
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getRowLevelSecurity(): ?array
    {
        return $this->rowLevelSecurity;
    }

    public function hasRowLevelSecurity(): bool
    {
        return $this->rowLevelSecurity !== null && ($this->rowLevelSecurity['enabled'] ?? false);
    }
}
