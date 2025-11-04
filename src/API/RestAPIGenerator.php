<?php

namespace DynamicCRUD\API;

use PDO;
use DynamicCRUD\SchemaAnalyzer;
use DynamicCRUD\CRUDHandler;
use DynamicCRUD\Security\PermissionManager;

class RestAPIGenerator
{
    private PDO $pdo;
    private ?string $jwtSecret;
    private ?PermissionManager $permissionManager = null;
    private array $config = [];

    public function __construct(PDO $pdo, ?string $jwtSecret = null, array $config = [])
    {
        $this->pdo = $pdo;
        $this->jwtSecret = $jwtSecret ?? bin2hex(random_bytes(32));
        $this->config = array_merge([
            'prefix' => '/api',
            'version' => 'v1',
            'cors' => true,
            'rate_limit' => 100,
        ], $config);
    }

    public function setPermissionManager(PermissionManager $manager): self
    {
        $this->permissionManager = $manager;
        return $this;
    }

    public function handleRequest(): void
    {
        if ($this->config['cors']) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
        }

        header('Content-Type: application/json');

        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            $prefix = $this->config['prefix'] . '/' . $this->config['version'];
            if (strpos($path, $prefix) === 0) {
                $path = substr($path, strlen($prefix));
            }
            
            $segments = array_filter(explode('/', $path));
            $segments = array_values($segments);

            if (empty($segments)) {
                $this->sendResponse(['message' => 'DynamicCRUD REST API', 'version' => $this->config['version']]);
                return;
            }

            if ($segments[0] === 'docs') {
                $this->sendResponse($this->generateOpenAPISpec());
                return;
            }

            if ($segments[0] === 'auth' && isset($segments[1])) {
                $this->handleAuth($segments[1]);
                return;
            }

            $table = $segments[0];
            $id = $segments[1] ?? null;

            $user = $this->authenticate();

            if ($this->permissionManager) {
                $action = match($method) {
                    'GET' => 'read',
                    'POST' => 'create',
                    'PUT' => 'update',
                    'DELETE' => 'delete',
                    default => null
                };

                if ($action && !$this->permissionManager->can($action)) {
                    $this->sendError('Permission denied', 403);
                    return;
                }
            }

            match($method) {
                'GET' => $this->handleGet($table, $id),
                'POST' => $this->handlePost($table),
                'PUT' => $this->handlePut($table, $id),
                'DELETE' => $this->handleDelete($table, $id),
                default => $this->sendError('Method not allowed', 405)
            };

        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function handleAuth(string $action): void
    {
        if ($action === 'login') {
            $data = $this->getRequestData();
            
            if (empty($data['email']) || empty($data['password'])) {
                $this->sendError('Email and password required', 400);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $data['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($data['password'], $user['password'])) {
                $this->sendError('Invalid credentials', 401);
                return;
            }

            $token = $this->generateJWT($user);
            $this->sendResponse(['token' => $token, 'user' => $this->sanitizeUser($user)]);
            
        } else {
            $this->sendError('Unknown auth action', 404);
        }
    }

    private function handleGet(string $table, ?string $id): void
    {
        $analyzer = new SchemaAnalyzer($this->pdo);
        $schema = $analyzer->getTableSchema($table);
        $pk = $schema['primary_key'];

        if ($id) {
            $sql = sprintf("SELECT * FROM %s WHERE %s = :id LIMIT 1", $table, $pk);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->sendError('Record not found', 404);
                return;
            }

            $this->sendResponse($record);
        } else {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = min((int)($_GET['per_page'] ?? 20), 100);
            $offset = ($page - 1) * $perPage;

            $countStmt = $this->pdo->query("SELECT COUNT(*) FROM $table");
            $total = (int)$countStmt->fetchColumn();

            $sql = sprintf("SELECT * FROM %s LIMIT %d OFFSET %d", $table, $perPage, $offset);
            $stmt = $this->pdo->query($sql);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse([
                'data' => $records,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        }
    }

    private function handlePost(string $table): void
    {
        $data = $this->getRequestData();
        
        $handler = new CRUDHandler($this->pdo, $table);
        $result = $handler->create($data);

        if ($result['success']) {
            $this->sendResponse(['id' => $result['id'], 'message' => 'Created successfully'], 201);
        } else {
            $this->sendError($result['error'] ?? 'Creation failed', 400);
        }
    }

    private function handlePut(string $table, ?string $id): void
    {
        if (!$id) {
            $this->sendError('ID required for update', 400);
            return;
        }

        $data = $this->getRequestData();
        
        $handler = new CRUDHandler($this->pdo, $table);
        $result = $handler->update((int)$id, $data);

        if ($result['success']) {
            $this->sendResponse(['message' => 'Updated successfully']);
        } else {
            $this->sendError($result['error'] ?? 'Update failed', 400);
        }
    }

    private function handleDelete(string $table, ?string $id): void
    {
        if (!$id) {
            $this->sendError('ID required for delete', 400);
            return;
        }

        $handler = new CRUDHandler($this->pdo, $table);
        $result = $handler->delete((int)$id);

        if ($result) {
            $this->sendResponse(['message' => 'Deleted successfully']);
        } else {
            $this->sendError('Delete failed', 400);
        }
    }

    private function authenticate(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        return $this->verifyJWT($token);
    }

    private function generateJWT(array $user): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + 86400
        ]));
        
        $signature = hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true);
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    private function verifyJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        
        $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true));
        
        if ($signature !== $validSignature) {
            return null;
        }

        $data = json_decode(base64_decode($payload), true);
        
        if ($data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private function getRequestData(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return $data ?? [];
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }

    private function sendResponse(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
        exit;
    }

    public function generateOpenAPISpec(): array
    {
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $paths = [];

        foreach ($tables as $table) {
            if (str_starts_with($table, '_dynamiccrud_')) {
                continue;
            }

            $paths["/{$table}"] = [
                'get' => [
                    'summary' => "List {$table}",
                    'parameters' => [
                        ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer']]
                    ],
                    'responses' => ['200' => ['description' => 'Success']]
                ],
                'post' => [
                    'summary' => "Create {$table}",
                    'responses' => ['201' => ['description' => 'Created']]
                ]
            ];

            $paths["/{$table}/{id}"] = [
                'get' => [
                    'summary' => "Get {$table} by ID",
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => ['200' => ['description' => 'Success']]
                ],
                'put' => [
                    'summary' => "Update {$table}",
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => ['200' => ['description' => 'Updated']]
                ],
                'delete' => [
                    'summary' => "Delete {$table}",
                    'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                    'responses' => ['200' => ['description' => 'Deleted']]
                ]
            ];
        }

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'DynamicCRUD REST API',
                'version' => $this->config['version']
            ],
            'paths' => $paths
        ];
    }
}
