<?php
/**
 * @var \Art\Settings\Fields\Textarea $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<textarea id="<?php echo esc_attr( $field->get_id() ); ?>"
	          name="<?php echo esc_attr( $field->get_id() ); ?>"
	          rows="5"
	          cols="50"
	          class="large-text"
          <?php echo $field->get_rendered_attributes(); ?>><?php echo esc_textarea( (string) $value ); ?></textarea>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>