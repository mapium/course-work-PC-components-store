<div class="admin-header">
    <h2>Управление категориями</h2>
    <a href="<?php echo APP_URL; ?>/admin?tab=categories&amp;action=add" class="btn">Добавить категорию</a>
</div>
<?php
$category_model = new \App\Models\Category($pdo);
$admin_categories = $category_model->getTree(true);
$category_action = \App\Helpers\Request::text($_GET, 'action');
if (in_array($category_action, ['add', 'edit'], true)) {
    include __DIR__ . '/category-form.php';
} else {
?>
<div class="data-table-wrapper">
    <table class="data-table">
        <thead><tr><th>Категория</th><th>Порядок</th><th>Видимость</th><th>Действия</th></tr></thead>
        <tbody>
            <?php foreach ($admin_categories as $row): ?>
                <tr>
                    <td><?php echo str_repeat('— ', $row['depth']); ?><?php if ($row['icon_path']): ?><img class="category-icon" src="<?php echo htmlspecialchars(\App\Helpers\Media::categoryUrl($row['icon_path'])); ?>" width="32" height="32" alt=""><?php endif; ?> <?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo (int)$row['sort_order']; ?></td>
                    <td><?php echo $row['visible'] ? 'Видна' : 'Скрыта'; ?></td>
                    <td><div class="table-actions"><a class="btn btn-sm btn-outline" href="<?php echo APP_URL; ?>/admin?tab=categories&amp;action=edit&amp;id=<?php echo (int)$row['id']; ?>">Редактировать</a><?php if ($row['visible']): ?><a class="btn btn-sm btn-outline" href="<?php echo htmlspecialchars(\App\Helpers\Url::category($row, $pdo)); ?>">Открыть раздел</a><?php endif; ?></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$admin_categories): ?><tr><td colspan="4">Категорий пока нет</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php } ?>
