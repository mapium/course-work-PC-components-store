(function () {
    'use strict';
    const timers = new WeakMap();
    const feedback = new WeakMap();
    const busyProducts = new Set();
    const submittedButtons = new WeakMap();

    function productControls(id) {
        return Array.from(document.querySelectorAll('[data-product-cart]')).filter(root => root.dataset.productCart === String(id));
    }

    function cancelInput(form) {
        clearTimeout(timers.get(form));
        timers.delete(form);
    }

    function updateButtons(root, quantity) {
        const updateUrl = `${Store.base}/cart/${root.dataset.cartId}/update`;
        const decrease = root.querySelector('[data-cart-decrease]');
        const increase = root.querySelector('[data-cart-increase]');
        root.querySelector('[data-cart-quantity-form]').action = updateUrl;
        decrease.value = Math.max(0, quantity - 1);
        decrease.formAction = quantity <= 1 ? `${Store.base}/cart/${root.dataset.cartId}/delete` : updateUrl;
        decrease.title = quantity <= 1 ? 'Убрать из корзины' : 'Уменьшить количество';
        increase.value = quantity + 1;
        increase.disabled = quantity >= Number(root.dataset.stock);
    }

    function showQuantity(root) {
        root.querySelector('[data-cart-add-form]').hidden = Number(root.dataset.quantity) > 0;
        root.querySelector('[data-cart-quantity-form]').hidden = Number(root.dataset.quantity) === 0;
    }

    function clearFeedback(root) {
        const previous = feedback.get(root);
        if (!previous) return;
        clearTimeout(previous.timer);
        root.querySelector('[data-cart-add-button]').innerHTML = previous.html;
        feedback.delete(root);
    }

    function setBusy(root, busy) {
        root.toggleAttribute('data-cart-busy', busy);
        root.querySelectorAll('input[type="number"]').forEach(input => { input.readOnly = busy; });
    }

    function init() {
        document.querySelectorAll('[data-product-cart]').forEach(root => {
            root.querySelector('[data-cart-apply]').hidden = true;
            updateButtons(root, Number(root.dataset.quantity));
            setBusy(root, busyProducts.has(root.dataset.productCart));
        });
    }

    init();
    document.addEventListener('store:updated', init);

    document.addEventListener('input', event => {
        const input = event.target.closest('.quantity-direct-form input[name="quantity"], [data-cart-quantity-form] input[name="quantity"]');
        if (!input) return;
        const root = input.closest('[data-product-cart]');
        if (root && input.validity.valid) updateButtons(root, Number(input.value));
        cancelInput(input.form);
        timers.set(input.form, setTimeout(() => {
            if (input.isConnected && input.reportValidity()) input.form.requestSubmit();
        }, 450));
    });

    // Одна операция корзины за раз, чтобы быстрые нажатия не перезаписывали количество.
    document.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        const root = event.target.closest('[data-product-cart]');
        if (root) {
            if (busyProducts.has(root.dataset.productCart)) { event.preventDefault(); event.stopImmediatePropagation(); return; }
            if (!event.target.checkValidity()) return;
            cancelInput(event.target);
            busyProducts.add(root.dataset.productCart);
            submittedButtons.set(event.target, event.submitter);
            productControls(root.dataset.productCart).forEach(control => setBusy(control, true));
            return;
        }
        if (!event.target.matches('[data-ajax="cart"]')) return;
        if (document.querySelector('main[data-cart-busy]')) { event.preventDefault(); event.stopImmediatePropagation(); return; }
        if (!event.target.checkValidity()) return;
        document.querySelector('main').dataset.cartBusy = 'true';
        cancelInput(event.target);
    }, true);

    document.addEventListener('store:form-complete', event => {
        document.querySelector('main')?.removeAttribute('data-cart-busy');
        const { form, result } = event.detail;
        const source = form.closest('[data-product-cart]');
        const submitter = submittedButtons.get(form);
        submittedButtons.delete(form);
        if (source) {
            busyProducts.delete(source.dataset.productCart);
            productControls(source.dataset.productCart).forEach(root => setBusy(root, false));
        }
        if (!result?.cart_item) return;
        const item = result.cart_item;
        productControls(item.product_id).forEach(root => {
            clearFeedback(root);
            root.dataset.cartId = item.id;
            root.dataset.quantity = item.quantity;
            root.dataset.stock = item.stock;
            const input = root.querySelector('[data-cart-quantity-form] input[name="quantity"]');
            input.value = Math.max(1, item.quantity);
            input.max = Math.max(1, item.stock);
            updateButtons(root, item.quantity);
            const button = root.querySelector('[data-cart-add-button]');
            button.disabled = item.stock <= 0;
            root.querySelector('[data-cart-status]').textContent = item.quantity > 0 ? `В корзине: ${item.quantity} шт.` : 'Товар удалён из корзины';
            if (root === source && form.dataset.ajax === 'cart-add') {
                const html = button.innerHTML;
                const restoreFocus = document.activeElement === button || document.activeElement === document.body;
                button.textContent = 'Добавлено ✓';
                button.disabled = true;
                root.querySelector('[data-cart-status]').textContent = `Товар добавлен в корзину. В корзине: ${item.quantity} шт.`;
                const timer = setTimeout(() => {
                    if (!root.isConnected) return;
                    clearFeedback(root);
                    button.disabled = Number(root.dataset.stock) <= 0;
                    showQuantity(root);
                    if (restoreFocus && (document.activeElement === button || document.activeElement === document.body)) input.focus({ preventScroll: true });
                }, 1000);
                feedback.set(root, { html, timer });
            } else {
                showQuantity(root);
                if (root === source && (document.activeElement === document.body || document.activeElement === submitter)) {
                    (item.quantity > 0 ? (submitter && !submitter.disabled && !submitter.hidden ? submitter : input) : button).focus({ preventScroll: true });
                }
            }
        });
    });
})();
