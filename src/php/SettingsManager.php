<?php

namespace Art\Settings;

use Art\Settings\Repositories\SettingsRepository;
use Art\Settings\Renderers\PageRenderer;
use JetBrains\PhpStorm\NoReturn;

class SettingsManager {

	protected array $config;

	protected SettingsRepository $repository;

	protected PageRenderer $renderer;


	public function __construct( array $config ) {

		$this->config     = $config;
		$this->repository = new SettingsRepository( $this->config['option_key'] ?? 'art_settings' );
		$this->renderer   = new PageRenderer( $this->config, $this->repository );
	}


	public function init(): void {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_menu', function (): void {

			$this->apply_position( $this->config['menu'] ?? [] );
		}, PHP_INT_MAX );
		add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
		add_action( 'admin_init', [ $this, 'handle_action' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}


	public function register_menu(): void {

		$menu = $this->config['menu'] ?? [];

		if ( empty( $menu['menu_slug'] ) ) {
			return;
		}

		$position = $this->get_registration_position( $menu['position'] ?? null );

		$normalized_tabs = $this->get_normalized_tabs();

		if ( ! empty( $menu['parent_slug'] ) ) {
			add_submenu_page(
				$menu['parent_slug'],
				$menu['page_title'] ?? 'Настройки',
				$menu['menu_title'] ?? 'Настройки',
				$menu['capability'] ?? 'manage_options',
				$menu['menu_slug'],
				fn() => $this->renderer->render( $normalized_tabs ),
				$position
			);
		} else {
			add_menu_page(
				$menu['page_title'] ?? 'Настройки',
				$menu['menu_title'] ?? 'Настройки',
				$menu['capability'] ?? 'manage_options',
				$menu['menu_slug'],
				fn() => $this->renderer->render( $normalized_tabs ),
				$menu['icon'] ?? 'dashicons-admin-generic',
				$position
			);
		}
	}


	/**
	 * Нормализует position из конфига для передачи в add_submenu_page/add_menu_page.
	 *
	 * WP требует числа; строки ('first'/'last') в UUID не годятся и вызывают _doing_it_wrong.
	 */
	protected function get_registration_position( mixed $position ): int|float|null {

		return is_numeric( $position ) ? $position : null;
	}


	/**
	 * Гарантированная позиция подменю после всех регистраций на admin_menu.
	 *
	 * Вызывается на хуке admin_menu с приоритетом PHP_INT_MAX. Конвенция:
	 * - 'first'  — в начало;
	 * - 'last'   — в конец (переживает подменю, добавленные позже);
	 * - int ≥ 0  — на конкретный индекс (array_splice);
	 * - null/отсутствует — без изменений.
	 */
	protected function apply_position( array $menu ): void {

		if ( empty( $menu['parent_slug'] ) || ! isset( $menu['position'] ) ) {
			return;
		}

		$slug     = $menu['menu_slug'];
		$position = $menu['position'];

		if ( 'first' === $position ) {
			$this->move_submenu_first( $menu['parent_slug'], $slug );
		} elseif ( 'last' === $position ) {
			$this->move_submenu_last( $menu['parent_slug'], $slug );
		} elseif ( is_int( $position ) ) {
			$this->move_submenu_at( $menu['parent_slug'], $slug, $position );
		}
	}


	/**
	 * Перемещает пункт подменю в конец массива $submenu.
	 */
	protected function move_submenu_last( string $parent_slug, string $menu_slug ): void {

		global $submenu;

		$item = $this->extract_submenu_item( $parent_slug, $menu_slug );

		if ( null === $item ) {
			return;
		}

		$submenu[ $parent_slug ][] = $item;
	}


	/**
	 * Перемещает пункт подменю в начало массива $submenu.
	 */
	protected function move_submenu_first( string $parent_slug, string $menu_slug ): void {

		global $submenu;

		$item = $this->extract_submenu_item( $parent_slug, $menu_slug );

		if ( null === $item ) {
			return;
		}

		array_unshift( $submenu[ $parent_slug ], $item );
	}


	/**
	 * Перемещает пункт подменю на конкретный индекс $submenu.
	 */
	protected function move_submenu_at( string $parent_slug, string $menu_slug, int $index ): void {

		global $submenu;

		$item = $this->extract_submenu_item( $parent_slug, $menu_slug );

		if ( null === $item ) {
			return;
		}

		$index       = max( 0, min( $index, count( $submenu[ $parent_slug ] ) ) );
		$submenu[ $parent_slug ] = array_merge(
			array_slice( $submenu[ $parent_slug ], 0, $index ),
			[ $item ],
			array_slice( $submenu[ $parent_slug ], $index )
		);
	}


	/**
	 * Ищет пункт подменю по slug и вынимает его из массива $submenu.
	 *
	 * @return array{0?:string,1?:string,2?:string}|null
	 */
	protected function extract_submenu_item( string $parent_slug, string $menu_slug ): ?array {

		global $submenu;

		if ( empty( $submenu[ $parent_slug ] ) || ! is_array( $submenu[ $parent_slug ] ) ) {
			return null;
		}

		foreach ( $submenu[ $parent_slug ] as $key => $item ) {
			if ( ! is_array( $item ) || ( $item[2] ?? '' ) !== $menu_slug ) {
				continue;
			}

			unset( $submenu[ $parent_slug ][ $key ] );

			return $item;
		}

		return null;
	}


	public function handle_action(): void {

		$menu_slug = $this->config['menu']['menu_slug'] ?? '';

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== $menu_slug ) {
			return;
		}

		$nonce_action = $this->config['nonce_action'] ?? ( 'art_settings_save_' . $menu_slug );
		$nonce_name   = $this->config['nonce_name'] ?? '_art_nonce';

		if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
			return;
		}

		if ( ! current_user_can( $this->config['menu']['capability'] ?? 'manage_options' ) ) {
			wp_die( esc_html__( 'У вас недостаточно прав для изменения настроек.', 'art-settings' ) );
		}

		$current_tab = sanitize_text_field( $_POST['current_tab'] ?? '' );
		$action      = sanitize_text_field( $_POST['art_settings_action'] ?? 'save' );

		if ( 'reset' === $action ) {
			$this->process_reset( $menu_slug, $current_tab );
		} else {
			$this->process_update( $menu_slug, $current_tab );
		}
	}


	/**
	 * Обработка сохранения настроек
	 */
	#[NoReturn]
	protected function process_update( string $menu_slug, string $current_tab ): void {

		$settings = $this->repository->get();

		foreach ( $this->get_registered_fields() as $field_id => $field_object ) {
			$is_in_post = array_key_exists( $field_id, $_POST );
			$is_boolean = $field_object instanceof \Art\Settings\Fields\Checkbox
			              || $field_object instanceof \Art\Settings\Fields\Toggle;

			if ( $is_in_post || $is_boolean ) {
				$raw_value             = $_POST[ $field_id ] ?? null;
				$settings[ $field_id ] = $field_object->sanitize( $raw_value );
			}
		}

		$this->repository->update( $settings );

		$this->redirect( $menu_slug, $current_tab, 'settings-updated' );
	}


	/**
	 * Обработка сброса всех настроек
	 */
	#[NoReturn]
	protected function process_reset( string $menu_slug, string $current_tab ): void {

		$this->repository->reset();

		$this->redirect( $menu_slug, $current_tab, 'settings-reset' );
	}


	/**
	 * Вспомогательный редирект с флагом статуса
	 */
	#[NoReturn]
	protected function redirect( string $menu_slug, string $current_tab, string $status_flag ): void {

		$redirect_url = add_query_arg(
			[
				'page'       => $menu_slug,
				'tab'        => $current_tab,
				$status_flag => 'true',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}


	public function enqueue_assets( string $hook_suffix ): void {

		$menu_slug = $this->config['menu']['menu_slug'] ?? '';
		if ( empty( $menu_slug ) || ! str_contains( $hook_suffix, $menu_slug ) ) {
			return;
		}

		$base_dir = dirname( __DIR__, 2 );
		$base_url = content_url( str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $base_dir ) ) ) . '/assets/';

		$assets = [
			'style'  => [
				'handle'    => 'ast-admin-style',
				'rel_path'  => 'css/ast-admin-style.min.css',
				'deps'      => [],
				'in_footer' => false,
			],
			'script' => [
				'handle'    => 'ast-admin-script',
				'rel_path'  => 'js/ast-admin-script.min.js',
				'deps'      => [ 'jquery' ],
				'in_footer' => true,
			],
		];

		foreach ( $assets as $type => $asset ) {
			$file_path = $base_dir . '/assets/' . $asset['rel_path'];
			$version   = file_exists( $file_path ) ? (string) filemtime( $file_path ) : '1.0.0';
			$file_url  = $base_url . $asset['rel_path'];

			if ( 'style' === $type ) {
				wp_enqueue_style( $asset['handle'], $file_url, $asset['deps'], $version );
			} else {
				wp_enqueue_script( $asset['handle'], $file_url, $asset['deps'], $version, $asset['in_footer'] );
			}
		}
	}


	public function admin_body_class( $body_class ): string {

		$menu_slug = $this->config['menu']['menu_slug'] ?? '';
		$screen    = get_current_screen();

		if ( ! $screen ) {
			return $body_class;
		}

		$is_our_page = str_ends_with( $screen->id, '_page_' . $menu_slug ) || $screen->id === 'toplevel_page_' . $menu_slug;

		if ( ! $is_our_page ) {
			return $body_class;
		}

		$body_class .= ' ast';

		return $body_class;
	}


	/**
	 * Нормализация табов: приводит классы табов и массивы к единому виду
	 */
	public function get_normalized_tabs(): array {

		$normalized = [];

		foreach ( $this->config['tabs'] ?? [] as $slug => $tab_item ) {
			if ( is_object( $tab_item ) && method_exists( $tab_item, 'get_sections' ) ) {
				$label       = method_exists( $tab_item, 'get_label' ) ? $tab_item->get_label() : $slug;
				$sections    = $tab_item->get_sections();
				$save_button = method_exists( $tab_item, 'has_save_button' ) ? $tab_item->has_save_button() : true;
			} else {
				$label       = $tab_item['label'] ?? $slug;
				$sections    = $tab_item['sections'] ?? [];
				$save_button = $tab_item['save_button'] ?? true;
			}

			foreach ( $sections as $section_key => &$section ) {
				foreach ( $section['fields'] ?? [] as $field_id => $field_object ) {
					if ( is_object( $field_object ) && method_exists( $field_object, 'set_id' ) ) {
						$field_object->set_id( (string) $field_id );
					}
				}
			}

			$normalized[ $slug ] = [
				'label'       => $label,
				'save_button' => (bool) $save_button,
				'sections'    => $sections,
			];
		}

		return $normalized;
	}


	/**
	 * Возвращает плоский список полей для цикла сохранения
	 *
	 * @return array<string, \Art\Settings\Fields\Field>
	 */
	protected function get_registered_fields(): array {

		$fields = [];

		foreach ( $this->get_normalized_tabs() as $tab ) {
			foreach ( $tab['sections'] ?? [] as $section ) {
				foreach ( $section['fields'] ?? [] as $field_id => $field_object ) {
					if ( is_object( $field_object ) ) {
						$fields[ $field_id ] = $field_object;
					}
				}
			}
		}

		return $fields;
	}
}