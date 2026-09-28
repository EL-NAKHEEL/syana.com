# TODO — facts to confirm before anything goes live

Nothing below is published until confirmed. Drafts carry `[TODO: …]` markers and cannot be published while any remain.

## Business facts (PLAN.md §12)
- [ ] Production domain → `APP_URL` (non-www)
- [ ] Google Business Profile URL + review link → admin › الإعدادات › بيانات النشاط
- [ ] Real social profiles (only owned accounts)
- [ ] Email to publish (draft: elnakheel55@gmail.com, hidden until «البريد مؤكَّد» is ticked)
- [ ] Public office? If yes, exact address as on Google Maps (otherwise service-area business)
- [ ] Opening hours; is 24/7 / emergency service real?
- [ ] Brands sold, authorized-dealer status, AC types installed and serviced
- [ ] Installation included in price? Delivery areas/fees/days, returns, installments
- [ ] Service areas (5–10 where you really work) with local details
- [ ] Founding year, owner name, technicians (names, years, photos), real project details and photos
- [ ] Starting prices per service (until then «السعر بعد المعاينة»)
- [ ] Notification email (`NOTIFY_EMAIL`) and SMTP settings
- [ ] Calculator coefficients (P2) — confirm with technicians

## Draft copy to review (admin › المحتوى › الصفحات)
- [ ] Home, About, Contact (source: `database/content/pages.php`)
- [ ] Policy pages: warranty, shipping & returns, privacy, terms (drafts with `[TODO]`; legal name, commercial
      register and tax card for the terms page)

## Blog, projects, reviews
- [ ] Real authors (admin › المدونة › الكتّاب والفريق): name, job, experience, real photo. Posts need a published author.
- [ ] First articles (topic list in PLAN.md §7); the blog hub stays `noindex` until 3 posts are live
- [ ] Real projects with the client's permission and real photos; hub `noindex` until 3
- [ ] Collect genuine reviews (form at `/reviews`, or ask past clients); every review is moderated before it shows

## Photos
- [ ] Real photos of the team, vans, installations and projects. Until then the design uses the old site's stock
      photos as optimized **temporary** placeholders (`public/images/placeholders/`, `resources/images/placeholders.json`);
      check their license/attribution or replace them before launch. The old `/img/*` URLs still return 410.
- [ ] A real logo file if you have one (the site uses the text block «النخيل كوول» like the existing site)

## Tracking
- [ ] Google Ads call conversion carried over (`AW-11415013969`); confirm the conversion label is still active
- [ ] Visitor-IP tracker (Google Sheet) from the old site: keep or drop? (PLAN.md Q18; dropped for now)
- [ ] Real numbers for the stats band (years, team, clients, projects) if you want it back

## Infrastructure
- [ ] Launch: follow the checklist in README.md › Launch checklist (includes the GitHub Pages redirect stubs)
- [ ] Hosting with PHP 8.4, MySQL 8, SSH, cron
- [ ] Off-server backup bucket (`BACKUP_DISKS=backups`, `BACKUP_S3_*`) and `BACKUP_ARCHIVE_PASSWORD`
- [ ] After HTTPS works: `SECURITY_HSTS=true`; after a clean week of CSP reports: `SECURITY_CSP_ENFORCE=true`
