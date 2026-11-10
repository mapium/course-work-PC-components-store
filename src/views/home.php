
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php
    /** @var \PDO $pdo */
    /** @var \App\Models\Category $category_model */
    /** @var array $admin_categories */
    $seo = ['schemas' => [
        ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'PCComponents Store', 'url' => APP_URL . '/',
            'logo' => \App\Helpers\Asset::image('pc_logo.svg'),
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'RU', 'addressLocality' => 'Екатеринбург'],
            'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => '+7-343-123-45-67', 'contactType' => 'customer service', 'availableLanguage' => 'Russian']],
        ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'PCComponents Store', 'url' => APP_URL . '/',
            'potentialAction' => ['@type' => 'SearchAction', 'target' => APP_URL . '/catalog?search={search_term_string}', 'query-input' => 'required name=search_term_string']]
    ]];
    include __DIR__ . '/includes/head.php';
    ?>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; 
    require_once dirname(__DIR__) . '/models/Product.php';

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
        <div class="container"><h2>Категории товаров</h2></div>
        <section class="categories">
            
            <div class="container">
                
                <div class="categories-carousel-wrapper">
                    <div class="categories-carousel">
                        <?php
                        require_once dirname(__DIR__) . '/models/Category.php';
                        $category_model = new \App\Models\Category($pdo);
                        $categories = $category_model->getAll();

                        foreach ($categories as $category):
                        ?>
                            <div class="category-card">
                                <?php if ($category['icon_path']): ?><img src="<?php echo htmlspecialchars(\App\Helpers\Media::categoryUrl($category['icon_path'])); ?>" class="category-icon" alt="" width="48" height="48" loading="lazy"><?php endif; ?>
                                <h3><a href="<?php echo htmlspecialchars(\App\Helpers\Url::category($category, $pdo)); ?>"><?php echo htmlspecialchars($category['name']); ?></a></h3>
                                <p><?php echo htmlspecialchars(\App\Helpers\Seo::categoryDescription($category)); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            <?php
            $recent_ids = \App\Helpers\BrowsingHistory::recentIds($pdo);
            $product_section = [
                'id' => 'recommended-products', 'title' => 'Рекомендуемые товары',
                'description' => $recent_ids ? 'Подборка с учётом просмотренных категорий и рекомендаций магазина.' : 'Выбранные товары магазина: популярные модели для вашей сборки.',
                'products' => $product_model->getRecommended(6, $recent_ids), 'return_to' => '/',
                'empty_message' => 'Рекомендации появятся, когда в каталоге будут доступные товары.'
            ];
            include __DIR__ . '/includes/product-section.php';
            ?>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script src="<?php echo \App\Helpers\Asset::js('hero-carousel.js'); ?>" defer></script>
    <script src="<?php echo \App\Helpers\Asset::js('category-carousel.js'); ?>" defer></script>
</body>
</html>
