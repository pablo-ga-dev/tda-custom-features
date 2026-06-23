<?php

namespace Crear\TdaCf\Cli;

use Crear\TdaCf\Api\VehicleApiClient;

class TdaCommand {
	private VehicleApiClient $vehicleApiClient;

	public function __construct( VehicleApiClient $vehicleApiClient ) {
		$this->vehicleApiClient = $vehicleApiClient;
	}

	/**
	 * Obtiene un vehículo por ID.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : ID del vehículo.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tda vehicle 42
	 */
	public function vehicle( array $args ): void {
		$id = (int) ( $args[0] ?? 0 );
		$result = $this->vehicleApiClient->getVehicleById( $id );
		call_user_func( [ 'WP_CLI', 'print_value' ], $result );
	}
}
