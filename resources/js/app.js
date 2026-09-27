// Public-site JS: small vanilla modules, progressive enhancement only (the site works without JS).
import.meta.glob(['../fonts/web/*.woff2', '../images/**'], { eager: false });

import { initNav } from './modules/nav.js';
import { initFacades } from './modules/facades.js';
import { initAnalytics } from './modules/analytics.js';
import { initFilters } from './modules/filters.js';
import { initCartBadge } from './modules/cart-badge.js';

initNav();
initFacades();
initAnalytics();
initFilters();
initCartBadge();
