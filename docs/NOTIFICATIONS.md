# Notifications & Webhooks Guide

**Version:** 2.3.0  
**Feature:** Email notifications and webhooks for CRUD events

---

## Overview

DynamicCRUD v2.3 introduces a powerful notification system that allows you to send email notifications and trigger webhooks automatically when records are created, updated, or deleted. Configure everything via JSON in your table's `COMMENT` field.

---

## Email Notifications

### Basic Configuration

Add email notification configuration to your table comment:

```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending'
) COMMENT = '{
    "notifications": {
        "on_create": {
            "email": ["admin@example.com"],
            "subject": "New Order Received",
            "template": "New order from {{data.customer_name}}"
        }
    }
}';
```

### Available Events

- `on_create` - Triggered after a record is created
- `on_update` - Triggered after a record is updated
- `on_delete` - Triggered after a record is deleted

### Email Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `email` | array | List of recipient email addresses |
| `subject` | string | Email subject (supports placeholders) |
| `template` | string | Email body (supports placeholders) |
| `fields` | array | (Optional) Only send on update if these fields changed |

### Template Placeholders

Use placeholders in `subject` and `template`:

- `{{id}}` - Record ID
- `{{data.field_name}}` - Any field from the record
- `{{event}}` - Event name (on_create, on_update, on_delete)

**Example:**

```sql
COMMENT = '{
    "notifications": {
        "on_create": {
            "email": ["admin@example.com", "sales@example.com"],
            "subject": "Order #{{id}} - {{data.customer_name}}",
            "template": "<h2>New Order</h2><p>Customer: {{data.customer_name}}</p><p>Amount: ${{data.amount}}</p>"
        },
        "on_update": {
            "email": ["admin@example.com"],
            "subject": "Order #{{id}} Updated",
            "fields": ["status"]
        }
    }
}'
```

### Field-Specific Notifications

Only send notifications when specific fields are updated:

```sql
COMMENT = '{
    "notifications": {
        "on_update": {
            "email": ["admin@example.com"],
            "subject": "Status Changed",
            "fields": ["status", "priority"]
        }
    }
}'
```

---

## Webhooks

### Basic Configuration

Configure webhooks to call external APIs:

```sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT
) COMMENT = '{
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://api.example.com/webhook",
            "method": "POST",
            "headers": {
                "Authorization": "Bearer your-token",
                "Content-Type": "application/json"
            }
        }
    ]
}';
```

### Webhook Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `event` | string | Event to trigger on (on_create, on_update, on_delete) |
| `url` | string | Webhook URL to call |
| `method` | string | HTTP method (GET, POST, PUT, DELETE) |
| `headers` | object | (Optional) HTTP headers to send |

### Webhook Payload

Webhooks receive a JSON payload:

```json
{
    "event": "on_create",
    "table": "contacts",
    "id": 42,
    "data": {
        "name": "John Doe",
        "email": "john@example.com",
        "message": "Hello"
    },
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Multiple Webhooks

Configure multiple webhooks for different events:

```sql
COMMENT = '{
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://api.example.com/new-contact",
            "method": "POST"
        },
        {
            "event": "on_update",
            "url": "https://api.example.com/update-contact",
            "method": "PUT"
        },
        {
            "event": "on_delete",
            "url": "https://api.example.com/delete-contact",
            "method": "DELETE"
        }
    ]
}'
```

---

## Combined Configuration

Use both email notifications and webhooks:

```sql
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    source VARCHAR(50),
    status VARCHAR(50) DEFAULT 'new'
) COMMENT = '{
    "display_name": "Sales Leads",
    "notifications": {
        "on_create": {
            "email": ["sales@example.com"],
            "subject": "New Lead: {{data.name}}",
            "template": "Source: {{data.source}}<br>Email: {{data.email}}"
        },
        "on_update": {
            "email": ["sales@example.com"],
            "subject": "Lead Status Changed",
            "fields": ["status"]
        }
    },
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://crm.example.com/api/leads",
            "method": "POST",
            "headers": {
                "Authorization": "Bearer crm-token"
            }
        }
    ]
}'
```

---

## PHP Usage

Notifications are triggered automatically when using DynamicCRUD:

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$crud = new DynamicCRUD($pdo, 'orders');

// Create record - triggers on_create notifications
$result = $crud->handleSubmission();
// Email sent to admin@example.com
// Webhook called automatically

// Update record - triggers on_update notifications
$_POST['id'] = 42;
$_POST['status'] = 'completed';
$result = $crud->handleSubmission();
// Email sent if 'status' field changed

// Delete record - triggers on_delete notifications
$crud->delete(42);
// Notifications sent
```

---

## Testing Webhooks

### Using webhook.site

