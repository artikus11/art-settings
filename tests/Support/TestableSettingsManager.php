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


	public function run_move_submenu_last( string $parent_slug, string $menu_slug ): void {

		$this->move_submenu_last( $parent_slug, $menu_slug );
	}


	public function run_move_submenu_first( string $parent_slug, string $menu_slug ): void {

		$this->move_submenu_first( $parent_slug, $menu_slug );
	}


	public function run_move_submenu_at( string $parent_slug, string $menu_slug, int $index ): void {

		$this->move_submenu_at( $parent_slug, $menu_slug, $index );
	}


	public function run_apply_position( array $menu ): void {

		$this->apply_position( $menu );
	}


	public function run_get_registration_position( mixed $position ): int|float|null {

		return $this->get_registration_position( $position );
	}

}
