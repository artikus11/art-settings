<?php
/**
 * @var \Art\Settings\Fields\Textarea $field
 * @var mixed                         $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows  = (int) $field->get_attribute( 'rows', 5 );
$cols  = (int) $field->get_attribute( 'cols', 50 );
$class = (string) $field->get_attribute( 'class', 'large-text' );
?>
	
	<textarea id="<?php echo esc_attr( $field->get_id() ); ?>"
	          name="<?php echo esc_attr( $field->get_id() ); ?>"
	          rows="<?php echo esc_attr( $rows ); ?>"
	          cols="<?php echo esc_attr( $cols ); ?>"
	          class="<?php echo esc_attr( $class ); ?>"
          <?php echo $field->get_rendered_attributes(); ?>><?php echo esc_textarea( (string) $value ); ?></textarea>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>