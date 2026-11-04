<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Category.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$category_model = new \App\Models\Category($pdo);
$product_model = new \App\Models\Product($pdo);

$slug = $_GET['slug'] ?? '';
$category = $category_model->getBySlug($slug);

if (!$category) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Категория не найдена</h1>';
    exit;
}

$products = $product_model->getByCategoryId($category['id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <title><?php echo htmlspecialchars($category['name']); ?> - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
</head>
<body>
<?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            <p><?php echo htmlspecialchars($category['description']); ?></p>

            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price"><?php echo number_format($product['price'], 2, '.', ' '); ?> ₽</p>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?></p>
                            <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="btn">Подробнее</a>
                            <button class="btn" data-add-to-cart="<?php echo $product['id']; ?>">В корзину</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script src="<?php echo \App\Helpers\Asset::js('main.js'); ?>"></script>
</body>
</html>
