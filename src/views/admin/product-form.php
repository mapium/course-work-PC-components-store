<?php
$editing_product = $product_form_action === 'edit';
$form_product_id = $editing_product ? \App\Helpers\Request::integer($_GET, 'id') : null;
$form_product = $editing_product ? $product_model->getById($form_product_id, true) : [
    'name' => '', 'category_id' => '', 'price' => '', 'old_price' => '', 'stock' => 0,
    'brand' => '', 'sku' => '', 'description' => '', 'detailed_description' => '', 'main_image' => '',
    'is_active' => 1, 'meta_title' => '', 'meta_description' => '', 'meta_keywords' => ''
];
if (!$form_product) {
    echo '<div class="alert alert-error">Товар не найден</div>';
    return;
}
$form_specs = [];
if ($editing_product) {
    foreach ($product_model->getSpecifications($form_product_id) as $spec) {
        $form_specs[] = ['name' => $spec['spec_name'], 'value' => $spec['spec_value']];
    }
}
$draft = $_SESSION['admin_product_draft'] ?? null;
unset($_SESSION['admin_product_draft']);
if ($draft && $draft['id'] === $form_product_id) {
    $data = $draft['data'];
    foreach (array_keys($form_product) as $field) {
        if (array_key_exists($field, $data) && is_scalar($data[$field])) {
            $form_product[$field] = $data[$field];
        }
    }
    $form_product['is_active'] = isset($data['is_active']) ? 1 : 0;
    $form_specs = is_array($data['specifications'] ?? null) ? array_values(array_filter($data['specifications'], 'is_array')) : [];
}
if (!$form_specs) {
    $form_specs[] = ['name' => '', 'value' => ''];
}
$form_categories = (new \App\Models\Category($pdo))->getTree(true);
$form_images = (new \App\Controllers\AdminProductController($pdo))->imageNames();
$form_gallery = $editing_product ? array_column($product_model->getImages($form_product_id), 'image_path') : [];
if ($draft && $draft['id'] === $form_product_id && is_array($draft['data']['gallery'] ?? null)) $form_gallery = array_values(array_intersect($draft['data']['gallery'], $form_images));
$spec_names = $pdo->query('SELECT DISTINCT spec_name FROM product_specifications ORDER BY spec_name')->fetchAll(\PDO::FETCH_COLUMN);
?>
<div class="product-form-card">
    <h3><?php echo $editing_product ? 'Редактирование товара' : 'Добавление товара'; ?></h3>
    <form method="POST" action="<?php echo APP_URL; ?>/admin?tab=products" enctype="multipart/form-data" data-product-form data-ajax="admin">
        <input type="hidden" name="action" value="<?php echo $editing_product ? 'update' : 'create'; ?>">
        <?php if ($editing_product): ?><input type="hidden" name="id" value="<?php echo $form_product_id; ?>"><?php endif; ?>
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <div class="form-grid">
            <div class="form-group"><label for="product-name">Название *</label><input id="product-name" type="text" name="name" maxlength="255" required value="<?php echo htmlspecialchars($form_product['name']); ?>"></div>
            <div class="form-group">
                <label for="product-category">Категория *</label>
                <select id="product-category" name="category_id" required>
                    <?php foreach ($form_categories as $category): ?>
                        <option value="<?php echo (int)$category['id']; ?>" <?php echo (int)$form_product['category_id'] === (int)$category['id'] ? 'selected' : ''; ?>><?php echo str_repeat('— ', $category['depth']) . htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label for="product-price">Цена *</label><input id="product-price" type="number" name="price" min="0.01" max="99999999.99" step="0.01" required value="<?php echo htmlspecialchars((string)$form_product['price']); ?>"></div>
            <div class="form-group"><label for="product-old-price">Старая цена</label><input id="product-old-price" type="number" name="old_price" min="0" max="99999999.99" step="0.01" value="<?php echo htmlspecialchars((string)($form_product['old_price'] ?? '')); ?>"></div>
            <div class="form-group"><label for="product-stock">Остаток *</label><input id="product-stock" type="number" name="stock" min="0" required value="<?php echo htmlspecialchars((string)$form_product['stock']); ?>"></div>
            <div class="form-group"><label for="product-brand">Бренд</label><input id="product-brand" type="text" name="brand" maxlength="100" value="<?php echo htmlspecialchars($form_product['brand'] ?? ''); ?>"></div>
            <div class="form-group"><label for="product-sku">SKU</label><input id="product-sku" type="text" name="sku" maxlength="50" value="<?php echo htmlspecialchars($form_product['sku'] ?? ''); ?>"></div>
            <div class="form-group"><label><input type="checkbox" name="is_active" value="1" <?php echo $form_product['is_active'] ? 'checked' : ''; ?>>Активен</label></div>
        </div>
        <div class="form-group">
            <label for="product-image">Главное изображение</label>
            <select id="product-image" name="main_image" data-image-select>
                <option value="">Без изображения</option>
                <?php foreach ($form_images as $image): ?>
                    <option value="<?php echo htmlspecialchars($image); ?>" data-image-url="<?php echo htmlspecialchars(\App\Helpers\Media::url($image, 'products', true)); ?>" <?php echo ($form_product['main_image'] ?? '') === $image ? 'selected' : ''; ?>><?php echo htmlspecialchars($image); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label for="main-upload">Загрузить главное изображение с компьютера</label><input id="main-upload" type="file" name="main_upload" accept="image/jpeg,image/png,image/webp,image/gif"></div>
        <?php include __DIR__ . '/product-media.php'; ?>
        <?php foreach (['description' => ['Описание', 3], 'detailed_description' => ['Подробное описание', 5], 'meta_description' => ['SEO: Meta Description', 2]] as $field => $settings): ?>
            <div class="form-group"><label for="product-<?php echo $field; ?>"><?php echo $settings[0]; ?></label><textarea id="product-<?php echo $field; ?>" name="<?php echo $field; ?>" rows="<?php echo $settings[1]; ?>" <?php echo $field === 'meta_description' ? 'maxlength="255"' : ''; ?>><?php echo htmlspecialchars($form_product[$field] ?? ''); ?></textarea></div>
        <?php endforeach; ?>
        <?php foreach (['meta_title' => 'SEO: Meta Title', 'meta_keywords' => 'SEO: Keywords'] as $field => $label): ?>
            <div class="form-group"><label for="product-<?php echo $field; ?>"><?php echo $label; ?></label><input type="text" id="product-<?php echo $field; ?>" name="<?php echo $field; ?>" maxlength="255" value="<?php echo htmlspecialchars($form_product[$field] ?? ''); ?>"></div>
        <?php endforeach; ?>
        <?php include __DIR__ . '/specifications.php'; ?>
        <p class="filter-help">Пустые описания и SEO-поля заполняются на странице автоматически. Адрес формируется из категории и названия товара.</p>
        <div class="form-actions"><button type="submit" class="btn"><?php echo $editing_product ? 'Сохранить' : 'Создать'; ?></button><a href="<?php echo APP_URL; ?>/admin?tab=products" class="btn btn-outline">Отмена</a></div>
    </form>
</div>
