<?php

namespace DynamicCRUD;

class SecurityModule
{
    private const TOKEN_LENGTH = 32;

    public function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }

    public function validateCsrfToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function sanitizeInput(array $data, array $allowedColumns, array $schema = []): array
    {
        $sanitized = [];
        
        foreach ($allowedColumns as $column) {
            if (isset($data[$column])) {
                $value = $this->sanitizeValue($data[$column]);
                
                // Convertir cadenas vacías a NULL para campos opcionales
                if ($value === '' && $this->isNullable($column, $schema)) {
                    $value = null;
                }
                
                $sanitized[$column] = $value;
            }
        }
        
        return $sanitized;
    }

    private function sanitizeValue($value)
    {
        if (is_array($value)) {
            return '';
        }
        
        return trim($value);
    }

    private function isNullable(string $columnName, array $schema): bool
    {
        if (empty($schema['columns'])) {
            return false;
        }
        
        foreach ($schema['columns'] as $column) {
            if ($column['name'] === $columnName) {
                return $column['is_nullable'];
            }
        }
        
        return false;
    }

    public function escapeOutput(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
