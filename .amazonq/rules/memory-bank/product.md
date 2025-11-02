# DynamicCRUD - Product Overview

## Purpose
DynamicCRUD is a PHP library that automatically generates complete CRUD (Create, Read, Update, Delete) forms with validation, security, and advanced features by analyzing MySQL database structure. It eliminates repetitive CRUD code by treating the database schema as the single source of truth.

## Core Value Proposition
- **Zero-configuration form generation**: Automatically creates forms from database tables without manual HTML or validation code
- **Database-first approach**: Schema defines everything - column types, constraints, relationships, and validation rules
- **Production-ready security**: Built-in CSRF protection, SQL injection prevention, XSS protection, and secure file uploads
- **Extensible architecture**: 10 lifecycle hooks allow custom logic without modifying core code

## Key Features

### Automatic Form Generation
- Analyzes MySQL schema and generates complete HTML forms
- Maps SQL types to appropriate input types (VARCHAR→text, INT→number, etc.)
- Respects NOT NULL constraints as required fields
- Supports ENUM fields with auto-generated select dropdowns
- Handles nullable fields with proper NULL vs empty string distinction

### Validation System
- **Server-side validation**: Type checking, length limits, required fields, format validation
- **Client-side validation**: Real-time JavaScript validation with instant feedback
- **Custom validation**: JSON metadata in column comments for min/max, patterns, tooltips
- **Cross-field validation**: Via afterValidate hook for complex business rules

### Relationship Management
- **Foreign keys (1:N)**: Automatic detection and dropdown generation with related table data
- **Many-to-many (M:N)**: Multi-select interface with automatic pivot table synchronization
- **Custom display columns**: Configure which field to show in relationship dropdowns

### Security Features
- CSRF protection with automatic token generation and validation
- SQL injection prevention using PDO prepared statements exclusively
- XSS protection through automatic input sanitization
- File upload security with real MIME type validation (not just extensions)
- Automatic transactions with rollback on errors

### File Upload Support
- MIME type validation using PHP's finfo extension
- File size limits configurable per field
- Unique filename generation to prevent conflicts
- Image preview functionality
- Configurable accepted file types

### Hooks/Events System
10 lifecycle hooks for custom logic:
- **beforeValidate/afterValidate**: Modify or validate data
- **beforeSave/afterSave**: Common logic for create and update
- **beforeCreate/afterCreate**: Creation-specific logic (welcome emails, defaults)
- **beforeUpdate/afterUpdate**: Update-specific logic (change tracking, cache clearing)
- **beforeDelete/afterDelete**: Deletion logic (dependency checks, file cleanup)

### Audit Logging
- Optional change tracking system
- Records: action type, user ID, IP address, timestamp
- Stores old and new values as JSON
- Useful for compliance and debugging

### Performance Optimization
- Schema caching system to avoid repeated database queries
- Lazy loading of relationship data
- Prepared statement reuse
- Configurable cache strategies (file-based included)

## Target Users

### Primary Users
- **PHP developers** building admin panels and backoffice applications
- **Full-stack developers** needing rapid CRUD prototyping
- **Small to medium teams** wanting to reduce boilerplate code
- **Agencies** building multiple client applications with standard CRUD needs

### Ideal Use Cases
- Admin panels for content management
- Application backoffice interfaces
- Rapid prototypes and MVPs
- Internal tools and dashboards
- Applications requiring audit trails
- Forms with complex validation rules

### Not Recommended For
- Highly customized UI requirements
- Multi-step wizards or complex workflows
- Applications without database backing
- Forms with extensive conditional logic across many fields
- Public-facing forms requiring specific branding

## Technical Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- PDO extension
- Fileinfo extension (for file uploads)
- JSON extension

## Customization Options

### JSON Metadata in Column Comments
Configure field behavior without code changes:
- `type`: Input type (email, url, number, file, etc.)
- `label`: Custom field label
- `tooltip`: Help text displayed to users
- `min/max`: Numeric range validation
- `minlength`: Minimum text length
- `hidden`: Hide field from forms
- `display_column`: Which column to show for foreign keys
- `accept`: File type restrictions
- `allowed_mimes`: Specific MIME types
- `max_size`: Maximum file size in bytes

### Example Metadata
```json
{
  "type": "email",
  "label": "Email Address",
  "tooltip": "We will never share your email",
  "minlength": 5
}
```

## Project Statistics
- **10 PHP classes** (~3,500 lines of code)
- **8 working examples** demonstrating all features
- **7 technical documents** (English and Spanish)
- **98.75% feature completion** of planned v1.0 scope
- **100% bug resolution rate** (6/6 resolved)
- **Development time**: Less than 1 day with AI assistance

## Licensing
MIT License - Free for commercial and personal use
