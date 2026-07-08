<?php

namespace Crear\TdaCf\Wordpress;

use Crear\TdaCf\Core\ServiceProvider;
use function DI\autowire;
use Crear\TdaCf\Wordpress\AssetsManager;

class WordpressServiceProvider implements ServiceProvider {
	private AssetsManager $assetsManager;
	private AdminSettingsPage $adminSettingsPage;

	public static function definitions(): array {
		return [
			AssetsManager::class => autowire(),
			AdminSettingsPage::class => autowire(),
			self::class => autowire(),
		];
	}

	public function __construct( AssetsManager $assetsManager, AdminSettingsPage $adminSettingsPage ) {
		$this->assetsManager = $assetsManager;
		$this->adminSettingsPage = $adminSettingsPage;
	}

	public function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this->assetsManager, 'enqueueFrontendScripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this->assetsManager, 'enqueueFrontendStyles' ] );
		add_action( 'admin_menu', [ $this->adminSettingsPage, 'registerAdminMenu' ] );
		add_action( 'admin_init', [ $this->adminSettingsPage, 'registerSettings' ] );
	}
}