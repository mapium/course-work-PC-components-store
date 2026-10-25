<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Product.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$product_model = new \App\Models\Product($pdo);

$product_id = $_GET['id'] ?? 0;
$product = $product_model->getById($product_id);
$specifications = $product_model->getSpecifications($product_id);
$reviews = $product_model->getReviews($product_id);
$similar_products = [];

if ($product) {
    $similar_products = $product_model->getSimilarProducts($product_id, $product['category_id'], 4);
}

if (!$product) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Товар не найден</h1>';
    exit;
}

// Вычисляем средний рейтинг
$average_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $average_rating = round($total_rating / count($reviews), 1);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['meta_title'] ?? $product['name'] . ' - купить в PCComponents Store'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($product['meta_description'] ?? substr($product['description'], 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($product['meta_keywords'] ?? $product['brand'] . ', ' . $product['name'] . ', купить'); ?>">
    <link rel="canonical" href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>">

    <!-- OpenGraph теги для социальных сетей -->
    <meta property="og:type" content="product">
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 200)); ?>">
    <meta property="og:image" content="<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>">
    <meta property="og:url" content="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>">
    <meta property="og:site_name" content="PCComponents Store">
    <meta property="product:price:amount" content="<?php echo $product['price']; ?>">
    <meta property="product:price:currency" content="RUB">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 200)); ?>">
    <meta name="twitter:image" content="<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>">

    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('product-tabs.css'); ?>">

    <!-- Микроразметка Schema.org Product -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?php echo addslashes($product['name']); ?>",
      "description": "<?php echo addslashes($product['description']); ?>",
      "sku": "<?php echo addslashes($product['sku'] ?? 'SKU-' . $product['id']); ?>",
      "brand": {
        "@type": "Brand",
        "name": "<?php echo addslashes($product['brand'] ?? 'Unknown'); ?>"
      },
      "image": "<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>",
      "offers": {
        "@type": "Offer",
        "url": "<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>",
        "priceCurrency": "RUB",
        "price": "<?php echo $product['price']; ?>",
        "priceValidUntil": "<?php echo date('Y-12-31'); ?>",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "<?php echo $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
        "seller": {
          "@type": "Organization",
          "name": "PCComponents Store"
        }
      }
      <?php if ($average_rating > 0): ?>
      ,
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $average_rating; ?>",
        "reviewCount": "<?php echo count($reviews); ?>",
        "bestRating": "5",
        "worstRating": "1"
      }
      <?php endif; ?>
    }
    </script>

    <!-- Микроразметка BreadcrumbList -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Главная",
          "item": "<?php echo APP_URL; ?>/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Каталог",
          "item": "<?php echo APP_URL; ?>/catalog"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?php echo addslashes($product['name']); ?>"
        }
      ]
    }
    </script>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <!-- Хлебные крошки -->
            <nav class="breadcrumbs">
                <a href="<?php echo APP_URL; ?>/">Главная</a> &raquo;
                <a href="<?php echo APP_URL; ?>/catalog">Каталог</a> &raquo;
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <!-- Основная информация о товаре -->
            <div class="product-detail">
                <div class="product-image-container">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($product['name'] . ' - ' . ($product['brand'] ?? '')); ?>"
                        class="product-image-large"
                        loading="lazy"
                        width="600"
                        height="600"
                    >
                </div>

                <div class="product-info-container">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>

                    <?php if ($product['brand']): ?>
                        <p class="product-brand">Бренд: <strong><?php echo htmlspecialchars($product['brand']); ?></strong></p>
                    <?php endif; ?>

                    <?php if ($average_rating > 0): ?>
                        <div class="product-rating">
                            <span class="rating-stars">★★★★★</span>
                            <span class="rating-value"><?php echo $average_rating; ?></span>
                            <span class="rating-count">(<?php echo count($reviews); ?> отзывов)</span>
                        </div>
                    <?php endif; ?>

                    <p class="product-price-large"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</p>

                    <p class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $product['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                    </p>

                    <div class="product-description">
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="product-actions">
                            <form method="POST" action="<?php echo APP_URL; ?>/api/cart/add" id="add-to-cart-form">
                                <div class="quantity-selector">
                                    <label for="quantity">Количество:</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                <button type="submit" class="btn btn-primary">Добавить в корзину</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline" disabled>Товар недоступен</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Вкладки -->
            <div class="product-tabs">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="description">Описание</button>
                    <button class="tab-btn" data-tab="specifications">Характеристики</button>
                    <button class="tab-btn" data-tab="reviews">Отзывы (<?php echo count($reviews); ?>)</button>
                </div>

                <!-- Описание -->
                <div class="tab-content active" id="description">
                    <div class="product-detailed-description">
                        <h2>Подробное описание</h2>
                        <p><?php echo nl2br(htmlspecialchars($product['detailed_description'] ?? $product['description'])); ?></p>
                    </div>
                </div>

                <!-- Характеристики -->
                <div class="tab-content" id="specifications">
                    <?php if (!empty($specifications)): ?>
                        <h2>Технические характеристики</h2>
                        <table class="specs-table">
                            <?php foreach ($specifications as $spec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                    <td><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p class="no-reviews">Характеристики не указаны</p>
                    <?php endif; ?>
                </div>

                <!-- Отзывы -->
                <div class="tab-content" id="reviews">
                    <h2>Отзывы покупателей</h2>

                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-container">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div>
                                            <div class="review-author"><?php echo htmlspecialchars($review['full_name'] ?? $review['username'] ?? 'Анонимный'); ?></div>
                                            <div class="review-rating">
                                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                            </div>
                                        </div>
                                        <div class="review-date"><?php echo date('d.m.Y', strtotime($review['created_at'])); ?></div>
                                    </div>
                                    <div class="review-text">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-reviews">
                            <p>Пока нет отзывов. Будьте первым!</p>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                        <button class="btn btn-secondary add-review-btn">Оставить отзыв</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Похожие товары -->
            <?php if (!empty($similar_products)): ?>
                <section class="similar-products">
                    <h2>Похожие товары</h2>
                    <div class="products-grid">
                        <?php foreach ($similar_products as $similar): ?>
                            <div class="product-card">
                                <img
                                    src="<?php echo APP_URL; ?>/public/images/<?php echo htmlspecialchars($similar['main_image'] ?? 'placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($similar['name']); ?>"
                                    class="product-image"
                                    loading="lazy"
                                    width="300"
                                    height="300"
                                >
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($similar['name']); ?></h3>
                                    <p class="product-price"><?php echo number_format($similar['price'], 0, '', ' '); ?> ₽</p>
                                    <p class="product-description"><?php echo htmlspecialchars(substr($similar['description'], 0, 100)); ?>…</p>
                                    <a href="<?php echo APP_URL; ?>/product/<?php echo $similar['id']; ?>" class="btn">Подробнее</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script>
        // Переключение вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                switchTab(this.getAttribute('data-tab'));
            });

            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    switchTab(this.getAttribute('data-tab'));
                }
            });
        });

        function switchTab(tabName) {
            // Удаляем active со всех кнопок и контента
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Добавляем active к выбранным
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
    </script>
</body>
</html>
