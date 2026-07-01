<?php

namespace Crear\TdaCf\Wordpress;

use Crear\TdaCf\Core\ServiceProvider;
use function DI\autowire;
use Crear\TdaCf\Wordpress\AssetsManager;

class WordpressServiceProvider implements ServiceProvider {
    private AssetsManager $assetsManager;
    
    public static function definitions(): array {
        return [
            AssetsManager::class => autowire(),
            self::class => autowire(),
        ];
    }

    public function __construct( AssetsManager $assetsManager ) {
        $this->assetsManager = $assetsManager;
    }

    public function init(): void {
        add_action( 'wp_enqueue_scripts', [ $this->assetsManager, 'enqueueFrontendScripts' ] );
    }
}