<?php

namespace Art\Settings\Fields;

class Button extends Field {

	protected string $action = '';

	protected array $args = [];

	protected string $hx_target = '';

	protected string $hx_swap = 'innerHTML';

	protected string $confirm = '';

	protected string $css_class = '';

	protected string $method = 'post';


	public function __construct( array $args = [] ) {

		$this->action    = (string) ( $args['action'] ?? '' );
		$this->args      = $args['args'] ?? [];
		$this->hx_target = (string) ( $args['hx_target'] ?? '' );
		$this->hx_swap   = (string) ( $args['hx_swap'] ?? 'innerHTML' );
		$this->confirm   = (string) ( $args['confirm'] ?? '' );
		$this->css_class = (string) ( $args['css_class'] ?? '' );
		$this->method    = (string) ( $args['method'] ?? 'post' );

		unset( $args['action'], $args['args'], $args['hx_target'], $args['hx_swap'], $args['confirm'], $args['css_class'], $args['method'] );

		parent::__construct( $args );
	}


	public function get_action(): string {

		return $this->action;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function get_args(): array {

		return $this->args;
	}


	public function get_hx_target(): string {

		return $this->hx_target;
	}


	public function get_hx_swap(): string {

		return $this->hx_swap;
	}


	public function get_confirm(): string {

		return $this->confirm;
	}


	public function get_css_class(): string {

		return $this->css_class;
	}


	public function get_method(): string {

		return $this->method;
	}


	/**
	 * Формирует htmx-атрибуты кнопки.
	 *
	 * @return array<string, string>
	 */
	public function get_hx_attrs( string $admin_ajax_url, string $nonce ): array {

		$values = array_merge(
			$this->args,
			[
				'ast_action' => $this->action,
				'ast_nonce'  => $nonce,
			]
		);

		$attrs = [
			'hx-' . $this->method => $admin_ajax_url . '?action=art_settings_htmx',
			'hx-vals'             => wp_json_encode( $values ),
		];

		if ( '' !== $this->hx_target ) {
			$attrs['hx-target'] = $this->hx_target;
		}

		if ( '' !== $this->hx_swap ) {
			$attrs['hx-swap'] = $this->hx_swap;
		}

		if ( '' !== $this->confirm ) {
			$attrs['hx-confirm'] = $this->confirm;
		}

		return $attrs;
	}


	public function get_template_name(): string {

		return 'button';
	}


	public function sanitize( mixed $value ): null {

		return null;
	}
}
