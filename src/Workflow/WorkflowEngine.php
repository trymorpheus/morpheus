<?php

namespace DynamicCRUD\Workflow;

use PDO;

class WorkflowEngine
{
    private PDO $pdo;
    private string $table;
    private array $config;
    private array $hooks = [];

    public function __construct(PDO $pdo, string $table, array $config)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->config = $config;
        
        $this->validateConfig();
    }

    private function validateConfig(): void
    {
        $this->validateField();
        $this->validateStates();
        $this->validateTransitions();
    }

    private function validateField(): void
    {
        if (empty($this->config['field'])) {
            throw new \InvalidArgumentException('Workflow field is required');
        }
    }

    private function validateStates(): void
    {
        if (empty($this->config['states'])) {
            throw new \InvalidArgumentException('Workflow states are required');
        }
    }

    private function validateTransitions(): void
    {
        if (empty($this->config['transitions'])) {
            throw new \InvalidArgumentException('Workflow transitions are required');
        }
    }

    public function getStates(): array
    {
        return $this->config['states'];
    }

    public function getTransitions(): array
    {
        return $this->config['transitions'];
    }

    public function getField(): string
    {
        return $this->config['field'];
    }

    public function getCurrentState(int $id): ?string
    {
        $field = $this->config['field'];
        $sql = "SELECT {$field} FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result[$field] ?? null;
    }

    public function canTransition(string $transition, ?string $currentState = null, ?array $user = null): bool
    {
        if (!$this->transitionExists($transition)) {
            return false;
        }
        
        $transitionConfig = $this->getTransitionConfig($transition);
        
        if (!$this->isValidFromState($transitionConfig, $currentState)) {
            return false;
        }
        
        if (!$this->hasPermission($transitionConfig, $user)) {
            return false;
        }
        
        return true;
    }

    private function transitionExists(string $transition): bool
    {
        return isset($this->config['transitions'][$transition]);
    }

    private function getTransitionConfig(string $transition): array
    {
        return $this->config['transitions'][$transition];
    }

    private function isValidFromState(array $transitionConfig, ?string $currentState): bool
    {
        if ($currentState === null) {
            return true;
        }
        
        $allowedFrom = $this->getAllowedFromStates($transitionConfig);
        return in_array($currentState, $allowedFrom);
    }

    private function getAllowedFromStates(array $transitionConfig): array
    {
        return is_array($transitionConfig['from']) 
            ? $transitionConfig['from'] 
            : [$transitionConfig['from']];
    }

    private function hasPermission(array $transitionConfig, ?array $user): bool
    {
        if ($user === null || !isset($transitionConfig['permissions'])) {
            return true;
        }
        
        $userRole = $user['role'] ?? 'guest';
        return in_array($userRole, $transitionConfig['permissions']);
    }

    public function getAvailableTransitions(?string $currentState = null, ?array $user = null): array
    {
        $available = [];
        
        foreach ($this->config['transitions'] as $name => $config) {
            if ($this->canTransition($name, $currentState, $user)) {
                $available[$name] = $config;
            }
        }
        
        return $available;
    }

    public function transition(int $id, string $transition, ?array $user = null): array
    {
        $currentState = $this->getCurrentState($id);
        
        if (!$this->canTransition($transition, $currentState, $user)) {
            return $this->transitionNotAllowed();
        }
        
        $newState = $this->getNewState($transition);
        $this->ensureHistoryTable();
        
        return $this->executeTransition($id, $transition, $currentState, $newState, $user);
    }

    private function transitionNotAllowed(): array
    {
        return ['success' => false, 'error' => 'Transition not allowed'];
    }

    private function getNewState(string $transition): string
    {
        return $this->config['transitions'][$transition]['to'];
    }

    private function ensureHistoryTable(): void
    {
        if ($this->isHistoryEnabled()) {
            $this->createHistoryTable($this->getHistoryTableName());
        }
    }

    private function isHistoryEnabled(): bool
    {
        return $this->config['history'] ?? false;
    }

    private function getHistoryTableName(): string
    {
        return $this->config['history_table'] ?? '_workflow_history';
    }

    private function executeTransition(int $id, string $transition, ?string $currentState, string $newState, ?array $user): array
    {
        try {
            $this->pdo->beginTransaction();
            
            $this->executeHook('before_' . $transition, $id, $currentState, $newState, $user);
            $this->updateState($id, $newState);
            $this->logTransitionIfEnabled($id, $transition, $currentState, $newState, $user);
            $this->executeHook('after_' . $transition, $id, $currentState, $newState, $user);
            
            $this->pdo->commit();
            
            return $this->transitionSuccess($currentState, $newState);
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return $this->transitionError($e->getMessage());
        }
    }

    private function updateState(int $id, string $newState): void
    {
        $field = $this->config['field'];
        $sql = "UPDATE {$this->table} SET {$field} = :state WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['state' => $newState, 'id' => $id]);
    }

    private function logTransitionIfEnabled(int $id, string $transition, ?string $currentState, string $newState, ?array $user): void
    {
        if ($this->isHistoryEnabled()) {
            $this->logTransition($id, $transition, $currentState, $newState, $user);
        }
    }

    private function transitionSuccess(?string $from, string $to): array
    {
        return ['success' => true, 'from' => $from, 'to' => $to];
    }

    private function transitionError(string $message): array
    {
        return ['success' => false, 'error' => $message];
    }

    private function logTransition(int $recordId, string $transition, ?string $fromState, string $toState, ?array $user): void
    {
        $historyTable = $this->config['history_table'] ?? '_workflow_history';
        
        $sql = "INSERT INTO {$historyTable} 
                (table_name, record_id, transition, from_state, to_state, user_id, user_ip, created_at) 
                VALUES (:table, :record_id, :transition, :from_state, :to_state, :user_id, :user_ip, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'table' => $this->table,
            'record_id' => $recordId,
            'transition' => $transition,
            'from_state' => $fromState,
            'to_state' => $toState,
            'user_id' => $user['id'] ?? null,
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    private function createHistoryTable(string $tableName): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_name VARCHAR(255) NOT NULL,
            record_id INT NOT NULL,
            transition VARCHAR(100) NOT NULL,
            from_state VARCHAR(100),
            to_state VARCHAR(100) NOT NULL,
            user_id INT,
            user_ip VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_record (table_name, record_id),
            INDEX idx_created (created_at)
        )";
        
        $this->pdo->exec($sql);
    }

    public function getHistory(int $recordId): array
    {
        if (!$this->isHistoryEnabled()) {
            return [];
        }
        
        $historyTable = $this->getHistoryTableName();
        $this->createHistoryTable($historyTable);
        
        return $this->fetchHistory($historyTable, $recordId);
    }

    private function fetchHistory(string $historyTable, int $recordId): array
    {
        $sql = "SELECT * FROM {$historyTable} 
                WHERE table_name = :table AND record_id = :record_id 
                ORDER BY created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['table' => $this->table, 'record_id' => $recordId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addHook(string $event, callable $callback): self
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }
        
        $this->hooks[$event][] = $callback;
        
        return $this;
    }

    private function executeHook(string $event, ...$args): void
    {
        if (!isset($this->hooks[$event])) {
            return;
        }
        
        foreach ($this->hooks[$event] as $callback) {
            $callback(...$args);
        }
    }

    public function renderTransitionButtons(int $id, ?array $user = null): string
    {
        $currentState = $this->getCurrentState($id);
        $transitions = $this->getAvailableTransitions($currentState, $user);
        
        if (empty($transitions)) {
            return '';
        }
        
        $html = '<div class="workflow-transitions" style="margin: 20px 0; padding: 15px; background: #f7fafc; border-radius: 6px;">' . "\n";
        $html .= sprintf('  <div style="margin-bottom: 10px;"><strong>Estado actual:</strong> <span class="workflow-state" style="padding: 4px 12px; background: #667eea; color: white; border-radius: 4px; font-size: 13px;">%s</span></div>' . "\n", htmlspecialchars($currentState ?? 'N/A'));
        $html .= '  <div style="display: flex; gap: 10px; flex-wrap: wrap;">' . "\n";
        
        foreach ($transitions as $name => $config) {
            $label = $config['label'] ?? ucfirst($name);
            $color = $config['color'] ?? '#667eea';
            
            $html .= sprintf(
                '    <form method="POST" style="display: inline;">' . "\n" .
                '      <input type="hidden" name="workflow_transition" value="%s">' . "\n" .
                '      <input type="hidden" name="workflow_id" value="%s">' . "\n" .
                '      <button type="submit" style="padding: 8px 16px; background: %s; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">%s</button>' . "\n" .
                '    </form>' . "\n",
                htmlspecialchars($name),
                $id,
                htmlspecialchars($color),
                htmlspecialchars($label)
            );
        }
        
        $html .= '  </div>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }

    public function renderStateColumn(string $state): string
    {
        $stateConfig = $this->config['state_labels'][$state] ?? [];
        $label = $stateConfig['label'] ?? ucfirst($state);
        $color = $stateConfig['color'] ?? '#718096';
        
        return sprintf(
            '<span class="workflow-state-badge" style="padding: 4px 12px; background: %s; color: white; border-radius: 4px; font-size: 12px; font-weight: 500;">%s</span>',
            htmlspecialchars($color),
            htmlspecialchars($label)
        );
    }
}
