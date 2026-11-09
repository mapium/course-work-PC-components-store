<form method="GET" action="<?php echo htmlspecialchars(APP_URL . $catalog['path']); ?>" id="catalog-filter-form" class="filters">
    <?php if ($catalog['search'] !== ''): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($catalog['search']); ?>"><?php endif; ?>
    <div class="filter-group">
        <label for="price-min">Цена, ₽</label>
        <input id="price-min" type="number" name="price_min" min="0" max="99999999.99" step="0.01" aria-label="Минимальная цена" placeholder="От" class="filter-input" value="<?php echo htmlspecialchars((string)($catalog['filters']['price_min'] ?? '')); ?>">
        <input id="price-max" type="number" name="price_max" min="0" max="99999999.99" step="0.01" aria-label="Максимальная цена" placeholder="До" class="filter-input" value="<?php echo htmlspecialchars((string)($catalog['filters']['price_max'] ?? '')); ?>">
    </div>
    <div class="filter-group">
        <label for="category-filter">Категория</label>
        <select name="category" id="category-filter" class="filter-input">
            <option value=""><?php echo $category ? 'Весь раздел' : 'Все категории'; ?></option>
            <?php $allowed_categories = (new \App\Models\Category($pdo))->descendantIds($category['id'] ?? null); ?>
            <?php foreach ($catalog['categories'] as $cat): ?>
                <?php if (!in_array((int)$cat['id'], $allowed_categories, true)) continue; ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php echo (int)$catalog['selected'] === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo str_repeat('— ', $cat['depth']) . htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="brand-filter-container" class="filter-group">
        <label for="brand-filter">Производитель</label>
        <select name="brand" id="brand-filter" class="filter-input">
            <option value="">Все производители</option>
            <?php foreach ($catalog['brands'] as $brand): ?>
                <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo $brand === ($catalog['filters']['brand'] ?? '') ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div id="spec-filters-container">
        <?php foreach ($catalog['specs'] as $name => $values): ?>
            <details class="filter-group" open>
                <summary><?php echo htmlspecialchars($name); ?></summary>
                <div class="checkbox-group">
                    <?php foreach ($values as $index => $value): $input_id = 'spec-' . md5($name) . '-' . $index; ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="<?php echo $input_id; ?>" name="spec[<?php echo htmlspecialchars($name); ?>][]" value="<?php echo htmlspecialchars($value); ?>" <?php echo in_array($value, $catalog['filters']['specifications'][$name] ?? [], true) ? 'checked' : ''; ?>>
                            <label for="<?php echo $input_id; ?>"><?php echo htmlspecialchars($value); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
        <?php if (!$catalog['selected']): ?><p class="filter-help">Выберите категорию, чтобы увидеть её характеристики.</p><?php endif; ?>
    </div>
    <noscript><button type="submit" class="btn">Применить фильтры</button></noscript>
    <a href="<?php echo htmlspecialchars(APP_URL . $catalog['path']); ?>" data-catalog-link id="reset-filters" class="btn btn-secondary">Сбросить</a>
</form>
