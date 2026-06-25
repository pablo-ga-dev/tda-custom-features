<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Api\VehicleRestController;

class ClientDataForm extends Shortcode {
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
		include $this->getViewsPath( 'client-data-form.php' );
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		wp_enqueue_style(
			'tda-client-data-form',
			$this->getAssetsUrl( 'css/client-data-form.css' ),
			[],
			filemtime( $this->getAssetsPath( 'css/client-data-form.css' ) )
		);

		wp_enqueue_script(
			'tda-client-data-form',
			$this->getAssetsUrl( 'js/client-data-form.js' ),
			[],
			filemtime( $this->getAssetsPath( 'js/client-data-form.js' ) ),
			true
		);

		wp_localize_script( 'tda-client-data-form', 'tdaClientDataForm', [
			'endpoint' => rest_url( VehicleRestController::REST_NAMESPACE . VehicleRestController::REST_ROUTE ),
			'submitEndpoint' => rest_url( VehicleRestController::REST_NAMESPACE . '/client-data-form/submit' ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'vehicleId' => get_the_ID(),
		] );
	}
}