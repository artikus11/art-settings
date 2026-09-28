<?php

namespace Art\Settings\Fields;

class Toggle extends Field {

	public function get_template_name(): string {

		return 'toggle';
	}


	public function sanitize( mixed $value ): bool {

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}
}
