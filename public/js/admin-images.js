(function () {
    'use strict';
    const objectUrls = new Set();
    function objectUrl(file) { const url = URL.createObjectURL(file); objectUrls.add(url); return url; }
    function enhanceSelect(select) {
        if (select.dataset.imageReady) return;
        select.dataset.imageReady = 'true';
        select.hidden = true;
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.id = `${select.id}-trigger`;
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-label', select.labels?.[0]?.textContent || 'Выберите изображение');
        Array.from(select.labels || []).forEach(label => { label.htmlFor = trigger.id; });
        const selected = document.createElement('span');
        selected.className = 'selected-option';
        const arrow = document.createElement('span');
        arrow.className = 'arrow';
        arrow.textContent = '▼';
        trigger.append(selected, arrow);
        const list = document.createElement('div');
        list.className = 'custom-options';
        list.id = `${select.id}-options`;
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', trigger.getAttribute('aria-label'));
        trigger.setAttribute('aria-controls', list.id);
        wrapper.append(trigger, list);
        select.after(wrapper);
        function contents(option) {
            const fragment = document.createDocumentFragment();
            if (option?.dataset.imageUrl) {
                const image = document.createElement('img');
                image.src = option.dataset.imageUrl;
                image.alt = '';
                image.width = image.height = 28;
                image.loading = 'lazy';
                fragment.append(image);
            }
            const text = document.createElement('span');
            text.textContent = option?.textContent || 'Выберите изображение';
            if (!option?.value) text.className = 'placeholder';
            fragment.append(text);
            return fragment;
        }
        function close() { list.classList.remove('open'); trigger.setAttribute('aria-expanded', 'false'); }
        function render() {
            selected.replaceChildren(contents(select.selectedOptions[0]));
            list.replaceChildren();
            Array.from(select.options).forEach(option => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `custom-option${option.selected ? ' selected' : ''}`;
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', String(option.selected));
                button.tabIndex = -1;
                button.append(contents(option));
                button.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    close();
                    trigger.focus();
                });
                list.append(button);
            });
        }
        function open() {
            list.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            (list.querySelector('.selected') || list.firstElementChild)?.focus();
        }
        trigger.addEventListener('click', () => list.classList.contains('open') ? close() : open());
        wrapper.addEventListener('keydown', event => {
            if (event.key === 'Escape') { close(); trigger.focus(); event.stopPropagation(); }
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                event.preventDefault();
                if (!list.classList.contains('open')) { open(); return; }
                const options = Array.from(list.children);
                const index = options.indexOf(document.activeElement);
                const next = event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1 : (index + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length;
                options[next]?.focus();
            }
        });
        wrapper.addEventListener('focusout', event => { if (!wrapper.contains(event.relatedTarget)) close(); });
        select.addEventListener('change', render);
        select.addEventListener('images:updated', render);
        render();
    }
    function enhanceGallery(form) {
        const root = form.querySelector('[data-product-media]');
        if (!root || root.dataset.mediaReady) return;
        root.dataset.mediaReady = 'true';
        const fallback = root.querySelector('[data-gallery-selection]');
        const library = root.querySelector('[data-gallery-library]');
        const files = root.querySelector('[data-gallery-uploads]');
        const main = form.querySelector('[name="main_image"]');
        const mainUpload = form.querySelector('[name="main_upload"]');
        const container = root.querySelector('[data-admin-gallery]');
        const imageInfo = new Map(Array.from(fallback.options).map(option => [option.value, { name: option.textContent, url: option.dataset.imageUrl }]));
        let gallery = Array.from(fallback.selectedOptions).map(option => option.value);
        let uploaded = [];
        fallback.disabled = true;
        root.querySelector('[data-gallery-fallback]').hidden = true;
        root.querySelector('[data-gallery-library-ui]').hidden = false;
        function syncFiles() {
            const data = new DataTransfer();
            const mapping = new Map();
            uploaded.forEach((item, index) => {
                const next = `upload:${index}`;
                mapping.set(item.key, next);
                item.key = next;
                data.items.add(item.file);
            });
            const selectedMain = main.value;
            gallery = gallery.map(key => mapping.get(key) || key);
            main.querySelectorAll('[data-upload-option]').forEach(option => option.remove());
            uploaded.forEach(item => {
                imageInfo.set(item.key, { name: item.file.name, url: item.url });
                const option = new Option(item.file.name, item.key);
                option.dataset.uploadOption = 'true';
                option.dataset.imageUrl = item.url;
                main.add(option);
            });
            main.value = mapping.get(selectedMain) || selectedMain;
            files.files = data.files;
            main.dispatchEvent(new Event('images:updated'));
        }
        function render() {
            container.replaceChildren();
            gallery.forEach((key, index) => {
                const info = imageInfo.get(key);
                if (!info) return;
                const row = document.createElement('div');
                row.className = 'admin-gallery-item';
                row.dataset.galleryKey = key;
                const image = document.createElement('img');
                image.src = info.url;
                image.alt = info.name;
                image.width = image.height = 100;
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'gallery[]'; input.value = key;
                const title = document.createElement('span'); title.textContent = info.name;
                row.append(image, input, title);
                const actions = document.createElement('div'); actions.className = 'gallery-item-actions';
                for (const [action, label] of [['main', main.value === key ? 'Главное ✓' : 'Сделать главным'], ['up', '↑'], ['down', '↓'], ['remove', 'Удалить']]) {
                    const button = document.createElement('button'); button.type = 'button'; button.className = 'btn btn-outline btn-sm'; button.textContent = label;
                    button.setAttribute('aria-label', (action === 'up' ? 'Переместить выше' : action === 'down' ? 'Переместить ниже' : label) + ': ' + info.name);
                    button.disabled = (action === 'up' && index === 0) || (action === 'down' && index === gallery.length - 1);
                    button.addEventListener('click', () => {
                        if (action === 'main') {
                            main.value = key;
                            mainUpload.value = '';
                            main.dispatchEvent(new Event('change', { bubbles: true }));
                        } else if (action === 'remove') {
                            gallery.splice(index, 1);
                            if (main.value === key) main.value = gallery.find(value => !value.startsWith('upload:')) || '';
                            uploaded = uploaded.filter(item => item.key !== key);
                            syncFiles();
                        } else {
                            const target = index + (action === 'up' ? -1 : 1);
                            [gallery[index], gallery[target]] = [gallery[target], gallery[index]];
                        }
                        render();
                    });
                    actions.append(button);
                }
                row.append(actions);
                container.append(row);
            });
        }
        function add(key) { if (key && !gallery.includes(key)) gallery.push(key); render(); }
        root.querySelector('[data-gallery-add]').addEventListener('click', () => add(library.value));
        main.addEventListener('change', () => { mainUpload.value = ''; add(main.value); });
        mainUpload.addEventListener('change', () => {
            const file = mainUpload.files[0];
            if (!file) return;
            const key = `upload:${uploaded.length}`;
            uploaded.push({ key, file, url: objectUrl(file) });
            gallery.push(key);
            syncFiles();
            main.value = key;
            mainUpload.value = '';
            main.dispatchEvent(new Event('change', { bubbles: true }));
        });
        files.addEventListener('change', () => {
            Array.from(files.files).forEach(file => {
                const key = `upload:${uploaded.length}`;
                uploaded.push({ key, file, url: objectUrl(file) });
                gallery.push(key);
            });
            syncFiles();
            render();
        });
        add(main.value);
    }
    function init() {
        document.querySelectorAll('[data-image-select]').forEach(enhanceSelect);
        document.querySelectorAll('[data-product-form]').forEach(enhanceGallery);
    }
    document.addEventListener('click', event => {
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            if (wrapper.contains(event.target)) return;
            wrapper.querySelector('.custom-options')?.classList.remove('open');
            wrapper.querySelector('.custom-select-trigger')?.setAttribute('aria-expanded', 'false');
        });
    });
    document.addEventListener('store:updated', () => {
        objectUrls.forEach(url => URL.revokeObjectURL(url));
        objectUrls.clear();
        init();
    });
    init();
})();
