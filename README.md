# Ethio Tour — Smart Tourism Services & Tour Guide Management Platform

[![CI](https://github.com/ERMI2112/ethio-tour/actions/workflows/ci.yml/badge.svg)](https://github.com/ERMI2112/ethio-tour/actions/workflows/ci.yml)

**Ethio Tour** is a multi-portal, web-based tourism platform for Ethiopia that connects
tourists, licensed tour guides, and local tourism businesses in one transactional
ecosystem — discovery, verification, booking, payment, and feedback in a single system.
The pilot implementation targets **Gondar, Ethiopia**, with an architecture designed to
scale to additional destinations nationwide.

---

## The Problem

Tourism in Ethiopia is rich in heritage — Gondar alone holds the UNESCO-listed Fasil
Ghebbi royal enclosure, Debre Berhan Selassie Church, and Fasilides' Bath — but the
service layer around it is fragmented:

- Tourists piece together information from brochures, social media pages, and informal
  referrals, with no single authoritative source.
- Tour guides are engaged through phone calls and personal networks; there is no central
  registry where a visitor can verify a guide's license, compare expertise, or check
  real-time availability.
- Hotels, restaurants, transportation providers, and event organizers each operate in
  isolation, with no shared booking infrastructure.
- Tourism bureaus verify providers, monitor activity, and compile reports manually.

## The Solution

Ethio Tour replaces these disconnected, manual workflows with one centralized platform:

- **Tourists** search, compare, book, and pay for verified services — and review them
  afterward.
- **Tour guides** maintain professional, bureau-verified profiles with availability
  calendars and incoming booking requests.
- **Businesses** (hotels, restaurants, transportation & car rental, event organizers)
  self-manage listings, availability, and reservations through their own portals.
- **The Tourism Bureau** verifies guides and businesses, manages destinations and
  heritage sites, and generates reports from real platform data.
- **Administrators** govern users, roles, subscriptions, commissions, and platform
  health.

The platform runs on a **commission + subscription** model so it can sustain itself
without depending indefinitely on external funding.

## Stakeholders & Portals

| Portal | Who it's for | Core responsibilities |
|---|---|---|
| Public / Tourist | Visitors | Discovery, search, interactive map, Smart Trip AI planner, booking, payment, reviews |
| Tour Guide | Licensed guides | Verified profile, availability calendar, accept/decline tour requests, earnings |
| Hotel | Hotel managers | Room types & inventory, availability, pricing, reservation management |
| Restaurant | Restaurant managers | Menus, table inventory, reservation management |
| Transportation | Transport & car-rental providers | Vehicles, trip/rental requests, pricing |
| Event Organizer | Festival & event organizers | Publish cultural events, manage ticket types and capacity |
| Tourism Bureau | Government tourism officers | Verify guides & businesses, manage destinations/heritage sites/museums, reports |
| Administrator | Platform operators | Users, roles, business approvals, subscriptions, commissions, analytics |

Museum content is curated through the Tourism Bureau portal and published on the public
portal.

## Major Features

### Core booking verticals
Four transactional verticals share one booking engine, availability logic, and payment
flow: **tour guides**, **hotels**, **restaurants**, and **transportation & car rental**.
Event ticketing extends the same model to cultural events.

### Guide verification workflow
Guides register with licensing documents → submissions land in the Tourism Bureau's
verification queue → bureau officers review documents and approve/reject → approved
guides become publicly bookable. A second administrative layer can audit approvals.

### Provider governance
Businesses onboard per vertical, submit legal documents, and move through a status
lifecycle (pending → verified / suspended) supervised by the bureau and administrators,
with subscription plans and commission rates assigned at approval.

### Chapa payment integration
Online payments are processed through [Chapa](https://chapa.co) with initialize →
redirect → verify → HMAC-signed webhook confirmation. Demo/sandbox behavior is clearly
separated from live transactions.

### Smart Trip AI
An AI-assisted trip planner that turns a traveler's intent into a structured itinerary
grounded in the platform's real destinations, guides, services, and events — with a
deterministic fallback that keeps the feature working when no AI key is configured.

### Maps
Interactive discovery map built with **Leaflet** and **OpenStreetMap** tiles: browse
attractions, heritage sites, and service providers spatially.

### Reviews & ratings
Post-booking reviews with eligibility checks — only tourists who completed a booking can
review the service, keeping ratings trustworthy.

### Notifications
An in-app notification center covers booking confirmations, request decisions, payment
outcomes, and approval events across all portals.

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, **Laravel 12** |
| Frontend | Blade templates, **Bootstrap 5**, Alpine.js, Sass, Vite 7 |
| Maps | Leaflet + OpenStreetMap |
| Database | SQLite by default (zero-config dev & CI); MySQL-compatible via env |
| Payments | Chapa (`initialize` / `verify` / webhook) |
| AI | OpenAI-compatible API (Smart Trip), optional — graceful fallback included |
| Testing | PHPUnit 11, 380+ feature & unit tests |
| Code style | Laravel Pint |
| CI | GitHub Actions |

## Architecture Overview

Classic Laravel MVC, organized around role-based portals:

- **Routes & middleware** (`routes/`, `app/Http/Middleware/`) — role middleware
  (`hotel-provider`, `restaurant-provider`, `transportation-provider`,
  `event-organizer`, `active`, `role`) isolates each portal; a global
  `SecurityHeaders` middleware hardens every web response.
- **Controllers** (`app/Http/Controllers/`) — grouped per portal
  (`Admin*`, `Bureau*`, `Hotel*`, `Restaurant*`, `TourGuide*`, …).
- **Domain services** (`app/Services/`) — the business core: availability engines per
  vertical, `BookingCompletionService`, `PaymentService` + `ChapaGateway`,
  `SmartTripAIService`, `NotificationService`, `AuditService`, and more.
- **Models** (`app/Models/`) — user specialization hierarchy (`User` → `Tourist`,
  `TourGuide`, `ServiceProvider`, `TourismBureauOfficer`, `Administrator`) plus domain
  entities (`Destination`, `HeritageSite`, `CulturalEvent`, `TourismService`,
  `Booking`, `Payment`, `Review`, `ProviderSubscription`, …).
- **Views** (`resources/views/`) — Blade layouts and per-portal workspaces.
- **Console** (`app/Console/`, `routes/console.php`) — operational commands wired into
  the scheduler (see below).

## Project Structure (abridged)

```
app/
  Console/Commands/     # Artisan commands (e.g. cleanup:stale-uat)
  Http/Controllers/     # Portal controllers
  Http/Middleware/      # Role guards + SecurityHeaders
  Models/               # Eloquent domain models
  Services/             # Business logic (bookings, payments, AI, notifications)
bootstrap/app.php       # Routing, middleware, scheduler, exception config
database/
  migrations/           # 40+ migrations
  seeders/              # Idempotent demo seed chain
resources/
  views/                # Blade templates per portal
  js/, css/             # Vite-managed assets (maps, Bootstrap, Sass)
routes/                 # web.php (portals) + console.php (commands)
tests/Feature/          # 60+ feature test classes
```

## Getting Started

### Requirements

- PHP **8.2+** (with `sqlite3`, `mbstring`, `curl`, `gd`, `intl`, `zip`)
- Composer 2
- Node.js **20+** & npm

### Installation

```bash
git clone https://github.com/ERMI2112/ethio-tour.git
cd ethio-tour

composer install
cp .env.example .env
php artisan key:generate
```

### Database setup

The default configuration uses SQLite — no database server required:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

`migrate --seed` builds the **complete demo environment** in one command. The seeder
chain is idempotent (safe to re-run) and creates:

1. **UatDemoSeeder** — demo accounts, verified/pending/suspended providers, guides,
   services, inventory, events, and tour packages (including Gondar pilot content).
2. **DemoContentSeeder** — heritage sites, subscription plans, provider subscription.
3. **DemoJourneySeeder** — a completed booking journey with demo payments, reviews,
   and notifications, so every portal has meaningful data on first login.

To use MySQL instead, set `DB_CONNECTION=mysql` and the `DB_*` credentials in `.env`.

### Frontend build

```bash
npm install
npm run build    # production assets
```

### Local development

```bash
composer run dev
```

This starts the Laravel server, queue listener, log tail (Pail), and the Vite dev
server concurrently. The app is available at <http://127.0.0.1:8000>.

## Demo Accounts

All seeded accounts use the password **`password`**:

| Role | Email | What to explore |
|---|---|---|
| Tourist | `tourist@test.com` | Search, map, Smart Trip AI, bookings, payments, reviews |
| Tour Guide | `guide@test.com` | Verified profile, availability, incoming tour requests |
| Hotel | `hotel@test.com` | Room inventory, reservations |
| Restaurant | `restaurant@test.com` | Tables, reservations |
| Transportation | `transport@test.com` | Vehicles, trip requests |
| Event Organizer | `event@test.com` | Events, ticket types |
| Tourism Bureau | `bureau@test.com` | Verification queues, destinations, reports |
| Administrator | `admin@test.com` | Users, approvals, subscriptions, commissions |

Additional UAT accounts (`uat-guide-pending@test.com`, `uat-provider-verified@test.com`,
…) exercise the pending/verified/suspended states of the governance lifecycle.

> These are **demo credentials for local evaluation only** — never reuse them in a
> deployed environment.

## Scheduled Tasks

The Laravel scheduler (`bootstrap/app.php` → `withSchedule`) runs:

| Command | Frequency | Purpose |
|---|---|---|
| `bookings:complete` | Hourly | Complete confirmed bookings whose service window has ended |
| `cleanup:stale-uat` | Daily | Remove stale `UAT%`-prefixed records left by prior seeder runs |

In production, register a single cron entry:

```
* * * * * cd /path-to-ethio-tour && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

```bash
php artisan test          # full suite (SQLite in-memory)
php artisan test --filter=SecurityHeadersTest   # focused
```

The suite covers portal isolation, booking lifecycles, payments, verification
workflows, the seeder chain, scheduler registration, and security headers.

## Continuous Integration

`.github/workflows/ci.yml` runs on every push and pull request:

- **Laravel job** — installs PHP 8.3 dependencies, runs the full test suite against
  in-memory SQLite, and checks code style with `vendor/bin/pint --test`.
- **Frontend job** — installs Node 22 dependencies with `npm ci` and runs the
  production Vite build.

No production secrets are required for CI.

## Security Notes

- Role-based middleware guards every portal; the Chapa webhook is the only CSRF-exempt
  route and is authenticated via HMAC signature verification.
- Passwords are bcrypt-hashed; verification documents are stored on the private `local`
  disk.
- A global middleware applies `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, and a Content-Security-Policy tuned
  to the application's actual asset sources (Google Fonts, Bootstrap Icons CDN,
  OpenStreetMap tiles). `Strict-Transport-Security` is only emitted over HTTPS.
- `APP_DEBUG` must be `false` outside local development.

### Important environment variables

| Variable | Purpose |
|---|---|
| `APP_KEY` | Application encryption key (`php artisan key:generate`) |
| `DB_CONNECTION` / `DB_DATABASE` | Database (SQLite default; MySQL supported) |
| `CHAPA_PUBLIC_KEY` / `CHAPA_SECRET_KEY` | Chapa payment gateway credentials |
| `CHAPA_WEBHOOK_SECRET` | HMAC secret used to verify Chapa webhooks |
| `CHAPA_BASE_URL` | Chapa API base (defaults to `https://api.chapa.co/v1`) |
| `OPENAI_API_KEY` / `OPENAI_BASE_URL` / `OPENAI_MODEL` | Smart Trip AI (optional — falls back to deterministic recommendations) |
| `MAIL_MAILER` | Mail transport (`log` for local development) |
| `QUEUE_CONNECTION` | Queue driver (`database` by default) |

## Screenshots

> **Placeholders** — the repository does not currently store UI screenshots. Capture
> these views after `php artisan migrate --seed` and place them under
> `docs/screenshots/`:

- [ ] Landing page with destination search
- [ ] Interactive map (Leaflet/OpenStreetMap)
- [ ] Smart Trip AI planner
- [ ] Tour guide profile with verification badge
- [ ] Booking flow with Chapa checkout
- [ ] Tourism Bureau verification queue
- [ ] Administrator dashboard

The platform's destination photography lives under `public/images/` — see
`public/images/CREDITS.md` for the full attribution table (all images are Wikimedia
Commons files under FAL / CC licenses, maintained locally rather than hot-linked).

## Credits & License

- Built on [Laravel](https://laravel.com) (MIT) with
  [Bootstrap](https://getbootstrap.com), [Leaflet](https://leafletjs.com), and map data
  © [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors.
- Destination imagery: Wikimedia Commons — per-file attribution in
  [`public/images/CREDITS.md`](public/images/CREDITS.md).
- Payments by [Chapa](https://chapa.co).
- Developed as a Software Engineering capstone project (University of Gondar), piloted
  for Gondar City, Ethiopia.

The Laravel framework is open-sourced software licensed under the
[MIT license](https://opensource.org/licenses/MIT).
