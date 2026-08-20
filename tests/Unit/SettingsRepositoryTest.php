<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Repositories\SettingsRepository;
use Art\Settings\Tests\TestCase;
use WP_Mock;

class SettingsRepositoryTest extends TestCase {

	public function test_get_returns_empty_array_when_option_is_missing(): void {

		WP_Mock::userFunction( 'get_option', [
			'times'  => 1,
			'args'   => [ 'my_plugin_options', [] ],
			'return' => [],
		] );

		$repository = new SettingsRepository( 'my_plugin_options' );

		$this->assertSame( [], $repository->get() );
	}


	public function test_get_caches_option_and_decodes_emoji(): void {

		WP_Mock::userFunction( 'get_option', [
			'times'  => 1,
			'args'   => [ 'my_plugin_options', [] ],
			'return' => [ 'title' => 'Hi \u{1F600}' ],
		] );

		$repository = new SettingsRepository( 'my_plugin_options' );

		$this->assertSame( 'Hi 😀', $repository->get()['title'] );
		$this->assertSame( 'Hi 😀', $repository->get()['title'] );
	}


	public function test_typed_getters_and_empty_value_fallback(): void {

		WP_Mock::userFunction( 'get_option', [
			'return' => [
				'api_key' => 'token',
				'limit'   => '15',
				'debug'   => 'yes',
				'empty'   => '',
			],
		] );

		$repository = new SettingsRepository( 'my_plugin_options' );

		$this->assertSame( 'token', $repository->get_string( 'api_key' ) );
		$this->assertSame( 15, $repository->get_int( 'limit' ) );
		$this->assertTrue( $repository->get_bool( 'debug' ) );
		$this->assertSame( 'fallback', $repository->get_string( 'empty', 'fallback' ) );
		$this->assertSame( 'missing', $repository->get_string( 'absent', 'missing' ) );
	}


	public function test_reset_writes_empty_array(): void {

		WP_Mock::userFunction( 'get_option', [
			'return' => [ 'api_key' => 'token' ],
		] );
		WP_Mock::userFunction( 'update_option', [
			'times'  => 1,
			'args'   => [ 'my_plugin_options', [] ],
			'return' => true,
		] );

		$repository = new SettingsRepository( 'my_plugin_options' );
		$repository->get();

		$this->assertTrue( $repository->reset() );
		$this->assertSame( [], $repository->get() );
	}
}
