-- Volcado de esquema para la base de datos: `test`
-- Generado el: 2025-11-03 17:08:38
-- --------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Estructura para la tabla `advanced_inputs`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `advanced_inputs`;
CREATE TABLE `advanced_inputs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_color` varchar(7) DEFAULT NULL COMMENT '{"type": "color", "label": "Brand Color", "placeholder": "#000000", "tooltip": "Pick your brand color"}',
  `phone` varchar(20) DEFAULT NULL COMMENT '{"type": "tel", "label": "Phone Number", "placeholder": "555-123-4567", "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}"}',
  `password` varchar(255) DEFAULT NULL COMMENT '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters"}',
  `search_query` varchar(255) DEFAULT NULL COMMENT '{"type": "search", "label": "Search", "placeholder": "Search..."}',
  `appointment_time` time DEFAULT NULL COMMENT '{"type": "time", "label": "Appointment Time"}',
  `birth_week` varchar(10) DEFAULT NULL COMMENT '{"type": "week", "label": "Birth Week"}',
  `birth_month` varchar(7) DEFAULT NULL COMMENT '{"type": "month", "label": "Birth Month"}',
  `satisfaction` int DEFAULT NULL COMMENT '{"type": "range", "label": "Satisfaction Level", "min": 0, "max": 100, "step": 10, "tooltip": "Rate from 0 to 100"}',
  `email` varchar(255) DEFAULT NULL COMMENT '{"type": "email", "label": "Email", "placeholder": "user@example.com", "autocomplete": "email"}',
  `website` varchar(255) DEFAULT NULL COMMENT '{"type": "url", "label": "Website", "placeholder": "https://example.com"}',
  `notes` text COMMENT '{"label": "Notes", "placeholder": "Enter your notes here..."}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Created At"}',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `audit_log`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` int NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `user_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `auth_users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `auth_users`;
CREATE TABLE `auth_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='{\n    "display_name": "Users",\n    "icon": "?",\n    "authentication": {\n        "enabled": true,\n        "identifier_field": "email",\n        "password_field": "password",\n        "registration": {\n            "enabled": true,\n            "auto_login": true,\n            "default_role": "user",\n            "required_fields": ["name", "email", "password"]\n        },\n        "login": {\n            "enabled": true,\n            "remember_me": true,\n            "max_attempts": 5,\n            "lockout_duration": 900,\n            "session_lifetime": 7200\n        }\n    },\n    "permissions": {\n        "create": ["guest"],\n        "read": ["owner", "admin"],\n        "update": ["owner", "admin"],\n        "delete": ["admin"]\n    },\n    "row_level_security": {\n        "enabled": true,\n        "owner_field": "id",\n        "owner_can_edit": true,\n        "owner_can_delete": false\n    }\n}';

-- --------------------------------------------------------
-- Estructura para la tabla `blog_comments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `blog_comments`;
CREATE TABLE `blog_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `blog_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='{\r\n    "display_name": "Comments",\r\n    "icon": "?",\r\n    "permissions": {\r\n        "create": ["admin", "editor", "author", "user"],\r\n        "read": ["*"],\r\n        "update": ["admin", "editor"],\r\n        "delete": ["admin", "editor"]\r\n    },\r\n    "row_level_security": {\r\n        "enabled": true,\r\n        "owner_field": "user_id",\r\n        "owner_can_edit": true,\r\n        "owner_can_delete": true\r\n    }\r\n}';

-- --------------------------------------------------------
-- Estructura para la tabla `blog_posts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='{\r\n    "display_name": "Blog Posts",\r\n    "icon": "?",\r\n    "permissions": {\r\n        "create": ["admin", "editor", "author"],\r\n        "read": ["*"],\r\n        "update": ["admin", "editor"],\r\n        "delete": ["admin"]\r\n    },\r\n    "row_level_security": {\r\n        "enabled": true,\r\n        "owner_field": "user_id",\r\n        "owner_can_edit": true,\r\n        "owner_can_delete": false\r\n    },\r\n    "behaviors": {\r\n        "timestamps": {\r\n            "created_at": "created_at",\r\n            "updated_at": "updated_at"\r\n        },\r\n        "sluggable": {\r\n            "source": "title",\r\n            "target": "slug",\r\n            "unique": true\r\n        }\r\n    },\r\n    "list_view": {\r\n        "columns": ["id", "title", "status", "created_at"],\r\n        "searchable": ["title", "content"],\r\n        "per_page": 20\r\n    }\r\n}';

-- --------------------------------------------------------
-- Estructura para la tabla `blog_users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `blog_users`;
CREATE TABLE `blog_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','editor','author','user','guest') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='{\r\n    "display_name": "Users",\r\n    "icon": "?",\r\n    "permissions": {\r\n        "create": ["admin"],\r\n        "read": ["admin", "editor"],\r\n        "update": ["admin"],\r\n        "delete": ["admin"]\r\n    }\r\n}';

