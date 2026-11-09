// Форма уже подготовлена PHP. JS лишь показывает её как модальное окно.
(function () {
    'use strict';
    function init() {
    const editor = document.getElementById('review-editor');
    if (!editor) return;
    if (editor.dataset.reviewReady) return;
    editor.dataset.reviewReady = 'true';
    const body = editor.querySelector('.review-editor-body');
    const closeButton = editor.querySelector('[data-close-review]');
    const summary = editor.querySelector('summary');
    let previousFocus;
    let wasOpen = false;

    editor.classList.add('is-enhanced');
    closeButton.hidden = false;

    function update() {
        if (wasOpen === editor.open) return;
        wasOpen = editor.open;
        document.documentElement.classList.toggle('review-open', editor.open);
        if (editor.open) {
            previousFocus = body.contains(document.activeElement) ? summary : document.activeElement;
            body.setAttribute('role', 'dialog');
            body.setAttribute('aria-modal', 'true');
            const focusable = body.querySelector('input:not([type="hidden"]), textarea, button');
            focusable?.focus();
        } else {
            body.removeAttribute('role');
            body.removeAttribute('aria-modal');
            if (previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
        }
    }

    closeButton.addEventListener('click', () => { editor.open = false; });
    editor.addEventListener('toggle', update);
    body.addEventListener('keydown', event => {
        if (event.isComposing) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            editor.open = false;
        } else if (event.key === 'Tab') {
            const controls = Array.from(body.querySelectorAll('input:not([type="hidden"]), textarea, button, a[href]'))
                .filter(control => !control.disabled && control.getClientRects().length);
            const index = controls.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? controls.length - 1 : index - 1) : (index + 1) % controls.length;
            event.preventDefault();
            controls[next]?.focus();
        }
    });
    update();
    }
    document.addEventListener('click', event => {
        const editor = document.getElementById('review-editor');
        if (editor?.open && !editor.querySelector('.review-editor-body').contains(event.target) && !editor.querySelector('summary').contains(event.target)) editor.open = false;
    });
    init();
    document.addEventListener('store:updated', init);
})();
