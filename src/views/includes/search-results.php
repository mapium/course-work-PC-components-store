<?php
/** @var \PDO $pdo */
// Поиск, история, цены, наличие и ссылки готовятся на сервере.
$header_search_query = $header_search_query ?? \App\Helpers\Request::text($_GET, 'search');
$header_search_results = $header_search_results ?? ($header_search_query !== '' ? \App\Helpers\Search::results($pdo, $header_search_query) : ['products' => [], 'categories' => []]);
$search_groups = [];
if ($header_search_query !== '') {
    $header_search_items = [];
    foreach ($header_search_results['products'] as $search_product) {
        $header_search_items[] = [
            'title' => $search_product['name'], 'kind' => 'product', 'icon' => 'memory', 'image' => $search_product['main_image'],
            'href' => \App\Helpers\Url::product($search_product, $pdo),
            'meta' => ($search_product['category_name'] ?? $search_product['brand'] ?? 'Товар') . ' · ' . ($search_product['stock'] > 0 ? 'В наличии' : 'Нет в наличии'),
            'price' => number_format($search_product['price'], 0, '', ' ') . ' ₽'
        ];
    }
    if ($header_search_items) $search_groups[] = ['title' => 'Товары', 'items' => $header_search_items];
    $header_search_items = [];
    foreach ($header_search_results['categories'] as $search_category) {
        $header_search_items[] = ['title' => $search_category['name'], 'kind' => 'category', 'icon' => 'folder_open', 'href' => \App\Helpers\Url::category($search_category, $pdo)];
    }
    if ($header_search_items) $search_groups[] = ['title' => 'Категории', 'items' => $header_search_items];
    if (!$search_groups) echo '<p class="search-message">Ничего не найдено. Попробуйте другое название или бренд.</p>';
    $search_groups[] = ['title' => 'Каталог', 'items' => [['title' => 'Все результаты по запросу «' . $header_search_query . '»', 'kind' => 'catalog', 'icon' => 'search', 'href' => APP_URL . '/catalog?search=' . rawurlencode($header_search_query)]]];
} else {
    $recent = array_slice($_SESSION['search_history'] ?? [], 0, 3);
    if ($recent) {
        $header_search_items = [];
        foreach ($recent as $recent_query) {
            $header_search_items[] = ['title' => $recent_query, 'kind' => 'suggestion', 'icon' => 'history', 'href' => APP_URL . '/catalog?search=' . rawurlencode($recent_query)];
        }
        $search_groups[] = ['title' => 'Недавно искали', 'items' => $header_search_items];
    }
    $header_search_items = [];
    foreach (\App\Helpers\Search::suggestions() as $suggestion) {
        $header_search_items[] = $suggestion + ['kind' => 'suggestion', 'href' => APP_URL . '/catalog?search=' . rawurlencode($suggestion['title'])];
    }
    $search_groups[] = ['title' => 'Часто ищут', 'items' => $header_search_items];
}
$search_option_index = 0;
?>
<?php foreach ($search_groups as $group_index => $search_group): ?>
    <div class="search-group" role="group" aria-labelledby="search-group-<?php echo $group_index; ?>">
        <div class="search-section-label" id="search-group-<?php echo $group_index; ?>"><?php echo $search_group['title']; ?></div>
        <?php foreach ($search_group['items'] as $search_item): ?>
            <a class="search-option" id="search-option-<?php echo $search_option_index++; ?>" href="<?php echo htmlspecialchars($search_item['href']); ?>" role="option" aria-selected="false" tabindex="-1" data-search-kind="<?php echo $search_item['kind']; ?>">
                <?php if ($search_item['kind'] === 'product'): ?>
                    <span class="search-option-image <?php echo empty($search_item['image']) ? 'is-placeholder' : ''; ?>"><img src="<?php echo empty($search_item['image']) ? APP_URL . '/public/images/icons/memory.svg' : htmlspecialchars(\App\Helpers\Media::url($search_item['image'], 'products', true)); ?>" alt="" width="40" height="40" loading="lazy"></span>
                <?php else: ?><img src="<?php echo APP_URL; ?>/public/images/icons/<?php echo $search_item['icon']; ?>.svg" alt="" width="20" height="20" class="search-option-icon"><?php endif; ?>
                <span class="search-option-copy"><span class="search-option-title"><?php echo htmlspecialchars($search_item['title']); ?></span><?php if (!empty($search_item['meta'])): ?><span class="search-option-meta"><?php echo htmlspecialchars($search_item['meta']); ?></span><?php endif; ?></span>
                <?php if (!empty($search_item['price'])): ?><span class="search-option-price"><?php echo $search_item['price']; ?></span><?php endif; ?>
                <span class="search-option-arrow" aria-hidden="true">↵</span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
