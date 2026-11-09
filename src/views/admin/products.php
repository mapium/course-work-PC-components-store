<div class="admin-header">
    <h2>Управление товарами</h2>
    <a href="<?php echo APP_URL; ?>/admin?tab=products&amp;action=add" class="btn"><img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">Добавить товар</a>
</div>
<?php
$product_form_action = \App\Helpers\Request::text($_GET, 'action');
if (in_array($product_form_action, ['add', 'edit'], true)) {
    include __DIR__ . '/product-form.php';
} else {
    $products = $product_model->getAll($admin_page_size + 1, $admin_offset, true);
    $has_more = count($products) > $admin_page_size;
    $products = array_slice($products, 0, $admin_page_size);
?>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Название</th><th>Цена</th><th>Остаток</th><th>Бренд</th><th>Статус</th><th>Действия</th></tr></thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo (int)$product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2, '.', ' '); ?> ₽</td>
                        <td><span class="badge <?php echo $product['stock'] > 10 ? 'badge-success' : ($product['stock'] > 0 ? 'badge-warning' : 'badge-destructive'); ?>"><?php echo (int)$product['stock']; ?></span></td>
                        <td><?php echo htmlspecialchars($product['brand'] ?? '-'); ?></td>
                        <td><span class="badge <?php echo $product['is_active'] ? 'badge-success' : ''; ?>"><?php echo $product['is_active'] ? 'Активен' : 'Неактивен'; ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a href="<?php echo APP_URL; ?>/admin?tab=products&amp;action=edit&amp;id=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-outline">Редактировать</a>
                                <?php if ($product['is_active']): ?>
                                    <form method="POST" action="<?php echo APP_URL; ?>/admin?tab=products" data-ajax="admin" data-confirm="Снять товар с продажи? Оформленные заказы сохранятся.">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
                                        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                        <button type="submit" class="btn btn-sm btn-destructive">Снять с продажи</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?><tr><td colspan="7">Товары не найдены</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/pagination.php'; ?>
<?php } ?>
