<?php

namespace Crear\TdaCf\Shortcode;

use Crear\TdaCf\Shared\Config;

class Shortcode {
    public function getAssetsUrl( string $fileName ): string {
        return Config::assetsUrl() . $fileName;
    }

    public function getAssetsPath( string $fileName ): string {
        return Config::assetsPath() . $fileName;
    }

    public function getViewsPath( string $fileName ): string {
        return Config::viewsPath() . $fileName;
    }
}