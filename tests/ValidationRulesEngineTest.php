<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\ValidationRulesEngine;
use PDO;

class ValidationRulesEngineTest extends TestCase
{
    private PDO $pdo;
    private string $table = 'test_validation_rules';

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE {$this->table} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sku TEXT,
                category TEXT,
                price REAL,
                discount REAL,
                status TEXT,
                min_stock INTEGER,
                user_id INTEGER
            )
        ");
    }

    public function testUniqueTogether()
    {
        $this->pdo->exec("INSERT INTO {$this->table} (sku, category) VALUES ('SKU-001', 'electronics')");

        $rules = [
            'validation_rules' => [
                'unique_together' => [
                    ['sku', 'category']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['sku' => 'SKU-001', 'category' => 'electronics'];
        $errors = $engine->validate($data);

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('sku', $errors);
    }

    public function testUniqueTogetherWithDifferentCategory()
    {
        $this->pdo->exec("INSERT INTO {$this->table} (sku, category) VALUES ('SKU-001', 'electronics')");

        $rules = [
            'validation_rules' => [
                'unique_together' => [
                    ['sku', 'category']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['sku' => 'SKU-001', 'category' => 'furniture'];
        $errors = $engine->validate($data);

        $this->assertEmpty($errors);
    }

    public function testUniqueTogetherOnUpdate()
    {
        $this->pdo->exec("INSERT INTO {$this->table} (id, sku, category) VALUES (1, 'SKU-001', 'electronics')");

        $rules = [
            'validation_rules' => [
                'unique_together' => [
                    ['sku', 'category']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['sku' => 'SKU-001', 'category' => 'electronics'];
        $errors = $engine->validate($data, 1);

        $this->assertEmpty($errors);
    }

    public function testRequiredIf()
    {
        $rules = [
            'validation_rules' => [
                'required_if' => [
                    'min_stock' => ['status' => 'active']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['status' => 'active'];
        $errors = $engine->validate($data);

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('min_stock', $errors);
    }

    public function testRequiredIfConditionNotMet()
    {
        $rules = [
            'validation_rules' => [
                'required_if' => [
                    'min_stock' => ['status' => 'active']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['status' => 'draft'];
        $errors = $engine->validate($data);

        $this->assertEmpty($errors);
    }

    public function testRequiredIfWithValue()
    {
        $rules = [
            'validation_rules' => [
                'required_if' => [
                    'min_stock' => ['status' => 'active']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['status' => 'active', 'min_stock' => 10];
        $errors = $engine->validate($data);

        $this->assertEmpty($errors);
    }

    public function testConditionalMax()
    {
        $rules = [
            'validation_rules' => [
                'conditional' => [
                    'discount' => [
                        'condition' => 'price > 100',
                        'max' => 50
                    ]
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['price' => 150, 'discount' => 60];
        $errors = $engine->validate($data);

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('discount', $errors);
    }

    public function testConditionalMaxValid()
    {
        $rules = [
            'validation_rules' => [
                'conditional' => [
                    'discount' => [
                        'condition' => 'price > 100',
                        'max' => 50
                    ]
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['price' => 150, 'discount' => 40];
        $errors = $engine->validate($data);

        $this->assertEmpty($errors);
    }

    public function testConditionalConditionNotMet()
    {
        $rules = [
            'validation_rules' => [
                'conditional' => [
                    'discount' => [
                        'condition' => 'price > 100',
                        'max' => 50
                    ]
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['price' => 50, 'discount' => 60];
        $errors = $engine->validate($data);

        $this->assertEmpty($errors);
    }

    public function testMaxRecordsPerUser()
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->pdo->exec("INSERT INTO {$this->table} (user_id) VALUES (1)");
        }

        $rules = [
            'business_rules' => [
                'max_records_per_user' => 5,
                'owner_field' => 'user_id'
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['user_id' => 1];
        $errors = $engine->validateBusinessRules($data, 1);

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('_global', $errors);
    }

    public function testMaxRecordsPerUserNotReached()
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->pdo->exec("INSERT INTO {$this->table} (user_id) VALUES (1)");
        }

        $rules = [
            'business_rules' => [
                'max_records_per_user' => 5,
                'owner_field' => 'user_id'
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = ['user_id' => 1];
        $errors = $engine->validateBusinessRules($data, 1);

        $this->assertEmpty($errors);
    }

    public function testMultipleRulesCombined()
    {
        $this->pdo->exec("INSERT INTO {$this->table} (sku, category) VALUES ('SKU-001', 'electronics')");

        $rules = [
            'validation_rules' => [
                'unique_together' => [
                    ['sku', 'category']
                ],
                'required_if' => [
                    'min_stock' => ['status' => 'active']
                ]
            ]
        ];

        $engine = new ValidationRulesEngine($this->pdo, $this->table, $rules);

        $data = [
            'sku' => 'SKU-001',
            'category' => 'electronics',
            'status' => 'active'
        ];

        $errors = $engine->validate($data);

        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('sku', $errors);
        $this->assertArrayHasKey('min_stock', $errors);
    }
}
