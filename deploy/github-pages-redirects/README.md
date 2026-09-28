# GitHub Pages redirect stubs

Generated at launch, not committed before the production domain is known:

```bash
APP_URL=https://your-domain.example php artisan app:github-pages-stubs
```

This writes one stub per old page (`index.html`, `about.html`, `syana.html`, …) with a canonical link, an instant
meta refresh and a visible link to the new URL, plus `404.html` (old images and unknown paths → new home page,
`noindex`). Publish the folder as the GitHub Pages source (a `gh-pages` branch, PLAN.md Q11) so links and bookmarks
to the old `github.io` site keep working. The same map runs as real 301s on the new domain (`redirects` table).
