<?php

namespace Art\Settings\Tests\Support;

use Art\Settings\Repositories\SettingsRepository;

class InMemorySettingsRepository extends SettingsRepository {

	public function __construct( array $data = [] ) {

		parent::__construct( 'test_option' );
		$this->cache = $data;
	}


	public function update( array $data ): bool {

		$this->cache = $data;

		return true;
	}


	public function reset(): bool {

		$this->cache = [];

		return true;
	}
}
