<?php

namespace Art\Settings\Services;

class HtmxHandler {

	protected array $config;


	public function __construct( array $config ) {

		$this->config = $config;
	}


	/**
	 * Регистрирует admin-ajax хук для HTMX-колбеков страницы настроек.
	 */
	public function register(): void {

		add_action( 'wp_ajax_art_settings_htmx', [ $this, 'handle' ] );
	}


	/**
	 * Точка входа admin-ajax: nonce, capability, диспатч колбека.
	 */
	public function handle(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверяется ниже в этом же методе.
		$nonce = sanitize_text_field( wp_unslash( $_POST['ast_nonce'] ?? '' ) );

		if ( ! $this->is_valid_nonce( $nonce ) ) {
			$this->error_log( 'nonce verification failed' );
			wp_send_json_error( [ 'message' => 'Invalid nonce.' ], 403 );

			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверен выше.
		$action = sanitize_key( wp_unslash( $_POST['ast_action'] ?? '' ) );

		$this->debug_log( sprintf( 'dispatch start action=%s', $action ) );

		if ( ! current_user_can( $this->config['menu']['capability'] ?? 'manage_options' ) ) {
			$this->error_log( 'insufficient capability' );
			wp_send_json_error( [ 'message' => 'Forbidden.' ], 403 );

			return;
		}

		$callback = $this->resolve_callback( $action );

		if ( null === $callback ) {
			$this->error_log( sprintf( 'callback not found action=%s', $action ) );
			wp_send_json_error( [ 'message' => sprintf( 'Unknown action: %s', $action ) ], 400 );

			return;
		}

		$args = $this->resolve_args();

		$this->debug_log( sprintf( 'dispatch %s -> %s', $action, is_callable( $callback ) ? 'callable' : 'array' ) );

		$result = call_user_func( $callback, $args );

		$this->debug_log( 'dispatch done action=' . $action );

		wp_send_json_success( [ 'result' => $result ] );
	}


	protected function is_valid_nonce( string $nonce ): bool {

		return '' !== $nonce && (bool) wp_verify_nonce( $nonce, 'art_settings_htmx' );
	}


	/**
	 * Ищет колбек: сначала в конфиге, затем через фильтр art_settings_htmx_callbacks.
	 */
	protected function resolve_callback( string $action ): mixed {

		$configured = $this->config['htmx_callbacks'] ?? [];

		if ( is_array( $configured ) && isset( $configured[ $action ] ) ) {
			return $configured[ $action ];
		}

		$filtered = apply_filters( 'art_settings_htmx_callbacks', [] );

		if ( is_array( $filtered ) && isset( $filtered[ $action ] ) ) {
			return $filtered[ $action ];
		}

		return null;
	}


	/**
	 * Аргументы колбека из POST (ассоц. массив без служебных ключей).
	 */
	protected function resolve_args(): array {

		$args = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce проверен в handle().
		foreach ( $_POST as $key => $value ) {
			if ( in_array( $key, [ 'action', 'ast_action', 'ast_nonce' ], true ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$args[ $key ] = array_map( static function ( $item ): string {

					return sanitize_text_field( wp_unslash( $item ?? '' ) );
				}, $value );
			} else {
				$args[ $key ] = sanitize_text_field( wp_unslash( $value ?? '' ) );
			}
		}

		return $args;
	}


	protected function debug_log( string $message ): void {

		if ( true !== ( $this->config['debug_logging'] ?? false ) ) {
			return;
		}

		error_log( sprintf( '[art-settings][htmx][debug] %s', $message ) );
	}


	protected function error_log( string $message ): void {

		error_log( sprintf( '[art-settings][htmx][error] %s', $message ) );
	}
}
