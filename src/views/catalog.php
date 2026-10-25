<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Category.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$product_model = new \App\Models\Product($pdo);
$category_model = new \App\Models\Category($pdo);

// Получение фильтров
$search = $_GET['search'] ?? '';
$price_min = isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (int)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (int)$_GET['price_max'] : null;
$brand = $_GET['brand'] ?? '';
$category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;

// Получение товаров
if (!empty($search)) {
    $products = $product_model->search($search, 50, 0);
} else {
    $products = $product_model->getAll(50, 0);
}

// Применение фильтров
if ($price_min !== null || $price_max !== null || !empty($brand) || $category_id !== null) {
    $products = array_filter($products, function($product) use ($price_min, $price_max, $brand, $category_id) {
        if ($price_min !== null && $product['price'] < $price_min) return false;
        if ($price_max !== null && $product['price'] > $price_max) return false;
        if (!empty($brand) && $product['brand'] !== $brand) return false;
        if ($category_id !== null && $product['category_id'] !== $category_id) return false;
        return true;
    });
}

$categories = $category_model->getAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <aside class="sidebar">
                    <h3>Фильтры</h3>
                    <form method="GET" class="filters">
                        <div class="filter-group">
                            <label>Цена</label>
                            <input type="number" name="price_min" placeholder="От" class="filter-input" value="<?php echo htmlspecialchars($price_min ?? ''); ?>">
                            <input type="number" name="price_max" placeholder="До" class="filter-input" value="<?php echo htmlspecialchars($price_max ?? ''); ?>">
                        </div>

                        <div class="filter-group">
                            <label>Бренд</label>
                            <select name="brand" class="filter-input">
                                <option value="">Все бренды</option>
                                <option value="Intel" <?php echo $brand === 'Intel' ? 'selected' : ''; ?>>Intel</option>
                                <option value="AMD" <?php echo $brand === 'AMD' ? 'selected' : ''; ?>>AMD</option>
                                <option value="NVIDIA" <?php echo $brand === 'NVIDIA' ? 'selected' : ''; ?>>NVIDIA</option>
                                <option value="Corsair" <?php echo $brand === 'Corsair' ? 'selected' : ''; ?>>Corsair</option>
                                <option value="Kingston" <?php echo $brand === 'Kingston' ? 'selected' : ''; ?>>Kingston</option>
                                <option value="Samsung" <?php echo $brand === 'Samsung' ? 'selected' : ''; ?>>Samsung</option>
                                <option value="ASUS" <?php echo $brand === 'ASUS' ? 'selected' : ''; ?>>ASUS</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Категория</label>
                            <select name="category" class="filter-input">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id === $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn">Применить фильтры</button>
                    </form>
                </aside>

                <div class="products-section">
                    <h1>Каталог товаров</h1>

                    <?php if (empty($products)): ?>
                        <p>Товары не найдены</p>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="product-card-link">
                                        <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" loading="lazy" width="300" height="300">
                                        <div class="product-info">
                                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="product-price"><?php echo number_format($product['price'], 2, '.', ' '); ?> ₽</p>
                                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?></p>
                                        </div>
                                    </a>
                                    <div class="product-actions">
                                        <form method="POST" action="<?php echo APP_URL; ?>/api/cart/add" class="add-to-cart-form">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                            <button type="submit" class="btn btn-sm">
                                                <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="16" height="16">
                                                В корзину
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 PCComponents Store</p>
    </footer>
</body>
</html>
