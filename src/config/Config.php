<?php

namespace App\Config;

if (!defined('APP_NAME')) define('APP_NAME', 'PCComponents Store');
if (!defined('APP_URL')) define('APP_URL', 'http://localhost/course-work-PC-components-store');
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'pc_components');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Пути
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(dirname(__DIR__)));
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');
if (!defined('SRC_PATH')) define('SRC_PATH', BASE_PATH . '/src');
if (!defined('VIEWS_PATH')) define('VIEWS_PATH', SRC_PATH . '/views');

// Безопасность
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600); // 1 час
if (!defined('PASSWORD_HASH_ALGO')) define('PASSWORD_HASH_ALGO', 'bcrypt');

// Роли пользователей
if (!defined('ROLE_GUEST')) define('ROLE_GUEST', 'guest');
if (!defined('ROLE_CLIENT')) define('ROLE_CLIENT', 'client');
if (!defined('ROLE_MANAGER')) define('ROLE_MANAGER', 'manager');
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 'admin');
