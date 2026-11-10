-- Повторяемая миграция действующего магазина (MySQL 8.0+).
-- Не удаляет существующие записи. Перед запуском сохраните резервную копию.
-- Учебные значения 20–500 плюс реальные неотменённые заказы назначаются только при создании столбца.

SET @orders_count_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'orders_count');
SET @discovery_ddl = IF(@orders_count_exists = 0,
    'ALTER TABLE products ADD COLUMN orders_count INT UNSIGNED NULL DEFAULT NULL COMMENT ''Количество неотменённых заказов с товаром, включая учебные значения'' AFTER views',
    'DO 0');
PREPARE discovery_statement FROM @discovery_ddl;
EXECUTE discovery_statement;
DEALLOCATE PREPARE discovery_statement;

UPDATE products p
SET p.orders_count = 20 + MOD(CRC32(p.slug), 481) + (
    SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id AND o.status != 'cancelled'
), p.updated_at = p.updated_at
WHERE p.orders_count IS NULL;

-- Промежуточные NULL позволяют безопасно продолжить миграцию после прерывания,
-- не сбрасывая уже назначенные значения (в том числе нулевые).
SET @orders_count_nullable = (SELECT IS_NULLABLE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'orders_count');
SET @discovery_ddl = IF(@orders_count_nullable = 'YES',
    'ALTER TABLE products MODIFY COLUMN orders_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Количество неотменённых заказов с товаром, включая учебные значения''',
    'DO 0');
PREPARE discovery_statement FROM @discovery_ddl;
EXECUTE discovery_statement;
DEALLOCATE PREPARE discovery_statement;

SET @discovery_index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_products_popularity');
SET @discovery_ddl = IF(@discovery_index_exists = 0,
    'ALTER TABLE products ADD KEY idx_products_popularity (is_active, orders_count, id)', 'DO 0');
PREPARE discovery_statement FROM @discovery_ddl;
EXECUTE discovery_statement;
DEALLOCATE PREPARE discovery_statement;

CREATE TABLE IF NOT EXISTS product_views (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    viewed_at DATETIME(6) NOT NULL COMMENT 'Время последнего просмотра в UTC',
    PRIMARY KEY (id),
    UNIQUE KEY unique_product_view (user_id, product_id),
    KEY idx_product_views_recent (user_id, viewed_at, id),
    KEY idx_product_views_product (product_id),
    CONSTRAINT product_views_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT product_views_product_fk FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
