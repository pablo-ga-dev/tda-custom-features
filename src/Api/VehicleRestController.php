<?php

namespace Crear\TdaCf\Api;

use Crear\TdaCf\Api\Models\Vehicle;

class VehicleRestController {
	public const REST_NAMESPACE = 'tda/v1';
	public const REST_ROUTE = '/vehicles';

	private const POST_TYPE = 'vehiculo';

	private const SEARCHABLE_META_KEYS = [
		'marca',
		'modelo',
		'combustible',
		'potencia',
		'carroceria',
		'codigo_de_motor',
	];

	private Vehicle $vehicle;

	public function __construct( Vehicle $vehicle ) {
		$this->vehicle = $vehicle;
	}

	public function addVehicleEndpoint(): void {
		register_rest_route( self::REST_NAMESPACE, self::REST_ROUTE, [
			'methods' => 'GET',
			'callback' => [ $this, 'getVehicles' ],
			'permission_callback' => '__return_true',
			'args' => [
				'q' => [
					'type' => 'string',
					'required' => false,
				],
				'page' => [
					'type' => 'integer',
					'required' => false,
					'default' => 1,
				],
				'per_page' => [
					'type' => 'integer',
					'required' => false,
					'default' => 20,
				],
			],
		] );

		register_rest_route( self::REST_NAMESPACE, self::REST_ROUTE . '/(?P<id>\\d+)', [
			'methods' => 'GET',
			'callback' => [ $this, 'getVehicleById' ],
			'permission_callback' => '__return_true',
			'args' => [
				'id' => [
					'type' => 'integer',
					'required' => true,
				],
			],
		] );
	}

	public function getVehicles( \WP_REST_Request $request ): \WP_REST_Response {
		$queryText = sanitize_text_field( wp_unslash( (string) $request->get_param( 'q' ) ) );
		$page = max( 1, (int) $request->get_param( 'page' ) );
		$perPage = min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) );

		$cacheKey = 'tda_vehicles_' . md5( wp_json_encode( [
			'q' => $queryText,
			'page' => $page,
			'per_page' => $perPage,
		] ) );

		$cached = get_transient( $cacheKey );
		if ( is_array( $cached ) ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$ids = $this->findVehicleIds( $queryText );
		$offset = ( $page - 1 ) * $perPage;
		$pagedIds = array_slice( $ids, $offset, $perPage + 1 );
		$hasMore = count( $pagedIds ) > $perPage;

		if ( $hasMore ) {
			$pagedIds = array_slice( $pagedIds, 0, $perPage );
		}

		$items = [];
		foreach ( $pagedIds as $vehicleId ) {
			$items[] = $this->vehicle->fromApi( [
				'id' => $vehicleId,
				'title' => get_the_title( $vehicleId ),
				'link' => get_permalink( $vehicleId ),
				'acf' => [
					'marca' => get_post_meta( $vehicleId, 'marca', true ),
					'modelo' => get_post_meta( $vehicleId, 'modelo', true ),
					'combustible' => get_post_meta( $vehicleId, 'combustible', true ),
					'potencia' => get_post_meta( $vehicleId, 'potencia', true ),
					'carroceria' => get_post_meta( $vehicleId, 'carroceria', true ),
					'codigo_de_motor' => get_post_meta( $vehicleId, 'codigo_de_motor', true ),
					'etiqueta_ambiental' => get_post_meta( $vehicleId, 'etiqueta_ambiental', true ),
				],
			] );
		}

		$response = [
			'items' => $items,
			'page' => $page,
			'per_page' => $perPage,
			'has_more' => $hasMore,
			'q' => $queryText,
		];

		set_transient( $cacheKey, $response, MINUTE_IN_SECONDS );

		return new \WP_REST_Response( $response, 200 );
	}

	public function getVehicleById( \WP_REST_Request $request ): \WP_REST_Response {
		$vehicleId = (int) $request->get_param( 'id' );

		if ( $vehicleId <= 0 || get_post_type( $vehicleId ) !== self::POST_TYPE || get_post_status( $vehicleId ) !== 'publish' ) {
			return new \WP_REST_Response( [
				'message' => 'Vehicle not found.',
			], 404 );
		}

		$item = $this->vehicle->fromApi( [
			'id' => $vehicleId,
			'title' => get_the_title( $vehicleId ),
			'link' => get_permalink( $vehicleId ),
			'acf' => [
				'marca' => get_post_meta( $vehicleId, 'marca', true ),
				'modelo' => get_post_meta( $vehicleId, 'modelo', true ),
				'combustible' => get_post_meta( $vehicleId, 'combustible', true ),
				'potencia' => get_post_meta( $vehicleId, 'potencia', true ),
				'carroceria' => get_post_meta( $vehicleId, 'carroceria', true ),
				'codigo_de_motor' => get_post_meta( $vehicleId, 'codigo_de_motor', true ),
				'etiqueta_ambiental' => get_post_meta( $vehicleId, 'etiqueta_ambiental', true ),
			],
		] );

		return new \WP_REST_Response( [
			'item' => $item,
		], 200 );
	}

	private function findVehicleIds( string $queryText ): array {
		$baseArgs = [
			'post_type' => self::POST_TYPE,
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters' => true,
		];

		if ( $queryText === '' ) {
			$query = new \WP_Query( $baseArgs );
			return is_array( $query->posts ) ? array_map( 'intval', $query->posts ) : [];
		}

		$titleQuery = new \WP_Query( array_merge( $baseArgs, [
			's' => $queryText,
		] ) );

		$metaQuery = [ 'relation' => 'OR' ];
		foreach ( self::SEARCHABLE_META_KEYS as $metaKey ) {
			$metaQuery[] = [
				'key' => $metaKey,
				'value' => $queryText,
				'compare' => 'LIKE',
			];
		}

		$metadataQuery = new \WP_Query( array_merge( $baseArgs, [
			'meta_query' => $metaQuery,
		] ) );

		$mergedIds = array_unique( array_merge(
			is_array( $titleQuery->posts ) ? $titleQuery->posts : [],
			is_array( $metadataQuery->posts ) ? $metadataQuery->posts : []
		) );

		usort( $mergedIds, static function ( $leftId, $rightId ) {
			return strcasecmp( get_the_title( (int) $leftId ), get_the_title( (int) $rightId ) );
		} );

		return array_map( 'intval', $mergedIds );
	}
}