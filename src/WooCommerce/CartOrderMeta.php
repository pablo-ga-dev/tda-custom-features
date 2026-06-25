<?php

namespace Crear\TdaCf\WooCommerce;

class CartOrderMeta {
	public function renderCartItemMeta( array $itemData, array $cartItem ): array {
		if ( empty( $cartItem['tda_form_submission'] ) ) {
			return $itemData;
		}

		$clientData = isset( $cartItem['tda_client_data'] ) && is_array( $cartItem['tda_client_data'] ) ? $cartItem['tda_client_data'] : [];
		$vehicleData = isset( $cartItem['tda_vehicle_data'] ) && is_array( $cartItem['tda_vehicle_data'] ) ? $cartItem['tda_vehicle_data'] : [];
		$files = isset( $cartItem['tda_uploaded_files'] ) && is_array( $cartItem['tda_uploaded_files'] ) ? $cartItem['tda_uploaded_files'] : [];

		foreach ( $clientData as $label => $value ) {
			if ( $value === '' ) {
				continue;
			}

			$itemData[] = [
				'name' => sprintf( 'Cliente - %s', $label ),
				'value' => (string) $value,
			];
		}

		foreach ( $vehicleData as $label => $value ) {
			if ( $value === '' ) {
				continue;
			}

			$itemData[] = [
				'name' => sprintf( 'Vehiculo - %s', $label ),
				'value' => (string) $value,
			];
		}

		if ( ! empty( $files ) ) {
			$links = [];
			foreach ( $files as $file ) {
				if ( empty( $file['url'] ) || empty( $file['name'] ) ) {
					continue;
				}

				$links[] = sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( (string) $file['url'] ),
					esc_html( (string) $file['name'] )
				);
			}

			if ( ! empty( $links ) ) {
				$itemData[] = [
					'name' => 'Adjuntos',
					'value' => implode( ', ', $links ),
				];
			}
		}

		return $itemData;
	}

	public function storeOrderItemMeta( \WC_Order_Item_Product $item, string $cartItemKey, array $values, \WC_Order $order ): void {
		if ( empty( $values['tda_form_submission'] ) ) {
			return;
		}

		$clientData = isset( $values['tda_client_data'] ) && is_array( $values['tda_client_data'] ) ? $values['tda_client_data'] : [];
		$vehicleData = isset( $values['tda_vehicle_data'] ) && is_array( $values['tda_vehicle_data'] ) ? $values['tda_vehicle_data'] : [];
		$files = isset( $values['tda_uploaded_files'] ) && is_array( $values['tda_uploaded_files'] ) ? $values['tda_uploaded_files'] : [];

		foreach ( $clientData as $label => $value ) {
			if ( $value !== '' ) {
				$item->add_meta_data( sprintf( 'Cliente - %s', $label ), (string) $value, true );
			}
		}

		foreach ( $vehicleData as $label => $value ) {
			if ( $value !== '' ) {
				$item->add_meta_data( sprintf( 'Vehiculo - %s', $label ), (string) $value, true );
			}
		}

		if ( ! empty( $files ) ) {
			$fileList = [];
			foreach ( $files as $file ) {
				if ( empty( $file['url'] ) || empty( $file['name'] ) ) {
					continue;
				}
				$fileList[] = sprintf( '%s: %s', (string) $file['name'], esc_url_raw( (string) $file['url'] ) );
			}

			if ( ! empty( $fileList ) ) {
				$item->add_meta_data( 'Adjuntos', implode( "\n", $fileList ), true );
			}
		}
	}
}
