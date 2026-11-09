    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">PCComponents Store</h3>
                    <p class="footer-description">
                        Ваш надежный партнер в мире компьютерных комплектующих.
                        Качество, надежность, доступные цены.
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Каталог</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo APP_URL; ?>/catalog">Все товары</a></li>
                        <?php foreach ((new \App\Models\Category($pdo))->getAll() as $footer_category): ?>
                            <li><a href="<?php echo htmlspecialchars(\App\Helpers\Url::category($footer_category, $pdo)); ?>"><?php echo htmlspecialchars($footer_category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Информация</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo APP_URL; ?>/">О магазине</a></li>
                        <li><a href="<?php echo APP_URL; ?>/cart">Корзина</a></li>
                        <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                            <li><a href="<?php echo APP_URL; ?>/account">Личный кабинет</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo APP_URL; ?>/auth">Вход / Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Контакты</h4>
                    <ul class="footer-contacts">
                        <li>
                            <img src="<?php echo APP_URL; ?>/public/images/icons/phone.svg" alt="" width="16" height="16">
                            <span>+7 (999) 123-45-67</span>
                        </li>
                        <li>
                            <img src="<?php echo APP_URL; ?>/public/images/icons/email.svg" alt="" width="16" height="16">
                            <span>info@pccomponents.ru</span>
                        </li>
                        <li>
                            <img src="<?php echo APP_URL; ?>/public/images/icons/schedule.svg" alt="" width="16" height="16">
                            <span>Пн-Пт: 9:00 - 18:00</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 PCComponents Store. Все права защищены.</p>
            </div>
        </div>
    </footer>
