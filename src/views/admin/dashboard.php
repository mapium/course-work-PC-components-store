<?php require __DIR__ . '/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php $seo = ['title' => $admin_sections[$current_tab]['title'] . ' — Панель управления']; include dirname(__DIR__) . '/includes/head.php'; ?>
</head>
<body>
    <?php include dirname(__DIR__) . '/includes/header.php'; ?>
    <main data-admin>
        <div class="container">
            <div class="page-header">
                <h1><img src="<?php echo APP_URL; ?>/public/images/icons/admin_panel_settings.svg" alt="" width="32" height="32" class="page-icon">Панель управления магазином</h1>
            </div>
            <div class="admin-container">
                <aside class="sticky-aside admin-sidebar">
                    <nav class="admin-nav" aria-label="Разделы панели управления">
                        <?php foreach ($admin_sections as $tab => $section): ?>
                            <a href="<?php echo APP_URL; ?>/admin?tab=<?php echo $tab; ?>" class="admin-nav-link <?php echo $current_tab === $tab ? 'active' : ''; ?>" <?php echo $current_tab === $tab ? 'aria-current="page"' : ''; ?>>
                                <img src="<?php echo APP_URL; ?>/public/images/icons/<?php echo $section['icon']; ?>.svg" alt="" width="18" height="18">
                                <?php echo $section['title']; ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </aside>
                <section class="admin-content">
                    <?php include __DIR__ . '/' . $admin_sections[$current_tab]['file']; ?>
                </section>
            </div>
        </div>
    </main>
    <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
</body>
</html>
