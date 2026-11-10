<?php
/** @var \PDO $pdo */
/** @var string $information_page */
$information_pages = [
    'about' => ['title' => 'О магазине', 'description' => 'PCComponents Store — комплектующие для игровых и рабочих компьютеров. Узнайте о каталоге, выборе совместимых компонентов и возможностях магазина.'],
    'delivery' => ['title' => 'Доставка', 'description' => 'Как оформить доставку комплектующих в PCComponents Store: стоимость, адрес, получение заказа и отслеживание его статуса.'],
    'payment' => ['title' => 'Способы оплаты', 'description' => 'Доступные способы оплаты в PCComponents Store: банковской картой онлайн и наличными при получении. Выбор оплаты при оформлении заказа.']
];
$information = $information_pages[$information_page];
$breadcrumbs = [['name' => 'Главная', 'url' => APP_URL . '/'], ['name' => $information['title'], 'url' => APP_URL . '/' . $information_page]];
$seo = [
    'title' => $information['title'] . ' — PCComponents Store',
    'description' => $information['description'],
    'url' => APP_URL . '/' . $information_page,
    'schemas' => [\App\Helpers\Seo::breadcrumbs($breadcrumbs)]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head><?php include __DIR__ . '/includes/head.php'; ?></head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main>
        <div class="container information-page">
            <nav class="breadcrumbs" aria-label="Хлебные крошки">
                <a href="<?php echo APP_URL; ?>/">Главная</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page"><?php echo htmlspecialchars($information['title']); ?></span>
            </nav>
            <div class="information-heading">
                <h1><?php echo htmlspecialchars($information['title']); ?></h1>
                <p><?php echo htmlspecialchars($information['description']); ?></p>
                <nav class="information-links" aria-label="Информация для покупателей">
                    <?php foreach ($information_pages as $key => $item): ?>
                        <a href="<?php echo APP_URL . '/' . $key; ?>" class="btn btn-sm <?php echo $key === $information_page ? '' : 'btn-outline'; ?>" <?php echo $key === $information_page ? 'aria-current="page"' : ''; ?>><?php echo htmlspecialchars($item['title']); ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="information-content">
                <?php if ($information_page === 'about'): ?>
                    <section class="card information-card">
                        <h2>Комплектующие для вашей сборки</h2>
                        <p>В PCComponents Store можно подобрать компоненты для домашнего, игрового или рабочего компьютера. В каталоге представлены процессоры, видеокарты, материнские платы, оперативная память, накопители и системы охлаждения.</p>
                        <p>Для любителей индивидуальных сборок есть комплекты кастомного водяного охлаждения и отдельные водоблоки.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Удобный выбор</h2>
                        <ul>
                            <li>Поиск по названию, бренду и характеристикам.</li>
                            <li>Фильтры по категории, цене, производителю и параметрам.</li>
                            <li>Фотографии, характеристики и отзывы в карточке товара.</li>
                            <li>Корзина с выбором количества и история заказов в личном кабинете.</li>
                        </ul>
                    </section>
                    <section class="card information-card">
                        <h2>Проверьте совместимость</h2>
                        <p>Перед заказом сверяйте сокет процессора и материнской платы, тип оперативной памяти, размеры видеокарты и требования к блоку питания.</p>
                        <p>Для СВО дополнительно проверьте крепления водоблока, место под радиатор и совместимость материалов контура. Информация о параметрах находится на странице каждого товара.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Как сделать заказ</h2>
                        <ol>
                            <li>Выберите товары в каталоге и добавьте их в корзину.</li>
                            <li>Войдите в аккаунт или зарегистрируйтесь.</li>
                            <li>Укажите контакты, адрес и способ оплаты.</li>
                            <li>Следите за статусом заказа в личном кабинете.</li>
                        </ol>
                    </section>
                <?php elseif ($information_page === 'delivery'): ?>
                    <section class="card information-card">
                        <h2>Курьерская доставка</h2>
                        <p>В текущем оформлении заказа используется доставка курьером по указанному адресу. Стоимость доставки — <strong>300 ₽ за заказ</strong>, независимо от количества товаров.</p>
                        <p>Она отображается отдельной строкой в корзине и при оформлении, а затем включается в итоговую сумму заказа.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Адрес и контакты</h2>
                        <p>Укажите город, улицу, номер дома и квартиры, имя получателя и телефон. При необходимости добавьте почтовый индекс.</p>
                        <p>В комментарии можно оставить номер подъезда, код домофона или другую информацию для курьера. Перед отправкой формы проверьте адрес и контактные данные.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Статус заказа</h2>
                        <p>После оформления заказ появится в <a href="<?php echo APP_URL; ?>/account">личном кабинете</a>. Там можно посмотреть состав, итоговую сумму и текущий статус.</p>
                        <p>Основные этапы: ожидание обработки, обработка, сборка, отправка и доставка. Срок и время получения уточняются при подтверждении заказа; сайт не рассчитывает их автоматически.</p>
                    </section>
                    <section class="card information-card">
                        <h2>При получении</h2>
                        <ul>
                            <li>Проверьте целостность упаковки.</li>
                            <li>Сверьте модели и количество комплектующих с заказом.</li>
                            <li>Сохраните документы и упаковку товара.</li>
                            <li>Если обнаружили повреждение или несоответствие, сообщите об этом при получении.</li>
                        </ul>
                    </section>
                <?php else: ?>
                    <section class="card information-card">
                        <h2>Банковской картой онлайн</h2>
                        <p>При оформлении выберите «Картой онлайн». В форме указаны карты Visa, Mastercard и «Мир».</p>
                        <p>В учебной версии платёжный шлюз не подключён: данные карты вводить не нужно, деньги не списываются. Выбранный способ оплаты сохраняется в заказе.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Наличными при получении</h2>
                        <p>Выберите «Наличными при получении», если хотите указать оплату курьеру. Этот вариант также сохраняется в информации о заказе.</p>
                        <p>Перед оплатой проверьте состав заказа и итоговую сумму с учётом доставки.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Итоговая сумма</h2>
                        <p>Сумма состоит из стоимости товаров с учётом выбранного количества и доставки — 300 ₽. Оба доступных способа оплаты используют одну и ту же итоговую сумму.</p>
                        <p>Перед подтверждением все позиции и расчёт видны в <a href="<?php echo APP_URL; ?>/cart">корзине</a> и на странице оформления заказа.</p>
                    </section>
                    <section class="card information-card">
                        <h2>Безопасность</h2>
                        <p>Никому не передавайте пароль от личного кабинета, PIN-код карты или коды подтверждения из SMS. Магазин не запрашивает эти данные в комментариях к заказу.</p>
                        <p>В этой демонстрации не следует вводить настоящие платёжные реквизиты или другие чувствительные данные.</p>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="information-note" aria-label="Об учебном проекте">Этот магазин — учебный проект. Товары, цены, условия доставки и оплаты приведены для демонстрации; реальные продажи, отправка товаров и списание средств не выполняются.</aside>
            <div class="information-actions">
                <a href="<?php echo APP_URL; ?>/catalog" class="btn">Перейти в каталог</a>
                <a href="<?php echo APP_URL; ?>/cart" class="btn btn-outline">Открыть корзину</a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
