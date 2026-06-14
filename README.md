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
2. Schema + admin shell — addons, versions, categories, licenses, reviews
3. Addon upload (Play-Store admin) — zip → auto version/desc, icon, screenshots, draft/publish
4. Public catalog + REST API
5. LMS integration — Marketplace menu + one-click install
6. Paid addons + licensing — license keys, per-customer / per-domain
7. Polish — reviews, search, analytics, security

## Local dev
```bash
composer install
npm install && npm run build
php artisan migrate
php artisan serve --port=8001     # http://127.0.0.1:8001
```
Create the first admin account at `/register`.
