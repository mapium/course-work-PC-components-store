<?php
if (!$product_images) $product_images = [['image_path' => 'placeholder.jpg', 'is_main' => true]];
$main_index = 0;
foreach ($product_images as $index => $image) if ($image['image_path'] === $product['main_image']) { $main_index = $index; break; }
?>
<div class="product-gallery" data-gallery>
    <div class="product-image-container gallery-stage">
        <a href="<?php echo htmlspecialchars($product_image_url); ?>" data-gallery-zoom aria-label="Увеличить фотографию">
            <img src="<?php echo htmlspecialchars($product_image_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image-large" width="600" height="600" data-gallery-main>
        </a>
        <?php if (count($product_images) > 1): ?>
            <button type="button" class="gallery-arrow gallery-prev" data-gallery-prev aria-label="Предыдущее изображение" hidden>‹</button>
            <button type="button" class="gallery-arrow gallery-next" data-gallery-next aria-label="Следующее изображение" hidden>›</button>
        <?php endif; ?>
    </div>
    <div class="gallery-thumbnails" aria-label="Фотографии товара">
        <?php foreach ($product_images as $index => $image): ?>
            <a href="<?php echo htmlspecialchars(\App\Helpers\Media::url($image['image_path'])); ?>" data-gallery-thumb class="gallery-thumbnail" aria-label="Изображение <?php echo $index + 1; ?>" <?php echo $index === $main_index ? 'aria-current="true"' : ''; ?>>
                <img src="<?php echo htmlspecialchars(\App\Helpers\Media::url($image['image_path'], 'products', true)); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> — фото <?php echo $index + 1; ?>" width="76" height="76" loading="lazy">
            </a>
        <?php endforeach; ?>
    </div>
    <p class="sr-only" data-gallery-status role="status"></p>
    <dialog class="gallery-lightbox" data-gallery-dialog aria-label="Увеличенное изображение товара">
        <button type="button" class="gallery-close btn btn-outline" data-gallery-close aria-label="Закрыть изображение">×</button>
        <button type="button" class="gallery-arrow gallery-prev" data-gallery-prev aria-label="Предыдущее изображение">‹</button>
        <img src="<?php echo htmlspecialchars($product_image_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" data-gallery-full>
        <button type="button" class="gallery-arrow gallery-next" data-gallery-next aria-label="Следующее изображение">›</button>
    </dialog>
</div>
