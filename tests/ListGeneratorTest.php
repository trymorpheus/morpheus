<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\ListGenerator;
use PHPUnit\Framework\TestCase;
use PDO;

class ListGeneratorTest extends TestCase
{
    private PDO $pdo;
    private ListGenerator $generator;
    private array $schema;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->schema = [
            'table' => 'users',
            'primary_key' => 'id',
            'columns' => [
                ['name' => 'id', 'is_primary' => true, 'sql_type' => 'int', 'metadata' => []],
                ['name' => 'name', 'is_primary' => false, 'sql_type' => 'varchar', 'metadata' => []],
                ['name' => 'email', 'is_primary' => false, 'sql_type' => 'varchar', 'metadata' => []],
            ]
        ];
        
        $this->generator = new ListGenerator($this->pdo, $this->schema);
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function createTestData(): void
    {
        $this->cleanupTestData();
        
        for ($i = 1; $i <= 25; $i++) {
            $this->pdo->exec("INSERT INTO users (name, email, password) VALUES ('User $i', 'user$i@test.com', 'test123')");
        }
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE '%@test.com'");
    }

    public function testListDefaultPagination(): void
    {
        $result = $this->generator->list();
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(20, $result['data']);
        $this->assertEquals(1, $result['pagination']['page']);
        $this->assertEquals(20, $result['pagination']['perPage']);
        $this->assertGreaterThanOrEqual(25, $result['pagination']['total']);
    }

    public function testListCustomPerPage(): void
    {
        $result = $this->generator->list(['perPage' => 10]);
        
        $this->assertCount(10, $result['data']);
        $this->assertEquals(10, $result['pagination']['perPage']);
        $this->assertGreaterThanOrEqual(3, $result['pagination']['totalPages']);
    }

    public function testListSecondPage(): void
    {
        $result = $this->generator->list(['page' => 2, 'perPage' => 10]);
        
        $this->assertCount(10, $result['data']);
        $this->assertEquals(2, $result['pagination']['page']);
    }

    public function testListLastPage(): void
    {
        $result = $this->generator->list(['page' => 3, 'perPage' => 10]);
        
        $this->assertLessThanOrEqual(10, count($result['data']));
        $this->assertEquals(3, $result['pagination']['page']);
    }

    public function testListWithFilters(): void
    {
        $result = $this->generator->list(['filters' => ['name' => 'User 1']]);
        
        $this->assertCount(1, $result['data']);
        $this->assertEquals('User 1', $result['data'][0]['name']);
    }

    public function testListWithSortAsc(): void
    {
        $result = $this->generator->list([
            'sort' => ['name' => 'ASC'],
            'perPage' => 5,
            'filters' => ['email' => 'user1@test.com']
        ]);
        
        $this->assertNotEmpty($result['data']);
        $this->assertEquals('User 1', $result['data'][0]['name']);
    }

    public function testListWithSortDesc(): void
    {
        $result = $this->generator->list(['sort' => ['name' => 'DESC'], 'perPage' => 5]);
        
        $this->assertEquals('User 9', $result['data'][0]['name']);
    }

    public function testRenderTableWithData(): void
    {
        $result = $this->generator->list(['perPage' => 2]);
        $html = $this->generator->renderTable($result['data']);
        
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('Eliminar', $html);
    }

    public function testRenderTableEmpty(): void
    {
        $html = $this->generator->renderTable([]);
        
        $this->assertStringContainsString('No hay registros', $html);
    }

    public function testRenderPaginationMultiplePages(): void
    {
        $pagination = ['page' => 2, 'totalPages' => 5];
        $html = $this->generator->renderPagination($pagination);
        
        $this->assertStringContainsString('Anterior', $html);
        $this->assertStringContainsString('Siguiente', $html);
        $this->assertStringContainsString('Página 2 de 5', $html);
    }

    public function testRenderPaginationFirstPage(): void
    {
        $pagination = ['page' => 1, 'totalPages' => 3];
        $html = $this->generator->renderPagination($pagination);
        
        $this->assertStringNotContainsString('Anterior', $html);
        $this->assertStringContainsString('Siguiente', $html);
    }

    public function testRenderPaginationLastPage(): void
    {
        $pagination = ['page' => 3, 'totalPages' => 3];
        $html = $this->generator->renderPagination($pagination);
        
        $this->assertStringContainsString('Anterior', $html);
        $this->assertStringNotContainsString('Siguiente', $html);
    }

    public function testRenderPaginationSinglePage(): void
    {
        $pagination = ['page' => 1, 'totalPages' => 1];
        $html = $this->generator->renderPagination($pagination);
        
        $this->assertEmpty($html);
    }
}
