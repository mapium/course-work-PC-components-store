// Основной файл JavaScript приложения

document.addEventListener('DOMContentLoaded', function() {
    console.log('PCComponents Store loaded');
    initializeApp();
});

function initializeApp() {
    // Инициализация всех модулей приложения
    setupFormValidation();
    setupCartFunctionality();
    setupNotifications();
    setupHeroCarousel();
    setupCategoriesCarousel();
}

// Hero-карусель
function setupHeroCarousel() {
    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('[data-hero-slide]');
    const prevBtn = carousel.querySelector('[data-hero-prev]');
    const nextBtn = carousel.querySelector('[data-hero-next]');
    const dots = carousel.querySelectorAll('[data-hero-dot]');

    if (slides.length === 0) return;

    let currentSlide = 0;
    let autoplayInterval;
    const autoplayDelay = 5000; // 5 секунд

    function goToSlide(index) {
        // Убираем активный класс со всех слайдов
        slides.forEach((slide, i) => {
            slide.classList.remove('is-active');
            slide.setAttribute('aria-hidden', i !== index);
        });

        // Убираем активный класс со всех точек
        dots.forEach(dot => {
            dot.classList.remove('is-active');
            dot.removeAttribute('aria-current');
        });

        // Активируем нужный слайд и точку
        slides[index].classList.add('is-active');
        slides[index].setAttribute('aria-hidden', 'false');

        if (dots[index]) {
            dots[index].classList.add('is-active');
            dots[index].setAttribute('aria-current', 'true');
        }

        currentSlide = index;
    }

    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        goToSlide(next);
    }

    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        goToSlide(prev);
    }

    function startAutoplay() {
        stopAutoplay();
        autoplayInterval = setInterval(nextSlide, autoplayDelay);
    }

    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }

    // Обработчики кнопок
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoplay();
            startAutoplay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoplay();
            startAutoplay();
        });
    }

    // Обработчики точек
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            goToSlide(index);
            stopAutoplay();
            startAutoplay();
        });
    });

    // Останавливаем автоплей при наведении
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);

    // Поддержка свайпов на мобильных
    let touchStartX = 0;
    let touchEndX = 0;

    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
            stopAutoplay();
            startAutoplay();
        }
    }

    // Поддержка клавиатуры
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            stopAutoplay();
            startAutoplay();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            stopAutoplay();
            startAutoplay();
        }
    });

    // Запускаем автоплей
    startAutoplay();
}

// Карусель категорий
function setupCategoriesCarousel() {
    const carousel = document.querySelector('.categories-carousel');
    if (!carousel) return;

    const cards = carousel.querySelectorAll('.category-card');
    if (cards.length === 0) return;

    // Дублируем карточки для бесконечной прокрутки
    cards.forEach(card => {
        const clone = card.cloneNode(true);
        carousel.appendChild(clone);
    });
}

// Валидация форм
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
            showFieldError(input, 'Поле заполнено некорректно');
        } else {
            clearFieldError(input);
        }
    });

    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');

    // Empty optional fields are always valid
    if (!value) {
        return !required;
    }

    if (type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    }

    if (type === 'tel') {
        const phoneRegex = /^[\d\+\-\s\(\)]{10,}$/;
        return phoneRegex.test(value);
    }

    if (type === 'number') {
        return !isNaN(Number(value));
    }

    return true;
}

function showFieldError(field, message) {
    field.classList.add('error');
    let errorEl = field.nextElementSibling;
    if (!errorEl || !errorEl.classList.contains('error-message')) {
        errorEl = document.createElement('div');
        errorEl.classList.add('error-message');
        field.parentNode.insertBefore(errorEl, field.nextSibling);
    }
    errorEl.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorEl = field.nextElementSibling;
    if (errorEl && errorEl.classList.contains('error-message')) {
        errorEl.remove();
    }
}

