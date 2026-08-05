<?php

namespace Art\Settings\Fields;

class Radio extends Field {

	protected array $options = [];

	public function __construct( array $args = [] ) {
		parent::__construct( $args );
		$this->options = $args['options'] ?? [];
	}

	public function get_options(): array {
		return $this->options;
	}

	public function sanitize( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );

		return array_key_exists( $value, $this->options ) ? $value : (string) $this->get_default();
	}

	public function get_template_name(): string {
		return 'radio';
	}
}