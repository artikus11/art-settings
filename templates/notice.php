<?php
/**
 * @var string $message
 * @var string $type success|error|warning|info
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type_class = match ( $type ) {
	'error'   => 'notice-error',
	'warning' => 'notice-warning',
	'info'    => 'notice-info',
	default   => 'notice-success',
};
?>

<div class="notice <?php echo esc_attr( $type_class ); ?> is-dismissible ast__notice ast__notice--<?php echo esc_attr( $type ); ?>">
	<p><strong><?php echo esc_html( $message ); ?></strong></p>
</div>