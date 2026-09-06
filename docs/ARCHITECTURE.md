# Architecture

## 1. Overview

The GWC Grading System is a server-rendered web application built using:

- PHP 8.2+
- Vanilla PHP
- MySQL
- PDO
- Composer
- Vanilla JavaScript
- HTML5
- CSS3

The application follows a lightweight MVC-inspired architecture with clear separation between:

- HTTP handling
- Business logic
- Data access
- Validation
- Authentication and authorization
- Presentation

The project must remain framework-independent.

---

## 2. Architecture

The application follows this flow:

```
Browser
    ↓
public/index.php
    ↓
Router
    ↓
Middleware
    ↓
Controller
    ↓
Service
    ↓
Repository
    ↓
PDO / MySQL
    ↓
Repository
    ↓
Service
    ↓
Controller
    ↓
View
    ↓
Browser
```

---

## 3. Project Structure

```text
project/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   ├── Middleware/
│   ├── Validators/
│   ├── Helpers/
│   └── Views/
│
├── bootstrap/
├── config/
├── database/
├── public/
├── routes/
├── storage/
├── tests/
├── vendor/
│
├── composer.json
├── composer.lock
├── .env
└── README.md
```

---

## 4. Front Controller

All web requests should enter through:

```text
public/index.php
```

The application must not expose individual PHP files as public endpoints.

Bad:

```text
/public/login.php
/public/save-grade.php
/public/delete-student.php
```

Good:

```text
/public/index.php
```

Routes determine the requested action.

---

## 5. Controllers

Controllers are responsible for:

- Receiving HTTP requests
- Reading request parameters
- Calling services
- Returning views or responses
- Redirecting after successful operations

Controllers must not contain complex business logic.

Bad:

```php
public function save()
{
    // 100 lines of grade calculation
    // SQL queries
    // email logic
}
```

Good:

```php
public function save()
{
    $this->gradingService->saveGrades($_POST);

    redirect('/faculty/grading');
}
```

---

## 6. Services

Services contain application and business logic.

Examples:

```text
AuthService
GradingService
GradeService
EvaluationService
NotificationService
EmailService
```

A service may coordinate:

- Validation
- Calculations
- Multiple repositories
- Transactions
- State transitions
- Notifications

---

## 7. Repositories

Repositories are responsible for database access.

Example:

```text
GradeRepository
StudentRepository
SubjectRepository
UserRepository
GradingSheetRepository
```

Repositories should contain SQL and database-specific operations.

Business rules must remain in services.

---

## 8. Models

Models represent application/domain entities.

Examples:

```text
User
Student
Faculty
Subject
Enrollment
Grade
GradingSheet
GradingPeriod
AcademicTerm
```

Models should not become large collections of unrelated database logic.

---

## 9. Middleware

Middleware handles cross-cutting request concerns.

Examples:

```text
AuthMiddleware
RoleMiddleware
CsrfMiddleware
```

Typical request flow:

```
Request
 ↓
Authentication
 ↓
Authorization
 ↓
Controller
```

---

## 10. Role-Based Access

The system contains:

```text
ADMIN
DEAN
FACULTY
STUDENT
```

Authorization must be enforced on the server.

Frontend hiding is not security.

Example:

```
Student
  ↓
GET /dean/grade-review
  ↓
RoleMiddleware
  ↓
403 Forbidden
```

---

## 11. Grading Workflow

The grading sheet follows a controlled state workflow:

```
DRAFT
  ↓
SUBMITTED
  ↓
UNDER_REVIEW
  ↓
APPROVED
  ↓
FINALIZED
```

Returned sheets may follow:

```
SUBMITTED
  ↓
UNDER_REVIEW
  ↓
RETURNED
  ↓
DRAFT
```

Only authorized roles may perform state transitions.

---

## 12. Database Access

All database access must use PDO.

Prepared statements are mandatory.

Never construct SQL using raw user input.

Bad:

```php
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

Good:

```php
$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE id = :id'
);

$stmt->execute([
    'id' => $id
]);
```

---

## 13. Transactions

Use database transactions when multiple operations must succeed or fail together.

Example:

```
Submit grading sheet
    ↓
Update grading sheet status
    ↓
Create approval record
    ↓
