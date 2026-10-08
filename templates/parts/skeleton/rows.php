<?php
/**
 * Вариант скелетона «rows» — только строки с ячейками.
 *
 * Используется как самостоятельный вариант (`variant => 'rows'`) для инлайн-
 * скелетонов таблиц/списков (обычно с `card => false`), а также как тело
 * варианта «modal-rows». Переменные приходят из внешнего партиала.
 *
 * @var int  $rows         Количество строк в теле скелетона.
 * @var bool $with_number  Показывать колонку номера.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
	<div class="ast__skeleton-row">
		<?php if ( $with_number ) : ?>
			<span class="ast__skeleton ast__skeleton-cell--number"></span>
		<?php endif; ?>
		<span class="ast__skeleton ast__skeleton-cell--text"></span>
		<span class="ast__skeleton ast__skeleton-cell--input"></span>
	</div>
<?php endfor; ?>