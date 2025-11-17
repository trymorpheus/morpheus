<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\UI\Components;

class ComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        Components::setTheme([
            'primary' => '#667eea',
            'success' => '#48bb78',
            'danger' => '#f56565'
        ]);
    }

    public function testAlert(): void
    {
        $html = Components::alert('Test message', 'success');
        
        $this->assertStringContainsString('Test message', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('#d4edda', $html); // Success background
    }

    public function testAlertNonDismissible(): void
    {
        $html = Components::alert('Test', 'info', false);
        
        $this->assertStringNotContainsString('&times;', $html);
    }

    public function testBadge(): void
    {
        $html = Components::badge('New', 'primary');
        
        $this->assertStringContainsString('New', $html);
        $this->assertStringContainsString('#667eea', $html);
        $this->assertStringContainsString('span', $html);
    }

    public function testButton(): void
    {
        $html = Components::button('Click Me', 'primary');
        
        $this->assertStringContainsString('Click Me', $html);
        $this->assertStringContainsString('button', $html);
        $this->assertStringContainsString('#667eea', $html);
    }

    public function testButtonWithHref(): void
    {
        $html = Components::button('Link', 'primary', ['href' => '/page']);
        
        $this->assertStringContainsString('<a href="/page"', $html);
        $this->assertStringContainsString('Link', $html);
    }

    public function testButtonSizes(): void
    {
        $small = Components::button('Small', 'primary', ['size' => 'small']);
        $large = Components::button('Large', 'primary', ['size' => 'large']);
        
        $this->assertStringContainsString('6px 12px', $small);
        $this->assertStringContainsString('12px 24px', $large);
    }

    public function testButtonGroup(): void
    {
        $html = Components::buttonGroup([
            ['text' => 'Edit', 'type' => 'primary', 'onclick' => 'edit()'],
            ['text' => 'Delete', 'type' => 'danger', 'onclick' => 'delete()']
        ]);
        
        $this->assertStringContainsString('Edit', $html);
        $this->assertStringContainsString('Delete', $html);
        $this->assertStringContainsString('edit()', $html);
    }

    public function testCard(): void
    {
        $html = Components::card('Title', '<p>Content</p>');
        
        $this->assertStringContainsString('Title', $html);
        $this->assertStringContainsString('<p>Content</p>', $html);
    }

    public function testCardWithFooter(): void
    {
        $html = Components::card('Title', 'Content', 'Footer');
        
        $this->assertStringContainsString('Footer', $html);
    }

    public function testStatCard(): void
    {
        $html = Components::statCard('Users', '1,234', 'up', '+12%');
        
        $this->assertStringContainsString('Users', $html);
        $this->assertStringContainsString('1,234', $html);
        $this->assertStringContainsString('+12%', $html);
        $this->assertStringContainsString('↑', $html);
    }

    public function testStatCardWithoutTrend(): void
    {
        $html = Components::statCard('Users', '1,234');
        
        $this->assertStringContainsString('Users', $html);
        $this->assertStringContainsString('1,234', $html);
        $this->assertStringNotContainsString('↑', $html);
    }

    public function testProgressBar(): void
    {
        $html = Components::progressBar(75, 'Loading');
        
        $this->assertStringContainsString('Loading', $html);
        $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('width: 75%', $html);
    }

    public function testProgressBarBounds(): void
    {
        $html = Components::progressBar(150); // Should cap at 100
        $this->assertStringContainsString('width: 100%', $html);
        
        $html = Components::progressBar(-10); // Should floor at 0
        $this->assertStringContainsString('width: 0%', $html);
    }

    public function testBreadcrumb(): void
    {
        $html = Components::breadcrumb([
            ['text' => 'Home', 'href' => '/'],
            'Current'
        ]);
        
        $this->assertStringContainsString('Home', $html);
        $this->assertStringContainsString('Current', $html);
        $this->assertStringContainsString('href="/"', $html);
        $this->assertStringContainsString('breadcrumb', $html);
    }

    public function testPagination(): void
    {
        $html = Components::pagination(3, 10, '?page=');
        
        $this->assertStringContainsString('?page=2', $html); // Previous
        $this->assertStringContainsString('?page=4', $html); // Next
        $this->assertStringContainsString('pagination', $html);
    }

    public function testPaginationSinglePage(): void
    {
        $html = Components::pagination(1, 1);
        
        $this->assertEmpty($html);
    }

    public function testTabs(): void
    {
        $html = Components::tabs([
            'tab1' => ['title' => 'Tab 1', 'content' => 'Content 1'],
            'tab2' => ['title' => 'Tab 2', 'content' => 'Content 2']
        ]);
        
        $this->assertStringContainsString('Tab 1', $html);
        $this->assertStringContainsString('Tab 2', $html);
        $this->assertStringContainsString('Content 1', $html);
        $this->assertStringContainsString('openTab', $html);
    }

    public function testAccordion(): void
    {
        $html = Components::accordion([
            ['title' => 'Question 1', 'content' => 'Answer 1'],
            ['title' => 'Question 2', 'content' => 'Answer 2']
        ]);
        
        $this->assertStringContainsString('Question 1', $html);
        $this->assertStringContainsString('Answer 1', $html);
        $this->assertStringContainsString('toggleAccordion', $html);
    }

    public function testTable(): void
    {
        $html = Components::table(
            ['Name', 'Email'],
            [
                ['John', 'john@example.com'],
                ['Jane', 'jane@example.com']
            ]
        );
        
        $this->assertStringContainsString('Name', $html);
        $this->assertStringContainsString('Email', $html);
        $this->assertStringContainsString('John', $html);
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringContainsString('<table', $html);
    }

    public function testTableWithOptions(): void
    {
        $html = Components::table(
            ['Col1'],
            [['Data1']],
            ['striped' => false, 'hover' => false]
        );
        
        $this->assertStringContainsString('Data1', $html);
    }

    public function testDropdown(): void
    {
        $html = Components::dropdown('Actions', [
            ['text' => 'Edit', 'href' => '#edit'],
            ['text' => 'Delete', 'href' => '#delete']
        ]);
        
        $this->assertStringContainsString('Actions', $html);
        $this->assertStringContainsString('Edit', $html);
        $this->assertStringContainsString('#edit', $html);
    }

    public function testModal(): void
    {
        $html = Components::modal('test-modal', 'Title', 'Content');
        
        $this->assertStringContainsString('id="test-modal"', $html);
        $this->assertStringContainsString('Title', $html);
        $this->assertStringContainsString('Content', $html);
        $this->assertStringContainsString('display: none', $html);
    }

    public function testModalWithButtons(): void
    {
        $html = Components::modal('test', 'Title', 'Content', [
            'primary_button' => 'Confirm',
            'close_button' => 'Cancel'
        ]);
        
        $this->assertStringContainsString('Confirm', $html);
        $this->assertStringContainsString('Cancel', $html);
    }

    public function testToast(): void
    {
        $html = Components::toast('Success!', 'success', 3000);
        
        $this->assertStringContainsString('Success!', $html);
        $this->assertStringContainsString('3000', $html);
        $this->assertStringContainsString('setTimeout', $html);
    }

    public function testSetTheme(): void
    {
        Components::setTheme(['primary' => '#ff0000']);
        $html = Components::badge('Test', 'primary');
        
        $this->assertStringContainsString('#ff0000', $html);
    }

    public function testXSSProtection(): void
    {
        $html = Components::alert('<script>alert("xss")</script>', 'info');
        
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
