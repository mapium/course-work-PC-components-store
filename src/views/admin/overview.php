<?php
$total_products = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$total_users = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$order_statistics = $pdo->query("SELECT COUNT(*) AS total_orders,
    COALESCE(SUM(CASE WHEN status IN ('pending', 'processing', 'assembled', 'shipped') THEN 1 ELSE 0 END), 0) AS active_orders,
    COALESCE(SUM(CASE WHEN status <> 'cancelled' THEN total_amount ELSE 0 END), 0) AS total_revenue FROM orders")->fetch(\PDO::FETCH_ASSOC);
$statistics = [
    ['value' => $total_products, 'label' => 'Всего товаров', 'icon' => 'inventory'],
    ['value' => $order_statistics['total_orders'], 'label' => 'Всего заказов', 'icon' => 'shopping_bag'],
    ['value' => $order_statistics['active_orders'], 'label' => 'Активных заказов', 'icon' => 'schedule'],
    ['value' => $total_users, 'label' => 'Пользователей', 'icon' => 'group'],
    ['value' => number_format($order_statistics['total_revenue'], 0, '', ' ') . ' ₽', 'label' => 'Сумма заказов без отменённых', 'icon' => 'payments']
];
?>
<h2>Статистика</h2>
<div class="stats-grid">
    <?php foreach ($statistics as $statistic): ?>
        <div class="stat-card">
            <div class="stat-icon"><img src="<?php echo APP_URL; ?>/public/images/icons/<?php echo $statistic['icon']; ?>.svg" alt="" width="24" height="24"></div>
            <div class="stat-content"><div class="stat-value"><?php echo $statistic['value']; ?></div><div class="stat-label"><?php echo $statistic['label']; ?></div></div>
        </div>
    <?php endforeach; ?>
</div>
