<?php

namespace Crear\TdaCf\Shortcode;

class TagImg extends Shortcode {
	public function __construct() {
	}

	/**
	 * Shortcode callback. Must return HTML, never echo.
	 *
	 * @return string
	 */
	public function render(): string {
		$this->enqueueAssets();
		$imgUrl = $this->getTagImage();

		ob_start();
		include $this->getViewsPath('tag-img.php');
		return ob_get_clean();
	}

	private function enqueueAssets(): void {
		wp_enqueue_style(
			'tda-tag-img',
			$this->getAssetsUrl('css/tag-img.css'),
			[],
			filemtime( $this->getAssetsPath('css/tag-img.css') )
		);
	}

	private function getTagImage(): string {
		$postId = get_the_ID();

		if ( ! $postId) {
			return '';
		}

		$tagId = get_post_meta( $postId, 'etiqueta_ambiental', true );

		if( ! $tagId ) {
			return '';
		}
		$product = wc_get_product( $tagId );

		if ( ! $product || ! $product instanceof \WC_Product ) {
			return '';
		}
		$image_id = $product->get_image_id();

		If ( ! $image_id ) {
			return '';
		}

		return wp_get_attachment_url( $image_id );
	}
}