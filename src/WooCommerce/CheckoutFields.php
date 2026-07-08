<?php

namespace Crear\TdaCf\WooCommerce;

class CheckoutFields {
	private const FIELD_KEY = 'billing_dni';
	private const BLOCK_FIELD_ID = 'tda-custom-features/dni';
	private const BLOCK_BILLING_META_KEY = '_wc_billing/tda-custom-features/dni';
	private const BLOCK_SHIPPING_META_KEY = '_wc_shipping/tda-custom-features/dni';

	public function init(): void {
		add_filter( 'woocommerce_checkout_fields', [ $this, 'addDniField' ] );
		add_action( 'woocommerce_after_checkout_validation', [ $this, 'validateDniField' ], 10, 2 );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'saveDniField' ], 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'renderAdminDniField' ] );
		add_action( 'woocommerce_init', [ $this, 'registerBlockDniField' ], 20 );

		if ( did_action( 'woocommerce_init' ) ) {
			$this->registerBlockDniField();
		}
	}

	public function registerBlockDniField(): void {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}

		try {
			woocommerce_register_additional_checkout_field( [
				'id' => self::BLOCK_FIELD_ID,
				'label' => __( 'DNI', 'tda-custom-features' ),
				'location' => 'address',
				'type' => 'text',
				'required' => true,
				'attributes' => [
					'autocomplete' => 'off',
					'maxLength' => 20,
				],
				'sanitize_callback' => static function ( $value, $field ) {
					return sanitize_text_field( wp_unslash( (string) $value ) );
				},
				'validate_callback' => static function ( $value, $field ) {
					if ( sanitize_text_field( wp_unslash( (string) $value ) ) === '' ) {
						return new \WP_Error( 'billing_dni_required', __( 'El campo DNI es obligatorio.', 'tda-custom-features' ) );
					}
				},
			] );
		} catch (\Throwable $e) {
			// Avoid breaking checkout if another registration path already added the field.
		}
	}

	/**
	 * @param array<string, mixed> $fields
	 * @return array<string, mixed>
	 */
	public function addDniField( array $fields ): array {
		if ( ! isset( $fields['billing'] ) || ! is_array( $fields['billing'] ) ) {
			$fields['billing'] = [];
		}

		$fields['billing'][ self::FIELD_KEY ] = [
			'type' => 'text',
			'label' => __( 'DNI', 'tda-custom-features' ),
			'placeholder' => __( 'Introduce tu DNI', 'tda-custom-features' ),
			'required' => true,
			'class' => [ 'form-row-wide' ],
			'priority' => 125,
			'clear' => true,
		];

		return $fields;
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function validateDniField( array $data, \WP_Error $errors ): void {
		$dni = isset( $data[ self::FIELD_KEY ] ) ? sanitize_text_field( wp_unslash( (string) $data[ self::FIELD_KEY ] ) ) : '';

		if ( $dni === '' ) {
			$errors->add( 'billing_dni_required', __( 'El campo DNI es obligatorio.', 'tda-custom-features' ) );
		}
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function saveDniField( \WC_Order $order, array $data ): void {
		$dni = isset( $data[ self::FIELD_KEY ] ) ? sanitize_text_field( wp_unslash( (string) $data[ self::FIELD_KEY ] ) ) : '';

		if ( $dni === '' ) {
			return;
		}

		$order->update_meta_data( self::FIELD_KEY, $dni );
	}

	public function renderAdminDniField( \WC_Order $order ): void {
		$dni = (string) $order->get_meta( self::FIELD_KEY );
		if ( $dni === '' ) {
			$dni = (string) $order->get_meta( self::BLOCK_BILLING_META_KEY );
		}
		if ( $dni === '' ) {
			$dni = (string) $order->get_meta( self::BLOCK_SHIPPING_META_KEY );
		}

		if ( $dni === '' ) {
			return;
		}

		echo '<p><strong>' . esc_html__( 'DNI', 'tda-custom-features' ) . ':</strong> ' . esc_html( $dni ) . '</p>';
	}
}