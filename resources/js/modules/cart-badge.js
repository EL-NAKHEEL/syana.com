// Cached pages are user-agnostic: the cart badge is filled client-side from the cart_count cookie.
export function initCartBadge() {
    const link = document.querySelector('[data-cart-link]');
    if (!link) return;

    const match = document.cookie.match(/(?:^|;\s*)cart_count=(\d+)/);
    const count = match ? Number(match[1]) : 0;
    if (count > 0) {
        link.hidden = false;
        link.querySelector('[data-cart-count]').textContent = String(count);
    }
}
