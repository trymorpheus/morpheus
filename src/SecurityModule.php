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
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION['_csrf_token'] = $token;
        
        return $token;
    }

    public function validateCsrfToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    }

    public function sanitizeInput(array $data, array $allowedColumns): array
    {
        $sanitized = [];
        
        foreach ($allowedColumns as $column) {
            if (isset($data[$column])) {
                $sanitized[$column] = $this->sanitizeValue($data[$column]);
            }
        }
        
        return $sanitized;
    }

    private function sanitizeValue($value): string
    {
        if (is_array($value)) {
            return '';
        }
        
        return trim($value);
    }

    public function escapeOutput(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
