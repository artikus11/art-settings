<?php
/**
 * @var \Art\Settings\Fields\Radio $field
 * @var mixed $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<fieldset>
		<?php foreach ( $field->get_options() as $option_key => $option_label ) : ?>
			<?php $input_id = $field->get_id() . '_' . $option_key; ?>
			<label for="<?php echo esc_attr( $input_id ); ?>" style="display: block; margin-bottom: 4px;">
				<input type="radio"
				       id="<?php echo esc_attr( $input_id ); ?>"
				       name="<?php echo esc_attr( $field->get_id() ); ?>"
				       value="<?php echo esc_attr( $option_key ); ?>"
					<?php checked( (string) $value, (string) $option_key ); ?>
					<?php echo $field->get_rendered_attributes(); ?>>
				<?php echo esc_html( $option_label ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>