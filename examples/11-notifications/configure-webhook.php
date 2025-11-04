<?php
/**
 * Helper script to configure webhook URL
 * 
 * Usage:
 * 1. Go to https://webhook.site and copy your unique URL
 * 2. Run: php configure-webhook.php YOUR_WEBHOOK_URL
 * 3. Test webhooks.php
 */

if ($argc < 2) {
    echo "Usage: php configure-webhook.php YOUR_WEBHOOK_URL\n";
    echo "Example: php configure-webhook.php https://webhook.site/abc123-def456\n";
    exit(1);
}

$webhookUrl = $argv[1];

// Validate URL
if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
    echo "Error: Invalid URL format\n";
    exit(1);
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get current comment
    $stmt = $pdo->query("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'test' AND TABLE_NAME = 'notif_contacts'");
    $currentComment = $stmt->fetchColumn();
    
    // Decode JSON
    $metadata = json_decode(html_entity_decode($currentComment), true);
    
    if (!$metadata) {
        echo "Error: Could not parse table metadata\n";
        exit(1);
    }
    
    // Update webhook URL
    $metadata['webhooks'][0]['url'] = $webhookUrl;
    
    // Encode and update
    $newComment = json_encode($metadata);
    $sql = "ALTER TABLE notif_contacts COMMENT = " . $pdo->quote($newComment);
    $pdo->exec($sql);
    
    echo "✅ Webhook URL updated successfully!\n";
    echo "📧 Table: notif_contacts\n";
    echo "🔗 URL: $webhookUrl\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Open webhooks.php in your browser\n";
    echo "2. Create a new contact\n";
    echo "3. Check webhook.site for the payload\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
