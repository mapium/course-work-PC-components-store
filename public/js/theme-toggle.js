(function () {
    'use strict';
    const toggle = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');
    if (!toggle || !icon) return;
    function update() {
        const dark = document.documentElement.classList.contains('dark');
        icon.src = icon.src.replace(/(?:dark|light)_mode\.svg/, `${dark ? 'light' : 'dark'}_mode.svg`);
        toggle.setAttribute('aria-label', dark ? 'Включить светлую тему' : 'Включить тёмную тему');
        toggle.setAttribute('aria-pressed', String(dark));
    }
    toggle.addEventListener('click', () => {
        const dark = document.documentElement.classList.toggle('dark');
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (_) { /* Тема работает без хранилища. */ }
        update();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('#theme-toggle-settings')) toggle.click();
    });
    update();
})();
