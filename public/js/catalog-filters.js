// Catalog Filters - Dynamic Category-Specific Filtering
(function() {
    'use strict';

    const APP_URL = window.location.origin + '/course-work-PC-components-store';

    // Priority specifications that should use checkboxes even if they have >10 values
    const PRIORITY_CHECKBOX_SPECS = [
        'Объем памяти',
        'Сокет',
        'Количество ядер',
        'Тип',
        'Емкость',
        'Форм-фактор',
        'Частота'
    ];

    // DOM Elements
    const filterForm = document.getElementById('catalog-filter-form');
    const categorySelect = document.getElementById('category-filter');
    const brandContainer = document.getElementById('brand-filter-container');
    const brandSelect = document.getElementById('brand-filter');
    const specContainer = document.getElementById('spec-filters-container');
    const productsContainer = document.getElementById('products-container');
    const productCount = document.getElementById('product-count');
    const resetBtn = document.getElementById('reset-filters');

    if (!filterForm || !categorySelect) {
        console.error('Catalog filter elements not found');
        return;
    }

    // Initialize
    init();

    function init() {
        // Category change event
        categorySelect.addEventListener('change', handleCategoryChange);

        // Apply checkbox, select, and price changes without an extra submit.
        filterForm.addEventListener('change', event => {
            if (event.target !== categorySelect && event.target.matches('input[type="checkbox"], input[type="number"], select')) {
                handleFormSubmit();
            }
        });

        // Form submit event
        filterForm.addEventListener('submit', handleFormSubmit);

        // Reset button event
        if (resetBtn) {
            resetBtn.addEventListener('click', handleReset);
        }

        // Load filters if category is pre-selected (from URL)
        if (categorySelect.value) {
            handleCategoryChange();
        }

        initializeCartButtons();
    }

    async function handleCategoryChange() {
        const categoryId = categorySelect.value;

        if (!categoryId) {
            // Hide dynamic filters if no category selected
            if (brandContainer) brandContainer.style.display = 'none';
            if (specContainer) specContainer.style.display = 'none';
            clearSpecificationFilters();
            handleFormSubmit();
            return;
        }

        showLoading();

        try {
            // Fetch specifications and brands in parallel
            const [specsResponse, brandsResponse] = await Promise.all([
                fetch(`${APP_URL}/api/specifications/${categoryId}`),
                fetch(`${APP_URL}/api/brands/${categoryId}`)
            ]);

            if (!specsResponse.ok || !brandsResponse.ok) {
                throw new Error('Failed to fetch filter data');
            }

            const specsData = await specsResponse.json();
            const brandsData = await brandsResponse.json();

            if (specsData.success && brandsData.success) {
                // Render brand dropdown
                renderBrands(brandsData.brands);

                // Render specification filters
                renderSpecificationFilters(specsData.specifications);

                // Show containers
                if (brandContainer) brandContainer.style.display = 'block';
                if (specContainer) specContainer.style.display = 'block';
            } else {
                console.error('API returned error:', specsData, brandsData);
            }
        } catch (error) {
            console.error('Error fetching filters:', error);
            showError('Ошибка загрузки фильтров');
        } finally {
            hideLoading();
        }

        handleFormSubmit();
    }

    function renderBrands(brands) {
        if (!brandSelect || !brands || brands.length === 0) {
            if (brandContainer) brandContainer.style.display = 'none';
            return;
        }

        const selectedBrand = brandSelect.value;
        // Clear existing options except the first (placeholder)
        brandSelect.innerHTML = '<option value="">Все производители</option>';

        brands.forEach(brand => {
            const option = document.createElement('option');
            option.value = brand;
            option.textContent = brand;
            option.selected = brand === selectedBrand;
            brandSelect.appendChild(option);
        });
    }

    function renderSpecificationFilters(specifications) {
        clearSpecificationFilters();

        if (!specifications || Object.keys(specifications).length === 0) {
            if (specContainer) specContainer.style.display = 'none';
            return;
        }

        const fragment = document.createDocumentFragment();

        // Sort specifications by name for consistent display
        const sortedSpecs = Object.entries(specifications).sort((a, b) =>
            a[0].localeCompare(b[0], 'ru')
        );

        sortedSpecs.forEach(([specName, values]) => {
            if (!values || values.length === 0) return;

            const filterGroup = document.createElement('div');
            filterGroup.className = 'filter-group';

            const heading = document.createElement('h4');
            heading.textContent = specName;
            filterGroup.appendChild(heading);

            // Decide whether to use checkboxes or dropdown
            const useCheckboxes = values.length <= 10 || PRIORITY_CHECKBOX_SPECS.includes(specName);

            if (useCheckboxes) {
                const checkboxGroup = createCheckboxGroup(specName, values);
                filterGroup.appendChild(checkboxGroup);
            } else {
                const dropdown = createMultiSelect(specName, values);
                filterGroup.appendChild(dropdown);
            }

            fragment.appendChild(filterGroup);
        });

        specContainer.appendChild(fragment);
    }

    function createCheckboxGroup(specName, values) {
        const container = document.createElement('div');
        container.className = 'checkbox-group';

        values.forEach((value, index) => {
            const checkboxItem = document.createElement('div');
            checkboxItem.className = 'checkbox-item';

            const inputId = `spec_${sanitizeId(specName)}_${index}`;
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = inputId;
            checkbox.name = `spec[${specName}][]`;
            checkbox.value = value;

            const label = document.createElement('label');
            label.htmlFor = inputId;
            label.textContent = value;

            checkboxItem.appendChild(checkbox);
            checkboxItem.appendChild(label);
            container.appendChild(checkboxItem);
        });

        return container;
    }

    function createMultiSelect(specName, values) {
        const select = document.createElement('select');
        select.className = 'multi-select';
        select.name = `spec[${specName}][]`;
        select.multiple = true;
        select.size = Math.min(values.length, 8);

        values.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        });

        return select;
    }

    function clearSpecificationFilters() {
        if (specContainer) {
            // Remove all filter groups, keep only the container
            const filterGroups = specContainer.querySelectorAll('.filter-group');
            filterGroups.forEach(group => group.remove());
        }
    }

    async function handleFormSubmit(e) {
        if (e) e.preventDefault();

        showLoading();

        try {
            const filters = gatherFilters();

            const response = await fetch(`${APP_URL}/api/products/filter`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filters)
            });

            if (!response.ok) {
                throw new Error('Filter request failed');
            }

            const data = await response.json();

            if (data.success) {
                updateProductGrid(data.products);
                updateProductCount(data.count);
            } else {
                showError('Ошибка фильтрации товаров');
            }
        } catch (error) {
            console.error('Error filtering products:', error);
            showError('Ошибка при применении фильтров');
        } finally {
            hideLoading();
        }
    }

    function gatherFilters() {
        const formData = new FormData(filterForm);
        const filters = {};

        // Keep the finder query when changing category, price, or specifications.
        const search = formData.get('search');
        if (search && search.trim()) {
            filters.search = search.trim();
        }

        // Category
        const categoryId = formData.get('category');
        if (categoryId) {
            filters.category_id = parseInt(categoryId, 10);
        }

        // Price range (optional)
        const priceMin = formData.get('price_min');
        const priceMax = formData.get('price_max');
        if (priceMin && priceMin.trim() !== '') {
            filters.price_min = parseFloat(priceMin);
        }
        if (priceMax && priceMax.trim() !== '') {
            filters.price_max = parseFloat(priceMax);
        }

        // Brand
        const brand = formData.get('brand');
        if (brand) {
            filters.brand = brand;
        }

        // Specifications
        const specifications = {};
        const specInputs = filterForm.querySelectorAll('input[type="checkbox"]:checked, select.multi-select option:checked');

        specInputs.forEach(input => {
            const name = input.name || input.parentElement.name;
            const match = name.match(/spec\[(.+?)\]\[\]/);

            if (match) {
                const specName = match[1];
                const specValue = input.value;

                if (!specifications[specName]) {
                    specifications[specName] = [];
                }

                if (!specifications[specName].includes(specValue)) {
                    specifications[specName].push(specValue);
                }
            }
        });

        if (Object.keys(specifications).length > 0) {
            filters.specifications = specifications;
        }

        return filters;
    }

    function updateProductGrid(products) {
        if (!productsContainer) return;

        if (!products || products.length === 0) {
            productsContainer.innerHTML = '<div class="no-products"><p>Товары не найдены. Попробуйте изменить фильтры.</p></div>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'products-grid';

        products.forEach(product => {
            const card = createProductCard(product);
            grid.appendChild(card);
        });

        productsContainer.innerHTML = '';
        productsContainer.appendChild(grid);

        // Re-initialize cart functionality for new product cards
        initializeCartButtons();
    }

    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';

        const imageUrl = product.main_image
            ? `${APP_URL}/public/images/products/${encodeURIComponent(product.main_image)}`
            : `${APP_URL}/public/images/products/placeholder.jpg`;

        const price = parseFloat(product.price).toLocaleString('ru-RU');
        const oldPrice = product.old_price
            ? `<del class="old-price">${parseFloat(product.old_price).toLocaleString('ru-RU')} ₽</del>`
            : '';
        const description = escapeHtml((product.description || '').substring(0, 100));

        card.innerHTML = `
            <a href="${APP_URL}/product/${encodeURIComponent(product.id)}" class="product-card-link">
                <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="product-image" loading="lazy" width="300" height="300">
                <div class="product-info">
                    <h3 class="product-name">${escapeHtml(product.name)}</h3>
                    <p class="product-price">${price} ₽ ${oldPrice}</p>
                    <p class="product-description">${description}</p>
                </div>
            </a>
            <div class="product-actions">
                <button class="btn btn-sm add-to-cart" data-product-id="${escapeHtml(String(product.id))}">
                    <img src="${APP_URL}/public/images/icons/shopping_cart.svg" alt="" width="16" height="16">
                    В корзину
                </button>
            </div>
        `;

        return card;
    }

    function updateProductCount(count) {
        if (!productCount) return;

        const countText = count === 1
            ? 'Найден 1 товар'
            : count < 5
                ? `Найдено ${count} товара`
                : `Найдено ${count} товаров`;

        productCount.textContent = countText;
    }

    function handleReset() {
        // Reset form
        filterForm.reset();

        // Hide dynamic filters
        if (brandContainer) brandContainer.style.display = 'none';
        if (specContainer) specContainer.style.display = 'none';

        // Clear specification filters
        clearSpecificationFilters();

        // Reload page to show all products
        window.location.href = `${APP_URL}/catalog`;
    }

    function initializeCartButtons() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.removeEventListener('click', handleAddToCart); // Remove existing listeners
            button.addEventListener('click', handleAddToCart);
        });
    }

    function handleAddToCart(e) {
        const button = e.currentTarget;
        const productId = button.dataset.productId;

        fetch(`${APP_URL}/api/cart/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = '✓ Добавлено';
                button.disabled = true;
                setTimeout(() => {
                    button.textContent = 'В корзину';
                    button.disabled = false;
                }, 2000);

                // Update cart count if exists
                updateCartCount();
            } else {
                alert(data.message || 'Ошибка добавления в корзину');
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            alert('Ошибка добавления в корзину');
        });
    }

    function updateCartCount() {
        fetch(`${APP_URL}/api/cart/count`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.count;
                    }
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
    }

    function showLoading() {
        if (productsContainer) {
            productsContainer.classList.add('filters-loading');
            const loader = document.createElement('div');
            loader.className = 'loading-spinner';
            loader.innerHTML = '<div class="spinner"></div><p>Загрузка...</p>';
            productsContainer.innerHTML = '';
            productsContainer.appendChild(loader);
        }
    }

    function hideLoading() {
        if (productsContainer) {
            productsContainer.classList.remove('filters-loading');
        }
    }

    function showError(message) {
        if (productsContainer) {
            productsContainer.innerHTML = `<div class="error-message"><p>${escapeHtml(message)}</p></div>`;
        }
    }

    function sanitizeId(str) {
        return str.replace(/[^a-zA-Z0-9]/g, '_');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
