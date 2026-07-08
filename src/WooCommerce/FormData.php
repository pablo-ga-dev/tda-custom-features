<?php

namespace Crear\TdaCf\WooCommerce;

class FormData {
	public function extractVehicleData( int $vehiclePostId, array $payload ): array {
		return [
			'ID vehiculo' => (string) $vehiclePostId,
			'Titulo vehiculo' => get_the_title( $vehiclePostId ),
			'Matricula' => isset( $payload['vehicle_plate'] ) ? sanitize_text_field( wp_unslash( (string) $payload['vehicle_plate'] ) ) : '',
			'Observaciones' => isset( $payload['vehicle_notes'] ) ? sanitize_textarea_field( wp_unslash( (string) $payload['vehicle_notes'] ) ) : '',
			'Marca' => (string) get_post_meta( $vehiclePostId, 'marca', true ),
			'Modelo' => (string) get_post_meta( $vehiclePostId, 'modelo', true ),
			'Combustible' => (string) get_post_meta( $vehiclePostId, 'combustible', true ),
			'Potencia' => (string) get_post_meta( $vehiclePostId, 'potencia', true ),
			'Carroceria' => (string) get_post_meta( $vehiclePostId, 'carroceria', true ),
			'Codigo de motor' => (string) get_post_meta( $vehiclePostId, 'codigo_de_motor', true ),
		];
	}

	public function uploadClientFiles( array $filesBag ): array {
		$documentFields = [
			'vehicle_technical_sheet' => 'Ficha tecnica del vehiculo',
			'vehicle_registration_permit' => 'Permiso de circulacion',
		];

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$uploaded = [];

		foreach ( $documentFields as $fieldName => $documentLabel ) {
			if ( empty( $filesBag[ $fieldName ] ) || ! is_array( $filesBag[ $fieldName ] ) ) {
				$this->debugLog( 'uploadClientFiles:missing_field', [ 'field' => $fieldName ] );
				continue;
			}

			$normalizedFiles = $this->normalizeUploadedFiles( $filesBag[ $fieldName ] );
			foreach ( $normalizedFiles as $singleFile ) {
				if ( empty( $singleFile['name'] ) || ( $singleFile['error'] ?? UPLOAD_ERR_NO_FILE ) !== UPLOAD_ERR_OK ) {
					$this->debugLog( 'uploadClientFiles:skip_file', [
						'field' => $fieldName,
						'name' => (string) ( $singleFile['name'] ?? '' ),
						'error' => (int) ( $singleFile['error'] ?? UPLOAD_ERR_NO_FILE ),
					] );
					continue;
				}

				$moveResult = wp_handle_upload( $singleFile, [ 'test_form' => false ] );
				if ( ! is_array( $moveResult ) || isset( $moveResult['error'] ) || empty( $moveResult['file'] ) ) {
					continue;
				}

				$attachment = [
					'post_mime_type' => (string) ( $moveResult['type'] ?? '' ),
					'post_title' => sanitize_text_field( pathinfo( $singleFile['name'], PATHINFO_FILENAME ) ),
					'post_content' => '',
					'post_status' => 'inherit',
				];

				$attachmentId = wp_insert_attachment( $attachment, (string) $moveResult['file'] );
				if ( ! is_wp_error( $attachmentId ) ) {
					$metadata = wp_generate_attachment_metadata( $attachmentId, (string) $moveResult['file'] );
					wp_update_attachment_metadata( $attachmentId, $metadata );
				} else {
					$this->debugLog( 'uploadClientFiles:attachment_error', [
						'field' => $fieldName,
						'name' => (string) $singleFile['name'],
						'message' => $attachmentId->get_error_message(),
					] );
				}

				$uploaded[] = [
					'name' => $documentLabel . ' - ' . sanitize_file_name( (string) $singleFile['name'] ),
					'url' => (string) ( $moveResult['url'] ?? '' ),
					'attachment_id' => is_wp_error( $attachmentId ) ? 0 : (int) $attachmentId,
					'document_type' => $fieldName,
				];
			}
		}

		return $uploaded;
	}

	private function normalizeUploadedFiles( array $fileData ): array {
		$normalized = [];

		if ( isset( $fileData['name'] ) && is_array( $fileData['name'] ) ) {
			foreach ( $fileData['name'] as $index => $name ) {
				$normalized[] = [
					'name' => sanitize_file_name( (string) $name ),
					'type' => (string) ( $fileData['type'][ $index ] ?? '' ),
					'tmp_name' => (string) ( $fileData['tmp_name'][ $index ] ?? '' ),
					'error' => (int) ( $fileData['error'][ $index ] ?? UPLOAD_ERR_NO_FILE ),
					'size' => (int) ( $fileData['size'][ $index ] ?? 0 ),
				];
			}

			return $normalized;
		}

		if ( isset( $fileData['name'] ) && is_string( $fileData['name'] ) ) {
			$normalized[] = [
				'name' => sanitize_file_name( (string) $fileData['name'] ),
				'type' => (string) ( $fileData['type'] ?? '' ),
				'tmp_name' => (string) ( $fileData['tmp_name'] ?? '' ),
				'error' => (int) ( $fileData['error'] ?? UPLOAD_ERR_NO_FILE ),
				'size' => (int) ( $fileData['size'] ?? 0 ),
			];
		}

		return $normalized;
	}

	private function debugLog( string $step, array $context = [] ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$message = '[TDA_CF_DEBUG] FormDataService::' . $step;
		if ( ! empty( $context ) ) {
			$encoded = wp_json_encode( $context );
			if ( is_string( $encoded ) ) {
				$message .= ' ' . $encoded;
			}
		}

		error_log( $message );
	}
}
