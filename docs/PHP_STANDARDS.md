# PHP Standards

## 1. PHP Version

Target:

```text
PHP 8.2+
```

Use strict typing.

Every PHP source file should begin with:

```php
<?php

declare(strict_types=1);
```

---

## 2. PSR-4 Autoloading

Use Composer PSR-4 autoloading.

Example:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

After changing autoload configuration:

```bash
composer dump-autoload
```

---

## 3. Namespaces

Classes must use namespaces.

Example:

```php
namespace App\Services;

class GradingService
{
}
```

---

## 4. Type Declarations

Use parameter and return types.

Good:

```php
public function findById(int $id): ?Student
{
}
```

Avoid:

```php
public function findById($id)
{
}
```

---

## 5. Visibility

Always explicitly declare visibility.

Use:

```php
public
protected
private
```

Do not rely on implicit visibility.

---

## 6. Classes

Classes should have a clear responsibility.

Example:

```php
final class GradeCalculator
{
}
```

Use `final` when inheritance is not intentionally supported.

---

## 7. Constructor Property Promotion

PHP 8+ property promotion is encouraged.

```php
public function __construct(
    private GradeRepository $gradeRepository
) {
}
```

---

## 8. Database

Use PDO.

Always use prepared statements.

```php
$stmt = $pdo->prepare(
    'SELECT * FROM grades WHERE student_id = :student_id'
);

$stmt->execute([
    'student_id' => $studentId,
]);
```

---

## 9. SQL

SQL keywords should be uppercase.

Good:

```sql
SELECT id, name
FROM students
WHERE id = :id
ORDER BY last_name ASC
```

---

## 10. Passwords

Never store plaintext passwords.

Use:

```php
password_hash($password, PASSWORD_DEFAULT);
```

Verify:

```php
password_verify($password, $hash);
```

---

## 11. Output Escaping

Escape HTML output.

Use:

```php
htmlspecialchars(
    $value,
    ENT_QUOTES,
    'UTF-8'
);
```

---

## 12. Sessions

Sessions must be initialized securely.

Production should use:

```text
HttpOnly
Secure
SameSite
```

where appropriate.

---

## 13. Exceptions

Use meaningful exceptions.

Examples:

```text
AuthenticationException
AuthorizationException
ValidationException
StudentNotFoundException
InvalidGradingSheetStateException
```

---

## 14. Formatting

Use PSR-12-compatible formatting.

Recommended tooling:

```text
PHP_CodeSniffer
PHP-CS-Fixer
PHPStan
PHPUnit
```

---

## 15. Imports

Imports should appear at the top of the file.

Do not use unnecessary fully qualified class names throughout the code.

---

## 16. Arrays

Use short array syntax:

```php
$data = [
    'name' => 'John',
];
```

---

## 17. Comparisons

Prefer strict comparison:

```php
$value === $expected
```

instead of:

```php
$value == $expected
```

---

## 18. Null Checks

Use explicit checks when appropriate.

```php
if ($student === null) {
    ...
}
```

---

## 19. Environment Variables

Never hardcode:

- Database passwords
- SMTP passwords
- API keys
- Application secrets

Use `.env`.

---

## 20. Composer

Never commit:

```text
vendor/
```

The project should be installable with:

```bash
composer install
```

`composer.lock` should be committed for applications.
