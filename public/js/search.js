// Finder: quick product search, suggestions, and keyboard navigation.
(function() {
    'use strict';

    const dialog = document.getElementById('search-dialog');
    const trigger = document.getElementById('search-trigger');
    const input = document.getElementById('search-dialog-input');
    if (!dialog || !trigger || !input || typeof dialog.showModal !== 'function') return;

    const form = document.getElementById('search-dialog-form');
    const results = document.getElementById('search-results');
    const clearButton = document.getElementById('search-clear');
    const message = document.getElementById('search-message');
    const status = document.getElementById('search-status');
    const baseUrl = dialog.dataset.baseUrl.replace(/\/$/, '');
    const historyKey = 'pccomponents:search-history';
    const priceFormatter = new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 });
    const suggestions = [
        { title: 'Видеокарты RTX', icon: 'videogame_asset' },
        { title: 'Процессоры Ryzen', icon: 'memory' },
        { title: 'Оперативная память DDR5', icon: 'dns' },
        { title: 'SSD NVMe', icon: 'storage' }
    ];

    let options = [];
    let activeIndex = -1;
    let searchTimer;
    let activeRequest;
    let requestVersion = 0;
    let previousFocus;
    let backdropPointerDown = false;
    let composing = false;

    document.getElementById('search-fallback').hidden = true;
    trigger.hidden = false;

    if (/Mac|iPhone|iPad/i.test(navigator.userAgentData?.platform || navigator.platform)) {
        trigger.querySelector('[data-search-shortcut]').textContent = '⌘ K';
        trigger.setAttribute('aria-label', 'Поиск товаров (⌘+K)');
    }

    trigger.addEventListener('click', openSearch);
    document.getElementById('search-close').addEventListener('click', () => dialog.close());
    clearButton.addEventListener('click', () => {
        input.value = '';
        refreshSearch();
        input.focus();
    });

    document.addEventListener('keydown', event => {
        const isSearchKey = event.code === 'KeyK' || event.key.toLowerCase() === 'k';
        if ((event.ctrlKey || event.metaKey) && !event.altKey && !event.shiftKey && isSearchKey && !event.isComposing) {
            event.preventDefault();
            if (!event.repeat) openSearch();
        }
    });

    dialog.addEventListener('keydown', event => {
        if (event.isComposing || composing) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            dialog.close();
        } else if (event.key === 'Tab') {
            const focusable = Array.from(dialog.querySelectorAll('input, button, a[href], [tabindex]'))
                .filter(element => !element.disabled && element.tabIndex >= 0 && element.getClientRects().length > 0);
            const current = focusable.indexOf(document.activeElement);
            const next = current < 0 ? (event.shiftKey ? focusable.length - 1 : 0) :
                (current + (event.shiftKey ? -1 : 1) + focusable.length) % focusable.length;
            event.preventDefault();
            focusable[next]?.focus();
        } else if (event.target === input && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
            event.preventDefault();
            if (options.length) {
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const nextIndex = activeIndex < 0 ? (direction === 1 ? 0 : options.length - 1) :
                    (activeIndex + direction + options.length) % options.length;
                selectOption(nextIndex, true);
            }
        }
    });

    dialog.addEventListener('pointerdown', event => {
        backdropPointerDown = isBackdrop(event);
    });
    dialog.addEventListener('click', event => {
        if (backdropPointerDown && isBackdrop(event)) dialog.close();
        backdropPointerDown = false;
    });
    dialog.addEventListener('close', () => {
        // A queued close event may arrive after the dialog has been reopened.
        if (dialog.open) return;
        cancelSearch();
        document.documentElement.classList.remove('search-open');
        trigger.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        results.setAttribute('aria-busy', 'false');
        status.textContent = '';
        composing = false;
        if (previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
    });

    input.addEventListener('input', event => {
        if (!event.isComposing && !composing) refreshSearch();
    });
    input.addEventListener('compositionstart', () => {
        composing = true;
        cancelSearch();
        selectOption(-1);
    });
    input.addEventListener('compositionend', () => {
        composing = false;
        refreshSearch();
    });

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (composing) return;
        if (activeIndex >= 0 && options[activeIndex]) {
            options[activeIndex].click();
        } else if (getQuery()) {
            rememberSearch(getQuery());
            window.location.assign(catalogUrl(getQuery()));
        }
    });

    results.addEventListener('pointermove', event => {
        const option = event.target.closest('[role="option"]');
        if (option) selectOption(options.indexOf(option));
    });
    results.addEventListener('mousedown', event => {
        // Keep the combobox focused while selecting results with the mouse.
        if (event.button === 0 && event.target.closest('[role="option"]')) event.preventDefault();
    });
    results.addEventListener('click', event => {
        const option = event.target.closest('[role="option"]');
        if (!option) return;
        if (option.dataset.searchKind === 'suggestion' && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) {
            event.preventDefault();
            input.value = option.dataset.query;
            refreshSearch(true);
            input.focus();
        } else {
            rememberSearch(option.dataset.query || getQuery());
        }
    });

    function openSearch() {
        if (!dialog.open) {
            previousFocus = document.activeElement;
            input.value = input.value.slice(0, 120);
            dialog.showModal();
            document.documentElement.classList.add('search-open');
            trigger.setAttribute('aria-expanded', 'true');
            input.setAttribute('aria-expanded', 'true');
            refreshSearch(true);
        }
        input.focus({ preventScroll: true });
        input.select();
    }

    function isBackdrop(event) {
        if (event.target !== dialog) return false;
        const bounds = dialog.getBoundingClientRect();
        return event.clientX < bounds.left || event.clientX > bounds.right ||
            event.clientY < bounds.top || event.clientY > bounds.bottom;
    }

    function getQuery() {
        return input.value.trim().replace(/\s+/g, ' ');
    }

    function cancelSearch() {
        window.clearTimeout(searchTimer);
        activeRequest?.abort();
        activeRequest = null;
        requestVersion += 1;
    }

    function refreshSearch(immediate = false) {
        cancelSearch();
        clearButton.hidden = !input.value;
        const query = getQuery();
        if (!query) {
            renderSuggestions();
            return;
        }

        showMessage('Ищем товары…', 'По названию, бренду и характеристикам', true);
        renderGroups([allResultsGroup(query)]);
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Ищем товары';
        const version = requestVersion;
        if (immediate) {
            fetchResults(query, version);
        } else {
            searchTimer = window.setTimeout(() => fetchResults(query, version), 180);
        }
    }

    async function fetchResults(query, version) {
        const controller = new AbortController();
        activeRequest = controller;
        const timeout = window.setTimeout(() => controller.abort(), 8000);
        try {
            const url = new URL(`${baseUrl}/api/search`);
            url.searchParams.set('q', query);
            const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Search request failed');
            const data = await response.json();
            if (!data.success || !Array.isArray(data.products) || !Array.isArray(data.categories)) {
                throw new Error('Invalid search response');
            }
            if (!dialog.open || version !== requestVersion) return;

            const products = data.products.filter(product => Number.isInteger(Number(product.id)) && Number(product.id) > 0);
            const categories = data.categories.filter(category => Number.isInteger(Number(category.id)) && Number(category.id) > 0);
            const groups = [];
            if (products.length) {
                groups.push({ title: 'Товары', items: products.map(product => ({
                    title: String(product.name),
                    meta: `${product.category_name || product.brand || 'Товар'} · ${Number(product.stock) > 0 ? 'В наличии' : 'Нет в наличии'}`,
                    price: priceFormatter.format(Number(product.price)),
                    image: product.main_image,
                    icon: 'memory',
                    href: `${baseUrl}/product/${Number(product.id)}`,
                    kind: 'product',
                    query
                })) });
            }
            if (categories.length) {
                groups.push({ title: 'Категории', items: categories.map(category => ({
                    title: String(category.name), icon: 'folder_open',
                    href: `${baseUrl}/catalog?category=${Number(category.id)}`, kind: 'category', query
                })) });
            }

            if (groups.length) {
                message.hidden = true;
            } else {
                showMessage('Ничего не найдено', 'Попробуйте другое название, бренд или артикул');
            }
            groups.push(allResultsGroup(query));
            renderGroups(groups, true);
            status.textContent = products.length || categories.length ?
                `Найдено товаров: ${products.length}. Категорий: ${categories.length}.` : 'Ничего не найдено';
        } catch (error) {
            if (!dialog.open || version !== requestVersion) return;
            showMessage('Не удалось загрузить подсказки', 'Попробуйте ещё раз или откройте результаты в каталоге');
            renderGroups([allResultsGroup(query)]);
            status.textContent = 'Подсказки недоступны. Поиск можно продолжить в каталоге.';
        } finally {
            window.clearTimeout(timeout);
            if (version === requestVersion) {
                activeRequest = null;
                results.setAttribute('aria-busy', 'false');
            }
        }
    }

    function renderSuggestions() {
        message.hidden = true;
        results.setAttribute('aria-busy', 'false');
        const groups = [];
        const recent = readHistory();
        if (recent.length) {
            groups.push({ title: 'Недавно искали', items: recent.slice(0, 3).map(query => ({
                title: query, query, icon: 'history', href: catalogUrl(query), kind: 'suggestion'
            })) });
        }
        groups.push({ title: 'Часто ищут', items: suggestions.map(item => ({
            ...item, query: item.title, href: catalogUrl(item.title), kind: 'suggestion'
        })) });
        renderGroups(groups);
        status.textContent = 'Доступны быстрые запросы. Введите название товара для поиска.';
    }

    function allResultsGroup(query) {
        return { className: 'search-all-results', items: [{
            title: `Все результаты по запросу «${query}»`, icon: 'search', href: catalogUrl(query), kind: 'catalog', query
        }] };
    }

    function renderGroups(groups, selectFirst = false) {
        const fragment = document.createDocumentFragment();
        input.removeAttribute('aria-activedescendant');
        options = [];
        activeIndex = -1;

        groups.forEach((group, groupIndex) => {
            const section = document.createElement('div');
            section.className = `search-group ${group.className || ''}`.trim();
            section.setAttribute('role', 'group');
            if (group.title) {
                const heading = document.createElement('div');
                heading.className = 'search-section-label';
                heading.id = `search-group-${groupIndex}`;
                heading.textContent = group.title;
                section.setAttribute('aria-labelledby', heading.id);
                section.appendChild(heading);
            } else {
                section.setAttribute('aria-label', 'Открыть каталог');
            }
            group.items.forEach(item => {
                const option = createOption(item, options.length);
                options.push(option);
                section.appendChild(option);
            });
            fragment.appendChild(section);
        });
        results.replaceChildren(fragment);
        if (selectFirst && options.length) selectOption(0);
    }

    function createOption(item, index) {
        const option = document.createElement('a');
        option.className = 'search-option';
        option.id = `search-option-${index}`;
        option.href = item.href;
        option.tabIndex = -1;
        option.dataset.searchKind = item.kind;
        option.dataset.query = item.query;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');

        const icon = document.createElement('img');
        icon.alt = '';
        icon.width = 20;
        icon.height = 20;
        icon.className = 'search-option-icon';
        icon.src = `${baseUrl}/public/images/icons/${item.icon}.svg`;
        if (item.kind === 'product') {
            const thumbnail = document.createElement('span');
            thumbnail.className = 'search-option-image';
            if (item.image) {
                icon.src = `${baseUrl}/public/images/products/${String(item.image).split('/').map(encodeURIComponent).join('/')}`;
                icon.addEventListener('error', () => {
                    thumbnail.classList.add('is-placeholder');
                    icon.src = `${baseUrl}/public/images/icons/memory.svg`;
                }, { once: true });
            } else {
                thumbnail.classList.add('is-placeholder');
            }
            thumbnail.appendChild(icon);
            option.appendChild(thumbnail);
        } else {
            option.appendChild(icon);
        }

        const copy = document.createElement('span');
        copy.className = 'search-option-copy';
        const title = document.createElement('span');
        title.className = 'search-option-title';
        appendHighlighted(title, item.title, item.kind === 'suggestion' ? '' : getQuery());
        copy.appendChild(title);
        if (item.meta) {
            const meta = document.createElement('span');
            meta.className = 'search-option-meta';
            meta.textContent = item.meta;
            copy.appendChild(meta);
        }
        option.appendChild(copy);
        if (item.price) {
            const price = document.createElement('span');
            price.className = 'search-option-price';
            price.textContent = item.price;
            option.appendChild(price);
        }
        const arrow = document.createElement('span');
        arrow.className = 'search-option-arrow';
        arrow.textContent = '↵';
        arrow.setAttribute('aria-hidden', 'true');
        option.appendChild(arrow);
        return option;
    }

    function appendHighlighted(element, text, query) {
        if (!query) {
            element.textContent = text;
            return;
        }
        const tokens = query.split(/\s+/).map(token => token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        const pattern = new RegExp(tokens.join('|'), 'giu');
        let offset = 0;
        for (const match of text.matchAll(pattern)) {
            element.appendChild(document.createTextNode(text.slice(offset, match.index)));
            const mark = document.createElement('mark');
            mark.textContent = match[0];
            element.appendChild(mark);
            offset = match.index + match[0].length;
        }
        element.appendChild(document.createTextNode(text.slice(offset)));
    }

    function selectOption(index, scroll = false) {
        activeIndex = index;
        options.forEach((option, optionIndex) => option.setAttribute('aria-selected', String(optionIndex === index)));
        if (index >= 0 && options[index]) {
            input.setAttribute('aria-activedescendant', options[index].id);
            if (scroll) options[index].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    function showMessage(title, description, loading = false) {
        message.hidden = false;
        message.classList.toggle('is-loading', loading);
        document.getElementById('search-message-title').textContent = title;
        document.getElementById('search-message-description').textContent = description;
        document.getElementById('search-message-icon').src = `${baseUrl}/public/images/icons/${loading ? 'search' : 'search_off'}.svg`;
    }

    function catalogUrl(query) {
        const url = new URL(`${baseUrl}/catalog`);
        url.searchParams.set('search', query);
        return url.href;
    }

    function readHistory() {
        try {
            const history = JSON.parse(localStorage.getItem(historyKey) || '[]');
            return Array.isArray(history) ? history.filter(query => typeof query === 'string' && query.trim() && query.length <= 120) : [];
        } catch (error) {
            return [];
        }
    }

    function rememberSearch(query) {
        if (!query || query.length > 120) return;
        try {
            const history = readHistory().filter(item => item.toLocaleLowerCase('ru-RU') !== query.toLocaleLowerCase('ru-RU'));
            localStorage.setItem(historyKey, JSON.stringify([query, ...history].slice(0, 5)));
        } catch (error) {
            // Storage may be disabled; search should still work.
        }
    }
})();