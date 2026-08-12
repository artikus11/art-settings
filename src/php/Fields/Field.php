<?php

namespace Art\Settings\Fields;

use Art\Settings\Renderers\PageRenderer;

abstract class Field {

	protected string $id = '';

	protected string $label = '';

	protected mixed $default = null;

	protected string $description = '';

	protected array $attributes = [];

	protected ?PageRenderer $renderer = null;


	public function __construct( array $args = [] ) {

		$this->label       = $args['label'] ?? '';
		$this->default     = $args['default'] ?? null;
		$this->description = $args['description'] ?? '';
		$this->attributes  = $args['attributes'] ?? [];

		unset( $args['label'], $args['default'], $args['description'], $args['attributes'] );

		$this->attributes = array_merge( $args, $this->attributes );
	}


	public function get_id(): string {

		return $this->id;
	}


	public function set_id( string $id ): self {

		$this->id = $id;

		return $this;
	}


	public function get_label(): string {

		return $this->label;
	}


	public function get_default(): mixed {

		return $this->default;
	}


	public function get_description(): string {

		return $this->description;
	}


	public function set_renderer( PageRenderer $renderer ): self {

		$this->renderer = $renderer;

		return $this;
	}


	public function get_attribute( string $key, mixed $default = null ): mixed {

		return $this->attributes[ $key ] ?? $default;
	}


	/**
	 * Формирует HTML-строку произвольных атрибутов (placeholder, min, max, readonly)
	 */
	public function get_rendered_attributes( array $exclude = [ 'rows', 'cols' ] ): string {

		if ( empty( $this->attributes ) ) {
			return '';
		}

		$html = [];
		foreach ( $this->attributes as $key => $value ) {
			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}

			if ( is_bool( $value ) ) {
				if ( $value ) {
					$html[] = esc_attr( $key );
				}
			} else {
				$html[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
			}
		}

		return empty( $html ) ? '' : ' ' . implode( ' ', $html );
	}


	/**
	 * Очистка входных данных перед сохранением
	 */
	abstract public function sanitize( mixed $value ): mixed;


	/**
	 * Имя PHP-файла шаблона поля (без расширения .php)
	 */
	abstract public function get_template_name(): string;


	/**
	 * Возвращает отрендеренный HTML-код поля
	 */
	public function render( mixed $current_value, PageRenderer $renderer ): string {

		$this->set_renderer( $renderer );

		$value = null !== $current_value ? $current_value : $this->get_default();

		ob_start();
		$this->renderer->render_template(
			'fields/' . $this->get_template_name() . '.php',
			[
				'field' => $this,
				'value' => $value,
			]
		);

		return (string) ob_get_clean();
	}
}