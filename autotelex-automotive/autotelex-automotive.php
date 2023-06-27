<?php
/**
 * Plugin Name: Autotelex Automotive
 * Description: A plugin for synchronizing autotelex data to a WordPress website running the Automotive WordPress theme.
 * Plugin URI: https://github.com/KiOui/autotelex-automotive
 * Version: 1.0.0
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

if ( true || is_plugin_active( 'automotive/index.php' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-aacore.php';
	$GLOBALS['AACore'] = AACore::instance();
} else {
	/**
	 * Echo an admin notice about the activation of the listing plugin.
	 *
	 * @return void
	 */
	function aa_admin_notice_automotive_inactive(): void {
		if ( is_admin() && current_user_can( 'edit_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( __( 'Autotelex Automotive requires the Automotive plugin to be active. Please activate the Automotive plugin to use the Autotelex Automotive plugin', 'autotelex-automotive' ) ) . '</p></div>';
		}
	}
	add_action( 'admin_notices', 'aa_admin_notice_automotive_inactive' );
}
