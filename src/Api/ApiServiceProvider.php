<?php
namespace Crear\TdaCf\Api;

use Crear\TdaCf\Core\ServiceProvider;
use Crear\TdaCf\Api\VehicleApiClient;
use Crear\TdaCf\Api\Models\Vehicle;
use function DI\autowire;

class ApiServiceProvider implements ServiceProvider {

	private VehicleRestController $vehicleRestController;

	/**
	 * Summary of definitions
	 * @return \DI\Definition\Helper\AutowireDefinitionHelper[]
	 */
	public static function definitions(): array {
		return [
			Vehicle::class => autowire(),
			VehicleApiClient::class => autowire(),
			VehicleRestController::class => autowire(),
			self::class => autowire(),
		];
	}

	public function __construct( VehicleRestController $vehicleRestController ) {
		$this->vehicleRestController = $vehicleRestController;
	}

	public function init(): void {
		add_action( 'rest_api_init', [ $this->vehicleRestController, 'addVehicleEndpoint' ] );
	}
}