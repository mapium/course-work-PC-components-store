    <!-- Контактная информация -->
    <div class="topbar">
        <div class="container">
            <div class="topbar-content">
                <div class="topbar-contacts">
                    <a href="tel:+73431234567" class="topbar-item">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/phone.svg" alt="" width="16" height="16">
                        +7 (343) 123-45-67
                    </a>
                    <span class="topbar-item">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/location_on.svg" alt="" width="16" height="16">
                        г. Екатеринбург, ул. Декабристов, д. 83
                    </span>
                    <span class="topbar-item">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/schedule.svg" alt="" width="16" height="16">
                        Пн-Пт: 9:00-20:00, Сб-Вс: 10:00-18:00
                    </span>
                </div>
            </div>
        </div>
    </div>

    <header>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-content">
                    <a href="<?php echo APP_URL; ?>/" class="logo">
                        <h1>PCComponents Store</h1>
                    </a>

                    <!-- Поиск -->
                    <form action="<?php echo APP_URL; ?>/catalog" method="GET" class="search-form">
                        <input
                            type="search"
                            name="search"
                            placeholder="Поиск товаров..."
                            class="search-input"
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            aria-label="Поиск товаров"
                        >
                        <button type="submit" class="btn-icon btn-ghost search-btn" aria-label="Найти">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/search.svg" alt="" width="20" height="20">
                        </button>
                    </form>

                    <ul class="nav-links">
                        <li>
                            <a href="<?php echo APP_URL; ?>/catalog">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/apps.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                Каталог
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/cart">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                Корзина
                            </a>
                        </li>
                        <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                            <li>
                                <a href="<?php echo APP_URL; ?>/account">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/account_circle.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                    Личный кабинет
                                </a>
                            </li>
                            <?php if (\App\Helpers\Auth::getCurrentRole() === 'admin' || \App\Helpers\Auth::getCurrentRole() === 'manager'): ?>
                                <li>
                                    <a href="<?php echo APP_URL; ?>/admin">
                                        <img src="<?php echo APP_URL; ?>/public/images/icons/admin_panel_settings.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                        Админ-панель
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="<?php echo APP_URL; ?>/logout">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/logout.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                    Выход
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="<?php echo APP_URL; ?>/auth">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/login.svg" alt="" width="18" height="18" style="display: inline-block; vertical-align: middle;">
                                    Вход
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <button id="theme-toggle" class="btn-icon btn-ghost" aria-label="Переключить тему">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/dark_mode.svg" alt="" width="18" height="18" id="theme-icon" style="display: inline-block;">
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <script src="<?php echo APP_URL; ?>/public/js/theme-toggle.js"></script>
