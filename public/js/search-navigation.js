// Визуальное выделение готовых PHP-ссылок и перемещение по ним клавиатурой.
(function () {
    'use strict';
    const dialog = document.getElementById('search-dialog');
    const input = document.getElementById('search-dialog-input');
    const results = document.getElementById('search-results');
    if (!dialog || !input || !results) return;
    let options = Array.from(results.querySelectorAll('[role="option"]'));
    let activeIndex = -1;

    function select(index, scroll = false) {
        activeIndex = index;
        options.forEach((option, position) => option.setAttribute('aria-selected', String(position === index)));
        if (options[index]) {
            input.setAttribute('aria-activedescendant', options[index].id);
            if (scroll) options[index].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    input.addEventListener('input', () => select(-1));
    results.addEventListener('search:updated', () => {
        options = Array.from(results.querySelectorAll('[role="option"]'));
        select(-1);
    });
    dialog.addEventListener('close', () => select(-1));
    input.addEventListener('keydown', event => {
        if (event.isComposing || !options.length || results.getAttribute('aria-busy') === 'true') return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            const next = activeIndex < 0 ? (direction > 0 ? 0 : options.length - 1) : (activeIndex + direction + options.length) % options.length;
            select(next, true);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            options[activeIndex].click();
        }
    });
    results.addEventListener('pointermove', event => {
        const option = event.target.closest('[role="option"]');
        if (option) select(options.indexOf(option));
    });
    results.addEventListener('mousedown', event => {
        if (event.button === 0 && event.target.closest('[role="option"]')) event.preventDefault();
    });
})();
