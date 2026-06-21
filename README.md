# Noor Marketplace
A standalone addon marketplace for **Noor LMS** — a Play-Store-style catalog where you
publish addons (free or paid) and every LMS install can browse, download and install
them from its admin panel.

This is a **separate application / repository** from the LMS. The LMS talks to it over a
REST API: it lists addons, shows details + screenshots, and installs a downloaded `.zip`
through the LMS's existing addon-install pipeline.

## Stack
- Laravel 12 · PHP 8.2+
- Breeze (Blade) auth · Tailwind CSS · Vite
- SQLite for local dev (switch to MySQL for production)

## Build roadmap
1. **Scaffold** — Laravel + Breeze auth + base layout · ✅
2. **Schema + admin shell** — addons, versions, categories, licenses, reviews · ✅
3. **Addon upload (Play-Store admin)** — zip → auto version/desc, icon, screenshots, draft/publish · ✅
4. **Public catalog + REST API** · ✅
5. **LMS integration** — Marketplace menu + one-click install · ✅
6. **Paid addons + licensing** — license keys, per-customer / per-domain · ✅
7. **Polish** — reviews & ratings, sort, rate-limiting · ✅

## REST API (consumed by LMS installs)
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET  | `/api/v1/addons` | List published addons (`q`, `category`, `type`, `sort`) |
| GET  | `/api/v1/addons/{slug}` | Detail + versions + screenshots |
| GET  | `/api/v1/addons/{slug}/download` | Stream the zip (paid: `X-License-Key` + `X-Site-Domain` headers) |
| GET  | `/api/v1/categories` | Categories + counts |
| POST | `/api/v1/licenses/validate` | Validate a key + bind the calling domain |

All endpoints are rate-limited (120/min; download & validate 30/min).

## Local dev
```bash
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed          # categories + a dev admin (admin@noor.test / password)
php artisan serve --port=8001     # http://127.0.0.1:8001
```

## Deploy notes
- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<your-domain>` (addon media URLs derive from it).
- Run `php artisan storage:link` once (icons/screenshots).
- Use MySQL in production; cache config/routes/views in the deploy script.
- **Change the seeded admin password immediately** after the first deploy.
