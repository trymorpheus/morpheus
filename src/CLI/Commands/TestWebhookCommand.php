<?php

namespace DynamicCRUD\CLI\Commands;

use PDO;

class TestWebhookCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            $this->info('Usage: php dynamiccrud test:webhook <table>');
            exit(1);
        }

        $table = $args[0];
        $pdo = $this->getPDO();

        $this->info("Testing webhooks for table: $table");

        // Get table metadata
        $stmt = $pdo->prepare("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        $comment = $stmt->fetchColumn();

        if (!$comment) {
            $this->error("Table '$table' not found");
            exit(1);
        }

        $metadata = json_decode(html_entity_decode($comment), true);

        if (!isset($metadata['webhooks']) || empty($metadata['webhooks'])) {
            $this->warning("No webhooks configured for table '$table'");
            exit(0);
        }

        $this->success("Found " . count($metadata['webhooks']) . " webhook(s)");

        foreach ($metadata['webhooks'] as $i => $webhook) {
            echo "\n";
            $this->info("Webhook #" . ($i + 1));
            echo "  URL: " . ($webhook['url'] ?? 'NOT SET') . "\n";
            echo "  Method: " . ($webhook['method'] ?? 'POST') . "\n";
            echo "  Event: " . ($webhook['event'] ?? 'NOT SET') . "\n";

            if (!isset($webhook['url'])) {
                $this->warning("  Skipping - no URL configured");
                continue;
            }

            // Test webhook
            $payload = [
                'event' => 'test',
                'table' => $table,
                'id' => 999,
                'data' => ['test' => 'CLI test'],
                'timestamp' => date('c')
            ];

            $ch = curl_init($webhook['url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
            $headers = ['Content-Type: application/json'];
            if (isset($webhook['headers'])) {
                foreach ($webhook['headers'] as $key => $value) {
                    $headers[] = "$key: $value";
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $this->error("  Failed: $error");
            } elseif ($httpCode >= 200 && $httpCode < 300) {
                $this->success("  ✓ Success (HTTP $httpCode)");
            } else {
                $this->warning("  HTTP $httpCode");
            }
        }

        echo "\n";
    }
}
