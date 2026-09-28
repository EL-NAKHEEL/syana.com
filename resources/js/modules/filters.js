// Progressive enhancement for the store filters: fetch the filtered page, swap the results region and keep the
// URL in sync with history.replaceState. Without JS the GET form submits normally.
export function initFilters() {
    const form = document.querySelector('[data-filter-form]');
    const details = document.querySelector('[data-filters]');
    if (!form) return;

    if (details && window.matchMedia('(max-width: 61.99rem)').matches) details.open = false;
    if (details) details.dataset.ready = '';

    let controller;
    const update = async () => {
        const params = new URLSearchParams(new FormData(form));
        for (const [key, value] of [...params.entries()]) if (value === '' || (key === 'sort' && value === 'featured')) params.delete(key);
        const url = form.action + (params.toString() ? `?${params}` : '');

        const results = document.querySelector('[data-results]');
        controller?.abort();
        controller = new AbortController();
        results?.classList.add('is-loading');

        try {
            const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'text/html' } });
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fresh = doc.querySelector('[data-results]');
            if (results && fresh) results.replaceWith(fresh);
            history.replaceState(null, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') window.location.href = url;
        }
    };

    form.addEventListener('change', update);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        update();
    });
}
