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
                    <button
                        type="button"
                        id="search-trigger"
                        class="btn btn-outline search-trigger"
                        aria-label="Поиск товаров (Ctrl+K)"
                        aria-haspopup="dialog"
                        aria-controls="search-dialog"
                        aria-expanded="false"
                        aria-keyshortcuts="Control+K Meta+K"
                        hidden
                    >
                        <img src="<?php echo APP_URL; ?>/public/images/icons/search.svg" alt="" width="20" height="20">
                        <span class="search-trigger-label">Поиск товаров</span>
                        <kbd class="search-key" data-search-shortcut aria-hidden="true">Ctrl K</kbd>
                    </button>
                    <form action="<?php echo APP_URL; ?>/catalog" method="GET" class="search-form" id="search-fallback" role="search">
                        <input
                            type="search"
                            name="search"
                            placeholder="Поиск товаров..."
                            class="search-input"
                            value="<?php echo htmlspecialchars(is_string($_GET['search'] ?? null) ? $_GET['search'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                            aria-label="Поиск товаров"
                            maxlength="120"
                        >
                        <button type="submit" class="btn btn-icon btn-ghost search-btn" aria-label="Найти">
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
                            <button id="theme-toggle" class="btn btn-icon btn-ghost" aria-label="Переключить тему">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/dark_mode.svg" alt="" width="18" height="18" id="theme-icon" style="display: inline-block;">
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <dialog
        id="search-dialog"
        class="search-dialog"
        aria-labelledby="search-dialog-title"
        data-base-url="<?php echo htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8'); ?>"
    >
        <h2 id="search-dialog-title" class="sr-only">Поиск товаров и категорий</h2>
        <form action="<?php echo APP_URL; ?>/catalog" method="GET" class="search-dialog-form" id="search-dialog-form" role="search">
            <img src="<?php echo APP_URL; ?>/public/images/icons/search.svg" alt="" width="22" height="22" class="search-dialog-icon">
            <input
                type="search"
                name="search"
                id="search-dialog-input"
                class="search-dialog-input"
                placeholder="Что ищем? Например, RTX 4070 или SSD"
                value="<?php echo htmlspecialchars(is_string($_GET['search'] ?? null) ? $_GET['search'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                maxlength="120"
                autocomplete="off"
                spellcheck="false"
                enterkeyhint="search"
                role="combobox"
                aria-label="Поиск товаров и категорий"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="search-results"
                aria-describedby="search-help"
            >
            <button type="button" id="search-clear" class="btn btn-icon btn-ghost search-clear" aria-label="Очистить поиск" hidden>
                <img src="<?php echo APP_URL; ?>/public/images/icons/close.svg" alt="" width="16" height="16">
            </button>
            <button type="button" id="search-close" class="btn btn-ghost search-close" aria-label="Закрыть поиск (Esc)">
                <kbd class="search-key" aria-hidden="true">Esc</kbd>
            </button>
        </form>
        <p id="search-help" class="sr-only">Введите название, бренд или характеристику товара. Стрелки вверх и вниз выбирают результат, Enter открывает его, Escape закрывает поиск.</p>
        <div class="search-body">
            <div id="search-message" class="search-message" hidden>
                <img src="<?php echo APP_URL; ?>/public/images/icons/search_off.svg" alt="" width="28" height="28" id="search-message-icon">
                <strong id="search-message-title"></strong>
                <p id="search-message-description"></p>
            </div>
            <div id="search-results" class="search-results" role="listbox" aria-label="Результаты поиска"></div>
        </div>
        <p id="search-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>
        <div class="search-footer" aria-hidden="true">
            <span><kbd class="search-key">↑</kbd><kbd class="search-key">↓</kbd> выбрать</span>
            <span><kbd class="search-key">↵</kbd> открыть</span>
            <span class="search-footer-label">Поиск по каталогу</span>
        </div>
    </dialog>

    <script src="<?php echo APP_URL; ?>/public/js/theme-toggle.js"></script>
    <script src="<?php echo \App\Helpers\Asset::js('search.js'); ?>" defer></script>
