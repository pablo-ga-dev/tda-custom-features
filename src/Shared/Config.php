<?php

namespace Crear\TdaCf\Shared;

class Config {
    public const VERSION = '1.2.0';
    public static function pluginPath(): string {
        return plugin_dir_path( __FILE__ ) . '../../';
    }

    public static function pluginUrl(): string {
        return plugin_dir_url( __FILE__ ) . '../../';
    }

    public static function assetsPath(): string {
        return self::pluginPath() . 'assets/';
    }

    public static function assetsUrl(): string {
        return self::pluginUrl() . 'assets/';
    }

    public static function viewsPath(): string {
        return self::pluginPath() . 'views/';
    }

    public static function viewsUrl(): string {
        return self::pluginUrl() . 'views/';
    }
}
