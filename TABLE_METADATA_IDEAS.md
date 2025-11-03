# Table-Level Metadata - Feature Ideas

**Author:** Amazon Q  
**Date:** November 2025  
**Status:** Brainstorming / Proposal

## 📋 Overview

Currently, DynamicCRUD uses JSON metadata in **column comments** to customize field behavior. This document explores using **table comments** to unlock powerful table-level features.

## 🎯 Core Concept

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255)
) COMMENT = '{
    "display_name": "User Management",
    "icon": "users",
    "permissions": {"create": ["admin"], "read": ["*"]},
    "list_view": {"columns": ["id", "name", "email"], "per_page": 25}
}';
```

---

## 💡 Feature Categories

### 1. UI/UX Customization

**Purpose:** Control how tables are displayed and interacted with.

```json
{
  "display_name": "Gestión de Usuarios",
  "icon": "users",
  "description": "Administración completa de usuarios del sistema",
  "color": "#667eea",
  "list_view": {
    "default_sort": "created_at DESC",
    "per_page": 25,
    "columns": ["id", "name", "email", "status", "created_at"],
    "searchable": ["name", "email"],
    "actions": ["edit", "delete", "view"],
    "card_view": false
  },
  "card_template": "<div class='card'><h3>{{name}}</h3><p>{{email}}</p></div>"
}
```

**Benefits:**
- ✅ Friendly names instead of technical table names
- ✅ Visual icons for navigation
- ✅ Control which columns appear in lists
- ✅ Configured search fields
- ✅ Custom card/grid views

**Implementation Complexity:** 🟢 Low  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐⭐⭐

---

### 2. Permissions & Security

**Purpose:** Role-based access control at table level.

```json
{
  "permissions": {
    "create": ["admin", "manager"],
    "read": ["admin", "manager", "user"],
    "update": ["admin", "manager"],
    "delete": ["admin"]
  },
  "soft_delete": true,
  "soft_delete_column": "deleted_at",
  "owner_field": "user_id",
  "row_level_security": {
    "enabled": true,
    "owner_can_edit": true,
    "owner_can_delete": false
  }
}
```

**Benefits:**
- ✅ RBAC (Role-Based Access Control)
- ✅ Soft deletes automatic
- ✅ Row-level security (users can only edit their own records)
- ✅ Production-ready security

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 Critical  
**Priority:** ⭐⭐⭐⭐⭐

---

### 3. Validation & Business Rules

**Purpose:** Complex validation beyond single-field rules.

```json
{
  "validation_rules": {
    "unique_together": [["email", "company_id"]],
    "required_if": {
      "phone": {"status": "active"},
      "address": {"type": "physical"}
    },
    "conditional": {
      "discount": {"condition": "price > 100", "max": 50}
    }
  },
  "business_rules": {
    "max_records_per_user": 100,
    "require_approval": true,
    "approval_field": "approved_at",
    "approval_roles": ["admin", "supervisor"]
  }
}
```

**Benefits:**
- ✅ Cross-field validation
- ✅ Conditional requirements
- ✅ Record limits per user
- ✅ Approval workflows

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐⭐

---

### 4. Advanced Relationships

**Purpose:** Support complex relationship patterns.

```json
{
  "relationships": {
    "polymorphic": {
      "commentable": {
        "type_column": "commentable_type",
        "id_column": "commentable_id",
        "types": ["Post", "Product", "User"]
      }
    },
    "has_many_through": {
      "subscribers": {
        "through": "subscriptions",
        "foreign_key": "user_id",
        "related_key": "subscriber_id"
      }
    },
    "self_referencing": {
      "parent_id": {
        "display_as": "tree",
        "max_depth": 5
      }
    }
  }
}
```

**Benefits:**
- ✅ Polymorphic relations (comments on multiple tables)
- ✅ Has-many-through automatic
- ✅ Tree structures (categories, org charts)
- ✅ Eager loading configured

**Implementation Complexity:** 🔴 High  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐

---

### 5. Dynamic Forms

**Purpose:** Control form layout and organization.

```json
{
  "form": {
    "layout": "tabs",
    "tabs": [
      {
        "name": "basic",
        "label": "Información Básica",
        "icon": "info",
        "fields": ["name", "email", "phone"]
      },
      {
        "name": "advanced",
        "label": "Configuración Avanzada",
        "icon": "settings",
        "fields": ["settings", "preferences", "notifications"]
      }
    ],
    "fieldsets": {
      "contact": {
        "label": "Información de Contacto",
        "fields": ["email", "phone", "address"],
        "collapsible": true,
        "collapsed": false
      },
      "security": {
        "label": "Seguridad",
        "fields": ["password", "two_factor"],
        "collapsible": true,
        "collapsed": true
      }
    },
    "columns": 2,
    "wizard": false
  }
}
```

**Benefits:**
- ✅ Tabbed forms for complex data
- ✅ Collapsible fieldsets
- ✅ Multi-column layouts
- ✅ Wizard-style forms (step-by-step)

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 Very High  
**Priority:** ⭐⭐⭐⭐⭐

---

### 6. Versioning & Audit

**Purpose:** Track changes and enable rollback.

```json
{
  "versioning": {
    "enabled": true,
    "version_table": "users_versions",
    "track_fields": ["name", "email", "role", "status"],
    "max_versions": 50,
    "compress_old": true
  },
  "audit": {
    "auto_enable": true,
    "track_ip": true,
    "track_user_agent": true,
    "track_changes_only": true,
    "exclude_fields": ["password", "token"]
  }
}
```

**Benefits:**
- ✅ Full version history
- ✅ Rollback to previous versions
- ✅ Automatic audit logging
- ✅ Compliance-ready

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐⭐

---

### 7. Automatic Behaviors

**Purpose:** Common patterns automated.

```json
{
  "behaviors": {
    "timestamps": {
      "created_at": "created_at",
      "updated_at": "updated_at",
      "deleted_at": "deleted_at"
    },
    "sluggable": {
      "source": "title",
      "target": "slug",
      "unique": true,
      "separator": "-",
      "lowercase": true
    },
    "sortable": {
      "field": "position",
      "scope": "category_id",
      "start": 1
    },
    "tree": {
      "parent_field": "parent_id",
      "left_field": "lft",
      "right_field": "rgt",
      "depth_field": "depth"
    }
  }
}
```

**Benefits:**
- ✅ Timestamps automatic
- ✅ Unique slugs generated
- ✅ Drag-and-drop ordering
- ✅ Nested sets for trees

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐⭐

---

### 8. API & Export

**Purpose:** Automatic REST API and data export.

```json
{
  "api": {
    "enabled": true,
    "endpoint": "/api/users",
    "methods": ["GET", "POST", "PUT", "DELETE"],
    "rate_limit": 100,
    "rate_limit_window": "1 hour",
    "public_fields": ["id", "name", "avatar", "bio"],
    "authentication": "bearer",
    "pagination": true
  },
  "export": {
    "formats": ["csv", "xlsx", "pdf", "json"],
    "filename_template": "users_{date}_{time}",
    "include_relations": true,
    "max_rows": 10000,
    "async": true
  }
}
```

**Benefits:**
- ✅ REST API zero-config
- ✅ Rate limiting built-in
- ✅ Multiple export formats
- ✅ Async exports for large datasets

**Implementation Complexity:** 🔴 High  
**User Impact:** 🔥 Very High  
**Priority:** ⭐⭐⭐

---

### 9. Search & Filters

**Purpose:** Advanced search and filtering.

```json
{
  "search": {
    "engine": "fulltext",
    "fields": ["name", "email", "bio", "notes"],
    "weights": {
      "name": 3,
      "email": 2,
      "bio": 1,
      "notes": 1
    },
    "min_length": 3,
    "highlight": true
  },
  "filters": [
    {
      "field": "status",
      "type": "select",
      "label": "Estado",
      "options": ["active", "inactive", "pending"]
    },
    {
      "field": "created_at",
      "type": "daterange",
      "label": "Fecha de Registro"
    },
    {
      "field": "role",
      "type": "multiselect",
      "label": "Roles"
    },
    {
      "field": "age",
      "type": "range",
      "label": "Edad",
      "min": 0,
      "max": 120
    }
  ]
}
```

**Benefits:**
- ✅ Full-text search configured
- ✅ Weighted relevance
- ✅ Advanced filters in lists
- ✅ Date ranges, multi-select, etc.

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐⭐

---

### 10. Analytics & Reporting

**Purpose:** Built-in metrics and dashboards.

```json
{
  "analytics": {
    "track_views": true,
    "track_edits": true,
    "track_deletes": true,
    "metrics": [
      {"name": "total", "label": "Total Usuarios"},
      {"name": "active", "label": "Activos", "filter": {"status": "active"}},
      {"name": "new_today", "label": "Nuevos Hoy", "filter": {"created_at": "today"}}
    ],
    "charts": [
      {
        "type": "line",
        "field": "created_at",
        "label": "Registros por Día",
        "period": "30 days"
      },
      {
        "type": "pie",
        "field": "status",
        "label": "Distribución por Estado"
      }
    ]
  },
  "reports": [
    {
      "name": "monthly_summary",
      "label": "Resumen Mensual",
      "schedule": "monthly",
      "format": "pdf",
      "recipients": ["admin@example.com"]
    }
  ]
}
```

**Benefits:**
- ✅ Automatic metrics
- ✅ Configured charts
- ✅ Dashboard per table
- ✅ Scheduled reports

**Implementation Complexity:** 🔴 High  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐

---

### 11. Notifications & Webhooks

**Purpose:** Event-driven integrations.

```json
{
  "notifications": {
    "on_create": {
      "email": ["admin@example.com"],
      "slack": "#general",
      "template": "user_created"
    },
    "on_update": {
      "notify_owner": true,
      "fields": ["status", "role"]
    },
    "on_delete": {
      "email": ["audit@example.com"],
      "require_reason": true
    }
  },
  "webhooks": [
    {
      "event": "created",
      "url": "https://api.example.com/webhook/user-created",
      "method": "POST",
      "headers": {"Authorization": "Bearer {token}"}
    },
    {
      "event": "updated",
      "url": "https://api.example.com/webhook/user-updated",
      "fields": ["status"]
    }
  ]
}
```

**Benefits:**
- ✅ Email notifications automatic
- ✅ Slack/Teams integration
- ✅ Webhooks for external systems
- ✅ Event-driven architecture

**Implementation Complexity:** 🟡 Medium  
**User Impact:** 🔥 High  
**Priority:** ⭐⭐⭐

---

## 📊 Priority Matrix

| Feature | Complexity | Impact | Priority | Quick Win |
|---------|-----------|--------|----------|-----------|
| UI/UX Customization | 🟢 Low | 🔥 High | ⭐⭐⭐⭐⭐ | ✅ Yes |
| Permissions & Security | 🟡 Medium | 🔥 Critical | ⭐⭐⭐⭐⭐ | ❌ No |
| Dynamic Forms | 🟡 Medium | 🔥 Very High | ⭐⭐⭐⭐⭐ | ✅ Yes |
| Automatic Behaviors | 🟡 Medium | 🔥 High | ⭐⭐⭐⭐ | ✅ Yes |
| Search & Filters | 🟡 Medium | 🔥 High | ⭐⭐⭐⭐ | ✅ Yes |
| Validation & Rules | 🟡 Medium | 🔥 High | ⭐⭐⭐⭐ | ❌ No |
| Versioning & Audit | 🟡 Medium | 🔥 High | ⭐⭐⭐⭐ | ❌ No |
| Notifications | 🟡 Medium | 🔥 High | ⭐⭐⭐ | ✅ Yes |
| Advanced Relationships | 🔴 High | 🔥 High | ⭐⭐⭐ | ❌ No |
| API & Export | 🔴 High | 🔥 Very High | ⭐⭐⭐ | ❌ No |
| Analytics | 🔴 High | 🔥 High | ⭐⭐⭐ | ❌ No |

---

## 🚀 Recommended Implementation Roadmap

### Phase 1: Quick Wins (v2.0)
1. **UI/UX Customization** - display_name, icon, list_view
2. **Dynamic Forms** - tabs, fieldsets
3. **Automatic Behaviors** - timestamps, sluggable, sortable
4. **Search & Filters** - basic search + filters

**Estimated Time:** 2-3 weeks  
**Value:** Immediate UX improvement

### Phase 2: Security & Validation (v2.1)
1. **Permissions & Security** - RBAC, soft deletes, row-level
2. **Validation & Rules** - unique_together, conditional, limits
3. **Notifications** - email, webhooks

**Estimated Time:** 3-4 weeks  
**Value:** Production-ready security

### Phase 3: Advanced Features (v2.2)
1. **Versioning & Audit** - full history, rollback
2. **Advanced Relationships** - polymorphic, has-many-through
3. **API & Export** - REST API, multiple formats

**Estimated Time:** 4-6 weeks  
**Value:** Enterprise features

### Phase 4: Analytics (v2.3)
1. **Analytics & Reporting** - metrics, charts, dashboards

**Estimated Time:** 3-4 weeks  
**Value:** Business intelligence

---

## 💻 Implementation Architecture

### Core Class Structure

```php
namespace DynamicCRUD\Metadata;

