<?php

namespace DynamicCRUD\Metadata;

use PDO;

class TableMetadata
{
    private PDO $pdo;
    private string $table;
    private array $metadata;
    
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->metadata = $this->loadMetadata();
    }
    
    private function loadMetadata(): array
    {
        $comment = $this->getTableComment();
        if (empty($comment)) {
            return [];
        }
        
        $decoded = json_decode($comment, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    private function getTableComment(): string
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'mysql') {
            $sql = "SELECT TABLE_COMMENT 
                    FROM information_schema.TABLES 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = :table";
        } else {
            $sql = "SELECT obj_description(oid) as table_comment
                    FROM pg_class
                    WHERE relname = :table AND relkind = 'r'";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['table' => $this->table]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['table_comment'] ?? $result['TABLE_COMMENT'] ?? '';
    }
    
    public function getDisplayName(): string
    {
        return $this->metadata['display_name'] ?? ucfirst(str_replace('_', ' ', $this->table));
    }
    
    public function getIcon(): ?string
    {
        return $this->metadata['icon'] ?? null;
    }
    
    public function getDescription(): ?string
    {
        return $this->metadata['description'] ?? null;
    }
    
    public function getColor(): ?string
    {
        return $this->metadata['color'] ?? null;
    }
    
    public function getListColumns(): array
    {
        return $this->metadata['list_view']['columns'] ?? [];
    }
    
    public function getDefaultSort(): string
    {
        return $this->metadata['list_view']['default_sort'] ?? 'id DESC';
    }
    
    public function getPerPage(): int
    {
        return $this->metadata['list_view']['per_page'] ?? 20;
    }
    
    public function getSearchableFields(): array
    {
        return $this->metadata['list_view']['searchable'] ?? [];
    }
    
    public function getActions(): array
    {
        return $this->metadata['list_view']['actions'] ?? ['edit', 'delete'];
    }
    
    public function hasCardView(): bool
    {
        return $this->metadata['list_view']['card_view'] ?? false;
    }
    
    public function getCardTemplate(): ?string
    {
        return $this->metadata['card_template'] ?? null;
    }
    
    // Form Methods
    
    public function getFormLayout(): string
    {
        return $this->metadata['form']['layout'] ?? 'standard';
    }
    
    public function getTabs(): array
    {
        return $this->metadata['form']['tabs'] ?? [];
    }
    
    public function getFieldsets(): array
    {
        return $this->metadata['form']['fieldsets'] ?? [];
    }
    
    public function getFormColumns(): int
    {
        return $this->metadata['form']['columns'] ?? 1;
    }

    // Automatic Behaviors
    public function getBehaviors(): array
    {
        return $this->metadata['behaviors'] ?? [];
    }

    public function hasTimestamps(): bool
    {
        return isset($this->metadata['behaviors']['timestamps']);
    }

    public function getTimestampFields(): array
    {
        return $this->metadata['behaviors']['timestamps'] ?? [];
    }

    public function isSluggable(): bool
    {
        return isset($this->metadata['behaviors']['sluggable']);
    }

    public function getSluggableConfig(): array
    {
        return $this->metadata['behaviors']['sluggable'] ?? [];
    }

    public function isSortable(): bool
    {
        return isset($this->metadata['behaviors']['sortable']);
    }

    public function getSortableConfig(): array
    {
        return $this->metadata['behaviors']['sortable'] ?? [];
    }

    // Search & Filters
    public function getSearchConfig(): array
    {
        return $this->metadata['search'] ?? [];
    }

    public function getSearchFields(): array
    {
        return $this->metadata['list_view']['searchable'] ?? [];
    }

    public function getFilters(): array
    {
        return $this->metadata['filters'] ?? [];
    }

    // Permissions & Security
    public function getPermissions(): array
    {
        return $this->metadata['permissions'] ?? [];
    }

    public function getRowLevelSecurity(): ?array
    {
        return $this->metadata['row_level_security'] ?? null;
    }

    public function hasPermissions(): bool
    {
        return !empty($this->metadata['permissions']);
    }

    public function hasRowLevelSecurity(): bool
    {
        return isset($this->metadata['row_level_security']) && 
               ($this->metadata['row_level_security']['enabled'] ?? false);
    }

    // Authentication
    public function getAuthentication(): ?array
    {
        return $this->metadata['authentication'] ?? null;
    }

    public function hasAuthentication(): bool
    {
        return isset($this->metadata['authentication']) && 
               ($this->metadata['authentication']['enabled'] ?? false);
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->hasAuthentication() && 
               ($this->metadata['authentication']['registration']['enabled'] ?? false);
    }

    public function isLoginEnabled(): bool
    {
        return $this->hasAuthentication() && 
               ($this->metadata['authentication']['login']['enabled'] ?? false);
    }
    
    // Soft Deletes
    public function hasSoftDeletes(): bool
    {
        return isset($this->metadata['behaviors']['soft_deletes']) && 
               ($this->metadata['behaviors']['soft_deletes']['enabled'] ?? false);
    }
    
    public function getSoftDeleteColumn(): string
    {
        return $this->metadata['behaviors']['soft_deletes']['column'] ?? 'deleted_at';
    }
    
    public function getSoftDeleteConfig(): array
    {
        return $this->metadata['behaviors']['soft_deletes'] ?? [];
    }
}
