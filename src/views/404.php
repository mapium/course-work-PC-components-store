<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница не найдена - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="error-page">
                <div class="error-content">
                    <div class="error-icon">
                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4"/>
                            <circle cx="12" cy="16" r="0.5" fill="currentColor"/>
                        </svg>
                    </div>

                    <h1 class="error-title">404</h1>
                    <h2 class="error-subtitle">Страница не найдена</h2>
                    <p class="error-description">
                        К сожалению, запрашиваемая страница не существует или была перемещена.
                        <br>
                        Возможно, вы перешли по устаревшей ссылке или ошиблись при вводе адреса.
                    </p>

                    <div class="error-actions">
                        <a href="<?php echo APP_URL; ?>/" class="btn btn-lg">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/home.svg" alt="" width="20" height="20">
                            На главную
                        </a>
                        <a href="<?php echo APP_URL; ?>/catalog" class="btn btn-outline btn-lg">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/apps.svg" alt="" width="20" height="20">
                            Каталог товаров
                        </a>
                    </div>

                    <div class="error-suggestions">
                        <p class="suggestions-title">Возможно, вас заинтересует:</p>
                        <ul class="suggestions-list">
                            <li>
                                <a href="<?php echo APP_URL; ?>/catalog">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/apps.svg" alt="" width="18" height="18">
                                    Каталог товаров
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo APP_URL; ?>/cart">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="18" height="18">
                                    Корзина покупок
                                </a>
                            </li>
                            <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                            <li>
                                <a href="<?php echo APP_URL; ?>/account">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/person.svg" alt="" width="18" height="18">
                                    Личный кабинет
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>
</body>
</html>
