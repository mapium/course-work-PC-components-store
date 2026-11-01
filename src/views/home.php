<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <meta name="description" content="PCComponents Store - интернет-магазин комплектующих для ПК. Процессоры, видеокарты, материнские платы и другие компоненты по лучшим ценам.">
    <meta name="keywords" content="комплектующие ПК, купить процессор, видеокарта, материнская плата, SSD, оперативная память">
    <title>PCComponents Store - Комплектующие для ПК</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
    <link rel="canonical" href="<?php echo APP_URL; ?>/">

    <!-- OpenGraph теги -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="PCComponents Store - Интернет-магазин комплектующих для ПК">
    <meta property="og:description" content="Лучший выбор комплектующих для ПК по конкурентным ценам. Процессоры, видеокарты, материнские платы, SSD и многое другое.">
    <meta property="og:url" content="<?php echo APP_URL; ?>/">
    <meta property="og:site_name" content="PCComponents Store">

    <!-- Микроразметка Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "PCComponents Store",
      "url": "<?php echo APP_URL; ?>/",
      "logo": "<?php echo APP_URL; ?>/public/images/logo.png",
      "description": "Интернет-магазин комплектующих для персональных компьютеров",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "RU",
        "addressLocality": "Екатеринбург"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+7-343-123-45-67",
        "contactType": "customer service",
        "availableLanguage": "Russian"
      }
    }
    </script>

    <!-- Микроразметка WebSite для поиска -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "PCComponents Store",
      "url": "<?php echo APP_URL; ?>/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo APP_URL; ?>/catalog?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Добро пожаловать в PCComponents Store</h1>
                <p>Лучший выбор комплектующих для ПК по конкурентным ценам</p>
                <a href="<?php echo APP_URL; ?>/catalog" class="btn">Начать покупки</a>
            </div>
        </section>

        <section class="featured-products">
            <div class="container">
                <h2>Популярные товары</h2>
                <div class="products-grid">
                    <?php
                    require_once dirname(__DIR__) . '/config/Database.php';
                    require_once dirname(__DIR__) . '/models/Product.php';

                    $db = new \App\Config\Database();
                    $pdo = $db->connect();
                    $product_model = new \App\Models\Product($pdo);
                    $featured = $product_model->getFeatured(6);

                    foreach ($featured as $product):
                    ?>
                        <div class="product-card">
                            <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="product-card-link">
                                <img
                                    src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name'] . ' - ' . ($product['brand'] ?? '')); ?>"
                                    class="product-image"
                                    loading="lazy"
                                    width="300"
                                    height="300"
                                >
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
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Категории товаров</h2>
                <div class="categories-grid">
                    <?php
                    require_once dirname(__DIR__) . '/models/Category.php';
                    $category_model = new \App\Models\Category($pdo);
                    $categories = $category_model->getAll();

                    foreach ($categories as $category):
                    ?>
                        <div class="category-card">
                            <h3><a href="<?php echo APP_URL; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></h3>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>О компании</h4>
                    <p>PCComponents Store - ваш надежный партнер в выборе комплектующих для ПК.</p>
                </div>
                <div class="footer-section">
                    <h4>Контакты</h4>
                    <p>Email: info@pccomponents.local</p>
                    <p>Телефон: +7 (343) 123-45-67</p>
                    <p>Адрес: г. Екатеринбург, ул. Декабристов, д. 83</p>
                </div>
                <div class="footer-section">
                    <h4>Быстрые ссылки</h4>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>/catalog">Каталог</a></li>
                        <li><a href="<?php echo APP_URL; ?>/about">О нас</a></li>
                        <li><a href="<?php echo APP_URL; ?>/delivery">Доставка</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contacts">Контакты</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 PCComponents Store. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo \App\Helpers\Asset::js('main.js'); ?>"></script>
</body>
</html>
