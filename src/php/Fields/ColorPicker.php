<?php

namespace Art\Settings\Fields;

class ColorPicker extends Field {

	public function get_template_name(): string {

		return 'color-picker';
	}


	public function sanitize( mixed $value ): string {

		return sanitize_hex_color( (string) $value ) ?? '';
	}
}