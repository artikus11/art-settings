<?php
/**
 * @var \Art\Settings\Fields\Text $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<input type="text"
	       id="<?php echo esc_attr( $field->get_id() ); ?>"
	       name="<?php echo esc_attr( $field->get_id() ); ?>"
	       value="<?php echo esc_attr( (string) $value ); ?>"
	       class="regular-text"
		<?php echo $field->get_rendered_attributes(); ?>>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>