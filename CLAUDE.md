# CLAUDE.md — النخيل كوول (Al Nakheel Cool)

Production site for an Egyptian AC company (sales, installation, maintenance). Arabic, RTL, Laravel 13 + Filament 5.
**Priority #1: SEO. Priority #2: fast, faithful design.** `PLAN.md` is the approved spec (decisions in its §0); read it
before starting a phase. Talk to the owner in Egyptian Arabic; code, commits and docs in English.

## Workflow
- Work in phases (PLAN.md §10). Every phase ends with `composer test`, `vendor/bin/pint --test` and
  `vendor/bin/phpstan analyse` all green, then a commit. Never push red.
- Never commit `.env`, keys, tokens or credentials. Config comes from `.env` / settings.
- Every absolute URL comes from `APP_URL` via `url()`/`route()`. Never hardcode a domain.
- Seed/demo content ships **unpublished** (`is_published = false`). `DemoSeeder` never runs in production.

## Content honesty (hard rules)
- Never invent facts: prices, years, stats, team size, response times, certifications, dealer status, testimonials,
  projects, addresses, hours. Unknowns are written as `[TODO: …]`. A test fails if `[TODO` renders on a published page.
- The old site's stats (25/135/957/9856/1839) and testimonials are template filler: never reuse them.
- Only phone: **01055207525** → `tel:+201055207525`, `https://wa.me/201055207525`. 01207720574 is private.
  Never publish the personal email from the old `mailto`. The email is shown only when settings say it is confirmed.
- No address/geo until the owner confirms a public location (service-area business).
- No 24/7 / emergency claims unless `settings.business.is_24_7` is true.
- Brand spelling exactly: **النخيل كوول**; alternates: شركة النخيل, Al Nakheel Cool.

## SEO (non-negotiable)
- Everything important is in server-rendered HTML; prices/specs are text. No SPA on public pages.
- `<html lang="ar" dir="rtl">`, landmarks, visible breadcrumbs on every page except home.
- **Exactly one `<h1>`** per page. Decorative numbers (LCD, HP numerals) are never headings.
- Every page sets SEO data through `App\Seo\SeoData` (title, description, canonical, robots, OG, breadcrumbs, schema).
  Titles ≤ 60 chars (test limit 65), descriptions 120–160 (test limit 100–170), unique sitewide. No meta keywords.
- Canonical: absolute, self-referencing, built from the route (tracking params never leak into it).
  Filter/sort params → `noindex, follow` and **no canonical**. `?page=N` is indexable, self-canonical, «صفحة N» in title.
- Hubs (areas, projects, reviews, blog, categories) are `noindex, follow` until they have ≥ 3 published items.
  Facets are indexable only with ≥ 3 live products and a unique intro. Areas publish only when the guard passes.
- JSON-LD: one `@graph` per page, stable `@id`s (`/#organization`, `/#website`, `{url}#webpage`, `{url}#breadcrumb`…).
  Never self-serving `aggregateRating` on the business. No Product markup on list pages. No LocalBusiness per area.
- Sitemaps list only indexable, published, canonical 200 URLs with real `lastmod` (`content_modified_at`, never
  `updated_at`). No priority/changefreq.
- Non-production: `X-Robots-Tag: noindex` + `robots.txt` `Disallow: /`.
- One canonical host: https, non-www, lowercase, no trailing slash (301, one hop).
- Slug changes create 301s automatically (slug history). Discontinued products 301 to the closest facet.
- Images: Arabic alt required, descriptive English filenames, AVIF/WebP + srcset, width/height, lazy below the fold,
  LCP image `fetchpriority="high"`. Maps and YouTube via click-to-load facades.
- Internal links use descriptive Arabic anchors. Footer links hubs + footer areas only.
- Keyword ownership: one primary intent per page (PLAN.md §7). Facets own brand/capacity price queries; `/prices`
  covers cross-brand comparison and service costs.

## RTL, design and front-end
- Spec: PLAN.md §8 (from the brief's §7). If `./design` appears, it becomes the source of truth: re-skin tokens first.
- Tokens only (`resources/css/tokens.css`): `--t45 #E73F1E`, `--t38 #FB6C00`, `--t30 #F9B637`, `--t24 #FFDD9C`,
  `--ink #1F120C`, `--paper #FFF6E8`. Text on `--t38`/`--t30` is always `--ink`; on `--t45` large text only.
- CSS logical properties only (`margin-inline-start`, `inset-inline-end`, `padding-block`…); no `left/right` props.
- Fonts: Changa 700/800 headings, IBM Plex Sans Arabic 400/500/600 body, Handjet (digits + ° only) for LCD.
  Self-hosted woff2, `font-display: swap`. Western digits 0–9. **Never `letter-spacing` on Arabic.**
- JS budget ≤ 50 KB gz on public pages; vanilla modules; no Bootstrap/jQuery. CSS-only motion.
  Louver intro ≤ 600 ms, once per session, off with `prefers-reduced-motion`; the H1 is visible from the first paint.
- WCAG 2.2 AA: visible focus, 44 px targets, decorative elements `aria-hidden`.
- Copy tone: simplified MSA for titles, meta, H1s, specs; light Egyptian colloquial for CTAs, hero slogan, friendly body.

## Security
- Admin (`/admin`) requires MFA for every user; login throttled. Security headers middleware stays on; CSP is
  report-only until the owner enforces it; HSTS only via `SECURITY_HSTS=true`.
- Sanitize admin rich text (purifier). Public forms: CSRF + honeypot + throttle.
- Backups (spatie/laravel-backup) run daily to the off-server disk from `.env`.

## Performance
- Targets (mobile): LCP < 2.5 s, INP < 200 ms, CLS < 0.1; Lighthouse SEO 100, Perf ≥ 90, A11y/BP ≥ 95.
- Guest response cache is invalidated on content changes; no N+1 (`preventLazyLoading` outside production).

## Commands
```
composer test                 # Pest (SQLite in-memory)
vendor/bin/pint               # format
vendor/bin/phpstan analyse    # Larastan
npm run build                 # Vite production assets
php artisan app:sitemap       # regenerate sitemaps
```
