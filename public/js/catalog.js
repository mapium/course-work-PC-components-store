(function () {
    'use strict';
    let timer;
    function apply(form) {
        if (!form.isConnected || !form.reportValidity()) return;
        const url = new URL(form.action);
        for (const [key, value] of new FormData(form)) if (value !== '') url.searchParams.append(key, value);
        Store.refresh(url, 'main', { history: 'push' }).catch(Store.showError);
    }
    document.addEventListener('input', event => {
        if (!event.target.matches('#catalog-filter-form input[type="number"]')) return;
        clearTimeout(timer);
        Store.cancel('main');
        timer = setTimeout(() => apply(event.target.form), 450);
    });
    document.addEventListener('change', event => {
        const form = event.target.form;
        if (form?.id !== 'catalog-filter-form' || event.target.type === 'number') return;
        clearTimeout(timer);
        if (event.target.name === 'category') {
            form.querySelector('[name="brand"]').value = '';
            form.querySelectorAll('[name^="spec["]').forEach(input => { input.checked = false; });
        }
        apply(form);
    });
    document.addEventListener('submit', event => {
        if (event.target.id !== 'catalog-filter-form') return;
        event.preventDefault();
        clearTimeout(timer);
        apply(event.target);
    });
    document.addEventListener('click', event => {
        const link = event.target.closest('[data-catalog-link]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) return;
        event.preventDefault();
        clearTimeout(timer);
        Store.refresh(link.href, 'main', { history: 'push', scroll: '.catalog-toolbar' }).catch(Store.showError);
    });
    window.addEventListener('popstate', () => {
        if (!document.querySelector('[data-catalog-url]')) return;
        clearTimeout(timer);
        Store.refresh(location.href, 'main').catch(Store.showError);
    });
})();
