<?php
/** @var \PDO $pdo */
$catalog = \App\Helpers\Catalog::load($pdo, $_GET, $category ?? null);
if ($catalog['not_found']) http_response_code(404);
$category = $catalog['category'];
$catalog_title = $catalog['search'] !== '' ? 'Поиск: ' . $catalog['search'] : ($category['name'] ?? 'Каталог товаров');
$catalog_description = $category ? \App\Helpers\Seo::categoryDescription($category) : 'Комплектующие для вашего ПК: сравнивайте цены, характеристики и отзывы. Процессоры, видеокарты, материнские платы, память и накопители.';
$breadcrumbs = [['name' => 'Главная', 'url' => APP_URL . '/'], ['name' => 'Каталог', 'url' => APP_URL . '/catalog']];
if ($category) foreach ((new \App\Models\Category($pdo))->trail($category['id']) as $crumb) {
    $breadcrumbs[] = ['name' => $crumb['name'], 'url' => \App\Helpers\Url::category($crumb, $pdo)];
}
$pagination_url = function ($number) use ($catalog) {
    $params = $catalog['params'];
    unset($params['page']);
    if ($number > 1) $params['page'] = $number;
    return APP_URL . $catalog['path'] . ($params ? '?' . http_build_query($params) : '');
};
$seo = [
    'title' => ($category['meta_title'] ?? '') ?: $catalog_title . ' — PCComponents Store',
    'description' => ($category['meta_description'] ?? '') ?: \App\Helpers\Seo::text($catalog_description),
    'url' => $catalog['url'],
    'noindex' => $catalog['not_found'] || (bool)array_diff(array_keys($catalog['params']), ['page']),
    'schemas' => [\App\Helpers\Seo::breadcrumbs($breadcrumbs)]
];
if ($catalog['page'] > 1) {
    $seo['title'] .= ' — страница ' . $catalog['page'];
    $seo['description'] = 'Страница ' . $catalog['page'] . '. ' . $seo['description'];
    $seo['prev'] = $pagination_url($catalog['page'] - 1);
}
if ($catalog['page'] < $catalog['pages']) $seo['next'] = $pagination_url($catalog['page'] + 1);
$list = [];
foreach ($catalog['products'] as $index => $listed) $list[] = ['@type' => 'ListItem', 'position' => ($catalog['page'] - 1) * \App\Helpers\Catalog::PAGE_SIZE + $index + 1, 'url' => \App\Helpers\Url::product($listed, $pdo), 'name' => $listed['name']];
$seo['schemas'][] = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $list];
$card_return_to = $catalog['path'] . ($catalog['params'] ? '?' . http_build_query($catalog['params']) : '');
?>
<!DOCTYPE html>
<html lang="ru">
<head><?php include __DIR__ . '/includes/head.php'; ?></head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main data-catalog-url="<?php echo htmlspecialchars($catalog['url']); ?>">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Хлебные крошки">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index): ?><span aria-hidden="true">›</span><?php endif; ?>
                    <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['name']); ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="catalog-heading">
                <h1><?php echo htmlspecialchars($catalog_title); ?></h1>
                <p><?php echo htmlspecialchars($catalog_description); ?></p>
                <nav class="category-links" aria-label="<?php echo $category ? 'Подкатегории' : 'Категории товаров'; ?>">
                    <?php $catalog_children = $category ? (new \App\Models\Category($pdo))->getSubcategories($category['id']) : (new \App\Models\Category($pdo))->getAll(); ?>
                    <?php foreach ($catalog_children as $child): ?>
                        <a href="<?php echo htmlspecialchars(\App\Helpers\Url::category($child, $pdo)); ?>" class="btn btn-outline btn-sm"><?php echo htmlspecialchars($child['name']); ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="catalog-wrapper">
                <aside class="sticky-aside sidebar">
                    <h2 class="filters-title">Фильтры</h2>
                    <?php include __DIR__ . '/includes/catalog-filters.php'; ?>
                </aside>
                <section class="products-section" aria-label="Товары каталога">
                    <div class="catalog-toolbar">
                        <p id="product-count" class="product-count" role="status">Найдено товаров: <?php echo $catalog['total']; ?></p>
                        <label for="catalog-sort">Сортировка
                            <select id="catalog-sort" name="sort" form="catalog-filter-form">
                                <?php foreach (\App\Helpers\Catalog::SORTS as $value => $label): ?>
                                    <?php if ($value === 'relevance' && $catalog['search'] === '') continue; ?>
                                    <option value="<?php echo $value; ?>" <?php echo $catalog['sort'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div id="products-container">
                        <?php if (!$catalog['products']): ?>
                            <div class="no-products"><p><?php echo $catalog['not_found'] ? 'Такой страницы каталога нет.' : 'Товары не найдены. Попробуйте изменить фильтры.'; ?></p></div>
                        <?php else: ?>
                            <div class="products-grid">
                                <?php foreach ($catalog['products'] as $card_product): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($catalog['pages'] > 1): ?>
                        <nav class="catalog-pagination" aria-label="Страницы каталога">
                            <?php if ($catalog['page'] > 1): ?><a data-catalog-link href="<?php echo htmlspecialchars($pagination_url($catalog['page'] - 1)); ?>" class="btn btn-outline">← Назад</a><?php endif; ?>
                            <?php for ($number = max(1, $catalog['page'] - 2); $number <= min($catalog['pages'], $catalog['page'] + 2); $number++): ?>
                                <a data-catalog-link href="<?php echo htmlspecialchars($pagination_url($number)); ?>" class="btn <?php echo $number === $catalog['page'] ? '' : 'btn-outline'; ?>" <?php echo $number === $catalog['page'] ? 'aria-current="page"' : ''; ?>><?php echo $number; ?></a>
                            <?php endfor; ?>
                            <?php if ($catalog['page'] < $catalog['pages']): ?><a data-catalog-link href="<?php echo htmlspecialchars($pagination_url($catalog['page'] + 1)); ?>" class="btn btn-outline">Далее →</a><?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
