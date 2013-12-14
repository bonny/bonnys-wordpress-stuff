<?php

// Set constants to detect if site is local dev, public dev, or prod
define("EP_DEV_IS_PUBLIC", (strpos($_SERVER["HTTP_HOST"], "-dev.earthpeople.se") !== false));
define("EP_DEV_IS_LOCAL", ($_SERVER["SERVER_ADDR"] === "127.0.0.1"));
define('WP_LOCAL_DEV', (EP_DEV_IS_LOCAL || EP_DEV_IS_PUBLIC) ? true : false);

if ( EP_DEV_IS_PUBLIC || EP_DEV_IS_LOCAL ) {

	// If local private or public dev

	// Always debug when on local
	define('WP_DEBUG', true);
	define('JETPACK_DEV_DEBUG', true);

	define('DB_NAME', '');
	define('DB_USER', '');
	define('DB_PASSWORD', '');
	define('DB_HOST', 'localhost');
	
} else {

	define('DB_NAME', '');
	define('DB_USER', '');
	define('DB_PASSWORD', '');
	define('DB_HOST', 'localhost');

}

// Dynamically set home and siteurls
define('WP_HOME', "http://" . $_SERVER["SERVER_NAME"] . "");
define('WP_SITEURL', "http://" . $_SERVER["SERVER_NAME"] . "");

// Use /assets/ instead of /wp-content/, so whole wp can be excluded
define( 'WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets' );
define( 'WP_CONTENT_URL', "http://" . $_SERVER["HTTP_HOST"] . '/assets');
