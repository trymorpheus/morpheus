# Notifications & Webhooks Examples

Examples demonstrating email notifications and webhook triggers on CRUD events.

## Setup

1. **Create database tables:**
```bash
mysql -u root -p test < setup.sql
```

2. **Configure webhook URL (optional):**
```bash
# Go to https://webhook.site and copy your unique URL
php configure-webhook.php https://webhook.site/YOUR-UNIQUE-ID
```

## Examples

### 1. Email Notifications (`email-notifications.php`)

Demonstrates email notifications on order creation and updates.

**Features:**
- Email sent to `admin@example.com` when order is created
- Email sent when `status` field is updated
- Template with placeholders (`{{id}}`, `{{data.customer_name}}`, etc.)
- Multiple recipients support

**Try it:**
```bash
php -S localhost:8000
# Open http://localhost:8000/email-notifications.php
```

**What happens:**
1. Create a new order → Email notification triggered
2. Edit order status → Email notification triggered
3. Check PHP error log for mail() calls

**Note:** PHP `mail()` requires server configuration. In development, check error logs to see the email would have been sent.

### 2. Webhooks (`webhooks.php`)

Demonstrates webhook triggers on contact form submission.

**Features:**
- POST request to webhook URL when contact is created
- Custom headers (Authorization)
- JSON payload with event data
- Non-blocking execution

**Try it:**
```bash
# Configure webhook URL first
php configure-webhook.php https://webhook.site/YOUR-UNIQUE-ID

# Start server
php -S localhost:8000
# Open http://localhost:8000/webhooks.php
```

**What happens:**
1. Create a new contact → Webhook triggered
2. Check webhook.site for the payload
3. See JSON data: `{event, id, data, timestamp}`

## Configuration

### Email Notifications

Configure in table `COMMENT`:

```sql
ALTER TABLE notif_orders COMMENT = '{
    "notifications": {
        "on_create": {
            "email": ["admin@example.com", "sales@example.com"],
            "subject": "New Order #{{id}}",
            "template": "Customer: {{data.customer_name}}<br>Amount: ${{data.amount}}"
        },
        "on_update": {
            "email": ["admin@example.com"],
            "subject": "Order Updated",
            "fields": ["status"]
        }
    }
}';
```

### Webhooks

Configure in table `COMMENT`:

```sql
ALTER TABLE notif_contacts COMMENT = '{
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://webhook.site/YOUR-UNIQUE-ID",
            "method": "POST",
            "headers": {
                "Authorization": "Bearer demo-token",
                "X-Custom-Header": "value"
            }
        }
    ]
}';
```

## Testing Tools

### Webhook.site
- **URL:** https://webhook.site
- **Features:** Inspect HTTP requests, view payloads, test webhooks
- **Free:** Yes, no registration required

### RequestBin
- **URL:** https://requestbin.com
- **Features:** Similar to webhook.site

### ngrok (for local testing)
- **URL:** https://ngrok.com
- **Features:** Expose local server to internet
- **Usage:** `ngrok http 8000`

## Troubleshooting

### Emails not sending

1. Check PHP mail configuration:
```bash
php -i | grep sendmail_path
```

2. Check error logs:
```bash
tail -f /var/log/php_errors.log
```

3. Test mail() function:
```php
mail('test@example.com', 'Test', 'Body');
```

### Webhooks not triggering

1. Verify URL is accessible:
```bash
curl -X POST https://webhook.site/YOUR-ID
```

2. Check table metadata:
```sql
SELECT TABLE_COMMENT FROM information_schema.TABLES 
WHERE TABLE_NAME = 'notif_contacts';
```

3. Enable error reporting:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Payload Examples

### Email Notification

**Subject:** `New Order #42`

**Body:**
```html
<h2>New Order #42</h2>
<p>Customer: John Doe</p>
<p>Product: Laptop</p>
<p>Amount: $1200.00</p>
```

### Webhook Payload

```json
{
  "event": "on_create",
  "id": 42,
  "data": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "555-0001",
    "message": "I need help",
    "status": "new"
  },
  "timestamp": "2025-01-15T10:30:00+00:00"
}
```

## Next Steps

1. Configure production email service (SendGrid, Mailgun, etc.)
2. Add webhook signature verification
3. Implement retry logic for failed webhooks
4. Queue notifications for async processing
5. Add notification templates in database

## Documentation

See [docs/NOTIFICATIONS.md](../../docs/NOTIFICATIONS.md) for complete guide.
