<?php
/**
 * Скелетон загрузки — «строки с ячейками» для HTMX-индикаторов.
 *
 * Оверлеи/модальная обёртка остаются зоной плагина: партиал рендерит только
 * тело скелетона. Использование (паттерн skl-product-feed):
 *
 * ```php
 * $renderer->render_template( 'parts/skeleton.php', [
 *     'rows'        => 15,
 *     'with_number' => true,
 *     'id'          => 'sklpf-mapping-skeleton',
 *     'class'       => 'htmx-indicator',
 * ] );
 * ```
 *
 * @var int    $rows         Количество строк в теле скелетона (default 5).
 * @var bool   $with_number  Показывать колонку номера (default false).
 * @var string $id           Атрибут id контейнера (default '').
 * @var string $class        Дополнительные классы контейнера (default '').
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows        = (int) ( $rows ?? 5 );
$with_number = (bool) ( $with_number ?? false );
$id          = (string) ( $id ?? '' );
$class       = (string) ( $class ?? '' );

$container_class = 'ast__skeleton-modal' . ( $class ? ' ' . $class : '' );
?>

<div class="<?php echo esc_attr( $container_class ); ?>"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
	<div class="ast__skeleton-modal-header">
		<span class="ast__skeleton ast__skeleton--title"></span>
		<span class="ast__skeleton ast__skeleton--icon"></span>
	</div>

	<div class="ast__skeleton-modal-body">
		<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
			<div class="ast__skeleton-row">
				<?php if ( $with_number ) : ?>
					<span class="ast__skeleton ast__skeleton-cell--number"></span>
				<?php endif; ?>
				<span class="ast__skeleton ast__skeleton-cell--text"></span>
				<span class="ast__skeleton ast__skeleton-cell--input"></span>
			</div>
		<?php endfor; ?>
	</div>

	<div class="ast__skeleton-modal-footer">
		<span class="ast__skeleton ast__skeleton--button"></span>
	</div>
</div>