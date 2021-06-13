<?php
/**
 * Plugin Name: WordLand
 * Plugin URI: https://github.com/ramphor
 * Author: Ramphor Premium
 * Author URI: https://puleeno.com
 * Version: 1.0.0.120
 * Description: The real estate plugin for WordPress: Realty, Property management
 * Tag: realty, real estate, property
 */

define( 'WORDLAND_PLUGIN_FILE', __FILE__ );

$composerAutoloader = sprintf( '%s/vendor/autoload.php', dirname( __FILE__ ) );
if ( file_exists( $composerAutoloader ) ) {
	require_once $composerAutoloader;
}

if ( ! class_exists( WordLand::class ) ) {
	error_log( 'WordLand is not working. The required features are missing.' );
	return;
}

if ( ! function_exists( 'wordland' ) ) {
	function wordland() {
		return WordLand::instance();
	}
}

$GLOBALS['wordland'] = wordland();
