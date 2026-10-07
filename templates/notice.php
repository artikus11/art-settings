<?php
/**
 * @var string $message
 * @var string $type success|error|warning|info
 * @var array  $args
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type = $type ?? 'success';
$args = wp_parse_args( $args ?? [], [
	'strong'      => true,
	'dismissible' => true,
	'oob_target'  => '',
	'allow_html'  => false,
] );

$type_class = match ( $type ) {
	'error'   => 'notice-error',
	'warning' => 'notice-warning',
	'info'    => 'notice-info',
	default   => 'notice-success',
};

$notice_class = 'notice ' . $type_class;

if ( $args['dismissible'] ) {
	$notice_class .= ' is-dismissible';
}

$notice_class .= ' ast__notice ast__notice--' . $type;

$message_html = $args['allow_html'] ? wp_kses_post( $message ) : esc_html( $message );

$message_content = $args['strong'] ? '<strong>' . $message_html . '</strong>' : $message_html;
?>

<?php if ( $args['oob_target'] ) : ?>
	<div hx-swap-oob="afterbegin:<?php echo esc_attr( $args['oob_target'] ); ?>">
<?php endif; ?>
	<div class="<?php echo esc_attr( $notice_class ); ?>">
		<p><?php echo wp_kses_post( $message_content ); ?></p>
		<?php if ( $args['dismissible'] ) : ?>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php echo esc_html( 'Скрыть уведомление' ); ?></span>
			</button>
		<?php endif; ?>
	</div>
<?php if ( $args['oob_target'] ) : ?>
	</div>
<?php endif; ?>