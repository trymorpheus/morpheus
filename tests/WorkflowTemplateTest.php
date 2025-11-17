<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Workflow\WorkflowTemplate;

class WorkflowTemplateTest extends TestCase
{
    public function testOrderManagementTemplate(): void
    {
        $config = WorkflowTemplate::orderManagement();
        
        $this->assertArrayHasKey('field', $config);
        $this->assertArrayHasKey('states', $config);
        $this->assertArrayHasKey('transitions', $config);
        $this->assertEquals('status', $config['field']);
        $this->assertContains('pending', $config['states']);
        $this->assertArrayHasKey('process', $config['transitions']);
    }
    
    public function testTicketSupportTemplate(): void
    {
        $config = WorkflowTemplate::ticketSupport();
        
        $this->assertEquals('status', $config['field']);
        $this->assertContains('open', $config['states']);
        $this->assertArrayHasKey('start', $config['transitions']);
    }
    
    public function testApprovalProcessTemplate(): void
    {
        $config = WorkflowTemplate::approvalProcess();
        
        $this->assertEquals('approval_status', $config['field']);
        $this->assertContains('draft', $config['states']);
        $this->assertArrayHasKey('approve', $config['transitions']);
    }
    
    public function testContentPublishingTemplate(): void
    {
        $config = WorkflowTemplate::contentPublishing();
        
        $this->assertEquals('status', $config['field']);
        $this->assertContains('draft', $config['states']);
        $this->assertArrayHasKey('publish', $config['transitions']);
    }
}
