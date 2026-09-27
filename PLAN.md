# PLAN — النخيل كوول (Al Nakheel Cool) production website

Status: **approved 2026-09-27** (decisions in §0) · Progress: **P1 done** · Date: 2026-09-27 · Branch: `claude/friendly-galileo-tdpcze`

Nothing in this plan is built yet. Items marked **[Q#]** depend on an open question in §12.
Items marked **[TODO]** are unknown facts that must come from the business, never be invented.

---

## 0. Decisions log (2026-09-27)

Blank answers keep the default in §12 and stay `[TODO]`. Nothing unconfirmed is published.

| # | Decision |
|---|---|
| Q1 | Build from spec §7. If a Claude Design export later lands in `./design`, re-skin the components to match it, **tokens first**. |
| Q2 | Non-www canonical host. Domain `[TODO]` — everything reads `APP_URL`. |
| Q3 | GBP and social links `[TODO]` → `sameAs` empty, review button hidden until set in settings. |
| Q4 | **01055207525** is the only number in calls, WhatsApp and schema. 01207720574 stays private (default). Email: `elnakheel55@gmail.com` is stored in settings as a draft, **hidden until confirmed** (the answer was left in template brackets). The personal address from the old `mailto` is never published anywhere. |
| Q5 | Office status `[TODO]` → service-area business, no address/geo. |
| Q6 | Hours `[TODO]`; no 24/7 claims; `/services/emergency-ac-repair` stays unpublished. |
| Q7 | Brands, dealer status, types `[TODO]` → no brand/dealer claims, no seeded real brands published. |
| Q8 | Installation-in-price, delivery, returns, installments `[TODO]` → no shippingDetails/return-policy schema until confirmed. |
| Q9 | Facets own brand/capacity price queries (with a live price table, change 2); `/prices` = cross-brand comparison + service costs. |
| Q10 | Hosting not chosen; target PHP 8.4, MySQL 8, SSH, cron. |
| Q11 | This repo; old site in `legacy/old-site/`; merge to `main` only at launch. **Never commit `.env` or any secret.** |
| Q12–Q14 | Areas, E-E-A-T facts, starting prices `[TODO]`; services show «السعر بعد المعاينة». |
| Q15 | Simplified MSA for titles, meta, H1s and specs; light Egyptian colloquial for CTAs, the hero slogan and friendly body copy. |
| Q16 | Filament database notifications + email to `[TODO]` (`MAIL_*` / `NOTIFY_EMAIL` in `.env`). |
| Q17 | Calculator coefficients are configurable defaults flagged `[TODO confirm]`. |
| C1 | Area publish guard: approved local reviews **optional** (threshold 0); all other fields required. **Hubs** (areas, projects, reviews, blog) are `noindex, follow` and out of the sitemap until they have **≥ 3 published items**. |
| C2 | Facet pages show a compact **live price table** above the grid (model, HP, type, price, sale price) with «آخر تحديث»; keyword map adds the colloquial plurals «تكييف 3 حصنة / 4 حصنة / 5 حصنة». |
| C3 | Old image redirects list **exact old file paths** only — no `/img/*` wildcard. |
| C4 | Home has one H1 holding slogan + keyword line: `<h1><span>خلّي الحرّ برّه.</span> <span>بيع وتركيب وصيانة التكييفات في مصر</span></h1>`. |
| C5 | Security (§6.9): Filament MFA required for every admin, login throttling, security headers (HSTS once HTTPS is live, CSP report-only first, `X-Content-Type-Options`, `Referrer-Policy`, …), spatie/laravel-backup on by default (daily DB + media to an off-server disk from `.env`). |

---

## 1. Findings from the old site

### 1.1 Inventory

Source: this repository (static HTML Codex template, served by GitHub Pages at
`https://el-nakheel.github.io/syana.com/`). No `./design` folder exists, so the design spec in the
brief (§7) is the visual source of truth unless a Claude Design export arrives **[Q1]**.

| Old file | Real content worth keeping | Notes |
|---|---|---|
| `index.html` | Brand name, phone, service list (home/commercial maintenance, installation, emergency), 5 project captions | Stats 25/135/957/9856 and 3 testimonials are template filler |
| `about.html` | One generic paragraph; email `elnakheel55@gmail.com`; a **second phone `+20 120 772 0574`** **[Q4]** | Stats here say 1839 projects vs 9856 on home → proves the numbers are filler; H1 "About Us" in English |
| `service.html` | 3 service blurbs | Duplicate of home section |
| `syana.html` (صيانة) | Best copy on the site: team, speed, 24/7, all AC types (split/window/central), periodic maintenance, filter cleaning | Egyptian colloquial tone |
| `tarkeeb.html` (تركيب) | **Actually about preparation**: pipe/electrical groundwork before install, dismantle & relocate, post-install support | Content and filename are swapped with tagheez |
| `tagheez.html` (تجهيزات) | **Actually about installation**: install new units, dismantle & move, all types | Swapped (see above) |
| `contact.html` | Phone, WhatsApp, email; `mailto` is broken and points to a **different personal address** **[Q4]**; Google Map at التجمع الخامس – شارع التسعين (30.00904, 31.44818) **[Q5]** | |
| `feature.html` | 3 generic "why us" points | Not in main nav; links to `testimonial.html` which does not exist |

Content will be **migrated by topic, not by file**: tarkeeb's preparation copy feeds `/services/ac-preparation`,
tagheez's installation/relocation copy feeds `/services/ac-installation` and `/services/ac-relocation`.
Redirects still follow URL meaning (tarkeeb → installation, tagheez → preparation) as briefed.

### 1.2 Defects to not carry over
- Same `<title>`/description on all 8 pages; `meta keywords`; 3–8 `<h1>` per page (decorative "25" is an H1); numbers as headings.
- Phone links: `tel:+2001055207525`, `tel:%2001055207525`, `tel:+0201055207525`, `wa.me/+2001055207525` (all invalid). Correct: `tel:+201055207525`, `https://wa.me/201055207525`.
- Dead links: `href=""` ×109 (social icons, newsletter), `href="#"` ×37, `tarkeeb.html.html`, `tagheez.html.html.html`, `testimonial.html`, breadcrumb "Home/Pages" in English.
- Missing images: `img/tarkeeb.jpg`, `img/f1fb76aad1964626edb76cece4cc0b38.jpg`. Empty YouTube iframes.
- Images are stock (Freepik file names), up to **15.7 MB** each, `alt=''`/`alt='صورة'`. Testimonial avatars are template stock.
- Bootstrap + jQuery + Owl + WOW + 4 CDN font/icon sheets; English template footer credit (htmlcodex).
- Fake newsletter form, "الشروط والأحكام / الدعم" links to nothing.

---

## 2. Architecture

- **Laravel 13.x** (latest: 13.33, Sep 2026) on **PHP 8.4** — several required Spatie packages and Pest 5 need `^8.4` **[Q10]**.
- **MySQL 8** (MariaDB 10.11+ compatible). Tests run on SQLite in-memory; queries kept portable.
- Public site: server-rendered **Blade components**, Vite, **vanilla JS modules** (no Alpine on public pages unless a
  component truly needs it; Alpine ≈ 15 KB gz would eat a third of the 50 KB budget). No Bootstrap/jQuery.
- Admin: **Filament 5.x** at `/admin`, `ar` locale, RTL.
- Drivers (shared-hosting defaults): `QUEUE_CONNECTION=database`, `CACHE_STORE=database` (Redis optional),
  `SESSION_DRIVER=database`; `schedule:run` via cron every minute; queue worked by
  `schedule` → `queue:work --stop-when-empty` on shared hosting, Supervisor on VPS. Assets prebuilt and committed to the release artifact.

### 2.1 Repository layout
The Laravel app lives at the repo root on this branch. The old static site moves to `legacy/old-site/`
(kept as the content/redirect reference, never deployed). **Do not merge this branch into `main` before launch**:
GitHub Pages serves `main`, and the old site must keep working until the redirect stubs replace it **[Q11]**.

```
app/
  Domain/            Catalog, Services, Areas, Content, Orders, Seo (models, actions, queries)
  Seo/               SeoData, TitleBuilder, CanonicalResolver, RobotsResolver, Schema/ (graph builders), Sitemap/
  Http/Middleware/   CanonicalizeRequest, LegacyRedirects, SlugHistoryRedirects, NoindexNonProduction, Log404
  Payments/          PaymentGateway (interface), CashOnDeliveryGateway
  Filament/          Resources, Pages (Settings, SeoDashboard), Components (SeoPanel, SerpPreview)
resources/
  css/               tokens.css, base.css, components/*.css (logical properties only)
  js/                modules: filters.js, cart.js, louver-intro.js, facades.js, analytics.js, calculator.js
  views/components/  design system (see §8)
  fonts/             subset woff2 (build script in scripts/fonts/)
deploy/github-pages-redirects/   one stub per old file (P5)
legacy/old-site/     the current HTML site, for reference only
tests/Feature/Seo/   crawler-based SEO suite
```

### 2.2 Request pipeline (public)
1. `CanonicalizeRequest` — one 301 hop to `https://{APP_URL host}` + lowercase path + no trailing slash + collapsed `//`.
   Mirrored in `public/.htaccess` / nginx snippet so most hits never boot PHP.
2. `LegacyRedirects` + `redirects` table lookup (cached map) → 301/302/410 and hit counter (queued increment).
3. Route match; model lookup by slug; on miss, `slug_histories` lookup → 301 to current URL.
4. Response cache (guests only, GET, 200, no filter params) with CSRF-token replacer; cart count is read client-side
   from a non-HttpOnly `cart_count` cookie so cached HTML stays user-agnostic.
5. `NoindexNonProduction` adds `X-Robots-Tag: noindex` when `APP_ENV !== production`.
6. Real 404 view (status 404) → `not_found_logs` upsert (queued, bots flagged).

---

## 3. Database schema

Conventions: `id` bigint, `timestamps`, `slug` unique per table, `is_published` + `published_at`, and
**`content_modified_at`** (set by an observer only when user-visible fields change — feeds `<lastmod>` and `dateModified`;
`updated_at` is never used for SEO dates). All Arabic text columns `utf8mb4_unicode_ci`.

### Shared / SEO
| Table | Columns |
|---|---|
| `seo_meta` (morph) | `seoable_type/id`, `title`, `description`, `canonical_override`, `robots` (`index,follow` / `noindex,follow` / `noindex,nofollow`), `primary_keyword`, `og_title`, `og_description`, `og_image_mode` (auto/custom) |
| `slug_histories` | `sluggable_type/id`, `old_slug`, `created_at`; unique (`type`,`old_slug`) |
| `redirects` | `from_path` unique, `to_url`, `status` (301/302/410), `source` (manual/slug/legacy/404-suggestion), `is_active`, `hits`, `last_hit_at`, `note` |
| `not_found_logs` | `path` unique, `hits`, `first_seen_at`, `last_seen_at`, `last_referrer`, `is_bot`, `resolved_redirect_id` |
| `faqs` (morph) | `faqable_type/id`, `question`, `answer`, `sort` |
| `media` | spatie/laravel-medialibrary (alt text required via custom property `alt`, enforced in admin + tests) |
| `settings` | spatie/laravel-settings groups: `business` (NAP, hours, sameAs, GBP URL, review URL), `seo` (title suffix, default OG, verification codes, IndexNow key), `analytics` (GA4/GTM IDs), `calculator` (coefficients), `commerce` (shipping fee rules, COD on/off, return days) |

### Catalog
| Table | Columns |
|---|---|
| `brands` | `name_ar`, `name_en`, `slug`, `logo` (media), `description`, `is_authorized_dealer` **[Q7]**, `sort`, publish fields |
| `products` | `brand_id`, `name` (auto-suggested), `slug`, `model_number` (mpn), `sku`, `type` enum (`split`,`window`,`concealed`,`cassette`,`floor-standing`), `hp` decimal(4,2) (1.50/2.25/3/4/5), `btu` int, `cooling` enum (`cool`,`cool-heat`), `is_inverter`, `energy_class`, `room_area_min/max` m², `warranty_months`, `warranty_note`, `price` decimal(10,2), `sale_price` nullable, `sale_ends_at`, `installments_note`, `installation_included` **[Q8]**, `stock_status` enum (`in_stock`,`out_of_stock`,`preorder`,`discontinued`), `specs` json (label/value rows), `short_description`, `description`, `search_text` (normalized), `price_changed_at`, publish + `content_modified_at` |
| `product_price_changes` | `product_id`, `old_price`, `new_price`, `old_sale_price`, `new_sale_price`, `changed_at` — source of truth for “آخر تحديث” |
| `facet_pages` | `kind` (`brand`,`capacity`,`type`,`brand_capacity`), `brand_id` nullable, `hp` nullable, `type` nullable, `path` unique (e.g. `brand/sharp/1-5-hp`), `h1`, `intro` (required, unique), `body`, publish fields; indexable = published ∧ intro filled ∧ ≥ 3 live products (computed + cached, re-evaluated on product save) |
| `related_products` | `product_id`, `related_id`, `sort` (manual alternatives; fallback = same hp ± brand) |

### Services, areas, prices
| Table | Columns |
|---|---|
| `services` | `slug`, `name`, `h1`, `summary`, `intro`, structured sections: `included` (json list), `warning_signs` (json), `process_steps` (json), `price_factors` (json), `body`, `starting_price` nullable, `price_unit_note`, `schema_service_type`, `requires_24_7` (bool, hides page unless settings say 24/7) , `sort`, publish fields |
| `areas` | `name_ar`, `name_en`, `slug`, `governorate`, `local_intro`, `response_time_note`, `local_notes`, `show_in_footer`, `sort`, publish fields. **Publish guard**: intro ≥ 150 words, response time, local notes, ≥ 1 service, ≥ 2 local FAQs, ≥ 1 nearby area; approved local reviews optional (`areas.min_reviews` setting, default 0) |
| `area_service` | `area_id`, `service_id`, `note` (local availability/price note) |
| `area_neighbors` | `area_id`, `neighbor_id` |
| `price_guides` | `slug`, `title`, `h1`, `intro`, `scope` (`brand`,`capacity`,`type`,`service`,`all`), `brand_id`/`hp`/`type`/`service_id` nullable, `body`, `show_year` (auto), publish fields; the table body is rendered from live `products`/`services` rows — no prices stored here |

### Content
| Table | Columns |
|---|---|
| `people` | `name`, `slug`, `role` (technician/engineer/writer/owner), `job_title`, `bio`, `credentials`, `years_experience` **[TODO]**, `photo`, `same_as` json, `is_author`, `is_team_member`, `sort` |
| `posts` | `title`, `slug`, `excerpt`, `body` (sanitized HTML with `[product:slug]` embeds), `post_category_id`, `author_id`, `reviewed_by_id` nullable, `reading_minutes` (computed), `featured image`, publish + `content_modified_at` |
| `post_categories` | `name`, `slug`, `intro`, publish fields |
| `projects` | `title`, `slug`, `client_type` (`home`,`office`,`retail`,`medical`,`hotel`,`other`), `area_id`, `service_id`, `summary`, `story`, `completed_on`, photos (media, before/after tags), publish fields |
| `reviews` | `name`, `area_id` nullable, `reviewable_type/id` (product/service) nullable, `rating` 1–5, `body`, `temp_before`, `temp_after`, `video_url`, `status` (`pending`,`approved`,`rejected`), `approved_at`, `ip_hash`, `source` (`site`,`manual`) |
| `pages` | static pages (`about`,`warranty`,`shipping-returns`,`privacy`,`terms`): `slug`, `title`, `h1`, `body`, publish fields |

### Orders & leads
| Table | Columns |
|---|---|
| `orders` | `number`, `name`, `phone` (normalized `01xxxxxxxxx`), `area_id`, `address`, `notes`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `payment_status`, `status` (`new`,`confirmed`,`delivered`,`cancelled`), `utm` json |
| `order_items` | `order_id`, `product_id`, snapshot (`name`,`model_number`,`unit_price`), `qty`, `line_total` |
| `booking_requests` | `name`, `phone`, `area_id`, `service_id`, `ac_type`, `preferred_date`, `notes`, `source_url`, `status`, `utm` json |
| `users` | admins (Filament), `jobs`, `failed_jobs`, `cache`, `sessions` |

---

## 4. Routes

All names are English, lowercase, hyphenated, no trailing slash. `{hp}` slugs: `1-5-hp`, `2-25-hp`, `3-hp`, `4-hp`, `5-hp`.
Types: `split`, `window`, `concealed`, `cassette`, `floor-standing`.

| Route | Name | Index? |
|---|---|---|
| `/` | home | index |
| `/store` | store.index | index (clean or `?page=N`) |
| `/store/brand/{brand}` · `/store/capacity/{hp}` · `/store/type/{type}` · `/store/brand/{brand}/{hp}` | store.facet | per facet rule (§6.3) |
| `/store/{product}` | store.product | index (discontinued → 301) |
| `/services`, `/services/{slug}` | services.* | index |
| `/areas`, `/areas/{slug}` | areas.* | index when published (guarded) |
| `/prices`, `/prices/{slug}` | prices.* | index |
| `/reviews` | reviews.index | index (filter params → noindex) |
| `/blog`, `/blog/{slug}`, `/blog/category/{slug}`, `/blog/author/{slug}` | blog.* | index (category/author with 0 posts → noindex) |
| `/projects`, `/projects/{slug}` | projects.* | index |
| `/about` `/contact` `/warranty` `/shipping-returns` `/privacy` `/terms` | pages.* | index |
| `/cart` `/checkout` `/search` `/thank-you` | — | noindex, follow |
| `POST /cart/items`, `PATCH/DELETE /cart/items/{id}`, `POST /checkout`, `POST /bookings`, `POST /reviews` | — | throttled, honeypot |
| `/sitemap.xml`, `/sitemaps/{type}.xml` | sitemap.* | — |
| `/robots.txt` | robots | — |
| `/{indexnow-key}.txt` | indexnow.key | — |
| `/og/{hash}.jpg` | generated OG images (static files under `public/og`) | — |

Route order: `/store/brand|capacity|type/...` are registered before `/store/{product}`; product slugs may not equal
`brand`, `capacity`, `type` (validation rule).

---

## 5. Redirect map (old → new)

Seeded into `redirects` (source `legacy`) so they also work if old paths hit the new domain, and generated as
GitHub Pages stubs in P5 (`<link rel="canonical">` + `<meta http-equiv="refresh" content="0; url=…">` + visible link).

| Old path (`/syana.com/…` on github.io, `/…` on new domain) | New URL |
|---|---|
| `/`, `/index.html` | `/` |
| `/about.html` | `/about` |
| `/service.html` | `/services` |
| `/syana.html` | `/services/ac-maintenance` |
| `/tarkeeb.html` | `/services/ac-installation` |
| `/tagheez.html` | `/services/ac-preparation` |
| `/contact.html` | `/contact` |
| `/feature.html` | `/about` (its “why us” content moves there) |
| `/testimonial.html` (linked, never existed) | `/reviews` |
| `/tarkeeb.html.html`, `/tagheez.html.html.html` (broken links that were crawlable) | `/services/ac-installation`, `/services/ac-preparation` |
| Exact old image paths (`/img/carousel-1.jpg`, `/img/feature.jpg`, … one row per file in `legacy/old-site/img/`) | 410 Gone (stock photos, no replacement). No wildcard, so new-site images can never match |

---

## 6. SEO specification

### 6.1 Global rules
- `<html lang="ar" dir="rtl">`; landmarks `header/nav/main/aside/footer`; visible breadcrumbs everywhere except home.
- Exactly one `<h1>`; decorative numbers (LCD, HP numerals) are `<span aria-hidden>` or `<data>`, never headings.
- Titles 30–60 chars, descriptions 120–160 chars (hard test limits: title ≤ 65, description 100–170); admin override wins,
  template is the fallback; duplicates are flagged in the SEO dashboard and fail the test suite.
- `{year}` renders only when in-scope prices changed within the current calendar year **and** within
  `seo.price_freshness_days` (default 90); otherwise the title drops the year automatically.
- Paginated pages: `… - صفحة N` in title and description, self-canonical (`?page=N`), page 1 canonical is the clean URL,
  `?page=1` 301 → clean, out-of-range page → 404.
- Canonical = absolute URL built from the route (never from the request), so `utm_*`, `gclid`, `fbclid` and unknown params
  canonicalize to the clean URL automatically.
- Recognized filter/sort params (`brand`, `hp`, `type`, `cooling`, `inverter`, `price_min`, `price_max`, `sort`,
  review filters) → `<meta name="robots" content="noindex, follow">` and **no canonical tag**.
- OG + Twitter (`summary_large_image`) on every page, `og:locale=ar_EG`, `og:site_name=النخيل كوول`; OG image 1200×630 JPEG
  < 300 KB generated from a brand template (GD + Intervention Image 4, Arabic shaping via `khaled.alshamaa/ar-php`),
  regenerated on title change (queued), per-record custom override.
- JSON-LD: one `<script type="application/ld+json">` per page holding an `@graph`; stable IDs:
  `{APP_URL}/#organization`, `{APP_URL}/#website`, `{url}#webpage`, `{url}#breadcrumb`, `{url}#product`,
  `{url}#service`, `{APP_URL}/blog/author/{slug}#person`.
- Descriptions end with a CTA («اتصل 01055207525» / «احجز فني» / «اطلبه الآن»).

### 6.2 Per page type

| Type | Title template (fallback) | Description template (fallback) | Schema (`@graph`) | Index | Sitemap (`lastmod`) |
|---|---|---|---|---|---|
| Home | `النخيل كوول \| بيع وتركيب وصيانة التكييفات في مصر` | Selling + install + maintenance summary, top areas, phone CTA | Organization (`HVACBusiness`,`Store`), WebSite, WebPage | ✔ | pages (max of settings/home content) |
| Store | `متجر التكييفات: اشتري تكييف بالتركيب والضمان \| النخيل كوول` | count of models, brands, COD, CTA | CollectionPage, BreadcrumbList | ✔ (clean/page) | pages (latest product change) |
| Brand facet | `تكييف {brand}: الأسعار والموديلات {year} \| النخيل كوول` | `{n}` models from `{min}` EGP, capacities, CTA | CollectionPage + ItemList(url only), BreadcrumbList | facet rule | facets (latest product change in scope) |
| Capacity facet | `تكييف {hp} حصان: الأسعار والموديلات {year} \| النخيل كوول` | room size hint + price range + CTA | same | facet rule | facets |
| Type facet | `تكييف {type_ar}: الأسعار والموديلات {year} \| النخيل كوول` | | same | facet rule | facets |
| Brand × capacity | `تكييف {brand} {hp} حصان: الأسعار {year} \| النخيل كوول` | | same | facet rule | facets |
| Product | `تكييف {brand} {hp} حصان {cooling} {inverter?} {model} \| النخيل كوول` (drops `{model}` then suffix if > 60) | price, warranty, stock, install note, CTA | Product + Offer (+ StrikethroughPrice UnitPriceSpecification on sale, shippingDetails, hasMerchantReturnPolicy), BreadcrumbList; `aggregateRating`/`review` only from approved product reviews shown on the page | ✔ (also out of stock) | products + `<image:image>` (`content_modified_at`) |
| Services hub | `خدمات التكييف: صيانة وتركيب وتأسيس \| النخيل كوول` | | CollectionPage, BreadcrumbList | ✔ | services |
| Service | admin-authored, fallback `{service_name} \| النخيل كوول` | starting price if known + CTA | Service (serviceType, provider→`#organization`, areaServed from published areas, offers.priceSpecification min price), WebPage, BreadcrumbList | ✔ | services |
| Areas hub | `مناطق خدمة النخيل كوول: صيانة وتركيب التكييفات` | | CollectionPage, BreadcrumbList | hub rule (≥ 3 published) | areas |
| Area | `صيانة وتركيب تكييفات في {area} \| النخيل كوول` | response time + services + CTA | WebPage + Service (areaServed = that Place) — **no LocalBusiness per area**, BreadcrumbList | only when publish guard passes | areas |
| Prices hub | `أسعار التكييفات وخدمات التركيب والصيانة {year} \| النخيل كوول` | | CollectionPage, BreadcrumbList | ✔ | prices |
| Price guide | admin title, e.g. `تكلفة تركيب التكييف {year}: بالتفصيل \| النخيل كوول` | "آخر تحديث {date}" + CTA | WebPage (`dateModified` = last real price change), BreadcrumbList; **no Product markup** | ✔ | prices (last real price change) |
| Reviews | `آراء عملاء النخيل كوول في التكييفات` (+ page N) | | WebPage, BreadcrumbList — **no aggregateRating** (self-serving) | hub rule (≥ 3 approved) | pages |
| Blog hub / category | `مدونة النخيل كوول: نصائح التكييف` / `{category} \| مدونة النخيل كوول` | | CollectionPage (+Blog), BreadcrumbList | hub rule (≥ 3 posts; same for categories) | posts |
| Post | `{title} \| النخيل كوول` (suffix dropped if > 60) | excerpt | BlogPosting (author→Person `@id`, datePublished, dateModified=`content_modified_at`, image, publisher→`#organization`), BreadcrumbList | ✔ | posts |
| Author | `{name}: {job_title} \| النخيل كوول` | | ProfilePage (mainEntity Person), BreadcrumbList | ✔ (≥ 1 post) | posts |
| Projects hub / project | `مشاريع النخيل كوول في التكييف` / `{title} في {area} \| النخيل كوول` | | CollectionPage / WebPage (+ ImageObject), BreadcrumbList | hub: hub rule (≥ 3); project: ✔ | projects |
| About / Contact / policies | fixed per page | | AboutPage / ContactPage / WebPage | ✔ | pages |
| Cart / checkout / search / thank-you | fixed | | none | noindex, follow; robots Disallow for `/cart` `/checkout` `/search` | ✗ |
| 404 | `الصفحة غير موجودة \| النخيل كوول` | | none | noindex, status 404 | ✗ |

**Organization node** (`["HVACBusiness","Store"]`): `name`=النخيل كوول, `alternateName`=[شركة النخيل, Al Nakheel Cool],
`logo`, `image`, `url`, `telephone`=+201055207525, `contactPoint` (customer service, `ar`), `areaServed` = published areas,
`openingHoursSpecification` from settings **[Q6]**, `sameAs` (GBP + real social profiles only) **[Q3]**, `priceRange` **[Q]**.
`address`/`geo` only once confirmed **[Q5]**. Note: without `address` the business is not eligible for Google's
local-business rich result — that is expected for a service-area business; local ranking comes from the Business Profile.

### 6.3 Facet (curated) pages
- Above the grid: a compact **price table from live data** (model, HP, type, price, sale price) with «آخر تحديث» = latest
  real price change in scope — this is what lets facets own «أسعار تكييف …» queries (Q9). Prices are text in a `<table>`.
- Only the four path shapes in §4 exist; any other combination is reachable only via GET filters on `/store` (noindex).
- A facet is **indexable** iff: `facet_pages` row published ∧ unique intro filled ∧ ≥ 3 live (published, not discontinued) products.
  Otherwise it still renders (200, useful for users) with `noindex, follow` and is omitted from the sitemap.
- Filter UI = `<form method="get">`; checkboxes/selects, not links. Progressive enhancement: `fetch` + `history.replaceState`.
- Discontinued product → 301 to brand × capacity facet if indexable, else capacity facet, else brand facet, else `/store`.

### 6.4 Sitemaps, robots, IndexNow
- `/sitemap.xml` = index → `pages`, `products` (with image entries), `facets`, `services`, `areas`, `prices`, `posts`, `projects`.
  Only indexable, published, canonical, 200 URLs. No `priority`/`changefreq`. Files written to `public/sitemaps/` by a
  queued `GenerateSitemaps` job (dispatched debounced on publish/update, plus daily at 03:10).
- The SEO test asserts sitemap URLs == set of indexable URLs discovered by the crawler.
- `robots.txt` (dynamic): production → `User-agent: *` `Allow: /` + `Disallow: /admin`, `/cart`, `/checkout`, `/search`
  + `Sitemap:`; never blocks CSS/JS/images or Googlebot, Bingbot, OAI-SearchBot, PerplexityBot. Non-production → `Disallow: /`.
- IndexNow: key in settings, key file route, queued ping to `api.indexnow.org` on publish/update/unpublish (production only).

### 6.5 Local SEO & E-E-A-T
- NAP from one settings source; the same `NapBlock` component everywhere; «قيّمنا على جوجل» button → GBP review link **[Q3]**.
- Area publish guard (see schema); no area × service matrix; area pages link to 3–6 nearby areas.
- About page: real history, team (people table), warranty & returns policies linked sitewide in footer.
- Stock photos allowed only as temporary placeholders, tracked in `TODO.md`; real photos replace them before/after launch.
- Author bios with credentials; posts can have `reviewed_by` a technician.

### 6.6 Internal linking plan
- Home → services, store hub, top facets, prices hub, areas hub, latest posts/projects.
- Service ↔ matching price guide ↔ areas where offered (from `area_service`); service → related projects & reviews.
- Product → its brand, capacity, brand×capacity facets, price guide, 4 alternatives; facet → price guide & calculator.
- Area → nearby areas, its services, local projects/reviews. Post → 1–3 money pages (admin field) + product embeds.
- Footer: hubs + `show_in_footer` areas only. All anchors descriptive Arabic (no «اضغط هنا»).
- P5 test: zero broken internal links; every indexable page has ≥ 1 internal inbound link (no orphans).

### 6.7 Search
- Normalization (applied to stored `search_text` and to queries): أ/إ/آ→ا, ة→ه, ى→ي, strip tashkeel (U+064B–U+0652, U+0670)
  and tatweel (U+0640), Arabic-Indic digits → Western, collapse spaces. `LIKE` across normalized columns (catalog is small);
  results page `noindex, follow`.

### 6.8 Analytics & verification
- GA4 (`gtag`, `async`) or GTM from settings; loaded after first paint; no render blocking.
- Events via `data-track` attributes: `click_call`, `click_whatsapp`, `view_item`, `add_to_cart`, `begin_checkout`,
  `purchase` (thank-you), `generate_lead` (booking/contact success).
- `google-site-verification`, `msvalidate.01` meta tags from settings.

### 6.9 Security (a hacked site serving spam wipes out rankings)
- Filament panel: MFA **required** for every admin (TOTP app authentication, recovery codes); login rate-limited
  (Filament’s throttle + `RateLimiter` 5/min per IP+email); admin at `/admin`, disallowed in robots, `noindex` header.
- `SecurityHeaders` middleware: `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`,
  `X-Frame-Options: SAMEORIGIN`, `Permissions-Policy` (camera/mic/geolocation off), `Cross-Origin-Opener-Policy: same-origin`;
  **HSTS** only when `SECURITY_HSTS=true` (enable after HTTPS is verified); **CSP** in `Content-Security-Policy-Report-Only`
  by default (`SECURITY_CSP_ENFORCE=false`), report endpoint logs violations; switch to enforce after a clean week.
- Public forms: CSRF, honeypot, throttling; admin rich text sanitized (purifier); uploads restricted to images.
- Backups: spatie/laravel-backup scheduled daily (DB + `storage/app/public` media) to `BACKUP_DISK` (S3-compatible
  off-server disk from `.env`), cleanup + monitor, failure mail to the notification address.
- `.env`, keys and credentials never committed (`.gitignore` + a test that fails if `.env` is tracked).

---

## 7. Keyword → page map (one primary intent per page)

Each record stores its `primary_keyword`; the SEO dashboard flags two pages sharing one. Variants are covered naturally in
body copy and headings — no stuffing.

| Page | Primary query (intent) | Natural variants covered on the same page |
|---|---|---|
| `/` | شركة تكييفات النخيل كوول / بيع وتركيب وصيانة تكييفات (brand + commercial) | شركة تكييف في مصر |
| `/store` | شراء تكييف أونلاين (transactional) | محل تكييفات، تكييف بالتقسيط **[Q8]** |
| `/store/brand/{brand}` | تكييف {brand} + أسعار تكييف {brand} (commercial) | موديلات، توكيل **[Q7]** |
| `/store/capacity/1-5-hp` | تكييف 1.5 حصان + سعر تكييف 1.5 حصان | تكييف حصان ونص، 12000 وحدة |
| `/store/capacity/2-25-hp` | تكييف 2.25 حصان | تكييف 2 وربع حصان، 18000 وحدة |
| `/store/capacity/3-hp` · `4-hp` · `5-hp` | تكييف 3 / 4 / 5 حصان | colloquial plurals «تكييف 3 حصنة»، «4 حصنة»، «5 حصنة» |
| `/store/type/split` … | تكييف سبليت / شباك / كونسيلد / كاسيت / دولابي | |
| `/store/brand/{brand}/{hp}` | تكييف {brand} {hp} حصان | سعر |
| `/store/{product}` | {brand} {model} (navigational/transactional) | سعر، مواصفات |
| `/services/ac-maintenance` | صيانة تكييف (service) | تصليح تكييف، فني تكييف، شركة صيانة تكييفات، أعطال التكييف |
| `/services/ac-installation` | تركيب تكييف | فني تركيب تكييفات، تركيب تكييف سبليت |
| `/services/ac-preparation` | تأسيس تكييف | تأسيس مواسير تكييف، مواسير نحاس، تمديدات، تجهيزات التكييف |
| `/services/ac-cleaning` | غسيل تكييف | تنظيف تكييف، تنظيف فلتر التكييف، تنظيف الوحدة الخارجية |
| `/services/freon-recharge` | شحن فريون تكييف | تسريب فريون، تكييف مش بيسقع (links to post) |
| `/services/ac-relocation` | فك وتركيب تكييف / نقل تكييف | فك تكييف |
| `/services/maintenance-contracts` | عقد صيانة تكييفات (B2B) | صيانة دورية للشركات والمباني |
| `/services/emergency-ac-repair` | فني تكييف طوارئ 24 ساعة (**only if 24/7 is real, [Q6]**) | فني تكييف دلوقتي |
| `/areas/{slug}` | صيانة تكييف في {area} / فني تكييف {area} | تركيب تكييف {area} |
| `/prices` | أسعار التكييفات في مصر {year} (cross-brand comparison) | |
| `/prices/ac-installation-cost` | سعر/تكلفة تركيب تكييف | مصنعية تركيب تكييف، سعر متر النحاس |
| `/prices/ac-maintenance-cost` | سعر صيانة تكييف | سعر غسيل تكييف، سعر شحن فريون (sections, not separate pages, until demand proves otherwise) |
| `/prices/{brand}-ac-prices`, `/prices/{hp}-ac-prices` | see **[Q9]** — cannibalization risk with facet pages | |
| `/reviews` | آراء عملاء النخيل كوول | |
| `/projects` | مشاريع تكييف مركزي/تجاري (B2B trust) | |
| Blog | informational — see below | |

**Rule for price vs. store pages [Q9]:** the brand template in the brief (“الأسعار والموديلات”) makes the brand/capacity facet the
natural owner of “أسعار تكييف {brand}” and “سعر تكييف {hp} حصان”. A separate `/prices/sharp-ac-prices` would compete
for the same query. **Recommendation:** facets own brand/capacity price queries; `/prices` guides cover what a product
grid can’t: cross-brand comparison (`/prices`), service costs (installation, maintenance, freon, preparation per meter),
and at most brand price guides that are genuinely different (e.g. a full comparison table + price history) with the
facet linking to it. If you prefer brand price guides, the facet title drops “الأسعار” instead.

### 7.1 Twenty blog topics (Egyptian search phrasing → money page it supports)
1. التكييف مش بيسقع: الأسباب والحل خطوة بخطوة → ac-maintenance, freon-recharge
2. التكييف بينقط مية من الوحدة الداخلية: ليه وإزاي تتصرف → ac-maintenance
3. تكييف 1.5 حصان يكفي كام متر؟ (مع حاسبة الحصان) → capacity facets, calculator
4. الفرق بين التكييف الإنفرتر والعادي: هل بيوفر كهرباء فعلًا؟ → store (inverter filter)
5. التكييف بيصرف كام كهرباء في الشهر؟ حساب الاستهلاك بالشرائح → store
6. علامات نقص الفريون وإمتى التكييف يحتاج شحن → freon-recharge
7. فريون R410 وR32 وR22: إيه الفرق وأنهي مناسب لتكييفك؟ → freon-recharge
8. أكواد أعطال تكييف شارب ومعناها → ac-maintenance, brand facet
9. أكواد أعطال تكييف كاريير ومعناها → ac-maintenance, brand facet
10. إزاي تنضف فلتر التكييف في البيت (ومتى تحتاج غسيل كامل) → ac-cleaning
11. ريحة وحشة طالعة من التكييف: الأسباب والعلاج → ac-cleaning
12. التكييف بيفصل لوحده بعد شوية: أشهر الأسباب → ac-maintenance
13. التكييف بيطلع صوت عالي من الداخلية أو الخارجية → ac-maintenance
14. أحسن مكان تركّب فيه التكييف في الأوضة والوحدة الخارجية → ac-installation
15. طول مواسير التكييف وقطرها: إيه الصح وإيه اللي بيبوّظ الأداء → ac-preparation, ac-installation-cost
16. تأسيس التكييف قبل التشطيب: كل اللي لازم تعرفه → ac-preparation
17. أحسن درجة حرارة للتكييف في الصيف توفّر كهرباء → store, blog
18. قبل ما تشتري تكييف: 10 أسئلة اسألها للبياع (ضمان، تركيب، توكيل) → store, warranty
19. شارب ولا كاريير ولا يونيون إير: مقارنة لتكييفات 1.5 حصان → brand facets (brands per [Q7])
20. صيانة التكييف قبل الصيف: قائمة مراجعة ومتى تحتاج فني → ac-maintenance, maintenance-contracts

Posts are written only with facts we can support (manufacturer manuals for error codes, cited tariffs for electricity).

---

## 8. Design system (spec §7, until/unless `./design` arrives [Q1])

- Tokens as CSS custom properties: `--t45 #E73F1E`, `--t38 #FB6C00`, `--t30 #F9B637`, `--t24 #FFDD9C`, `--ink #1F120C`,
  `--paper #FFF6E8` + spacing/radius/shadow scales; logical properties only (`margin-inline-start`, `inset-inline-end`…).
  Contrast rules enforced by a unit test over token pairs (text on `--t38/--t30` = `--ink`; `--t45` large text only).
- Fonts (self-hosted woff2, `font-display: swap`): Changa 700/800 (headings), IBM Plex Sans Arabic 400/500/600,
  Handjet subset to `0-9 ° . , % -` for LCD digits. Subsetting via `pyftsubset` script (Arabic + Latin basic + digits).
  Preload only Changa 800 + Plex 400. Western digits everywhere. No `letter-spacing` on Arabic (lint rule in CSS test).
- Blade components (P1 unless noted): `layout`, `header` (louver top bar, logo: date palm over 4 stripes, SVG),
  `nav`, `breadcrumbs`, `footer`, `mobile-action-bar` (اتصل / واتساب / السلة), `louver-divider`, `lcd` (number panel),
  `button`, `stamp`, `coupon`, `card-*`, `faq` (`<details>`), `nap`, `cta-band`, `booking-form` (P3), `picture`
  (AVIF/WebP srcset, width/height, lazy/eager+fetchpriority), `split-flap-board` (P3, whole-row flip, CSS only),
  `remote-filter-panel` (P2, bottom sheet on mobile), `product-card` (P2), `calculator` (P2), `review-card` (P4),
  `map-facade`, `youtube-facade`.
- Homepage “cools from 45° to 24°” = section backgrounds stepping through `--t45 → --t24` with a sticky LCD readout
  (decorative, `aria-hidden`); scroll-driven animation in CSS (`animation-timeline: view()`) with static fallback.
- Louver intro: CSS only, ≤ 600 ms, once per session (sessionStorage flag set by a 300-byte inline script),
  disabled under `prefers-reduced-motion`; animates `transform`/`clip-path` of an overlay — the H1 is painted from first
  frame, never `opacity: 0`. Heat haze (SVG filter) only at `(min-width: 1024px) and (hover: hover)`.
- Accessibility: WCAG 2.2 AA, visible `:focus-visible`, 44×44 targets, decorative elements `aria-hidden`, skip link.

---

## 9. Packages (verified against Packagist, Sep 2026)

| Package | Version | Why |
|---|---|---|
| laravel/framework | ^13.33 | core (PHP ^8.3) |
| filament/filament | ^5.9 | admin (Laravel 11–13) |
| filament/spatie-laravel-media-library-plugin, filament/spatie-laravel-settings-plugin | ^5.9 | admin integrations |
| spatie/laravel-sitemap | ^8.2 | sitemaps (PHP ^8.4) |
| spatie/schema-org | ^5.0 | typed JSON-LD builders (PHP ^8.4) |
| spatie/laravel-medialibrary | ^11.23 | images, AVIF/WebP conversions, responsive images (GD has AVIF+WebP here) |
| spatie/laravel-responsecache | ^8.4 | guest full-page cache (PHP ^8.4) |
| spatie/laravel-settings | ^3.9 | typed settings |
| spatie/laravel-sluggable | ^4.0 | slug generation (history handled by our observer) |
| spatie/laravel-honeypot | ^4.7 | spam protection on public forms |
| spatie/laravel-backup | ^10.3 | **on by default**: daily DB + media to an off-server disk configured in `.env` |
| intervention/image | ^4.3 | OG image rendering |
| khaled.alshamaa/ar-php | ^7.0 | Arabic glyph shaping for GD text in OG images |
| mews/purifier | ^3.4 | sanitize admin rich text |
| pestphp/pest (+ laravel plugin) | ^5.2 | tests (PHP ^8.4) |
| larastan/larastan | ^3.12 | static analysis (level 6 target) |
| laravel/pint | ^1.32 | formatting |

Dev tooling: Vite + `lightningcss` (no Tailwind, hand-written tokens/components keep CSS small), `@lhci/cli` for Lighthouse
runs in P5 against a production build using the pre-installed Chromium. **Minimum PHP: 8.4.**

---

## 10. Phases (each ends with green Pest + Pint + Larastan and a commit)

**P1 — Foundation & homepage**
Laravel + Filament install, move old site to `legacy/old-site/`, settings, design tokens + fonts + core components,
layout, SEO foundation (`SeoData` builder, title/description templates, canonical & robots resolvers, JSON-LD graph,
OG image generator, sitemap framework, dynamic robots.txt, `CanonicalizeRequest`, redirects + slug history, 404 + log,
non-production noindex), security baseline (§6.9: admin MFA, throttling, headers, backups), homepage with draft copy (C4 H1), about/contact skeletons, CLAUDE.md (step 3), SEO crawler test harness.

**P2 — Catalog & commerce**
Brands, products (+ CSV import in admin), price change log, facet pages + indexability rule, filters (GET form + PE),
product page (schema, alternatives, out-of-stock/discontinued behavior), HP calculator, session cart, phone-first
checkout, `PaymentGateway` + COD, order notifications, GA4 e-commerce events, Arabic search.

**P3 — Services, areas, prices, bookings**
Service pages (structured sections), areas with publish guard + nearby areas + split-flap board, price guides from live
data (“آخر تحديث” logic), booking form on service/area/contact, admin notifications, IndexNow.

**P4 — Trust & content**
Reviews (submission, moderation, placement), blog (categories, authors/ProfilePage, TOC, reading time, related posts,
product embeds), projects, static policy pages, admin SEO panel (counters, RTL SERP preview), redirects manager,
404 log with suggestions, SEO warnings dashboard.

**P5 — Performance & launch**
Image pipeline audit, response cache tuning, headers (immutable hashed assets, Brotli/gzip), Lighthouse CI on the 7 page
types, full SEO crawl (links, orphans, sitemap parity, redirect map), GitHub Pages redirect stubs, README (local setup,
shared hosting + VPS deploy, cron/queue, launch checklist), `TODO.md` of placeholders.

Copy (workflow step 5): Arabic drafts for home, about and each service page are delivered in P1/P3 as **unpublished**
records + a review file, with every unknown marked `[TODO]`. Seeders: `DemoSeeder` (dev only, never runs in production,
everything `is_published=false`) and `LegacyContentSeeder` (migrated copy, unpublished until your review).

---

## 11. Content honesty rules (will be codified in CLAUDE.md)
- No stats, years, team sizes, prices, testimonials, projects, certifications or response times unless you confirm them.
- Unknowns in copy are literal `[TODO: …]`; a test fails if `[TODO` appears in any **published** record or rendered page.
- The old testimonials (أحمد علي، منى سعيد، سامي حسين) and stats are dropped unless confirmed real.

---

## 12. Open questions

| # | Question | Default if you don't answer |
|---|---|---|
| Q1 | No `./design` folder in the repo. Is a Claude Design export coming, or do I build from spec §7? | Build from §7 |
| Q2 | Production domain? (needed for APP_URL, OG, sitemap, GBP website field). www or non-www? | non-www, placeholder until known |
| Q3 | Google Business Profile URL + review link; real social profiles (Facebook/Instagram/TikTok/YouTube)? The old site links to none | Omit `sameAs`, hide review button |
| Q4 | Public email: `elnakheel55@gmail.com`? The old site also shows **01207720574** (about page) and `hanymahmoud81984@gmail.com` (contact mailto). Publish either? | Only 01055207525; email hidden until confirmed |
| Q5 | Is التجمع الخامس – شارع التسعين a real public location (office/showroom customers can visit) or just a pin? | Service-area business, no address |
| Q6 | Opening hours? Is 24/7 support/emergency real (decides `/services/emergency-ac-repair`)? | Hours `[TODO]`, no 24/7 claims, emergency page unpublished |
| Q7 | Which brands do you sell, and are you an authorized dealer for any? Which types (central/VRF/concealed/cassette) do you actually install and service? | Only brands you list; no “وكيل معتمد” claims |
| Q8 | Is installation included in product prices? Delivery areas/fees/time, return window, installments providers (valU, Souhoola, bank cards)? | Show “التركيب يُحسب منفصل” `[TODO]`; no shipping/return schema until confirmed |
| Q9 | Price guides for brands/capacities vs. facet pages owning those queries (see §7) | Recommendation in §7 |
| Q10 | Hosting: which provider, shared (cPanel) or VPS? Does it offer PHP 8.4 + MySQL 8, SSH, cron? | Target PHP 8.4, test both deploy paths |
| Q11 | Keep the Laravel app in this repo (old site moved to `legacy/`, merge to `main` only at launch, Pages switched to a `gh-pages` branch with the stubs), or a new private repo? | This repo, as described |
| Q12 | Service areas list (start with 5–10 where you really work, with local details for each) | Areas hub live, individual areas unpublished until filled |
| Q13 | Real facts for E-E-A-T: founding year, founder/owner name, technicians (names, years, photos, certifications), real project details/photos (old captions: commercial building, admin office, hospital, hotels, pipe groundwork — are they real?) | `[TODO]` placeholders, projects unpublished |
| Q14 | Starting prices for services (maintenance visit, cleaning, freon, installation, preparation per meter)? | Show “السعر بعد المعاينة” until provided |
| Q15 | Copy tone: simplified MSA for headings/meta with light Egyptian colloquial in body and CTAs (like the old service pages)? | Yes |
| Q16 | Where should order/booking notifications go (email address, WhatsApp number)? SMTP provider? | Filament database notifications + `MAIL_*` from `.env` |
| Q17 | Calculator coefficients (BTU per m², sun and top-floor multipliers): do your technicians use a rule of thumb? | Configurable defaults flagged `[TODO confirm]` |
