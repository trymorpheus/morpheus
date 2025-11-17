<?php

namespace Morpheus;

class NotificationManager
{
    public function sendEmailNotifications(array $config, array $data, int $id): void
    {
        if (!$this->hasRecipients($config)) {
            return;
        }

        $recipients = $this->getRecipients($config);
        $subject = $this->getSubject($config);
        $body = $this->prepareEmailBody($config, $data, $id);

        $this->sendToRecipients($recipients, $subject, $body, $data, $id);
    }

    private function hasRecipients(array $config): bool
    {
        return !empty($config['email']);
    }

    private function getRecipients(array $config): array
    {
        return $config['email'];
    }

    private function getSubject(array $config): string
    {
        return $config['subject'] ?? 'Notification';
    }

    private function prepareEmailBody(array $config, array $data, int $id): string
    {
        $template = $config['template'] ?? null;
        
        if ($template) {
            return $this->replacePlaceholders($template, $data, $id);
        }
        
        return json_encode($data);
    }

    private function sendToRecipients(array $recipients, string $subject, string $body, array $data, int $id): void
    {
        foreach ($recipients as $recipient) {
            $this->sendEmail($recipient, $subject, $body, $data, $id);
        }
    }

    public function triggerWebhooks(array $webhooks, string $event, array $data, int $id): void
    {
        foreach ($webhooks as $webhook) {
            if (!$this->shouldTriggerWebhook($webhook, $event)) {
                continue;
            }

            $this->executeWebhook($webhook, $event, $data, $id);
        }
    }

    private function shouldTriggerWebhook(array $webhook, string $event): bool
    {
        if (!isset($webhook['event'])) {
            return true;
        }
        
        return $webhook['event'] === $event;
    }

    private function executeWebhook(array $webhook, string $event, array $data, int $id): void
    {
        $url = $webhook['url'];
        $method = $this->getWebhookMethod($webhook);
        $headers = $this->getWebhookHeaders($webhook);
        $payload = $this->buildWebhookPayload($event, $data, $id);

        $this->callWebhook($url, $method, $payload, $headers);
    }

    private function getWebhookMethod(array $webhook): string
    {
        return $webhook['method'] ?? 'POST';
    }

    private function getWebhookHeaders(array $webhook): array
    {
        return $webhook['headers'] ?? [];
    }

    private function buildWebhookPayload(string $event, array $data, int $id): array
    {
        return [
            'event' => $event,
            'id' => $id,
            'data' => $data,
            'timestamp' => date('c')
        ];
    }

    protected function sendEmail(string $to, string $subject, string $body, array $data, int $id): void
    {
        $subject = $this->replacePlaceholders($subject, $data, $id);
        $body = $this->replacePlaceholders($body, $data, $id);
        $headers = $this->buildEmailHeaders();

        @mail($to, $subject, $body, $headers);
    }

    private function buildEmailHeaders(): string
    {
        $headers = [
            'From: noreply@dynamiccrud.local',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: DynamicCRUD'
        ];
        
        return implode("\r\n", $headers);
    }

    protected function callWebhook(string $url, string $method, array $payload, array $headers): void
    {
        if (!$this->isCurlAvailable()) {
            return;
        }

        $ch = $this->initializeCurl($url);
        if (!$ch) {
            return;
        }

        $this->configureCurl($ch, $method, $payload, $headers);
        $this->executeCurl($ch);
    }

    private function isCurlAvailable(): bool
    {
        return function_exists('curl_init');
    }

    private function initializeCurl(string $url)
    {
        return @curl_init($url);
    }

    private function configureCurl($ch, string $method, array $payload, array $headers): void
    {
        $this->setBasicCurlOptions($ch);
        $this->setMethodAndPayload($ch, $method, $payload);
        $this->setCurlHeaders($ch, $headers);
    }

    private function setBasicCurlOptions($ch): void
    {
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    private function setMethodAndPayload($ch, string $method, array $payload): void
    {
        if ($method === 'POST' || $method === 'PUT') {
            @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
    }

    private function setCurlHeaders($ch, array $headers): void
    {
        $curlHeaders = $this->formatCurlHeaders($headers);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
    }

    private function formatCurlHeaders(array $headers): array
    {
        $curlHeaders = ['Content-Type: application/json'];
        
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }
        
        return $curlHeaders;
    }

    private function executeCurl($ch): void
    {
        @curl_exec($ch);
        @curl_close($ch);
    }

    private function replacePlaceholders(string $text, array $data, int $id): string
    {
        $replacements = ['{{id}}' => $id];

        foreach ($data as $key => $value) {
            $replacements["{{data.{$key}}}"] = $value ?? '';
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
