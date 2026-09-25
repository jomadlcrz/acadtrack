# Acadtrack Docker Setup & Operations Guide

This document provides complete instructions for starting, operating, and developing **Acadtrack** using Docker and Docker Compose.

---

## 1. Prerequisites

Before running the containers, ensure you have:
1. **Docker Desktop** installed and running on Windows with the **WSL 2** backend.
2. A terminal (PowerShell, Command Prompt, or VS Code integrated terminal) opened in the project root directory:
   ```powershell
   cd C:\xampp\htdocs\acadtrack
   ```

---

## 2. Quick Start

### Start all services in the background:
```powershell
docker compose up -d
```

### Stop all services (preserves database data):
```powershell
docker compose stop
```

### Resume stopped services:
```powershell
docker compose start
```

### Tear down containers and networks:
```powershell
docker compose down
```

### Rebuild containers after modifying `Dockerfile` or dependencies:
```powershell
docker compose up -d --build
```

---

## 3. Services & Port Mappings

| Service | Container Name | Technology | Port (Host:Container) | URL / Access Point |
| :--- | :--- | :--- | :--- | :--- |
| **Web Application** | `acadtrack-app` | PHP 8.2 + Apache | `8080:80` | [http://localhost:8080](http://localhost:8080) |
| **Database** | `acadtrack-db` | MySQL 8.0 | `3306:3306` | `localhost:3306` |
| **Database GUI** | `acadtrack-pma` | phpMyAdmin | `8081:80` | [http://localhost:8081](http://localhost:8081) |

> [!NOTE]
> All code changes made locally in your IDE/editor are live-mounted into `/var/www/html` inside `acadtrack-app`. Changes reflect instantly without needing a rebuild or restart.

---

## 4. Default Seeded User Accounts

The database is pre-seeded with baseline institutional roles and credentials:

| Role | Email | Temporary Password | Details |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@gwc.edu` | `GWC_acadtrack@2026` | Full administrative & institutional control |
| **College Dean** | `dean@gwc.edu` | `dean123` | CITE Dean: curriculum & grade verification |
| **Faculty Member** | `faculty@gwc.edu` | `faculty123` | Instructor: grade encoding & attendance |
| **Student** | `student@gwc.edu` | `student123` | Juan Dela Cruz (`2026-0001`), Regular BSIT |

---

## 5. Database Credentials

### Application Database Connection (`.env` / Docker environment):
- **Host**: `db` (inside Docker network) or `127.0.0.1` (from host machine)
- **Port**: `3306`
- **Database**: `acadtrack`
- **Username**: `acadtrack`
- **Password**: `secret`
- **Root Password**: `root`

### phpMyAdmin Access:
1. Open [http://localhost:8081](http://localhost:8081) in your browser.
2. Enter:
   - **Server**: `db`
   - **Username**: `acadtrack` (or `root`)
   - **Password**: `secret` (or `root`)

---

## 6. Developer Commands

All PHP, Composer, and database operations can be run directly inside the containers without requiring PHP or MySQL to be installed on your Windows host:

### Run PHPUnit Test Suite:
```powershell
docker compose exec app vendor/bin/phpunit --testdox
```

### Run Composer Commands:
```powershell
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer dump-autoload
```

### Access Container Shell (Bash):
```powershell
docker compose exec app bash
```

### Access MySQL Interactive Shell:
```powershell
docker compose exec db mysql -uacadtrack -psecret acadtrack
```

### View Live Container Logs:
```powershell
# Follow all container logs
docker compose logs -f

# Follow web application logs only
docker compose logs -f app

# Follow database logs only
docker compose logs -f db
```

---

## 7. Database Reset / Re-import

If you ever need to completely wipe and recreate the database with the initial schema and seeds:

```powershell
# 1. Stop and remove containers and database volumes
docker compose down -v

# 2. Re-launch containers (schema.sql is re-executed automatically on startup)
docker compose up -d
```

---

## 8. Customizing Ports (Optional)

If port `8080`, `3306`, or `8081` conflicts with existing host services (such as XAMPP Apache or MySQL), you can customize the ports in your `.env` file:

```env
APP_PORT=8090
DB_FORWARD_PORT=3307
PMA_PORT=8091
```

Then restart containers:
```powershell
docker compose up -d
```
