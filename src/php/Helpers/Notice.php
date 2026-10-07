<?php

namespace Art\Settings\Helpers;

/**
 * Статический рендер уведомлений поверх templates/notice.php.
 *
 * Единый вывод ast__notice вместо собственных копий View::notice() в плагинах:
 * не требует инстанса PageRenderer, работает с дефолтным шаблоном либы и
 * уважает фильтр art_settings_template_path.
 */
final class Notice {

	/**
	 * Рендерит уведомление в HTML-строку.
	 *
	 * @param string $message Текст сообщения.
	 * @param string $type    Тип: success|error|warning|info.
	 * @param array  $args    {
	 *     @type bool   $strong      Оборачивать сообщение в <strong> (default true).
	 *     @type bool   $dismissible Выводить кнопку закрытия .notice-dismiss (default true).
	 *     @type string $oob_target  HTMX OOB-цель: hx-swap-oob="afterbegin:<target>" (default '').
	 *     @type bool   $allow_html  Разрешить HTML в сообщении через wp_kses_post (default false).
	 * }
	 */
	public static function html( string $message, string $type = 'success', array $args = [] ): string {

		$args = wp_parse_args( $args, [
			'strong'      => true,
			'dismissible' => true,
			'oob_target'  => '',
			'allow_html'  => false,
		] );

		$template_path = self::get_template_path();

		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		ob_start();

		include $template_path;

		return ob_get_clean();
	}


	/**
	 * Путь к шаблону уведомления: кастомный через фильтр art_settings_template_path,
	 * фолбэк — папка шаблонов либы (логика PageRenderer::get_template_path()).
	 */
	private static function get_template_path(): string {

		$default_path = dirname( __DIR__, 3 ) . '/templates/notice.php';

		$filtered_path = (string) apply_filters( 'art_settings_template_path', $default_path, 'notice.php', [] );

		if ( '' !== $filtered_path && file_exists( $filtered_path ) ) {
			return $filtered_path;
		}

		return $default_path;
	}
}
