# Module Organization

## 1. Purpose

Modules should be organized by responsibility.

The project must not become a collection of unrelated PHP files.

---

## 2. Backend Modules

```text
app/
├── Controllers/
├── Models/
├── Repositories/
├── Services/
├── Middleware/
├── Validators/
├── Helpers/
└── Views/
```

---

## 3. Controllers

Controllers are grouped by role when useful.

```text
Controllers/
├── AuthController.php
├── DashboardController.php
│
├── Admin/
├── Dean/
├── Faculty/
└── Student/
```

---

## 4. Services

Services should represent application capabilities.

```text
Services/
├── AuthService.php
├── GradingService.php
├── GradeService.php
├── EvaluationService.php
├── NotificationService.php
└── EmailService.php
```

Avoid creating services simply because a file is getting long.

---

## 5. Repositories

Repositories represent persistence concerns.

```text
Repositories/
├── UserRepository.php
├── StudentRepository.php
├── FacultyRepository.php
├── SubjectRepository.php
├── GradeRepository.php
└── GradingSheetRepository.php
```

---

## 6. Views

Views are grouped according to application areas.

```text
Views/
├── layouts/
├── components/
├── auth/
├── admin/
├── dean/
├── faculty/
└── student/
```

---

## 7. Shared Components

Reusable UI belongs in:

```text
Views/components/
```

Examples:

```text
navbar.php
sidebar.php
modal.php
toast.php
pagination.php
```

---

## 8. CSS

Global CSS:

```text
assets/css/app.css
```

Shared components:

```text
assets/css/components.css
```

Page-specific:

```text
assets/css/pages/
├── dashboard.css
├── grading.css
└── auth.css
```

---

## 9. JavaScript

```text
assets/js/
├── app.js
├── components/
└── pages/
```

Components contain reusable behavior.

Pages contain page-specific behavior.

---

## 10. Database

```text
database/
├── migrations/
├── seeders/
└── sql/
```

Schema:

```text
database/sql/schema.sql
```

---

## 11. Storage

Runtime-generated files belong in:

```text
storage/
├── logs/
├── uploads/
└── cache/
```

Storage must not contain application source code.

---

## 12. Public Directory

Only publicly accessible files belong in:

```text
public/
```

The following should NOT be publicly accessible:

```text
.env
config/
app/
database/
storage/
vendor/
```

---

## 13. Dependency Direction

Prefer:

```text
Controller
    ↓
Service
    ↓
Repository
    ↓
Database
```

Do not allow repositories to depend on controllers.

Do not allow models to directly depend on views.

---

## 14. Feature Growth

If a feature becomes sufficiently large, it may receive its own internal organization.

Example:

```text
app/Services/Grading/
├── GradeCalculator.php
├── GradeSubmissionService.php
└── GradeApprovalService.php
```

Do this only when the feature actually requires it.

Avoid premature complexity.
