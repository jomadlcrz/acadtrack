# AGENTS.md

Acadtrack (Golden West Colleges, Inc.) — server-rendered academic grading/evaluation system. Vanilla PHP 8.2 (no framework), Eloquent via `illuminate/database` Capsule, MySQL, PHPMailer, Bootstrap 5 vendored in `public/assets/vendor`. Roles: Admin, Dean, Faculty, Student.

`CLAUDE.md` holds the architecture map and is accurate except where noted below. Detailed standards live in `docs/` and `.agents/skills/SKILL.md`. This file only records what is easy to get wrong.

## Commands

```powershell
composer install
.\vendor\bin\phpunit                                  # full suite (Unit + Feature)
.\vendor\bin\phpunit --testsuite Unit                 # fast subset, no workflow DB churn
.\vendor\bin\phpunit tests/Feature\GradingSystemWorkflowTest.php
.\vendor\bin\phpunit --filter testName
docker compose up -d                                  # alt env: app :8080, db :3306, pma :8081
```

There is **no linter, formatter, or typecheck** and no build step — CSS/JS are plain files under `public/assets`. Nothing to run before tests.

Full suite is 162 tests / ~35s and **must pass before you call work done** (CI runs it on every push to `main`).

## Migrations — the main trap

Migrations run **automatically on every request** (`AutoMigrationService::runIfNeeded()` in `bootstrap/app.php`), tracked in a `migrations` table keyed by filename.

- Adding a file in `database/migrations/` is enough; you do not need to run anything locally.
- **The fingerprint cache is `storage/cache/migrations_fingerprint.txt`** (git-ignored). It short-circuits the check on filenames only. If a migration appears to be "skipped", delete this file.
- **`php bin/migrate.php` is wrong/dangerous** — it re-runs *every* migration unconditionally with no `migrations` table check, so most `up()` methods will fail on re-application. `CLAUDE.md` still recommends it; that line is stale.
- Use `php database/migrate.php` instead. It tracks applied migrations, supports `php database/migrate.php status`, and refreshes the fingerprint cache. This is also the only runner that reports failures loudly.
- A migration whose `up()` throws is still recorded as applied (`AutoMigrationService.php:93-100`) to avoid infinite retry loops. A silently-skipped migration means a swallowed error in `storage/logs` or the PHP error log — check there rather than assuming it ran.
- `database/sql/schema.sql` is the bootstrap schema for fresh DBs **and** the CI test DB. New tables/columns need to land there too, or CI will fail on a clean database.

## Tests hit the real database

`tests/bootstrap.php` loads `.env`, connects to the live MySQL instance, defines `PHPUNIT_RUNNING`, and runs `TestDatabaseSeeder::seedIfNeeded()`. **There is no mocking and no separate test DB — your development data is the fixture.** Tests insert rows; only `audit_logs` rows created during a run are cleaned up on shutdown.

- XAMPP/standalone MySQL must be running before PHPUnit or the whole suite errors out.
- `TestDatabaseSeeder` seeds fixed IDs (users 1-4, terms 1-2, department 1, program 1, `student_number` `2026-0001`) and only fills gaps — a polluted local DB produces confusing failures.
- `Email failed: SMTP Error: Could not authenticate.` on stderr is **expected noise**, not a failure. `EmailService` catches and `error_log`s; tests still pass.
- `PHPUNIT_RUNNING` makes `Response` and `redirect()` skip real `header()`/`exit`, which is what lets tests invoke controller actions directly. Don't remove those guards.
- `TestDatabaseSeeder` is also where "fresh vs legacy test DB" fallbacks live (it re-runs the `2026_10_04` migration and hand-creates `audit_logs` / `student_term_registrations`). Changing a table shape means updating it too.

## Routing and authorization

- `routes/web.php` spells out middleware per route: `new AuthMiddleware(), new RoleMiddleware(['Admin']), new CsrfMiddleware()`. **Every state-changing POST needs `CsrfMiddleware`** — its absence is a real vulnerability, not a style issue.
- Role strings are **Title Case**: `'Admin'`, `'Dean'`, `'Faculty'`, `'Student'`. `.agents/skills/SKILL.md` §4 lists them UPPERCASE; the code and every route use Title Case. Trust the code.
- Route order matters — literal paths are registered before `{id}` params (e.g. `/admin/sets` before `/admin/sets/{id}`). Match the existing ordering when adding routes.
- `Router::group()` exists but has zero call sites; keep adding the explicit per-route form.
- `/change-password` is a forced gate for accounts created with a generated temporary password (`is_temp_password`). Auth middleware must keep honoring it.

