<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Category.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$product_model = new \App\Models\Product($pdo);
$category_model = new \App\Models\Category($pdo);

// Build filters array
$filters = [];
$has_filters = false;

$search_query = is_string($_GET['search'] ?? null) ? trim($_GET['search']) : '';
if ($search_query !== '') {
    $filters['search'] = $search_query;
    $has_filters = true;
}

if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $filters['category_id'] = (int)$_GET['category'];
    $has_filters = true;
}

if (isset($_GET['price_min']) && is_numeric($_GET['price_min']) && $_GET['price_min'] !== '') {
    $filters['price_min'] = (float)$_GET['price_min'];
    $has_filters = true;
}

if (isset($_GET['price_max']) && is_numeric($_GET['price_max']) && $_GET['price_max'] !== '') {
    $filters['price_max'] = (float)$_GET['price_max'];
    $has_filters = true;
}

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $filters['brand'] = $_GET['brand'];
    $has_filters = true;
}

// Parse specification filters from GET parameters
// Format: spec[Spec Name][]=Value1&spec[Spec Name][]=Value2
if (isset($_GET['spec']) && is_array($_GET['spec'])) {
    $specifications = [];
    foreach ($_GET['spec'] as $spec_name => $spec_values) {
        if (is_array($spec_values) && !empty($spec_values)) {
            $specifications[$spec_name] = array_values($spec_values);
        }
    }
    if (!empty($specifications)) {
        $filters['specifications'] = $specifications;
        $has_filters = true;
    }
}

// Get products using new filtering method
if ($has_filters) {
    $products = $product_model->getWithFilters($filters, 100, 0);
} else {
    $products = $product_model->getAll(50, 0);
}

$categories = $category_model->getAll();
$selected_category = $filters['category_id'] ?? null;
$selected_brand = $filters['brand'] ?? '';
$price_min = $filters['price_min'] ?? '';
$price_max = $filters['price_max'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <meta name="description" content="Каталог комплектующих для ПК - процессоры, видеокарты, материнские платы, оперативная память, накопители. Лучшие цены и быстрая доставка.">
    <meta name="keywords" content="каталог компьютерных комплектующих, купить процессор, купить видеокарту, SSD диски">
    <title>Каталог товаров - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
    <link rel="canonical" href="<?php echo APP_URL; ?>/catalog">

    <!-- OpenGraph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Каталог комплектующих для ПК - PCComponents Store">
    <meta property="og:description" content="Широкий выбор комплектующих для персональных компьютеров по выгодным ценам">
    <meta property="og:url" content="<?php echo APP_URL; ?>/catalog">

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
          "name": "Каталог"
        }
      ]
    }
    </script>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="catalog-wrapper">
                <aside class="sticky-aside sidebar">
                    <h3>Фильтры</h3>
                    <form method="GET" id="catalog-filter-form" class="filters">
                        <?php if ($search_query !== ''): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <!-- 1. Price Range (always visible, optional) -->
                        <div class="filter-group">
                            <label>Цена</label>
                            <input type="number" name="price_min" placeholder="От" class="filter-input" value="<?php echo htmlspecialchars($price_min); ?>">
                            <input type="number" name="price_max" placeholder="До" class="filter-input" value="<?php echo htmlspecialchars($price_max); ?>">
                        </div>

                        <!-- 2. Category (always visible, from database) -->
                        <div class="filter-group">
                            <label>Категория</label>
                            <select name="category" id="category-filter" class="filter-input">
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $selected_category === $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 3. Brand (hidden until category selected) -->
                        <div id="brand-filter-container" class="filter-group" style="display: <?php echo $selected_category ? 'block' : 'none'; ?>;">
                            <label>Производитель</label>
                            <select name="brand" id="brand-filter" class="filter-input">
                                <option value="">Все производители</option>
                                <?php if ($selected_brand): ?>
                                    <option value="<?php echo htmlspecialchars($selected_brand); ?>" selected><?php echo htmlspecialchars($selected_brand); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- 4. Dynamic Specification Filters (hidden until category selected) -->
                        <div id="spec-filters-container" style="display: <?php echo $selected_category ? 'block' : 'none'; ?>;">
                            <!-- Dynamically loaded specification filters will appear here -->
                        </div>

                        <button type="submit" class="btn btn-primary">Применить фильтры</button>
                        <button type="button" id="reset-filters" class="btn btn-secondary" style="margin-top: 0.5rem; width: 100%;">Сбросить</button>
                    </form>
                </aside>

                <div class="products-section">
                    <h1><?php echo $search_query !== '' ? 'Результаты поиска' : 'Каталог товаров'; ?></h1>
                    <?php if ($search_query !== ''): ?>
                        <p class="catalog-search-query">По запросу «<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>»</p>
                    <?php endif; ?>

                    <div id="product-count" class="product-count">
                        <?php
                        $count = count($products);
                        if ($count === 0) {
                            echo 'Товары не найдены';
                        } elseif ($count === 1) {
                            echo 'Найден 1 товар';
                        } elseif ($count < 5) {
                            echo "Найдено {$count} товара";
                        } else {
                            echo "Найдено {$count} товаров";
                        }
                        ?>
                    </div>

                    <div id="products-container">
                        <?php if (empty($products)): ?>
                            <div class="no-products">
                                <p>Товары не найдены. Попробуйте изменить фильтры.</p>
                            </div>
                        <?php else: ?>
                            <div class="products-grid">
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card">
                                        <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="product-card-link">
                                            <img
                                                src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="product-image"
                                                loading="lazy"
                                                width="300"
                                                height="300"
                                            >
                                            <div class="product-info">
                                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                                <p class="product-price">
                                                    <?php echo number_format($product['price'], 2, '.', ' '); ?> ₽
                                                    <?php if (!empty($product['old_price'])): ?>
                                                        <del class="old-price"><?php echo number_format($product['old_price'], 0, '.', ' '); ?> ₽</del>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)); ?></p>
                                            </div>
                                        </a>
                                        <div class="product-actions">
                                            <button class="btn btn-sm add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                                <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="16" height="16">
                                                В корзину
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>
    <script src="<?php echo \App\Helpers\Asset::js('main.js'); ?>"></script>
    <script src="<?php echo \App\Helpers\Asset::js('catalog-filters.js'); ?>"></script>
</body>
</html>
