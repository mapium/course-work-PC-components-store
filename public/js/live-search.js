(function () {
    'use strict';
    const input = document.getElementById('search-dialog-input');
    const results = document.getElementById('search-results');
    const status = document.getElementById('search-status');
    if (!input || !results) return;
    let controller;
    let timer;
    let generation = 0;
    document.getElementById('search-dialog').addEventListener('search:open', () => {
        clearTimeout(timer);
        controller?.abort();
        load(++generation);
    });
    async function load(version) {
        controller = new AbortController();
        const current = controller;
        try {
            const url = new URL(`${Store.base}/api/search`);
            url.searchParams.set('q', input.value.trim());
            const data = await Store.request(url, { signal: current.signal });
            if (version !== generation) return;
            results.innerHTML = data.html;
            results.dispatchEvent(new Event('search:updated'));
            status.textContent = data.products.length ? `Найдено товаров: ${data.products.length}` : input.value ? 'Товары не найдены' : 'Популярные запросы';
        } catch (error) {
            if (error.name === 'AbortError' || version !== generation) return;
            results.replaceChildren();
            const message = document.createElement('p');
            message.className = 'search-message';
            message.textContent = 'Подсказки временно недоступны. Нажмите Enter для поиска в каталоге.';
            results.append(message);
            results.dispatchEvent(new Event('search:updated'));
            status.textContent = message.textContent;
        } finally {
            if (version === generation) results.removeAttribute('aria-busy');
        }
    }
    input.addEventListener('input', event => {
        clearTimeout(timer);
        controller?.abort();
        generation++;
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Поиск…';
        if (!event.isComposing) {
            const version = generation;
            timer = setTimeout(() => load(version), 220);
        }
    });
    input.addEventListener('compositionend', () => input.dispatchEvent(new Event('input')));
    document.getElementById('search-dialog').addEventListener('close', () => {
        clearTimeout(timer);
        controller?.abort();
        generation++;
        results.removeAttribute('aria-busy');
    });
})();
