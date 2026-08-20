<?php

namespace Art\Settings\Tests\Unit;

use Art\Settings\Fields\Checkbox;
use Art\Settings\Fields\Select;
use Art\Settings\Fields\Text;
use Art\Settings\Tests\Support\InMemorySettingsRepository;
use Art\Settings\Tests\Support\TestableSettingsManager;
use Art\Settings\Tests\TestCase;

class SettingsManagerTest extends TestCase {

	protected function setUp(): void {

		parent::setUp();
		$this->stub_wp_sanitizers();
		$_POST = [];
	}


	protected function tearDown(): void {

		$_POST = [];
		parent::tearDown();
	}


	public function test_process_update_merges_posted_fields_and_unchecked_checkboxes(): void {

		$repository = new InMemorySettingsRepository( [
			'api_key'        => 'old-key',
			'enable_cache'   => true,
			'other_tab_text' => 'keep-me',
			'mode'           => 'b',
		] );

		$manager = $this->create_manager( $repository );

		$_POST = [
			'api_key' => 'new-key',
			'mode'    => 'zzz',
		];

		$manager->run_process_update( 'my-settings', 'general' );

		$this->assertSame(
			[
				'api_key'        => 'new-key',
				'enable_cache'   => false,
				'other_tab_text' => 'keep-me',
				'mode'           => 'a',
			],
			$repository->get()
		);
	}


	public function test_process_reset_clears_all_settings(): void {

		$repository = new InMemorySettingsRepository( [
			'api_key' => 'old-key',
		] );

		$manager = $this->create_manager( $repository );
		$manager->run_process_reset( 'my-settings', 'general' );

		$this->assertSame( [], $repository->get() );
	}


	public function test_get_normalized_tabs_supports_tab_objects_and_sets_field_ids(): void {

		$field = new Text( [ 'label' => 'API' ] );

		$tab = new class( $field ) {
			private Text $field;

			public function __construct( Text $field ) {
				$this->field = $field;
			}

			public function get_label(): string {
				return 'Shop';
			}

			public function has_save_button(): bool {
				return false;
			}

			public function get_sections(): array {
				return [
					'checkout' => [
						'title'  => 'Checkout',
						'fields' => [
							'api_key' => $this->field,
						],
					],
				];
			}
		};

		$manager = new TestableSettingsManager( [
			'option_key' => 'my_plugin_options',
			'menu'       => [ 'menu_slug' => 'my-settings' ],
			'tabs'       => [ 'shop' => $tab ],
		] );

		$tabs = $manager->get_normalized_tabs();

		$this->assertSame( 'Shop', $tabs['shop']['label'] );
		$this->assertFalse( $tabs['shop']['save_button'] );
		$this->assertSame( 'api_key', $field->get_id() );
	}


	private function create_manager( InMemorySettingsRepository $repository ): TestableSettingsManager {

		$manager = new TestableSettingsManager( [
			'option_key' => 'my_plugin_options',
			'menu'       => [ 'menu_slug' => 'my-settings' ],
			'tabs'       => [
				'general' => [
					'label'    => 'General',
					'sections' => [
						'main' => [
							'fields' => [
								'api_key'      => new Text(),
								'enable_cache' => new Checkbox(),
							],
						],
					],
				],
				'extra'   => [
					'label'    => 'Extra',
					'sections' => [
						'more' => [
							'fields' => [
								'other_tab_text' => new Text(),
								'mode'           => new Select( [
									'options' => [
										'a' => 'A',
										'b' => 'B',
									],
									'default' => 'a',
								] ),
							],
						],
					],
				],
			],
		] );

		$manager->set_repository( $repository );

		return $manager;
	}
}
