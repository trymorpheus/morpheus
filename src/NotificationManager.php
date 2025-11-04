<?php

namespace DynamicCRUD;

class NotificationManager
{
    public function sendEmailNotifications(array $config, array $data, int $id): void
    {
        if (empty($config['email'])) {
            return;
        }

        $recipients = $config['email'];
        $subject = $config['subject'] ?? 'Notification';
        $template = $config['template'] ?? null;

        foreach ($recipients as $recipient) {
            $body = $template ? $this->replacePlaceholders($template, $data, $id) : json_encode($data);
            $this->sendEmail($recipient, $subject, $body, $data, $id);
        }
    }

    public function triggerWebhooks(array $webhooks, string $event, array $data, int $id): void
    {
        foreach ($webhooks as $webhook) {
            if (isset($webhook['event']) && $webhook['event'] !== $event) {
                continue;
            }

            $url = $webhook['url'];
            $method = $webhook['method'] ?? 'POST';
            $headers = $webhook['headers'] ?? [];

            $payload = [
                'event' => $event,
                'id' => $id,
                'data' => $data,
                'timestamp' => date('c')
            ];

            $this->callWebhook($url, $method, $payload, $headers);
        }
    }

    protected function sendEmail(string $to, string $subject, string $body, array $data, int $id): void
    {
        $subject = $this->replacePlaceholders($subject, $data, $id);
        $body = $this->replacePlaceholders($body, $data, $id);

        $headers = [
            'From: noreply@dynamiccrud.local',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: DynamicCRUD'
        ];

        @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    protected function callWebhook(string $url, string $method, array $payload, array $headers): void
    {
        if (!function_exists('curl_init')) {
            return;
        }

        $ch = @curl_init($url);
        if ($ch === false) {
            return;
        }

        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($method === 'POST' || $method === 'PUT') {
            @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $curlHeaders = ['Content-Type: application/json'];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

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
