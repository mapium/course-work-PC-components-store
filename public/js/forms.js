(function () {
    'use strict';
    const busy = new WeakSet();
    function errors(form, error) {
        const summary = document.createElement('p');
        summary.className = 'form-error';
        summary.dataset.formError = '';
        summary.setAttribute('role', 'alert');
        summary.textContent = error.message;
        form.prepend(summary);
        Object.entries(error.errors || {}).forEach(([name, message], index) => {
            const field = Array.from(form.elements).find(input => input.name === name);
            if (!field) return;
            const messageNode = document.createElement('p');
            messageNode.className = 'field-error';
            messageNode.dataset.formError = '';
            messageNode.id = `field-error-${index}`;
            messageNode.textContent = message;
            field.setAttribute('aria-invalid', 'true');
            field.setAttribute('aria-errormessage', messageNode.id);
            (field.closest('.form-group') || field.parentElement).append(messageNode);
        });
        form.querySelector('[aria-invalid="true"]')?.focus();
    }
    document.addEventListener('submit', async event => {
        const form = event.target.closest('form[data-ajax]');
        if (!form || event.defaultPrevented) return;
        event.preventDefault();
        if (busy.has(form) || !form.reportValidity()) return;
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
        busy.add(form);
        form.querySelectorAll('[data-form-error]').forEach(node => node.remove());
        form.querySelectorAll('[aria-invalid]').forEach(node => { node.removeAttribute('aria-invalid'); node.removeAttribute('aria-errormessage'); });
        const data = new FormData(form);
        if (event.submitter?.name) data.set(event.submitter.name, event.submitter.value);
        const buttons = Array.from(form.querySelectorAll('button')).filter(button => !button.disabled);
        buttons.forEach(button => { button.disabled = true; });
        form.setAttribute('aria-busy', 'true');
        let result;
        try {
            const action = event.submitter?.hasAttribute('formaction') ? event.submitter.formAction : form.getAttribute('action');
            result = await Store.request(action, { method: 'POST', body: data });
            if (result.cart_count !== undefined) document.querySelectorAll('[data-cart-count]').forEach(node => {
                node.textContent = result.cart_count;
                node.hidden = result.cart_count === 0;
            });
            if (form.dataset.ajax !== 'cart-add' && form.dataset.ajax !== 'cart-product') {
                if (form.dataset.ajax !== 'cart') window.StoreNotify(result.message, 'success');
                try {
                    await Store.refresh(result.redirect || location.href, 'main', { history: form.dataset.ajax === 'admin' || form.dataset.ajax === 'review' ? 'replace' : undefined });
                } catch (error) {
                    if (error.name !== 'AbortError') window.StoreNotify('Изменения сохранены. Не удалось обновить представление — обновите страницу.', 'info');
                }
            }
        } catch (error) {
            errors(form, error);
            Store.showError(error);
        } finally {
            busy.delete(form);
            buttons.forEach(button => { button.disabled = false; });
            form.removeAttribute('aria-busy');
            document.dispatchEvent(new CustomEvent('store:form-complete', { detail: { form, result } }));
        }
    });
})();
