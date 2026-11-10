<?php
/** @var \PDO $pdo */
require_once dirname(__DIR__) . '/models/Product.php';

$product_model = new \App\Models\Product($pdo);
$product = $product_model->getByPath(\App\Helpers\Request::text($_GET, 'product_path'));
if (!$product) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}
$product_id = (int)$product['id'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    \App\Helpers\BrowsingHistory::remember($pdo, $product_id);
}
$product_path = \App\Helpers\Url::productPath($product, $pdo);
$product_url = APP_URL . $product_path;
$product['description'] = \App\Helpers\Seo::productDescription($product);
$product_images = $product_model->getImages($product_id);
$category_trail = (new \App\Models\Category($pdo))->trail($product['category_id']);
$specifications = $product_model->getSpecifications($product_id);
$reviews = $product_model->getReviews($product_id);
$similar_products = $product_model->getSimilarProducts($product_id, $product['category_id'], 4);
$average_rating = \App\Helpers\Rating::normalize($product['average_rating']);
$review_count = (int)$product['review_count'];
$current_tab = \App\Helpers\Request::text($_GET, 'tab', 'description');
if (!in_array($current_tab, ['description', 'specifications', 'reviews'], true)) {
    $current_tab = 'description';
}

// Режим формы и необходимость оценки определяются в PHP.
$review_action = \App\Helpers\Request::text($_GET, 'review_action', 'add');
$selected_review_id = \App\Helpers\Request::integer($_GET, 'review_id');
$selected_review = null;
foreach ($reviews as $entry) {
    foreach (array_merge([$entry], $entry['replies']) as $candidate) {
        if ((int)$candidate['id'] === $selected_review_id) {
            $selected_review = $candidate;
            break 2;
        }
    }
}
$review_editor = ['action' => 'add', 'title' => 'Оставить отзыв', 'parent_id' => '', 'review_id' => '', 'rating' => 0, 'comment' => '', 'show_rating' => true];
$review_form_open = isset($_GET['review_action']) && \App\Helpers\Auth::isLoggedIn();
if ($review_form_open) {
    $current_tab = 'reviews';
    if ($review_action === 'reply' && $selected_review && $selected_review['parent_id'] === null) {
        $review_editor['title'] = 'Ответить на отзыв';
        $review_editor['parent_id'] = $selected_review['id'];
        $review_editor['show_rating'] = false;
    } elseif ($review_action === 'edit' && $selected_review && $selected_review['user_id'] == \App\Helpers\Auth::getUserId()) {
        $review_editor = [
            'action' => 'update', 'title' => $selected_review['parent_id'] === null ? 'Редактировать отзыв' : 'Редактировать ответ',
            'parent_id' => '', 'review_id' => $selected_review['id'], 'rating' => $selected_review['rating'],
            'comment' => $selected_review['comment'], 'show_rating' => $selected_review['parent_id'] === null
        ];
    } elseif ($review_action !== 'add') {
        $review_form_open = false;
    }
}

