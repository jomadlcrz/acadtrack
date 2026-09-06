# GWC Grading System Development Skill

## Purpose

This skill defines how the GWC Grading System should be developed, modified, reviewed, and maintained.

The application uses:

- PHP 8.2+
- Vanilla PHP
- MySQL
- PDO
- Composer
- Vanilla JavaScript
- HTML5
- CSS3

The project must remain framework-independent.

---

# 1. Core Architecture

Follow:

```
Request
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
Database
```

For responses:

```
Database
  ↓
Repository
  ↓
Service
  ↓
Controller
  ↓
View / JSON
```

Never bypass the architecture without a strong reason.

---

# 2. Responsibilities

## Controller

Controllers handle:

- HTTP input
- Request parameters
- Calling services
- Redirects
- Views
- HTTP responses

Controllers must not contain complex business logic.

---

## Service

Services handle:

- Business rules
- Workflows
- Calculations
- Transactions
- State changes
- Coordination between repositories

---

## Repository

Repositories handle:

- SQL
- PDO
- Database queries
- Persistence
- Retrieval

Repositories must not contain UI logic.

---

## Model

Models represent domain entities.

---

## Middleware

Middleware handles:

- Authentication
- Authorization
- CSRF
- Request-level security

---

## Validator

Validators handle input validation.

---

## View

Views handle presentation only.

Never put SQL in views.

---

# 3. Security Rules

Always assume user input is malicious.

Validate:

```text
GET
POST
JSON
FILES
Cookies
Headers
```

Use PDO prepared statements.

Never concatenate user input into SQL.

Use:

```php
password_hash()
password_verify()
```

for passwords.

Escape HTML using:

```php
htmlspecialchars()
```

Use CSRF protection for state-changing requests.

Authorization must always happen on the server.

---

# 4. Role-Based Access

Supported roles:

```text
ADMIN
DEAN
FACULTY
STUDENT
```

Never rely on frontend role checks for security.

Example:

```text
Student
 ↓
Dean endpoint
 ↓
Authorization
 ↓
403
```

---

# 5. Grading Workflow

Use the grading sheet state machine:

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

Possible return path:

```
UNDER_REVIEW
 ↓
RETURNED
 ↓
DRAFT
```

Every state transition must be validated.

A user must not be able to manually submit:

```text
status=APPROVED
```

through POST data.

The server determines valid state transitions.

---

# 6. Grade Calculation

Grade calculations must be isolated from presentation.

Example:

```php
calculateFinalGrade(
    grades,
    gradingPeriodWeights,
    gradingMethod
);
```

Do not duplicate grading formulas across controllers.

The same calculation must produce the same result regardless of the UI used.

---

# 7. Database Rules

Use transactions for multi-step operations.

Example:

```text
Submit Grade Sheet
 ↓
Validate grades
 ↓
Update status
 ↓
Create approval record
 ↓
Create notification
```

These operations should be atomic when required.

Always use explicit column lists.

Avoid:

```sql
SELECT *
```

in production repository methods unless there is a specific reason.

Prefer:

```sql
SELECT
    id,
    student_id,
    grading_sheet_id,
    score
FROM grades
WHERE student_id = :student_id
```

---

# 8. PHP Rules

Every PHP source file:

```php
<?php

declare(strict_types=1);
```

Use:

- PSR-4
- PSR-12
- Typed parameters
- Typed return values
- Strict comparisons
- Dependency injection
- Small functions

Avoid:

- Global state
- Massive classes
- Massive functions
- Duplicate logic
- Hidden side effects
- Suppressed errors

---

# 9. JavaScript Rules

Use Vanilla JavaScript.

Prefer:

```js
addEventListener()
fetch()
FormData
querySelector()
classList
```

Avoid inline event handlers.

Bad:

```html
onclick="saveGrades()"
```

Good:

```js
button.addEventListener('click', saveGrades);
```

JavaScript is responsible for interaction.

PHP is responsible for security and business rules.

---

# 10. API/JSON

For asynchronous requests use a consistent response format.

Success:

```json
{
    "success": true,
    "message": "Operation completed successfully.",
    "data": {}
}
```

Failure:

```json
{
    "success": false,
    "message": "Unable to complete the operation.",
    "errors": {}
}
```

HTTP status codes must also be meaningful.

Examples:

```text
200 OK
201 Created
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Unprocessable Entity
500 Internal Server Error
```

---

# 11. UI Rules

Follow these principles:

