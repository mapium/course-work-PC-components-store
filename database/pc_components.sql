-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 11 2026 г., 00:49
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `pc_components`
--

-- --------------------------------------------------------

--
-- Структура таблицы `audit_issues`
--

CREATE TABLE `audit_issues` (
  `id` int NOT NULL,
  `issue_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_description` text COLLATE utf8mb4_unicode_ci,
  `severity` enum('critical','high','medium','low') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','in_progress','resolved') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `audit_issues`
--

INSERT INTO `audit_issues` (`id`, `issue_title`, `issue_description`, `severity`, `category`, `status`, `priority`, `notes`, `created_at`, `resolved_at`) VALUES
(1, 'Отсутствуют meta теги', 'На многих страницах отсутствуют оптимизированные meta description', 'high', 'meta_tags', 'new', 1, NULL, '2026-11-10 18:59:47', NULL),
(2, 'Нет микроразметки', 'Отсутствует Schema.org разметка для товаров', 'high', 'schema', 'new', 2, NULL, '2026-11-10 18:59:47', NULL),
(3, 'Медленная загрузка изображений', 'Изображения товаров не оптимизированы', 'medium', 'performance', 'new', 3, NULL, '2026-11-10 18:59:47', NULL),
(4, 'Отсутствует sitemap.xml', 'Нет файла sitemap для индексации', 'high', 'indexation', 'new', 4, NULL, '2026-11-10 18:59:47', NULL),
(5, 'Нет robots.txt', 'Отсутствует файл robots.txt', 'medium', 'indexation', 'new', 5, NULL, '2026-11-10 18:59:47', NULL),
(6, 'Нет canonical тегов', 'Могут быть дубли страниц фильтров', 'medium', 'deduplication', 'new', 6, NULL, '2026-11-10 18:59:47', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `backlinks`
--

CREATE TABLE `backlinks` (
  `id` int NOT NULL,
  `source_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anchor_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quality` enum('high','medium','low') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `follow` tinyint(1) DEFAULT '1',
  `status` enum('active','removed','toxic') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `added_at`) VALUES
(12, NULL, 'ut753u8lv8thg7iog1g5tq6atoid0ioi', 12, 1, '2026-11-01 17:36:21'),
(17, 2, '9njdbts2e4ogjdm57vnv1ahnnh3d6pfv', 14, 2, '2026-11-01 19:14:24'),
(19, 2, '9njdbts2e4ogjdm57vnv1ahnnh3d6pfv', 1, 1, '2026-11-01 19:17:10'),
(20, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 12, 1, '2026-11-04 17:05:50'),
(21, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 11, 1, '2026-11-04 17:05:52'),
(22, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 10, 1, '2026-11-04 17:05:53'),
(23, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 9, 1, '2026-11-04 17:05:53'),
(29, 2, NULL, 4, 1, '2026-11-09 18:50:36'),
(30, 2, NULL, 11, 3, '2026-11-09 18:51:10');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `meta_title`, `meta_description`, `parent_id`, `icon_path`, `created_at`, `updated_at`, `sort_order`, `is_visible`) VALUES
(1, 'Процессоры', 'processors', 'Процессоры Intel и AMD для компьютеров', 'Процессоры - купить CPU Intel и AMD', 'Широкий выбор процессоров Intel Core и AMD Ryzen. Низкие цены, гарантия качества.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16', 0, 1),
(2, 'Видеокарты', 'graphics-cards', 'Видеокарты NVIDIA и AMD для игр и работы', 'Видеокарты - купить GPU NVIDIA и AMD', 'Мощные видеокарты NVIDIA GeForce и AMD Radeon. Быстрая доставка.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16', 0, 1),
(3, 'Материнские платы', 'motherboards', 'Материнские платы для Intel и AMD', 'Материнские платы - купить для Intel и AMD', 'Качественные материнские платы ASUS, MSI, Gigabyte. Гарантия производителя.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16', 0, 1),
(4, 'Оперативная память', 'ram', 'Оперативная память DDR4 и DDR5', 'Оперативная память - купить RAM DDR4/DDR5', 'Быстрая оперативная память от ведущих производителей. Доступные цены.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16', 0, 1),
(5, 'Накопители', 'storage', 'SSD и HDD накопители для компьютера', 'Накопители - купить SSD и HDD', 'Надежные SSD M.2 NVMe и SATA накопители. Большой выбор объемов.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16', 0, 1),
(6, 'Охлаждение', 'cooling', 'Воздушные кулеры, готовые СЖО и компоненты кастомных водяных контуров', NULL, NULL, NULL, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 10, 1),
(7, 'Воздушные кулеры', 'air-cooling', 'Башенные кулеры для охлаждения процессоров', NULL, NULL, 6, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 0, 1),
(8, 'Готовые СЖО', 'liquid-cooling', 'Замкнутые системы жидкостного охлаждения процессора', NULL, NULL, 6, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 1, 1),
(9, 'Кастомное водяное охлаждение', 'custom-water-cooling', 'Наборы для сборки открытого водяного контура и отдельные водоблоки', NULL, NULL, 6, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 2, 1),
(10, 'Блоки питания', 'power-supplies', 'Блоки питания для домашних и игровых компьютеров', NULL, NULL, NULL, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 11, 1),
(11, 'Корпуса', 'cases', 'Корпуса с продуманной вентиляцией и местом для комплектующих', NULL, NULL, NULL, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21', 12, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `content_plan`
--

CREATE TABLE `content_plan` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('high','medium','low') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('planned','draft','published','scheduled') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `content_plan`
--

INSERT INTO `content_plan` (`id`, `title`, `topic`, `keyword`, `url_slug`, `priority`, `status`, `target_date`, `notes`, `created_at`) VALUES
(1, 'Как выбрать видеокарту для игр в 2026 году', 'gaming', 'выбрать видеокарту', 'how-to-choose-gpu-2026', 'high', 'planned', NULL, NULL, '2026-11-10 18:59:47'),
(2, 'Топ-5 процессоров для сборки рабочей станции', 'workstation', 'процессоры для рабочей станции', 'top-5-processors-workstation', 'high', 'planned', NULL, NULL, '2026-11-10 18:59:47'),
(3, 'Совместимость комплектующих: на что обратить внимание', 'compatibility', 'совместимость комплектующих', 'component-compatibility', 'medium', 'planned', NULL, NULL, '2026-11-10 18:59:47'),
(4, 'SSD vs HDD: что выбрать в 2026 году?', 'storage', 'ssd vs hdd', 'ssd-vs-hdd-2026', 'medium', 'planned', NULL, NULL, '2026-11-10 18:59:47'),
(5, 'Гайд по сборке ПК для начинающих', 'building', 'как собрать пк', 'pc-building-guide', 'high', 'planned', NULL, NULL, '2026-11-10 18:59:47');

-- --------------------------------------------------------

--
-- Структура таблицы `delivery_addresses`
--

CREATE TABLE `delivery_addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `keyword_rankings`
--

CREATE TABLE `keyword_rankings` (
  `id` int NOT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_engine` enum('google','yandex') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_position` int DEFAULT NULL,
  `previous_position` int DEFAULT NULL,
  `search_volume` int DEFAULT NULL,
  `competition` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intent` enum('commercial','informational','navigational') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `keyword_rankings`
--

INSERT INTO `keyword_rankings` (`id`, `keyword`, `search_engine`, `current_position`, `previous_position`, `search_volume`, `competition`, `intent`, `url`, `tracked_at`) VALUES
(1, 'купить комплектующие для пк', 'google', NULL, NULL, 2500, 'high', 'commercial', '/catalog', '2026-11-10 18:59:47'),
(2, 'процессоры интел', 'google', NULL, NULL, 1800, 'high', 'commercial', '/catalog?category=processors', '2026-11-10 18:59:47'),
(3, 'видеокарта nvidia', 'google', NULL, NULL, 3200, 'high', 'commercial', '/catalog?category=video-cards', '2026-11-10 18:59:47'),
(4, 'материнская плата', 'google', NULL, NULL, 1500, 'high', 'commercial', '/catalog?category=motherboards', '2026-11-10 18:59:47'),
(5, 'оперативная память дdr5', 'google', NULL, NULL, 1200, 'medium', 'commercial', '/catalog?category=memory', '2026-11-10 18:59:47'),
(6, 'как выбрать видеокарту', 'google', NULL, NULL, 800, 'low', 'informational', '/blog/how-to-choose-gpu', '2026-11-10 18:59:47'),
(7, 'совместимость комплектующих', 'google', NULL, NULL, 600, 'low', 'informational', '/blog/compatibility', '2026-11-10 18:59:47');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','assembled','shipped','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delivery_method` enum('pickup','courier','mail') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'courier',
  `payment_method` enum('card','cash','online') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'card',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `total_amount`, `delivery_address`, `delivery_method`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-20261101-E5B6C1', 'pending', '235280.00', 'ывфаыва, аывааыв, ываываыв', 'courier', 'card', NULL, '2026-11-01 18:56:58', '2026-11-01 18:56:58'),
(2, 2, 'ORD-20261101-03B3EC', 'delivered', '64260.00', 'фывфыв, ыфвфыв, фывфыв', 'courier', 'card', NULL, '2026-11-01 19:07:40', '2026-11-09 19:17:44'),
(3, 2, 'ORD-20261101-A52476', 'assembled', '140279.00', 'ываываыав, ывавыаыав, ываываыва', 'courier', 'card', NULL, '2026-11-01 19:11:03', '2026-11-09 19:17:39');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 4, 1, '159990.00', '159990.00'),
(2, 1, 5, 1, '74990.00', '74990.00'),
(3, 2, 11, 4, '15990.00', '63960.00'),
(4, 3, 11, 1, '15990.00', '15990.00'),
(5, 3, 1, 1, '55990.00', '55990.00'),
(6, 3, 14, 1, '67999.00', '67999.00');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `detailed_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `views` int DEFAULT '0',
  `orders_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Количество неотменённых заказов с товаром, включая учебные значения',
  `rating` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `detailed_description`, `price`, `old_price`, `stock`, `brand`, `sku`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `views`, `orders_count`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 'Intel Core i9-13900K', 'intel-core-i9-13900k', 'Флагманский 24-ядерный процессор Intel 13-го поколения', 'Intel Core i9-13900K - это мощнейший процессор для энтузиастов и профессионалов. 24 ядра (8P+16E), 32 потока, тактовая частота до 5.8 ГГц в режиме Turbo Boost Max 3.0. Поддержка DDR5-5600 и PCIe 5.0. Идеален для игр в 4K, стриминга, 3D-рендеринга и профессиональных задач.', '55990.00', '64990.00', 15, 'Intel', 'INTEL-I9-13900K', '', '', '', 1, 1, 0, 372, '4.80', '2026-10-25 15:59:16', '2026-10-25 17:29:29'),
(2, 1, 'Intel Core i5-13600K', 'intel-core-i5-13600k', '14-ядерный процессор Intel для игр и работы', 'Intel Core i5-13600K - оптимальное соотношение цена/производительность. 14 ядер (6P+8E), 20 потоков, частота до 5.1 ГГц. Отличный выбор для игровых систем среднего класса и многозадачности. Поддержка DDR5 и PCIe 5.0.', '29990.00', '34990.00', 25, 'Intel', 'INTEL-I5-13600K', NULL, NULL, NULL, 1, 1, 0, 413, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(3, 1, 'AMD Ryzen 9 7950X', 'amd-ryzen-9-7950x', '16-ядерный процессор AMD на архитектуре Zen 4', 'AMD Ryzen 9 7950X - топовый процессор на новейшей архитектуре Zen 4. 16 ядер, 32 потока, частота до 5.7 ГГц. Технология AMD 3D V-Cache обеспечивает невероятную производительность в играх и рабочих приложениях. Поддержка DDR5 и PCIe 5.0.', '52990.00', '59990.00', 18, 'AMD', 'AMD-R9-7950X', NULL, NULL, NULL, 1, 1, 0, 210, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(4, 2, 'NVIDIA GeForce RTX 4090', 'nvidia-geforce-rtx-4090', 'Флагманская видеокарта NVIDIA для 4K гейминга', 'NVIDIA GeForce RTX 4090 - абсолютный лидер в мире видеокарт. 24 GB GDDR6X памяти, архитектура Ada Lovelace, поддержка Ray Tracing 3-го поколения и DLSS 3. Идеальна для игр в 4K с максимальными настройками, 3D-моделирования и работы с ИИ. Частота GPU до 2.52 ГГц.', '159990.00', '179990.00', 8, 'NVIDIA', 'RTX-4090-24GB', NULL, NULL, NULL, 1, 1, 0, 497, '5.00', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(5, 2, 'NVIDIA GeForce RTX 4070 Ti', 'nvidia-geforce-rtx-4070-ti', 'Мощная видеокарта для 1440p и 4K гейминга', 'NVIDIA GeForce RTX 4070 Ti - отличный выбор для высокопроизводительного гейминга. 12 GB GDDR6X памяти, поддержка DLSS 3 и Ray Tracing. Обеспечивает стабильные 60+ FPS в играх AAA в разрешении 1440p и 4K. Энергоэффективная архитектура Ada Lovelace.', '74990.00', '84990.00', 20, 'NVIDIA', 'RTX-4070TI-12GB', NULL, NULL, NULL, 1, 1, 0, 336, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(6, 2, 'AMD Radeon RX 7900 XTX', 'amd-radeon-rx-7900-xtx', 'Топовая видеокарта AMD с 24 GB памяти', 'AMD Radeon RX 7900 XTX - флагман AMD на архитектуре RDNA 3. 24 GB GDDR6 памяти, поддержка FSR 3 и Ray Tracing. Превосходная производительность в играх 4K, майнинге и работе с графикой. Частота GPU до 2.5 ГГц, 384-bit шина памяти.', '89990.00', '99990.00', 12, 'AMD', 'RX-7900XTX-24GB', NULL, NULL, NULL, 1, 1, 0, 367, '4.60', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(7, 3, 'ASUS ROG STRIX Z790-E', 'asus-rog-strix-z790-e', 'Премиальная материнская плата для Intel 13-го поколения', 'ASUS ROG STRIX Z790-E Gaming WiFi - топовая плата на чипсете Z790. Поддержка процессоров Intel 12-го и 13-го поколений, DDR5-7800+(OC), PCIe 5.0 x16, 4x M.2 слота. WiFi 6E, 2.5G LAN, USB 3.2 Gen 2x2. Продвинутая система питания 18+1 фаз для разгона. RGB подсветка Aura Sync.', '39990.00', '44990.00', 10, 'ASUS', 'ASUS-Z790-E-GAMING', NULL, NULL, NULL, 1, 1, 0, 466, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(8, 3, 'MSI MEG Z790 ACE', 'msi-meg-z790-ace', 'Флагманская материнская плата MSI для энтузиастов', 'MSI MEG Z790 ACE - премиум плата для экстремального разгона. Поддержка Intel 13-го поколения, DDR5-7200+(OC), 5x M.2 слотов с радиаторами, PCIe 5.0. Система питания 22+1+1 фаз. WiFi 6E, 2.5G + 10G LAN. Поддержка MSI Mystic Light RGB.', '44990.00', NULL, 8, 'MSI', 'MSI-Z790-ACE', NULL, NULL, NULL, 0, 1, 0, 364, '4.80', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(9, 3, 'AMD ROG STRIX X870-E', 'amd-rog-strix-x870-e', 'Топовая материнская плата для AMD Ryzen 7000', 'ASUS ROG STRIX X870-E Gaming - премиальное решение для AMD Ryzen 7000. Чипсет X870, поддержка DDR5-6400+(OC), PCIe 5.0 для GPU и NVMe, 4x M.2 слота. WiFi 6E, 2.5G LAN, USB 4.0. Система питания 18+2 фаз. RGB подсветка Aura Sync.', '38990.00', '42990.00', 12, 'ASUS', 'ASUS-X870-E-GAMING', NULL, NULL, NULL, 1, 1, 0, 171, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(10, 4, 'Corsair Vengeance RGB PRO 32GB DDR4', 'corsair-vengeance-rgb-pro-32gb-ddr4', 'Высокопроизводительная память с RGB подсветкой', 'Corsair Vengeance RGB PRO 32GB (2x16GB) DDR4-3600 CL18 - идеальный выбор для игровых систем. Яркая RGB подсветка с синхронизацией через iCUE, алюминиевый радиатор для эффективного охлаждения. XMP 2.0 профили для легкого разгона. Совместимость с Intel и AMD платформами.', '12990.00', '14990.00', 30, 'Corsair', 'CORSAIR-VEN-RGB-32GB', NULL, NULL, NULL, 1, 1, 0, 245, '4.60', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(11, 4, 'Kingston Fury Beast 32GB DDR5', 'kingston-fury-beast-32gb-ddr5', 'Современная DDR5 память для новых платформ', 'Kingston Fury Beast 32GB (2x16GB) DDR5-6000 CL36 - передовая память для Intel 13-го поколения и AMD Ryzen 7000. Встроенное управление питанием, поддержка Intel XMP 3.0 и AMD EXPO. Низкие тайминги и высокая частота для максимальной производительности.', '15990.00', NULL, 25, 'Kingston', 'KINGSTON-BEAST-DDR5-32GB', NULL, NULL, NULL, 1, 1, 0, 280, '4.50', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(12, 5, 'Samsung 990 PRO 1TB M.2 NVMe', 'samsung-990-pro-1tb-m2-nvme', 'Сверхбыстрый SSD PCIe 4.0 от Samsung', 'Samsung 990 PRO 1TB M.2 NVMe - топовый накопитель с PCIe 4.0 x4. Скорость чтения до 7450 МБ/с, записи до 6900 МБ/с. Технология Samsung V-NAND 3-bit MLC, контроллер Samsung Pascal. Идеален для игр, работы с видео и профессиональных задач. Гарантия 5 лет.', '11990.00', '13990.00', 40, 'Samsung', 'SAMSUNG-990PRO-1TB', NULL, NULL, NULL, 1, 1, 0, 435, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(13, 5, 'Samsung 870 EVO 1TB SSD', 'samsung-870-evo-1tb-ssd', 'Надежный SATA SSD для апгрейда системы', 'Samsung 870 EVO 1TB 2.5\" SATA III - проверенное решение для модернизации ПК или ноутбука. Скорость чтения/записи до 560/530 МБ/с. Технология Samsung V-NAND 3-bit MLC обеспечивает надежность и долговечность. TurboWrite для ускорения операций записи. Гарантия 5 лет.', '8990.00', '9990.00', 50, 'Samsung', 'SAMSUNG-870EVO-1TB', NULL, NULL, NULL, 0, 1, 0, 156, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(14, 1, 'Intel Core i9', 'intel-core-i9', 'апу-впалшщвапор', 'ого да верю ему', '67999.00', '79999.00', 9, 'Intel', NULL, 'egf', 'ee', 'hgh', 0, 1, 0, 411, NULL, '2026-11-01 16:42:30', '2026-11-09 18:57:15'),
(15, 1, 'AMD Ryzen 5 7600', 'amd-ryzen-5-7600', 'Процессор Zen 4 с 6 ядрами и 12 потоками для платформы AM5. Частота до 5,1 ГГц, поддержка DDR5 и кулер Wraith Stealth в комплекте.', 'Ryzen 5 7600 подходит для игрового и домашнего компьютера на платформе AM5. Шесть ядер Zen 4 и двенадцать потоков обеспечивают многозадачность, а встроенная графика помогает запустить систему без дискретной видеокарты. Для установки нужна совместимая материнская плата AM5 и память DDR5. В коробочной версии предусмотрен кулер Wraith Stealth.', '18990.00', '20990.00', 20, 'AMD', '100-100001015BOX', 'AMD Ryzen 5 7600 — PCComponents Store', 'Процессор Zen 4 с 6 ядрами и 12 потоками для платформы AM5. Частота до 5,1 ГГц, поддержка DDR5 и кулер Wraith Stealth в комплекте.', NULL, 1, 1, 0, 489, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(16, 1, 'AMD Ryzen 7 7800X3D', 'amd-ryzen-7-7800x3d', 'Игровой процессор AM5 с 8 ядрами, 16 потоками и 96 МБ кэша L3 благодаря технологии 3D V-Cache. Кулер приобретается отдельно.', 'Ryzen 7 7800X3D объединяет архитектуру Zen 4 и дополнительный слой кэша 3D V-Cache. Процессор рассчитан на платформу AM5 с памятью DDR5 и подходит для мощной игровой сборки. В комплект не входит система охлаждения: перед установкой подберите совместимый кулер и проверьте поддержку процессора в BIOS материнской платы.', '39990.00', NULL, 12, 'AMD', '100-100000910WOF', 'AMD Ryzen 7 7800X3D — PCComponents Store', 'Игровой процессор AM5 с 8 ядрами, 16 потоками и 96 МБ кэша L3 благодаря технологии 3D V-Cache. Кулер приобретается отдельно.', NULL, 1, 1, 0, 352, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(17, 1, 'Intel Core i7-14700K', 'intel-core-i7-14700k', 'Процессор LGA1700 с 20 ядрами (8P + 12E), 28 потоками и частотой до 5,6 ГГц. Поддерживает DDR4 или DDR5 в зависимости от материнской платы.', 'Core i7-14700K использует гибридную конфигурацию из восьми производительных и двенадцати энергоэффективных ядер. Разблокированный множитель подходит для совместимой платы с чипсетом Z-серии. Процессор имеет встроенную графику UHD Graphics 770. Требуется отдельная эффективная система охлаждения; для платы LGA1700 проверьте поддержку 14-го поколения и установите актуальный BIOS.', '36990.00', NULL, 14, 'Intel', 'BX8071514700K', 'Intel Core i7-14700K — PCComponents Store', 'Процессор LGA1700 с 20 ядрами (8P + 12E), 28 потоками и частотой до 5,6 ГГц. Поддерживает DDR4 или DDR5 в зависимости от материнской платы.', NULL, 0, 1, 0, 397, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(18, 2, 'ASUS Dual GeForce RTX 4060 OC 8GB', 'asus-dual-geforce-rtx-4060-oc-8gb', 'Видеокарта GeForce RTX 4060 с 8 ГБ памяти GDDR6, двухвентиляторным охлаждением и поддержкой DLSS 3 для игр в Full HD.', 'ASUS Dual RTX 4060 OC — компактная видеокарта на архитектуре NVIDIA Ada Lovelace. Два вентилятора охлаждают графический процессор, а технологии трассировки лучей и DLSS поддерживаются в совместимых играх. Перед покупкой проверьте свободное место в корпусе, наличие 8-контактного кабеля PCIe и достаточную мощность блока питания.', '32990.00', NULL, 18, 'ASUS', 'DUAL-RTX4060-O8G', 'ASUS Dual GeForce RTX 4060 OC 8GB — PCComponents Store', 'Видеокарта GeForce RTX 4060 с 8 ГБ памяти GDDR6, двухвентиляторным охлаждением и поддержкой DLSS 3 для игр в Full HD.', NULL, 0, 1, 0, 474, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(19, 2, 'ASUS TUF Gaming GeForce RTX 4070 SUPER OC 12GB', 'asus-tuf-geforce-rtx-4070-super-oc-12gb', 'GeForce RTX 4070 SUPER с 12 ГБ GDDR6X, тремя вентиляторами и металлической конструкцией TUF Gaming для игровой сборки высокого класса.', 'TUF Gaming RTX 4070 SUPER OC оснащена тремя вентиляторами и усиленной конструкцией. Архитектура Ada Lovelace поддерживает DLSS 3 и аппаратную трассировку лучей. Карта рассчитана на производительную игровую систему; перед установкой проверьте длину и толщину корпуса видеокарты, питание 16-pin и рекомендации к мощности блока питания.', '79990.00', NULL, 8, 'ASUS', 'TUF-RTX4070S-O12G-GAMING', 'ASUS TUF Gaming GeForce RTX 4070 SUPER OC 12GB — PCComponents Store', 'GeForce RTX 4070 SUPER с 12 ГБ GDDR6X, тремя вентиляторами и металлической конструкцией TUF Gaming для игровой сборки высокого класса.', NULL, 1, 1, 0, 270, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(20, 3, 'ASUS TUF Gaming B650-PLUS WIFI', 'asus-tuf-gaming-b650-plus-wifi', 'Материнская плата ATX на чипсете AMD B650 для сокета AM5. Четыре слота DDR5, три M.2, сеть 2,5 Гбит/с и Wi-Fi 6.', 'TUF Gaming B650-PLUS WIFI служит основой игровой системы с процессором AMD Ryzen для AM5. Плата поддерживает память DDR5, имеет три разъёма M.2 и радиаторы для накопителей. Проводная сеть 2,5 Гбит/с и Wi-Fi 6 помогают подключить компьютер к сети. Для установки новых поколений Ryzen может потребоваться обновление BIOS.', '22990.00', NULL, 11, 'ASUS', 'TUF-GAMING-B650-PLUS-WIFI', 'ASUS TUF Gaming B650-PLUS WIFI — PCComponents Store', 'Материнская плата ATX на чипсете AMD B650 для сокета AM5. Четыре слота DDR5, три M.2, сеть 2,5 Гбит/с и Wi-Fi 6.', NULL, 0, 1, 0, 160, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(21, 3, 'ASUS TUF Gaming B760-PLUS WIFI', 'asus-tuf-gaming-b760-plus-wifi', 'Материнская плата ATX с сокетом LGA1700 и чипсетом Intel B760. Память DDR5, три слота M.2, Wi-Fi 6 и Ethernet 2,5 Гбит/с.', 'TUF Gaming B760-PLUS WIFI подходит для совместимых процессоров Intel Core с сокетом LGA1700. Это версия с DDR5, а не модификация D4. Разъёмы M.2 позволяют установить быстрые NVMe-накопители, а встроенные Wi-Fi и Ethernet обеспечивают выбор способа подключения. Перед установкой процессора 14-го поколения проверьте версию BIOS.', '20990.00', NULL, 13, 'ASUS', 'TUF-GAMING-B760-PLUS-WIFI', 'ASUS TUF Gaming B760-PLUS WIFI — PCComponents Store', 'Материнская плата ATX с сокетом LGA1700 и чипсетом Intel B760. Память DDR5, три слота M.2, Wi-Fi 6 и Ethernet 2,5 Гбит/с.', NULL, 0, 1, 0, 361, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(22, 4, 'G.SKILL Trident Z5 Neo RGB 32GB DDR5-6000 CL36', 'gskill-trident-z5-neo-rgb-32gb-ddr5-6000-cl36', 'Комплект из двух модулей DDR5 по 16 ГБ с профилем AMD EXPO, скоростью 6000 MT/s, таймингами CL36 и RGB-подсветкой.', 'Trident Z5 Neo RGB — комплект памяти общей ёмкостью 32 ГБ для совместимых DDR5-систем. Два модуля по 16 ГБ оснащены радиаторами и RGB-подсветкой. Заявленные 6000 MT/s и тайминги 36-36-36-96 доступны при включении профиля разгона на совместимой плате; результат зависит от процессора и BIOS. Перед покупкой проверьте список совместимости платы.', '13990.00', NULL, 20, 'G.SKILL', 'F5-6000J3636F16GX2-TZ5NR', 'G.SKILL Trident Z5 Neo RGB 32GB DDR5-6000 CL36 — PCComponents Store', 'Комплект из двух модулей DDR5 по 16 ГБ с профилем AMD EXPO, скоростью 6000 MT/s, таймингами CL36 и RGB-подсветкой.', NULL, 0, 1, 0, 90, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(23, 4, 'Corsair Vengeance 64GB DDR5-6000 CL30', 'corsair-vengeance-64gb-ddr5-6000-cl30', 'Комплект DDR5 общей ёмкостью 64 ГБ (2 × 32 ГБ), скоростью 6000 MT/s и таймингами CL30. Чёрные радиаторы без подсветки.', 'Corsair Vengeance 64 ГБ подходит для работы с большими проектами, виртуальными машинами и многозадачностью. Низкопрофильные алюминиевые радиаторы облегчают установку рядом с процессорным кулером. Память имеет профиль Intel XMP 3.0; скорость 6000 MT/s требует совместимого контроллера, платы и настройки BIOS. Комплект не подходит для DDR4-слотов.', '23990.00', NULL, 15, 'Corsair', 'CMK64GX5M2B6000C30', 'Corsair Vengeance 64GB DDR5-6000 CL30 — PCComponents Store', 'Комплект DDR5 общей ёмкостью 64 ГБ (2 × 32 ГБ), скоростью 6000 MT/s и таймингами CL30. Чёрные радиаторы без подсветки.', NULL, 0, 1, 0, 239, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(24, 10, 'Corsair RM850x 850W 80 PLUS Gold', 'corsair-rm850x-850w-gold', 'Полностью модульный блок питания RM850x серии 2021 мощностью 850 Вт, с сертификатом 80 PLUS Gold и 135-мм вентилятором.', 'Corsair RM850x 2021 рассчитан на производительные компьютеры. Полностью модульные кабели помогают аккуратно собрать систему, а режим Zero RPM останавливает вентилятор при небольшой нагрузке. Это модель поколения ATX 2.4: кабель для видеокарты с 16-контактным разъёмом нужно подбирать отдельно с учётом совместимости. Используйте только кабели, предназначенные для этой серии блока питания.', '16990.00', NULL, 10, 'Corsair', 'CP-9020200-NA', 'Corsair RM850x 850W 80 PLUS Gold — PCComponents Store', 'Полностью модульный блок питания RM850x серии 2021 мощностью 850 Вт, с сертификатом 80 PLUS Gold и 135-мм вентилятором.', NULL, 0, 1, 0, 68, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(25, 11, 'Corsair 4000D Airflow Black', 'corsair-4000d-airflow-black', 'Корпус Mid-Tower с продуваемой передней панелью, закалённым стеклом и двумя вентиляторами 120 мм. Подходит для плат ATX, Micro-ATX и Mini-ITX.', '4000D Airflow имеет перфорированную переднюю панель и систему укладки кабелей RapidRoute. Боковая панель из закалённого стекла позволяет видеть комплектующие. Спереди можно разместить радиатор до 360 мм; совместимость верхнего радиатора зависит от памяти и других компонентов. Корпус продаётся без блока питания и без комплектующих, изображённых в примерах сборок.', '10990.00', NULL, 14, 'Corsair', 'CC-9011200-WW', 'Corsair 4000D Airflow Black — PCComponents Store', 'Корпус Mid-Tower с продуваемой передней панелью, закалённым стеклом и двумя вентиляторами 120 мм. Подходит для плат ATX, Micro-ATX и Mini-ITX.', NULL, 0, 1, 0, 240, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(26, 7, 'DeepCool AK620', 'deepcool-ak620', 'Двухбашенный процессорный кулер с шестью медными тепловыми трубками и двумя PWM-вентиляторами 120 мм. Высота — 160 мм.', 'DeepCool AK620 использует две секции радиатора и шесть тепловых трубок для отвода тепла от процессора. Два вентилятора FK120 регулируются по PWM. Перед установкой убедитесь, что корпус допускает кулер высотой 160 мм, а память помещается под передним вентилятором. Проверьте наличие крепления для вашего сокета в выбранной комплектации.', '6490.00', NULL, 24, 'DeepCool', 'R-AK620-BKNNMT-G', 'DeepCool AK620 — PCComponents Store', 'Двухбашенный процессорный кулер с шестью медными тепловыми трубками и двумя PWM-вентиляторами 120 мм. Высота — 160 мм.', NULL, 0, 1, 0, 246, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(27, 8, 'ARCTIC Liquid Freezer III 360 Black', 'arctic-liquid-freezer-iii-360-black', 'Готовая СЖО с радиатором 360 мм, тремя вентиляторами P12 и отдельным вентилятором для зоны VRM. Чёрная версия без подсветки.', 'Liquid Freezer III 360 — замкнутая система охлаждения, не требующая сборки собственного контура. Толстый 38-мм радиатор и три вентилятора P12 рассчитаны на отвод тепла от производительного процессора. Водоблок оснащён вентилятором для области VRM. Перед покупкой проверьте место для радиатора вместе с вентиляторами и совместимость с материнской платой по списку ARCTIC.', '13990.00', NULL, 12, 'ARCTIC', 'ACFRE00136A', 'ARCTIC Liquid Freezer III 360 Black — PCComponents Store', 'Готовая СЖО с радиатором 360 мм, тремя вентиляторами P12 и отдельным вентилятором для зоны VRM. Чёрная версия без подсветки.', NULL, 0, 1, 0, 477, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(28, 8, 'Corsair iCUE H150i Elite Capellix XT', 'corsair-icue-h150i-elite-capellix-xt', 'Готовая СЖО 360 мм с тремя вентиляторами AF120 RGB Elite, подсветкой Capellix и контроллером Commander Core.', 'H150i Elite Capellix XT — закрытая система жидкостного охлаждения процессора с радиатором 360 мм. В комплект входят три вентилятора AF120 RGB Elite и контроллер Commander Core для управления охлаждением и подсветкой через iCUE. Эта СЖО не является набором для кастомного контура. Для установки нужны подходящие крепления, место в корпусе, питание SATA и внутренний USB-разъём.', '24990.00', NULL, 9, 'Corsair', 'CW-9060070-WW', 'Corsair iCUE H150i Elite Capellix XT — PCComponents Store', 'Готовая СЖО 360 мм с тремя вентиляторами AF120 RGB Elite, подсветкой Capellix и контроллером Commander Core.', NULL, 0, 1, 0, 459, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(29, 9, 'Corsair Hydro X iCUE XH305i RGB PRO', 'corsair-hydro-x-icue-xh305i-rgb-pro', 'Комплект кастомного водяного охлаждения с помпой XD5, водоблоком XC7 RGB PRO, медным радиатором XR5 360 и жёсткими трубками 14 мм.', 'XH305i RGB PRO — набор для самостоятельной сборки обслуживаемого водяного контура, а не готовая AIO-система. Он включает CPU-водоблок XC7 RGB PRO, помпу с резервуаром XD5, радиатор XR5 360, три вентилятора QL120 RGB, фитинги, жёсткие трубки и инструменты для их обработки. Перед сборкой необходимо проверить совместимость сокета и корпуса. Контур требует формовки трубок, проверки герметичности и регулярного обслуживания. Водоблок видеокарты не входит в комплект.', '74990.00', '79990.00', 4, 'Corsair', 'CX-9070008-WW', 'Corsair Hydro X iCUE XH305i RGB PRO — PCComponents Store', 'Комплект кастомного водяного охлаждения с помпой XD5, водоблоком XC7 RGB PRO, медным радиатором XR5 360 и жёсткими трубками 14 мм.', NULL, 1, 1, 0, 228, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(30, 9, 'Corsair Hydro X iCUE XH303i RGB PRO', 'corsair-hydro-x-icue-xh303i-rgb-pro', 'Набор кастомной СЖО с компактной помпой XD3 RGB, CPU-водоблоком XC7 RGB PRO, радиатором XR5 360 и тремя вентиляторами SP120 RGB Elite.', 'XH303i RGB PRO предназначен для ручной сборки открытого водяного контура с жёсткими трубками. Компактная помпа с резервуаром XD3 RGB помогает разместить систему в подходящем корпусе. В комплект входят XC7 RGB PRO, радиатор XR5 360, три вентилятора SP120 RGB Elite, трубки, фитинги и инструменты. Набор нужно самостоятельно собрать, заполнить и проверить на герметичность. Совместимость корпуса и сокета проверяется до покупки; GPU-водоблок приобретается отдельно.', '57990.00', NULL, 5, 'Corsair', 'CX-9070006-WW', 'Corsair Hydro X iCUE XH303i RGB PRO — PCComponents Store', 'Набор кастомной СЖО с компактной помпой XD3 RGB, CPU-водоблоком XC7 RGB PRO, радиатором XR5 360 и тремя вентиляторами SP120 RGB Elite.', NULL, 1, 1, 0, 383, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(31, 9, 'Corsair Hydro X XC7 RGB PRO Black', 'corsair-hydro-x-xc7-rgb-pro-black', 'CPU-водоблок для кастомной СЖО с никелированным медным основанием, прозрачной камерой и RGB-подсветкой. Помпа и радиатор не входят в комплект.', 'XC7 RGB PRO — отдельный процессорный водоблок для пользовательского водяного контура. Никелированное медное основание отводит тепло от процессора, прозрачная камера позволяет видеть жидкость, а RGB-подсветка управляется совместимым контроллером. Для работы необходимы помпа, резервуар, радиатор, трубки, фитинги G1/4 и подходящая охлаждающая жидкость. Это не самостоятельная система охлаждения и не готовая СЖО.', '11990.00', NULL, 7, 'Corsair', 'CX-9010015-WW', 'Corsair Hydro X XC7 RGB PRO Black — PCComponents Store', 'CPU-водоблок для кастомной СЖО с никелированным медным основанием, прозрачной камерой и RGB-подсветкой. Помпа и радиатор не входят в комплект.', NULL, 0, 1, 0, 193, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(32, 9, 'Corsair Hydro X XF Hardline 14mm, 4 шт.', 'corsair-hydro-x-xf-hardline-14mm-4-pack', 'Набор из четырёх чёрных фитингов G1/4 для жёстких трубок с внешним диаметром 14 мм. Для сборки кастомного водяного контура.', 'Фитинги Hydro X XF Hardline соединяют жёсткие трубки с водоблоками, радиаторами и другими компонентами с резьбой G1/4. В наборе четыре фитинга для трубок с внешним диаметром 14 мм. Они не предназначены для мягких шлангов или трубок другого диаметра. При сборке подготовьте ровные края трубок и проверьте герметичность контура до включения компьютера.', '2990.00', NULL, 16, 'Corsair', 'CX-9052008-WW', 'Corsair Hydro X XF Hardline 14mm, 4 шт. — PCComponents Store', 'Набор из четырёх чёрных фитингов G1/4 для жёстких трубок с внешним диаметром 14 мм. Для сборки кастомного водяного контура.', NULL, 0, 1, 0, 396, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21'),
(33, 9, 'Corsair Hydro X XF Ball Valve Black', 'corsair-hydro-x-xf-ball-valve-black', 'Чёрный шаровой кран с портами G1/4 для организации слива жидкости из кастомной СЖО. Фитинги и заглушки приобретаются отдельно.', 'Hydro X XF Ball Valve помогает организовать обслуживаемый сливной участок пользовательского водяного контура. Кран устанавливается с подходящими соединителями G1/4; фитинги, переходники и заглушки не входят в этот товар. Место установки выбирайте так, чтобы кран был доступен при обслуживании и не мешал другим компонентам. Перед запуском системы проверьте положение крана и герметичность соединений.', '2490.00', NULL, 12, 'Corsair', 'CX-9055019-WW', 'Corsair Hydro X XF Ball Valve Black — PCComponents Store', 'Чёрный шаровой кран с портами G1/4 для организации слива жидкости из кастомной СЖО. Фитинги и заглушки приобретаются отдельно.', NULL, 0, 1, 0, 463, NULL, '2026-11-10 20:26:21', '2026-11-10 20:26:21');

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_main`, `sort_order`, `created_at`) VALUES
(66, 1, 'Intel Core i9-13900K.jpg', 'Intel Core i9-13900K', 1, 1, '2026-10-25 15:59:16'),
(67, 2, 'Intel Core i5-13600K.jpg', 'Intel Core i5-13600K', 1, 1, '2026-10-25 15:59:16'),
(68, 3, 'AMD Ryzen 9 7950X.jpg', 'AMD Ryzen 9 7950X', 1, 1, '2026-10-25 15:59:16'),
(69, 4, 'NVIDIA GeForce RTX 4090.jpg', 'NVIDIA GeForce RTX 4090', 1, 1, '2026-10-25 15:59:16'),
(70, 5, 'NVIDIA GeForce RTX 4070 Ti.jpg', 'NVIDIA GeForce RTX 4070 Ti', 1, 1, '2026-10-25 15:59:16'),
(71, 6, 'AMD Radeon RX 7900 XTX.jpg', 'AMD Radeon RX 7900 XTX', 1, 1, '2026-10-25 15:59:16'),
(72, 7, 'ASUS ROG STRIX Z790-E.jpg', 'ASUS ROG STRIX Z790-E Gaming WiFi', 1, 1, '2026-10-25 15:59:16'),
(73, 8, 'MSI MEG Z790 ACE.jpg', 'MSI MEG Z790 ACE', 1, 1, '2026-10-25 15:59:16'),
(74, 9, 'AMD ROG STRIX X870-E.jpg', 'ASUS ROG STRIX X870-E Gaming', 1, 1, '2026-10-25 15:59:16'),
(75, 10, 'Corsair Vengeance RGB PRO 32GB DDR4.jpg', 'Corsair Vengeance RGB PRO 32GB DDR4', 1, 1, '2026-10-25 15:59:16'),
(76, 11, 'Kingston Fury Beast 32GB DDR5.jpg', 'Kingston Fury Beast 32GB DDR5', 1, 1, '2026-10-25 15:59:16'),
(77, 12, 'Samsung 990 PRO 1TB M.2 NVMe.jpg', 'Samsung 990 PRO 1TB M.2 NVMe', 1, 1, '2026-10-25 15:59:16'),
(78, 13, 'Samsung 870 EVO 1TB SSD.jpg', 'Samsung 870 EVO 1TB SSD', 1, 1, '2026-10-25 15:59:16'),
(79, 14, 'Samsung 870 EVO 1TB SSD.jpg', NULL, 1, 0, '2026-11-09 18:57:15'),
(80, 14, 'upload-22b05af35faddb3727e9a770.webp', NULL, 0, 1, '2026-11-09 18:57:15'),
(81, 15, 'amd-ryzen-5-7600.webp', 'Процессор AMD Ryzen 5 7600, вид со стороны теплораспределителя', 1, 0, '2026-11-10 20:26:21'),
(82, 15, 'amd-ryzen-5-7600-box.webp', 'Коробка процессора AMD Ryzen 5 7600', 0, 1, '2026-11-10 20:26:21'),
(83, 16, 'amd-ryzen-7-7800x3d.webp', 'Процессор AMD Ryzen 7 7800X3D, вид сверху', 1, 0, '2026-11-10 20:26:21'),
(84, 16, 'amd-ryzen-7-7800x3d-box.webp', 'Коробка процессора AMD Ryzen 7 7800X3D', 0, 1, '2026-11-10 20:26:21'),
(85, 17, 'intel-core-i7-14700k.webp', 'Процессор Intel Core i7-14700K, вид сверху', 1, 0, '2026-11-10 20:26:21'),
(86, 17, 'intel-core-i7-14700k-box.webp', 'Коробка процессора Intel Core i7-14700K', 0, 1, '2026-11-10 20:26:21'),
(87, 18, 'asus-dual-rtx-4060-oc-8gb.webp', 'Видеокарта ASUS Dual GeForce RTX 4060 OC 8GB, сторона вентиляторов', 1, 0, '2026-11-10 20:26:21'),
(88, 18, 'asus-dual-rtx-4060-oc-8gb-back.webp', 'Видеокарта ASUS Dual RTX 4060 OC, обратная сторона', 0, 1, '2026-11-10 20:26:21'),
(89, 19, 'asus-tuf-rtx-4070-super-oc-12gb.webp', 'Видеокарта ASUS TUF Gaming GeForce RTX 4070 SUPER OC, сторона вентиляторов', 1, 0, '2026-11-10 20:26:21'),
(90, 19, 'asus-tuf-rtx-4070-super-oc-12gb-back.webp', 'Видеокарта ASUS TUF Gaming RTX 4070 SUPER OC, обратная сторона', 0, 1, '2026-11-10 20:26:21'),
(91, 20, 'asus-tuf-b650-plus-wifi.webp', 'Материнская плата ASUS TUF Gaming B650-PLUS WIFI', 1, 0, '2026-11-10 20:26:21'),
(92, 20, 'asus-tuf-b650-plus-wifi-angle.webp', 'ASUS TUF Gaming B650-PLUS WIFI, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(93, 21, 'asus-tuf-b760-plus-wifi.webp', 'Материнская плата ASUS TUF Gaming B760-PLUS WIFI', 1, 0, '2026-11-10 20:26:21'),
(94, 21, 'asus-tuf-b760-plus-wifi-angle.webp', 'ASUS TUF Gaming B760-PLUS WIFI, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(95, 22, 'gskill-trident-z5-neo-rgb-32gb.webp', 'Комплект G.SKILL Trident Z5 Neo RGB 32GB DDR5', 1, 0, '2026-11-10 20:26:21'),
(96, 22, 'gskill-trident-z5-neo-rgb-32gb-angle.webp', 'Модули G.SKILL Trident Z5 Neo RGB, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(97, 23, 'corsair-vengeance-64gb-ddr5.webp', 'Два модуля Corsair Vengeance 64GB DDR5, чёрные радиаторы', 1, 0, '2026-11-10 20:26:21'),
(98, 23, 'corsair-vengeance-64gb-ddr5-angle.webp', 'Corsair Vengeance DDR5, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(99, 24, 'corsair-rm850x-850w.webp', 'Блок питания Corsair RM850x 850W серии 2021', 1, 0, '2026-11-10 20:26:21'),
(100, 24, 'corsair-rm850x-850w-angle.webp', 'Corsair RM850x, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(101, 25, 'corsair-4000d-airflow-black.webp', 'Чёрный корпус Corsair 4000D Airflow', 1, 0, '2026-11-10 20:26:21'),
(102, 25, 'corsair-4000d-airflow-black-angle.webp', 'Корпус Corsair 4000D Airflow, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(103, 26, 'deepcool-ak620.webp', 'Двухбашенный процессорный кулер DeepCool AK620', 1, 0, '2026-11-10 20:26:21'),
(104, 26, 'deepcool-ak620-angle.webp', 'Кулер DeepCool AK620, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(105, 27, 'arctic-liquid-freezer-iii-360.webp', 'Система охлаждения ARCTIC Liquid Freezer III 360 Black', 1, 0, '2026-11-10 20:26:21'),
(106, 27, 'arctic-liquid-freezer-iii-360-angle.webp', 'ARCTIC Liquid Freezer III 360, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(107, 28, 'corsair-h150i-elite-capellix-xt.webp', 'Corsair iCUE H150i Elite Capellix XT с радиатором 360 мм', 1, 0, '2026-11-10 20:26:21'),
(108, 28, 'corsair-h150i-elite-capellix-xt-angle.webp', 'Corsair H150i Elite Capellix XT, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(109, 29, 'corsair-hydro-x-xh305i-rgb-pro.webp', 'Комплект кастомного водяного охлаждения Corsair Hydro X XH305i RGB PRO', 1, 0, '2026-11-10 20:26:21'),
(110, 29, 'corsair-hydro-x-xh305i-rgb-pro-components.webp', 'Компоненты набора Corsair Hydro X XH305i RGB PRO', 0, 1, '2026-11-10 20:26:21'),
(111, 30, 'corsair-hydro-x-xh303i-rgb-pro.webp', 'Комплект кастомного водяного охлаждения Corsair Hydro X XH303i RGB PRO', 1, 0, '2026-11-10 20:26:21'),
(112, 30, 'corsair-hydro-x-xh303i-rgb-pro-components.webp', 'Компоненты набора Corsair Hydro X XH303i RGB PRO', 0, 1, '2026-11-10 20:26:21'),
(113, 31, 'corsair-hydro-x-xc7-rgb-pro.webp', 'Процессорный водоблок Corsair Hydro X XC7 RGB PRO Black', 1, 0, '2026-11-10 20:26:21'),
(114, 31, 'corsair-hydro-x-xc7-rgb-pro-angle.webp', 'Corsair Hydro X XC7 RGB PRO, дополнительный ракурс', 0, 1, '2026-11-10 20:26:21'),
(115, 32, 'corsair-hydro-x-xf-hardline-14mm.webp', 'Фитинги Corsair Hydro X XF Hardline для жёстких трубок 14 мм', 1, 0, '2026-11-10 20:26:21'),
(116, 33, 'corsair-hydro-x-xf-ball-valve.webp', 'Шаровой кран Corsair Hydro X XF Ball Valve Black', 1, 0, '2026-11-10 20:26:21');

-- --------------------------------------------------------

--
-- Структура таблицы `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `spec_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_specifications`
--

INSERT INTO `product_specifications` (`id`, `product_id`, `spec_name`, `spec_value`) VALUES
(703, 1, 'Количество ядер', '24 (8P+16E)'),
(704, 1, 'Количество потоков', '32'),
(705, 1, 'Базовая частота', '3.0 ГГц'),
(706, 1, 'Turbo частота', '5.8 ГГц'),
(707, 1, 'Кэш L3', '36 МБ'),
(708, 1, 'TDP', '125 Вт'),
(709, 1, 'Сокет', 'LGA1700'),
(710, 1, 'Поддержка памяти', 'DDR5-5600, DDR4-3200'),
(711, 1, 'Встроенная графика', 'Intel UHD Graphics 770'),
(712, 1, 'Техпроцесс', '10 нм'),
(713, 2, 'Количество ядер', '14 (6P+8E)'),
(714, 2, 'Количество потоков', '20'),
(715, 2, 'Базовая частота', '3.5 ГГц'),
(716, 2, 'Turbo частота', '5.1 ГГц'),
(717, 2, 'Кэш L3', '24 МБ'),
(718, 2, 'TDP', '125 Вт'),
(719, 2, 'Сокет', 'LGA1700'),
(720, 2, 'Поддержка памяти', 'DDR5-5600, DDR4-3200'),
(721, 2, 'Встроенная графика', 'Intel UHD Graphics 770'),
(722, 3, 'Количество ядер', '16'),
(723, 3, 'Количество потоков', '32'),
(724, 3, 'Базовая частота', '4.5 ГГц'),
(725, 3, 'Boost частота', '5.7 ГГц'),
(726, 3, 'Кэш L3', '64 МБ'),
(727, 3, 'TDP', '170 Вт'),
(728, 3, 'Сокет', 'AM5'),
(729, 3, 'Поддержка памяти', 'DDR5-5200'),
(730, 3, 'Встроенная графика', 'AMD Radeon Graphics'),
(731, 3, 'Техпроцесс', '5 нм'),
(732, 4, 'Объем памяти', '24 GB GDDR6X'),
(733, 4, 'Шина памяти', '384-bit'),
(734, 4, 'Частота GPU', '2235 / 2520 МГц'),
(735, 4, 'CUDA ядер', '16384'),
(736, 4, 'Интерфейс', 'PCI-E 4.0 x16'),
(737, 4, 'Разъемы', '3x DisplayPort 1.4a, 1x HDMI 2.1'),
(738, 4, 'Энергопотребление', '450 Вт'),
(739, 4, 'Рекомендуемый БП', '850 Вт'),
(740, 4, 'Охлаждение', '3 вентилятора'),
(741, 5, 'Объем памяти', '12 GB GDDR6X'),
(742, 5, 'Шина памяти', '192-bit'),
(743, 5, 'Частота GPU', '2310 / 2610 МГц'),
(744, 5, 'CUDA ядер', '7680'),
(745, 5, 'Интерфейс', 'PCI-E 4.0 x16'),
(746, 5, 'Разъемы', '3x DisplayPort 1.4a, 1x HDMI 2.1'),
(747, 5, 'Энергопотребление', '285 Вт'),
(748, 5, 'Рекомендуемый БП', '700 Вт'),
(749, 6, 'Объем памяти', '24 GB GDDR6'),
(750, 6, 'Шина памяти', '384-bit'),
(751, 6, 'Частота GPU', '2300 / 2500 МГц'),
(752, 6, 'Stream процессоров', '6144'),
(753, 6, 'Интерфейс', 'PCI-E 4.0 x16'),
(754, 6, 'Разъемы', '2x DisplayPort 2.1, 1x HDMI 2.1, 1x USB Type-C'),
(755, 6, 'Энергопотребление', '355 Вт'),
(756, 6, 'Рекомендуемый БП', '800 Вт'),
(757, 7, 'Чипсет', 'Intel Z790'),
(758, 7, 'Сокет', 'LGA1700'),
(759, 7, 'Форм-фактор', 'ATX'),
(760, 7, 'Память', 'DDR5, 4 слота, до 128 GB'),
(761, 7, 'Слоты расширения', '2x PCIe 5.0 x16, 1x PCIe 4.0 x16'),
(762, 7, 'M.2 слоты', '4x M.2 (PCIe 5.0/4.0)'),
(763, 7, 'SATA', '6 портов'),
(764, 7, 'USB', 'USB 3.2 Gen 2x2, Type-C, WiFi 6E'),
(765, 7, 'Сеть', '2.5G LAN'),
(766, 7, 'Питание', '18+1 фаз'),
(767, 8, 'Чипсет', 'Intel Z790'),
(768, 8, 'Сокет', 'LGA1700'),
(769, 8, 'Форм-фактор', 'ATX'),
(770, 8, 'Память', 'DDR5, 4 слота, до 128 GB'),
(771, 8, 'Слоты расширения', '2x PCIe 5.0 x16'),
(772, 8, 'M.2 слоты', '5x M.2 (PCIe 5.0/4.0)'),
(773, 8, 'SATA', '6 портов'),
(774, 8, 'USB', 'USB 3.2 Gen 2x2, WiFi 6E'),
(775, 8, 'Сеть', '2.5G + 10G LAN'),
(776, 8, 'Питание', '22+1+1 фаз'),
(777, 9, 'Чипсет', 'AMD X870'),
(778, 9, 'Сокет', 'AM5'),
(779, 9, 'Форм-фактор', 'ATX'),
(780, 9, 'Память', 'DDR5, 4 слота, до 128 GB'),
(781, 9, 'Слоты расширения', '2x PCIe 5.0 x16'),
(782, 9, 'M.2 слоты', '4x M.2 (PCIe 5.0/4.0)'),
(783, 9, 'SATA', '6 портов'),
(784, 9, 'USB', 'USB 4.0, WiFi 6E'),
(785, 9, 'Сеть', '2.5G LAN'),
(786, 9, 'Питание', '18+2 фазы'),
(787, 10, 'Тип', 'DDR4'),
(788, 10, 'Объем', '32 GB (2x16 GB)'),
(789, 10, 'Частота', '3600 МГц'),
(790, 10, 'Тайминги', 'CL18-22-22-42'),
(791, 10, 'Напряжение', '1.35 В'),
(792, 10, 'Подсветка', 'RGB'),
(793, 10, 'Радиатор', 'Алюминий'),
(794, 10, 'Профили', 'XMP 2.0'),
(795, 11, 'Тип', 'DDR5'),
(796, 11, 'Объем', '32 GB (2x16 GB)'),
(797, 11, 'Частота', '6000 МГц'),
(798, 11, 'Тайминги', 'CL36-38-38-80'),
(799, 11, 'Напряжение', '1.35 В'),
(800, 11, 'Радиатор', 'Алюминий'),
(801, 11, 'Профили', 'XMP 3.0, AMD EXPO'),
(802, 12, 'Интерфейс', 'M.2 NVMe PCIe 4.0 x4'),
(803, 12, 'Форм-фактор', 'M.2 2280'),
(804, 12, 'Емкость', '1 TB'),
(805, 12, 'Чтение', 'до 7450 МБ/с'),
(806, 12, 'Запись', 'до 6900 МБ/с'),
(807, 12, 'Тип памяти', 'Samsung V-NAND 3-bit MLC'),
(808, 12, 'Контроллер', 'Samsung Pascal'),
(809, 12, 'Ресурс записи', '600 TBW'),
(810, 12, 'Гарантия', '5 лет'),
(811, 13, 'Интерфейс', 'SATA III 6 Гб/с'),
(812, 13, 'Форм-фактор', '2.5\"'),
(813, 13, 'Емкость', '1 TB'),
(814, 13, 'Чтение', 'до 560 МБ/с'),
(815, 13, 'Запись', 'до 530 МБ/с'),
(816, 13, 'Тип памяти', 'Samsung V-NAND 3-bit MLC'),
(817, 13, 'Контроллер', 'Samsung MKX'),
(818, 13, 'Ресурс записи', '600 TBW'),
(819, 13, 'Гарантия', '5 лет'),
(823, 14, 'Встроенная графика', 'Нет'),
(824, 14, 'Количество потоков', '32'),
(825, 14, 'Количество ядер', '16'),
(826, 15, 'Сокет', 'AM5'),
(827, 15, 'Количество ядер', '6'),
(828, 15, 'Количество потоков', '12'),
(829, 15, 'Базовая частота', '3,8 ГГц'),
(830, 15, 'Максимальная частота', '5,1 ГГц'),
(831, 15, 'Кэш L3', '32 МБ'),
(832, 15, 'Тип памяти', 'DDR5'),
(833, 15, 'TDP', '65 Вт'),
(834, 15, 'Комплектация', 'BOX, Wraith Stealth'),
(835, 16, 'Сокет', 'AM5'),
(836, 16, 'Количество ядер', '8'),
(837, 16, 'Количество потоков', '16'),
(838, 16, 'Базовая частота', '4,2 ГГц'),
(839, 16, 'Максимальная частота', '5,0 ГГц'),
(840, 16, 'Кэш L3', '96 МБ'),
(841, 16, 'Тип памяти', 'DDR5'),
(842, 16, 'TDP', '120 Вт'),
(843, 16, 'Комплектация', 'BOX, без кулера'),
(844, 17, 'Сокет', 'LGA1700'),
(845, 17, 'Количество ядер', '20 (8P + 12E)'),
(846, 17, 'Количество потоков', '28'),
(847, 17, 'Базовая частота P-ядер', '3,4 ГГц'),
(848, 17, 'Максимальная частота', '5,6 ГГц'),
(849, 17, 'Кэш L3', '33 МБ'),
(850, 17, 'Тип памяти', 'DDR4 / DDR5'),
(851, 17, 'Базовая мощность', '125 Вт'),
(852, 17, 'Максимальная турбомощность', '253 Вт'),
(853, 17, 'Встроенная графика', 'Intel UHD Graphics 770'),
(854, 18, 'Графический процессор', 'GeForce RTX 4060'),
(855, 18, 'Видеопамять', '8 ГБ'),
(856, 18, 'Тип памяти', 'GDDR6'),
(857, 18, 'Шина памяти', '128 бит'),
(858, 18, 'CUDA-ядра', '3072'),
(859, 18, 'Интерфейс', 'PCI Express 4.0 x8'),
(860, 18, 'Вентиляторы', '2'),
(861, 18, 'Дополнительное питание', '1 × 8-pin'),
(862, 18, 'Видеовыходы', '3 × DisplayPort 1.4a, 1 × HDMI 2.1a'),
(863, 19, 'Графический процессор', 'GeForce RTX 4070 SUPER'),
(864, 19, 'Видеопамять', '12 ГБ'),
(865, 19, 'Тип памяти', 'GDDR6X'),
(866, 19, 'Шина памяти', '192 бита'),
(867, 19, 'CUDA-ядра', '7168'),
(868, 19, 'Интерфейс', 'PCI Express 4.0 x16'),
(869, 19, 'Вентиляторы', '3'),
(870, 19, 'Дополнительное питание', '1 × 16-pin'),
(871, 19, 'Видеовыходы', '3 × DisplayPort 1.4a, 1 × HDMI 2.1a'),
(872, 20, 'Сокет', 'AM5'),
(873, 20, 'Чипсет', 'AMD B650'),
(874, 20, 'Форм-фактор', 'ATX'),
(875, 20, 'Тип памяти', 'DDR5'),
(876, 20, 'Слоты памяти', '4'),
(877, 20, 'Разъёмы M.2', '3'),
(878, 20, 'Порты SATA', '4'),
(879, 20, 'Сеть', '2,5 Гбит/с'),
(880, 20, 'Беспроводная сеть', 'Wi-Fi 6'),
(881, 20, 'Bluetooth', '5.2'),
(882, 21, 'Сокет', 'LGA1700'),
(883, 21, 'Чипсет', 'Intel B760'),
(884, 21, 'Форм-фактор', 'ATX'),
(885, 21, 'Тип памяти', 'DDR5'),
(886, 21, 'Слоты памяти', '4'),
(887, 21, 'Разъёмы M.2', '3'),
(888, 21, 'Порты SATA', '4'),
(889, 21, 'Сеть', '2,5 Гбит/с'),
(890, 21, 'Беспроводная сеть', 'Wi-Fi 6'),
(891, 21, 'Bluetooth', '5.2'),
(892, 22, 'Тип памяти', 'DDR5'),
(893, 22, 'Объём', '32 ГБ'),
(894, 22, 'Комплект', '2 × 16 ГБ'),
(895, 22, 'Скорость', '6000 MT/s'),
(896, 22, 'CAS Latency', 'CL36'),
(897, 22, 'Тайминги', '36-36-36-96'),
(898, 22, 'Напряжение', '1,35 В'),
(899, 22, 'Профиль разгона', 'AMD EXPO'),
(900, 22, 'Подсветка', 'RGB'),
(901, 22, 'Форм-фактор', 'DIMM'),
(902, 23, 'Тип памяти', 'DDR5'),
(903, 23, 'Объём', '64 ГБ'),
(904, 23, 'Комплект', '2 × 32 ГБ'),
(905, 23, 'Скорость', '6000 MT/s'),
(906, 23, 'CAS Latency', 'CL30'),
(907, 23, 'Профиль разгона', 'Intel XMP 3.0'),
(908, 23, 'Подсветка', 'Нет'),
(909, 23, 'Цвет', 'Чёрный'),
(910, 23, 'Форм-фактор', 'DIMM'),
(911, 24, 'Мощность', '850 Вт'),
(912, 24, 'Сертификация', '80 PLUS Gold'),
(913, 24, 'Стандарт', 'ATX 2.4'),
(914, 24, 'Кабели', 'Полностью модульные'),
(915, 24, 'Вентилятор', '135 мм'),
(916, 24, 'Режим Zero RPM', 'Да'),
(917, 24, 'Размеры', '160 × 150 × 86 мм'),
(918, 24, 'Цвет', 'Чёрный'),
(919, 25, 'Тип корпуса', 'Mid-Tower'),
(920, 25, 'Материнские платы', 'ATX / Micro-ATX / Mini-ITX'),
(921, 25, 'Материал боковой панели', 'Закалённое стекло'),
(922, 25, 'Вентиляторы в комплекте', '2 × 120 мм'),
(923, 25, 'Максимальная длина видеокарты', '360 мм'),
(924, 25, 'Максимальная высота кулера', '170 мм'),
(925, 25, 'Передний радиатор', 'До 360 мм'),
(926, 25, 'Размеры', '453 × 230 × 466 мм'),
(927, 25, 'Цвет', 'Чёрный'),
(928, 25, 'Блок питания в комплекте', 'Нет'),
(929, 26, 'Тип охлаждения', 'Воздушное, двухбашенное'),
(930, 26, 'Сокеты', 'LGA1700 / LGA1200 / LGA115x / AM5 / AM4'),
(931, 26, 'Тепловые трубки', '6'),
(932, 26, 'Вентиляторы', '2 × 120 мм'),
(933, 26, 'Скорость вентиляторов', '500–1850 об/мин'),
(934, 26, 'Управление', 'PWM, 4-pin'),
(935, 26, 'Высота', '160 мм'),
(936, 26, 'Размеры', '129 × 138 × 160 мм'),
(937, 26, 'Подсветка', 'Нет'),
(938, 27, 'Тип охлаждения', 'Готовая СЖО (AIO)'),
(939, 27, 'Радиатор', '360 мм'),
(940, 27, 'Размеры радиатора', '398 × 120 × 38 мм'),
(941, 27, 'Сокеты', 'LGA1700 / AM5 / AM4'),
(942, 27, 'Вентиляторы', '3 × 120 мм, P12 PWM PST'),
(943, 27, 'Скорость вентиляторов', '200–1800 об/мин'),
(944, 27, 'Вентилятор VRM', 'Да'),
(945, 27, 'Длина шлангов', '450 мм'),
(946, 27, 'Подсветка', 'Нет'),
(947, 28, 'Тип охлаждения', 'Готовая СЖО (AIO)'),
(948, 28, 'Радиатор', '360 мм'),
(949, 28, 'Размеры радиатора', '397 × 120 × 27 мм'),
(950, 28, 'Сокеты', 'LGA1700 / LGA1200 / LGA115x / AM5 / AM4'),
(951, 28, 'Вентиляторы', '3 × 120 мм, AF120 RGB Elite'),
(952, 28, 'Скорость вентиляторов', '550–2100 об/мин'),
(953, 28, 'Длина шлангов', '450 мм'),
(954, 28, 'Подсветка', 'RGB, Capellix'),
(955, 28, 'Контроллер', 'iCUE Commander Core'),
(956, 29, 'Тип охлаждения', 'Кастомная СЖО, набор'),
(957, 29, 'Водоблок CPU', 'XC7 RGB PRO'),
(958, 29, 'Помпа и резервуар', 'XD5 RGB, D5'),
(959, 29, 'Радиатор', 'XR5 360, медный'),
(960, 29, 'Вентиляторы', '3 × QL120 RGB, 120 мм'),
(961, 29, 'Трубки', 'XT Hardline, 14 мм'),
(962, 29, 'Резьба фитингов', 'G1/4'),
(963, 29, 'Сокеты', 'LGA1700 / LGA1200 / AM5 / AM4'),
(964, 29, 'Подсветка', 'RGB, iCUE'),
(965, 29, 'Охлаждение видеокарты', 'Водоблок приобретается отдельно'),
(966, 29, 'Сборка и обслуживание', 'Требуются'),
(967, 30, 'Тип охлаждения', 'Кастомная СЖО, набор'),
(968, 30, 'Водоблок CPU', 'XC7 RGB PRO'),
(969, 30, 'Помпа и резервуар', 'XD3 RGB, DDC'),
(970, 30, 'Радиатор', 'XR5 360, медный'),
(971, 30, 'Вентиляторы', '3 × SP120 RGB Elite, 120 мм'),
(972, 30, 'Трубки', 'XT Hardline, 14 мм'),
(973, 30, 'Резьба фитингов', 'G1/4'),
(974, 30, 'Сокеты', 'LGA1700 / LGA1200 / AM5 / AM4'),
(975, 30, 'Подсветка', 'RGB, iCUE'),
(976, 30, 'Охлаждение видеокарты', 'Водоблок приобретается отдельно'),
(977, 30, 'Сборка и обслуживание', 'Требуются'),
(978, 31, 'Тип охлаждения', 'CPU-водоблок для кастомной СЖО'),
(979, 31, 'Сокеты', 'LGA1700 / LGA1200 / AM5 / AM4'),
(980, 31, 'Материал основания', 'Никелированная медь'),
(981, 31, 'Резьба портов', 'G1/4'),
(982, 31, 'Подсветка', 'RGB, 16 светодиодов'),
(983, 31, 'Цвет', 'Чёрный'),
(984, 31, 'Помпа и радиатор в комплекте', 'Нет'),
(985, 31, 'Самостоятельная работа', 'Только в составе водяного контура'),
(986, 32, 'Тип', 'Фитинг для жёстких трубок'),
(987, 32, 'Диаметр трубок', '14 мм (внешний)'),
(988, 32, 'Резьба', 'G1/4'),
(989, 32, 'Количество в комплекте', '4 шт.'),
(990, 32, 'Цвет', 'Чёрный'),
(991, 32, 'Назначение', 'Кастомный водяной контур'),
(992, 33, 'Тип', 'Шаровой кран для СЖО'),
(993, 33, 'Резьба портов', 'G1/4'),
(994, 33, 'Назначение', 'Слив и обслуживание контура'),
(995, 33, 'Цвет', 'Чёрный'),
(996, 33, 'Фитинги в комплекте', 'Нет');

-- --------------------------------------------------------

--
-- Структура таблицы `product_views`
--

CREATE TABLE `product_views` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `viewed_at` datetime(6) NOT NULL COMMENT 'Время последнего просмотра в UTC'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `product_views`
--

INSERT INTO `product_views` (`id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 2, 33, '2026-11-10 21:38:38.399072'),
(2, 2, 29, '2026-11-10 21:38:46.160912'),
(3, 2, 4, '2026-11-10 21:38:53.911502');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `parent_id`, `rating`, `comment`, `is_approved`, `created_at`, `updated_at`) VALUES
(49, 1, 1, NULL, 5, 'Отличный процессор! Справляется со всеми задачами на ура. Рендер в 3D Max летает.', 1, '2026-10-25 15:59:16', NULL),
(50, 1, 2, NULL, 5, 'Разогнал до 5.9 ГГц на всех ядрах. Температуры в норме с хорошим кулером.', 1, '2026-10-25 15:59:16', NULL),
(51, 4, 1, NULL, 5, 'Лучшая видеокарта на рынке! 4K гейминг с трассировкой лучей - просто восторг!', 1, '2026-10-25 15:59:16', NULL),
(52, 4, 2, NULL, 5, 'Дорого, но оно того стоит. Все игры на максималках идут плавно.', 1, '2026-10-25 15:59:16', NULL),
(53, 7, 1, NULL, 5, 'Качественная плата с отличной системой питания. Разгон процессора идет стабильно.', 1, '2026-10-25 15:59:16', NULL),
(54, 12, 2, NULL, 5, 'Самый быстрый SSD! Загрузка Windows за 5 секунд.', 1, '2026-10-25 15:59:16', NULL),
(55, 2, 1, NULL, 4, 'Хороший процессор для своей цены. Игры идут отлично.', 1, '2026-10-25 15:59:16', NULL),
(56, 5, 2, NULL, 5, 'Отличная видеокарта для 1440p. Тихая и холодная.', 1, '2026-10-25 15:59:16', NULL),
(57, 12, 3, NULL, 4, 'не', 1, '2026-11-04 21:08:26', '2026-11-04 21:23:28'),
(58, 11, 3, NULL, 5, 'yt', 1, '2026-11-04 21:24:39', NULL),
(59, 11, 2, 58, 5, 'не', 1, '2026-11-04 21:45:19', NULL),
(60, 14, 1, NULL, 1, '=kal', 1, '2026-11-09 18:58:16', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `seo_metrics`
--

CREATE TABLE `seo_metrics` (
  `id` int NOT NULL,
  `metric_date` date DEFAULT NULL,
  `metric_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metric_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `seo_metrics`
--

INSERT INTO `seo_metrics` (`id`, `metric_date`, `metric_name`, `metric_value`, `category`, `notes`, `created_at`) VALUES
(1, '2026-10-25', 'indexed_pages', '0', 'indexation', 'Количество проиндексированных страниц в Google', '2026-11-10 18:59:47'),
(2, '2026-10-25', 'indexed_pages_yandex', '0', 'indexation', 'Количество проиндексированных страниц в Яндексе', '2026-11-10 18:59:47'),
(3, '2026-10-25', 'avg_position', 'N/A', 'rankings', 'Средняя позиция по целевым запросам', '2026-11-10 18:59:47'),
(4, '2026-10-25', 'organic_traffic', '0', 'traffic', 'Органический трафик за месяц', '2026-11-10 18:59:47'),
(5, '2026-10-25', 'conversions', '0', 'conversions', 'Количество заказов из органического трафика', '2026-11-10 18:59:47'),
(6, '2026-10-25', 'bounce_rate', '0%', 'ux', 'Процент отскоков', '2026-11-10 18:59:47'),
(7, '2026-10-25', 'avg_session_duration', '0m', 'ux', 'Средняя длительность сессии', '2026-11-10 18:59:47'),
(8, '2026-10-25', 'page_load_speed', 'N/A', 'performance', 'Скорость загрузки страниц', '2026-11-10 18:59:47'),
(9, '2026-10-25', 'mobile_score', 'N/A', 'performance', 'Мобильная оценка PageSpeed', '2026-11-10 18:59:47'),
(10, '2026-10-25', 'desktop_score', 'N/A', 'performance', 'Desktop оценка PageSpeed', '2026-11-10 18:59:47');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','manager','client') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'client',
  `status` enum('active','blocked','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@pccomponents.local', '$2y$10$adqdp4dT/9.gdAOqBHNVGumnmdr/zYKvhPtZ1TCjRfE8UUzX/N.xi', 'Администратор', '+7 (343) 123-45-67', 'admin', 'active', '2026-10-25 11:59:18', '2026-10-25 16:18:32'),
(2, 'client', 'client@example.local', '$2y$10$adqdp4dT/9.gdAOqBHNVGumnmdr/zYKvhPtZ1TCjRfE8UUzX/N.xi', 'Иван Петров', '+7 (900) 123-45-67', 'client', 'active', '2026-10-25 11:59:18', '2026-10-25 16:18:30'),
(3, 'manager', 'manager@pccomponents.local', '$2y$10$adqdp4dT/9.gdAOqBHNVGumnmdr/zYKvhPtZ1TCjRfE8UUzX/N.xi', 'Менеджер', '+7 (343) 987-65-43', 'manager', 'active', '2026-10-25 11:59:18', '2026-10-25 16:18:26'),
(4, 'user2026', 'user2026@test.com', '$2y$10$ifTnkICPWIk6AuulUKoqkubWCAIDvANUhs4k457FR1jQc.iohWth2', '', '', 'client', 'active', '2026-10-25 12:40:31', '2026-10-25 12:40:31'),
(5, 'demo', 'demo@test.com', '$2y$10$adqdp4dT/9.gdAOqBHNVGumnmdr/zYKvhPtZ1TCjRfE8UUzX/N.xi', '', '', 'client', 'active', '2026-10-25 12:41:11', '2026-10-25 12:41:11');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `audit_issues`
--
ALTER TABLE `audit_issues`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `backlinks`
--
ALTER TABLE `backlinks`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Индексы таблицы `content_plan`
--
ALTER TABLE `content_plan`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `keyword_rankings`
--
ALTER TABLE `keyword_rankings`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_orders_user_created` (`user_id`,`created_at`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_brand` (`brand`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_products_price` (`price`),
  ADD KEY `idx_products_created` (`created_at`),
  ADD KEY `idx_products_popularity` (`is_active`,`orders_count`,`id`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_search` (`name`,`description`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_view` (`user_id`,`product_id`),
  ADD KEY `idx_product_views_recent` (`user_id`,`viewed_at`,`id`),
  ADD KEY `idx_product_views_product` (`product_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Индексы таблицы `seo_metrics`
--
ALTER TABLE `seo_metrics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `audit_issues`
--
ALTER TABLE `audit_issues`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `backlinks`
--
ALTER TABLE `backlinks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `content_plan`
--
ALTER TABLE `content_plan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `keyword_rankings`
--
ALTER TABLE `keyword_rankings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT для таблицы `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=997;

--
-- AUTO_INCREMENT для таблицы `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT для таблицы `seo_metrics`
--
ALTER TABLE `seo_metrics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD CONSTRAINT `delivery_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `product_specifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_views`
--
ALTER TABLE `product_views`
  ADD CONSTRAINT `product_views_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_views_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
