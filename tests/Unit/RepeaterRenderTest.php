<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Fields\Repeater;
use Art\Settings\Fields\Text;
use Art\Settings\Renderers\PageRenderer;
use Art\Settings\Tests\Support\InMemorySettingsRepository;
use Art\Settings\Tests\TestCase;
use WP_Mock;

class RepeaterRenderTest extends TestCase {

	protected function setUp(): void {

		parent::setUp();

		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/' );
		}

		WP_Mock::userFunction( 'esc_html', [
			'return' => static fn( $value ) => (string) $value,
		] );

		WP_Mock::userFunction( 'esc_attr', [
			'return' => static fn( $value ) => (string) $value,
		] );

		WP_Mock::userFunction( 'apply_filters', [
			'return' => static fn( $tag, $value = null ) => $value,
		] );
	}


	protected function make_repeater(): Repeater {

		$repeater = new Repeater( [
			'row_label' => 'Строка',
			'fields'    => [
				'title' => new Text( [ 'label' => 'Заголовок' ] ),
			],
		] );

		$repeater->set_id( 'hero_repeater' );

		return $repeater;
	}


	protected function render_repeater( Repeater $repeater ): string {

		$renderer = new PageRenderer( [], new InMemorySettingsRepository() );

		return $repeater->render( [], $renderer );
	}


	public function test_add_button_rendered_above_and_below_rows(): void {

		$html = $this->render_repeater( $this->make_repeater() );

		self::assertSame( 2, substr_count( $html, 'data-repeater-add' ) );
	}


	public function test_template_row_contains_row_label_with_index_placeholder(): void {

		$html = $this->render_repeater( $this->make_repeater() );

		self::assertStringContainsString( 'ast__repeater-row-label', $html );
		self::assertStringContainsString( '#__INDEX__', $html );
	}


	public function test_first_row_contains_numbered_row_label(): void {

		$html = $this->render_repeater( $this->make_repeater() );

		self::assertStringContainsString( 'Строка #1', $html );
	}


	public function test_row_label_omitted_when_not_configured(): void {

		$repeater = new Repeater( [
			'fields' => [
				'title' => new Text( [ 'label' => 'Заголовок' ] ),
			],
		] );
		$repeater->set_id( 'hero_repeater' );

		$html = $this->render_repeater( $repeater );

		self::assertDoesNotMatchRegularExpression( '/ast__repeater-row-label/', $html );
	}


	public function test_each_existing_row_gets_sequential_number(): void {

		$repeater = $this->make_repeater();

		$html = $repeater->render( [ [ 'title' => 'Первый' ], [ 'title' => 'Второй' ] ], new PageRenderer( [], new InMemorySettingsRepository() ) );

		self::assertStringContainsString( 'Строка #1', $html );
		self::assertStringContainsString( 'Строка #2', $html );
		self::assertSame( 1, substr_count( $html, 'Строка #1' ) );
		self::assertSame( 1, substr_count( $html, 'Строка #2' ) );
	}


	public function test_row_renders_one_field_block_per_sub_field(): void {

		$repeater = new Repeater( [
			'row_label' => 'Строка',
			'fields'    => [
				'title'       => new Text( [ 'label' => 'Заголовок' ] ),
				'description' => new Text( [ 'label' => 'Описание' ] ),
			],
		] );
		$repeater->set_id( 'hero_repeater' );

		$html = $this->render_repeater( $repeater );

		// 2 под-поля × 2 строки (видимая строка min_rows=1 + шаблонная) = 4 блока
		// + 2 обёртки .ast__repeater-fields (по одной на строку).
		self::assertSame( 2, substr_count( $html, 'ast__repeater-fields' ) );
		self::assertSame( 4, substr_count( $html, 'class="ast__repeater-field"' ) );
		self::assertStringContainsString( 'hero_repeater[0][title]', $html );
		self::assertStringContainsString( 'hero_repeater[0][description]', $html );
	}


	public function test_row_renders_saved_values(): void {

		$repeater = $this->make_repeater();

		$html = $repeater->render( [ [ 'title' => 'Сохранённое значение' ] ], new PageRenderer( [], new InMemorySettingsRepository() ) );

		self::assertStringContainsString( 'value="Сохранённое значение"', $html );
	}


	public function test_template_row_uses_index_placeholder_in_attributes(): void {

		$html = $this->render_repeater( $this->make_repeater() );

		self::assertStringContainsString( 'hero_repeater[__INDEX__][title]', $html );
	}
}
