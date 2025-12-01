<div align="center">

# THODZ – Social Media Website

Comprehensive PHP/MySQL social platform with posts, chat, followers, profile customization, and Dockerized local environment.

</div>

## Table of Contents

1. [Features](#features)
2. [Tech Stack](#tech-stack)
3. [Project Structure](#project-structure)
4. [Quick Start (Docker)](#quick-start-docker)
5. [Manual Setup](#manual-setup)
6. [Environment Variables](#environment-variables)
7. [Database Import](#database-import)
8. [Development Notes](#development-notes)

## Features

- User authentication, verification emails, and password recovery
- Rich profile management (avatars, cover images, followers/following)
- Post creation with secure media uploads and threaded comments
- Real-time style chat with rate limiting, image/audio attachments, and unread tracking
- Responsive UI variants (legacy and *_new.php entry points) for iterative redesign
- Docker Compose for reproducible dev stack (PHP 8.2 + MariaDB 10.4)

## Tech Stack

| Layer        | Technology                               |
|--------------|-------------------------------------------|
| Backend      | PHP 8.2, PDO, custom MVC-style controllers|
| Database     | MariaDB/MySQL                             |
| Frontend     | Vanilla JS, CSS (Styles/), HTML templates |
| Email        | Gmail SMTP via PHPMailer                  |
| Container    | Docker, docker-compose                    |

## Project Structure

```
├── api/                 # AJAX endpoints (signup, login, posts, media)
├── controler/           # Business logic controllers
├── models/              # Database layer (User, Post, Chat, Security, etc.)
├── templates/, Styles/  # Layout fragments and styling assets
├── uploads/             # User-generated media (ignored in VCS)
├── thodz.sql            # Seed database schema & data
├── docker-compose.yml   # Web + DB services for local dev
├── Dockerfile           # PHP-Apache image with GD + PDO
├── config.php           # Runtime configuration defaults
└── .env                 # Private environment overrides (not committed)
```

## Quick Start (Docker)

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/).
2. Copy `.env.example` if provided or create `.env` (see [Environment Variables](#environment-variables)).
3. Run `docker-compose up --build` from the repo root.
4. Access the app at [http://localhost:8080](http://localhost:8080).

Containers:

- `web`: PHP 8.2 Apache server with hot reloading via bind mount.
- `db`: MariaDB initialized with `thodz.sql` and persisted via named volume.

## Manual Setup

1. Install PHP ≥8.1, Composer (optional), and MySQL ≥5.7.
2. Create a database named `THODZ` (or update `config.php` & `.env`).
3. Import `thodz.sql` into the database.
4. Update `config.php`/`.env` with your DB host/user/password and SMTP credentials.
5. Serve the project through Apache/Nginx pointing the document root to the repo root.

## Environment Variables

Sensitive settings live in `.env` and are loaded via Docker + runtime helpers.

| Variable   | Description                              |
|------------|------------------------------------------|
| `DB_HOST`  | Database host (defaults to `db` in Docker)|
| `DB_NAME`  | Database schema name (`thodz`)            |
| `DB_USER`  | Database username                         |
| `DB_PASS`  | Database password                         |
| `SMTP_USER`| Gmail/SMTP username                       |
| `SMTP_PASS`| App-specific password/token               |
| `SMTP_FROM`| Sender email (falls back to `SMTP_USER`)  |

> ℹ️ Never commit real credentials. Use environment overrides or Docker secrets.

## Database Import

The `thodz.sql` dump is automatically ingested by Docker on first run. For manual setups:

```bash
mysql -u <user> -p THODZ < thodz.sql
```

Ensure the MySQL user has privileges to create tables and insert seed data.

## Development Notes

- Default base URL: `http://localhost:8080` (adjust `config.php` for production).
- File uploads are capped at 7 MB for profile images and 5 MB for cover photos; global limits set in `config.php` and `models`.
- Email functionality requires valid Gmail SMTP app password configured via `.env`.
- Keep `uploads/` writable and outside version control.

Happy hacking! 🎉
