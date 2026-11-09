(function () {
    'use strict';
    function init() {
        document.querySelectorAll('[data-gallery]').forEach(root => {
            if (root.dataset.galleryReady) return;
            root.dataset.galleryReady = 'true';
            const thumbs = Array.from(root.querySelectorAll('[data-gallery-thumb]'));
            const main = root.querySelector('[data-gallery-main]');
            const zoom = root.querySelector('[data-gallery-zoom]');
            const dialog = root.querySelector('[data-gallery-dialog]');
            let index = Math.max(0, thumbs.findIndex(thumb => thumb.hasAttribute('aria-current')));
            let start;
            function select(next) {
                index = (next + thumbs.length) % thumbs.length;
                main.src = thumbs[index].href;
                zoom.href = main.src;
                root.querySelector('[data-gallery-full]').src = main.src;
                thumbs.forEach((thumb, i) => i === index ? thumb.setAttribute('aria-current', 'true') : thumb.removeAttribute('aria-current'));
                root.querySelector('[data-gallery-status]').textContent = `Изображение ${index + 1} из ${thumbs.length}`;
            }
            thumbs.forEach((thumb, i) => thumb.addEventListener('click', event => { event.preventDefault(); select(i); }));
            root.querySelectorAll('[data-gallery-prev], [data-gallery-next]').forEach(button => {
                button.hidden = thumbs.length < 2;
                button.addEventListener('click', () => select(index + (button.hasAttribute('data-gallery-prev') ? -1 : 1)));
            });
            zoom.addEventListener('click', event => {
                if (!dialog.showModal) return;
                event.preventDefault();
                dialog.showModal();
            });
            root.querySelector('[data-gallery-close]').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
            root.addEventListener('keydown', event => {
                if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
                event.preventDefault();
                select(index + (event.key === 'ArrowLeft' ? -1 : 1));
            });
            root.addEventListener('pointerdown', event => { if (event.pointerType === 'touch') start = [event.clientX, event.clientY]; });
            root.addEventListener('pointerup', event => {
                if (!start) return;
                const dx = event.clientX - start[0];
                const dy = event.clientY - start[1];
                start = null;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) select(index + (dx < 0 ? 1 : -1));
            });
            root.addEventListener('pointercancel', () => { start = null; });
        });
    }
    init();
    document.addEventListener('store:updated', init);
})();
