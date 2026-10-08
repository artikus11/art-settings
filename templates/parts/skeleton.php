<?php
/**
 * Скелетон загрузки — универсальная обёртка для HTMX-индикаторов.
 *
 * Партиал рендерит внешнюю обёртку (root — зона оверлея плагина) и внутреннюю
 * «карточку», внутри которой подключается шаблон выбранного варианта. Варианты
 * регистрируются фильтром `art_settings_skeleton_variants` (slug => абсолютный
 * путь к PHP-шаблону); встроены `modal-rows` (default) и `rows`.
 *
 * Оверлей HTMX (`.htmx-request`) и стили модалки накладываются плагином на
 * внешнюю обёртку (root), а не на элементы тела — поэтому у root есть отдельный
 * параметр `class`, а тело скелетона выносится во внутреннюю обёртку
 * `ast__skeleton-card`.
 *
 * Использование (паттерн skl-product-feed):
 *
 * ```php
 * $renderer->render_template( 'parts/skeleton.php', [
 *     'variant'     => 'modal-rows', // default
 *     'rows'        => 15,
 *     'with_number' => true,
 *     'id'          => 'sklpf-mapping-skeleton',
 *     'class'       => 'htmx-indicator',
 * ] );
 * ```
 *
 * @var string $variant      Slug варианта скелетона (default 'modal-rows').
 * @var int    $rows         Количество строк в теле скелетона (default 5).
 * @var bool   $with_number  Показывать колонку номера (default false).
 * @var string $id           Атрибут id внешней обёртки (default '').
 * @var string $class        Дополнительные классы внешней обёртки (default '').
 * @var bool   $card         Рендерить внутреннюю обёртку-карточку (default true).
 * @var string $class_card   Дополнительные классы карточки (default '').
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant     = (string) ( $variant ?? 'modal-rows' );
$rows        = (int) ( $rows ?? 5 );
$with_number = (bool) ( $with_number ?? false );
$id          = (string) ( $id ?? '' );
$class       = (string) ( $class ?? '' );
$card        = (bool) ( $card ?? true );
$class_card  = (string) ( $class_card ?? '' );

$variants = (array) apply_filters(
	'art_settings_skeleton_variants',
	[
		'modal-rows' => __DIR__ . '/skeleton/modal-rows.php',
		'rows'       => __DIR__ . '/skeleton/rows.php',
	],
	[
		'variant'     => $variant,
		'rows'        => $rows,
		'with_number' => $with_number,
		'id'          => $id,
		'class'       => $class,
		'card'        => $card,
		'class_card'  => $class_card,
	]
);

$variant_path = isset( $variants[ $variant ] ) ? (string) $variants[ $variant ] : '';

if ( '' === $variant_path || ! is_file( $variant_path ) ) {
	$variant_path = isset( $variants['modal-rows'] ) ? (string) $variants['modal-rows'] : '';
}

if ( '' === $variant_path || ! is_file( $variant_path ) ) {
	return;
}

$root_class = 'ast__skeleton-root' . ( $class ? ' ' . $class : '' );
$card_class = 'ast__skeleton-card' . ( $class_card ? ' ' . $class_card : '' );
?>

<div class="<?php echo esc_attr( $root_class ); ?>"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
	<?php if ( $card ) : ?>
		<div class="<?php echo esc_attr( $card_class ); ?>">
	<?php endif; ?>

	<?php require $variant_path; ?>

	<?php if ( $card ) : ?>
		</div>
	<?php endif; ?>
</div>