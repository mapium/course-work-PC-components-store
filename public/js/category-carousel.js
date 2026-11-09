// Дополнительная дорожка для бесконечной визуальной прокрутки категорий.
(function () {
    'use strict';
    const carousel = document.querySelector('.categories-carousel');
    if (!carousel) return;
    Array.from(carousel.children).forEach(card => {
        const clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('a').forEach(link => { link.tabIndex = -1; });
        carousel.appendChild(clone);
    });
})();
