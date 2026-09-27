// Google tag (GA4 / Google Ads) or GTM, injected after the page has loaded (never render-blocking) and only
// in production (the layout omits the meta tags elsewhere). Events use data-track="event_name" attributes.
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
    const ads = meta('nk-ads');
    const adsCall = meta('nk-ads-call');

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };

    const start = () => {
        if (gtm) {
            window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
            loadScript(`https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(gtm)}`);
        }
        if (ga4 || ads) {
            window.gtag('js', new Date());
            if (ga4) window.gtag('config', ga4);
            if (ads) window.gtag('config', ads);
            loadScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga4 || ads)}`);
        }
    };

    if (ga4 || gtm || ads) {
        if (document.readyState === 'complete') start();
        else window.addEventListener('load', start, { once: true });
    }

    // Conversion pages (thank-you) fire their event once on load.
    document.querySelectorAll('[data-track-onload]').forEach((el) => {
        const params = {};
        if (el.dataset.trackValue) Object.assign(params, { value: Number(el.dataset.trackValue), currency: 'EGP' });
        if (el.dataset.trackTransaction) params.transaction_id = el.dataset.trackTransaction;
        track(el.dataset.trackOnload, params);
    });

    document.addEventListener('click', (event) => {
        const el = event.target.closest('[data-track]');
        if (!el) return;

        const name = el.dataset.track;
        track(name, { link_url: el.href || undefined, location: el.dataset.trackLocation || undefined });

        // Same Google Ads call conversion as the existing site (value 1 EGP). The tel: link is not delayed.
        if (name === 'click_call' && adsCall) {
            window.gtag('event', 'conversion', { send_to: adsCall, value: 1.0, currency: 'EGP' });
        }
    });
}
