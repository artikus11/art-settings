<?php

namespace Art\Settings\Tests\Support;

use Art\Settings\Repositories\SettingsRepository;
use Art\Settings\SettingsManager;

class TestableSettingsManager extends SettingsManager {

	public function set_repository( SettingsRepository $repository ): void {

		$this->repository = $repository;
	}


	public function run_process_update( string $menu_slug, string $current_tab ): void {

		$this->process_update( $menu_slug, $current_tab );
	}


	public function run_process_reset( string $menu_slug, string $current_tab ): void {

		$this->process_reset( $menu_slug, $current_tab );
	}


	protected function redirect( string $menu_slug, string $current_tab, string $status_flag ): void {
	}
}
