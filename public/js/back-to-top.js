(function () {
    'use strict';
    // Делегирование сохраняет работу кнопки после AJAX-обновлений. Ссылка работает и без JS.
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-back-to-top]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) return;
        event.preventDefault();
        document.getElementById('page-top')?.focus({ preventScroll: true });
        window.scrollTo({ top: 0, left: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    });
})();
