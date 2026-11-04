-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 04 2026 г., 20:19
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
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `added_at`) VALUES
(1, 1, 'r13eqgejtal45ck3pfk609rjdldn9dms', 12, 1, '2026-11-01 17:00:09'),
(12, NULL, 'ut753u8lv8thg7iog1g5tq6atoid0ioi', 12, 1, '2026-11-01 17:36:21'),
(17, 2, '9njdbts2e4ogjdm57vnv1ahnnh3d6pfv', 14, 2, '2026-11-01 19:14:24'),
(19, 2, '9njdbts2e4ogjdm57vnv1ahnnh3d6pfv', 1, 1, '2026-11-01 19:17:10'),
(20, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 12, 1, '2026-11-04 17:05:50'),
(21, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 11, 1, '2026-11-04 17:05:52'),
(22, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 10, 1, '2026-11-04 17:05:53'),
(23, NULL, 'f0a2un2c9o6l6pn68sf37604llqrbfo5', 9, 1, '2026-11-04 17:05:53');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `icon_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `meta_title`, `meta_description`, `parent_id`, `icon_path`, `created_at`, `updated_at`) VALUES
(1, 'Процессоры', 'processors', 'Процессоры Intel и AMD для компьютеров', 'Процессоры - купить CPU Intel и AMD', 'Широкий выбор процессоров Intel Core и AMD Ryzen. Низкие цены, гарантия качества.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(2, 'Видеокарты', 'graphics-cards', 'Видеокарты NVIDIA и AMD для игр и работы', 'Видеокарты - купить GPU NVIDIA и AMD', 'Мощные видеокарты NVIDIA GeForce и AMD Radeon. Быстрая доставка.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(3, 'Материнские платы', 'motherboards', 'Материнские платы для Intel и AMD', 'Материнские платы - купить для Intel и AMD', 'Качественные материнские платы ASUS, MSI, Gigabyte. Гарантия производителя.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(4, 'Оперативная память', 'ram', 'Оперативная память DDR4 и DDR5', 'Оперативная память - купить RAM DDR4/DDR5', 'Быстрая оперативная память от ведущих производителей. Доступные цены.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(5, 'Накопители', 'storage', 'SSD и HDD накопители для компьютера', 'Накопители - купить SSD и HDD', 'Надежные SSD M.2 NVMe и SATA накопители. Большой выбор объемов.', NULL, NULL, '2026-10-25 15:59:16', '2026-10-25 15:59:16');

-- --------------------------------------------------------

--
-- Структура таблицы `delivery_addresses`
--

CREATE TABLE `delivery_addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','assembled','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `delivery_method` enum('pickup','courier','mail') COLLATE utf8mb4_unicode_ci DEFAULT 'courier',
  `payment_method` enum('card','cash','online') COLLATE utf8mb4_unicode_ci DEFAULT 'card',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `total_amount`, `delivery_address`, `delivery_method`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-20261101-E5B6C1', 'pending', '235280.00', 'ывфаыва, аывааыв, ываываыв', 'courier', 'card', NULL, '2026-11-01 18:56:58', '2026-11-01 18:56:58'),
