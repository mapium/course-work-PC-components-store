<fieldset class="product-media" data-product-media>
    <legend>Галерея товара</legend>
    <input type="hidden" name="gallery_present" value="1">
    <div class="form-group" data-gallery-fallback>
        <label for="gallery-selection">Изображения из библиотеки (можно выбрать несколько)</label>
        <select id="gallery-selection" name="gallery[]" multiple size="6" data-gallery-selection>
            <?php foreach (array_unique(array_merge($form_gallery, $form_images)) as $image): ?>
                <option value="<?php echo htmlspecialchars($image); ?>" data-image-url="<?php echo htmlspecialchars(\App\Helpers\Media::url($image, 'products', true)); ?>" <?php echo in_array($image, $form_gallery, true) ? 'selected' : ''; ?>><?php echo htmlspecialchars($image); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" data-gallery-library-ui hidden>
        <label for="gallery-library">Добавить из библиотеки</label>
        <select id="gallery-library" data-image-select data-gallery-library>
            <option value="">Выберите изображение</option>
            <?php foreach ($form_images as $image): ?>
                <option value="<?php echo htmlspecialchars($image); ?>" data-image-url="<?php echo htmlspecialchars(\App\Helpers\Media::url($image, 'products', true)); ?>"><?php echo htmlspecialchars($image); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-outline btn-sm" data-gallery-add>Добавить в галерею</button>
    </div>
    <div class="form-group"><label for="gallery-uploads">Загрузить дополнительные фотографии</label><input id="gallery-uploads" type="file" name="gallery_uploads[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-gallery-uploads></div>
    <p class="filter-help">До 12 фотографий по 10 МБ. Можно менять порядок, назначать главное изображение и удалять фотографии из галереи.</p>
    <div class="admin-gallery" data-admin-gallery></div>
</fieldset>
