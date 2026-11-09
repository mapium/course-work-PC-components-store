// Необязательное подтверждение опасных действий. Проверки прав и сохранение — в PHP.
(function () {
    'use strict';
    document.addEventListener('submit', event => {
        const message = event.target.dataset.confirm;
        if (!event.target.hasAttribute('data-ajax') && message && !window.confirm(message)) event.preventDefault();
    });
})();
