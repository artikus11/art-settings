<?php

namespace Art\Settings\Fields;

class Select extends Field {

	protected array $options = [];


	public function __construct( array $args = [] ) {

		$this->options = $args['options'] ?? [];

		unset( $args['options'] );

		parent::__construct( $args );
	}


	public function get_options(): array {

		return $this->options;
	}


	public function get_template_name(): string {

		return 'select';
	}


	public function sanitize( mixed $value ): string {

		$value = sanitize_text_field( (string) $value );

		return array_key_exists( $value, $this->options ) ? $value : (string) $this->get_default();
	}
}