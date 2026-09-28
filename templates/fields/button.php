<?php
/**
 * @var \Art\Settings\Fields\Button $field
 * @var mixed                       $value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$admin_ajax_url = admin_url( 'admin-ajax.php' );
$nonce          = wp_create_nonce( 'art_settings_htmx' );
$class          = 'button' . ( '' !== $field->get_css_class() ? ' ' . $field->get_css_class() : '' );
$attrs          = $field->get_hx_attrs( $admin_ajax_url, $nonce );
?>
<button type="button"
	class="<?php echo esc_attr( $class ); ?>"
	<?php foreach ( $attrs as $attr_key => $attr_value ) : ?>
		<?php echo esc_attr( $attr_key ); ?>="<?php echo esc_attr( $attr_value ); ?>"
	<?php endforeach; ?>
	<?php echo $field->get_rendered_attributes( [ 'rows', 'cols', 'options', 'action', 'args', 'hx_target', 'hx_swap', 'confirm', 'css_class', 'method' ] ); ?>>
	<?php echo esc_html( $field->get_label() ); ?>
</button>
<?php if ( ! empty( $field->get_description() ) ) : ?>
	<p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
<?php endif; ?>