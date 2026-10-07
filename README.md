# Hive Coworking

A full-stack WordPress project: a block theme plus a custom plugin for a fictional
coworking network, with room booking, a REST API, custom Gutenberg blocks and the
Interactivity API.

> Work in progress. See [the design spec](docs/specs/2026-10-07-hive-coworking-design.md).

## Repository layout

| Path | What it is |
|---|---|
| `plugins/hive-core` | Plugin with all data and business logic (post types, bookings, REST API) |
| `themes/hive` | Block theme, presentation only (coming soon) |
| `docs/specs` | Design spec |

## Development

Requirements: PHP 8.1+, Composer, Node.js 20+, Docker.

```bash
composer install
npm install
npm run env:start   # WordPress at http://localhost:8888 (admin / password)
```

| Command | What it does |
|---|---|
| `composer test:unit` | Unit tests for pure domain code (no WordPress, runs on the host) |
| `npm run test:php` | Integration tests inside the wp-env tests container |
| `composer lint` | PHPCS with WordPress Coding Standards |
| `composer analyse` | PHPStan, level 6 |

## REST API

Namespace `hive/v1`. Times are ISO 8601; times without an offset are read in the site timezone.

| Method | Route | Access | Description |
|---|---|---|---|
| `GET` | `/spaces/{id}/availability?date=YYYY-MM-DD` | Public | Slots of a day with `available` flags |
| `POST` | `/bookings` | `hive_book_spaces` | Book a space: `{ space_id, start, end }` |
| `GET` | `/bookings?scope=upcoming\|past` | Logged in | Current user's bookings |
| `GET` | `/bookings/{id}` | Owner or manager | One booking |
| `PATCH` | `/bookings/{id}` | Owner or manager | Cancel: `{ "status": "cancelled" }` |

Errors use WordPress's standard shape (`code`, `message`, `data.status`), for example:

| Status | Code | When |
|---|---|---|
| 400 | `hive_outside_hours`, `hive_too_soon`, `hive_too_long`, `hive_off_grid` | A booking rule is broken |
| 401 / 403 | `rest_not_logged_in`, `rest_forbidden` | Not logged in, or not allowed to book |
| 403 | `hive_cancel_window` | Too late for a member to cancel |
| 404 | `hive_space_not_found`, `hive_booking_not_found` | Unknown space, or someone else's booking |
| 409 | `hive_conflict` | The slot overlaps another booking |

Other members' bookings return `404` rather than `403`, so booking IDs cannot be probed.
