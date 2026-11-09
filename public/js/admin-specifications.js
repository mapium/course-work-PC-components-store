// Только добавление и удаление полей интерфейса. Сохранение и валидация — в PHP.
(function () {
    'use strict';
    function init() {
    const form = document.querySelector('[data-product-form]');
    if (!form) return;
    if (form.dataset.specsReady) return;
    form.dataset.specsReady = 'true';
    const container = form.querySelector('[data-specifications]');
    const template = form.querySelector('[data-specification-template]');

    function reindex() {
        container.querySelectorAll('[data-specification-row]').forEach((row, index) => {
            row.querySelectorAll('input').forEach((input, position) => {
                input.name = `specifications[${index}][${position === 0 ? 'name' : 'value'}]`;
            });
            row.querySelector('[data-remove-specification]').value = `remove_specification:${index}`;
        });
    }

    form.addEventListener('click', event => {
        const add = event.target.closest('[data-add-specification]');
        const remove = event.target.closest('[data-remove-specification]');
        if (!add && !remove) return;
        event.preventDefault();
        if (add) {
            container.appendChild(template.content.cloneNode(true));
            reindex();
            container.lastElementChild.querySelector('input').focus();
        } else {
            remove.closest('[data-specification-row]').remove();
            reindex();
        }
    });
    }
    init();
    document.addEventListener('store:updated', init);
})();
