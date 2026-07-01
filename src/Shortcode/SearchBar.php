<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Api\VehicleRestController;

class SearchBar extends Shortcode {
	public function __construct() {
	}

	/**
	 * Shortcode callback. Must return HTML, never echo.
	 *
	 * @return string
	 */
	public function render(): string {
		$this->enqueueAssets();

		ob_start();
		include $this->getViewsPath( 'search-bar.php' );
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		wp_enqueue_style(
			'tda-search-bar',
			$this->getAssetsUrl( 'css/search-bar.css' ),
			[],
			filemtime( $this->getAssetsPath( 'css/search-bar.css' ) )
		);

		wp_enqueue_script(
			'tda-search-bar-app',
			$this->getAssetsUrl( 'js/search-bar/app.js' ),
			[],
			filemtime( $this->getAssetsPath( 'js/search-bar/app.js' ) ),
			true
		);

		wp_enqueue_script(
			'tda-search-bar-anime',
			$this->getAssetsUrl( 'js/vendor/anime.umd.min.js' ),
			[],
			filemtime( $this->getAssetsPath( 'js/vendor/anime.umd.min.js' ) ),
			true
		);

		wp_enqueue_script(
			'tda-search-bar-animate',
			$this->getAssetsUrl( 'js/search-bar/animate.js' ),
			[ 'tda-search-bar-anime' ],
			filemtime( $this->getAssetsPath( 'js/search-bar/animate.js' ) ),
			true
		);

		wp_localize_script( 'tda-search-bar-app', 'tdaSearchBar', [
			'endpoint' => rest_url( VehicleRestController::REST_NAMESPACE . VehicleRestController::REST_ROUTE ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );
	}
}