<?php
/**
 * @var string $menu_slug
 * @var string $page_title
 * @var array  $tabs
 * @var string $active_tab
 * @var string $message_notice
 * @var array  $saved_data
 * @var string $nonce_action
 * @var string $nonce_name
 * @var \Art\Settings\Renderers\PageRenderer $renderer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap art-settings-wrap">
	<div class="skl-core__header">
		<div class="skl-core__title-section skl-core__title-section--header">
			<h1><?php echo esc_html( $page_title ); ?></h1>
		</div>

		<?php $renderer->render_tabs( $tabs, $active_tab, $menu_slug ); ?>
	</div>

	<hr class="wp-header-end">

	<?php if ( ! empty( $message_notice ) ) : ?>
		<?php echo $message_notice; ?>
	<?php endif; ?>

	<div id="skl-core-notices"></div>

	<div class="skl-core__body hide-if-no-js skl-core__<?php echo esc_attr( $active_tab ); ?>-tab">
		<form method="post" action="">
			<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
			<input type="hidden" name="current_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<?php
			$current_sections = $tabs[ $active_tab ]['sections'] ?? [];
			foreach ( $current_sections as $section ) {
				$renderer->render_section( $section, $saved_data );
			}

			submit_button();
			?>
		</form>
	</div>
</div>