<div class="admin-header"><h2>Управление заказами</h2></div>
<?php
if (\App\Helpers\Request::text($_GET, 'action') === 'view') {
    include __DIR__ . '/order-detail.php';
} else {
    $orders = $order_model->getAll($admin_page_size + 1, $admin_offset);
    $has_more = count($orders) > $admin_page_size;
    $orders = array_slice($orders, 0, $admin_page_size);
?>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Номер заказа</th><th>Клиент</th><th>Дата</th><th>Сумма</th><th>Статус</th><th>Действия</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): $status = \App\Models\Order::statusInfo($order['status']); ?>
                    <tr>
                        <td><?php echo (int)$order['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['username'] ?? 'Гость'); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</td>
                        <td><span class="badge <?php echo $status['class']; ?>"><?php echo htmlspecialchars($status['label']); ?></span></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/admin?tab=orders&amp;action=view&amp;id=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline">Просмотр</a>
                            <?php $order_status_view = 'list'; include __DIR__ . '/order-status-form.php'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$orders): ?><tr><td colspan="7">Заказов пока нет</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/pagination.php'; ?>
<?php } ?>
