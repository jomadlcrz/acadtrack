# Function Design

## 1. Purpose

Functions should be:

- Small
- Focused
- Predictable
- Testable
- Explicit

---

## 2. One Responsibility

Bad:

```php
function processStudent()
{
    validateStudent();
    saveStudent();
    sendEmail();
    createNotification();
    generateReport();
}
```

Good:

```php
validateStudent();

$studentId = $studentRepository->create($data);

$emailService->sendCredentials($student);

$notificationService->create(...);
```

---

## 3. Naming

Function names should describe actions.

Good:

```text
findStudent()
createStudent()
updateStudent()
deleteStudent()
calculateGrade()
submitGradingSheet()
approveGradingSheet()
sendNotification()
```

Avoid:

```text
doIt()
process()
handle()
run()
execute()
```

unless the context makes the purpose clear.

---

## 4. Parameters

Prefer a small number of parameters.

Bad:

```php
createStudent(
    $firstName,
    $lastName,
    $email,
    $status,
    $yearLevel,
    $course,
    $section,
    $address
);
```

Prefer:

```php
createStudent(StudentData $student);
```

or:

```php
createStudent(array $data);
```

when appropriate.

---

## 5. Return Values

Functions should have predictable return values.

Example:

```php
public function findById(int $id): ?Student
```

means:

```text
Student found → Student
Student not found → null
```

---

## 6. Boolean Functions

Boolean functions should read naturally.

Good:

```text
isAuthenticated()
isApproved()
hasPermission()
canEditGrade()
isFinalized()
```

---

## 7. Avoid Boolean Parameters

Avoid:

```php
saveGrade($grade, true, false);
```

because the meaning is unclear.

Prefer:

```php
saveDraftGrade($grade);
```

or:

```php
submitGrade($grade);
```

---

## 8. Guard Clauses

Prefer early validation.

Good:

```php
if (!$student) {
    throw new StudentNotFoundException();
}

if (!$this->canEdit($student)) {
    throw new AuthorizationException();
}

return $this->save($student);
```

instead of deeply nested conditions.

---

## 9. Functions Should Not Know Too Much

A function should depend only on the information it actually needs.

---

## 10. Pure Calculations

Grade calculations should preferably be isolated from database operations.

Example:

```php
public function calculateFinalGrade(
    array $grades,
    array $weights
): float
```

This makes grading calculations easy to test.

---

## 11. Side Effects

Database writes, emails, notifications, and file creation are side effects.

Keep them explicit.

---

## 12. Exceptions

Use exceptions for exceptional conditions.

Example:

```php
if ($gradingSheet->status !== 'SUBMITTED') {
    throw new InvalidGradingSheetStateException();
}
```

---

## 13. Function Length

As a guideline:

- Prefer under 30 lines
- Review functions above 50 lines
- Split functions above 100 lines unless there is a strong reason

The goal is readability, not an arbitrary line limit.