1. Go to [webhook.site](https://webhook.site)
2. Copy your unique URL
3. Update your table comment:

```sql
ALTER TABLE contacts 
COMMENT = '{
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://webhook.site/your-unique-id",
            "method": "POST"
        }
    ]
}';
```

4. Create a record and check webhook.site for the payload

### Local Testing

For local development, use tools like:
- [ngrok](https://ngrok.com) - Expose local server to internet
- [RequestBin](https://requestbin.com) - Inspect HTTP requests
- [Beeceptor](https://beeceptor.com) - Mock API endpoints

---

## Email Configuration

### PHP mail() Function

By default, DynamicCRUD uses PHP's `mail()` function. Ensure your server is configured:

```bash
# Check PHP mail configuration
php -i | grep sendmail_path
```

### Custom Email Handler

For production, integrate with email services (SendGrid, Mailgun, etc.):

```php
use DynamicCRUD\NotificationManager;

class CustomNotificationManager extends NotificationManager
{
    protected function sendEmail(string $to, string $subject, string $body, array $data, int $id): void
    {
        // Use SendGrid, Mailgun, etc.
        $client = new \SendGrid\Mail\Mail();
        $client->setFrom("noreply@example.com");
        $client->addTo($to);
        $client->setSubject($subject);
        $client->addContent("text/html", $body);
        
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $sendgrid->send($client);
    }
}
```

---

## Security Considerations

### Email Security

- **Validate recipients** - Only send to trusted addresses
- **Sanitize data** - Escape user input in templates
- **Rate limiting** - Prevent email spam
- **Authentication** - Verify sender identity (SPF, DKIM)

### Webhook Security

- **HTTPS only** - Always use secure connections
- **Authentication** - Include API keys/tokens in headers
- **Signature verification** - Sign payloads for verification
- **IP whitelisting** - Restrict webhook sources
- **Timeout handling** - Set reasonable timeouts (5-10 seconds)

**Example with signature:**

```sql
COMMENT = '{
    "webhooks": [
        {
            "url": "https://api.example.com/webhook",
            "method": "POST",
            "headers": {
                "Authorization": "Bearer secret-token",
                "X-Signature": "sha256-hash-of-payload"
            }
        }
    ]
}'
```

---

## Error Handling

Notifications are non-blocking - errors won't prevent CRUD operations:

```php
// Even if email fails, record is still saved
$result = $crud->handleSubmission();
// $result['success'] = true (record saved)
// Email error logged but not returned
```

Check PHP error logs for notification failures:

```bash
tail -f /var/log/php_errors.log
```

---

## Performance Considerations

### Async Processing

For high-traffic applications, queue notifications:

```php
use DynamicCRUD\NotificationManager;

class QueuedNotificationManager extends NotificationManager
{
    protected function sendEmail(string $to, string $subject, string $body, array $data, int $id): void
    {
        // Queue for background processing
        $queue->push('send_email', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body
        ]);
    }
    
    protected function callWebhook(string $url, string $method, array $payload, array $headers): void
    {
        // Queue webhook call
        $queue->push('call_webhook', [
            'url' => $url,
            'method' => $method,
            'payload' => $payload,
            'headers' => $headers
        ]);
    }
}
```

### Caching

Cache notification configurations:

```php
use DynamicCRUD\Cache\FileCacheStrategy;

$cache = new FileCacheStrategy(__DIR__ . '/cache');
$crud = new DynamicCRUD($pdo, 'orders', cache: $cache);
// Notification config cached with table metadata
```

---

## Examples

See working examples in `examples/11-notifications/`:

1. **email-notifications.php** - Email notifications on order creation/update
2. **webhooks.php** - Webhook triggers on contact form submission

Run the examples:

```bash
# Setup database
mysql -u root -p test < examples/11-notifications/setup.sql

# Start PHP server
php -S localhost:8000 -t examples/11-notifications/

# Open in browser
open http://localhost:8000/email-notifications.php
open http://localhost:8000/webhooks.php
```

---

## Troubleshooting

### Emails Not Sending

1. Check PHP mail configuration:
   ```bash
   php -i | grep mail
   ```

2. Test mail() function:
   ```php
   mail('test@example.com', 'Test', 'Body');
   ```

3. Check error logs:
   ```bash
   tail -f /var/log/php_errors.log
   ```

### Webhooks Not Triggering

1. Verify URL is accessible:
   ```bash
   curl -X POST https://webhook.site/your-id
   ```

2. Check table metadata:
   ```sql
   SELECT TABLE_COMMENT FROM information_schema.TABLES 
   WHERE TABLE_NAME = 'your_table';
   ```

3. Enable error reporting:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

---

## API Reference

### NotificationManager

```php
class NotificationManager
{
    // Send email notifications
    public function sendEmailNotifications(array $config, array $data, int $id): void
    
    // Trigger webhooks
    public function triggerWebhooks(array $webhooks, string $event, array $data, int $id): void
    
    // Send single email (override for custom implementation)
    protected function sendEmail(string $to, string $subject, string $body, array $data, int $id): void
    
    // Call single webhook (override for custom implementation)
    protected function callWebhook(string $url, string $method, array $payload, array $headers): void
}
```

### TableMetadata

```php
// Check if table has notifications
public function hasNotifications(): bool

// Get notification configuration
public function getNotificationConfig(): array
```

---

## Changelog

### v2.3.0 (2024-01-15)
- ✅ Email notifications with template placeholders
- ✅ Webhook triggers with custom headers
- ✅ Field-specific update notifications
- ✅ Multiple recipients and webhooks
- ✅ Non-blocking error handling
- ✅ 2 working examples
- ✅ 11 automated tests

---

## Next Steps

- Explore [RBAC Guide](RBAC.md) for authentication
- Learn about [Validation Rules](VALIDATION_RULES.md)
- Check [Table Metadata Guide](TABLE_METADATA.md)
- See [Hooks System](HOOKS.md) for custom logic

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
