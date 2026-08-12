<?php
/**
 * @var \Art\Settings\Fields\ColorPicker $field
 * @var mixed                            $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'wp-color-picker' );

$default_color = $field->get_default() ? (string) $field->get_default() : '';

wp_add_inline_script(
	'wp-color-picker',
	'jQuery(document).ready(function($){ $(".ast-color-picker").wpColorPicker(); });',
	'after'
);

wp_add_inline_style(
	'wp-color-picker',
	'.wp-picker-open + .wp-picker-input-wrap { display: flex !important; }'
);

?>
	<input type="text"
	       id="<?php echo esc_attr( $field->get_id() ); ?>"
	       name="<?php echo esc_attr( $field->get_id() ); ?>"
	       value="<?php echo esc_attr( (string) $value ); ?>"
	       class="ast-color-picker"
	       data-default-color="<?php echo esc_attr( $default_color ); ?>"
		<?php echo $field->get_rendered_attributes(); ?> />

<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>