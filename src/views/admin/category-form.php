<?php
$editing_category = $category_action === 'edit';
$category_id = $editing_category ? \App\Helpers\Request::integer($_GET, 'id') : null;
$form_category = $editing_category ? $category_model->getById($category_id) : ['name' => '', 'parent_id' => null, 'description' => '', 'icon_path' => '', 'sort_order' => 0, 'is_visible' => 1, 'meta_title' => '', 'meta_description' => ''];
if (!$form_category) { echo '<p class="alert alert-error">Категория не найдена</p>'; return; }
$draft = $_SESSION['admin_category_draft'] ?? null;
unset($_SESSION['admin_category_draft']);
if ($draft && $draft['id'] === $category_id) {
    foreach ($form_category as $field => $value) if (isset($draft['data'][$field]) && is_scalar($draft['data'][$field])) $form_category[$field] = $draft['data'][$field];
    $form_category['is_visible'] = isset($draft['data']['is_visible']);
}
$excluded = $category_id ? $category_model->descendantIds($category_id, true) : [];
?>
<div class="product-form-card">
    <h3><?php echo $editing_category ? 'Редактирование категории' : 'Добавление категории'; ?></h3>
    <form method="POST" action="<?php echo APP_URL; ?>/admin?tab=categories" enctype="multipart/form-data" data-ajax="admin" data-category-form>
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <input type="hidden" name="action" value="<?php echo $editing_category ? 'category_update' : 'category_create'; ?>">
        <?php if ($editing_category): ?><input type="hidden" name="id" value="<?php echo $category_id; ?>"><?php endif; ?>
        <div class="form-grid">
            <div class="form-group"><label for="category-name">Название *</label><input id="category-name" name="name" maxlength="100" required value="<?php echo htmlspecialchars($form_category['name']); ?>"></div>
            <div class="form-group"><label for="category-parent">Родительская категория</label><select id="category-parent" name="parent_id"><option value="">Корневая категория</option>
                <?php foreach ($admin_categories as $row): if (in_array((int)$row['id'], $excluded, true)) continue; ?>
                    <option value="<?php echo (int)$row['id']; ?>" <?php echo (int)$form_category['parent_id'] === (int)$row['id'] ? 'selected' : ''; ?>><?php echo str_repeat('— ', $row['depth']) . htmlspecialchars($row['name']); ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="form-group"><label for="category-order">Порядок отображения</label><input id="category-order" name="sort_order" type="number" min="0" max="1000000" required value="<?php echo (int)$form_category['sort_order']; ?>"></div>
            <div class="form-group"><label><input type="checkbox" name="is_visible" value="1" <?php echo $form_category['is_visible'] ? 'checked' : ''; ?>>Показывать категорию</label></div>
        </div>
        <div class="form-group"><label for="category-description">Описание</label><textarea id="category-description" name="description" rows="4" maxlength="10000" placeholder="Оставьте пустым для автоматического описания"><?php echo htmlspecialchars($form_category['description'] ?? ''); ?></textarea></div>
        <div class="form-group"><label for="category-icon">Изображение / иконка из библиотеки</label><select id="category-icon" name="icon_path" data-image-select>
            <option value="">Без изображения</option>
            <?php foreach (\App\Helpers\Media::categoryIcons() as $icon): ?>
                <option value="<?php echo htmlspecialchars($icon); ?>" data-image-url="<?php echo htmlspecialchars(\App\Helpers\Media::categoryUrl($icon)); ?>" <?php echo $form_category['icon_path'] === $icon ? 'selected' : ''; ?>><?php echo htmlspecialchars(basename($icon)); ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="form-group"><label for="category-upload">Загрузить изображение с компьютера</label><input id="category-upload" name="category_upload" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><p class="filter-help">До 10 МБ. PHP подготовит оптимизированное изображение и миниатюру.</p></div>
        <?php foreach (['meta_title' => 'SEO: заголовок', 'meta_description' => 'SEO: описание'] as $field => $label): ?>
            <div class="form-group"><label for="category-<?php echo $field; ?>"><?php echo $label; ?></label><input id="category-<?php echo $field; ?>" name="<?php echo $field; ?>" maxlength="255" placeholder="Генерируется автоматически, если не заполнено" value="<?php echo htmlspecialchars($form_category[$field] ?? ''); ?>"></div>
        <?php endforeach; ?>
        <p class="filter-help">Адрес формируется автоматически из названия и родительских категорий.</p>
        <div class="form-actions"><button type="submit" class="btn"><?php echo $editing_category ? 'Сохранить категорию' : 'Создать категорию'; ?></button><a href="<?php echo APP_URL; ?>/admin?tab=categories" class="btn btn-outline">Отмена</a></div>
    </form>
</div>
<?php if ($editing_category): ?>
<div class="product-form-card">
    <h3>Удаление категории</h3>
    <form method="POST" action="<?php echo APP_URL; ?>/admin?tab=categories" data-ajax="admin" data-confirm="Удалить категорию? Содержимое будет перенесено в выбранный раздел.">
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <input type="hidden" name="action" value="category_delete"><input type="hidden" name="id" value="<?php echo $category_id; ?>">
        <div class="form-group"><label for="category-transfer">Перенести товары и подкатегории в</label><select id="category-transfer" name="transfer_id"><option value="">Не переносить (только пустая категория)</option>
            <?php foreach ($admin_categories as $row): if (in_array((int)$row['id'], $excluded, true)) continue; ?>
                <option value="<?php echo (int)$row['id']; ?>"><?php echo str_repeat('— ', $row['depth']) . htmlspecialchars($row['name']); ?></option>
            <?php endforeach; ?>
        </select></div>
        <button type="submit" class="btn btn-destructive">Удалить категорию</button>
    </form>
</div>
<?php endif; ?>
