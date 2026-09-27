// Replaces a facade with its iframe on click (Google Maps, YouTube). Nothing loads before the click.
export function initFacades() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-facade-load]');
        if (!button) return;

        const facade = button.closest('[data-facade-src]');
        const iframe = document.createElement('iframe');
        iframe.src = facade.dataset.facadeSrc;
        iframe.title = facade.dataset.facadeTitle || '';
        iframe.loading = 'lazy';
        iframe.allow = 'accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        facade.replaceChildren(iframe);
    });
}