-- --------------------------------------------------------
-- Estructura para la tabla `categories`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '{"label": "Category Name", "placeholder": "e.g., Technology, Business"}',
  `description` text COMMENT '{"label": "Description", "placeholder": "Describe this category..."}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `contacts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}',
  `email` varchar(255) NOT NULL COMMENT '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We never share your email", "autocomplete": "email"}',
  `phone` varchar(20) DEFAULT NULL COMMENT '{"type": "tel", "label": "Phone Number", "placeholder": "+1 (555) 123-4567", "pattern": "[0-9+\\-\\s()]+", "autocomplete": "tel"}',
  `website` varchar(255) DEFAULT NULL COMMENT '{"type": "url", "label": "Website", "placeholder": "https://example.com", "tooltip": "Enter a valid URL"}',
  `message` text COMMENT '{"label": "Your Message", "placeholder": "Tell us what you need...", "minlength": 10}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='{"display_name": "Contact Forms", "icon": "?", "color": "#17a2b8", "list_view": {"columns": ["id", "name", "email", "created_at"], "default_sort": "created_at DESC", "per_page": 25, "searchable": ["name", "email", "message"], "actions": ["edit", "delete"]}, "form": {"layout": "tabs", "tabs": [{"name": "basic", "label": "Basic Info", "fields": ["name", "email"]}, {"name": "contact", "label": "Contact Details", "fields": ["phone", "website"]}, {"name": "message", "label": "Message", "fields": ["message"]}]}}';

-- --------------------------------------------------------
-- Estructura para la tabla `post_tags`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `post_tags`;
CREATE TABLE `post_tags` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `posts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '{"label": "Post Title", "placeholder": "Enter an engaging title", "minlength": 5}',
  `slug` varchar(255) DEFAULT NULL COMMENT '{"label": "URL Slug", "placeholder": "auto-generated-from-title", "tooltip": "Leave empty to auto-generate", "pattern": "[a-z0-9-]+", "readonly": true}',
  `content` text COMMENT '{"label": "Content", "placeholder": "Write your post content here..."}',
  `status` enum('draft','published') DEFAULT 'draft' COMMENT '{"type": "select", "label": "Status"}',
  `published_at` datetime DEFAULT NULL COMMENT '{"type": "datetime-local", "label": "Publish Date", "tooltip": "Auto-set when status is published"}',
  `category_id` int DEFAULT NULL COMMENT '{"label": "Category"}',
  `user_id` int DEFAULT NULL COMMENT '{"label": "Author", "display_column": "name"}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Created"}',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Updated"}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='{"display_name": "Blog Posts", "icon": "?", "color": "#28a745", "list_view": {"columns": ["id", "title", "status", "created_at"], "default_sort": "created_at DESC", "per_page": 3, "searchable": ["title", "content"], "actions": ["edit", "delete"]}, "filters": [{"field": "status", "type": "select", "label": "Estado", "options": ["draft", "published"]}, {"field": "created_at", "type": "daterange", "label": "Fecha de Creación"}], "behaviors": {"timestamps": {"created_at": "created_at", "updated_at": "updated_at"}, "sluggable": {"source": "title", "target": "slug", "unique": true, "separator": "-", "lowercase": true}}}';

-- --------------------------------------------------------
-- Estructura para la tabla `products`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '{"label": "Product Name", "placeholder": "Enter product name"}',
  `description` text COMMENT '{"label": "Description", "placeholder": "Describe your product..."}',
  `price` decimal(10,2) NOT NULL COMMENT '{"type": "number", "step": "0.01", "min": 0, "label": "Price (USD)", "placeholder": "0.00"}',
  `image` varchar(255) DEFAULT NULL COMMENT '{"type": "file", "accept": "image/*", "max_size": 2097152, "label": "Product Image", "tooltip": "JPG, PNG or WebP. Max 2MB"}',
  `category_id` int DEFAULT NULL COMMENT '{"label": "Category"}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='{"display_name": "Products", "icon": "", "color": "#fd7e14", "list_view": {"columns": ["id", "name", "price", "category_id"], "default_sort": "name ASC", "per_page": 20,             "searchable": ["name", "description"],, "actions": ["edit", "delete"]}}';

-- --------------------------------------------------------
-- Estructura para la tabla `soft_posts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `soft_posts`;
CREATE TABLE `soft_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `author` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='{\n    "display_name": "Posts (Soft Deletes)",\n    "icon": "?",\n    "description": "Posts with soft delete support",\n    "behaviors": {\n        "soft_deletes": {\n            "enabled": true,\n            "column": "deleted_at"\n        },\n        "timestamps": {\n            "created_at": "created_at",\n            "updated_at": "updated_at"\n        }\n    },\n    "list_view": {\n        "columns": ["id", "title", "author", "created_at", "deleted_at"],\n        "searchable": ["title", "content", "author"],\n        "per_page": 10\n    }\n}';

-- --------------------------------------------------------
-- Estructura para la tabla `tags`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '{"label": "Tag Name", "placeholder": "e.g., PHP, MySQL"}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `test_no_soft`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `test_no_soft`;
CREATE TABLE `test_no_soft` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura para la tabla `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}',
  `email` varchar(255) NOT NULL COMMENT '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We will never share your email", "autocomplete": "email"}',
  `password` varchar(255) NOT NULL COMMENT '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters", "tooltip": "Use a strong password"}',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2392 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='{"display_name": "User Management", "icon": "?", "description": "Complete user administration", "color": "#667eea", "list_view": {"columns": ["id", "name", "email", "created_at"], "default_sort": "created_at DESC", "per_page": 25, "searchable": ["name", "email"], "actions": ["edit", "delete"]}}';

SET FOREIGN_KEY_CHECKS = 1;