class TableMetadata {
    private PDO $pdo;
    private string $table;
    private array $metadata;
    
    public function __construct(PDO $pdo, string $table) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->metadata = $this->loadMetadata();
    }
    
    private function loadMetadata(): array {
        $comment = $this->getTableComment();
        return json_decode($comment, true) ?? [];
    }
    
    // UI/UX
    public function getDisplayName(): string;
    public function getIcon(): ?string;
    public function getListColumns(): array;
    public function getDefaultSort(): string;
    
    // Permissions
    public function hasPermission(string $action, string $role): bool;
    public function hasSoftDelete(): bool;
    public function getOwnerField(): ?string;
    
    // Forms
    public function getFormLayout(): string;
    public function getTabs(): array;
    public function getFieldsets(): array;
    
    // Behaviors
    public function hasTimestamps(): bool;
    public function isSluggable(): bool;
    public function isSortable(): bool;
    
    // Search
    public function getSearchFields(): array;
    public function getFilters(): array;
}
```

### Integration with DynamicCRUD

```php
class DynamicCRUD {
    private TableMetadata $tableMetadata;
    
    public function __construct(PDO $pdo, string $table, ...) {
        // ... existing code ...
        $this->tableMetadata = new TableMetadata($pdo, $table);
    }
    
    public function renderForm($id = null): string {
        $layout = $this->tableMetadata->getFormLayout();
        
        if ($layout === 'tabs') {
            return $this->renderTabbedForm($id);
        }
        
        return $this->renderStandardForm($id);
    }
    
