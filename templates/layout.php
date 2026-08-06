<?php
/**
 * Layout for the settings page.
 *
 * @var string                               $menu_slug
 * @var string                               $page_title
 * @var array                                $tabs
 * @var string                               $active_tab
 * @var string                               $message_notice
 * @var array                                $saved_data
 * @var string                               $nonce_action
 * @var string                               $nonce_name
 * @var \Art\Settings\Renderers\PageRenderer $renderer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ast__wrapper">
	<div class="ast__header">
		<div class="ast__title-section">
			<h1 class="ast__title"><?php echo esc_html( $page_title ); ?></h1>
		</div>
		
		<div class="ast__title-section--info">
			<?php do_action( 'ast_title_section_info' ); ?>
		</div>
		
		<?php $renderer->render_tabs( $tabs, $active_tab, $menu_slug ); ?>
	</div>
	
	<hr class="wp-header-end">
	
	<?php if ( ! empty( $message_notice ) ) : ?>
		<div class="ast__notices">
			<?php echo $message_notice; ?>
		</div>
	<?php endif; ?>
	
	<div class="ast__body hide-if-no-js ast__body--tab-<?php echo esc_attr( $active_tab ); ?>">
		<form method="post"
		      action=""
		      class="ast__form">
			
			<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
			
			<input type="hidden"
			       name="current_tab"
			       value="<?php echo esc_attr( $active_tab ); ?>">
			
			<div class="ast__section-wrapper">
				<?php
				$current_tab_data = $tabs[ $active_tab ] ?? [];
				$current_sections = $current_tab_data['sections'] ?? [];
				
				foreach ( $current_sections as $section ) :
					$renderer->render_section( $section, $saved_data );
				endforeach;
				?>
			</div>
			
			<?php
			$has_save_button = $current_tab_data['save_button'] ?? true;
			if ( $has_save_button ) :
				submit_button();
			endif;
			?>
		
		</form>
	</div>
</div>