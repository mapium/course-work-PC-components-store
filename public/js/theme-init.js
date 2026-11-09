// Синхронно в <head>, до CSS и первого отображения страницы.
(function () {
    'use strict';
    let theme = 'light';
    try { if (localStorage.getItem('theme') === 'dark') theme = 'dark'; } catch (_) { /* Хранилище может быть недоступно. */ }
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.style.colorScheme = theme;
})();
