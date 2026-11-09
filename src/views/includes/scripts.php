<?php foreach (['notifications', 'ajax', 'forms', 'theme-toggle', 'tabs', 'search-dialog', 'search-navigation', 'live-search', 'catalog', 'cart', 'admin-navigation', 'admin-images', 'admin-specifications', 'review-modal', 'reviews', 'gallery', 'confirmations'] as $script): ?>
    <script src="<?php echo \App\Helpers\Asset::js($script . '.js'); ?>" defer></script>
<?php endforeach; ?>
