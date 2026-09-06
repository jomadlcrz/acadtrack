# Naming Conventions

## 1. General Rule

Names should describe the purpose of the item.

Prefer:

```text
gradingSheet
studentId
academicTerm
```

Avoid:

```text
x
data1
thing
temp
foo
```

---

## 2. PHP Classes

Use:

```text
PascalCase
```

Examples:

```text
AuthController
GradingService
GradeRepository
StudentValidator
EmailService
```

---

## 3. PHP Methods

Use:

```text
camelCase
```

Examples:

```text
findStudent()
createStudent()
updateGrade()
submitGradingSheet()
approveGradingSheet()
```

---

## 4. PHP Variables

Use:

```text
camelCase
```

Examples:

```php
$studentId;
$gradingSheet;
$academicTerm;
$finalGrade;
```

---

## 5. Constants

Use:

```text
UPPER_SNAKE_CASE
```

Example:

```php
public const MAX_GRADE = 100;
```

---

## 6. Database Tables

Use:

```text
snake_case
```

Examples:

```text
users
students
faculty_subjects
academic_terms
grading_sheets
grading_periods
approval_logs
```

---

## 7. Database Columns

Use:

```text
snake_case
```

Examples:

```text
student_id
academic_term_id
grading_sheet_id
created_at
updated_at
submitted_at
approved_at
```

---

## 8. Foreign Keys

Use:

```text
<entity>_id
```

Examples:

```text
student_id
faculty_id
subject_id
user_id
grading_sheet_id
```

---

## 9. JavaScript

Variables/functions:

```text
camelCase
```

Examples:

```js
const studentId = 10;

function saveGrades() {}
```

Classes:

```text
PascalCase
```

---

## 10. CSS

Use:

```text
kebab-case
```

Examples:

```text
grading-table
student-card
submit-button
modal-overlay
```

---

## 11. Routes

Use lowercase kebab-case.

Good:

```text
/faculty/grading
/faculty/grade-submissions
/dean/grade-review
/student/whole-evaluation
```

Avoid:

```text
/GetGrades
/get_grades
/Get_Student_Grades
```

---

## 12. Files

PHP class filenames must match the class name.

```text
GradingService.php
GradeRepository.php
StudentController.php
```

Views may use lowercase kebab-case:

```text
grade-review.php
whole-evaluation.php
```

---

## 13. Boolean Names

Boolean variables should communicate true/false meaning.

Good:

```text
$isAuthenticated
$isApproved
$canEdit
$hasGrades
$canSubmit
```

Avoid:

```text
$status
$flag
$check
```

when the value is boolean.

---

## 14. IDs

Use descriptive IDs.

Good:

```text
$studentId
$facultyId
$subjectId
$gradingSheetId
$academicTermId
```

Avoid:

```text
$id1
$id2
$id3
```

---

## 15. Status Values

Use consistent uppercase database values when represented as enums/strings:

```text
DRAFT
SUBMITTED
UNDER_REVIEW
RETURNED
APPROVED
FINALIZED
```

Roles:

```text
ADMIN
DEAN
FACULTY
STUDENT
```

---

## 16. Avoid Abbreviations

Prefer:

```text
$studentNumber
```

over:

```text
$studNo
```

Prefer:

```text
$academicTerm
```

over:

```text
$acadTerm
```

Common technical abbreviations such as:

```text
ID
URL
HTTP
API
SQL
```

are acceptable.
