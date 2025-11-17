<?php

namespace Morpheus;

use PDO;
use Morpheus\Metadata\TableMetadata;
use Morpheus\Security\PermissionManager;
use Morpheus\UI\Components;

class ListGenerator
{
    private PDO $pdo;
    private string $table;
    private array $schema;
    private ?TableMetadata $tableMetadata;
    private ?PermissionManager $permissionManager = null;
    
    public function __construct(PDO $pdo, string $table, array $schema = [], ?TableMetadata $tableMetadata = null, ?PermissionManager $permissionManager = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->schema = $schema;
        $this->tableMetadata = $tableMetadata;
        $this->permissionManager = $permissionManager;
    }
    
    public function render(array $options = []): string
    {
        $page = $options['page'] ?? $_GET['page'] ?? 1;
        $perPage = $this->tableMetadata?->getPerPage() ?? $options['perPage'] ?? 20;
        $search = $options['search'] ?? $_GET['search'] ?? '';
        
        $data = $this->fetchData($page, $perPage, $search);
        
        $html = '<div class="list-container">' . "\n";
        $html .= $this->renderHeader();
        $html .= $this->renderSearchAndFilters($search);
        $html .= $this->renderContent($data['records']);
        $html .= $this->renderPagination($data['total'], $page, $perPage);
        $html .= '</div>';
        
        return $html;
    }
    
    private function renderHeader(): string
    {
        $displayName = $this->tableMetadata?->getDisplayName() ?? ucfirst($this->table);
        $icon = $this->tableMetadata?->getIcon();
        $color = $this->tableMetadata?->getColor();
        $desc = $this->tableMetadata?->getDescription();
        
        $header = ($icon ? $icon . ' ' : '') . htmlspecialchars($displayName);
        $content = $desc ? '<p style="margin: 8px 0 0 0; color: #666; font-size: 14px;">' . htmlspecialchars($desc) . '</p>' : '';
        
        $style = $color ? ' style="border-left: 4px solid ' . $color . '; padding-left: 16px;"' : '';
        return '<div class="list-header"' . $style . '><h2 style="margin: 0;">' . $header . '</h2>' . $content . '</div>' . "\n";
    }
    
    private function renderSearchAndFilters(string $search): string
    {
        if (empty($this->tableMetadata?->getSearchableFields())) {
            return '';
        }
        return $this->renderSearch($search) . "\n";
    }
    
    private function renderContent(array $records): string
    {
        if ($this->tableMetadata?->hasCardView()) {
            return $this->renderCards($records) . "\n";
        }
        return $this->renderTableWithComponents($records) . "\n";
    }
    
    private function fetchData(int $page, int $perPage, string $search): array
    {
        $offset = ($page - 1) * $perPage;
        $columns = $this->tableMetadata?->getListColumns();
        
        if (empty($columns)) {
            $columns = array_map(fn($col) => $col['name'], $this->schema['columns']);
        }
        
        // Always include owner_field for permission checks
        if ($this->permissionManager && $this->permissionManager->hasRowLevelSecurity()) {
            $ownerField = $this->permissionManager->getRowLevelSecurity()['owner_field'] ?? 'user_id';
            if (!in_array($ownerField, $columns)) {
                $columns[] = $ownerField;
            }
        }
        
        $select = implode(', ', $columns);
        $conditions = [];
        $params = [];
        
        // Search
        if ($search && !empty($searchFields = $this->tableMetadata?->getSearchableFields())) {
            $searchConditions = array_map(fn($field) => "$field LIKE :search", $searchFields);
            $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
            $params['search'] = "%$search%";
        }
        
        // Filters
        if ($filters = $this->tableMetadata?->getFilters()) {
            foreach ($filters as $filter) {
                $field = $filter['field'];
                $type = $filter['type'];
                
                if ($type === 'select' && !empty($_GET[$field])) {
                    $conditions[] = "$field = :filter_$field";
                    $params["filter_$field"] = $_GET[$field];
                } elseif ($type === 'daterange') {
                    if (!empty($_GET[$field . '_from'])) {
                        $conditions[] = "$field >= :filter_{$field}_from";
                        $params["filter_{$field}_from"] = $_GET[$field . '_from'];
                    }
                    if (!empty($_GET[$field . '_to'])) {
                        $conditions[] = "$field <= :filter_{$field}_to";
                        $params["filter_{$field}_to"] = $_GET[$field . '_to'];
                    }
                }
            }
        }
        
        // Soft Deletes - exclude deleted records unless withTrashed is requested
        if ($this->tableMetadata && $this->tableMetadata->hasSoftDeletes()) {
            $withTrashed = $_GET['withTrashed'] ?? false;
            if (!$withTrashed) {
                $column = $this->tableMetadata->getSoftDeleteColumn();
                $conditions[] = "$column IS NULL";
            }
        }
        
        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sort = $this->tableMetadata?->getDefaultSort() ?? 'id DESC';
        
        $sql = "SELECT $select FROM {$this->table} $where ORDER BY $sort LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $where";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return ['records' => $records, 'total' => $total];
    }
    
