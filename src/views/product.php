<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Product.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$product_model = new \App\Models\Product($pdo);

$product_id = $_GET['id'] ?? 0;
$product = $product_model->getById($product_id);
$specifications = $product_model->getSpecifications($product_id);
$reviews = $product_model->getReviews($product_id);
$similar_products = [];

if ($product) {
    $similar_products = $product_model->getSimilarProducts($product_id, $product['category_id'], 4);
}

if (!$product) {
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Товар не найден</h1>';
    exit;
}

// Вычисляем средний рейтинг
$average_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $average_rating = round($total_rating / count($reviews), 1);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <title><?php echo htmlspecialchars($product['meta_title'] ?? $product['name'] . ' - купить в PCComponents Store'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($product['meta_description'] ?? substr($product['description'], 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($product['meta_keywords'] ?? $product['brand'] . ', ' . $product['name'] . ', купить'); ?>">
    <link rel="canonical" href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>">

    <!-- OpenGraph теги для социальных сетей -->
    <meta property="og:type" content="product">
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 200)); ?>">
    <meta property="og:image" content="<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>">
    <meta property="og:url" content="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>">
    <meta property="og:site_name" content="PCComponents Store">
    <meta property="product:price:amount" content="<?php echo $product['price']; ?>">
    <meta property="product:price:currency" content="RUB">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($product['name']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(substr($product['description'], 0, 200)); ?>">
    <meta name="twitter:image" content="<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>">

    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('product-tabs.css'); ?>">

    <!-- Микроразметка Schema.org Product -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?php echo addslashes($product['name']); ?>",
      "description": "<?php echo addslashes($product['description']); ?>",
      "sku": "<?php echo addslashes($product['sku'] ?? 'SKU-' . $product['id']); ?>",
      "brand": {
        "@type": "Brand",
        "name": "<?php echo addslashes($product['brand'] ?? 'Unknown'); ?>"
      },
      "image": "<?php echo APP_URL; ?>/public/images/<?php echo $product['main_image'] ?? 'placeholder.jpg'; ?>",
      "offers": {
        "@type": "Offer",
        "url": "<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>",
        "priceCurrency": "RUB",
        "price": "<?php echo $product['price']; ?>",
        "priceValidUntil": "<?php echo date('Y-12-31'); ?>",
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "<?php echo $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
        "seller": {
          "@type": "Organization",
          "name": "PCComponents Store"
        }
      }
      <?php if ($average_rating > 0): ?>
      ,
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $average_rating; ?>",
        "reviewCount": "<?php echo count($reviews); ?>",
        "bestRating": "5",
        "worstRating": "1"
      }
      <?php endif; ?>
    }
    </script>

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
          "name": "Каталог",
          "item": "<?php echo APP_URL; ?>/catalog"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?php echo addslashes($product['name']); ?>"
        }
      ]
    }
    </script>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <!-- Хлебные крошки -->
            <nav class="breadcrumbs">
                <a href="<?php echo APP_URL; ?>/">Главная</a> &raquo;
                <a href="<?php echo APP_URL; ?>/catalog">Каталог</a> &raquo;
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <!-- Основная информация о товаре -->
            <div class="product-detail">
                <div class="product-image-container">
                    <img
                        src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($product['main_image'] ?? 'placeholder.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($product['name'] . ' - ' . ($product['brand'] ?? '')); ?>"
                        class="product-image-large"
                        loading="lazy"
                        width="600"
                        height="600"
                    >
                </div>

                <div class="product-info-container">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>

                    <?php if ($product['brand']): ?>
                        <p class="product-brand">Бренд: <strong><?php echo htmlspecialchars($product['brand']); ?></strong></p>
                    <?php endif; ?>

                    <?php if ($average_rating > 0): ?>
                        <div class="product-rating">
                            <span class="rating-stars">★★★★★</span>
                            <span class="rating-value"><?php echo $average_rating; ?></span>
                            <span class="rating-count">(<?php echo count($reviews); ?> отзывов)</span>
                        </div>
                    <?php endif; ?>

                    <p class="product-price-large"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</p>

                    <p class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $product['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                    </p>

                    <div class="product-description">
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="product-actions">
                            <form method="POST" action="<?php echo APP_URL; ?>/api/cart/add" id="add-to-cart-form">
                                <div class="quantity-selector">
                                    <label for="quantity">Количество:</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                <button type="submit" class="btn">Добавить в корзину</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline" disabled>Товар недоступен</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Вкладки -->
            <div class="product-tabs">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="description">Описание</button>
                    <button class="tab-btn" data-tab="specifications">Характеристики</button>
                    <button class="tab-btn" data-tab="reviews">Отзывы (<?php echo count($reviews); ?>)</button>
                </div>

                <!-- Описание -->
                <div class="tab-content active" id="description">
                    <div class="product-detailed-description">
                        <h2>Подробное описание</h2>
                        <p><?php echo nl2br(htmlspecialchars($product['detailed_description'] ?? $product['description'])); ?></p>
                    </div>
                </div>

                <!-- Характеристики -->
                <div class="tab-content" id="specifications">
                    <?php if (!empty($specifications)): ?>
                        <h2>Технические характеристики</h2>
                        <table class="specs-table">
                            <?php foreach ($specifications as $spec): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($spec['spec_name']); ?></td>
                                    <td><?php echo htmlspecialchars($spec['spec_value']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p class="no-reviews">Характеристики не указаны</p>
                    <?php endif; ?>
                </div>

                <!-- Отзывы -->
                <div class="tab-content" id="reviews">
                    <h2>Отзывы покупателей</h2>

                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-container">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card" data-review-id="<?php echo $review['id']; ?>">
                                    <div class="review-header">
                                        <div class="review-author-info">
                                            <div class="review-author"><?php echo htmlspecialchars($review['full_name'] ?? $review['username'] ?? 'Анонимный'); ?></div>
                                            <div class="review-rating">
                                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                            </div>
                                        </div>
                                        <div class="review-actions">
                                            <div class="review-date">
                                                <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                                <?php if (isset($review['updated_at']) && $review['updated_at'] !== $review['created_at']): ?>
                                                    <span class="review-edited" title="Отредактировано">(изменено)</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                                                <?php if (\App\Helpers\Auth::getUserId() == $review['user_id']): ?>
                                                    <button class="btn-icon edit-review-btn"
                                                            data-review-id="<?php echo $review['id']; ?>"
                                                            data-rating="<?php echo $review['rating']; ?>"
                                                            data-comment="<?php echo htmlspecialchars($review['comment']); ?>"
                                                            title="Редактировать отзыв">
                                                        <img src="<?php echo APP_URL; ?>/public/images/icons/edit.svg" alt="" width="16" height="16">
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (\App\Helpers\Auth::getUserId() == $review['user_id'] || in_array(\App\Helpers\Auth::getUserRole(), ['admin', 'manager'])): ?>
                                                    <button class="btn-icon delete-review-btn" data-review-id="<?php echo $review['id']; ?>" title="Удалить отзыв">
                                                        <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="16" height="16">
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="review-text">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </div>

                                    <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                                        <button class="btn-text reply-review-btn" data-review-id="<?php echo $review['id']; ?>">
                                            <img src="<?php echo APP_URL; ?>/public/images/icons/reply.svg" alt="" width="14" height="14">
                                            Ответить
                                        </button>
                                    <?php endif; ?>

                                    <!-- Ответы на отзыв -->
                                    <?php if (!empty($review['replies'])): ?>
                                        <div class="review-replies">
                                            <?php foreach ($review['replies'] as $reply): ?>
                                                <div class="review-card review-reply" data-review-id="<?php echo $reply['id']; ?>">
                                                    <div class="review-header">
                                                        <div class="review-author-info">
                                                            <div class="review-author">
                                                                <?php echo htmlspecialchars($reply['full_name'] ?? $reply['username'] ?? 'Анонимный'); ?>
                                                                <?php if (isset($reply['user_id'])):
                                                                    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
                                                                    $stmt->execute([':id' => $reply['user_id']]);
                                                                    $user_role = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                    if ($user_role && in_array($user_role['role'], ['admin', 'manager'])):
                                                                ?>
                                                                    <span class="badge badge-secondary">Представитель магазина</span>
                                                                <?php endif; endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="review-actions">
                                                            <div class="review-date">
                                                                <?php echo date('d.m.Y', strtotime($reply['created_at'])); ?>
                                                                <?php if (isset($reply['updated_at']) && $reply['updated_at'] !== $reply['created_at']): ?>
                                                                    <span class="review-edited" title="Отредактировано">(изменено)</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                                                                <?php if (\App\Helpers\Auth::getUserId() == $reply['user_id']): ?>
                                                                    <button class="btn-icon edit-review-btn"
                                                                            data-review-id="<?php echo $reply['id']; ?>"
                                                                            data-is-reply="true"
                                                                            data-comment="<?php echo htmlspecialchars($reply['comment']); ?>"
                                                                            title="Редактировать ответ">
                                                                        <img src="<?php echo APP_URL; ?>/public/images/icons/edit.svg" alt="" width="16" height="16">
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if (\App\Helpers\Auth::getUserId() == $reply['user_id'] || in_array(\App\Helpers\Auth::getUserRole(), ['admin', 'manager'])): ?>
                                                                    <button class="btn-icon delete-review-btn" data-review-id="<?php echo $reply['id']; ?>" title="Удалить ответ">
                                                                        <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="16" height="16">
                                                                    </button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="review-text">
                                                        <?php echo nl2br(htmlspecialchars($reply['comment'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-reviews">
                            <p>Пока нет отзывов. Будьте первым!</p>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Helpers\Auth::isLoggedIn()): ?>
                        <button class="btn btn-secondary add-review-btn">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">
                            Оставить отзыв
                        </button>
                    <?php else: ?>
                        <p class="login-prompt">
                            <a href="<?php echo APP_URL; ?>/auth">Войдите</a>, чтобы оставить отзыв
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Похожие товары -->
            <?php if (!empty($similar_products)): ?>
                <section class="similar-products">
                    <h2>Похожие товары</h2>
                    <div class="products-grid">
                        <?php foreach ($similar_products as $similar): ?>
                            <div class="product-card">
                                <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="product-card-link">
                                <img
                                    src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($similar['main_image'] ?? 'placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($similar['name']); ?>"
                                    class="product-image"
                                    loading="lazy"
                                    width="300"
                                    height="300"
                                >
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($similar['name']); ?></h3>
                                    <p class="product-price"><?php echo number_format($similar['price'], 0, '', ' '); ?> ₽</p>
                                    <p class="product-description"><?php echo htmlspecialchars(substr($similar['description'], 0, 100)); ?>…</p>
                                    <a href="<?php echo APP_URL; ?>/product/<?php echo $similar['id']; ?>" class="btn">Подробнее</a>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Модальное окно для отзыва -->
    <div id="reviewModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="reviewModalTitle">Оставить отзыв</h3>
                <button class="modal-close" onclick="closeReviewModal()">&times;</button>
            </div>
            <form id="reviewForm">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="parent_id" id="parentReviewId" value="">
                <input type="hidden" name="review_id" id="editReviewId" value="">
                <input type="hidden" name="is_edit" id="isEdit" value="0">
                <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                <div class="form-group" id="ratingGroup">
                    <label>Оценка *</label>
                    <div class="rating-input">
                        <input type="radio" name="rating" value="5" id="star5" required>
                        <label for="star5" title="5 звёзд">★</label>
                        <input type="radio" name="rating" value="4" id="star4">
                        <label for="star4" title="4 звезды">★</label>
                        <input type="radio" name="rating" value="3" id="star3">
                        <label for="star3" title="3 звезды">★</label>
                        <input type="radio" name="rating" value="2" id="star2">
                        <label for="star2" title="2 звезды">★</label>
                        <input type="radio" name="rating" value="1" id="star1">
                        <label for="star1" title="1 звезда">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Комментарий *</label>
                    <textarea name="comment" id="reviewComment" rows="5" required placeholder="Напишите ваш отзыв..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Отправить</button>
                    <button type="button" class="btn btn-outline" onclick="closeReviewModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script>
        // Переключение вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                switchTab(this.getAttribute('data-tab'));
            });

            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    switchTab(this.getAttribute('data-tab'));
                }
            });
        });

        function switchTab(tabName) {
            // Удаляем active со всех кнопок и контента
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Добавляем active к выбранным
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        // Работа с отзывами
        const reviewModal = document.getElementById('reviewModal');
        const reviewForm = document.getElementById('reviewForm');
        const reviewModalTitle = document.getElementById('reviewModalTitle');
        const parentReviewId = document.getElementById('parentReviewId');
        const editReviewId = document.getElementById('editReviewId');
        const isEdit = document.getElementById('isEdit');
        const ratingGroup = document.getElementById('ratingGroup');
        const reviewComment = document.getElementById('reviewComment');

        function setReviewRatingEnabled(enabled) {
            ratingGroup.style.display = enabled ? 'block' : 'none';
            ratingGroup.querySelectorAll('input[name="rating"]').forEach((input, index) => {
                // Скрытая обязательная radio-группа блокирует submit до запуска JS.
                input.disabled = !enabled;
                input.required = enabled && index === 0;
                if (!enabled) {
                    input.checked = false;
                }
            });
        }

        // Открытие модального окна для нового отзыва
        function attachAddReviewListener() {
            const addReviewBtn = document.querySelector('.add-review-btn');
            if (!addReviewBtn) return;

            addReviewBtn.addEventListener('click', function() {
                reviewForm.reset();
                reviewModalTitle.textContent = 'Оставить отзыв';
                parentReviewId.value = '';
                editReviewId.value = '';
                isEdit.value = '0';
                setReviewRatingEnabled(true);
                reviewModal.style.display = 'flex';
                reviewComment.focus();
            });
        }

        // Открытие модального окна для ответа на отзыв
        function attachReplyListeners() {
            document.querySelectorAll('.reply-review-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const reviewId = this.getAttribute('data-review-id');
                    reviewForm.reset();
                    reviewModalTitle.textContent = 'Ответить на отзыв';
                    parentReviewId.value = reviewId;
                    editReviewId.value = '';
                    isEdit.value = '0';
                    setReviewRatingEnabled(false);

                    reviewModal.style.display = 'flex';
                    reviewComment.focus();
                });
            });
        }

        // Открытие модального окна для редактирования отзыва
        function attachEditListeners() {
            document.querySelectorAll('.edit-review-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const reviewId = this.getAttribute('data-review-id');
                    const rating = this.getAttribute('data-rating');
                    const comment = this.getAttribute('data-comment');
                    const isReply = this.getAttribute('data-is-reply') === 'true';

                    reviewForm.reset();
                    reviewModalTitle.textContent = isReply ? 'Редактировать ответ' : 'Редактировать отзыв';
                    parentReviewId.value = '';
                    editReviewId.value = reviewId;
                    isEdit.value = '1';
                    reviewComment.value = comment;
                    setReviewRatingEnabled(!isReply);

                    if (!isReply && rating) {
                        document.getElementById('star' + rating).checked = true;
                    }

                    reviewModal.style.display = 'flex';
                    reviewComment.focus();
                });
            });
        }

        attachAddReviewListener();
        attachReplyListeners();
        attachEditListeners();

        // Закрытие модального окна
        function closeReviewModal() {
            reviewModal.style.display = 'none';
            reviewForm.reset();
            parentReviewId.value = '';
            editReviewId.value = '';
            isEdit.value = '0';
            setReviewRatingEnabled(true);
        }

        // Закрытие при клике вне модального окна
        if (reviewModal) {
            reviewModal.addEventListener('click', function(e) {
                if (e.target === reviewModal) {
                    closeReviewModal();
                }
            });
        }

        // Функция для перезагрузки отзывов
        async function reloadReviews() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Обновляем контейнер с отзывами
                const newReviewsContainer = doc.querySelector('#reviews');
                const currentReviewsContainer = document.querySelector('#reviews');

                if (newReviewsContainer && currentReviewsContainer) {
                    currentReviewsContainer.innerHTML = newReviewsContainer.innerHTML;

                    // Переподключаем обработчики после обновления
                    attachAddReviewListener();
                    attachReplyListeners();
                    attachEditListeners();
                    attachDeleteListeners();

                    // Обновляем счетчик отзывов на вкладке
                    const newTabBtn = doc.querySelector('[data-tab="reviews"]');
                    const currentTabBtn = document.querySelector('[data-tab="reviews"]');
                    if (newTabBtn && currentTabBtn) {
                        currentTabBtn.textContent = newTabBtn.textContent;
                    }
                }
            } catch (error) {
                console.error('Error reloading reviews:', error);
            }
        }

        // Отправка формы отзыва
        if (reviewForm) {
            reviewForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = reviewForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';

                const formData = new FormData(reviewForm);
                const isEditMode = isEdit.value === '1';

                // Определяем URL в зависимости от режима
                const url = isEditMode
                    ? '<?php echo APP_URL; ?>/api/reviews/update'
                    : '<?php echo APP_URL; ?>/api/reviews/add';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        closeReviewModal();
                        // Перезагружаем отзывы без перезагрузки страницы
                        await reloadReviews();

                        // Показываем уведомление
                        const message = isEditMode ? 'Отзыв обновлен!' : 'Отзыв успешно добавлен!';
                        showNotification(message, 'success');
                    } else {
                        showNotification(result.message || 'Ошибка при сохранении отзыва', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Произошла ошибка при отправке отзыва', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Функция для показа уведомлений
        function showNotification(message, type = 'info') {
            // Создаем элемент уведомления
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;

            // Добавляем на страницу
            document.body.appendChild(notification);

            // Показываем с анимацией
            setTimeout(() => notification.classList.add('show'), 10);

            // Убираем через 3 секунды
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Удаление отзыва
        function attachDeleteListeners() {
            document.querySelectorAll('.delete-review-btn').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (!confirm('Вы уверены, что хотите удалить этот отзыв?')) {
                        return;
                    }

                    const reviewId = this.getAttribute('data-review-id');
                    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

                    try {
                        const response = await fetch('<?php echo APP_URL; ?>/api/reviews/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `review_id=${reviewId}&csrf_token=${csrfToken}`
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Перезагружаем отзывы без перезагрузки страницы
                            await reloadReviews();
                            showNotification('Отзыв удален', 'success');
                        } else {
                            showNotification(result.message || 'Ошибка при удалении отзыва', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showNotification('Произошла ошибка при удалении отзыва', 'error');
                    }
                });
            });
        }

        attachDeleteListeners();
    </script>
</body>
</html>
