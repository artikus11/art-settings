<?php

namespace Art\Settings;

use Art\Settings\Repositories\SettingsRepository;
use Art\Settings\Renderers\PageRenderer;

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
		add_action( 'admin_init', [ $this, 'handle_save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}


	public function register_menu(): void {

		$menu = $this->config['menu'] ?? [];

		if ( empty( $menu['menu_slug'] ) ) {
			return;
		}

		$normalized_tabs = $this->get_normalized_tabs();

		if ( ! empty( $menu['parent_slug'] ) ) {
			add_submenu_page(
				$menu['parent_slug'],
				$menu['page_title'] ?? 'Настройки',
				$menu['menu_title'] ?? 'Настройки',
				$menu['capability'] ?? 'manage_options',
				$menu['menu_slug'],
				fn() => $this->renderer->render( $normalized_tabs )
			);
		} else {
			add_menu_page(
				$menu['page_title'] ?? 'Настройки',
				$menu['menu_title'] ?? 'Настройки',
				$menu['capability'] ?? 'manage_options',
				$menu['menu_slug'],
				fn() => $this->renderer->render( $normalized_tabs ),
				$menu['icon'] ?? 'dashicons-admin-generic',
				$menu['position'] ?? null
			);
		}
	}


	public function handle_save(): void {
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

		$new_fields = [];

		// Читаем напрямую из $_POST по ID зарегистрированных полей
		foreach ( $this->get_registered_fields() as $field_id => $field_object ) {
			$raw_value              = $_POST[ $field_id ] ?? null;
			$new_fields[ $field_id ] = $field_object->sanitize( $raw_value );
		}

		$this->repository->update( $new_fields );

		$redirect_url = add_query_arg(
			[
				'page'             => $menu_slug,
				'tab'              => sanitize_text_field( $_POST['current_tab'] ?? '' ),
				'settings-updated' => 'true',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}


	public function enqueue_assets( string $hook_suffix ): void {

		// Подключаем стили и скрипты только на своей странице настроек
		$menu_slug = $this->config['menu']['menu_slug'] ?? '';
		if ( empty( $menu_slug ) || ! str_contains( $hook_suffix, $menu_slug ) ) {
			return;
		}

		// Логика подключения билда из /assets/ (css/js)
	}


	/**
	 * Нормализация табов: приводит классы табов и массивы к единому виду
	 */
	public function get_normalized_tabs(): array {

		$normalized = [];

		foreach ( $this->config['tabs'] ?? [] as $slug => $tab_item ) {
			if ( is_object( $tab_item ) && method_exists( $tab_item, 'get_sections' ) ) {
				$label    = method_exists( $tab_item, 'get_label' ) ? $tab_item->get_label() : $slug;
				$sections = $tab_item->get_sections();
			} else {
				$label    = $tab_item['label'] ?? $slug;
				$sections = $tab_item['sections'] ?? [];
			}

			// Проставляем ID полям из ключей массива секции
			foreach ( $sections as $section_key => &$section ) {
				foreach ( $section['fields'] ?? [] as $field_id => $field_object ) {
					if ( is_object( $field_object ) && method_exists( $field_object, 'set_id' ) ) {
						$field_object->set_id( (string) $field_id );
					}
				}
			}

			$normalized[ $slug ] = [
				'label'    => $label,
				'sections' => $sections,
			];
		}

		return $normalized;
	}


	/**
	 * Возвращает плоский список полей для цикла сохранения
	 *
	 * @return array<string, \Art\Settings\Fields\AbstractField>
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