    private function renderSearch(string $search): string
    {
        $html = '<div class="list-search">' . "\n";
        $html .= '  <form method="GET" class="search-form">' . "\n";
        
        // Preserve filter parameters (including 'user' for demo)
        foreach ($_GET as $key => $value) {
            if ($key !== 'search' && $key !== 'page' && $key !== 'delete' && $key !== 'id') {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $html .= sprintf('    <input type="hidden" name="%s[]" value="%s">', htmlspecialchars($key), htmlspecialchars($v)) . "\n";
                    }
                } else {
                    $html .= sprintf('    <input type="hidden" name="%s" value="%s">', htmlspecialchars($key), htmlspecialchars($value)) . "\n";
                }
            }
        }
        
        $html .= sprintf('    <input type="search" name="search" value="%s" placeholder="Buscar..." class="search-input">', htmlspecialchars($search)) . "\n";
        $html .= '    <button type="submit" class="search-button">🔍 Buscar</button>' . "\n";
        if ($search) {
            $html .= '    <a href="?" class="clear-search">✕ Limpiar</a>' . "\n";
        }
        $html .= '  </form>' . "\n";
        
        // Render filters if configured
        if ($filters = $this->tableMetadata?->getFilters()) {
            $html .= $this->renderFilters($filters) . "\n";
        }
        
        $html .= '</div>' . "\n";
        
        return $html;
    }
    
    private function renderFilters(array $filters): string
    {
        $html = '<div class="list-filters">' . "\n";
        $html .= '  <form method="GET" class="filters-form">' . "\n";
        
        // Preserve search and other parameters (including 'user' for demo)
        foreach ($_GET as $key => $value) {
            if ($key !== 'page' && !is_array($value)) {
                $html .= sprintf('    <input type="hidden" name="%s" value="%s">', htmlspecialchars($key), htmlspecialchars($value)) . "\n";
            }
        }
        
        foreach ($filters as $filter) {
            $field = $filter['field'];
            $type = $filter['type'];
            $label = $filter['label'] ?? ucfirst($field);
            $value = $_GET[$field] ?? '';
            
            $html .= sprintf('    <div class="filter-group">' . "\n");
            $html .= sprintf('      <label>%s</label>' . "\n", htmlspecialchars($label));
            
            if ($type === 'select') {
                $html .= sprintf('      <select name="%s">' . "\n", $field);
                $html .= '        <option value="">Todos</option>' . "\n";
                foreach ($filter['options'] as $option) {
                    $selected = $value == $option ? ' selected' : '';
                    $html .= sprintf('        <option value="%s"%s>%s</option>' . "\n",
                        htmlspecialchars($option), $selected, htmlspecialchars(ucfirst($option)));
                }
                $html .= '      </select>' . "\n";
            } elseif ($type === 'daterange') {
                $html .= sprintf('      <input type="date" name="%s_from" value="%s" placeholder="Desde">' . "\n",
                    $field, htmlspecialchars($_GET[$field . '_from'] ?? ''));
                $html .= sprintf('      <input type="date" name="%s_to" value="%s" placeholder="Hasta">' . "\n",
                    $field, htmlspecialchars($_GET[$field . '_to'] ?? ''));
            }
            
            $html .= '    </div>' . "\n";
        }
        
        $html .= '    <button type="submit" class="filter-button">Filtrar</button>' . "\n";
        $html .= '  </form>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }
    
    private function renderTableWithComponents(array $records): string
    {
        if (empty($records)) {
            return Components::alert('No hay registros.', 'info');
        }
        
        $columns = array_keys($records[0]);
        $actions = $this->tableMetadata?->getActions() ?? ['edit', 'delete'];
        
        $headers = array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $columns);
        if (!empty($actions)) {
            $headers[] = 'Acciones';
        }
        
        $rows = [];
        foreach ($records as $record) {
            $row = array_map(fn($col) => htmlspecialchars($record[$col] ?? ''), $columns);
            
            if (!empty($actions)) {
                $row[] = $this->renderActionButtons($record, $actions);
            }
            
            $rows[] = $row;
        }
        
        return Components::table($headers, $rows, ['striped' => true, 'hover' => true]);
    }
    
    private function renderActionButtons(array $record, array $actions): string
    {
        $pk = $this->schema['primary_key'] ?? 'id';
        $id = $record[$pk] ?? $record['id'] ?? null;
        
        if ($id === null) {
            return '';
        }
        
        $queryParams = $_GET;
        unset($queryParams['id'], $queryParams['delete'], $queryParams['view']);
        $queryString = http_build_query($queryParams);
        $separator = $queryString ? '&' : '';
        
        $buttons = [];
        
        foreach ($actions as $action) {
            if ($action === 'edit' && (!$this->permissionManager || $this->permissionManager->canUpdate($record))) {
                $buttons[] = sprintf('<a href="?%sid=%s" style="color: #667eea; text-decoration: none; margin-right: 8px;">✏️ Editar</a>', $queryString . $separator, $id);
            } elseif ($action === 'delete' && (!$this->permissionManager || $this->permissionManager->canDelete($record))) {
                $buttons[] = sprintf('<a href="?%sdelete=%s" style="color: #e53e3e; text-decoration: none; margin-right: 8px;" onclick="return confirm(\'¿Eliminar?\')">🗑️ Eliminar</a>', $queryString . $separator, $id);
            } elseif ($action === 'view' && (!$this->permissionManager || $this->permissionManager->canRead($record))) {
                $buttons[] = sprintf('<a href="?%sview=%s" style="color: #4299e1; text-decoration: none; margin-right: 8px;">👁️ Ver</a>', $queryString . $separator, $id);
            }
        }
        
        return implode('', $buttons);
    }
    
    private function renderCards(array $records): string
    {
        if (empty($records)) {
            return '<p>No hay registros.</p>';
        }
        
        $template = $this->tableMetadata?->getCardTemplate();
        
        $html = '<div class="list-cards">' . "\n";
        
        foreach ($records as $record) {
            if ($template) {
                $card = $template;
                foreach ($record as $key => $value) {
                    $card = str_replace("{{" . $key . "}}", htmlspecialchars($value), $card);
                }
                $html .= $card . "\n";
            } else {
                $html .= '<div class="card">' . "\n";
                foreach ($record as $key => $value) {
                    $html .= sprintf('  <div><strong>%s:</strong> %s</div>', htmlspecialchars($key), htmlspecialchars($value)) . "\n";
                }
                $html .= '</div>' . "\n";
            }
        }
        
        $html .= '</div>' . "\n";
        
        return $html;
    }
    
    private function renderPagination(int $total, int $page, int $perPage): string
    {
        $totalPages = ceil($total / $perPage);
        
        if ($totalPages <= 1) {
            return '';
        }
        
        $queryParams = $_GET;
        unset($queryParams['page']);
        $queryString = http_build_query($queryParams);
        $baseUrl = '?' . ($queryString ? $queryString . '&' : '') . 'page=';
        
        return Components::pagination($page, $totalPages, $baseUrl);
    }
}
