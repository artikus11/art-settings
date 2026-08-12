<?php
/**
 * @var \Art\Settings\Fields\Number $field
 * @var mixed                       $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = (int) $field->get_attribute( 'class', 'small-text' );
?>
	
	<input type="number"
	       id="<?php echo esc_attr( $field->get_id() ); ?>"
	       name="<?php echo esc_attr( $field->get_id() ); ?>"
	       value="<?php echo esc_attr( (string) $value ); ?>"
	       class="<?php echo esc_attr( $class ); ?>"
		<?php echo $field->get_rendered_attributes(); ?>>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>