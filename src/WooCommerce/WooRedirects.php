<?php

namespace Crear\TdaCf\WooCommerce;

class WooRedirects {
	public function maybeRedirect(): void {
		if ( is_admin() ) {
			return;
		}

		if ( strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) !== 'GET' ) {
			return;
		}

		if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) ) {
			return;
		}

		if ( is_cart() ) {
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		if ( is_shop() || is_product() || is_product_category() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}
}
