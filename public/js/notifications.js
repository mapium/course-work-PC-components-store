(function () {
    'use strict';
    const container = document.createElement('div');
    container.id = 'notifications';
    container.setAttribute('aria-label', 'Уведомления');
    document.body.append(container);
    function notify(message, type = 'info', duration = 4500) {
        if (!message) return;
        const item = document.createElement('div');
        item.className = `notification notification-${['success', 'error', 'info'].includes(type) ? type : 'info'}`;
        item.setAttribute('role', type === 'error' ? 'alert' : 'status');
        const text = document.createElement('span');
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'notification-close';
        close.setAttribute('aria-label', 'Закрыть уведомление');
        close.textContent = '×';
        item.append(text, close);
        container.append(item);
        requestAnimationFrame(() => item.classList.add('show'));
        let timer;
        function dismiss() {
            clearTimeout(timer);
            item.classList.remove('show');
            setTimeout(() => item.remove(), 300);
        }
        function schedule() { clearTimeout(timer); timer = setTimeout(dismiss, duration); }
        close.addEventListener('click', dismiss);
        item.addEventListener('mouseenter', () => clearTimeout(timer));
        item.addEventListener('focusin', () => clearTimeout(timer));
        item.addEventListener('mouseleave', schedule);
        item.addEventListener('focusout', schedule);
        schedule();
    }
    window.StoreNotify = notify;
    document.querySelectorAll('[data-flash]').forEach(item => {
        notify(item.textContent.trim(), item.dataset.flash);
        item.closest('.flash-messages')?.setAttribute('hidden', '');
    });
})();