## Views and assets

- Views are plain PHP in `app/Views/{admin,dean,faculty,student,...}`; `View::render('dean.grade-review.show')` maps dots to slashes. Layouts in `app/Views/layouts` (`main`, `dashboard`, `auth`).
- `View::render` post-processes HTML to prepend the base path to root-relative `href`/`action`/`src`. Root-relative URLs are fine; don't fight this.
- Modals are centralized in `public/assets/css/components/modal.css`. Use bare `modal-content / modal-header / modal-title / modal-body / modal-footer` and **never** add per-modal padding/background/radius/shadow/font-weight or inline styles.
- Icons must be flat: bare icon in a semantic text color. No `bg-*-subtle` tiles, no colored circles behind icons (`docs/ANTI_GENERIC_DESIGN.md`).
- UI casing: Title Case for page titles, nav, modal titles, tabs, section headings; Sentence case for buttons, labels, table headers, helper text (`CASING_GUIDELINES.md` — duplicated at `docs/CASING_GUIDELINES.md`, identical). Never recase backend vocabulary or error messages.
- Page-specific JS/CSS belongs in `public/assets/js/pages` and `public/assets/css/components|layouts`. No inline `onclick=`; bind with `addEventListener`.
- Vanilla JS only. Adding React/Vue/CDN frameworks is out of scope without explicit approval.

## Domain rules that bite

- Grading sheet state machine: `DRAFT → SUBMITTED → UNDER_REVIEW → APPROVED → FINALIZED`, with `UNDER_REVIEW → RETURNED → DRAFT`. Transitions are validated server-side; a POST must never be able to set `status=APPROVED` directly.
- Grade computation lives in `app/Helpers/grading.php` and the grading services — never re-derive weights or zero/50-based conversion inside a controller or view.
- Records are archived/restored, not hard-deleted (`docs/DELETION_AND_ARCHIVING_ARCHITECTURE.md`, Admin `ArchiveController`). Don't add `DELETE` endpoints.
- Layering is `Router → Middleware → Controller → Service → Repository → Model`; no SQL in views, no business logic in controllers, no UI concerns in repositories.

## Error handling

`Application::handleException` translates PDO duplicate/FK errors into flashed messages — on POST/PUT/DELETE it flashes and redirects to `HTTP_REFERER`, on GET it renders `app/Views/errors/error.php`. Traces only when `APP_DEBUG=true` or `APP_ENV=development`. Controllers add their own `catch` + flash for known cases. Prefer catching the specific exception and flashing a precise message over relying on the generic fallback.

## Env and deploy

- `.env` is git-ignored; `.env.example` is the template. `bootstrap/app.php` hard-requires `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `APP_URL`.
- `docker-compose.yml` contains a **committed plaintext SMTP app password**. Never copy that value into docs, commits, or new files.
- CI (`.github/workflows/deploy.yml`) on push to `main`: spin up MySQL 8, `cp .env.example .env`, `composer install`, import `database/sql/schema.sql`, run PHPUnit, then `composer install --no-dev` and FTP-deploy to InfinityFree. It then hits the live site with headless Chromium (`scripts/trigger-deploy-migration.js`) so `AutoMigrationService` applies pending migrations there. Tests and `docs/` are excluded from the upload.
- Deploy is FTP + `--no-dev`, so anything not committed or in `composer.json` `require` will not exist in production.

## Repo traps

- **`.kilo/worktrees/plume-kip/` is a full duplicate checkout of this repo.** It is untracked (via `.git/info/exclude`, not `.gitignore`). Glob, grep, and file-tree searches will return doubled results — always scope searches to `app/`, `tests/`, `routes/`, `public/`, or exclude `.kilo`. Never edit files inside it.
- `composer.lock` is in `.gitignore` even though it exists locally; don't assume dependency versions are pinned in git.
- `.phpunit.result.cache` is committed-adjacent noise; ignore it.
- Commit messages loosely follow Conventional Commits (`feat:`, `fix:`, `refactor:`, `docs:`) but the history is mixed — match the surrounding style.