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


	public function test_move_submenu_last_puts_item_at_the_end(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'first' ],
				1 => [ 'Title', 'manage_options', 'target' ],
				2 => [ 'Title', 'manage_options', 'third' ],
			],
		];

		$manager->run_move_submenu_last( 'parent', 'target' );

		$this->assertSame( [ 'first', 'third', 'target' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_move_submenu_last_with_missing_item_leaves_submenu_untouched(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'other' ],
			],
		];

		$manager->run_move_submenu_last( 'parent', 'missing' );

		$this->assertSame( [ 'other' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_move_submenu_first_puts_item_at_the_beginning(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'first' ],
				1 => [ 'Title', 'manage_options', 'target' ],
				2 => [ 'Title', 'manage_options', 'third' ],
			],
		];

		$manager->run_move_submenu_first( 'parent', 'target' );

		$this->assertSame( [ 'target', 'first', 'third' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_move_submenu_at_inserts_at_requested_index(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'zero' ],
				1 => [ 'Title', 'manage_options', 'one' ],
				2 => [ 'Title', 'manage_options', 'target' ],
				3 => [ 'Title', 'manage_options', 'three' ],
			],
		];

		$manager->run_move_submenu_at( 'parent', 'target', 1 );

		$this->assertSame( [ 'zero', 'target', 'one', 'three' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_reorder_submenu_last_and_first_and_int(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'alpha' ],
				1 => [ 'Title', 'manage_options', 'beta' ],
				2 => [ 'Title', 'manage_options', 'gamma' ],
			],
		];

		$manager->run_reorder_submenu( [
			'parent_slug' => 'parent',
			'menu_slug'   => 'beta',
			'position'    => 'last',
		] );
		$this->assertSame( [ 'alpha', 'gamma', 'beta' ], array_column( $submenu['parent'], 2 ) );

		$manager->run_reorder_submenu( [
			'parent_slug' => 'parent',
			'menu_slug'   => 'beta',
			'position'    => 'first',
		] );
		$this->assertSame( [ 'beta', 'alpha', 'gamma' ], array_column( $submenu['parent'], 2 ) );

		$manager->run_reorder_submenu( [
			'parent_slug' => 'parent',
			'menu_slug'   => 'beta',
			'position'    => 1,
		] );
		$this->assertSame( [ 'alpha', 'beta', 'gamma' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_reorder_submenu_ignores_null_or_missing_position(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'alpha' ],
				1 => [ 'Title', 'manage_options', 'beta' ],
			],
		];

		$manager->run_reorder_submenu( [
			'parent_slug' => 'parent',
			'menu_slug'   => 'beta',
		] );
		$this->assertSame( [ 'alpha', 'beta' ], array_column( $submenu['parent'], 2 ) );

		$manager->run_reorder_submenu( [
			'parent_slug' => 'parent',
			'menu_slug'   => 'beta',
			'position'    => null,
		] );
		$this->assertSame( [ 'alpha', 'beta' ], array_column( $submenu['parent'], 2 ) );
	}


	public function test_reorder_submenu_without_parent_slug_is_noop(): void {

		global $submenu;

		$manager = $this->create_manager( new InMemorySettingsRepository( [] ) );

		$submenu = [
			'parent' => [
				0 => [ 'Title', 'manage_options', 'alpha' ],
				1 => [ 'Title', 'manage_options', 'beta' ],
			],
		];

		$manager->run_reorder_submenu( [
			'menu_slug' => 'beta',
			'position'  => 'last',
		] );
		$this->assertSame( [ 'alpha', 'beta' ], array_column( $submenu['parent'], 2 ) );
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
