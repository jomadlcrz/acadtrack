# Acadtrack — Bootstrap 5 Component & Form Guidelines

> **Project:** Golden West Colleges, Inc. (GWC) Acadtrack System  
> **Framework:** Bootstrap 5.3.3 + Bootstrap Icons 1.11.3  
> **Official Bootstrap Documentation Website:** [https://getbootstrap.com/docs/5.3/](https://getbootstrap.com/docs/5.3/)  
> **Official Bootstrap Icons Website:** [https://icons.getbootstrap.com/](https://icons.getbootstrap.com/)  
> **Related Standards:** [CASING_GUIDELINES.md](file:///C:/xampp/htdocs/acadtrack/CASING_GUIDELINES.md) | [WEB_DESIGN_GUIDE.md](file:///C:/xampp/htdocs/acadtrack/docs/WEB_DESIGN_GUIDE.md) | [ANTI_GENERIC_DESIGN.md](file:///C:/xampp/htdocs/acadtrack/docs/ANTI_GENERIC_DESIGN.md)

---

## Official Documentation Quick Reference

| Component / Topic | Official Documentation Link |
| :--- | :--- |
| **Main Documentation** | [getbootstrap.com/docs/5.3/](https://getbootstrap.com/docs/5.3/) |
| **Forms Overview** | [getbootstrap.com/docs/5.3/forms/overview/](https://getbootstrap.com/docs/5.3/forms/overview/) |
| **Form Controls (`.form-control`)** | [getbootstrap.com/docs/5.3/forms/form-control/](https://getbootstrap.com/docs/5.3/forms/form-control/) |
| **Select Dropdowns (`.form-select`)**| [getbootstrap.com/docs/5.3/forms/select/](https://getbootstrap.com/docs/5.3/forms/select/) |
| **Input Group (`.input-group`)** | [getbootstrap.com/docs/5.3/forms/input-group/](https://getbootstrap.com/docs/5.3/forms/input-group/) |
| **Grid System (`.row`, `.col-*`)** | [getbootstrap.com/docs/5.3/layout/grid/](https://getbootstrap.com/docs/5.3/layout/grid/) |
| **Card Components (`.card`)** | [getbootstrap.com/docs/5.3/components/card/](https://getbootstrap.com/docs/5.3/components/card/) |
| **Buttons (`.btn`)** | [getbootstrap.com/docs/5.3/components/buttons/](https://getbootstrap.com/docs/5.3/components/buttons/) |
| **Tables (`.table`)** | [getbootstrap.com/docs/5.3/content/tables/](https://getbootstrap.com/docs/5.3/content/tables/) |
| **Badges (`.badge`)** | [getbootstrap.com/docs/5.3/components/badge/](https://getbootstrap.com/docs/5.3/components/badge/) |
| **Alerts (`.alert`)** | [getbootstrap.com/docs/5.3/components/alerts/](https://getbootstrap.com/docs/5.3/components/alerts/) |
| **Modals (`.modal`)** | [getbootstrap.com/docs/5.3/components/modal/](https://getbootstrap.com/docs/5.3/components/modal/) |
| **Pagination (`.pagination`)** | [getbootstrap.com/docs/5.3/components/pagination/](https://getbootstrap.com/docs/5.3/components/pagination/) |
| **Bootstrap Icons Catalog** | [icons.getbootstrap.com/](https://icons.getbootstrap.com/) |

---

## Acadtrack Component Stylesheets Directory (`public/assets/css/components/`)

All UI components are decoupled from page styles and maintained as standalone stylesheets to guarantee cross-portal consistency:

| UI Component | Stylesheet Path | Key Classes & Elements Governed |
| :--- | :--- | :--- |
| **Buttons & Actions** | `components/button.css` | `.btn`, `.btn-primary`, `.btn-outline-*`, `.btn-dark`, `.btn-sm`, `.btn-group` |
| **Form Inputs** | `components/input.css` | `.form-control`, `.form-control-sm`, `.form-label`, `.grade-input`, `.form-text`, `.input-group` |
| **Select Dropdowns** | `components/select.css` | `.form-select`, `.form-select-sm`, custom SVG caret, inline min-width |
| **Card & KPI Blocks** | `components/card.css` | `.stat-card`, `.dashboard-stats`, `.stat-icon-*`, `.quick-action-card`, `.content-card` |
| **Academic Tables** | `components/table.css` | `.table`, `.table.align-middle`, thead uppercase 11px specs, tbody cells, `.table-academic`, `.tabular-nums` |
| **Modals & Dialogs** | `components/modal.css` | `.modal-content` (8px radius, border), `.modal-header`, `.modal-body`, `.modal-footer`, backdrops |
| **Badges & Tags** | `components/badge.css` | `.badge`, `.badge-role`, `.badge-admin`, `.badge-dean`, `.badge-approved`, `.text-amber` |
| **Top Navbar** | `components/navbar.css` | `.app-navbar`, brand text, term badge, user card, avatar, logout button |
| **Sidebar Navigation**| `components/sidebar.css` | `.sidebar`, `.sidebar-nav`, `.sidebar-group`, `.sidebar-link`, active link state |
| **Alerts & Toasts** | `components/alert.css` | `.alert`, `.alert-success`, `.alert-danger`, `.alert-warning`, `.alert-info`, `.toast-container` |
| **Pagination** | `components/pagination.css` | `.pagination`, `.pagination-sm`, `.page-item`, `.page-link`, active/disabled states |
| **Empty States** | `components/empty-state.css` | `.empty-state`, `.empty-state-card`, `.empty-state-icon`, `.empty-state-title`, `.empty-state-text` |
| **Page & Section Headers** | `components/page-header.css` | `.page-header`, `.page-header-title`, `.page-header-subtitle`, `.page-header-actions`, `.section-header` |
| **Filter Bars** | `components/filter-bar.css` | `.filter-bar`, `.filter-bar-card`, `.filter-search`, `.filter-group`, `.filter-counter` |
| **Layout Shells** | `layouts/app-shell.css`, `layouts/auth.css`, `layouts/home.css` | `.app-container`, `.main-content`, `.auth-card`, `.auth-container`, `.landing-body` |

---

## 1. Overview & Core Rules

To guarantee visual harmony, accessibility, and high information density across all authenticated portals (Admin, Dean, Faculty, Student), all forms and UI components must strictly adhere to the following Bootstrap 5 conventions:

1. **Consistent Form Controls:**
   - Text, password, email, and number inputs **MUST** use `.form-control`.
   - Dropdown selections **MUST** use `.form-select` (never `.form-control` on `<select>`).
   - Labels **MUST** use `.form-label` with `font-weight: 600`.
   - Field containers **MUST** use `.mb-3` for vertical spacing.
   - Helper notes **MUST** use `<div class="form-text">`.
2. **Grid Layout:**
   - Multi-column forms **MUST** use `<div class="row g-3">` with responsive column widths (`col-md-6`, `col-md-4`, etc.).
3. **Card Containers:**
   - Form and content panels **MUST** be structured within Bootstrap `.card` elements with crisp `.card-header`, `.card-body`, and optional `.card-footer`.
4. **Casing & Typography Standards:**
   - Follow [CASING_GUIDELINES.md](file:///C:/xampp/htdocs/acadtrack/CASING_GUIDELINES.md): Title Case for card headers, page titles, and modal titles; Sentence case for labels, helper text, button labels, and table headers; UPPERCASE for acronyms (`ID`, `CSRF`, `GWC`).
   - Maximum font weight is capped at **`600`** (semibold).
5. **Route URLs:**
   - All form `action` attributes and navigation `href` links must be wrapped with the `url(...)` helper function (e.g. `action="<?= url('/admin/users') ?>"`).
6. **Unified Page Header Architecture (No Double Headers):**
   - Authenticated views **MUST NOT** render their own inner page title or header block (`<h1>`, `<h2>`, or inner `<div class="d-flex justify-content-between ...">`).
   - All authenticated page headers are centrally rendered by `app/Views/layouts/dashboard.php`.
   - Views configure `$pageTitle`, optional `$subtitle`, and optional `$headerActions` at the very top of the file before `ob_start()`.
   - Views **MUST NEVER** overwrite or reassign `$pageTitle` at the bottom before `include layouts/dashboard.php`.

---

## 2. Standard Form Patterns

### 2.1 Standard Form Field
```html
<div class="mb-3">
    <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="e.g., Juan" required>
    <div class="form-text">Institutional legal first name.</div>
</div>
```

### 2.2 Dropdown Select Field
Always use `.form-select` (never `.form-control`):
```html
<div class="mb-3">
    <label for="role" class="form-label">System role <span class="text-danger">*</span></label>
    <select class="form-select" id="role" name="role" required>
        <option value="">Select a role</option>
        <option value="Student">Student</option>
        <option value="Faculty">Faculty</option>
        <option value="Dean">Dean</option>
        <option value="Admin">Admin</option>
    </select>
    <div class="form-text">Determines system permissions and portal navigation.</div>
</div>
```

### 2.3 Responsive Multi-Column Form Grid
Use `.row g-3` with `.col-md-*`:
```html
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
    </div>
    <div class="col-md-6">
        <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
    </div>
</div>
```

### 2.4 Input Group with Icon
```html
<div class="mb-3">
    <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input type="email" class="form-control" id="email" name="email" placeholder="name@gwc.edu" required>
    </div>
    <div class="form-text">Must be a valid institutional or personal email address.</div>
</div>
```

### 2.5 Tabular Numeric Grade Input
High-density numeric input with right alignment and `tabular-nums`:
```html
<input type="number" 
       name="grades[<?= $student['id'] ?>]" 
       value="<?= htmlspecialchars($score) ?>" 
       min="0" max="100" step="0.01" 
       class="form-control grade-input text-end font-monospace" 
       placeholder="0.00">
```

---

## 3. Unified Page Header Architecture (Single `<h1>` Standard)

To prevent visual "double headers" and maintain consistent hierarchy across all authenticated views, individual view templates **MUST NEVER** render inner page header blocks (`<h1>`, `<h2>`, or inner `<div class="d-flex justify-content-between mb-4">`).

### 3.1 Central Layout Ownership
The page header is centrally rendered by `app/Views/layouts/dashboard.php`:
```html
<div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if (!empty($headerActions)): ?>
        <div class="dashboard-actions d-flex align-items-center gap-2">
            <?= $headerActions ?>
        </div>
    <?php endif; ?>
</div>
```

### 3.2 View Implementation Pattern
Every authenticated view sets `$pageTitle`, optional `$subtitle`, and optional `$headerActions` at the very top before `ob_start()`.

```php
<?php
$pageTitle = 'User Management';
$subtitle = 'Manage institutional accounts, role assignments, and system access.';
$headerActions = '<a href="' . url('/admin/users/create') . '" class="btn btn-primary d-inline-flex align-items-center gap-2"><i class="bi bi-person-plus"></i> Add user</a>';
ob_start();
?>

<!-- View Content Card / Table / Forms (NO inner <h1> or <h2> headers) -->
<div class="card shadow-sm border-0 mb-4">
    ...
</div>

<?php
$content = ob_get_clean();
// CRITICAL: NEVER overwrite $pageTitle here!
include __DIR__ . '/../../layouts/dashboard.php';
?>
```

### 3.3 Strict Rules for Page Headers
1. **Exactly ONE `<h1>` per page:** Rendered exclusively by `layouts/dashboard.php`.
2. **No Inner Page Headers in Views:** Do not place `<div class="d-flex justify-content-between ..."><h2>...</h2></div>` inside the view body.
3. **No Bottom Title Overwriting:** Never set `$pageTitle = '...'` at the bottom of the file before `include layouts/dashboard.php`.
4. **Casing Rules:**
   - `$pageTitle`: **Title Case** (e.g. `User Management`, `Curricular Subjects`, `Grade Encoding Sheet`).
   - `$subtitle`: **Sentence case**, concise 1-sentence description (e.g. `Manage institutional accounts, role assignments, and system access.`).
   - `$headerActions`: Action buttons must use **Sentence case** labels with Bootstrap Icons (e.g. `<i class="bi bi-person-plus"></i> Add user`, `<i class="bi bi-arrow-left"></i> Back to users`).

---

## 4. Card Container Specifications

Forms in authenticated pages must be placed inside grounded `.card` structures:

```html
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-plus text-primary"></i> Account Credentials & Profile
            </h3>
            <small class="text-muted">All primary fields are required for initial provisioning.</small>
        </div>
    </div>
    
    <form method="POST" action="<?= url('/admin/users') ?>">
        <?= csrf_field() ?>
        
        <div class="card-body p-4">
            <!-- Form fields go here -->
        </div>
        
        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-check2"></i> Create user
            </button>
        </div>
    </form>
</div>
```

---

## 5. Filter & Toolbar Bar Specifications

Index/roster pages must use clean horizontal filter toolbars:

```html
<div class="card shadow-sm mb-4" style="border: 1px solid #e2e8f0; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="role" class="form-label mb-0 fw-semibold small text-muted">
                    <i class="bi bi-funnel text-primary me-1"></i> Filter by role:
                </label>
                <select id="role" name="role" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    <option value="Admin">Admin</option>
                    <option value="Dean">Dean</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Student">Student</option>
                </select>
            </div>
            <?php if (!empty($currentRole)): ?>
            <div class="col-auto">
                <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-x-circle"></i> Clear filter
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
```

---

## 6. Table Specifications

Data tables must use Bootstrap `.table` with responsive wrapper and Sentence case headers:

```html
<div class="card shadow-sm" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold text-muted small">Subject code</th>
                    <th class="fw-semibold text-muted small">Descriptive title</th>
                    <th class="fw-semibold text-muted small">Assigned faculty</th>
                    <th class="fw-semibold text-muted small text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-semibold text-primary font-monospace">IT 101</td>
                    <td class="fw-semibold text-dark">Introduction to Computing</td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">Assigned</span></td>
                    <td class="text-end">
                        <a href="..." class="btn btn-sm btn-outline-primary">Edit</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

---

## 7. Action Button Hierarchy

| Role / Intent | Bootstrap Class | Example Text (Sentence case) | Icon |
| :--- | :--- | :--- | :--- |
| **Primary CTA** | `btn btn-primary` | `Create user`, `Save settings`, `Enter grades` | `bi bi-check2`, `bi bi-plus-circle` |
| **Secondary / Safe** | `btn btn-outline-secondary` | `Cancel`, `Back to users`, `Save draft` | `bi bi-arrow-left`, `bi bi-save` |
| **Commitment Action** | `btn btn-warning` | `Submit for review` | `bi bi-send` |
| **Dean Approval** | `btn btn-success` | `Approve` | `bi bi-check-circle` |
| **Destructive** | `btn btn-danger` | `Delete`, `Remove assignment` | `bi bi-trash`, `bi bi-person-x` |
| **Compact Table Action**| `btn btn-sm ...` | `Edit`, `View students` | `bi bi-pencil`, `bi bi-people` |

---

## 8. Developer Verification Checklist

Before deploying any view containing forms or UI elements:
- [ ] Is there exactly **ONE `<h1>`** on the page, rendered exclusively by `app/Views/layouts/dashboard.php`?
- [ ] Did you verify that **NO duplicate inner `<h1>`, `<h2>`, or `<div class="d-flex justify-content-between mb-4">`** header exists inside the view body?
- [ ] Are action buttons passed via `$headerActions` at the top before `ob_start()`, rather than placed inside an inner header?
- [ ] Did you verify that `$pageTitle` is **NOT re-assigned or overwritten** at the bottom before `include layouts/dashboard.php`?
- [ ] Are text/number inputs using `.form-control`?
- [ ] Are `<select>` elements using `.form-select` or `.form-select-sm` (NOT `.form-control`)?
- [ ] Are form labels using `.form-label` with `font-weight: 600`?
- [ ] Are fields grouped with `.mb-3` or `<div class="row g-3">`?
- [ ] Are forms enclosed in Bootstrap `.card` containers with `.card-header` and `.card-footer`?
- [ ] Are all button labels, form labels, and table headers in **Sentence case** per `CASING_GUIDELINES.md`?
- [ ] Are all form action attributes and links using `<?= url('/path') ?>`?
- [ ] Is `font-weight` capped at `600` (zero `700`, `800`, or `bold`)?
