(function () {
    'use strict';
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-admin] a[href]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target) return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || url.pathname !== new URL(`${Store.base}/admin`).pathname) return;
        event.preventDefault();
        Store.refresh(url, 'main', { history: 'push' }).catch(Store.showError);
    });
    window.addEventListener('popstate', () => {
        if (document.querySelector('[data-admin]')) Store.refresh(location.href, 'main').catch(Store.showError);
    });
})();
