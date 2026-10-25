-- Файл для фиксации начальных метрик SEO и аудита

-- 1. НАЧАЛЬНЫЕ МЕТРИКИ (фиксируются перед оптимизацией)

CREATE TABLE IF NOT EXISTS seo_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_date DATE,
    metric_name VARCHAR(100),
    metric_value VARCHAR(255),
    category VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO seo_metrics (metric_date, metric_name, metric_value, category, notes) VALUES
('2026-10-25', 'indexed_pages', '0', 'indexation', 'Количество проиндексированных страниц в Google'),
('2026-10-25', 'indexed_pages_yandex', '0', 'indexation', 'Количество проиндексированных страниц в Яндексе'),
('2026-10-25', 'avg_position', 'N/A', 'rankings', 'Средняя позиция по целевым запросам'),
('2026-10-25', 'organic_traffic', '0', 'traffic', 'Органический трафик за месяц'),
('2026-10-25', 'conversions', '0', 'conversions', 'Количество заказов из органического трафика'),
('2026-10-25', 'bounce_rate', '0%', 'ux', 'Процент отскоков'),
('2026-10-25', 'avg_session_duration', '0m', 'ux', 'Средняя длительность сессии'),
('2026-10-25', 'page_load_speed', 'N/A', 'performance', 'Скорость загрузки страниц'),
('2026-10-25', 'mobile_score', 'N/A', 'performance', 'Мобильная оценка PageSpeed'),
('2026-10-25', 'desktop_score', 'N/A', 'performance', 'Desktop оценка PageSpeed');

-- 2. ТАБЛИЦА АУДИТА ПРОБЛЕМ

CREATE TABLE IF NOT EXISTS audit_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    issue_title VARCHAR(255),
    issue_description TEXT,
    severity ENUM('critical', 'high', 'medium', 'low'),
    category VARCHAR(50),
    status ENUM('new', 'in_progress', 'resolved'),
    priority INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME
);

-- Пример проблем для аудита
INSERT INTO audit_issues (issue_title, issue_description, severity, category, status, priority) VALUES
('Отсутствуют meta теги', 'На многих страницах отсутствуют оптимизированные meta description', 'high', 'meta_tags', 'new', 1),
('Нет микроразметки', 'Отсутствует Schema.org разметка для товаров', 'high', 'schema', 'new', 2),
('Медленная загрузка изображений', 'Изображения товаров не оптимизированы', 'medium', 'performance', 'new', 3),
('Отсутствует sitemap.xml', 'Нет файла sitemap для индексации', 'high', 'indexation', 'new', 4),
('Нет robots.txt', 'Отсутствует файл robots.txt', 'medium', 'indexation', 'new', 5),
('Нет canonical тегов', 'Могут быть дубли страниц фильтров', 'medium', 'deduplication', 'new', 6);

-- 3. ТАБЛИЦА КЛЮЧЕВЫХ ЗАПРОСОВ И ПОЗИЦИЙ

CREATE TABLE IF NOT EXISTS keyword_rankings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    keyword VARCHAR(255),
    search_engine ENUM('google', 'yandex'),
    current_position INT,
    previous_position INT,
    search_volume INT,
    competition ENUM('low', 'medium', 'high'),
    intent ENUM('commercial', 'informational', 'navigational'),
    url VARCHAR(255),
    tracked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Пример ключевых запросов
INSERT INTO keyword_rankings (keyword, search_engine, search_volume, competition, intent, url) VALUES
('купить комплектующие для пк', 'google', 2500, 'high', 'commercial', '/catalog'),
('процессоры интел', 'google', 1800, 'high', 'commercial', '/catalog?category=processors'),
('видеокарта nvidia', 'google', 3200, 'high', 'commercial', '/catalog?category=video-cards'),
('материнская плата', 'google', 1500, 'high', 'commercial', '/catalog?category=motherboards'),
('оперативная память дdr5', 'google', 1200, 'medium', 'commercial', '/catalog?category=memory'),
('как выбрать видеокарту', 'google', 800, 'low', 'informational', '/blog/how-to-choose-gpu'),
('совместимость комплектующих', 'google', 600, 'low', 'informational', '/blog/compatibility');

-- 4. ТАБЛИЦА ССЫЛОЧНОГО ПРОФИЛЯ

CREATE TABLE IF NOT EXISTS backlinks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source_url VARCHAR(255),
    source_domain VARCHAR(255),
    anchor_text VARCHAR(255),
    target_url VARCHAR(255),
    quality ENUM('high', 'medium', 'low'),
    follow BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'removed', 'toxic'),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. ТАБЛИЦА КОНТЕНТ-ПЛАНА

CREATE TABLE IF NOT EXISTS content_plan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    topic VARCHAR(100),
    keyword VARCHAR(255),
    url_slug VARCHAR(255),
    priority ENUM('high', 'medium', 'low'),
    status ENUM('planned', 'draft', 'published', 'scheduled'),
    target_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO content_plan (title, topic, keyword, url_slug, priority, status) VALUES
('Как выбрать видеокарту для игр в 2026 году', 'gaming', 'выбрать видеокарту', 'how-to-choose-gpu-2026', 'high', 'planned'),
('Топ-5 процессоров для сборки рабочей станции', 'workstation', 'процессоры для рабочей станции', 'top-5-processors-workstation', 'high', 'planned'),
('Совместимость комплектующих: на что обратить внимание', 'compatibility', 'совместимость комплектующих', 'component-compatibility', 'medium', 'planned'),
('SSD vs HDD: что выбрать в 2026 году?', 'storage', 'ssd vs hdd', 'ssd-vs-hdd-2026', 'medium', 'planned'),
('Гайд по сборке ПК для начинающих', 'building', 'как собрать пк', 'pc-building-guide', 'high', 'planned');
