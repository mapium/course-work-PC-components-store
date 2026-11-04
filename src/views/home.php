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
    <?php include dirname(__FILE__) . '/includes/header.php'; 
    require_once dirname(__DIR__) . '/config/Database.php';
    require_once dirname(__DIR__) . '/models/Product.php';

    $db = new \App\Config\Database();
    $pdo = $db->connect();
    $product_model = new \App\Models\Product($pdo);
    ?>

    <main>
        <section class="hero-carousel" aria-label="Специальные предложения">
            <div class="hero-carousel-track" aria-live="off">
                <article class="hero-slide is-active" data-hero-slide aria-hidden="false">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/banner3.png"
                        alt="Водоблок для кастомной системы водяного охлаждения"
                        class="hero-slide-image"
                        width="1584"
                        height="672"
                        fetchpriority="high"
                    >
                    <div class="hero-slide-overlay"></div>
                    <div class="container hero-slide-content">
                        <p class="hero-slide-kicker">Кастомное СВО</p>
                        <h1>Экстремальное охлаждение без компромиссов</h1>
                        <p class="hero-slide-description">Водоблоки и компоненты для тихой системы, стабильного разгона и максимальной производительности.</p>
                        <a href="<?php echo APP_URL; ?>/catalog?search=<?php echo rawurlencode('водоблок'); ?>" class="btn hero-slide-cta">Собрать контур охлаждения <span aria-hidden="true">→</span></a>
                    </div>
                </article>

                <article class="hero-slide" data-hero-slide aria-hidden="true">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/banner2.png"
                        alt="Мощная игровая видеокарта"
                        class="hero-slide-image"
                        width="1584"
                        height="672"
                        loading="lazy"
                    >
                    <div class="hero-slide-overlay"></div>
                    <div class="container hero-slide-content hero-slide-content-right">
                        <p class="hero-slide-kicker">Гейминг · Рендеринг · AI</p>
                        <h2>Максимальный FPS в любых задачах</h2>
                        <p class="hero-slide-description">Новейшие графические ускорители для киберспорта, 3D-моделирования и нейросетей в наличии.</p>
                        <a href="<?php echo APP_URL; ?>/category/graphics-cards" class="btn hero-slide-cta">Смотреть видеокарты <span aria-hidden="true">→</span></a>
                    </div>
                </article>

                <article class="hero-slide" data-hero-slide aria-hidden="true">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/banner1.png"
                        alt="Материнская плата с усиленной системой питания"
                        class="hero-slide-image"
                        width="1584"
                        height="672"
                        loading="lazy"
                    >
                    <div class="hero-slide-overlay"></div>
                    <div class="container hero-slide-content">
                        <p class="hero-slide-kicker">Платформа для разгона</p>
                        <h2>Надежный фундамент вашей сборки</h2>
                        <p class="hero-slide-description">Материнские платы с усиленным питанием VRM и поддержкой PCIe 5.0. Раскройте потенциал процессоров Intel Core и AMD Ryzen.</p>
                        <a href="<?php echo APP_URL; ?>/category/motherboards" class="btn hero-slide-cta">Подобрать материнскую плату</a>
                    </div>
                </article>

                <article class="hero-slide" data-hero-slide aria-hidden="true">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/banner4.png"
                        alt="Комплект из видеокарты и материнской платы для апгрейда"
                        class="hero-slide-image"
                        width="1584"
                        height="672"
                        loading="lazy"
                    >
                    <div class="hero-slide-overlay"></div>
                    <div class="container hero-slide-content">
                        <p class="hero-slide-kicker">Выгода до 15%</p>
                        <h2>Готовые решения для апгрейда</h2>
                        <p class="hero-slide-description">Выгодные комплектующие для быстрой модернизации системы со скидкой до 15%.</p>
                        <a href="<?php echo APP_URL; ?>/catalog" class="btn hero-slide-cta">Смотреть акции <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            </div>

            <button type="button" class="hero-carousel-arrow hero-carousel-prev" data-hero-prev aria-label="Предыдущий баннер">
                <span aria-hidden="true">‹</span>
            </button>
            <button type="button" class="hero-carousel-arrow hero-carousel-next" data-hero-next aria-label="Следующий баннер">
                <span aria-hidden="true">›</span>
            </button>

            <div class="hero-carousel-dots" role="group" aria-label="Выбор баннера">
                <button type="button" class="hero-carousel-dot is-active" data-hero-dot="0" aria-label="Показать баннер 1 из 4" aria-current="true"></button>
                <button type="button" class="hero-carousel-dot" data-hero-dot="1" aria-label="Показать баннер 2 из 4"></button>
                <button type="button" class="hero-carousel-dot" data-hero-dot="2" aria-label="Показать баннер 3 из 4"></button>
                <button type="button" class="hero-carousel-dot" data-hero-dot="3" aria-label="Показать баннер 4 из 4"></button>
            </div>
        </section>
        <section class="categories">
            <div class="container">
                <h2>Категории товаров</h2>
                <div class="categories-carousel-wrapper">
                    <div class="categories-carousel">
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
            </div>
        </section>
        <section class="featured-products">
            <div class="container">
                <h2>Популярные товары</h2>
                <div class="products-grid">
                    <?php
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
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script src="<?php echo \App\Helpers\Asset::js('main.js'); ?>"></script>
</body>
</html>
