<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Api\VehicleRestController;

class TagImg {
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
		include plugin_dir_path( __FILE__ ) . '/../../views/tag-img.php';
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		$baseUrl = plugin_dir_url( __FILE__ ) . '../../assets/';
		$baseDir = plugin_dir_path( __FILE__ ) . '../../assets/';

		wp_enqueue_style(
			'tda-tag-img',
			$baseUrl . 'css/tag-img.css',
			[],
			filemtime( $baseDir . 'css/tag-img.css' )
		);
	}
}