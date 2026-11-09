// Только отображение вкладок. Без JS используются обычные ссылки и выбор вкладки в PHP.
(function () {
    'use strict';

    function init() { document.querySelectorAll('[data-tabs]').forEach(root => {
        if (root.dataset.tabsReady) return;
        root.dataset.tabsReady = 'true';
        const tabs = Array.from(root.querySelectorAll('[data-tab]'));
        const panels = Array.from(root.querySelectorAll('.tab-content, .auth-form'));
        const navigation = root.querySelector('.tabs-nav, .auth-tabs');
        navigation?.setAttribute('role', 'tablist');

        function activate(tab, focus = false) {
            const panel = document.getElementById(tab.dataset.tabTarget || tab.dataset.tab);
            if (!panel || !root.contains(panel)) return;
            tabs.forEach(item => {
                const active = item === tab;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
            });
            panels.forEach(item => item.classList.toggle('active', item === panel));
            if (focus) tab.focus();
        }

        tabs.forEach((tab, index) => {
            const target = tab.dataset.tabTarget || tab.dataset.tab;
            tab.setAttribute('role', 'tab');
            tab.setAttribute('aria-controls', target);
            tab.addEventListener('click', event => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                activate(tab);
                history.pushState({ store: 'tabs' }, '', tab.href);
            });
            tab.addEventListener('keydown', event => {
                let next;
                if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
                if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
                if (event.key === 'Home') next = 0;
                if (event.key === 'End') next = tabs.length - 1;
                if (event.key === ' ') next = index;
                if (next !== undefined) {
                    event.preventDefault();
                    activate(tabs[next], true);
                    history.pushState({ store: 'tabs' }, '', tabs[next].href);
                }
            });
        });
        panels.forEach(panel => panel.setAttribute('role', 'tabpanel'));
        const active = tabs.find(tab => tab.classList.contains('active')) || tabs[0];
        if (active) activate(active);
    }); }
    init();
    document.addEventListener('store:updated', init);
    window.addEventListener('popstate', () => {
        const current = new URL(location.href).searchParams.get('tab');
        document.querySelectorAll('[data-tabs]').forEach(root => {
            const tabs = Array.from(root.querySelectorAll('[data-tab]'));
            const active = tabs.find(tab => tab.dataset.tab === current) || tabs[0];
            if (!active) return;
            const target = active.dataset.tabTarget || active.dataset.tab;
            tabs.forEach(tab => {
                tab.classList.toggle('active', tab === active);
                tab.setAttribute('aria-selected', String(tab === active));
                tab.tabIndex = tab === active ? 0 : -1;
            });
            root.querySelectorAll('.tab-content, .auth-form').forEach(panel => panel.classList.toggle('active', panel.id === target));
        });
    });
})();
