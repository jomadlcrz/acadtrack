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
| **Bootstrap Icons Catalog** | [icons.getbootstrap.com/](https://icons.getbootstrap.com/) |

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
   - Follow [CASING_GUIDELINES.md](file:///C:/xampp/htdocs/acadtrack/CASING_GUIDELINES.md): Title Case for card headers and modal titles; Sentence case for labels, helper text, button labels, and table headers; UPPERCASE for acronyms (`ID`, `CSRF`, `GWC`).
   - Maximum font weight is capped at **`600`** (semibold).
5. **Route URLs:**
   - All form `action` attributes and navigation `href` links must be wrapped with the `url(...)` helper function (e.g. `action="<?= url('/admin/users') ?>"`).

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

## 3. Card Container Specifications

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

## 4. Filter & Toolbar Bar Specifications

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

## 5. Table Specifications

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

## 6. Action Button Hierarchy

| Role / Intent | Bootstrap Class | Example Text (Sentence case) | Icon |
| :--- | :--- | :--- | :--- |
| **Primary CTA** | `btn btn-primary` | `Create user`, `Save settings`, `Enter grades` | `bi bi-check2`, `bi bi-plus-circle` |
| **Secondary / Safe** | `btn btn-outline-secondary` | `Cancel`, `Back to users`, `Save draft` | `bi bi-arrow-left`, `bi bi-save` |
| **Commitment Action** | `btn btn-warning` | `Submit for review` | `bi bi-send` |
| **Dean Approval** | `btn btn-success` | `Approve` | `bi bi-check-circle` |
| **Destructive** | `btn btn-danger` | `Delete`, `Remove assignment` | `bi bi-trash`, `bi bi-person-x` |
| **Compact Table Action**| `btn btn-sm ...` | `Edit`, `View students` | `bi bi-pencil`, `bi bi-people` |

---

## 7. Developer Verification Checklist

Before deploying any view containing forms or UI elements:
- [ ] Are text/number inputs using `.form-control`?
- [ ] Are `<select>` elements using `.form-select` (NOT `.form-control`)?
- [ ] Are form labels using `.form-label` with `font-weight: 600`?
- [ ] Are fields grouped with `.mb-3` or `<div class="row g-3">`?
- [ ] Are forms enclosed in Bootstrap `.card` containers with `.card-header` and `.card-footer`?
- [ ] Are all button labels, form labels, and table headers in **Sentence case** per `CASING_GUIDELINES.md`?
- [ ] Are all form action attributes and links using `<?= url('/path') ?>`?
- [ ] Is `font-weight` capped at `600` (zero `700`, `800`, or `bold`)?
