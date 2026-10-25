-- Вставка тестовых данных

-- Категории
INSERT INTO categories (name, slug, description, meta_title, meta_description) VALUES
('Процессоры', 'processors', 'Процессоры для ПК от Intel и AMD', 'Купить процессор - PCComponents Store', 'Процессоры для персональных компьютеров'),
('Видеокарты', 'video-cards', 'Видеокарты NVIDIA и AMD для игр и работы', 'Видеокарты - PCComponents Store', 'Купить видеокарту по лучшей цене'),
('Материнские платы', 'motherboards', 'Материнские платы LGA1700 и AM5', 'Материнские платы - PCComponents Store', 'Выбрать материнскую плату'),
('Оперативная память', 'memory', 'DDR4 и DDR5 оперативная память', 'ОЗУ для ПК - PCComponents Store', 'Купить оперативную память'),
('Накопители', 'storage', 'SSD и HDD накопители для ПК', 'Жесткие диски и SSD - PCComponents Store', 'Выбрать накопитель для компьютера');

-- Пользователи (администратор и тестовый клиент)
-- Пароли: admin/admin, client/client, manager/manager
INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES
('admin', 'admin@pccomponents.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор', '+7 (343) 123-45-67', 'admin', 'active'),
('client', 'client@example.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Иван Петров', '+7 (900) 123-45-67', 'client', 'active'),
('manager', 'manager@pccomponents.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Менеджер', '+7 (343) 987-65-43', 'manager', 'active');

-- Товары: Процессоры
INSERT INTO products (category_id, name, slug, description, detailed_description, price, brand, stock, sku, meta_title, meta_description, is_featured, is_active) VALUES
(1, 'Intel Core i9-13900K', 'intel-core-i9-13900k', 'Процессор Intel последнего поколения для высокопроизводительных систем', 'Intel Core i9-13900K - один из самых мощных процессоров для персональных компьютеров. Оснащен 24 ядрами (8 P-cores + 16 E-cores) и поддерживает технологию Hyper-Threading. Идеален для творческих профессионалов и геймеров.', 69990.00, 'Intel', 5, 'BX8071513900K', 'Intel Core i9-13900K купить', 'Мощный процессор Intel для игр и работы', 1, 1),
(1, 'AMD Ryzen 9 7950X', 'amd-ryzen-9-7950x', 'Процессор AMD Ryzen 9 с 16 ядрами', 'AMD Ryzen 9 7950X - высокопроизводительный процессор с 16 ядрами и 32 потоками. Отлично подходит для многопоточных приложений, видеомонтажа и компьютерных игр.', 64990.00, 'AMD', 8, 'AMD-RYZEN-9-7950X', 'AMD Ryzen 9 7950X цена', 'Процессор AMD для профессиональной работы', 1, 1),
(1, 'Intel Core i5-13600K', 'intel-core-i5-13600k', 'Средний класс процессор Intel для игр', 'Intel Core i5-13600K - универсальный процессор для любителей игр и работы. Содержит 14 ядер и хорошо подходит для большинства приложений.', 34990.00, 'Intel', 12, 'BX8071513600K', 'Intel Core i5-13600K', 'Процессор для игр среднего уровня', 1, 1);

-- Товары: Видеокарты
INSERT INTO products (category_id, name, slug, description, detailed_description, price, brand, stock, sku, meta_title, meta_description, is_featured, is_active) VALUES
(2, 'NVIDIA GeForce RTX 4090', 'nvidia-geforce-rtx-4090', 'Флагманская видеокарта NVIDIA для экстремальных требований', 'NVIDIA GeForce RTX 4090 - самая мощная видеокарта на рынке. Идеальна для 4K-игр, профессионального видеомонтажа и научных вычислений.', 189990.00, 'NVIDIA', 3, 'RTX-4090', 'RTX 4090 купить', 'Самая мощная видеокарта на рынке', 1, 1),
(2, 'NVIDIA GeForce RTX 4070 Ti', 'nvidia-geforce-rtx-4070-ti', 'Видеокарта среднего-высокого класса NVIDIA', 'RTX 4070 Ti - отличный выбор для 1440p и 4K-игр. Поддерживает трассировку лучей и DLSS для лучшей производительности.', 89990.00, 'NVIDIA', 6, 'RTX-4070-Ti', 'RTX 4070 Ti цена', 'Видеокарта для 4K-игр', 1, 1),
(2, 'AMD Radeon RX 7900 XTX', 'amd-radeon-rx-7900-xtx', 'Флагманская видеокарта AMD', 'AMD Radeon RX 7900 XTX - мощная альтернатива RTX 4090 с хорошим соотношением цены и производительности.', 79990.00, 'AMD', 4, 'RX-7900-XTX', 'AMD RX 7900 XTX', 'Видеокарта AMD высокого класса', 1, 1);

