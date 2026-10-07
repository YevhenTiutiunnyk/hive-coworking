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
