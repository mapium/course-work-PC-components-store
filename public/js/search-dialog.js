// Только открытие окна поиска, управление фокусом и очистка поля.
(function () {
    'use strict';
    const dialog = document.getElementById('search-dialog');
    const trigger = document.getElementById('search-trigger');
    const input = document.getElementById('search-dialog-input');
    if (!dialog || !trigger || !input || typeof dialog.showModal !== 'function') return;
    const clear = document.getElementById('search-clear');
    let previousFocus;
    let backdropPointerDown = false;

    document.getElementById('search-fallback').hidden = true;
    trigger.hidden = false;
    if (/Mac|iPhone|iPad/i.test(navigator.userAgentData?.platform || navigator.platform)) {
        trigger.querySelector('[data-search-shortcut]').textContent = '⌘ K';
        trigger.setAttribute('aria-label', 'Поиск товаров (⌘+K)');
    }

    function open() {
        if (!dialog.open) {
            previousFocus = document.activeElement;
            dialog.showModal();
            document.documentElement.classList.add('search-open');
            trigger.setAttribute('aria-expanded', 'true');
            input.setAttribute('aria-expanded', 'true');
            clear.hidden = !input.value;
            dialog.dispatchEvent(new Event('search:open'));
        }
        input.focus({ preventScroll: true });
        input.select();
    }

    function isBackdrop(event) {
        if (event.target !== dialog) return false;
        const bounds = dialog.getBoundingClientRect();
        return event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom;
    }

    trigger.addEventListener('click', open);
    document.getElementById('search-close').addEventListener('click', () => dialog.close());
    clear.addEventListener('click', () => {
        input.value = '';
        clear.hidden = true;
        input.dispatchEvent(new Event('input'));
        input.focus();
    });
    input.addEventListener('input', () => { clear.hidden = !input.value; });
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && !event.altKey && !event.shiftKey && !event.isComposing &&
            (event.code === 'KeyK' || event.key.toLowerCase() === 'k')) {
            event.preventDefault();
            if (!event.repeat) open();
        }
    });
    dialog.addEventListener('pointerdown', event => { backdropPointerDown = isBackdrop(event); });
    dialog.addEventListener('click', event => {
        if (backdropPointerDown && isBackdrop(event)) dialog.close();
        backdropPointerDown = false;
    });
    dialog.addEventListener('close', () => {
        if (dialog.open) return;
        document.documentElement.classList.remove('search-open');
        trigger.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        if (previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
    });
    dialog.addEventListener('keydown', event => {
        if (event.isComposing) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            dialog.close();
        } else if (event.key === 'Tab') {
            const controls = Array.from(dialog.querySelectorAll('input, button, a[href]'))
                .filter(control => !control.disabled && control.tabIndex >= 0 && control.getClientRects().length);
            const current = controls.indexOf(document.activeElement);
            const next = event.shiftKey ? (current <= 0 ? controls.length - 1 : current - 1) : (current + 1) % controls.length;
            event.preventDefault();
            controls[next]?.focus();
        }
    });
})();
