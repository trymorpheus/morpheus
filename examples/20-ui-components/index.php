<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\UI\Components;

// Set custom theme (optional)
Components::setTheme([
    'primary' => '#667eea',
    'success' => '#48bb78',
    'danger' => '#f56565',
    'warning' => '#ed8936'
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI Components Library - DynamicCRUD</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f7fafc; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header h1 { font-size: 36px; margin-bottom: 10px; }
        .header p { font-size: 18px; opacity: 0.9; }
        .section { background: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section h2 { font-size: 24px; margin-bottom: 20px; color: #2d3748; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .demo { margin-bottom: 30px; }
        .demo h3 { font-size: 18px; margin-bottom: 12px; color: #4a5568; }
        .code { background-color: #2d3748; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 14px; overflow-x: auto; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: white; color: #667eea; text-decoration: none; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .back-link:hover { background-color: #f7fafc; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.html" class="back-link">← Back to Examples</a>

        <div class="header">
            <h1>🎨 UI Components Library</h1>
            <p>Reusable, accessible, and beautiful UI components for your applications</p>
        </div>

        <!-- Alerts -->
        <div class="section">
            <h2>Alerts</h2>
            
            <div class="demo">
                <h3>Success Alert</h3>
                <?= Components::alert('Your changes have been saved successfully!', 'success') ?>
                <div class="code">Components::alert('Your changes have been saved successfully!', 'success')</div>
            </div>

            <div class="demo">
                <h3>Danger Alert</h3>
                <?= Components::alert('An error occurred while processing your request.', 'danger') ?>
                <div class="code">Components::alert('An error occurred while processing your request.', 'danger')</div>
            </div>

            <div class="demo">
                <h3>Warning Alert</h3>
                <?= Components::alert('Please review your information before submitting.', 'warning') ?>
                <div class="code">Components::alert('Please review your information before submitting.', 'warning')</div>
            </div>

            <div class="demo">
                <h3>Info Alert (Non-dismissible)</h3>
                <?= Components::alert('This is an informational message.', 'info', false) ?>
                <div class="code">Components::alert('This is an informational message.', 'info', false)</div>
            </div>
        </div>

        <!-- Badges -->
        <div class="section">
            <h2>Badges</h2>
            
            <div class="demo">
                <h3>Badge Types</h3>
                <?= Components::badge('Primary', 'primary') ?>
                <?= Components::badge('Success', 'success') ?>
                <?= Components::badge('Danger', 'danger') ?>
                <?= Components::badge('Warning', 'warning') ?>
                <?= Components::badge('Info', 'info') ?>
                <div class="code">Components::badge('Primary', 'primary')</div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="section">
            <h2>Buttons</h2>
            
            <div class="demo">
                <h3>Button Types</h3>
                <?= Components::button('Primary', 'primary') ?>
                <?= Components::button('Success', 'success') ?>
                <?= Components::button('Danger', 'danger') ?>
                <?= Components::button('Warning', 'warning') ?>
                <div class="code">Components::button('Primary', 'primary')</div>
            </div>

            <div class="demo">
                <h3>Button Sizes</h3>
                <?= Components::button('Small', 'primary', ['size' => 'small']) ?>
                <?= Components::button('Medium', 'primary', ['size' => 'medium']) ?>
                <?= Components::button('Large', 'primary', ['size' => 'large']) ?>
                <div class="code">Components::button('Small', 'primary', ['size' => 'small'])</div>
            </div>

            <div class="demo">
                <h3>Button Group</h3>
                <?= Components::buttonGroup([
                    ['text' => 'Edit', 'type' => 'primary', 'onclick' => 'alert("Edit clicked")'],
                    ['text' => 'Delete', 'type' => 'danger', 'onclick' => 'alert("Delete clicked")'],
                    ['text' => 'View', 'type' => 'info', 'onclick' => 'alert("View clicked")']
                ]) ?>
                <div class="code">Components::buttonGroup([...])</div>
            </div>
        </div>

        <!-- Cards -->
        <div class="section">
            <h2>Cards</h2>
            
            <div class="grid">
                <?= Components::card(
                    'Simple Card',
                    '<p>This is a simple card with just a title and content.</p>'
                ) ?>

                <?= Components::card(
                    'Card with Footer',
                    '<p>This card includes a footer section with actions.</p>',
                    Components::button('Action', 'primary') . ' ' . Components::button('Cancel', 'secondary')
                ) ?>
            </div>
            <div class="code">Components::card('Title', 'Content', 'Footer')</div>
        </div>

        <!-- Stat Cards -->
        <div class="section">
            <h2>Stat Cards</h2>
            
            <div class="grid">
                <?= Components::statCard('Total Users', '1,234', 'up', '+12%') ?>
                <?= Components::statCard('Revenue', '$45,678', 'up', '+8.5%') ?>
                <?= Components::statCard('Orders', '892', 'down', '-3.2%') ?>
                <?= Components::statCard('Active Sessions', '156') ?>
            </div>
            <div class="code">Components::statCard('Total Users', '1,234', 'up', '+12%')</div>
        </div>

        <!-- Progress Bar -->
        <div class="section">
            <h2>Progress Bars</h2>
            
            <div class="demo">
                <?= Components::progressBar(75, 'Upload Progress') ?>
            </div>
            <div class="demo">
                <?= Components::progressBar(45, 'Processing...') ?>
            </div>
            <div class="demo">
                <?= Components::progressBar(100, 'Complete!') ?>
            </div>
            <div class="code">Components::progressBar(75, 'Upload Progress')</div>
        </div>

        <!-- Breadcrumb -->
        <div class="section">
            <h2>Breadcrumb</h2>
            
            <div class="demo">
                <?= Components::breadcrumb([
                    ['text' => 'Home', 'href' => '#'],
                    ['text' => 'Products', 'href' => '#'],
                    ['text' => 'Electronics', 'href' => '#'],
                    'Laptop'
                ]) ?>
                <div class="code">Components::breadcrumb([['text' => 'Home', 'href' => '#'], 'Current'])</div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="section">
            <h2>Pagination</h2>
            
            <div class="demo">
                <?= Components::pagination(3, 10, '?page=') ?>
                <div class="code">Components::pagination(3, 10, '?page=')</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="section">
            <h2>Tabs</h2>
            
            <div class="demo" id="tabs">
                <?= Components::tabs([
                    'profile' => [
                        'title' => 'Profile',
                        'content' => '<h3>Profile Information</h3><p>Manage your profile settings and preferences.</p>'
                    ],
                    'settings' => [
                        'title' => 'Settings',
                        'content' => '<h3>Account Settings</h3><p>Configure your account options and security.</p>'
                    ],
                    'notifications' => [
                        'title' => 'Notifications',
                        'content' => '<h3>Notification Preferences</h3><p>Choose how you want to be notified.</p>'
                    ]
                ]) ?>
                <div class="code">Components::tabs(['tab1' => ['title' => 'Tab 1', 'content' => '...']])</div>
            </div>
        </div>

        <!-- Accordion -->
        <div class="section">
            <h2>Accordion</h2>
            
            <div class="demo">
                <?= Components::accordion([
                    ['title' => 'What is DynamicCRUD?', 'content' => '<p>Morpheus is a powerful PHP library that automatically generates CRUD forms with validation based on your database structure.</p>'],
                    ['title' => 'How do I install it?', 'content' => '<p>Simply run: <code>composer require trymorpheus/morpheus</code></p>'],
                    ['title' => 'Is it free?', 'content' => '<p>Yes! Morpheus is open-source and licensed under MIT.</p>']
                ]) ?>
                <div class="code">Components::accordion([['title' => 'Question', 'content' => 'Answer']])</div>
            </div>
        </div>

        <!-- Table -->
        <div class="section">
            <h2>Table</h2>
            
            <div class="demo">
                <?= Components::table(
                    ['Name', 'Email', 'Role', 'Status'],
                    [
                        ['John Doe', 'john@example.com', 'Admin', Components::badge('Active', 'success')],
                        ['Jane Smith', 'jane@example.com', 'User', Components::badge('Active', 'success')],
                        ['Bob Johnson', 'bob@example.com', 'User', Components::badge('Inactive', 'secondary')],
                        ['Alice Brown', 'alice@example.com', 'Manager', Components::badge('Active', 'success')]
                    ],
                    ['striped' => true, 'hover' => true]
                ) ?>
                <div class="code">Components::table(['Header1', 'Header2'], [['Cell1', 'Cell2']])</div>
            </div>
        </div>

        <!-- Dropdown -->
        <div class="section">
            <h2>Dropdown</h2>
            
            <div class="demo">
                <?= Components::dropdown('Actions', [
                    ['text' => 'Edit', 'href' => '#edit'],
                    ['text' => 'Delete', 'href' => '#delete'],
                    ['text' => 'Archive', 'href' => '#archive']
                ]) ?>
                <div class="code">Components::dropdown('Actions', [['text' => 'Edit', 'href' => '#']])</div>
            </div>
        </div>

        <!-- Modal -->
        <div class="section">
            <h2>Modal</h2>
            
            <div class="demo">
                <?= Components::button('Open Modal', 'primary', ['onclick' => "document.getElementById('demo-modal').style.display='block'"]) ?>
                
                <?= Components::modal(
                    'demo-modal',
                    'Confirm Action',
                    '<p>Are you sure you want to proceed with this action? This cannot be undone.</p>',
                    ['primary_button' => 'Confirm', 'close_button' => 'Cancel']
                ) ?>
                <div class="code">Components::modal('id', 'Title', 'Content', ['primary_button' => 'OK'])</div>
            </div>
        </div>

        <!-- Toast -->
        <div class="section">
            <h2>Toast Notifications</h2>
            
            <div class="demo">
                <?= Components::button('Show Success Toast', 'success', ['onclick' => "showToast('success')"]) ?>
                <?= Components::button('Show Error Toast', 'danger', ['onclick' => "showToast('danger')"]) ?>
                <?= Components::button('Show Info Toast', 'info', ['onclick' => "showToast('info')"]) ?>
                <div class="code">Components::toast('Message', 'success', 3000)</div>
            </div>
        </div>

        <script>
        function showToast(type) {
            var messages = {
                'success': 'Operation completed successfully!',
                'danger': 'An error occurred!',
                'info': 'This is an informational message.'
            };
            
            var toast = document.createElement('div');
            toast.innerHTML = <?= json_encode(Components::toast('PLACEHOLDER', 'TYPE', 3000)) ?>;
            toast.innerHTML = toast.innerHTML.replace('PLACEHOLDER', messages[type]).replace('TYPE', type);
            document.body.appendChild(toast.firstElementChild);
        }
        </script>
    </div>
</body>
</html>
