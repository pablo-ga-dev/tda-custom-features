<?php

namespace Crear\TdaCf\Api\Models;

class Vehicle {
	/**
	 * @param array $data
	 * @return array
	 */
	public function fromApi( array $data ): array {
		$acf = isset( $data['acf'] ) && is_array( $data['acf'] ) ? $data['acf'] : [];
		$titleData = $data['title'] ?? null;

		if ( is_array( $titleData ) ) {
			$title = isset( $titleData['rendered'] ) ? (string) $titleData['rendered'] : null;
		} elseif ( is_scalar( $titleData ) ) {
			$title = (string) $titleData;
		} else {
			$title = null;
		}

		return [
			'id' => isset( $data['id'] ) ? (int) $data['id'] : null,
			'title' => $title,
			'url' => isset( $data['link'] ) ? esc_url_raw( (string) $data['link'] ) : null,
			'marca' => isset( $acf['marca'] ) ? (string) $acf['marca'] : null,
			'modelo' => isset( $acf['modelo'] ) ? (string) $acf['modelo'] : null,
			'combustible' => isset( $acf['combustible'] ) ? (string) $acf['combustible'] : null,
			'potencia' => isset( $acf['potencia'] ) ? (string) $acf['potencia'] : null,
			'carroceria' => isset( $acf['carroceria'] ) ? (string) $acf['carroceria'] : null,
			'codigo_de_motor' => isset( $acf['codigo_de_motor'] ) ? (string) $acf['codigo_de_motor'] : null,
			'etiqueta_ambiental' => isset( $acf['etiqueta_ambiental'] ) ? (int) $acf['etiqueta_ambiental'] : null,
		];
	}
}
