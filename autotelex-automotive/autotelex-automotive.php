<?php
/**
 * Plugin Name: Autotelex Automotive
 * Description: A plugin for synchronizing autotelex data to a WordPress website running the Automotive WordPress theme.
 * Plugin URI: https://github.com/KiOui/autotelex-automotive
 * Version: 0.0.1
 * Author: Lars van Rhijn
 * Author URI: https://larsvanrhijn.nl/
 * Text Domain: autotelex-automotive
 * Domain Path: /languages/
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AA_PLUGIN_FILE' ) ) {
	define( 'AA_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'AA_PLUGIN_URI' ) ) {
	define( 'AA_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

require_once dirname( __FILE__ ) . '/includes/class-aacore.php';

$GLOBALS['AACore'] = AACore::instance();