(2, 2, 'ORD-20261101-03B3EC', 'pending', '64260.00', 'фывфыв, ыфвфыв, фывфыв', 'courier', 'card', NULL, '2026-11-01 19:07:40', '2026-11-01 19:07:40'),
(3, 2, 'ORD-20261101-A52476', 'pending', '140279.00', 'ываываыав, ывавыаыав, ываываыва', 'courier', 'card', NULL, '2026-11-01 19:11:03', '2026-11-01 19:11:03');

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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `detailed_description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `views` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `detailed_description`, `price`, `old_price`, `stock`, `brand`, `sku`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 'Intel Core i9-13900K', 'intel-core-i9-13900k', 'Флагманский 24-ядерный процессор Intel 13-го поколения', 'Intel Core i9-13900K - это мощнейший процессор для энтузиастов и профессионалов. 24 ядра (8P+16E), 32 потока, тактовая частота до 5.8 ГГц в режиме Turbo Boost Max 3.0. Поддержка DDR5-5600 и PCIe 5.0. Идеален для игр в 4K, стриминга, 3D-рендеринга и профессиональных задач.', '55990.00', '64990.00', 15, 'Intel', 'INTEL-I9-13900K', '', '', '', 1, 1, 0, '4.80', '2026-10-25 15:59:16', '2026-10-25 17:29:29'),
(2, 1, 'Intel Core i5-13600K', 'intel-core-i5-13600k', '14-ядерный процессор Intel для игр и работы', 'Intel Core i5-13600K - оптимальное соотношение цена/производительность. 14 ядер (6P+8E), 20 потоков, частота до 5.1 ГГц. Отличный выбор для игровых систем среднего класса и многозадачности. Поддержка DDR5 и PCIe 5.0.', '29990.00', '34990.00', 25, 'Intel', 'INTEL-I5-13600K', NULL, NULL, NULL, 1, 1, 0, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(3, 1, 'AMD Ryzen 9 7950X', 'amd-ryzen-9-7950x', '16-ядерный процессор AMD на архитектуре Zen 4', 'AMD Ryzen 9 7950X - топовый процессор на новейшей архитектуре Zen 4. 16 ядер, 32 потока, частота до 5.7 ГГц. Технология AMD 3D V-Cache обеспечивает невероятную производительность в играх и рабочих приложениях. Поддержка DDR5 и PCIe 5.0.', '52990.00', '59990.00', 18, 'AMD', 'AMD-R9-7950X', NULL, NULL, NULL, 1, 1, 0, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(4, 2, 'NVIDIA GeForce RTX 4090', 'nvidia-geforce-rtx-4090', 'Флагманская видеокарта NVIDIA для 4K гейминга', 'NVIDIA GeForce RTX 4090 - абсолютный лидер в мире видеокарт. 24 GB GDDR6X памяти, архитектура Ada Lovelace, поддержка Ray Tracing 3-го поколения и DLSS 3. Идеальна для игр в 4K с максимальными настройками, 3D-моделирования и работы с ИИ. Частота GPU до 2.52 ГГц.', '159990.00', '179990.00', 8, 'NVIDIA', 'RTX-4090-24GB', NULL, NULL, NULL, 1, 1, 0, '5.00', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(5, 2, 'NVIDIA GeForce RTX 4070 Ti', 'nvidia-geforce-rtx-4070-ti', 'Мощная видеокарта для 1440p и 4K гейминга', 'NVIDIA GeForce RTX 4070 Ti - отличный выбор для высокопроизводительного гейминга. 12 GB GDDR6X памяти, поддержка DLSS 3 и Ray Tracing. Обеспечивает стабильные 60+ FPS в играх AAA в разрешении 1440p и 4K. Энергоэффективная архитектура Ada Lovelace.', '74990.00', '84990.00', 20, 'NVIDIA', 'RTX-4070TI-12GB', NULL, NULL, NULL, 1, 1, 0, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(6, 2, 'AMD Radeon RX 7900 XTX', 'amd-radeon-rx-7900-xtx', 'Топовая видеокарта AMD с 24 GB памяти', 'AMD Radeon RX 7900 XTX - флагман AMD на архитектуре RDNA 3. 24 GB GDDR6 памяти, поддержка FSR 3 и Ray Tracing. Превосходная производительность в играх 4K, майнинге и работе с графикой. Частота GPU до 2.5 ГГц, 384-bit шина памяти.', '89990.00', '99990.00', 12, 'AMD', 'RX-7900XTX-24GB', NULL, NULL, NULL, 1, 1, 0, '4.60', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(7, 3, 'ASUS ROG STRIX Z790-E', 'asus-rog-strix-z790-e', 'Премиальная материнская плата для Intel 13-го поколения', 'ASUS ROG STRIX Z790-E Gaming WiFi - топовая плата на чипсете Z790. Поддержка процессоров Intel 12-го и 13-го поколений, DDR5-7800+(OC), PCIe 5.0 x16, 4x M.2 слота. WiFi 6E, 2.5G LAN, USB 3.2 Gen 2x2. Продвинутая система питания 18+1 фаз для разгона. RGB подсветка Aura Sync.', '39990.00', '44990.00', 10, 'ASUS', 'ASUS-Z790-E-GAMING', NULL, NULL, NULL, 1, 1, 0, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(8, 3, 'MSI MEG Z790 ACE', 'msi-meg-z790-ace', 'Флагманская материнская плата MSI для энтузиастов', 'MSI MEG Z790 ACE - премиум плата для экстремального разгона. Поддержка Intel 13-го поколения, DDR5-7200+(OC), 5x M.2 слотов с радиаторами, PCIe 5.0. Система питания 22+1+1 фаз. WiFi 6E, 2.5G + 10G LAN. Поддержка MSI Mystic Light RGB.', '44990.00', NULL, 8, 'MSI', 'MSI-Z790-ACE', NULL, NULL, NULL, 0, 1, 0, '4.80', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(9, 3, 'AMD ROG STRIX X870-E', 'amd-rog-strix-x870-e', 'Топовая материнская плата для AMD Ryzen 7000', 'ASUS ROG STRIX X870-E Gaming - премиальное решение для AMD Ryzen 7000. Чипсет X870, поддержка DDR5-6400+(OC), PCIe 5.0 для GPU и NVMe, 4x M.2 слота. WiFi 6E, 2.5G LAN, USB 4.0. Система питания 18+2 фаз. RGB подсветка Aura Sync.', '38990.00', '42990.00', 12, 'ASUS', 'ASUS-X870-E-GAMING', NULL, NULL, NULL, 1, 1, 0, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(10, 4, 'Corsair Vengeance RGB PRO 32GB DDR4', 'corsair-vengeance-rgb-pro-32gb-ddr4', 'Высокопроизводительная память с RGB подсветкой', 'Corsair Vengeance RGB PRO 32GB (2x16GB) DDR4-3600 CL18 - идеальный выбор для игровых систем. Яркая RGB подсветка с синхронизацией через iCUE, алюминиевый радиатор для эффективного охлаждения. XMP 2.0 профили для легкого разгона. Совместимость с Intel и AMD платформами.', '12990.00', '14990.00', 30, 'Corsair', 'CORSAIR-VEN-RGB-32GB', NULL, NULL, NULL, 1, 1, 0, '4.60', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(11, 4, 'Kingston Fury Beast 32GB DDR5', 'kingston-fury-beast-32gb-ddr5', 'Современная DDR5 память для новых платформ', 'Kingston Fury Beast 32GB (2x16GB) DDR5-6000 CL36 - передовая память для Intel 13-го поколения и AMD Ryzen 7000. Встроенное управление питанием, поддержка Intel XMP 3.0 и AMD EXPO. Низкие тайминги и высокая частота для максимальной производительности.', '15990.00', NULL, 25, 'Kingston', 'KINGSTON-BEAST-DDR5-32GB', NULL, NULL, NULL, 1, 1, 0, '4.50', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(12, 5, 'Samsung 990 PRO 1TB M.2 NVMe', 'samsung-990-pro-1tb-m2-nvme', 'Сверхбыстрый SSD PCIe 4.0 от Samsung', 'Samsung 990 PRO 1TB M.2 NVMe - топовый накопитель с PCIe 4.0 x4. Скорость чтения до 7450 МБ/с, записи до 6900 МБ/с. Технология Samsung V-NAND 3-bit MLC, контроллер Samsung Pascal. Идеален для игр, работы с видео и профессиональных задач. Гарантия 5 лет.', '11990.00', '13990.00', 40, 'Samsung', 'SAMSUNG-990PRO-1TB', NULL, NULL, NULL, 1, 1, 0, '4.90', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(13, 5, 'Samsung 870 EVO 1TB SSD', 'samsung-870-evo-1tb-ssd', 'Надежный SATA SSD для апгрейда системы', 'Samsung 870 EVO 1TB 2.5\" SATA III - проверенное решение для модернизации ПК или ноутбука. Скорость чтения/записи до 560/530 МБ/с. Технология Samsung V-NAND 3-bit MLC обеспечивает надежность и долговечность. TurboWrite для ускорения операций записи. Гарантия 5 лет.', '8990.00', '9990.00', 50, 'Samsung', 'SAMSUNG-870EVO-1TB', NULL, NULL, NULL, 0, 1, 0, '4.70', '2026-10-25 15:59:16', '2026-10-25 15:59:16'),
(14, 1, 'Intel Core i9', 'intel-core-i9', '', NULL, '67999.00', '79999.00', 9, 'Intel', '', '', '', '', 0, 1, 0, NULL, '2026-11-01 16:42:30', '2026-11-01 16:42:30');

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(78, 13, 'Samsung 870 EVO 1TB SSD.jpg', 'Samsung 870 EVO 1TB SSD', 1, 1, '2026-10-25 15:59:16');

-- --------------------------------------------------------

--
-- Структура таблицы `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `spec_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
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
(819, 13, 'Гарантия', '5 лет');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `is_approved`, `created_at`) VALUES
(49, 1, 1, 5, 'Отличный процессор! Справляется со всеми задачами на ура. Рендер в 3D Max летает.', 1, '2026-10-25 15:59:16'),
(50, 1, 2, 5, 'Разогнал до 5.9 ГГц на всех ядрах. Температуры в норме с хорошим кулером.', 1, '2026-10-25 15:59:16'),
(51, 4, 1, 5, 'Лучшая видеокарта на рынке! 4K гейминг с трассировкой лучей - просто восторг!', 1, '2026-10-25 15:59:16'),
(52, 4, 2, 5, 'Дорого, но оно того стоит. Все игры на максималках идут плавно.', 1, '2026-10-25 15:59:16'),
(53, 7, 1, 5, 'Качественная плата с отличной системой питания. Разгон процессора идет стабильно.', 1, '2026-10-25 15:59:16'),
(54, 12, 2, 5, 'Самый быстрый SSD! Загрузка Windows за 5 секунд.', 1, '2026-10-25 15:59:16'),
(55, 2, 1, 4, 'Хороший процессор для своей цены. Игры идут отлично.', 1, '2026-10-25 15:59:16'),
(56, 5, 2, 5, 'Отличная видеокарта для 1440p. Тихая и холодная.', 1, '2026-10-25 15:59:16');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','manager','client') COLLATE utf8mb4_unicode_ci DEFAULT 'client',
  `status` enum('active','blocked','deleted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
-- Индексы таблицы `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `idx_products_created` (`created_at`);
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
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT для таблицы `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=820;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
