<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Fields\Checkbox;
use Art\Settings\Fields\ColorPicker;
use Art\Settings\Fields\Number;
use Art\Settings\Fields\Select;
use Art\Settings\Fields\Text;
use Art\Settings\Fields\Textarea;
use Art\Settings\Tests\TestCase;

class FieldsSanitizeTest extends TestCase {

	protected function setUp(): void {

		parent::setUp();
		$this->stub_wp_sanitizers();
	}


	public function test_text_and_textarea_sanitize_strings(): void {

		$text     = new Text();
		$textarea = new Textarea();

		$this->assertSame( 'hello', $text->sanitize( '  hello  ' ) );
		$this->assertSame( 'line', $textarea->sanitize( '  line  ' ) );
	}


	public function test_number_keeps_int_or_float_and_falls_back_to_default(): void {

		$field = new Number( [ 'default' => 7 ] );

		$this->assertSame( 10, $field->sanitize( '10' ) );
		$this->assertSame( 1.5, $field->sanitize( '1.5' ) );
		$this->assertSame( 7, $field->sanitize( 'not-a-number' ) );
	}


	public function test_select_accepts_only_known_options(): void {

		$field = new Select( [
			'options' => [
				'usd' => 'USD',
				'eur' => 'EUR',
			],
			'default' => 'usd',
		] );

		$this->assertSame( 'eur', $field->sanitize( 'eur' ) );
		$this->assertSame( 'usd', $field->sanitize( 'btc' ) );
	}


	public function test_checkbox_casts_to_boolean(): void {

		$field = new Checkbox();

		$this->assertTrue( $field->sanitize( 'on' ) );
		$this->assertTrue( $field->sanitize( '1' ) );
		$this->assertFalse( $field->sanitize( null ) );
		$this->assertFalse( $field->sanitize( '0' ) );
	}


	public function test_color_picker_keeps_hex_and_rejects_invalid(): void {

		$field = new ColorPicker( [ 'default' => '#000000' ] );

		$this->assertSame( '#ff00aa', $field->sanitize( '#ff00aa' ) );
		$this->assertSame( '', $field->sanitize( 'red' ) );
	}
}
