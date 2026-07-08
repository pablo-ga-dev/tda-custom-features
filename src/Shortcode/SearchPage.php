<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Api\VehicleRestController;

class SearchPage extends Shortcode {
	/**
	 * @param array<string, mixed> $atts
	 */
	public function render( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'per_page' => 12,
		], $atts, 'tda_search_page' );

		$this->enqueueAssets();

		$perPage = max( 1, min( 50, (int) $atts['per_page'] ) );

		ob_start();
		include $this->getViewsPath( 'search-page.php' );
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		wp_enqueue_style(
			'tda-search-page',
			$this->getAssetsUrl( 'css/search-page.css' ),
			[],
			filemtime( $this->getAssetsPath( 'css/search-page.css' ) )
		);

		wp_enqueue_script(
			'tda-search-page-app',
			$this->getAssetsUrl( 'js/search-page/app.js' ),
			[],
			filemtime( $this->getAssetsPath( 'js/search-page/app.js' ) ),
			true
		);

		wp_localize_script( 'tda-search-page-app', 'tdaSearchPage', [
			'endpoint' => rest_url( VehicleRestController::REST_NAMESPACE . VehicleRestController::REST_ROUTE ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );
	}
}