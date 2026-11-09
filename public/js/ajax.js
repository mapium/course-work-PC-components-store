// PHP возвращает данные действий и готовую HTML-разметку. JS меняет только представление.
(function () {
    'use strict';
    const base = document.querySelector('meta[name="app-url"]').content;
    const requests = new Map();
    function localUrl(value) {
        const url = new URL(value, location.href);
        const root = new URL(base);
        if (url.origin !== root.origin || !(url.pathname === root.pathname || url.pathname.startsWith(`${root.pathname}/`))) throw new Error('Недопустимый адрес');
        return url;
    }
    async function request(url, options = {}) {
        const response = await fetch(localUrl(url), { credentials: 'same-origin', cache: 'no-store', ...options,
            headers: { 'X-Requested-With': 'XMLHttpRequest', ...options.headers } });
        const type = response.headers.get('Content-Type') || '';
        if (!type.includes('application/json')) throw new Error('Не удалось получить ответ сервера. Повторите действие.');
        const data = await response.json();
        if (!response.ok || data.success === false) {
            const error = new Error(data.message || 'Не удалось выполнить действие');
            error.errors = data.errors || {};
            error.status = response.status;
            throw error;
        }
        return data;
    }
    function metadata(doc) {
        document.head.querySelectorAll('[data-store-meta]').forEach(node => node.remove());
        doc.head.querySelectorAll('[data-store-meta]').forEach(node => document.head.append(document.importNode(node, true)));
    }
    async function refresh(url, selector = 'main', options = {}) {
        const target = document.querySelector(selector);
        if (!target) return;
        requests.get(selector)?.abort();
        const controller = new AbortController();
        requests.set(selector, controller);
        target.setAttribute('aria-busy', 'true');
        const scroll = [scrollX, scrollY];
        const focused = target.contains(document.activeElement) ? document.activeElement : null;
        const focusId = focused?.id;
        try {
            const response = await fetch(localUrl(url), { credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
            if (!response.ok || !response.headers.get('Content-Type')?.includes('text/html')) throw new Error('Не удалось обновить страницу. Повторите действие.');
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            const replacement = doc.querySelector(selector);
            if (!replacement || controller.signal.aborted) throw new Error('Раздел недоступен. Обновите страницу.');
            target.replaceWith(document.importNode(replacement, true));
            document.documentElement.classList.remove('review-open');
            metadata(doc);
            const canonicalState = doc.querySelector('[data-catalog-url]')?.dataset.catalogUrl;
            const nextUrl = localUrl(canonicalState || url);
            if (options.history === 'push') history.pushState({ store: selector }, '', nextUrl);
            if (options.history === 'replace') history.replaceState({ store: selector }, '', nextUrl);
            document.dispatchEvent(new CustomEvent('store:updated', { detail: { selector } }));
            if (focusId && (!document.activeElement || document.activeElement === document.body)) document.getElementById(focusId)?.focus({ preventScroll: true });
            if (options.scroll) document.querySelector(options.scroll)?.scrollIntoView({ block: 'start' });
            else window.scrollTo(...scroll);
        } finally {
            if (requests.get(selector) === controller) {
                document.querySelector(selector)?.removeAttribute('aria-busy');
                requests.delete(selector);
            }
        }
    }
    function showError(error) {
        if (error.name !== 'AbortError') window.StoreNotify(error.message || 'Ошибка соединения. Повторите действие.', 'error');
    }
    function cancel(selector) { requests.get(selector)?.abort(); }
    window.Store = { base, request, refresh, localUrl, showError, cancel };
})();
