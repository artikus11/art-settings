<?php

namespace Art\Settings\Fields;

class Checkbox extends Field {

	public function get_template_name(): string {

		return 'checkbox';
	}


	public function sanitize( mixed $value ): bool {

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}
}