$product_image_url = \App\Helpers\Media::url($product['main_image'] ?: 'placeholder.jpg');
$product_schema = [
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product['name'],
    'description' => $product['description'] ?? '', 'sku' => $product['sku'] ?: 'SKU-' . $product_id,
    'image' => $product_images ? array_map(function ($image) { return \App\Helpers\Media::url($image['image_path']); }, $product_images) : [$product_image_url],
    'offers' => [
        '@type' => 'Offer', 'url' => $product_url, 'priceCurrency' => 'RUB',
        'price' => $product['price'],
        'itemCondition' => 'https://schema.org/NewCondition',
        'availability' => $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'seller' => ['@type' => 'Organization', 'name' => 'PCComponents Store']
    ]
];
if ($product['brand']) $product_schema['brand'] = ['@type' => 'Brand', 'name' => $product['brand']];
if ($review_count > 0) {
    $product_schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $average_rating, 'reviewCount' => $review_count, 'bestRating' => 5, 'worstRating' => 1];
}
$breadcrumbs = [['name' => 'Главная', 'url' => APP_URL . '/'], ['name' => 'Каталог', 'url' => APP_URL . '/catalog']];
foreach ($category_trail as $crumb) $breadcrumbs[] = ['name' => $crumb['name'], 'url' => \App\Helpers\Url::category($crumb, $pdo)];
$breadcrumbs[] = ['name' => $product['name'], 'url' => $product_url];
$seo = [
    'title' => $product['meta_title'] ?: $product['name'] . ' — купить в PCComponents Store',
    'description' => $product['meta_description'] ?: \App\Helpers\Seo::text($product['description']),
    'keywords' => $product['meta_keywords'] ?: $product['name'] . ', ' . ($product['brand'] ?? '') . ', купить',
    'url' => $product_url, 'image' => $product_image_url, 'type' => 'product', 'price' => $product['price'],
    'noindex' => isset($_GET['review_action']), 'schemas' => [$product_schema, \App\Helpers\Seo::breadcrumbs($breadcrumbs)]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('product-tabs.css'); ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main data-product-page>
        <div class="container">
            <nav class="breadcrumbs" aria-label="Хлебные крошки">
                <a href="<?php echo APP_URL; ?>/">Главная</a> &raquo;
                <a href="<?php echo APP_URL; ?>/catalog">Каталог</a> &raquo;
                <?php foreach ($category_trail as $crumb): ?>
                    <a href="<?php echo htmlspecialchars(\App\Helpers\Url::category($crumb, $pdo)); ?>"><?php echo htmlspecialchars($crumb['name']); ?></a> &raquo;
                <?php endforeach; ?>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
            <div class="product-detail">
                <?php include __DIR__ . '/includes/product-gallery.php'; ?>
                <div class="product-info-container">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if ($product['brand']): ?><p class="product-brand">Бренд: <strong><?php echo htmlspecialchars($product['brand']); ?></strong></p><?php endif; ?>
                    <?php $rating_value = $average_rating; $rating_count = $review_count; include __DIR__ . '/includes/rating.php'; ?>
                    <p class="product-price-large"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</p>
                    <p class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>"><?php echo $product['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?></p>
                    <p class="product-order-count">Количество заказов: <?php echo number_format((int)$product['orders_count'], 0, '', ' '); ?></p>
                    <div class="product-description"><p><?php echo htmlspecialchars($product['description'] ?? ''); ?></p></div>
                    <?php if ($product['stock'] > 0): ?>
                        <div class="product-actions">
                            <?php
                            $cart_product = $product;
                            $cart_return_to = $product_path;
                            $cart_compact = false;
                            include __DIR__ . '/includes/product-cart.php';
                            ?>
                        </div>
                    <?php else: ?><button class="btn btn-outline" disabled>Товар недоступен</button><?php endif; ?>
                </div>
            </div>
            <div class="product-tabs" data-tabs>
                <div class="tabs-nav">
                    <?php foreach (['description' => 'Описание', 'specifications' => 'Характеристики', 'reviews' => 'Отзывы (' . $review_count . ')'] as $tab_id => $tab_label): ?>
                        <a href="<?php echo htmlspecialchars($product_url); ?>?tab=<?php echo $tab_id; ?>#<?php echo $tab_id; ?>" class="tab-btn <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" data-tab="<?php echo $tab_id; ?>"><?php echo $tab_label; ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="tab-content <?php echo $current_tab === 'description' ? 'active' : ''; ?>" id="description">
                    <div class="product-detailed-description">
                        <h2>Подробное описание</h2>
                        <p><?php echo nl2br(htmlspecialchars($product['detailed_description'] ?: ($product['description'] ?? ''))); ?></p>
                    </div>
                </div>
                <div class="tab-content <?php echo $current_tab === 'specifications' ? 'active' : ''; ?>" id="specifications">
                    <?php if ($specifications): ?>
                        <h2>Технические характеристики</h2>
                        <table class="specs-table">
                            <?php foreach ($specifications as $spec): ?>
                                <tr><td><?php echo htmlspecialchars($spec['spec_name']); ?></td><td><?php echo htmlspecialchars($spec['spec_value']); ?></td></tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?><p class="no-reviews">Характеристики не указаны</p><?php endif; ?>
                </div>
                <div class="tab-content <?php echo $current_tab === 'reviews' ? 'active' : ''; ?>" id="reviews">
                    <h2>Отзывы покупателей</h2>
                    <?php if ($reviews): ?>
                        <div class="reviews-container">
                            <?php foreach ($reviews as $review_entry): $review_is_reply = false; include __DIR__ . '/includes/review-card.php'; endforeach; ?>
                        </div>
                    <?php else: ?><div class="no-reviews"><p>Пока нет отзывов. Будьте первым!</p></div><?php endif; ?>
                    <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                        <?php include __DIR__ . '/includes/review-form.php'; ?>
                    <?php else: ?><p class="login-prompt"><a href="<?php echo APP_URL; ?>/auth">Войдите</a>, чтобы оставить отзыв</p><?php endif; ?>
                </div>
            </div>
            <?php
            $product_section = [
                'id' => 'similar-products', 'title' => 'Похожие товары',
                'description' => 'Альтернативы из той же категории с близкими характеристиками и ценой.',
                'products' => $similar_products, 'return_to' => $product_path,
                'empty_message' => 'Пока нет похожих товаров в каталоге. Посмотрите другие комплектующие.'
            ];
            include __DIR__ . '/includes/product-section.php';
            ?>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
