<?php
/**
 * @var array  $tabs
 * @var string $active_tab
 * @var string $menu_slug
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_classes = [
	'skl-core__tabs-wrapper',
	'hide-if-no-js',
	'tab-count-' . count( $tabs ),
];
?>

<nav class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<?php foreach ( $tabs as $slug => $tab_data ) : ?>
		<a href="<?php echo esc_url( add_query_arg( [ 'page' => $menu_slug, 'tab' => $slug ], admin_url( 'admin.php' ) ) ); ?>"
		   class="skl-core__tab <?php echo $active_tab === $slug ? 'active' : ''; ?>">
			<?php echo esc_html( $tab_data['label'] ); ?>
		</a>
	<?php endforeach; ?>
</nav>