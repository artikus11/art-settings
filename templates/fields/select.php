<?php
/**
 * @var \Art\Settings\Fields\Select $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<select id="<?php echo esc_attr( $field->get_id() ); ?>"
	        name="<?php echo esc_attr( $field->get_id() ); ?>"
		<?php echo $field->get_rendered_attributes(); ?>>
		<?php foreach ( $field->get_options() as $option_key => $option_label ) : ?>
			<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( (string) $value, (string) $option_key ); ?>>
				<?php echo esc_html( $option_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>