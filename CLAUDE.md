# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What is Baïkal

Baïkal is a lightweight CalDAV and CardDAV server. It wraps **SabreDAV** (the core WebDAV/CalDAV/CardDAV library) with an admin UI, authentication layer, and configuration system. Supported databases: SQLite, MySQL 8+, PostgreSQL 16+.

## Commands

```bash
composer install          # Install PHP dependencies
composer cs-fixer         # Auto-fix code style (PHP CS-Fixer, PSR-2/Symfony standard)
composer phpstan          # Static analysis (PHPStan, level 0)
composer test             # Run both cs-fixer and phpstan

python run_tests.py       # Integration tests (requires mechanicalsoup, runs against a live server)

make dist                 # Build a distributable .zip archive
```

Integration tests are configured via environment variables (`BASE_URL`, `ADMIN_PASSWORD`) and test real HTTP interactions against a running Baikal instance.

## Architecture

### Request Flow

All DAV traffic enters through `html/`:
- `cal.php` → CalDAV (calendars)
- `card.php` → CardDAV (contacts)
- `dav.php` → generic WebDAV root

Each endpoint sets `BAIKAL_CONTEXT = true`, includes the bootstrap, instantiates `\Baikal\Core\Server`, and calls `->start()` which delegates directly to SabreDAV.

Admin UI is served from `html/admin/` using the **BaikalAdmin** framework.

### Framework Layers (`Core/Frameworks/`)

The codebase contains several custom micro-frameworks loaded via PSR-0 autoloading:

| Framework | Role |
|-----------|------|
| **Flake** | Lightweight MVC core: routing, DB abstraction (PDO), model/view base classes |
| **Formal** | Form generation and validation |
| **Baikal** | Main application: `Core/Server.php` (DAV server setup), models, auth |
| **BaikalAdmin** | Admin interface: routes, controllers, views for users/calendars/settings |

### Key Models (`Core/Frameworks/Baikal/Model/`)

- `User` — stores username + `digesta1` (MD5 Digest auth hash)
- `Principal` — DAV principal identity, linked to a User
- `Calendar` / `Calendar\Event` — calendar collections and events
- `AddressBook` — contact collections
- `Config\Standard` — wraps `config/baikal.yaml` with typed getters/setters

### Configuration

Runtime config lives in `config/baikal.yaml` (copied from `baikal.yaml.dist` on first setup). Key fields: database backend and DSN, auth type (Digest or Basic), CalDAV/CardDAV enabled flags, encryption key, timezone.

`\Baikal\Core\Server` reads this config to enable/disable SabreDAV plugins (CalDAV, CardDAV, ACL, scheduling, etc.).

### Authentication

`Core/Frameworks/Baikal/Core/PDOBasicAuth.php` implements SabreDAV's auth backend interface. Passwords are stored as MD5 Digest hashes (`username:BaikalDAV:password`). Changing auth type between Digest and Basic requires re-hashing all user passwords.

### Database Schema

Initial schema scripts are in `Core/Resources/Db/` (separate files for MySQL and SQLite). Schema migrations run automatically on version upgrade via `\Baikal\Core\Server`.

### Customization
This is a customized version. All possible modification should be located in `vhs` folder.