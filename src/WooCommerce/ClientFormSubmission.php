<?php

namespace Crear\TdaCf\WooCommerce;

use Throwable;

class ClientFormSubmission {
	public const REST_NAMESPACE = 'tda/v1';
	public const SUBMIT_ROUTE = '/client-data-form/submit';

	private FormData $formData;

	public function __construct( FormData $formData ) {
		$this->formData = $formData;
	}

	public function handleSubmit(): void {
		if ( ! function_exists( 'WC' ) || ! class_exists( 'WooCommerce' ) ) {
			$this->debugLog( 'handleSubmit:woocommerce_missing' );
			return;
		}

		if ( strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) !== 'POST' ) {
			return;
		}

		if ( ! $this->isClientDataFormPost( $_POST ) ) {
			$this->debugLog( 'handleSubmit:not_client_form_post' );
			return;
		}

		try {
			$result = $this->processSubmission( $_POST, $_FILES, false );
		} catch (Throwable $e) {
			$this->debugLog( 'handleSubmit:exception', [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			] );
			wc_add_notice( __( 'Error interno procesando el formulario.', 'tda-custom-features' ), 'error' );
			return;
		}

		if ( is_wp_error( $result ) ) {
			$this->debugLog( 'handleSubmit:wp_error', [
				'code' => $result->get_error_code(),
				'message' => $result->get_error_message(),
			] );
			wc_add_notice( $result->get_error_message(), 'error' );
			return;
		}

		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}

	public function registerRestRoutes(): void {
		register_rest_route( self::REST_NAMESPACE, self::SUBMIT_ROUTE, [
			'methods' => 'POST',
			'callback' => [ $this, 'submitFromJs' ],
			'permission_callback' => [ $this, 'validateRestNonce' ],
		] );
	}

	public function validateRestNonce( \WP_REST_Request $request ) {
		$nonce = (string) $request->get_header( 'X-WP-Nonce' );

		if ( $nonce === '' || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'tda_invalid_nonce', __( 'Nonce invalido.', 'tda-custom-features' ), [ 'status' => 403 ] );
		}

		return true;
	}

	public function submitFromJs( \WP_REST_Request $request ): \WP_REST_Response {
		$payload = $request->get_params();
		$files = $request->get_file_params();

		try {
			$result = $this->processSubmission( $payload, $files, true );
		} catch (Throwable $e) {
			$this->debugLog( 'submitFromJs:exception', [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			] );

			return new \WP_REST_Response( [
				'message' => __( 'Error interno procesando el formulario.', 'tda-custom-features' ),
			], 500 );
		}

		if ( is_wp_error( $result ) ) {
			$this->debugLog( 'submitFromJs:wp_error', [
				'code' => $result->get_error_code(),
				'message' => $result->get_error_message(),
			] );
			return new \WP_REST_Response( [
				'message' => $result->get_error_message(),
			], 400 );
		}

		return new \WP_REST_Response( [
			'message' => __( 'Etiqueta anadida al carrito con tus datos.', 'tda-custom-features' ),
			'redirect_url' => wc_get_checkout_url(),
		], 200 );
	}

	public function ensureCartItemUnique( array $cartItemData, int $productId, int $variationId, int $quantity ): array {
		if ( isset( $cartItemData['tda_form_submission'] ) ) {
			$cartItemData['tda_unique_key'] = md5( microtime( true ) . wp_rand() );
		}

		return $cartItemData;
	}

	private function isClientDataFormPost( array $payload ): bool {
		$required = [
			'client_name',
			'client_email',
			'client_phone',
			'client_nif',
			'vehicle_vin',
			'vehicle_plate',
		];

		foreach ( $required as $field ) {
			if ( ! array_key_exists( $field, $payload ) ) {
				return false;
			}
		}

		return true;
	}

	private function resolveVehiclePostId( array $payload, bool $preferPayload ): int {
		$vehiclePostId = (int) get_queried_object_id();

		if ( $preferPayload && isset( $payload['vehicle_id'] ) ) {
			$vehiclePostId = (int) $payload['vehicle_id'];
		}

		if ( $vehiclePostId <= 0 && isset( $payload['vehicle_id'] ) ) {
			$vehiclePostId = (int) $payload['vehicle_id'];
		}

		return $vehiclePostId;
	}

	private function processSubmission( array $payload, array $files, bool $preferPayloadVehicleId ) {
		if ( ! function_exists( 'WC' ) || ! class_exists( 'WooCommerce' ) ) {
			return new \WP_Error( 'tda_wc_missing', __( 'WooCommerce no esta disponible.', 'tda-custom-features' ) );
		}

		if ( empty( $payload['privacy_accept'] ) ) {
			return new \WP_Error( 'tda_privacy_required', __( 'Debes aceptar la politica de privacidad.', 'tda-custom-features' ) );
		}

		$vehiclePostId = $this->resolveVehiclePostId( $payload, $preferPayloadVehicleId );

		if ( $vehiclePostId <= 0 || get_post_type( $vehiclePostId ) !== 'vehiculo' ) {
			return new \WP_Error( 'tda_invalid_vehicle', __( 'Vehiculo no valido.', 'tda-custom-features' ) );
		}

		$productId = (int) get_post_meta( $vehiclePostId, 'etiqueta_ambiental', true );
        
		if ( $productId <= 0 ) {
			return new \WP_Error( 'tda_missing_tag', __( 'No se encontro la etiqueta para este vehiculo.', 'tda-custom-features' ) );
		}

		$product = wc_get_product( $productId );
		if ( ! $product || ! $product->is_purchasable() ) {
			return new \WP_Error( 'tda_unavailable_tag', __( 'La etiqueta asociada no esta disponible para compra.', 'tda-custom-features' ) );
		}

		$cartItemData = [
			'tda_client_data' => $this->formData->extractClientData( $payload ),
			'tda_vehicle_data' => $this->formData->extractVehicleData( $vehiclePostId, $payload ),
			'tda_uploaded_files' => $this->formData->uploadClientFiles( $files ),
			'tda_form_submission' => true,
		];

		$cart = $this->getWooCart();
		if ( ! $cart ) {
			return new \WP_Error( 'tda_cart_unavailable', __( 'No se pudo inicializar el carrito de WooCommerce.', 'tda-custom-features' ) );
		}

		$added = $cart->add_to_cart( $productId, 1, 0, [], $cartItemData );

		if ( ! $added ) {
			return new \WP_Error( 'tda_cart_add_failed', __( 'No se pudo anadir la etiqueta al carrito.', 'tda-custom-features' ) );
		}

		return [
			'product_id' => $productId,
		];
	}

	private function getWooCart() {
		if ( ! function_exists( 'WC' ) ) {
			$this->debugLog( 'getWooCart:wc_function_missing' );
			return null;
		}

		$woo = WC();
		if ( ! $woo ) {
			$this->debugLog( 'getWooCart:wc_instance_missing' );
			return null;
		}

		if ( function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}

		if ( ! isset( $woo->cart ) || ! $woo->cart ) {
			if ( method_exists( $woo, 'initialize_session' ) ) {
				$woo->initialize_session();
				$this->debugLog( 'getWooCart:initialize_session_called' );
			}

			if ( method_exists( $woo, 'initialize_cart' ) ) {
				$woo->initialize_cart();
				$this->debugLog( 'getWooCart:initialize_cart_called' );
			}
		}

		$isReady = isset( $woo->cart ) && $woo->cart;

		return $isReady ? $woo->cart : null;
	}

	private function debugLog( string $step, array $context = [] ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$message = '[TDA_CF_DEBUG] ' . $step;
		if ( ! empty( $context ) ) {
			$encoded = wp_json_encode( $context );
			if ( is_string( $encoded ) ) {
				$message .= ' ' . $encoded;
			}
		}

		error_log( $message );
	}
}
