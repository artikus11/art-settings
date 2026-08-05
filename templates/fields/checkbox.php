<?php
/**
 * @var \Art\Settings\Fields\Checkbox $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<label for="<?php echo esc_attr( $field->get_id() ); ?>">
	<input type="checkbox"
	       id="<?php echo esc_attr( $field->get_id() ); ?>"
	       name="<?php echo esc_attr( $field->get_id() ); ?>"
	       value="1"
		<?php checked( (bool) $value, true ); ?>
		<?php echo $field->get_rendered_attributes(); ?>>
	<?php echo esc_html( $field->get_description() ); ?>
</label>