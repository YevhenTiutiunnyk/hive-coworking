# Hive Coworking: Design Spec

A full-stack WordPress portfolio project: a website for **Hive Coworking**, a fictional
network of three coworking spaces. The site shows how modern WordPress is built:
a block theme, a custom plugin with its own REST API and database table,
Gutenberg blocks, the Interactivity API, tests and CI.

## Goals

- Show modern, dependency-free WordPress engineering (no ACF, no page builders).
- Run as a live demo in **WordPress Playground** (SQLite, no cron, no outgoing mail),
  so all features must work without external services.
- English UI and content, fully internationalized, with a complete Russian translation.

## Approach

Native WordPress stack:

- Block theme (FSE): `theme.json`, templates, template parts, patterns.
- Plugin `hive-core` holds all data and business logic.
- Custom blocks in React via `@wordpress/scripts`.
- Front-end interactivity (booking, filters) through the Interactivity API,
  backed by the plugin's own REST API.
- Local development via `wp-env` (Docker); PHPUnit and Playwright tests;
  GitHub Actions CI; Playground blueprint for the demo.

Rejected alternatives: a classic agency stack (Bedrock, classic theme, ACF) looks dated
and needs paid plugins; a React SPA booking widget is worse for SEO and performance
and is less idiomatic WordPress.

## Repository layout (monorepo)

```
hive-coworking/
├── plugins/hive-core/      # business logic
├── themes/hive/            # block theme, presentation only
├── blueprint.json          # Playground demo
├── .wp-env.json            # local development
├── tests/e2e/              # Playwright
└── .github/workflows/      # CI: lint, PHPUnit, e2e, release zips
```

**Separation rule:** the plugin owns data and logic; the theme owns presentation only.
Booking keeps working if the theme is switched.

## Data model (`hive-core`)

| Entity | Storage | Fields |
|---|---|---|
| Location | CPT `hive_location` | address, opening hours, coordinates, gallery |
| Space | CPT `hive_space` | location (parent), type (meeting room / private office / desk), capacity, hourly price |
| Amenity | taxonomy `hive_amenity` on spaces | Wi-Fi, projector, kitchen, … (used for filters) |
| Plan | CPT `hive_plan` | price, billing period, feature list |
| Event | CPT `hive_event` | start/end date, location, seat limit |
| Booking | custom table `{prefix}hive_bookings` | space_id, user_id, start, end, status, created_at |

- Post meta registered with `register_post_meta(..., show_in_rest => true)`, so it can be
  edited in the block editor without ACF.
- Bookings live in a custom table rather than posts so overlap checks are fast,
  indexed queries. The schema is versioned and migrated with `dbDelta`.

<!-- Next sections (features, REST API, blocks, account area, admin, testing) are added as they are approved. -->
