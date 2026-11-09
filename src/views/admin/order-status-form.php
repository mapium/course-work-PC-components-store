<?php if (\App\Helpers\Auth::canManageStore()): ?>
    <form method="POST" action="<?php echo APP_URL; ?>/admin?tab=orders" class="order-status-form" data-ajax="admin">
        <input type="hidden" name="action" value="update_order_status">
        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
        <input type="hidden" name="return_view" value="<?php echo $order_status_view; ?>">
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <label for="order-status-<?php echo (int)$order['id']; ?>">Изменить статус заказа</label>
        <select id="order-status-<?php echo (int)$order['id']; ?>" name="status" class="status-select" required>
            <?php foreach (\App\Models\Order::STATUSES as $status_code => $status_info): ?>
                <option value="<?php echo $status_code; ?>" <?php echo $order['status'] === $status_code ? 'selected' : ''; ?>><?php echo $status_info['label']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm">Обновить статус</button>
    </form>
<?php endif; ?>
