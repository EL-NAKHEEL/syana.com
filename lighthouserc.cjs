// Lighthouse CI on the page types (PLAN.md §10 P5). Targets from CLAUDE.md: Perf ≥ 90, A11y/BP ≥ 95, SEO 100.
// Run against a production-mode build (APP_ENV=production, tracking IDs empty):
//   LHCI_BASE_URL=https://staging.example.com npx @lhci/cli@0.14 autorun
// Pass real slugs with LHCI_SERVICE, LHCI_PRODUCT, LHCI_POST, LHCI_AREA, LHCI_GUIDE when they exist.
const base = (process.env.LHCI_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const paths = [
    '/',
    '/store',
    `/services/${process.env.LHCI_SERVICE || 'ac-maintenance'}`,
    process.env.LHCI_PRODUCT && `/store/${process.env.LHCI_PRODUCT}`,
    process.env.LHCI_POST && `/blog/${process.env.LHCI_POST}`,
    process.env.LHCI_AREA && `/areas/${process.env.LHCI_AREA}`,
    process.env.LHCI_GUIDE && `/prices/${process.env.LHCI_GUIDE}`,
    '/contact',
].filter(Boolean);

module.exports = {
    ci: {
        collect: {
            url: paths.map((path) => base + path),
            numberOfRuns: 3,
            settings: { chromeFlags: '--no-sandbox --headless=new' },
        },
        assert: {
            assertions: {
                'categories:performance': ['error', { minScore: 0.9 }],
                'categories:accessibility': ['error', { minScore: 0.95 }],
                'categories:best-practices': ['error', { minScore: 0.95 }],
                'categories:seo': ['error', { minScore: 1 }],
                'largest-contentful-paint': ['error', { maxNumericValue: 2500 }],
                'cumulative-layout-shift': ['error', { maxNumericValue: 0.1 }],
            },
        },
        upload: { target: 'filesystem', outputDir: 'storage/lighthouse' },
    },
};
