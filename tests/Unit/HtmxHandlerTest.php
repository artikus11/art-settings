<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Services\HtmxHandler;
use Art\Settings\Tests\TestCase;
use WP_Mock;

class HtmxHandlerTest extends TestCase {

	protected array $callback_log = [];

	protected array $sent = [];


	protected function setUp(): void {

		parent::setUp();
		$this->stub_wp_sanitizers();
		$this->callback_log = [];
		$this->sent         = [];

		WP_Mock::userFunction( 'wp_unslash', [
			'return' => static function ( $value ) {

				return $value;
			},
		] );

		WP_Mock::userFunction( 'sanitize_key', [
			'return' => static function ( $value ) {

				return (string) $value;
			},
		] );

		WP_Mock::userFunction( 'wp_send_json_error', [
			'return' => static function ( $data, int $status = 400 ) {

				global $test_htmx_sent;

				$test_htmx_sent[] = [ 'error', $data, $status ];
			},
		] );

		WP_Mock::userFunction( 'wp_send_json_success', [
			'return' => static function ( $data ) {

				global $test_htmx_sent;

				$test_htmx_sent[] = [ 'success', $data ];
			},
		] );

		global $test_htmx_sent;

		$test_htmx_sent = [];
	}


	protected function tearDown(): void {

		global $test_htmx_sent;

		$test_htmx_sent = [];
		parent::tearDown();
	}


	private function create_handler( array $config = [] ): HtmxHandler {

		return new HtmxHandler( array_merge( [
			'menu' => [ 'capability' => 'manage_options' ],
		], $config ) );
	}


	private function stub_nonce( bool $valid ): void {

		WP_Mock::userFunction( 'wp_verify_nonce', [
			'return' => $valid ? 1 : false,
		] );
	}


	private function stub_capability( bool $allowed ): void {

		WP_Mock::userFunction( 'current_user_can', [
			'return' => $allowed,
		] );
	}


	public function test_handle_dispatches_callback_from_config(): void {

		global $test_htmx_sent;

		$handler = $this->create_handler( [
			'htmx_callbacks' => [
				'run_cleanup' => static function ( array $args ): string {

					return 'cleaned:' . ( $args['limit'] ?? 'none' );
				},
			],
		] );

		$this->stub_nonce( true );
		$this->stub_capability( true );

		$_POST = [
			'ast_nonce'  => 'abc',
			'ast_action' => 'run_cleanup',
			'limit'      => '5',
		];

		$handler->handle();

		$this->assertSame( 1, count( $test_htmx_sent ) );
		$this->assertSame( 'success', $test_htmx_sent[0][0] );
		$this->assertSame( 'cleaned:5', $test_htmx_sent[0][1]['result'] );
	}


	public function test_handle_rejects_invalid_nonce(): void {

		global $test_htmx_sent;

		$handler = $this->create_handler();
		$this->stub_nonce( false );
		$this->stub_capability( true );

		$_POST = [
			'ast_nonce'  => 'bad',
			'ast_action' => 'anything',
		];

		$handler->handle();

		$this->assertSame( 1, count( $test_htmx_sent ) );
		$this->assertSame( 'error', $test_htmx_sent[0][0] );
		$this->assertSame( 403, $test_htmx_sent[0][2] );
	}


	public function test_handle_unknown_action_returns_error(): void {

		global $test_htmx_sent;

		$handler = $this->create_handler();
		$this->stub_nonce( true );
		$this->stub_capability( true );

		$_POST = [
			'ast_nonce'  => 'abc',
			'ast_action' => 'missing',
		];

		$handler->handle();

		$this->assertSame( 1, count( $test_htmx_sent ) );
		$this->assertSame( 'error', $test_htmx_sent[0][0] );
		$this->assertSame( 400, $test_htmx_sent[0][2] );
	}


	public function test_handle_dispatches_callback_from_filter(): void {

		global $test_htmx_sent;

		WP_Mock::onFilter( 'art_settings_htmx_callbacks' )
			->with( [] )
			->reply( [
				'flush_cache' => static function ( array $args ): string {

					return 'flushed';
				},
			] );

		$handler = $this->create_handler();
		$this->stub_nonce( true );
		$this->stub_capability( true );

		$_POST = [
			'ast_nonce'  => 'abc',
			'ast_action' => 'flush_cache',
		];

		$handler->handle();

		$this->assertSame( 1, count( $test_htmx_sent ) );
		$this->assertSame( 'success', $test_htmx_sent[0][0] );
		$this->assertSame( 'flushed', $test_htmx_sent[0][1]['result'] );
	}
}