// Функционал корзины
function setupCartFunctionality() {
    const addToCartBtns = document.querySelectorAll('[data-add-to-cart]');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', addToCart);
    });

    // Обработка форм добавления в корзину
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', handleAddToCartForm);
    });

    // Обработка форм изменения количества в корзине
    const quantityForms = document.querySelectorAll('.quantity-form');
    quantityForms.forEach(form => {
        form.addEventListener('submit', handleQuantityUpdate);
    });

    // Обработка форм удаления из корзины
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', handleDeleteFromCart);
    });
}

function handleAddToCartForm(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const productId = formData.get('product_id');
    const quantity = formData.get('quantity') || 1;

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар добавлен в корзину', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Ошибка при добавлении товара', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при добавлении товара', 'error');
    });
}

function addToCart(e) {
    e.preventDefault();
    const productId = this.getAttribute('data-add-to-cart');
    const quantity = document.querySelector(`[data-quantity-${productId}]`)?.value || 1;

    // Получаем базовый URL из мета-тега или используем пустую строку
    const baseUrl = document.querySelector('meta[name="app-url"]')?.content || '';

    fetch(baseUrl + '/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар добавлен в корзину', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Ошибка при добавлении товара', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при добавлении товара', 'error');
    });
}

function handleQuantityUpdate(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const newQuantity = parseInt(formData.get('quantity'));

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ quantity: newQuantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Перезагружаем страницу для обновления данных
            location.reload();
        } else {
            showNotification(data.message || 'Ошибка при обновлении количества', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении количества', 'error');
    });
}

function updateCartSummary() {
    const baseUrl = document.querySelector('meta[name="app-url"]')?.content || '';

    fetch(baseUrl + '/api/cart/summary')
        .then(response => response.json())
        .then(data => {
            // Обновляем значения в блоке итого
            const subtotalEl = document.querySelector('.summary-row:nth-child(1) .summary-value');
            const totalEl = document.querySelector('.summary-total .summary-value');

            if (subtotalEl) {
                subtotalEl.textContent = data.subtotal.toLocaleString('ru-RU') + ' ₽';
            }
            if (totalEl) {
                totalEl.textContent = data.total.toLocaleString('ru-RU') + ' ₽';
            }

            // Обновляем счетчик товаров в хедере
            updateCartCount();
        })
        .catch(error => {
            console.error('Error updating summary:', error);
        });
}

function handleDeleteFromCart(e) {
    e.preventDefault();
    const form = e.target;
    const baseUrl = document.querySelector('meta[name="app-url"]')?.content || '';

    // Извлекаем ID из action URL
    const actionUrl = form.action;
    const cartId = actionUrl.match(/\/api\/cart\/(\d+)/)?.[1];

    if (!cartId) {
        showNotification('Ошибка: не удалось определить ID товара', 'error');
        return;
    }

    fetch(baseUrl + '/api/cart/' + cartId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар удален из корзины', 'success');
            // Перезагружаем страницу через небольшую задержку
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message || 'Ошибка при удалении товара', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при удалении товара', 'error');
    });
}

function updateCartCount() {
    const baseUrl = document.querySelector('meta[name="app-url"]')?.content || '';

    fetch(baseUrl + '/api/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('[data-cart-count]');
            if (cartBadge) {
                cartBadge.textContent = data.count;
            }
        });
}

// Всплывающие уведомления
function setupNotifications() {
    // Контейнер для уведомлений уже должен быть в HTML
    if (!document.getElementById('notifications')) {
        const container = document.createElement('div');
        container.id = 'notifications';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notifications');
    if (!container) {
        setupNotifications();
    }

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        background-color: ${type === 'success' ? '#00c853' : type === 'error' ? '#d32f2f' : '#1976d2'};
        color: white;
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
    `;

    container.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// Анимация для уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .error {
        border-color: #d32f2f !important;
    }

    .error-message {
        color: #d32f2f;
        font-size: 0.85rem;
        margin-top: -10px;
        margin-bottom: 10px;
    }
`;
document.head.appendChild(style);

// Экспортируем функции в глобальный scope
window.addToCart = addToCart;
window.handleAddToCartForm = handleAddToCartForm;
window.handleQuantityUpdate = handleQuantityUpdate;
window.handleDeleteFromCart = handleDeleteFromCart;
window.showNotification = showNotification;
window.validateForm = validateForm;
