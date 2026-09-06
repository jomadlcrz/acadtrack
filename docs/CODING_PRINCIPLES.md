# Coding Principles

## 1. Keep It Simple

Prefer simple solutions over unnecessary abstractions.

Do not introduce a class, service, repository, or package unless it provides a clear benefit.

---

## 2. Single Responsibility

Each class/function should have one clear responsibility.

Bad:

```text
UserController
    ├── Login
    ├── Send Email
    ├── Generate PDF
    ├── Calculate Grades
    └── Execute SQL
```

Good:

```text
UserController
AuthService
EmailService
ReportService
GradeService
UserRepository
```

---

## 3. Don't Repeat Yourself

Avoid duplicated business logic.

If grade calculation exists in:

```text
Faculty
Dean
Student
```

do not implement it three times.

Create one reusable service.

```php
$gradeService->calculateFinalGrade(...);
```

---

## 4. Explicit Code

Prefer code that is easy to understand.

Avoid clever one-liners when they reduce readability.

Good:

```php
$isApproved = $sheet->status === 'APPROVED';

if ($isApproved) {
    ...
}
```

---

## 5. Fail Clearly

Do not silently ignore important errors.

Bad:

```php
try {
    ...
} catch (Exception $e) {
}
```

Good:

```php
try {
    ...
} catch (Throwable $e) {
    logger()->error($e->getMessage());

    throw $e;
}
```

---

## 6. Validate at Boundaries

Validate external input before using it.

External input includes:

- POST
- GET
- JSON
- Uploaded files
- Cookies
- Headers

---

## 7. Never Trust the Client

The frontend can be manipulated.

Never assume:

```text
User is Admin
Grade is valid
Student owns this record
Sheet is approved
```

Verify everything on the server.

---

## 8. Secure by Default

Use:

- Prepared statements
- Password hashing
- CSRF protection
- Output escaping
- Session protection
- Authorization checks

---

## 9. Avoid Magic Values

Bad:

```php
if ($status === 5) {
}
```

Good:

```php
if ($status === GradingSheetStatus::APPROVED) {
}
```

---

## 10. Prefer Constants for Fixed Values

Example:

```php
final class Roles
{
    public const ADMIN = 'ADMIN';
    public const DEAN = 'DEAN';
    public const FACULTY = 'FACULTY';
    public const STUDENT = 'STUDENT';
}
```

---

## 11. Keep Functions Small

A function should normally perform one logical operation.

Avoid functions with hundreds of lines.

---

## 12. Avoid Hidden Side Effects

Functions should clearly communicate what they change.

Bad:

```php
calculateGrade();
```

if the function also:

```text
updates database
sends email
creates notification
```

Separate these operations.

---

## 13. Use Dependency Injection

Prefer:

```php
public function __construct(
    GradeRepository $gradeRepository
) {
    $this->gradeRepository = $gradeRepository;
}
```

instead of creating dependencies inside methods.

---

## 14. Comments

Comments should explain:

```text
WHY
```

not:

```text
WHAT
```

Bad:

```php
// Increment i
$i++;
```

Good:

```php
// Skip archived grading periods because they are no longer editable.
```

---

## 15. No Dead Code

Remove:

- Unused functions
- Unused variables
- Commented-out old implementations
- Unused imports
- Debug statements

---

## 16. No Debug Code in Production

Never leave:

```php
var_dump();
print_r();
dd();
die();
```

in production code.
