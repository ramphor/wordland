<?php
/**
 * Plugin Name: WordLand
 * Plugin URI: https://github.com/ramphor
 * Author: Puleeno Nguyen
 * Author URI: https://puleeno.com
 * Version: 1.0.0
 * Description: The real estate plugin for WordPress: Realty, Property management
 * Tag: realty, real estate, property
 */

define( 'WORDLAND_PLUGIN_FILE', __FILE__ );

if ( ! class_exists( 'WordLand' ) ) {
	require_once dirname( WORDLAND_PLUGIN_FILE ) . '/includes/class-wordland.php';
}

if ( ! function_exists( 'wordland' ) ) {
	function wordland() {
		return WordLand::instance();
	}
}

$GLOBALS['wordland'] = wordland();
