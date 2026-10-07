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

## Features

### 1. Booking (core feature)

- Block `hive/booking-widget` on a space page. The user picks a date; the block calls
  `GET /hive/v1/spaces/{id}/availability?date=YYYY-MM-DD` and shows free slots
  (slot length from settings, default 60 min) within the location's opening hours.
- The user selects a start and end slot; the block calls `POST /hive/v1/bookings`.
- Server-side validation, in this order:
  1. user has `hive_book_spaces`; valid REST nonce;
  2. start is in the future and at least *minimum notice* from now;
  3. start/end fall inside opening hours and on slot boundaries;
  4. duration does not exceed *maximum duration*;
  5. no overlap with another `confirmed` booking of the same space
     (`existing.start < new.end AND existing.end > new.start`).
  The overlap check and insert run under a per-space lock (an atomic `INSERT IGNORE` into
  the options table, the same technique core uses for its upgrader lock). This works on both
  MySQL and SQLite, so it also protects the Playground demo; a transaction alone would not
  stop two concurrent requests under MySQL's default isolation level.
- Bookings are confirmed immediately (no payments).
- The owner can cancel via `PATCH /hive/v1/bookings/{id}` with `status=cancelled`,
  until *cancellation window* hours before start. Managers can cancel any booking anytime.
- Guests see availability, but the book button is replaced by "Log in to book".

### 2. Space finder

- Block `hive/space-finder`: filters by location, type, minimum capacity, amenities.
- Interactivity API + Interactivity Router: filters are written to the URL
  (`?location=…&type=…&capacity=…&amenity[]=…`), the result list is rendered on the
  server and swapped without a full reload. URLs are shareable and crawlable.

### 3. Other blocks

- `hive/plan-comparison`: pricing table built from `hive_plan`, monthly/yearly toggle.
- `hive/upcoming-events`: next events with an "Add to calendar" link that downloads an
  `.ics` file (`GET /hive/v1/events/{id}/ics`).
- `hive/my-bookings`: account area with upcoming and past bookings and a cancel button.

### 4. Users and capabilities

- Role `hive_member` with capability `hive_book_spaces`; new registrations get this role.
- Administrators get `hive_book_spaces` and `hive_manage_bookings`.
- Every REST route has an explicit `permission_callback`.

### 5. Admin

- "Bookings" screen built on `WP_List_Table`: filter by location and status,
  bulk cancel, CSV export.
- Dashboard widget "Today's bookings" with occupancy per location.
- Settings page (Settings API): slot length, minimum notice, maximum duration,
  cancellation window.

### 6. WP-CLI

- `wp hive seed`: demo content (3 locations, ~12 spaces, plans, events, a demo member
  with bookings). Idempotent; the Playground blueprint uses the same seeding code.
- `wp hive bookings list [--date=<date>] [--format=<format>]`.

### 7. Email

- Booking confirmation and cancellation emails via `wp_mail` with an HTML template.
  In Playground mail is silently not delivered, which is acceptable.

### Out of scope

Payments, maps, event registration, recurring bookings.

## Theme `hive`

- Visual direction: warm editorial look; honey/amber accent on an off-white background,
  deep charcoal text. Headings in Fraunces (serif), body in Inter. Fonts are bundled
  in the theme and registered via `theme.json` `fontFace` (no Google CDN).
- Style variation "Night Shift" (dark).
- Templates: `front-page`, `single-hive_location`, `single-hive_space`,
  `archive-hive_space`, `archive-hive_event`, `page-account`, `404`, `index`.
  Template parts: `header`, `footer`.
- Patterns: hero, location cards, testimonials, call to action, FAQ.
- Accessibility: WCAG 2.2 AA; booking slots are keyboard-operable (arrow keys,
  `aria-pressed`); visible focus styles; respects `prefers-reduced-motion`.
- Performance: no jQuery; block scripts are ES modules (`viewScriptModule`) and load
  only on pages that contain the block.

## Quality

- **PHPUnit** (wp-env tests environment): unit tests for booking validation (overlap,
  opening hours, limits, notice); integration tests for REST routes (permissions,
  `409 Conflict` on overlap, cancellation window).
- **Jest**: slot-calculation utilities used by the booking block.
- **Playwright** e2e: find a room, book it, hit a conflict, cancel.
- **Static analysis**: PHPCS with WordPress Coding Standards, PHPStan level 6 with
  WordPress stubs, ESLint and Stylelint via `@wordpress/scripts`.
- **i18n**: text domains `hive-core` and `hive`; `.pot` generated with `wp i18n make-pot`;
  `ru_RU` `.po`/`.mo` plus JSON translations for JavaScript.

## CI and demo

- GitHub Actions: lint + PHPStan → PHPUnit (PHP 8.1 and 8.3) → Playwright e2e.
- On `main`, CI builds plugin and theme zips and deploys them to **GitHub Pages**
  (Pages serves CORS headers; GitHub release downloads do not, so Playground cannot
  fetch those).
- `blueprint.json` installs the zips from Pages, runs the seeder and logs in the demo
  member. The README links to the Playground demo.

## Implementation order

Each step is committed and pushed separately.

1. Tooling: `package.json`, `composer.json`, `.wp-env.json`, lint configs.
2. Plugin skeleton: CPTs, meta, taxonomy, role.
3. Bookings table, repository, validator, tests.
4. REST API and tests.
5. Settings, admin list table, dashboard widget, WP-CLI, seeder.
6. Blocks: booking widget, space finder, plans, events, my bookings.
7. Theme.
8. Emails, i18n and Russian translation.
9. E2E, CI, Pages + Playground, README with screenshots.
