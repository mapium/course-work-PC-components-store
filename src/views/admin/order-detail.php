<?php
$order_id = \App\Helpers\Request::integer($_GET, 'id');
$order = $order_id > 0 ? $order_model->getById($order_id) : null;
if (!$order) {
    echo '<div class="alert alert-error">Заказ не найден</div>';
    return;
}
// Изображение берётся из product_images, поля products.main_image в схеме нет.
$order_items = $order_model->getItems($order_id);
$status = \App\Models\Order::statusInfo($order['status']);
$delivery_methods = ['pickup' => 'Самовывоз', 'courier' => 'Курьер', 'mail' => 'Почта'];
$payment_methods = ['card' => 'Карта', 'cash' => 'Наличные', 'online' => 'Онлайн'];
?>
<div class="product-form-card">
    <h3>Заказ <?php echo htmlspecialchars($order['order_number']); ?></h3>
    <div class="order-details">
        <div class="form-grid">
            <div class="form-group"><label>Клиент</label><p><?php echo htmlspecialchars($order['username'] ?? 'Не указан'); ?> (<?php echo htmlspecialchars($order['email'] ?? ''); ?>)</p></div>
            <div class="form-group"><label>Дата создания</label><p><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p></div>
            <div class="form-group"><label>Метод доставки</label><p><?php echo htmlspecialchars($delivery_methods[$order['delivery_method']] ?? $order['delivery_method']); ?></p></div>
            <div class="form-group"><label>Метод оплаты</label><p><?php echo htmlspecialchars($payment_methods[$order['payment_method']] ?? $order['payment_method']); ?></p></div>
            <div class="form-group"><label>Текущий статус</label><p><span class="badge <?php echo $status['class']; ?>"><?php echo htmlspecialchars($status['label']); ?></span></p></div>
        </div>
        <div class="form-group"><label>Адрес доставки</label><p><?php echo htmlspecialchars($order['delivery_address'] ?: 'Не указан'); ?></p></div>
        <?php if ($order['notes']): ?><div class="form-group"><label>Примечания и контакты</label><p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p></div><?php endif; ?>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead><tr><th>Товар</th><th>Количество</th><th>Цена</th><th>Итого</th></tr></thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><div class="admin-order-product"><img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($item['main_image'] ?: 'placeholder.jpg'); ?>" alt="" width="40" height="40"><?php echo htmlspecialchars($item['name'] ?? 'Товар недоступен'); ?></div></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td><?php echo number_format($item['unit_price'], 2, '.', ' '); ?> ₽</td>
                            <td><?php echo number_format($item['total_price'], 2, '.', ' '); ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr><td colspan="3"><strong>Общая сумма с доставкой:</strong></td><td><strong><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</strong></td></tr>
                </tbody>
            </table>
        </div>
        <?php $order_status_view = 'detail'; include __DIR__ . '/order-status-form.php'; ?>
        <a href="<?php echo APP_URL; ?>/admin?tab=orders" class="btn btn-outline">Назад к списку</a>
    </div>
</div>
