# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Acadtrack (Golden West Colleges): a server-rendered academic grading and curriculum evaluation system. Vanilla PHP 8.2 (no framework), Eloquent via `illuminate/database` Capsule, MySQL/MariaDB, PHPMailer, Bootstrap 5 (vendored in `public/assets/vendor`). Roles: Admin, Dean, Faculty, Student.

## Commands

```bash
composer install
mysql -u root acadtrack < database/sql/schema.sql   # initial schema (XAMPP; config in .env, see .env.example)
php bin/migrate.php                                 # apply database/migrations/*.php manually
.\vendor\bin\phpunit                                # full suite (Unit + Feature)
.\vendor\bin\phpunit tests/Feature/GradingSystemWorkflowTest.php
.\vendor\bin\phpunit --filter testName
```

App runs under Apache at `http://localhost/acadtrack/`. The root `index.php` just requires `public/index.php`; `.htaccess` gives clean URLs, and `Application::run` 301-redirects any `/public/` URL to the clean one. There is no linter or build step (CSS/JS are plain files under `public/assets`).

Tests hit a **real MySQL database** from `.env` (no mocking): `tests/bootstrap.php` connects and calls `Tests\TestDatabaseSeeder::seedIfNeeded()`, and defines `PHPUNIT_RUNNING`. XAMPP MySQL must be running. CI (`.github/workflows/deploy.yml`) runs tests against a MySQL service, then deploys to InfinityFree on pushes to `main`.

## Architecture

Request flow: `public/index.php` → `bootstrap/app.php` (dotenv, `session_start`, `Core\Database` boots Eloquent Capsule, `AutoMigrationService::runIfNeeded()`, loads `routes/web.php` and `routes/api.php`) → `Core\Application::run` → `Core\Router::resolve`.

- **Routing**: `routes/web.php` registers `$router->get/post(path, [Controller::class, 'method'], [middleware instances])`. Paths use `{id}` params. There is also `$router->group(prefix, middleware, callback)`, but most routes spell out `new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()` per route. All state-changing POSTs need `CsrfMiddleware`.
- **Layering**: Controllers (grouped by role: `Admin/`, `Dean/`, `Faculty/`, `Student/`) → Services (business rules: grading, ranking, term closure, evaluation, email, notifications) / Repositories (queries) → Eloquent Models. Validators in `app/Validators`. Views are plain PHP in `app/Views/{role}`, with shared pieces in `Views/components` and `Views/layouts`. `app/Core` holds the hand-rolled framework (Router, Request, Response, Session, View, Database, plus a legacy Model/QueryBuilder). Global helpers (`env`, auth, redirect, response, csrf) are autoloaded from `app/Helpers` via composer `files`.
- **Migrations are automatic**: `AutoMigrationService` runs on every boot, but bails out fast by comparing a hash of migration filenames to `storage/cache/migrations_fingerprint.txt`. Migration files in `database/migrations/` are dated (`2026_09_25_*.php`) and return an object with `up()`. Adding a new file triggers it on the next request. If a migration seems skipped, delete the fingerprint file.
- **Error handling**: `Application::handleException` maps PDO errors (duplicate entry, FK violations) to friendly messages. On POST/PUT/DELETE it flashes the message and redirects to the referer. On GET it renders `Views/errors/error.php`. Raw traces show only when `APP_DEBUG=true`. Controllers also catch specific exceptions and flash their own messages (`components/alert.php`).
- **Deletion model**: records are archived/restored rather than hard-deleted (see `docs/DELETION_AND_ARCHIVING_ARCHITECTURE.md`; Admin `ArchiveController`).
- **Domain flow**: Dean assigns subjects to faculty → Faculty sets up subject, enrolls students, encodes grades per grading period → submits grading sheet → Dean reviews/approves → grades finalized, students notified by email and view evaluation. Admin manages users, departments, programs/curricula, sets, academic terms and term closure (locks terms and periods). See `systemflow.md` and `docs/WORKFLOW.md`.
- **Auth**: new accounts get generated temporary passwords and are forced through `/change-password` on first login. Public QR verification lives in `PublicVerificationController`.

## Conventions

Detailed standards live in `docs/` (`PHP_STANDARDS.md`, `NAMING_CONVENTIONS.md`, `MODULE_ORGANIZATION.md`, `CODING_PRINCIPLES.md`, `JAVASCRIPT_PRINCIPLES.md`, `WEB_DESIGN_GUIDE.md`, `BOOTSTRAP_GUIDE.md`). Files use `declare(strict_types=1)` and PSR-4 `App\` → `app/`. UI text follows `CASING_GUIDELINES.md`: Title Case for page titles, nav, modal titles, tabs and section headings; Sentence case for buttons, form labels, table headers and helper text; UPPERCASE only for acronyms. Never recase backend vocabulary or error messages. Icons are always flat: never put them on a colored/tinted background tile or circle (no `bg-*-subtle`, pastel hex backplates); use the bare icon with a semantic text color (see `docs/ANTI_GENERIC_DESIGN.md`). Modals are centralized in `public/assets/css/components/modal.css`: views use bare `modal-content / modal-header / modal-title / modal-body / modal-footer` (plus `modal-lg`, `modal-dialog-centered`, `justify-content-between` on a footer when needed) and never add per-modal padding, border, background, radius, shadow, font-weight utilities or inline styles. Page-specific JS/CSS goes in `public/assets/js/pages` and `public/assets/css/components|layouts`.
