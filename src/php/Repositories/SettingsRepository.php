<?php

namespace Art\Settings\Repositories;

class SettingsRepository {

	protected string $option_key;

	protected ?array $cache = null;


	public function __construct( string $option_key ) {

		$this->option_key = $option_key;
	}


	/**
	 * Получает плоский массив всех настроек из wp_options.
	 *
	 * @return array<string, mixed>
	 */
	public function get(): array {

		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$options = get_option( $this->option_key, [] );

		if ( ! is_array( $options ) ) {
			$options = [];
		}

		$this->cache = $options;

		return $this->cache;
	}


	/**
	 * Сохраняет плоский массив настроек в wp_options.
	 *
	 * @param  array<string, mixed> $data
	 */
	public function update( array $data ): bool {

		$this->cache = $data;

		return update_option( $this->option_key, $data );
	}


	/**
	 * Возвращает значение конкретного поля по его ID.
	 */
	public function get_field_value( string $field_id, mixed $default = null ): mixed {

		$options = $this->get();

		return $options[ $field_id ] ?? $default;
	}


	/**
	 * Сбрасывает сохраненные опции до пустого массива [].
	 */
	public function reset(): bool {

		$this->cache = [];

		return update_option( $this->option_key, [] );
	}


	/**
	 * Полностью удаляет опцию из базы данных wp_options.
	 */
	public function delete(): bool {

		$this->cache = null;

		return delete_option( $this->option_key );
	}


	/**
	 * Сбрасывает локальный кэш процесса.
	 */
	public function clear_cache(): void {

		$this->cache = null;
	}
}