<?php

namespace Art\Settings\Fields;

class Text extends Field {

	public function sanitize( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}

	public function get_template_name(): string {
		return 'text';
	}
}