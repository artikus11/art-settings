<?php

namespace Art\Settings\Fields;

class Number extends Field {

	public function get_template_name(): string {

		return 'number';
	}


	public function sanitize( mixed $value ): int|float {

		if ( is_numeric( $value ) ) {
			return str_contains( (string) $value, '.' ) ? (float) $value : (int) $value;
		}

		return (int) $this->get_default();
	}
}