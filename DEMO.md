# Ethio Tour — Demo Runbook

A rehearsed 5-minute click path for juries, reviewers, and evaluators, built
entirely on the seeded Gondar pilot data (`php artisan migrate --seed`).

All demo accounts use the password **`password`**.

---

## 0. Pre-demo checklist (2 minutes before you present)

```bash
cp .env.example .env        # once
php artisan key:generate    # once
php artisan migrate --seed  # fresh, populated demo data
npm run build               # once
php artisan serve           # http://127.0.0.1:8000
```

- [ ] Open an incognito window for the tourist flow (clean session)
- [ ] Open a second window (or profile) logged in as `bureau@test.com`
- [ ] Open a third tab logged in as `admin@test.com`
- [ ] Have the offline fallback ready (section 3)
- [ ] If demoing online payment: Chapa sandbox keys set in `.env`
      (`CHAPA_PUBLIC_KEY`, `CHAPA_SECRET_KEY`). Without keys the payment flow
      still demonstrates safely — the app reports that Chapa is not configured
      instead of crashing. Decide **before** the demo which path you show.

## 1. The 5-minute click path

### Act 1 — The tourist experience (~2 min)

| # | Do this | Say this |
|---|---|---|
| 1 | Landing page `/` — use the search bar, search "Gondar" | "One entry point for every tourism service in the city." |
| 2 | Open `/map` | "Every attraction, guide, and provider, spatially — Leaflet + OpenStreetMap, no paid map dependency." |
| 3 | Open `/smart-trip` and generate an itinerary as `tourist@test.com` | "Smart Trip AI builds an itinerary grounded in *real* platform data — and degrades gracefully to deterministic recommendations if the AI key is absent." |
| 4 | Open `/tour-guides` → pick the verified Gondar castles guide (`guide@test.com`, 2,000 ETB/day) → **Book** | "Only bureau-verified guides are bookable. The badge is earned, not claimed." |
| 5 | Complete the booking, then pay (Chapa) | "Payment state is server-sourced and webhook-confirmed — the amount never comes from the client." |

### Act 2 — Trust & governance (~1.5 min)

| # | Do this | Say this |
|---|---|---|
| 6 | As `bureau@test.com`: open the verification queue → the pending guide (`uat-guide-pending@test.com`) and pending businesses (Ras Dashen Lodge, Habesha Cultural Dining) | "Government verifies identity documents before anyone can earn money on the platform." |
| 7 | Approve or reject one request, show the audit trail | "Every decision is recorded — accountability is built in." |
| 8 | Show bureau-managed content: destinations (Fasil Ghebbi, Debre Berhan Selassie, Kuskuam), museums, events (Timkat 2027) | "The bureau curates the official tourism content tourists see." |

### Act 3 — The business & platform side (~1.5 min)

| # | Do this | Say this |
|---|---|---|
| 9 | As `hotel@test.com` (Goha Hotel Gondar): room inventory → reservation inbox → accept one | "Four booking verticals — hotels, restaurants, transport, events — share one availability and reservation engine." |
| 10 | As `guide@test.com`: availability calendar + incoming requests + earnings | "Guides run their business here, not over phone calls." |
| 11 | As `admin@test.com`: dashboard → users, subscriptions, commissions | "The platform sustains itself: subscriptions plus commission, not donor funding." |
| 12 | Close on the tourist's review + notification center | "Trust loops close: completed booking → review → reputation." |

### Closing line

> "Gondar is the pilot. The architecture — nine stakeholder workspaces, one
> booking engine, government verification — scales to any Ethiopian
> destination without redesign."

## 2. Demo data reference (seeded)

| What | Seeded example |
|---|---|
| Verified guides | Gondar castles (2,000 ETB/day) · Simien trekking (2,800) · Lalibela churches (2,200) · Lake Tana (1,800) |
| Pending guide | `uat-guide-pending@test.com` |
| Approved businesses | Goha Hotel Gondar · Four Sisters Restaurant · Simien Highlands 4x4 Transport · Gondar Cultural Events Association |
| Pending businesses | Ras Dashen Lodge · Habesha Cultural Dining |
| Suspended business | Abyssinia Car Rentals |
| Events | Timkat Gondar Epiphany & Cultural Festival (2027-01-19) · Lalibela Meskel Celebration (2026-09-27) |
| Pre-built journey | 6 bookings, 5 payments, 4 reviews, 3 notifications already in the system |
| Accounts | `tourist@` `guide@` `hotel@` `restaurant@` `transport@` `event@` `bureau@` `admin@test.com` — all `password` |

## 3. Offline / incident fallback

If the venue network fails or a third-party service is down:

1. **The app itself is local** — `php artisan serve` needs no internet for any
   core flow (maps need network for tiles; everything else works offline).
2. **Chapa unreachable?** Show the initialized payment attempt and explain the
   webhook confirmation flow from the callback page — the code path is the
   demo, not the bank.
3. **OpenAI key missing/down?** Say it out loud: "watch the fallback" —
   Smart Trip returns deterministic recommendations from real platform data.
   This is a *feature* of the engineering, not a failure.
4. **Total machine failure?** Fall back to the screenshots in
   `docs/screenshots/` and walk the runbook verbally.

## 4. Reset between demos

```bash
php artisan migrate:fresh --seed
```

The seeder chain is idempotent, but a fresh database guarantees identical
storytelling for every jury.

## 5. Anticipated jury questions (one-line answers)

- **Why Laravel?** Mature MVC, first-class testing (388 tests), queues,
  scheduler, and an ecosystem the local job market knows.
- **Why SQLite by default?** Zero-config evaluation and CI; MySQL is one
  `DB_CONNECTION` change away.
- **How are payments safe?** Server-sourced amounts, HMAC-verified webhooks,
  CSRF everywhere except the signature-verified webhook, rate-limited
  initialization.
- **What stops fake reviews?** Review eligibility requires a completed
  booking (`ReviewEligibilityService`).
- **What stops fake guides?** Document upload → Tourism Bureau verification →
  administrative audit. Unverified guides are never publicly bookable.
- **How does it sustain itself?** Provider subscriptions + per-booking
  commission, managed from the admin portal.
- **What if the AI provider is down?** Deterministic recommendation fallback —
  the feature never hard-fails.
