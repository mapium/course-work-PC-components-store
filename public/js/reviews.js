(function () {
    'use strict';
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-review-link]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        Store.refresh(link.href, 'main', { history: 'push' }).catch(Store.showError);
    });
    window.addEventListener('popstate', () => {
        if (document.querySelector('[data-product-page]')) Store.refresh(location.href, 'main').catch(Store.showError);
    });
})();
