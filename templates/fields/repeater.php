<?php
/**
 * @var \Art\Settings\Fields\Repeater $field
 * @var mixed                         $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows      = is_array( $value ) ? $value : [];
$min_rows  = $field->get_min_rows();
$max_rows  = $field->get_max_rows();
$field_id  = (string) $field->get_id();
$renderer  = $field->get_renderer();

$rows_count = count( $rows );

while ( $rows_count < $min_rows ) {
	$rows[] = $field->get_empty_row();
	++$rows_count;
}
?>
<div class="ast__repeater"
	data-field-id="<?php echo esc_attr( $field_id ); ?>"
	data-min-rows="<?php echo esc_attr( (string) $min_rows ); ?>"
	data-max-rows="<?php echo esc_attr( null !== $max_rows ? (string) $max_rows : '' ); ?>">
	<button type="button"
			class="button ast__repeater-add"
			data-repeater-add>
		<?php echo esc_html( $field->get_button_label() ); ?>
	</button>

	<div class="ast__repeater-rows"
		data-repeater-rows>
		<?php foreach ( $rows as $row_index => $row ) : ?>
			<?php echo $field->render_row( $row, $row_index, $renderer ); ?>
		<?php endforeach; ?>
	</div>

	<button type="button"
			class="button ast__repeater-add"
			data-repeater-add>
		<?php echo esc_html( $field->get_button_label() ); ?>
	</button>

	<div class="ast__repeater-template"
		data-repeater-template
		hidden>
		<?php echo $field->render_row( [], '__INDEX__', $renderer ); ?>
	</div>
</div>
<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>