-- Товары: Материнские платы
INSERT INTO products (category_id, name, slug, description, detailed_description, price, brand, stock, sku, meta_title, meta_description, is_featured, is_active) VALUES
(3, 'ASUS ROG STRIX Z790-E', 'asus-rog-strix-z790-e', 'Премиум материнская плата для Intel LGA1700', 'ASUS ROG STRIX Z790-E - топовая материнская плата для процессоров Intel 13-го поколения. Поддерживает PCIe 5.0 и имеет высокий класс питания.', 44990.00, 'ASUS', 5, 'ROG-STRIX-Z790-E', 'ASUS ROG STRIX Z790-E', 'Материнская плата Intel Z790', 1, 1),
(3, 'MSI MEG Z790 ACE', 'msi-meg-z790-ace', 'Материнская плата премиум-класса от MSI', 'MSI MEG Z790 ACE - флагманская материнская плата для Intel LGA1700 с множеством продвинутых функций.', 39990.00, 'MSI', 4, 'MEG-Z790-ACE', 'MSI MEG Z790 ACE цена', 'Премиум материнская плата для Intel', 0, 1),
(3, 'AMD ROG STRIX X870-E', 'rog-strix-x870-e', 'Топовая материнская плата для AMD', 'ROG STRIX X870-E - новая материнская плата для AMD Ryzen с поддержкой AM5 и DDR5 памяти.', 34990.00, 'ASUS', 6, 'STRIX-X870-E', 'ASUS ROG STRIX X870-E', 'Материнская плата AMD X870', 1, 1);

-- Товары: Оперативная память
INSERT INTO products (category_id, name, slug, description, detailed_description, price, brand, stock, sku, meta_title, meta_description, is_featured, is_active) VALUES
(4, 'Corsair Vengeance RGB PRO 32GB DDR4', 'corsair-vengeance-32gb-ddr4', 'Оперативная память Corsair 32GB DDR4 с RGB подсветкой', 'Corsair Vengeance RGB PRO 32GB - комплект из 2x16GB оперативной памяти DDR4 со скоростью 3600 МГц. Отлично подходит для игр и работы.', 12990.00, 'Corsair', 15, 'CMW32GX4M2Z3600C18', 'Corsair DDR4 32GB', 'Память для ПК Corsair', 1, 1),
(4, 'Kingston Fury Beast 32GB DDR5', 'kingston-fury-beast-32gb-ddr5', 'Память Kingston Fury Beast 32GB DDR5', 'Kingston Fury Beast 32GB - оперативная память DDR5 со скоростью 6000 МГц. Идеальна для новых материнских плат с поддержкой DDR5.', 19990.00, 'Kingston', 10, 'KF560C32RS-16', 'Kingston DDR5 32GB', 'Оперативная память DDR5', 1, 1);

-- Товары: Накопители
INSERT INTO products (category_id, name, slug, description, detailed_description, price, brand, stock, sku, meta_title, meta_description, is_featured, is_active) VALUES
(5, 'Samsung 870 EVO 1TB SSD', 'samsung-870-evo-1tb', 'SSD накопитель Samsung 1TB SATA', 'Samsung 870 EVO - надежный SATA SSD с емкостью 1TB. Отличная скорость чтения и записи до 560/530 МБ/с.', 8990.00, 'Samsung', 20, 'MZ-77E1T0', 'Samsung SSD 1TB', 'SSD диск для компьютера', 1, 1),
(5, 'Samsung 990 PRO 1TB M.2 NVMe', 'samsung-990-pro-1tb', 'Быстрый NVMe SSD Samsung 1TB', 'Samsung 990 PRO - супербыстрый NVMe SSD с поддержкой PCIe 4.0. Скорость чтения до 7450 МБ/с. Идеален для творческих задач.', 14990.00, 'Samsung', 12, 'MZ-V9P1T0', 'Samsung NVMe 990 PRO', 'Быстрый SSD для ПК', 1, 1);

-- Характеристики товаров
INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES
(1, 'Сокет', 'LGA1700'),
(1, 'Ядра/Потоки', '24/32'),
(1, 'Тактовая частота', '3.0-5.8 ГГц'),
(1, 'TDP', '125W'),
(2, 'Сокет', 'AM5'),
(2, 'Ядра/Потоки', '16/32'),
(2, 'Тактовая частота', '4.5-5.7 ГГц'),
(2, 'TDP', '170W');
