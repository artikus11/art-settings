<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Tests\TestCase;
use WP_Mock;

class SkeletonTest extends TestCase {

	protected function setUp(): void {

		parent::setUp();

		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/' );
		}

		$fixture    = dirname( __DIR__ ) . '/Fixtures/skeleton/custom.php';
		$modal_rows = dirname( __DIR__, 2 ) . '/templates/parts/skeleton/modal-rows.php';
		$rows       = dirname( __DIR__, 2 ) . '/templates/parts/skeleton/rows.php';

		WP_Mock::onFilter( 'art_settings_skeleton_variants' )->withAnyArgs()->reply( [
			'modal-rows' => $modal_rows,
			'rows'       => $rows,
			'custom'     => $fixture,
		] );

		WP_Mock::userFunction( 'esc_attr', [
			'return' => static function ( $text ) {

				return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
			},
		] );
	}


	/**
	 * Рендерит партиал skeleton.php как это делает PageRenderer::render_template().
	 */
	private function render( array $data ): string {

		$defaults = [
			'variant'     => 'modal-rows',
			'rows'        => 5,
			'with_number' => false,
			'id'          => '',
			'class'       => '',
			'card'        => true,
			'class_card'  => '',
		];

		extract( array_merge( $defaults, $data ) ); // phpcs:ignore WordPress.PHP.DontExtract -- тестовый харнесс повторяет рендерер.

		ob_start();
		include dirname( __DIR__, 2 ) . '/templates/parts/skeleton.php';

		return (string) ob_get_clean();
	}


	public function test_default_modal_rows_renders_root_card_and_rows(): void {

		$html = $this->render( [
			'rows'        => 3,
			'with_number' => true,
			'id'          => 'sklpf-test-skeleton',
			'class'       => 'htmx-indicator',
		] );

		$this->assertStringContainsString( 'class="ast__skeleton-root htmx-indicator"', $html );
		$this->assertStringContainsString( 'id="sklpf-test-skeleton"', $html );
		$this->assertStringContainsString( 'class="ast__skeleton-card"', $html );
		$this->assertStringContainsString( 'ast__skeleton-modal-header', $html );
		$this->assertStringContainsString( 'ast__skeleton-modal-body', $html );
		$this->assertStringContainsString( 'ast__skeleton-modal-footer', $html );
		$this->assertEquals( 3, substr_count( $html, 'ast__skeleton-row' ) );
		$this->assertStringContainsString( 'ast__skeleton-cell--number', $html );
	}


	public function test_rows_variant_skips_modal_header_and_footer(): void {

		$html = $this->render( [
			'variant'     => 'rows',
			'rows'        => 2,
			'with_number' => false,
		] );

		$this->assertStringContainsString( 'ast__skeleton-card', $html );
		$this->assertEquals( 2, substr_count( $html, 'ast__skeleton-row' ) );
		$this->assertStringNotContainsString( 'ast__skeleton-modal-header', $html );
		$this->assertStringNotContainsString( 'ast__skeleton-modal-footer', $html );
		$this->assertStringNotContainsString( 'ast__skeleton-cell--number', $html );
	}


	public function test_card_false_removes_inner_wrapper(): void {

		$html = $this->render( [ 'card' => false ] );

		$this->assertStringContainsString( 'ast__skeleton-root', $html );
		$this->assertStringNotContainsString( 'ast__skeleton-card', $html );
		$this->assertStringContainsString( 'ast__skeleton-modal-header', $html );
	}


	public function test_class_card_appends_to_card(): void {

		$html = $this->render( [ 'class_card' => 'sklpf-modal' ] );

		$this->assertStringContainsString( 'class="ast__skeleton-card sklpf-modal"', $html );
	}


	public function test_custom_variant_from_filter_renders_inside_card(): void {

		$html = $this->render( [ 'variant' => 'custom' ] );

		$this->assertStringContainsString( 'ast__skeleton-card', $html );
		$this->assertStringContainsString( 'ast__test-custom-skeleton', $html );
		$this->assertStringNotContainsString( 'ast__skeleton-modal-header', $html );
	}


	public function test_unknown_variant_falls_back_to_modal_rows(): void {

		$html = $this->render( [ 'variant' => 'nope' ] );

		$this->assertStringContainsString( 'ast__skeleton-modal-header', $html );
	}
}