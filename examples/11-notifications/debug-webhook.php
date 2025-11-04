<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get webhook config
$stmt = $pdo->query("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'test' AND TABLE_NAME = 'notif_contacts'");
$comment = $stmt->fetchColumn();
$metadata = json_decode(html_entity_decode($comment), true);

echo "<pre>";
echo "=== WEBHOOK DEBUG ===\n\n";
echo "Table metadata:\n";
print_r($metadata);

if (isset($metadata['webhooks'])) {
    echo "\n\nWebhooks configured: " . count($metadata['webhooks']) . "\n";
    
    foreach ($metadata['webhooks'] as $i => $webhook) {
        echo "\nWebhook #$i:\n";
        echo "  URL: " . ($webhook['url'] ?? 'NOT SET') . "\n";
        echo "  Method: " . ($webhook['method'] ?? 'POST') . "\n";
        echo "  Event: " . ($webhook['event'] ?? 'NOT SET') . "\n";
        
        // Test the webhook
        if (isset($webhook['url'])) {
            echo "\n  Testing webhook...\n";
            
            $payload = [
                'event' => 'test',
                'id' => 999,
                'data' => ['test' => 'debug'],
                'timestamp' => date('c')
            ];
            
            $ch = curl_init($webhook['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            echo "  HTTP Code: $httpCode\n";
            echo "  Response: " . substr($response, 0, 100) . "\n";
            if ($error) {
                echo "  Error: $error\n";
            } else {
                echo "  ✅ Request sent successfully!\n";
            }
        }
    }
} else {
    echo "\n❌ No webhooks configured!\n";
}

echo "\n\n=== curl_init available: " . (function_exists('curl_init') ? 'YES' : 'NO') . " ===\n";
echo "</pre>";
