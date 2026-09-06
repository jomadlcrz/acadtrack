# GWC Grading System

A lightweight, server-rendered academic grading and evaluation system built with Vanilla PHP 8.2, MySQL/MariaDB, and Composer.

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
   DB_DATABASE=grading_system
   DB_USERNAME=root
   DB_PASSWORD=
   APP_ENV=development
   APP_DEBUG=true
   ```
3. Initialize the database schema:
   ```bash
   mysql -u root grading_system < database/sql/schema.sql
   ```

### Accessing the Web Application
Open your browser at:
**[http://localhost/grading-system/public/login](http://localhost/grading-system/public/login)**

---

## 2. User Accounts

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | System Admin | `admin@gwc.edu` | `admin123` |
| **Dean** | College Dean | `dean@gwc.edu` | `dean123` |
| **Faculty** | John Teacher | `faculty@gwc.edu` | `faculty123` |
| **Faculty** | Sarah Connor | `sarah.connor@gwc.edu` | `faculty123` |
| **Student** | Jane Student | `student@gwc.edu` | `student123` |
| **Student** | Mark Reyes | `mark.reyes@gwc.edu` | `student123` |
| **Student** | Anna Gomez | `anna.gomez@gwc.edu` | `student123` |

---

## 3. Error Handling & UI Notification Guide

### User-Friendly UI Instead of Fatal Errors
In standard PHP/PDO applications, a unique constraint conflict (e.g., adding an already existing subject code like `CS102` for an academic term) triggers an uncaught fatal crash:

```text
Fatal error: Uncaught PDOException: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'CS102-1' for key 'unique_subject' in Model.php...
```

The GWC Grading System implements a two-tier error handling architecture to ensure that raw exceptions are never presented to end-users:

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

.................                                                 17 / 17 (100%)

Time: 00:00.161, Memory: 8.00 MB

OK (17 tests, 34 assertions)
```

---

## 5. Architectural Principles

Detailed architecture and design rules can be found under `docs/`:
* [docs/ARCHITECTURE.md](file:///C:/xampp/htdocs/grading-system/docs/ARCHITECTURE.md) - System architecture, routing, and error design
* [docs/DESIGN_PRINCIPLES.md](file:///C:/xampp/htdocs/grading-system/docs/DESIGN_PRINCIPLES.md) - UI design, alert design, and error messaging standards
* [docs/CODING_PRINCIPLES.md](file:///C:/xampp/htdocs/grading-system/docs/CODING_PRINCIPLES.md) - Clean code and strict typing guidelines