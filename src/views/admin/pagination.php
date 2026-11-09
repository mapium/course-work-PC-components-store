<?php if ($admin_page > 1 || $has_more): ?>
    <nav class="admin-pagination" aria-label="Страницы списка">
        <?php if ($admin_page > 1): ?><a href="<?php echo APP_URL; ?>/admin?tab=<?php echo $current_tab; ?>&amp;page=<?php echo $admin_page - 1; ?>" class="btn btn-outline">Назад</a><?php endif; ?>
        <span>Страница <?php echo $admin_page; ?></span>
        <?php if ($has_more): ?><a href="<?php echo APP_URL; ?>/admin?tab=<?php echo $current_tab; ?>&amp;page=<?php echo $admin_page + 1; ?>" class="btn btn-outline">Далее</a><?php endif; ?>
    </nav>
<?php endif; ?>
