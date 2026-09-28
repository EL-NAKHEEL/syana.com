# النخيل كوول — Al Nakheel Cool

Production website for an Egyptian AC company (sales, installation, maintenance). Arabic, RTL, SEO-first.
Laravel 13 · PHP 8.4 · MySQL 8 · Blade + Vite (vanilla JS) · Filament 5 admin.

- **Spec:** [`PLAN.md`](PLAN.md) (approved; decisions in §0) · **Rules for every session:** [`CLAUDE.md`](CLAUDE.md)
- **Open facts to confirm before launch:** [`TODO.md`](TODO.md)
- The old static site lives in `legacy/old-site/` for reference only (it is never deployed).

> Status: **P1–P5 built.** Everything ships unpublished; the site goes live section by section as the owner confirms
> the facts in `TODO.md`. Deployment and the launch checklist are below.

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

## Deployment

Requirements: PHP 8.4 (`intl`, `gd` or `imagick`, `pdo_mysql`, `mbstring`, `fileinfo`, `zip`), MySQL 8, Composer,
Node 20+ for building assets (can run locally), cron, HTTPS.

### Production `.env` (never committed)

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com            # final domain: https, non-www
DB_CONNECTION=mysql                    # + DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD
SESSION_SECURE_COOKIE=true
MAIL_MAILER=smtp                       # + SMTP credentials; MAIL_FROM_ADDRESS on the site's domain
NOTIFY_EMAIL=owner@example.com         # new bookings/orders/reviews
BACKUP_DISKS=backups                   # off-server disk + BACKUP_S3_* keys, BACKUP_ARCHIVE_PASSWORD
TRUSTED_PROXIES=*                      # or the proxy/CDN IPs
# After HTTPS is confirmed: SECURITY_HSTS=true · after a clean week of CSP reports: SECURITY_CSP_ENFORCE=true
```

### First deploy / every deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build                        # or build locally and upload public/build
php artisan migrate --force                    # includes the old-URL redirect map
php artisan db:seed --force                    # first deploy only: migrated drafts (unpublished); no demo data
php artisan storage:link
php artisan filament:assets
php artisan optimize                           # config/route/view/event caches
php artisan responsecache:clear && php artisan app:sitemap
php artisan make:filament-user                 # first deploy only; MFA enrolment on first login
```

Add the scheduler cron (see **Scheduler & queue**). Run `php artisan optimize:clear && php artisan optimize` after
changing `.env`.

### Shared hosting (cPanel / hPanel, Apache or LiteSpeed)

- Point the domain's document root at `public/`. If the host forces `public_html`, upload the project one level above
  it and make `public_html` a symlink to `public/` (or copy `public/` into it and fix the two paths in `index.php`).
- `public/.htaccess` already has the front controller, Brotli/gzip and cache headers (hashed `build/` assets:
  1 year immutable; photos: 30 days).
- Force HTTPS and pick the non-www host in the panel; Laravel then 301s any other host/https variant in one hop.
- No Supervisor: the scheduler drains the queue every minute (`QUEUE_SUPERVISED=false`).

### VPS (nginx + PHP-FPM)

- `deploy/nginx.conf` is a complete sample: www/http → https non-www in one hop, gzip (Brotli if the module exists),
  immutable caching for hashed assets.
- Run `php artisan queue:work --tries=3` under Supervisor and set `QUEUE_SUPERVISED=true`.

### Moving off GitHub Pages

The old static site is served by GitHub Pages from `main`. At launch, generate the stubs with the production URL and
publish them as the Pages source (e.g. a `gh-pages` branch) so old links 301-equivalent to the new pages:

```bash
APP_URL=https://example.com php artisan app:github-pages-stubs    # → deploy/github-pages-redirects/
```

If the domain itself moves to the new host, the same map already runs as real 301s (`redirects` table).

## Launch checklist

1. `TODO.md` answered; every page you publish is free of `[TODO]` (publishing is blocked otherwise).
2. Production `.env` set (above); `APP_URL` is the final https non-www domain; `php artisan optimize` done.
3. Admin user created, MFA enrolled; `/admin` login works; test notification email received.
4. Settings reviewed: بيانات النشاط (email confirmed? address? hours? 24/7?), الهيدر والقائمة والفوتر, إعدادات SEO
   (Search Console / Bing verification codes, IndexNow key), التحليلات (GA4/GTM, Google Ads conversion label).
5. Content published: home, about, contact, services, policies; areas only once they pass the guard; hubs become
   indexable by themselves at ≥ 3 items.
6. `https://domain/robots.txt` shows `Allow` + the sitemap line (non-production shows `Disallow: /`);
   `https://domain/sitemap.xml` lists only live pages. Submit the sitemap in Search Console and Bing Webmaster.
7. Old URLs: `/about.html`, `/syana.html`… answer one 301 to the new page; GitHub Pages stubs published.
8. Place a test booking and a COD test order; confirm the email + admin notification, then delete them.
9. Lighthouse (mobile) on a production build: `LHCI_BASE_URL=https://domain npx @lhci/cli@0.14 autorun`
   — targets Perf ≥ 90, A11y/BP ≥ 95, SEO 100, LCP < 2.5 s, CLS < 0.1 (see `lighthouserc.cjs`).
10. Backups: `php artisan backup:run` succeeds to the off-server disk; `backup:monitor` is green.
11. Google Business Profile: website link → new domain; review link saved in settings.
12. After a week: CSP report log clean → `SECURITY_CSP_ENFORCE=true`; HTTPS stable → `SECURITY_HSTS=true`.

## Assets

- Fonts: `scripts/fonts/subset.sh` rebuilds the woff2 subsets (OFL, see `resources/fonts/README.md`).
- Icons: `scripts/icons/build.sh` renders the favicon and logo PNGs (needs Chromium + Pillow).
- Placeholder photos: `scripts/images/build-placeholders.py` builds the AVIF/WebP/JPEG sizes from `legacy/old-site/img`.
