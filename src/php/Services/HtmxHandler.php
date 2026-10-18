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

		$result = call_user_func( $callback, $args );

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

		$merged = $this->collect_callbacks();

		return $merged[ $action ] ?? null;
	}


	/**
	 * Собирает карты колбеков всех подписчиков фильтра art_settings_htmx_callbacks.
	 *
	 * На сети активно несколько skl-плагинов с HTMX-страницами, и каждый подписчик
	 * возвращает собственную карту (конвенция «верни своё»), не учитывая входящее
	 * значение. apply_filters в такой схеме отдаёт результат только последнего
	 * подписчика — колбеки остальных плагинов пропадают («Unknown action»).
	 * Здесь каждый подписчик вызывается отдельно, возвраты мержатся; подписчики
	 * с merge-конвенцией (array_merge($value, $own)) при $value=[] работают так же.
	 *
	 * @return array<string, callable>
	 */
	protected function collect_callbacks(): array {

		$hook = $GLOBALS['wp_filter']['art_settings_htmx_callbacks'] ?? null;

		// Duck typing: WP_Hook в проде; в юнит-среде — любой объект с ->callbacks
		// (массив приоритет => [definition => ['function' => callable]]).
		$is_hook = ( $hook instanceof \WP_Hook )
			|| ( is_object( $hook ) && isset( $hook->callbacks ) && is_array( $hook->callbacks ) );

		if ( ! $is_hook ) {
			$filtered = apply_filters( 'art_settings_htmx_callbacks', [] );

			return is_array( $filtered ) ? $filtered : [];
		}

		$merged = [];

		foreach ( $hook->callbacks as $callbacks ) {
			foreach ( $callbacks as $definition ) {

				$callable = $definition['function'] ?? null;

				if ( ! is_callable( $callable ) ) {
					continue;
				}

				$result = call_user_func( $callable, [] );

				if ( is_array( $result ) ) {
					$merged = array_merge( $merged, $result );
				}
			}
		}

		// Fallback: подписчиков нет или структура нестандартна — обычный прогон фильтра.
		if ( [] === $merged ) {
			$filtered = apply_filters( 'art_settings_htmx_callbacks', [] );

			return is_array( $filtered ) ? $filtered : [];
		}

		return $merged;
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


	protected function error_log( string $message ): void {

		error_log( sprintf( '[art-settings][htmx][error] %s', $message ) );
	}
}
