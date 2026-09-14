# Acadtrack

A lightweight, server-rendered academic grading and evaluation system built with Vanilla PHP 8.2, Eloquent ORM (`illuminate/database` Capsule), MySQL/MariaDB, and Composer.

---

## 1. Quick Start & Setup

### Prerequisites
* **XAMPP** (or standalone Apache 2.4+ and MySQL 10.4+)
* **PHP 8.2+** with `pdo_mysql`, `mbstring`, and `openssl` enabled
* **Composer**

### Configuration
1. Ensure Apache and MySQL are running in XAMPP.
2. Verify `.env` database credentials:
   ```env
   DB_HOST=127.0.0.1
   DB_DATABASE=acadtrack
   DB_USERNAME=root
   DB_PASSWORD=
   APP_ENV=development
   APP_DEBUG=true
   ```
3. Initialize the database schema:
   ```bash
   mysql -u root acadtrack < database/sql/schema.sql
   ```

### Accessing the Web Application
Open your browser at:
**[http://localhost/grading-system/login](http://localhost/grading-system/login)** (or simply **[http://localhost/grading-system/](http://localhost/grading-system/)**)

---

## 2. User Accounts

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | System Admin | `admin@gwc.edu` | `GWC_acadtrack@2026` |
| **Dean** | College Dean | `dean@gwc.edu` | `dean123` |
| **Faculty** | John Teacher | `faculty@gwc.edu` | `faculty123` |
| **Student** | Enrolled Students | *(Provisioned email)* | *Auto-generated temporary password (sent via email; forced change upon login)* |

---

## 3. Error Handling & UI Notification Guide

### User-Friendly UI Instead of Fatal Errors
In standard PHP/PDO applications, a unique constraint conflict (e.g., adding an already existing subject code like `CS102` for an academic term) triggers an uncaught fatal crash:

```text
Fatal error: Uncaught PDOException: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'CS102-1' for key 'unique_subject' in Model.php...
```

The GWC Acadtrack implements a two-tier error handling architecture to ensure that raw exceptions are never presented to end-users:

### 1. Specific Controller-Level Handling & Flash Alerts
Controllers capture database exceptions on state-mutating actions (such as subject creation, user registration, and faculty assignment):
* **Duplicate Subject:** When Dean tries to create an existing subject code (e.g., `CS102`), the system intercepts `1062 Duplicate entry` and flashes:
  > **`Subject code 'CS102' already exists for this academic term.`**
* **Duplicate Email:** Intercepts duplicate user accounts and flashes:
  > **`An account with this email address already exists.`**
* **Duplicate Assignment:** Intercepts redundant faculty assignments and flashes:
  > **`This faculty member is already assigned to this subject for this term.`**
* **Foreign Key Dependencies:** Intercepts deletion of subjects with enrolled students or assigned faculty:
  > **`Cannot delete subject because it has linked faculty assignments or enrollments.`**

The user is automatically redirected back to their current form with the input preserved, and the dismissible alert banner is displayed via `app/Views/components/alert.php`.

### 2. Global Exception Interceptor (`Application::run`)
For any unhandled exception across the application:
* In `POST`/`PUT`/`DELETE` requests: Automatically flashes a human-readable summary and safely redirects back to `HTTP_REFERER`.
* In `GET` requests: Renders a dedicated error page (`app/Views/errors/error.php`) styled with the application design system, "Go Back", and "Return to Dashboard" actions.
* When `APP_DEBUG=true`: Technical trace details are contained inside an expandable debug container.

---

## 4. Running Automated Tests

Run the full PHPUnit unit and feature test suite from the repository root:

```bash
.\vendor\bin\phpunit
```

```text
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.

.............................                                     29 / 29 (100%)

Time: 00:00.715, Memory: 16.00 MB

OK (29 tests, 100 assertions)
```

---

## 5. System Features & Workflow Architecture

The application strictly implements the workflow specified in `grading_system_workflow_text_based.pdf`:

```text
1. Login  ──>  2. Dean Assigns Subjects  ──>  3. Faculty Sets Up Subject  ──>  4. Faculty Adds/Selects Students
                                                                                         │
8. Submit Grading Sheet  <──  7. Save and Review  <──  6. Faculty Enters Grades  <──  5. Select Semester
         │
         ▼
9. Dean/Admin Review  ──>  10. Grade Finalization  ──>  11. Email Notification  ──>  12. Student Views Evaluation
```

- **Features & Workflow:** [grading_system_workflow.md](file:///C:/xampp/htdocs/acadtrack/grading_system_workflow.md) | [docs/WORKFLOW.md](file:///C:/xampp/htdocs/acadtrack/docs/WORKFLOW.md)
- **Bootstrap 5 & Forms Guide:** [docs/BOOTSTRAP_GUIDE.md](file:///C:/xampp/htdocs/acadtrack/docs/BOOTSTRAP_GUIDE.md)
- **Casing & Typography Standards:** [CASING_GUIDELINES.md](file:///C:/xampp/htdocs/acadtrack/CASING_GUIDELINES.md)
- **Web Design & UI/UX Architecture:** [docs/WEB_DESIGN_GUIDE.md](file:///C:/xampp/htdocs/acadtrack/docs/WEB_DESIGN_GUIDE.md)
- **Anti-Generic Design Standards:** [docs/ANTI_GENERIC_DESIGN.md](file:///C:/xampp/htdocs/acadtrack/docs/ANTI_GENERIC_DESIGN.md)
- **System Architecture & Error Handling:** [docs/ARCHITECTURE.md](file:///C:/xampp/htdocs/acadtrack/docs/ARCHITECTURE.md)
- **Design Principles:** [docs/DESIGN_PRINCIPLES.md](file:///C:/xampp/htdocs/acadtrack/docs/DESIGN_PRINCIPLES.md)
- **Coding Principles:** [docs/CODING_PRINCIPLES.md](file:///C:/xampp/htdocs/acadtrack/docs/CODING_PRINCIPLES.md)