<?php

namespace Morpheus\Security;

use PDO;

class AuthenticationManager
{
    private PDO $pdo;
    private string $userTable;
    private array $authConfig;
    private int $maxAttempts = 5;
    private int $lockoutDuration = 900;

    public function __construct(PDO $pdo, string $userTable, array $authConfig = [])
    {
        $this->pdo = $pdo;
        $this->userTable = $userTable;
        $this->authConfig = $authConfig;
        
        $this->maxAttempts = $authConfig['login']['max_attempts'] ?? 5;
        $this->lockoutDuration = $authConfig['login']['lockout_duration'] ?? 900;
    }

    public function register(array $data): array
    {
        $identifierField = $this->authConfig['identifier_field'] ?? 'email';
        $passwordField = $this->authConfig['password_field'] ?? 'password';
        
        if (empty($data[$identifierField]) || empty($data[$passwordField])) {
            return ['success' => false, 'error' => 'Email and password are required'];
        }
        
        if ($this->userExists($data[$identifierField])) {
            return ['success' => false, 'error' => 'User already exists'];
        }
        
        unset($data['csrf_token'], $data['action']);
        
        $data[$passwordField] = password_hash($data[$passwordField], PASSWORD_DEFAULT);
        
        if (isset($this->authConfig['registration']['default_role'])) {
            $data['role'] = $this->authConfig['registration']['default_role'];
        }
        
        try {
            $columns = array_keys($data);
            $placeholders = array_map(fn($col) => ":$col", $columns);
            
            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->userTable,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $userId = (int) $this->pdo->lastInsertId();
            
            if ($this->authConfig['registration']['auto_login'] ?? false) {
                $this->createSession($userId, $data);
            }
            
            return ['success' => true, 'id' => $userId];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function login(string $identifier, string $password, bool $remember = false): array
    {
        if ($this->isLockedOut()) {
            return ['success' => false, 'error' => 'Too many failed attempts. Try again later.'];
        }
        
        $identifierField = $this->authConfig['identifier_field'] ?? 'email';
        $passwordField = $this->authConfig['password_field'] ?? 'password';
        
        $sql = "SELECT * FROM {$this->userTable} WHERE {$identifierField} = :identifier LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user[$passwordField])) {
            $this->recordFailedAttempt();
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        $this->clearFailedAttempts();
        $this->createSession($user['id'], $user, $remember);
        
        return ['success' => true, 'user' => $user];
    }

    public function logout(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        return true;
    }

    public function getCurrentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $sql = "SELECT * FROM {$this->userTable} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $_SESSION['user_id']]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        return isset($_SESSION['user_id']);
    }

    private function userExists(string $identifier): bool
    {
        $identifierField = $this->authConfig['identifier_field'] ?? 'email';
        
        $sql = "SELECT COUNT(*) FROM {$this->userTable} WHERE {$identifierField} = :identifier";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        
        return $stmt->fetchColumn() > 0;
    }

    private function createSession(int $userId, array $userData, bool $remember = false): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $userData['role'] ?? 'user';
        $_SESSION['user_email'] = $userData[$this->authConfig['identifier_field'] ?? 'email'] ?? '';
        
        if ($remember) {
            $lifetime = $this->authConfig['login']['session_lifetime'] ?? 7200;
            setcookie(session_name(), session_id(), time() + $lifetime, '/');
        }
    }

    private function isLockedOut(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        if (!isset($_SESSION['login_attempts'])) {
            return false;
        }
        
        $attempts = $_SESSION['login_attempts'];
        
        if ($attempts['count'] >= $this->maxAttempts) {
            $timeSinceLastAttempt = time() - $attempts['last_attempt'];
            
            if ($timeSinceLastAttempt < $this->lockoutDuration) {
                return true;
            }
            
            $_SESSION['login_attempts'] = ['count' => 0, 'last_attempt' => 0];
        }
        
        return false;
    }

    private function recordFailedAttempt(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = ['count' => 0, 'last_attempt' => 0];
        }
        
        $_SESSION['login_attempts']['count']++;
        $_SESSION['login_attempts']['last_attempt'] = time();
    }

    private function clearFailedAttempts(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        unset($_SESSION['login_attempts']);
    }
    
    // Password Reset Methods
    
    public function requestPasswordReset(string $email): array
    {
        $identifierField = $this->authConfig['identifier_field'] ?? 'email';
        
        $sql = "SELECT id FROM {$this->userTable} WHERE {$identifierField} = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        $this->ensurePasswordResetsTable();
        
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        return ['success' => true, 'token' => $token];
    }
    
    public function validateResetToken(string $token): ?string
    {
        $this->ensurePasswordResetsTable();
        
        $sql = "SELECT email, expires_at FROM password_resets WHERE token = :token LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            return null;
        }
        
        if (strtotime($reset['expires_at']) < time()) {
            return null;
        }
        
        return $reset['email'];
    }
    
    public function resetPassword(string $token, string $newPassword): array
    {
        $email = $this->validateResetToken($token);
        
        if (!$email) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }
        
        $identifierField = $this->authConfig['identifier_field'] ?? 'email';
        $passwordField = $this->authConfig['password_field'] ?? 'password';
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->userTable} SET {$passwordField} = :password WHERE {$identifierField} = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'password' => $hashedPassword,
            'email' => $email
        ]);
        
        $sql = "DELETE FROM password_resets WHERE token = :token";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        
        return ['success' => true];
    }
    
    private function ensurePasswordResetsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (email),
            INDEX (token)
        )";
        
        $this->pdo->exec($sql);
    }
}
