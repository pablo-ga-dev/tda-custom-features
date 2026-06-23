<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Api\VehicleRestController;

class SearchBar {
	public function __construct() {
	}

	/**
	 * Shortcode callback. Must return HTML, never echo.
	 *
	 * @param array $atts
	 * @return string
	 */
	public function render( array $atts = [] ): string {
		$this->enqueueAssets();

		ob_start();
		include plugin_dir_path( __FILE__ ) . '/../../views/search-bar.php';
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		$baseUrl = plugin_dir_url( __FILE__ ) . '../../assets/';
		$baseDir = plugin_dir_path( __FILE__ ) . '../../assets/';

		wp_enqueue_style(
			'tda-search-bar',
			$baseUrl . 'css/search-bar.css',
			[],
			filemtime( $baseDir . 'css/search-bar.css' )
		);

		wp_enqueue_script(
			'tda-search-bar',
			$baseUrl . 'js/search-bar.js',
			[],
			filemtime( $baseDir . 'js/search-bar.js' ),
			true
		);

		wp_localize_script( 'tda-search-bar', 'tdaSearchBar', [
			'endpoint' => rest_url( VehicleRestController::REST_NAMESPACE . VehicleRestController::REST_ROUTE ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );
	}
}