- Clear hierarchy
- Consistent spacing
- Consistent components
- Clear primary actions
- Destructive confirmation
- Visible loading states
- Clear error messages
- Responsive layout
- Accessible controls

Do not use color as the only indication of status.

---

# 12. Forms

Forms must:

1. Validate client-side for UX
2. Validate server-side for security
3. Preserve valid values after validation errors
4. Prevent duplicate submission
5. Show clear errors

Client validation never replaces server validation.

---

# 13. Modals

Use modals for:

- Confirmation
- Short forms
- Focused secondary tasks

Do not put extremely large workflows inside nested modals.

Avoid:

```text
Modal
  ↓
Modal
  ↓
Modal
  ↓
Modal
```

Prefer navigating to a dedicated page for complex workflows.

---

# 14. Notifications

Use:

```text
Toast
```

for lightweight feedback:

```text
Saved successfully
Updated successfully
```

Use confirmation dialogs for consequential actions:

```text
Submit grading sheet?
Approve grading sheet?
Finalize grades?
```

Use inline validation for field errors.

---

# 15. File Organization

Backend:

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

Frontend:

```text
public/assets/
├── css/
├── js/
└── images/
```

---

# 16. Naming

PHP:

```text
PascalCase classes
camelCase methods
camelCase variables
UPPER_SNAKE_CASE constants
```

Database:

```text
snake_case
```

JavaScript:

```text
camelCase functions/variables
PascalCase classes
```

CSS:

```text
kebab-case
```

Routes:

```text
lowercase/kebab-case
```

---

# 17. Before Modifying Code

Before changing an existing feature:

1. Identify the route.
2. Identify the controller.
3. Identify the service.
4. Identify the repository.
5. Identify the relevant model.
6. Identify the view.
7. Identify related JavaScript.
8. Check existing validation.
9. Check authorization.
10. Check database constraints.

Do not immediately create duplicate code.

---

# 18. Before Creating a New Class

Ask:

```text
Does this responsibility already exist?
```

Search the project first.

Do not create:

```text
GradeService2
GradeHelperNew
NewGradeController
GradeManager
GradeProcessor
```

simply because the existing implementation is inconvenient.

Improve the existing architecture when appropriate.

---

# 19. Before Creating a New Package

Prefer native PHP when the problem is simple.

Use Composer packages when they provide meaningful functionality.

Do not add dependencies for trivial tasks.

Example:

```text
Simple validation → native PHP
Simple JSON → native PHP
Simple routing → project router
Complex email → PHPMailer
Environment configuration → phpdotenv
Testing → PHPUnit
```

---

# 20. Error Handling

Never hide exceptions.

Bad:

```php
catch (Throwable $e) {
}
```

Good:

```php
catch (Throwable $e) {
    logger()->error($e->getMessage());

    throw $e;
}
```

User-facing messages should not expose sensitive implementation details.

---

# 21. Testing

Important business logic must be testable.

Prioritize tests for:

```text
Grade calculation
Grading weights
50-based conversion
Zero-based grading
Grade submission
Grade approval
Authorization
State transitions
```

---

# 22. Git Rules

Commit messages should describe the change.

Good:

```text
feat: add grading sheet submission
fix: prevent duplicate grade submission
refactor: extract grade calculation service
docs: update architecture
```

Avoid:

```text
update
changes
fix
asdf
test
```

---

# 23. Definition of Done

A feature is not complete until:

- Backend logic works
- Validation works
- Authorization works
- Database operations are safe
- Errors are handled
- UI works
- JavaScript works
- Mobile layout is acceptable
- No debug statements remain
- Existing functionality is not broken
- Relevant tests pass

---

# 24. AI Coding Rule

When modifying this project:

> Prefer consistency with the existing architecture over introducing a new architecture.

Do not introduce a framework unless explicitly requested.

Do not replace Vanilla PHP with Laravel, Symfony, CodeIgniter, React, Vue, or another framework without explicit approval.

Do not rewrite working modules unnecessarily.

Make the smallest clean change that correctly solves the problem.

---

# 25. Priority Order

When requirements conflict, prioritize:

1. Security
2. Correctness
3. Data integrity
4. Authorization
5. Maintainability
6. Accessibility
7. User experience
8. Performance
9. Convenience

Never sacrifice security or data integrity for UI convenience.

---

# 26. Golden Rule

The project should remain:

```text
Simple
Secure
Readable
Testable
Maintainable
Framework-independent
```

Every new implementation should make the codebase easier to understand, not harder.
