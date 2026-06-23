<?php

namespace Crear\TdaCf\Api;

use Crear\TdaCf\Api\Models\Vehicle;
use Exception;

class VehicleApiClient {
	const REST_BASE = 'wp/v2/vehiculos';

	private Vehicle $vehicleModel;

	public function __construct( Vehicle $vehicleModel ) {
		$this->vehicleModel = $vehicleModel;
	}

	/**
	 * Summary of getVehicleById
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function getVehicleById( int $id ): array {
		$data = $this->maybeGetCachedVehicle( $id );

		if ( is_null( $data ) ) {
			$data = $this->request( $id );
			$this->cacheVehicle( $id, $data );
		}

		return $data;
	}

	private function request( int $id ): array {
		$request = new \WP_REST_Request( 'GET', '/' . self::REST_BASE . '/' . $id );
		$response = rest_do_request( $request );

		if ( $response instanceof \WP_Error ) {
			throw new Exception( $response->get_error_message() );
		}

		if ( method_exists( $response, 'get_status' ) && $response->get_status() >= 400 ) {
			throw new Exception( 'Vehicle API returned HTTP error ' . (int) $response->get_status() );
		}

		$data = $response->get_data();
		if ( ! is_array( $data ) ) {
			throw new Exception( 'Vehicle API returned invalid payload.' );
		}

		return $this->vehicleModel->fromApi( $data );
	}

	private function maybeGetCachedVehicle( int $id ): array|null {
		$cached = get_transient( "tda_vehicle_{$id}" );
		return is_array( $cached ) ? $cached : null;
	}

	private function cacheVehicle( int $id, array $vehicle ): void {
		set_transient( "tda_vehicle_{$id}", $vehicle, HOUR_IN_SECONDS );
	}
}
