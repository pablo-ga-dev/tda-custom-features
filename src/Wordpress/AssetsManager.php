<?php

namespace Crear\TdaCf\Wordpress;

use Crear\TdaCf\Shared\Config;

class AssetsManager {
    public function enqueueFrontendScripts(): void {
        wp_enqueue_script(
            'tda-anime-js',
            Config::assetsUrl() . 'vendor/js/anime.umd.min.js',
            [],
            Config::VERSION,
            true
        );
    }

    public function enqueueFrontendStyles(): void {
        wp_enqueue_style(
            'tda-checkout-css',
            Config::assetsUrl() . 'css/checkout.css',
            [],
            Config::VERSION
        );
    }
}