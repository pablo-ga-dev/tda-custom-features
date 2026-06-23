<?php

namespace Crear\TdaCf\Core;

use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use Throwable;

use Crear\TdaCf\Cli\TdaCommand;
use Crear\TdaCf\Api\ApiServiceProvider;
use Crear\TdaCf\Shortcode\ShortcodeServiceProvider;

class Plugin {
	/** @var self|null */
	private static $instance = null;

	/** @var ContainerInterface */
	private ContainerInterface $container;

	public function __construct() {
		$builder = new ContainerBuilder();

		$builder->addDefinitions( ApiServiceProvider::definitions() );
		$builder->addDefinitions( ShortcodeServiceProvider::definitions() );

		$this->container = $builder->build();
	}

	/**
	 * A singleton method to get the unique plugin instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Returns a dependency from the container.
	 *
	 * @param string $id
	 * @return mixed|null
	 */
	public function get( string $id ) {
		try {
			return $this->container->get( $id );
		} catch (Throwable $e) {
			return null;
		}
	}

	/**
	 * Initialize plugin services and hooks.
	 *
	 * @return void
	 */
	public function run() {
		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			$command = $this->get( TdaCommand::class);
			if ( $command instanceof TdaCommand ) {
				call_user_func( [ 'WP_CLI', 'add_command' ], 'tda', $command );
			}
		}

		$apiProvider = $this->get( ApiServiceProvider::class);
		if ( $apiProvider instanceof ApiServiceProvider ) {
			$apiProvider->init();
		}

		$shortcodeProvider = $this->get( ShortcodeServiceProvider::class);
		if ( $shortcodeProvider instanceof ShortcodeServiceProvider ) {
			$shortcodeProvider->init();
		}
	}
}
