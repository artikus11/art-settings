<?php

namespace Art\Settings\Services;

class SanitizationService {

	/**
	 * Санитизирует массив данных перед сохранением в БД
	 */
	public function sanitize_for_save( array $data ): array {

		return array_map( [ $this, 'sanitize_value_for_save' ], $data );
	}


	/**
	 * Подготавливает массив данных после чтения из БД
	 */
	public function prepare_for_read( array $data ): array {

		return array_map( [ $this, 'prepare_value_for_read' ], $data );
	}


	/**
	 * Рекурсивная очистка отдельного значения при сохранении
	 */
	protected function sanitize_value_for_save( mixed $value ): mixed {

		if ( is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_value_for_save' ], $value );
		}

		if ( is_string( $value ) ) {
			$value = trim( $value );

			return $this->encode_emoji( $value );
		}

		return $value;
	}


	/**
	 * Рекурсивное восстановление значения при чтении
	 */
	protected function prepare_value_for_read( mixed $value ): mixed {

		if ( is_array( $value ) ) {
			return array_map( [ $this, 'prepare_value_for_read' ], $value );
		}

		if ( is_string( $value ) ) {
			return $this->decode_emoji( $value );
		}

		return $value;
	}


	/**
	 * Кодирование 4-байтовых UTF-8 символов (эмодзи) в \u{XXXX}
	 */
	protected function encode_emoji( string $value ): string {

		return preg_replace_callback( '/[\xF0-\xF7][\x80-\xBF]{3}/', static function ( array $match ): string {

			return sprintf( '\u{%X}', mb_ord( $match[0], 'UTF-8' ) );
		}, $value );
	}


	/**
	 * Декодирование \u{XXXX} обратно в эмодзи
	 */
	protected function decode_emoji( string $value ): string {

		return preg_replace_callback( '/\\\u\{([0-9A-Fa-f]+)\}/', static function ( array $match ): string {

			return mb_chr( (int) hexdec( $match[1] ), 'UTF-8' );
		}, $value );
	}
}