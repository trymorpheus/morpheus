# Multiple File Upload Example

Upload multiple files (photos) for a single record with drag & drop UI.

## Features

- \u2705 Drag & drop interface
- \u2705 Image preview
- \u2705 Multiple file selection
- \u2705 Max files limit (configurable)
- \u2705 File size validation
- \u2705 MIME type validation
- \u2705 Manage existing files (remove)
- \u2705 JSON storage

## Setup

```bash
# Create table
mysql -u root -p test < setup.sql

# Start server
php -S localhost:8000 -t examples/15-multiple-files

# Open browser
http://localhost:8000
```

## Usage

### Metadata Configuration

```sql
ALTER TABLE properties 
MODIFY COLUMN photos JSON 
COMMENT '{
    "type": "multiple_files",
    "accept": "image/*",
    "max_files": 10,
    "max_size": 5242880,
    "label": "Fotos"
}';
```

### PHP Code

```php
$crud = new Morpheus($pdo, 'properties');
echo $crud->renderForm(); // Drag & drop UI automatic!
```

## Metadata Options

| Option | Type | Description | Example |
|--------|------|-------------|---------|
| `type` | string | Must be `multiple_files` | `"multiple_files"` |
| `accept` | string | Allowed MIME types | `"image/*"` |
| `max_files` | int | Maximum files allowed | `10` |
| `max_size` | int | Max size per file (bytes) | `5242880` (5MB) |
| `label` | string | Field label | `"Fotos"` |

## Storage

Files are stored as JSON array in the database:

```json
[
    "../uploads/abc123_1234567890_0.jpg",
    "../uploads/def456_1234567891_1.jpg",
    "../uploads/ghi789_1234567892_2.jpg"
]
```

## Use Cases

1. **Real Estate** - Property photos
2. **E-commerce** - Product images
3. **Portfolio** - Project screenshots
4. **Gallery** - Photo albums
5. **Documents** - Multiple file attachments

## How It Works

1. User drags files or clicks to select
2. JavaScript shows preview
3. Form submits with `multipart/form-data`
4. `FileUploadHandler::handleMultipleUploads()` processes files
5. Files saved to `uploads/` directory
6. Paths stored as JSON in database
7. Existing files can be removed individually

## Example: Real Estate

This example demonstrates a complete real estate application with:
- Property listing with photos
- Add/edit properties
- Multiple photo upload per property
- Photo preview in listing
- Status management (available, sold, rented)

Perfect for building property management systems!
