<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Workflow\WorkflowEngine;
use PDO;

class WorkflowEngineTest extends TestCase
{
    private PDO $pdo;
    private WorkflowEngine $workflow;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create test table
        $this->pdo->exec("DROP TABLE IF EXISTS test_orders");
        $this->pdo->exec("CREATE TABLE test_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            status VARCHAR(50) NOT NULL DEFAULT 'pending'
        )");
        
        $this->pdo->exec("INSERT INTO test_orders (status) VALUES ('pending')");
        
        $this->workflow = new WorkflowEngine($this->pdo, 'test_orders', [
            'field' => 'status',
            'states' => ['pending', 'processing', 'shipped', 'delivered'],
            'transitions' => [
                'process' => [
                    'from' => 'pending',
                    'to' => 'processing',
                    'label' => 'Process',
                    'permissions' => ['admin']
                ],
                'ship' => [
                    'from' => 'processing',
                    'to' => 'shipped'
                ]
            ]
        ]);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS test_orders");
        $this->pdo->exec("DROP TABLE IF EXISTS _workflow_history");
    }

    public function testGetStates()
    {
        $states = $this->workflow->getStates();
        $this->assertEquals(['pending', 'processing', 'shipped', 'delivered'], $states);
    }

    public function testGetTransitions()
    {
        $transitions = $this->workflow->getTransitions();
        $this->assertArrayHasKey('process', $transitions);
        $this->assertArrayHasKey('ship', $transitions);
    }

    public function testGetCurrentState()
    {
        $state = $this->workflow->getCurrentState(1);
        $this->assertEquals('pending', $state);
    }

    public function testCanTransitionWithValidState()
    {
        $can = $this->workflow->canTransition('process', 'pending');
        $this->assertTrue($can);
    }

    public function testCanTransitionWithInvalidState()
    {
        $can = $this->workflow->canTransition('process', 'shipped');
        $this->assertFalse($can);
    }

    public function testCanTransitionWithPermissions()
    {
        $user = ['id' => 1, 'role' => 'admin'];
        $can = $this->workflow->canTransition('process', 'pending', $user);
        $this->assertTrue($can);
        
        $user = ['id' => 1, 'role' => 'guest'];
        $can = $this->workflow->canTransition('process', 'pending', $user);
        $this->assertFalse($can);
    }

    public function testGetAvailableTransitions()
    {
        $available = $this->workflow->getAvailableTransitions('pending');
        $this->assertArrayHasKey('process', $available);
        $this->assertArrayNotHasKey('ship', $available);
    }

    public function testTransitionSuccess()
    {
        $result = $this->workflow->transition(1, 'process');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('pending', $result['from']);
        $this->assertEquals('processing', $result['to']);
        
        $newState = $this->workflow->getCurrentState(1);
        $this->assertEquals('processing', $newState);
    }

    public function testTransitionFailure()
    {
        $result = $this->workflow->transition(1, 'ship');
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testTransitionWithHistory()
    {
        $workflow = new WorkflowEngine($this->pdo, 'test_orders', [
            'field' => 'status',
            'states' => ['pending', 'processing'],
            'transitions' => [
                'process' => ['from' => 'pending', 'to' => 'processing']
            ],
            'history' => true
        ]);
        
        $result = $workflow->transition(1, 'process', ['id' => 1, 'role' => 'admin']);
        $this->assertTrue($result['success']);
        
        $history = $workflow->getHistory(1);
        $this->assertGreaterThanOrEqual(1, count($history));
        $this->assertEquals('process', $history[0]['transition']);
        $this->assertEquals('pending', $history[0]['from_state']);
        $this->assertEquals('processing', $history[0]['to_state']);
    }

    public function testHooks()
    {
        $beforeCalled = false;
        $afterCalled = false;
        
        $this->workflow->addHook('before_process', function() use (&$beforeCalled) {
            $beforeCalled = true;
        });
        
        $this->workflow->addHook('after_process', function() use (&$afterCalled) {
            $afterCalled = true;
        });
        
        $this->workflow->transition(1, 'process');
        
        $this->assertTrue($beforeCalled);
        $this->assertTrue($afterCalled);
    }

    public function testRenderTransitionButtons()
    {
        $html = $this->workflow->renderTransitionButtons(1);
        
        $this->assertStringContainsString('workflow-transitions', $html);
        $this->assertStringContainsString('Process', $html);
        $this->assertStringContainsString('pending', $html);
    }

    public function testRenderStateColumn()
    {
        $html = $this->workflow->renderStateColumn('pending');
        
        $this->assertStringContainsString('workflow-state-badge', $html);
        $this->assertStringContainsString('Pending', $html); // Label is capitalized
    }
}
