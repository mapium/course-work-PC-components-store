    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo htmlspecialchars(APP_URL); ?>">
    <link rel="icon" href="<?php echo \App\Helpers\Asset::image('favicon.ico'); ?>" type="image/x-icon" sizes="16x16 32x32 48x48">
    <link rel="icon" href="<?php echo \App\Helpers\Asset::image('favicon-32x32.png'); ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?php echo \App\Helpers\Asset::image('favicon.svg'); ?>" type="image/svg+xml" sizes="any">
    <link rel="apple-touch-icon" href="<?php echo \App\Helpers\Asset::image('apple-touch-icon.png'); ?>" sizes="180x180">
    <script src="<?php echo \App\Helpers\Asset::js('theme-init.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('ui.css'); ?>">
    <?php \App\Helpers\Seo::render($seo ?? []); ?>
