<?php
/**
 * @var \Art\Settings\Fields\Toggle $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<label for="<?php echo esc_attr( $field->get_id() ); ?>"
		class="ast__switch">
	<input type="checkbox"
			id="<?php echo esc_attr( $field->get_id() ); ?>"
			name="<?php echo esc_attr( $field->get_id() ); ?>"
			value="1"
		<?php checked( (bool) $value, true ); ?>
		<?php echo $field->get_rendered_attributes( [ 'rows', 'cols', 'options', 'on_label', 'off_label' ] ); ?>>
	<span class="ast__slider"></span>
</label>
<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description ast__switch-description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>