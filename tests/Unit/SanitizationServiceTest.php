<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Services\SanitizationService;
use Art\Settings\Tests\TestCase;

class SanitizationServiceTest extends TestCase {

	private SanitizationService $sanitizer;


	protected function setUp(): void {

		parent::setUp();
		$this->sanitizer = new SanitizationService();
	}


	public function test_sanitize_for_save_trims_strings_and_keeps_non_strings(): void {

		$result = $this->sanitizer->sanitize_for_save( [
			'name'  => '  value  ',
			'count' => 12,
			'on'    => true,
		] );

		$this->assertSame( 'value', $result['name'] );
		$this->assertSame( 12, $result['count'] );
		$this->assertTrue( $result['on'] );
	}


	public function test_sanitize_for_save_encodes_emoji_recursively(): void {

		$result = $this->sanitizer->sanitize_for_save( [
			'title' => 'Hello 😀',
			'meta'  => [
				'note' => 'ok 🎉',
			],
		] );

		$this->assertSame( 'Hello \u{1F600}', $result['title'] );
		$this->assertSame( 'ok \u{1F389}', $result['meta']['note'] );
	}


	public function test_prepare_for_read_decodes_emoji_recursively(): void {

		$result = $this->sanitizer->prepare_for_read( [
			'title' => 'Hello \u{1F600}',
			'meta'  => [
				'note' => 'ok \u{1F389}',
			],
		] );

		$this->assertSame( 'Hello 😀', $result['title'] );
		$this->assertSame( 'ok 🎉', $result['meta']['note'] );
	}
}