    public function list(array $options = []): array {
        $columns = $this->tableMetadata->getListColumns();
        $sort = $this->tableMetadata->getDefaultSort();
        
        // Apply filters, search, etc.
    }
}
```

---

## 🎯 Competitive Analysis

### vs Laravel Nova
- ✅ **DynamicCRUD:** Zero-config from database
- ❌ **Nova:** Requires PHP resource classes

### vs Backpack
- ✅ **DynamicCRUD:** Metadata in database (portable)
- ❌ **Backpack:** Configuration in PHP files

### vs AdminLTE
- ✅ **DynamicCRUD:** Automatic from schema
- ❌ **AdminLTE:** Manual HTML/JS

**Unique Selling Point:** Configuration lives in the database, making it portable, versionable, and accessible to non-developers.

---

## 📝 Next Steps

1. **Review & Prioritize** - Discuss with Mario which features to implement first
2. **Prototype** - Build TableMetadata class with Phase 1 features
3. **Test** - Create examples demonstrating new capabilities
4. **Document** - Update docs with table metadata options
5. **Release** - Ship as v2.0 with breaking changes notice

---

## 🤔 Open Questions

1. **Backward Compatibility:** Should we maintain support for tables without metadata?
2. **Migration Path:** How do users migrate from v1.x to v2.x?
3. **UI Library:** Should we bundle a UI library (Bootstrap, Tailwind) or stay agnostic?
4. **Admin Panel:** Should we build a visual admin panel to edit table metadata?
5. **Performance:** How do we cache table metadata efficiently?

---

**End of Document**
