/**
 * Glow Halal storefront — progressive enhancement only.
 *
 * Nothing here renders content. Every word on the page is in the server HTML;
 * this file only adds behaviour on top of it. If it fails to load, the site
 * degrades to: menu closed, footer sections expanded, theme = the value the
 * inline <head> script already resolved. All three are usable states.
 *
 * No framework, no dependencies. This ships on a congested Pakistani cell to a
 * device with a small CPU budget, and it is the last thing that should be
 * competing with the hero image for either.
 */

/* ------------------------------------------------------------------ */
/* Mobile menu — a bottom sheet (§5.2)                                  */
/* ------------------------------------------------------------------ */

(function mobileMenu() {
    const sheet = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    const trigger = document.getElementById('menu-button');

    if (!sheet || !overlay || !trigger) return;

    const FOCUSABLE =
        'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

    let lastFocused = null;

    function open() {
        lastFocused = document.activeElement;
        sheet.hidden = false;
        overlay.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        document.documentElement.classList.add('scroll-locked');

        const first = sheet.querySelector(FOCUSABLE);
        if (first) first.focus();
    }

    function close() {
        sheet.hidden = true;
        overlay.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        document.documentElement.classList.remove('scroll-locked');

        // Focus returns to the control that opened the sheet, never to <body>.
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    trigger.addEventListener('click', () => (sheet.hidden ? open() : close()));

    document.querySelectorAll('[data-menu-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (sheet.hidden) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        // Focus trap.
        if (event.key !== 'Tab') return;

        const items = Array.from(sheet.querySelectorAll(FOCUSABLE)).filter(
            (el) => el.offsetParent !== null
        );
        if (items.length === 0) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    // A resize past the breakpoint would leave a dangling scroll lock.
    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches && !sheet.hidden) close();
    });
})();

/* ------------------------------------------------------------------ */
/* Footer accordions (§5.4)                                             */
/* ------------------------------------------------------------------ */

(function footerAccordions() {
    const blocks = document.querySelectorAll('[data-footer-accordion]');
    if (blocks.length === 0) return;

    const desktop = window.matchMedia('(min-width: 768px)');

    // Rendered `open` so that with JS disabled every link is visible and
    // crawlable. Collapse them only once we know we are on a narrow viewport.
    function sync() {
        blocks.forEach((block) => {
            block.open = desktop.matches;
        });
    }

    sync();
    desktop.addEventListener('change', sync);
})();

/* ------------------------------------------------------------------ */
/* Theme toggle (§1.5)                                                  */
/* ------------------------------------------------------------------ */

(function themeToggle() {
    const group = document.getElementById('theme-toggle');
    if (!group) return;

    const options = Array.from(group.querySelectorAll('[data-theme-option]'));
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const KEY = 'glowhalal:theme';

    function stored() {
        try {
            return localStorage.getItem(KEY) || 'system';
        } catch (e) {
            return 'system';
        }
    }

    function resolve(pref) {
        const dark = pref === 'dark' || (pref === 'system' && media.matches);
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme-pref', pref);
    }

    // Roving tabindex: the group is one tab stop, arrows move within it.
    function paint(pref) {
        options.forEach((option) => {
            const selected = option.dataset.themeOption === pref;
            option.setAttribute('aria-checked', selected ? 'true' : 'false');
            option.tabIndex = selected ? 0 : -1;
        });
    }

    function choose(pref) {
        try {
            localStorage.setItem(KEY, pref);
        } catch (e) {
            /* private mode — the choice simply does not persist */
        }
        resolve(pref);
        paint(pref);
    }

    options.forEach((option, index) => {
        option.addEventListener('click', () => choose(option.dataset.themeOption));

        option.addEventListener('keydown', (event) => {
            const step =
                event.key === 'ArrowRight' || event.key === 'ArrowDown'
                    ? 1
                    : event.key === 'ArrowLeft' || event.key === 'ArrowUp'
                      ? -1
                      : 0;
            if (step === 0) return;

            event.preventDefault();
            const next = options[(index + step + options.length) % options.length];
            next.focus();
            choose(next.dataset.themeOption);
        });
    });

    // When the preference is "system", follow the OS as it changes.
    media.addEventListener('change', () => {
        if (stored() === 'system') resolve('system');
    });

    paint(stored());
})();
