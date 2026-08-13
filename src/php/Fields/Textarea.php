<?php

namespace Art\Settings\Fields;

class Textarea extends Field {

	public function get_template_name(): string {

		return 'textarea';
	}


	public function sanitize( mixed $value ): string {

		return sanitize_textarea_field( (string) $value );
	}
}