// Native details remains usable if this optional enhancement cannot load.
const mobileViewport = window.matchMedia('(max-width: 40rem)');

for (const menu of document.querySelectorAll('[data-mobile-navigation]')) {
    const toggle = menu.querySelector('summary');
    const desktopNavigation = document.querySelector('.site-header__nav');
    let navigationFocus = null;

    // CSS can hide a focused link before matchMedia's change event fires.
    document.addEventListener('focusin', ({ target }) => {
        navigationFocus = menu.contains(target) || desktopNavigation?.contains(target) ? target : null;
    });

    const close = (restoreFocus = false) => {
        if (!menu.open) return;
        menu.open = false;
        if (restoreFocus) toggle.focus({ preventScroll: true });
    };

    menu.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.open) {
            event.preventDefault();
            close(true);
        }
    });

    menu.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a[href]')) {
            // Preserve the link's default navigation, including anchor focus.
            close();
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Node && !menu.contains(event.target)) {
            close(menu.contains(document.activeElement));
            navigationFocus = null;
        }
    });

    mobileViewport.addEventListener('change', ({ matches }) => {
        const active = document.activeElement === document.body ? navigationFocus : document.activeElement;
        close();
        if (!matches && menu.contains(active)) {
            desktopNavigation?.querySelector('a')?.focus({ preventScroll: true });
        } else if (matches && desktopNavigation?.contains(active)) {
            toggle.focus({ preventScroll: true });
        }
    });
}
