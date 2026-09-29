<?php
/**
 * @var \Art\Settings\Fields\Repeater $field
 * @var array                         $row
 * @var int|string                    $row_index
 * @var \Art\Settings\Renderers\PageRenderer $renderer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_id   = (string) $field->get_id();
$row_label  = $field->get_row_label();
$row_index  = (string) $row_index;
$is_label   = '' !== $row_label;
$row_number = '__INDEX__' === $row_index ? '__INDEX__' : (string) ( (int) $row_index + 1 );
?>
<div class="ast__repeater-row"
	data-repeater-row>
	<?php if ( $is_label ) : ?>
		<span class="ast__repeater-row-label"><?php echo esc_html( $row_label ); ?> #<?php echo esc_html( $row_number ); ?></span>
	<?php endif; ?>

	<?php foreach ( $field->get_fields() as $sub_field_id => $sub_field ) : ?>
		<?php
		if ( ! is_object( $sub_field ) || ! method_exists( $sub_field, 'render' ) ) :
			continue;
		endif;

		$sub_field->set_id( $field_id . '[' . $row_index . '][' . $sub_field_id . ']' );
		$sub_value = $row[ $sub_field_id ] ?? $sub_field->get_default();
		?>
		<div class="ast__repeater-field">
			<label for="<?php echo esc_attr( (string) $sub_field->get_id() ); ?>"
					class="ast__label">
				<?php echo esc_html( $sub_field->get_label() ); ?>
			</label>
			<?php echo $sub_field->render( $sub_value, $renderer ); ?>
		</div>
	<?php endforeach; ?>

	<button type="button"
			class="button ast__repeater-remove"
			data-repeater-remove>
		<?php echo esc_html( 'Удалить' ); ?>
	</button>
</div>