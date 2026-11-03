// One server-rendered navigation; native dialog supplies modal focus containment and inert background.
const dialog = document.querySelector('#mobile-menu');
const opener = document.querySelector('[aria-controls="mobile-menu"]');
const nav = document.querySelector('.site-header__nav');
if (dialog && opener && nav && typeof dialog.showModal === 'function') {
    const mobile = matchMedia('(max-width: 40rem)');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const slot = document.createComment('navigation');
    nav.before(slot);
    let timer;
    let frame;
    let scrollPosition;
    let closing = false;
    let previousFocus;
    let restoreFocus = true;
    const bodyProperties = ['position', 'top', 'left', 'width', 'paddingRight'];
    let savedStyles;

    const finishClose = () => {
        clearTimeout(timer);
        cancelAnimationFrame(frame);
        dialog.classList.remove('is-open');
        if (dialog.open) dialog.close();
        closing = false;
        if (savedStyles) {
            for (const key of bodyProperties) document.body.style[key] = savedStyles[key];
            savedStyles = null;
            window.scrollTo(0, scrollPosition);
        }
        opener.setAttribute('aria-expanded', 'false');
    };
    const close = (immediate = false, restore = true) => {
        if (!dialog.open || closing && !immediate) return;
        restoreFocus = restore;
        closing = true;
        cancelAnimationFrame(frame);
        dialog.classList.remove('is-open');
        if (immediate || reducedMotion.matches) finishClose();
        else timer = setTimeout(finishClose, 220);
    };
    opener.addEventListener('click', () => {
        if (!mobile.matches || dialog.open) return;
        previousFocus = opener;
        scrollPosition = window.scrollY;
        savedStyles = Object.fromEntries(bodyProperties.map(key => [key, document.body.style[key]]));
        const scrollbar = innerWidth - document.documentElement.clientWidth;
        const padding = parseFloat(getComputedStyle(document.body).paddingRight) || 0;
        Object.assign(document.body.style, { position: 'fixed', top: `-${scrollPosition}px`, left: '0', width: '100%', paddingRight: `${padding + scrollbar}px` });
        dialog.showModal();
        opener.setAttribute('aria-expanded', 'true');
        // Resolve the initial translated position before applying the opening state.
        dialog.getBoundingClientRect();
        frame = requestAnimationFrame(() => dialog.classList.add('is-open'));
    });
    dialog.querySelector('.mobile-navigation__close').addEventListener('click', () => close());
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
    dialog.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const controls = [...dialog.querySelectorAll('button:not(:disabled), a[href]')];
        const first = controls[0];
        const last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault(); last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault(); first.focus();
        }
    });
    dialog.addEventListener('click', event => {
        if (event.target === dialog) close();
        if (event.target instanceof Element && event.target.closest('a[href]')) close(true, false);
    });
    dialog.addEventListener('close', () => {
        if (!restoreFocus || dialog.open) return;
        const target = mobile.matches ? previousFocus : nav.querySelector('a');
        // Link default actions retain their own subsequent anchor focus and scrolling.
        target?.focus({ preventScroll: true });
    });
    const synchronize = () => {
        const focused = dialog.contains(document.activeElement) || document.activeElement === opener;
        close(true);
        if (mobile.matches) {
            dialog.querySelector('.mobile-navigation__links').append(nav);
            opener.hidden = false;
        } else {
            slot.after(nav);
            opener.hidden = true;
            if (focused) nav.querySelector('a')?.focus({ preventScroll: true });
        }
    };
    if (location.pathname === '/') nav.querySelector('a[href="/"]')?.setAttribute('aria-current', 'page');
    window.addEventListener('pagehide', () => close(true, false));
    mobile.addEventListener('change', synchronize);
    synchronize();
}
