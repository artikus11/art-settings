<?php

namespace Art\Settings\Renderers;

use Art\Settings\Repositories\SettingsRepository;

class PageRenderer {

	protected array $config;

	protected SettingsRepository $repository;


	public function __construct( array $config, SettingsRepository $repository ) {

		$this->config     = $config;
		$this->repository = $repository;
	}


	public function render( array $normalized_tabs ): void {

		if ( empty( $normalized_tabs ) ) {
			return;
		}

		$active_tab = sanitize_text_field( $_GET['tab'] ?? '' );
		if ( ! isset( $normalized_tabs[ $active_tab ] ) ) {
			$active_tab = (string) array_key_first( $normalized_tabs );
		}

		$is_saved       = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
		$message_notice = $is_saved ? $this->get_notice_html( 'Настройки сохранены.', 'success' ) : '';

		$layout_data = [
			'menu_slug'      => $this->config['menu']['menu_slug'] ?? '',
			'page_title'     => $this->config['menu']['page_title'] ?? 'Настройки',
			'tabs'           => $normalized_tabs,
			'active_tab'     => $active_tab,
			'message_notice' => $message_notice,
			'saved_data'     => $this->repository->get(),
			'nonce_action'   => $this->config['nonce_action'] ?? ( 'art_settings_save_' . ( $this->config['menu']['menu_slug'] ?? '' ) ),
			'nonce_name'     => $this->config['nonce_name'] ?? '_art_nonce',
			'renderer'       => $this,
		];

		$this->render_template( 'layout.php', $layout_data );
	}


	/**
	 * Рендерит блок переключений табов
	 */
	public function render_tabs( array $tabs, string $active_tab, string $menu_slug ): void {

		$this->render_template( 'tabs.php', [
			'tabs'       => $tabs,
			'active_tab' => $active_tab,
			'menu_slug'  => $menu_slug,
		] );
	}


	/**
	 * Рендерит отдельную секцию
	 */
	public function render_section( array $section, array $saved_data ): void {

		$this->render_template( 'section.php', [
			'section'    => $section,
			'saved_data' => $saved_data,
			'renderer'   => $this,
		] );
	}


	/**
	 * Подключает PHP-шаблон с передачей переменных
	 */
	public function render_template( string $template_name, array $data = [] ): void {

		$template_path = $this->get_template_path( $template_name );

		if ( ! file_exists( $template_path ) ) {
			echo sprintf( '<!-- Template not found: %s -->', esc_html( $template_path ) );

			return;
		}

		extract( $data );

		include $template_path;
	}


	public function get_notice_html( string $message, string $type = 'success' ): string {

		ob_start();
		$this->render_template( 'notice.php', [
			'message' => $message,
			'type'    => $type,
		] );

		return ob_get_clean();
	}


	public function get_template_path( string $template_name ): string {

		// 1. Проверяем, задан ли кастомный базовый путь в конфиге
		if ( ! empty( $this->config['template_path'] ) ) {
			$base_dir = rtrim( $this->config['template_path'], '/\\' );
		} else {
			// 2. Дефолтный путь: корень библиотеки (3 уровня вверх от src/php/Renderers)
			$base_dir = dirname( __DIR__, 3 ) . '/templates';
		}

		$template_path = $base_dir . '/' . ltrim( $template_name, '/\\' );

		// 3. Возможность точечного переопределения через WP-фильтр
		return (string) apply_filters(
			'art_settings_template_path',
			$template_path,
			$template_name,
			$this->config
		);
	}
}