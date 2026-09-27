// GA4 / GTM are injected after the page is interactive (never render-blocking). IDs come from settings
// via <meta name="nk-ga4"> / <meta name="nk-gtm">. Events use data-track="event_name" attributes.
const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || '';

function loadScript(src) {
    const script = document.createElement('script');
    script.async = true;
    script.src = src;
    document.head.append(script);
}

export function track(event, params = {}) {
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag === 'function') {
        window.gtag('event', event, params);
    } else {
        window.dataLayer.push({ event, ...params });
    }
}

export function initAnalytics() {
    const ga4 = meta('nk-ga4');
    const gtm = meta('nk-gtm');

    const start = () => {
        window.dataLayer = window.dataLayer || [];
        if (gtm) {
            window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
            loadScript(`https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(gtm)}`);
        } else if (ga4) {
            window.gtag = function gtag() {
                window.dataLayer.push(arguments);
            };
            window.gtag('js', new Date());
            window.gtag('config', ga4);
            loadScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga4)}`);
        }
    };

    if (ga4 || gtm) {
        if (document.readyState === 'complete') start();
        else window.addEventListener('load', start, { once: true });
    }

    document.addEventListener('click', (event) => {
        const el = event.target.closest('[data-track]');
        if (el) track(el.dataset.track, { link_url: el.href || undefined, location: el.dataset.trackLocation || undefined });
    });
}
