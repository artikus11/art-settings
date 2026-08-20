<?php

namespace Art\Settings\Tests;

use WP_Mock;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {

		parent::setUp();
		WP_Mock::setUp();
	}


	protected function tearDown(): void {

		WP_Mock::tearDown();
		parent::tearDown();
	}


	protected function stub_wp_sanitizers(): void {

		WP_Mock::userFunction( 'sanitize_text_field', [
			'return' => static function ( $value ) {

				return is_scalar( $value ) ? trim( (string) $value ) : '';
			},
		] );

		WP_Mock::userFunction( 'sanitize_textarea_field', [
			'return' => static function ( $value ) {

				return is_scalar( $value ) ? trim( (string) $value ) : '';
			},
		] );

		WP_Mock::userFunction( 'sanitize_hex_color', [
			'return' => static function ( $value ) {

				$value = (string) $value;

				if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $value ) ) {
					return $value;
				}

				return null;
			},
		] );
	}
}
