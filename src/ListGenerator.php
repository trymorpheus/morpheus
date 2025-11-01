<?php

namespace DynamicCRUD;

use PDO;

class ListGenerator
{
    private PDO $pdo;
    private array $schema;
    private string $table;

    public function __construct(PDO $pdo, array $schema)
    {
        $this->pdo = $pdo;
        $this->schema = $schema;
        $this->table = $schema['table'];
    }

    public function list(array $options = []): array
    {
        $page = $options['page'] ?? 1;
        $perPage = $options['perPage'] ?? 20;
        $filters = $options['filters'] ?? [];
        $sort = $options['sort'] ?? [];
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                $conditions[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if (!empty($sort)) {
            $orderBy = [];
            foreach ($sort as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderBy[] = "{$field} {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderBy);
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = $this->getTotal($filters);
        
        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ]
        ];
    }

    private function getTotal(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                $conditions[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    public function renderTable(array $data, string $editUrl = '?id='): string
    {
        if (empty($data)) {
            return '<p>No hay registros para mostrar.</p>';
        }
        
        $visibleColumns = array_filter(
            $this->schema['columns'],
            fn($col) => !($col['metadata']['hidden'] ?? false) && $col['sql_type'] !== 'text'
        );
        
        $html = '<table class="crud-table">';
        $html .= '<thead><tr>';
        
        foreach ($visibleColumns as $column) {
            $label = $column['metadata']['label'] ?? ucfirst($column['name']);
            $html .= sprintf('<th>%s</th>', htmlspecialchars($label));
        }
        
        $html .= '<th>Acciones</th></tr></thead><tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            
            foreach ($visibleColumns as $column) {
                $value = $row[$column['name']] ?? '';
                $html .= sprintf('<td>%s</td>', htmlspecialchars($value));
            }
            
            $pk = $this->schema['primary_key'];
            $id = $row[$pk];
            
            $html .= sprintf(
                '<td><a href="%s%s">Editar</a> | <a href="?delete=%s" onclick="return confirm(\'¿Eliminar este registro?\')">Eliminar</a></td>',
                $editUrl,
                $id,
                $id
            );
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }

    public function renderPagination(array $pagination, string $baseUrl = '?'): string
    {
        $page = $pagination['page'];
        $totalPages = $pagination['totalPages'];
        
        if ($totalPages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        
        if ($page > 1) {
            $html .= sprintf('<a href="%spage=%d">« Anterior</a> ', $baseUrl, $page - 1);
        }
        
        $html .= sprintf('<span>Página %d de %d</span>', $page, $totalPages);
        
        if ($page < $totalPages) {
            $html .= sprintf(' <a href="%spage=%d">Siguiente »</a>', $baseUrl, $page + 1);
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
