<?php

namespace Morpheus\CLI\Commands;

use PDO;

class ConfigureWebhookCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0]) || empty($args[1])) {
            $this->error('Table name and webhook URL required');
            $this->info('Usage: php dynamiccrud webhook:configure <table> <url> [--event=on_create]');
            exit(1);
        }

        $table = $args[0];
        $url = $args[1];
        $event = $this->getOption($args, '--event') ?? 'on_create';

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL format');
            exit(1);
        }

        $pdo = $this->getPDO();

        // Get current metadata
        $stmt = $pdo->prepare("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        $comment = $stmt->fetchColumn();

        if ($comment === false) {
            $this->error("Table '$table' not found");
            exit(1);
        }

        $metadata = $comment ? json_decode(html_entity_decode($comment), true) : [];
        if (!is_array($metadata)) {
            $metadata = [];
        }

        // Add/update webhook
        if (!isset($metadata['webhooks'])) {
            $metadata['webhooks'] = [];
        }

        $metadata['webhooks'][] = [
            'event' => $event,
            'url' => $url,
            'method' => 'POST',
            'headers' => []
        ];

        // Update table comment
        $newComment = json_encode($metadata);
        $sql = "ALTER TABLE `$table` COMMENT = " . $pdo->quote($newComment);
        $pdo->exec($sql);

        $this->success("Webhook configured for table '$table'");
        echo "  Event: $event\n";
        echo "  URL: $url\n";
    }


}
