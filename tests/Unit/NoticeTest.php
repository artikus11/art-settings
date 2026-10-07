<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Helpers\Notice;
use Art\Settings\Tests\TestCase;
use WP_Mock;

class NoticeTest extends TestCase {

	protected function setUp(): void {

		parent::setUp();

		WP_Mock::userFunction( 'wp_parse_args', [
			'return' => static function ( $args, $defaults = [] ) {

				return array_merge( $defaults, is_array( $args ) ? $args : [] );
			},
		] );

		WP_Mock::userFunction( 'apply_filters', [
			'return' => static function ( $hook, $value ) {

				return $value;
			},
		] );

		WP_Mock::userFunction( 'esc_html', [
			'return' => static function ( $text ) {

				return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
			},
		] );

		WP_Mock::userFunction( 'esc_attr', [
			'return' => static function ( $text ) {

				return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
			},
		] );

		WP_Mock::userFunction( 'wp_kses_post', [
			'return' => static function ( $text ) {

				return (string) $text;
			},
		] );
	}


	public function test_type_maps_to_notice_class(): void {

		$cases = [
			'success' => 'notice-success',
			'error'   => 'notice-error',
			'warning' => 'notice-warning',
			'info'    => 'notice-info',
		];

		foreach ( $cases as $type => $expected_class ) {
			$html = Notice::html( 'Сообщение', $type );

			$this->assertStringContainsString( $expected_class, $html );
			$this->assertStringContainsString( 'ast__notice--' . $type, $html );
		}
	}


	public function test_dismissible_adds_dismiss_button(): void {

		$html = Notice::html( 'Сообщение' );

		$this->assertStringContainsString( 'is-dismissible', $html );
		$this->assertStringContainsString( 'notice-dismiss', $html );
	}


	public function test_dismissible_false_removes_button_and_class(): void {

		$html = Notice::html( 'Сообщение', 'success', [ 'dismissible' => false ] );

		$this->assertStringNotContainsString( 'is-dismissible', $html );
		$this->assertStringNotContainsString( 'notice-dismiss', $html );
	}


	public function test_oob_target_renders_hx_swap_oob_attribute(): void {

		$html = Notice::html( 'Сообщение', 'success', [ 'oob_target' => '#notices' ] );

		$this->assertStringContainsString( 'hx-swap-oob="afterbegin:#notices"', $html );
	}


	public function test_oob_target_keeps_notice_wrapper_inside_oob_container(): void {

		$html = Notice::html( 'Сообщение', 'success', [ 'oob_target' => '#notices' ] );

		// htmx при afterbegin вставляет СОДЕРЖИМОЕ oob-элемента, а не сам элемент:
		// классы теряются, если hx-swap-oob стоит на самом .notice. Обёртка-контейнер
		// должна нести атрибут, а .notice — оставаться её содержимым.
		$this->assertStringContainsString( '<div hx-swap-oob="afterbegin:#notices">', $html );

		$this->assertStringNotContainsString(
			'<div class="notice notice-success is-dismissible ast__notice ast__notice--success" hx-swap-oob',
			$html
		);

		$this->assertMatchesRegularExpression(
			'~<div hx-swap-oob="afterbegin:#notices">\s*<div class="notice notice-success is-dismissible ast__notice ast__notice--success">~',
			$html
		);
	}


	public function test_message_is_escaped_by_default(): void {

		$html = Notice::html( '<b>жирный</b> & текст', 'success' );

		$this->assertStringContainsString( '&lt;b&gt;жирный&lt;/b&gt; &amp; текст', $html );
		$this->assertStringNotContainsString( '<b>', $html );
	}


	public function test_allow_html_renders_kses_posted_message(): void {

		$html = Notice::html( '<b>жирный</b>', 'success', [ 'allow_html' => true ] );

		$this->assertStringContainsString( '<b>жирный</b>', $html );
	}


	public function test_strong_false_removes_strong_wrapper(): void {

		$html = Notice::html( 'Сообщение', 'success', [ 'strong' => false ] );

		$this->assertStringNotContainsString( '<strong>', $html );
		$this->assertStringContainsString( '<p>Сообщение</p>', $html );
	}


	public function test_default_markup_is_backward_compatible_with_notice_template(): void {

		$html = Notice::html( 'Настройки сохранены.', 'success' );

		$this->assertStringContainsString(
			'<div class="notice notice-success is-dismissible ast__notice ast__notice--success">',
			$html
		);
		$this->assertStringContainsString( '<p><strong>Настройки сохранены.</strong></p>', $html );
	}
}