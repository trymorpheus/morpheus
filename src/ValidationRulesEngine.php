<?php

namespace Morpheus;

use PDO;

class ValidationRulesEngine
{
    private PDO $pdo;
    private string $table;
    private array $rules;

    public function __construct(PDO $pdo, string $table, array $rules)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->rules = $rules;
    }

    public function validate(array $data, ?int $id = null): array
    {
        $errors = [];

        if (!isset($this->rules['validation_rules'])) {
            return $errors;
        }

        $validationRules = $this->rules['validation_rules'];

        // unique_together
        if (isset($validationRules['unique_together'])) {
            $errors = array_merge($errors, $this->validateUniqueTogether($data, $id, $validationRules['unique_together']));
        }

        // required_if
        if (isset($validationRules['required_if'])) {
            $errors = array_merge($errors, $this->validateRequiredIf($data, $validationRules['required_if']));
        }

        // conditional
        if (isset($validationRules['conditional'])) {
            $errors = array_merge($errors, $this->validateConditional($data, $validationRules['conditional']));
        }

        return $errors;
    }

    private function validateUniqueTogether(array $data, ?int $id, array $uniqueTogetherRules): array
    {
        $errors = [];

        foreach ($uniqueTogetherRules as $fields) {
            $conditions = [];
            $params = [];
            $fieldNames = [];

            foreach ($fields as $field) {
                if (!isset($data[$field])) {
                    continue 2; // Skip this combination if any field is missing
                }
                $conditions[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
                $fieldNames[] = $field;
            }

            $sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s", $this->table, implode(' AND ', $conditions));

            if ($id !== null) {
                $sql .= " AND id != :id";
                $params['id'] = $id;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->fetchColumn() > 0) {
                $fieldList = implode(', ', $fieldNames);
                $errors[$fields[0]] = "La combinación de {$fieldList} ya existe";
            }
        }

        return $errors;
    }

    private function validateRequiredIf(array $data, array $requiredIfRules): array
    {
        $errors = [];

        foreach ($requiredIfRules as $field => $conditions) {
            $shouldBeRequired = true;

            foreach ($conditions as $condField => $condValue) {
                if (!isset($data[$condField]) || $data[$condField] != $condValue) {
                    $shouldBeRequired = false;
                    break;
                }
            }

            if ($shouldBeRequired && (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null)) {
                $errors[$field] = "El campo {$field} es obligatorio";
            }
        }

        return $errors;
    }

    private function validateConditional(array $data, array $conditionalRules): array
    {
        $errors = [];

        foreach ($conditionalRules as $field => $rule) {
            if (!isset($data[$field])) {
                continue;
            }

            $condition = $rule['condition'];
            $value = $data[$field];

            // Parse condition
            $conditionMet = $this->evaluateCondition($condition, $data);

            if ($conditionMet) {
                if (isset($rule['min']) && $value < $rule['min']) {
                    $errors[$field] = "El campo {$field} debe ser al menos {$rule['min']}";
                }
                if (isset($rule['max']) && $value > $rule['max']) {
                    $errors[$field] = "El campo {$field} no puede ser mayor que {$rule['max']}";
                }
            }
        }

        return $errors;
    }

    private function evaluateCondition(string $condition, array $data): bool
    {
        // Decode HTML entities recursively (MySQL may double-encode)
        $decoded = $condition;
        do {
            $previous = $decoded;
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5);
        } while ($decoded !== $previous);
        
        $condition = $decoded;
        
        // Replace field names with values (use word boundaries to avoid partial matches)
        foreach ($data as $field => $value) {
            $pattern = '/\b' . preg_quote($field, '/') . '\b/';
            // Convert value to number if numeric string
            $numValue = is_numeric($value) ? floatval($value) : var_export($value, true);
            $condition = preg_replace($pattern, $numValue, $condition);
        }

        // Safe eval (only allow basic comparisons and decimals)
        if (preg_match('/^[\d\.\s\+\-\*\/\(\)<>=!&|]+$/', $condition)) {
            try {
                return eval("return ({$condition});");
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }

    public function validateBusinessRules(array $data, ?int $userId = null): array
    {
        $errors = [];

        if (!isset($this->rules['business_rules'])) {
            return $errors;
        }

        $businessRules = $this->rules['business_rules'];

        // max_records_per_user
        if (isset($businessRules['max_records_per_user']) && $userId !== null) {
            $ownerField = $businessRules['owner_field'] ?? 'user_id';
            $maxRecords = $businessRules['max_records_per_user'];

            $sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s = :user_id", $this->table, $ownerField);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            if ($stmt->fetchColumn() >= $maxRecords) {
                $errors['_global'] = "Has alcanzado el límite de {$maxRecords} registros";
            }
        }

        // require_approval
        if (isset($businessRules['require_approval']) && $businessRules['require_approval']) {
            $approvalField = $businessRules['approval_field'] ?? 'approved_at';
            if (!isset($data[$approvalField]) || empty($data[$approvalField])) {
                $data[$approvalField] = null; // Mark as pending approval
            }
        }

        return $errors;
    }
}
