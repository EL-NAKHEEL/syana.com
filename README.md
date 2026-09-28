# النخيل كوول — Al Nakheel Cool

Production website for an Egyptian AC company (sales, installation, maintenance). Arabic, RTL, SEO-first.
Laravel 13 · PHP 8.4 · MySQL 8 · Blade + Vite (vanilla JS) · Filament 5 admin.

- **Spec:** [`PLAN.md`](PLAN.md) (approved; decisions in §0) · **Rules for every session:** [`CLAUDE.md`](CLAUDE.md)
- **Open facts to confirm before launch:** [`TODO.md`](TODO.md)
- The old static site lives in `legacy/old-site/` for reference only (it is never deployed).

> Status: **P1 done** (foundation, SEO core, design system, homepage). Full deployment guide and launch checklist
> arrive in P5.

## Local setup

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite            # or configure MySQL in .env
php artisan migrate --seed                 # seeds the migrated drafts, all UNPUBLISHED
php artisan storage:link
npm install && npm run build               # or `npm run dev`
php artisan make:filament-user             # admin account; MFA is enrolled on first login
php artisan serve
```

Admin: `/admin`. Drafts are 404 for visitors; while signed in you see them with a «مسودة» banner (noindex).
A page cannot be published while it still contains a `[TODO: …]` marker.

## Quality gates (all must pass before every commit)

```bash
composer test                               # Pest: SEO crawler, redirects, robots, security, content honesty…
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
```

## Scheduler & queue

One cron entry runs everything (sitemaps, backups, queue draining on shared hosting):

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

On a VPS, run `php artisan queue:work` under Supervisor and set `QUEUE_SUPERVISED=true`.

IndexNow pings (Bing/Yandex) are queued on every publish/update/unpublish in production. The key lives in
admin › الإعدادات › إعدادات SEO and is served at `/{key}.txt`; disable with `INDEXNOW_ENABLED=false`.

## What the owner edits in the admin

- **الإعدادات › الهيدر والقائمة والفوتر:** top-bar text, header WhatsApp button, menu order/labels/visibility, footer text
  and columns, floating buttons.
- **المحتوى › الصفحات:** home (hero, sections order/visibility, texts, photos, FAQs), about, contact, policies, and the
  section pages (template «صفحة قسم»: services, store, prices, areas, blog, projects, reviews). A section page's
  edits show once it is published; until then the built-in wording is used.
- Services, areas, price guides, products, brands, facets, posts, projects, reviews and people each have their own
  resource. Photos are uploaded with an Arabic alt; empty = the temporary placeholder.

## Admin SEO health

admin › SEO › صحة SEO lists duplicate titles/descriptions/primary keywords, manual `noindex` records, published
areas failing the local-content guard, images without Arabic alt and the top unresolved 404s.

## Assets

- Fonts: `scripts/fonts/subset.sh` rebuilds the woff2 subsets (OFL, see `resources/fonts/README.md`).
- Icons: `scripts/icons/build.sh` renders the favicon and logo PNGs (needs Chromium + Pillow).
- Placeholder photos: `scripts/images/build-placeholders.py` builds the AVIF/WebP/JPEG sizes from `legacy/old-site/img`.
