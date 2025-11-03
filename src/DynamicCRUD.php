<?php

namespace DynamicCRUD;

use DynamicCRUD\Cache\CacheStrategy;
use DynamicCRUD\I18n\Translator;
use DynamicCRUD\Template\TemplateEngine;
use DynamicCRUD\Metadata\TableMetadata;
use DynamicCRUD\Security\PermissionManager;
use DynamicCRUD\Security\AuthenticationManager;
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
    private ?AuthenticationManager $authManager = null;

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

    public function enableAuthentication(): self
    {
        if ($this->tableMetadata->hasAuthentication()) {
            $authConfig = $this->tableMetadata->getAuthentication();
            $this->authManager = new AuthenticationManager($this->pdo, $this->table, $authConfig);
        }
        
        return $this;
    }

    public function renderRegistrationForm(): string
    {
        if (!$this->authManager) {
            throw new \Exception('Authentication not enabled. Call enableAuthentication() first.');
        }
        
        $security = new SecurityModule();
        $requiredFields = $this->tableMetadata->getAuthentication()['registration']['required_fields'] ?? ['name', 'email', 'password'];
        
        $html = '<div class="auth-container">' . "\n";
        $html .= '<h2>Register</h2>' . "\n";
        $html .= '<form method="POST" class="auth-form">' . "\n";
        $html .= '<input type="hidden" name="csrf_token" value="' . $security->generateCsrfToken() . '">' . "\n";
        
        foreach ($requiredFields as $field) {
            $type = $field === 'password' ? 'password' : ($field === 'email' ? 'email' : 'text');
            $label = ucfirst($field);
            
            $html .= '<div class="form-group">' . "\n";
            $html .= "  <label for=\"$field\">$label</label>" . "\n";
            $html .= "  <input type=\"$type\" name=\"$field\" id=\"$field\" required>" . "\n";
            $html .= '</div>' . "\n";
        }
        
        $html .= '<button type="submit" name="action" value="register">Register</button>' . "\n";
        $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }

    public function renderLoginForm(): string
    {
        if (!$this->authManager) {
            throw new \Exception('Authentication not enabled. Call enableAuthentication() first.');
        }
        
        $security = new SecurityModule();
        $identifierField = $this->tableMetadata->getAuthentication()['identifier_field'] ?? 'email';
        $rememberMe = $this->tableMetadata->getAuthentication()['login']['remember_me'] ?? false;
        
        $html = '<div class="auth-container">' . "\n";
        $html .= '<h2>Login</h2>' . "\n";
        $html .= '<form method="POST" class="auth-form">' . "\n";
        $html .= '<input type="hidden" name="csrf_token" value="' . $security->generateCsrfToken() . '">' . "\n";
        
        $html .= '<div class="form-group">' . "\n";
        $html .= "  <label for=\"$identifierField\">" . ucfirst($identifierField) . "</label>" . "\n";
        $html .= "  <input type=\"email\" name=\"$identifierField\" id=\"$identifierField\" required>" . "\n";
        $html .= '</div>' . "\n";
        
        $html .= '<div class="form-group">' . "\n";
        $html .= '  <label for="password">Password</label>' . "\n";
        $html .= '  <input type="password" name="password" id="password" required>' . "\n";
        $html .= '</div>' . "\n";
        
        if ($rememberMe) {
            $html .= '<div class="form-group">' . "\n";
            $html .= '  <label><input type="checkbox" name="remember" value="1"> Remember me</label>' . "\n";
            $html .= '</div>' . "\n";
        }
        
        $html .= '<button type="submit" name="action" value="login">Login</button>' . "\n";
        $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";
        
        return $html;
    }

    public function handleAuthentication(): array
    {
        if (!$this->authManager) {
            return ['success' => false, 'error' => 'Authentication not enabled'];
        }
        
        $security = new SecurityModule();
        if (!$security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'register') {
            return $this->authManager->register($_POST);
        }
        
        if ($action === 'login') {
            $identifierField = $this->tableMetadata->getAuthentication()['identifier_field'] ?? 'email';
            return $this->authManager->login(
                $_POST[$identifierField] ?? '',
                $_POST['password'] ?? '',
                isset($_POST['remember'])
            );
        }
        
        if ($action === 'logout') {
            $this->authManager->logout();
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Invalid action'];
    }

    public function getCurrentUser(): ?array
    {
        return $this->authManager?->getCurrentUser();
    }

    public function isAuthenticated(): bool
    {
        return $this->authManager?->isAuthenticated() ?? false;
    }
}
