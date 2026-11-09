<div class="form-group">
    <label>Характеристики товара</label>
    <div id="specifications-container" data-specifications>
        <?php foreach ($form_specs as $index => $spec): ?>
            <div class="specification-row" data-specification-row>
                <input type="text" name="specifications[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars(\App\Helpers\Request::text($spec, 'name')); ?>" maxlength="100" placeholder="Название характеристики" aria-label="Название характеристики" class="spec-name-input" list="spec-names-list">
                <input type="text" name="specifications[<?php echo $index; ?>][value]" value="<?php echo htmlspecialchars(\App\Helpers\Request::text($spec, 'value')); ?>" maxlength="255" placeholder="Значение" aria-label="Значение характеристики">
                <button type="submit" class="btn btn-sm btn-destructive" name="form_command" value="remove_specification:<?php echo $index; ?>" formnovalidate data-remove-specification aria-label="Удалить характеристику"><img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14"></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-outline" name="form_command" value="add_specification" formnovalidate data-add-specification><img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">Добавить характеристику</button>
    <datalist id="spec-names-list"><?php foreach ($spec_names as $spec_name): ?><option value="<?php echo htmlspecialchars($spec_name); ?>"><?php endforeach; ?></datalist>
    <template data-specification-template>
        <div class="specification-row" data-specification-row>
            <input type="text" data-specification-field="name" maxlength="100" placeholder="Название характеристики" aria-label="Название характеристики" class="spec-name-input" list="spec-names-list">
            <input type="text" data-specification-field="value" maxlength="255" placeholder="Значение" aria-label="Значение характеристики">
            <button type="submit" class="btn btn-sm btn-destructive" name="form_command" formnovalidate data-remove-specification aria-label="Удалить характеристику"><img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14"></button>
        </div>
    </template>
</div>
