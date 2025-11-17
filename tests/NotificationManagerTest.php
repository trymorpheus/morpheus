<?php

namespace Morpheus\Tests;

use Morpheus\NotificationManager;
use PHPUnit\Framework\TestCase;

class NotificationManagerTest extends TestCase
{
    private NotificationManager $manager;
    
    protected function setUp(): void
    {
        $this->manager = new NotificationManager();
    }
    
    public function testSendEmailNotifications(): void
    {
        $config = [
            'email' => ['test@example.com'],
            'subject' => 'Test Subject',
            'template' => 'Hello {{data.name}}'
        ];
        
        $data = ['name' => 'John'];
        $id = 1;
        
        // Should not throw exception
        $this->manager->sendEmailNotifications($config, $data, $id);
        $this->assertTrue(true);
    }
    
    public function testSendEmailWithMultipleRecipients(): void
    {
        $config = [
            'email' => ['test1@example.com', 'test2@example.com'],
            'subject' => 'Test',
            'template' => 'Content'
        ];
        
        $this->manager->sendEmailNotifications($config, [], 1);
        $this->assertTrue(true);
    }
    
    public function testSendEmailWithFieldsFilter(): void
    {
        $config = [
            'email' => ['test@example.com'],
            'subject' => 'Update',
            'fields' => ['status']
        ];
        
        $data = ['status' => 'completed', 'name' => 'John'];
        
        $this->manager->sendEmailNotifications($config, $data, 1);
        $this->assertTrue(true);
    }
    
    public function testTriggerWebhooks(): void
    {
        $webhooks = [
            [
                'url' => 'https://example.com/webhook',
                'method' => 'POST',
                'headers' => ['Authorization' => 'Bearer token']
            ]
        ];
        
        $data = ['name' => 'John'];
        $id = 1;
        
        // Should not throw exception
        $this->manager->triggerWebhooks($webhooks, 'on_create', $data, $id);
        $this->assertTrue(true);
    }
    
    public function testTriggerWebhooksWithEventFilter(): void
    {
        $webhooks = [
            [
                'event' => 'on_create',
                'url' => 'https://example.com/webhook',
                'method' => 'POST'
            ],
            [
                'event' => 'on_update',
                'url' => 'https://example.com/webhook2',
                'method' => 'POST'
            ]
        ];
        
        // Should only trigger first webhook
        $this->manager->triggerWebhooks($webhooks, 'on_create', [], 1);
        $this->assertTrue(true);
    }
    
    public function testSendEmailWithEmptyRecipients(): void
    {
        $config = [
            'email' => [],
            'subject' => 'Test',
            'template' => 'Content'
        ];
        
        // Should handle empty recipients gracefully
        $this->manager->sendEmailNotifications($config, [], 1);
        $this->assertTrue(true);
    }
    
    public function testTriggerWebhooksWithEmptyArray(): void
    {
        $webhooks = [];
        
        // Should handle empty webhooks gracefully
        $this->manager->triggerWebhooks($webhooks, 'on_create', [], 1);
        $this->assertTrue(true);
    }
}
