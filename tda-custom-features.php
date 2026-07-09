<?php

/* Plugin Name: TDA Custom Features
 * Description: Custom features for TDA.
 * Version: 1.2.0
 * Author: Crear & Co
 * Author URI: https://www.crear-digital.com/
 * License: GPL2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';

// Check if Composer's autoload file exists and include it.
if ( file_exists( $autoload ) ) {
	require_once $autoload;
	\Crear\TdaCf\Core\Plugin::instance()->run();
} else {
	add_action( 'admin_notices', static function () {
		echo '<div class="notice notice-error"><p>TDA Custom Features: falta vendor/autoload.php. Ejecuta composer install.</p></div>';
	} );
}