Create notification
```

These operations should be transactional when consistency requires it.

---

## 14. Views

Views are responsible only for presentation.

Views may contain:

- HTML
- Escaped output
- Small presentation conditions
- Simple loops

Views must not contain:

- SQL
- Database connections
- Business calculations
- Authentication logic
- Complex application logic

---

## 15. Security

The architecture must protect against:

- SQL injection
- XSS
- CSRF
- Session attacks
- Unauthorized access
- Privilege escalation

Passwords must always be stored using:

```php
password_hash()
```

and verified using:

```php
password_verify()
```

---

## 16. Composer

Third-party dependencies belong in Composer.

Dependencies must never be manually copied into the project.

Use:

```text
composer.json
composer.lock
vendor/
```

Never modify:

```text
vendor/
```

manually.

---

## 17. Environment Configuration

Secrets belong in `.env`.

Never commit:

```text
.env
```

Commit:

```text
.env.example
```

---

## 18. Core Rule

The most important architectural rule is:

> Keep responsibilities separate.

Controllers handle requests.

Services handle business rules.

Repositories handle persistence.

Views handle presentation.

Middleware handles request protection.

Database handles persistence.

---

## 19. Error Handling & Integrity Violation Guide

To ensure high reliability and a seamless user experience, raw PHP fatal errors, database stack traces, and uncaught exceptions must never leak to end-users.

### 19.1 Preventing Raw Database Exceptions

When database actions conflict with unique constraints (e.g., duplicate subject code per term, duplicate user email, or duplicate assignment):

1. **Controller-Level Catching & Friendly Flash Alerts:**
   - Controllers wrap state-changing repository calls (`create`, `update`, `delete`) in `try ... catch (\PDOException $e)` blocks.
   - Specific constraint codes (such as SQLSTATE `23000` / MySQL error `1062 Duplicate entry`) are translated into clear, contextual messages (e.g., *"Subject code 'CS102' already exists for this academic term."*).
   - The message is stored in the session flash (`$session->flash('error', $message)`).
   - The user is redirected back to the originating page via HTTP 302, where [components/alert.php](file:///C:/xampp/htdocs/grading-system/app/Views/components/alert.php) renders a dismissible alert banner.

### 19.2 Global Exception Interception (`Application::run`)

In the event of an unhandled exception or database error:
- [Application::run()](file:///C:/xampp/htdocs/grading-system/app/Core/Application.php) catches `\Throwable $e`.
- Mutating requests (`POST`, `PUT`, `DELETE`) automatically extract a user-friendly summary, flash an error message, and redirect safely back to `$_SERVER['HTTP_REFERER']`.
- Non-mutating requests (`GET`) or unhandled page loads render the dedicated [app/Views/errors/error.php](file:///C:/xampp/htdocs/grading-system/app/Views/errors/error.php) template.
- When `APP_DEBUG=true`, technical stack traces are neatly encapsulated in an expandable `<details>` container for developers, keeping the interface clean and secure in production.

---

## 20. View & Template Architecture (Unified Layout & Header Pattern)

Views in the GWC Acadtrack system follow a structured buffer-and-include pattern to render pages inside unified master layouts.

### 20.1 View Rendering Flow
```text
Controller
   │
   ▼ calls $view->render('admin.users.index', $data)
View.php extracts $data and includes app/Views/admin/users/index.php
   │
   ▼ View template sets configuration
   $pageTitle = 'User Management';
   $subtitle = 'Manage institutional accounts, role assignments, and system access.';
   $headerActions = '<a href="..." class="btn btn-primary">...</a>';
   ob_start();
   │
   ▼ View outputs main card / table / form body
   │
   ▼ View closes buffer and includes layout
   $content = ob_get_clean();
   include __DIR__ . '/../../layouts/dashboard.php';
```

### 20.2 The Single Header Rule (Preventing Double Headers)
1. **Master Layout Header Ownership:**
   - `app/Views/layouts/dashboard.php` centrally renders the only `<h1>` tag on the page inside `.dashboard-header`, alongside the optional subtitle and right-aligned `$headerActions`.
2. **Strict Prohibition of View-Level Inner Headers:**
   - Views **must never** render an inner `<div class="d-flex justify-content-between ..."><h2>...</h2></div>` or inner `<h1>`.
   - Action buttons that belong in the header (such as "Add user" or "Back to subjects") must be assigned to `$headerActions` as HTML strings before `ob_start()`.
3. **No Bottom Variable Overwriting:**
   - Never reassign `$pageTitle` at the bottom of the view template before `include layouts/dashboard.php`. All view metadata is declared once at the top of the file.
