<?php
/**
 * Вариант скелетона «modal-rows» — «строки с ячейками» в обёртке карточки.
 *
 * Подключается партиалом `parts/skeleton.php` при `variant => 'modal-rows'`
 * (значение по умолчанию). Переменные приходят из внешнего партиала.
 *
 * @var int  $rows         Количество строк в теле скелетона.
 * @var bool $with_number  Показывать колонку номера.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ast__skeleton-modal-header">
	<span class="ast__skeleton ast__skeleton--title"></span>
	<span class="ast__skeleton ast__skeleton--icon"></span>
</div>

<div class="ast__skeleton-modal-body">
	<?php require __DIR__ . '/rows.php'; ?>
</div>

<div class="ast__skeleton-modal-footer">
	<span class="ast__skeleton ast__skeleton--button"></span>
</div>