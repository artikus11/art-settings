<?php

namespace Art\Settings\Repositories;

class SettingsRepository {

	protected string $option_key;

	protected ?array $cache = null;


	public function __construct( string $option_key ) {

		$this->option_key = $option_key;
	}


	public function get(): array {

		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$options     = get_option( $this->option_key, [] );

		$this->cache = is_array( $options ) ? $options : [];

		return $this->cache;
	}


	public function update( array $data ): bool {

		$this->cache = $data;

		return update_option( $this->option_key, $data );
	}


	public function get_field_value( string $field_id, mixed $default = null ): mixed {

		$options = $this->get();

		if ( array_key_exists( $field_id, $options ) && '' !== $options[ $field_id ] && null !== $options[ $field_id ] ) {
			return $options[ $field_id ];
		}

		return $default;
	}


	// Хелперы поверх get_field_value
	public function get_string( string $field_id, string $default = '' ): string {

		$value = $this->get_field_value( $field_id, $default );

		return is_scalar( $value ) ? (string) $value : $default;
	}


	public function get_int( string $field_id, int $default = 0 ): int {

		$value = $this->get_field_value( $field_id, $default );

		return is_numeric( $value ) ? (int) $value : $default;
	}


	public function get_bool( string $field_id, bool $default = false ): bool {

		$value = $this->get_field_value( $field_id, $default );

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? $default;
	}


	public function reset(): bool {

		$this->cache = [];

		return update_option( $this->option_key, [] );
	}


	public function delete(): bool {

		$this->cache = null;

		return delete_option( $this->option_key );
	}


	public function clear_cache(): void {

		$this->cache = null;
	}
}