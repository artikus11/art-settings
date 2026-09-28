<?php

namespace Art\Settings\Fields;

use Art\Settings\Renderers\PageRenderer;

class Repeater extends Field {

	protected array $fields = [];

	protected int $min_rows = 1;

	protected ?int $max_rows = null;

	protected string $button_label = '';

	protected string $row_label = '';


	public function __construct( array $args = [] ) {

		$this->fields       = $args['fields'] ?? [];
		$this->min_rows     = max( 0, (int) ( $args['min_rows'] ?? 1 ) );
		$this->max_rows     = isset( $args['max_rows'] ) ? max( $this->min_rows, (int) $args['max_rows'] ) : null;
		$this->button_label = (string) ( $args['button_label'] ?? '' );
		$this->row_label    = (string) ( $args['row_label'] ?? '' );

		unset( $args['fields'], $args['min_rows'], $args['max_rows'], $args['button_label'], $args['row_label'] );

		parent::__construct( $args );
	}


	/**
	 * @return array<string, \Art\Settings\Fields\Field>
	 */
	public function get_fields(): array {

		return $this->fields;
	}


	public function get_min_rows(): int {

		return $this->min_rows;
	}


	public function get_max_rows(): ?int {

		return $this->max_rows;
	}


	public function get_button_label(): string {

		return '' !== $this->button_label ? $this->button_label : 'Добавить';
	}


	public function get_row_label(): string {

		return $this->row_label;
	}


	public function get_template_name(): string {

		return 'repeater';
	}


	public function sanitize( mixed $value ): array {

		if ( ! is_array( $value ) ) {
			return (array) ( $this->get_default() ?? [] );
		}

		$rows = [];

		foreach ( $value as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$normalized_row = [];

			foreach ( $this->fields as $field_id => $field_object ) {
				if ( ! is_object( $field_object ) || ! method_exists( $field_object, 'sanitize' ) ) {
					continue;
				}

				$normalized_row[ $field_id ] = $field_object->sanitize( $row[ $field_id ] ?? null );
			}

			$rows[] = $normalized_row;
		}

		$rows_count = count( $rows );

		while ( $rows_count < $this->min_rows ) {
			$rows[] = $this->get_empty_row();
			++$rows_count;
		}

		if ( null !== $this->max_rows ) {
			$rows = array_slice( $rows, 0, $this->max_rows );
		}

		return $rows;
	}


	/**
	 * Рендерит HTML одной строки репитера.
	 *
	 * @param  mixed                    $row       Значения строки.
	 * @param  int|string               $row_index Индекс строки или плейсхолдер шаблона ('__INDEX__').
	 * @param  \Art\Settings\Renderers\PageRenderer $renderer Рендерер.
	 * @return string
	 */
	public function render_row( mixed $row, mixed $row_index, PageRenderer $renderer ): string {

		$this->set_renderer( $renderer );

		ob_start();
		$this->renderer->render_template( 'fields/repeater-row.php', [
			'field'     => $this,
			'row'       => is_array( $row ) ? $row : $this->get_empty_row(),
			'row_index' => $row_index,
			'renderer'  => $renderer,
		] );

		return (string) ob_get_clean();
	}


	/**
	 * Строка со значениями по умолчанию вложенных полей.
	 *
	 * @return array<string, mixed>
	 */
	public function get_empty_row(): array {

		$row = [];

		foreach ( $this->fields as $field_id => $field_object ) {
			if ( is_object( $field_object ) && method_exists( $field_object, 'get_default' ) ) {
				$row[ $field_id ] = $field_object->get_default();
			}
		}

		return $row;
	}
}
