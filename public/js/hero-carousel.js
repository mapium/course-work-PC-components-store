// Баннеры: анимация, кнопки и жесты.
(function () {
    'use strict';

    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(carousel.querySelectorAll('[data-hero-dot]'));
    if (!slides.length) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let autoplay;
    let touchStart;

    function show(index) {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            slide.classList.toggle('is-active', position === current);
            slide.setAttribute('aria-hidden', String(position !== current));
            slide.querySelectorAll('a').forEach(link => { link.tabIndex = position === current ? 0 : -1; });
        });
        dots.forEach((dot, position) => {
            dot.classList.toggle('is-active', position === current);
            if (position === current) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    }

    function stop() {
        window.clearInterval(autoplay);
    }

    function start() {
        stop();
        if (!reducedMotion.matches && !document.hidden && !carousel.matches(':hover, :focus-within')) {
            autoplay = window.setInterval(() => show(current + 1), 5000);
        }
    }

    carousel.querySelector('[data-hero-prev]')?.addEventListener('click', () => { show(current - 1); start(); });
    carousel.querySelector('[data-hero-next]')?.addEventListener('click', () => { show(current + 1); start(); });
    dots.forEach((dot, index) => dot.addEventListener('click', () => { show(index); start(); }));
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', start);
    carousel.addEventListener('touchstart', event => { touchStart = event.changedTouches[0].screenX; }, { passive: true });
    carousel.addEventListener('touchend', event => {
        const delta = touchStart - event.changedTouches[0].screenX;
        if (Math.abs(delta) > 50) { show(current + (delta > 0 ? 1 : -1)); start(); }
    }, { passive: true });
    carousel.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(current + (event.key === 'ArrowRight' ? 1 : -1));
            start();
        }
    });
    document.addEventListener('visibilitychange', start);
    reducedMotion.addEventListener('change', start);
    show(0);
    start();
})();
