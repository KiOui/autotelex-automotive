<?php
/**
 * Settings configuration
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once AA_ABSPATH . 'includes/settings/conditions/class-fieldssetsettingscondition.php';

if ( ! function_exists( 'aa_get_settings_config' ) ) {
	/**
	 * Get the settings config.
	 *
	 * @return array The settings config.
	 */
	function aa_get_settings_config(): array {
		return array(
			'group_name' => 'autotelex_automotive_settings',
			'name' => 'autotelex_automotive_settings',
			'settings' => array(
				array(
					'type'    => 'text',
					'id'      => 'authentication_settings_username',
					'name'    => __( 'Username', 'autotelex-automotive' ),
					'can_be_null' => true,
					'hint'    => __( 'Autotelex username.', 'autotelex-automotive' ),
				),
				array(
					'type'    => 'text',
					'id'      => 'authentication_settings_password',
					'name'    => __( 'Password', 'autotelex-automotive' ),
					'can_be_null' => true,
					'hint'    => __( 'Autotelex password.', 'autotelex-automotive' ),
				),
				array(
					'type'    => 'bool',
					'id'      => 'rest_remove_listings_on_delete_call',
					'name'    => __( 'Remove listings from website', 'autotelex-automotive' ),
					'default' => true,
					'hint'    => __( 'Whether to remove listings from the website when removed from Autotelex.', 'autotelex-automotive' ),
				),
			),
		);
	}
}

if ( ! function_exists( 'aa_get_settings_screen_config' ) ) {
	/**
	 * Get the settings screen config.
	 *
	 * @return array The settings screen config.
	 */
	function aa_get_settings_screen_config(): array {
		return array(
			'page_title'        => esc_html__( 'Autotelex Automotive', 'autotelex-automotive' ),
			'menu_title'        => esc_html__( 'Autotelex Automotive', 'autotelex-automotive' ),
			'capability_needed' => 'edit_plugins',
			'menu_slug'         => 'aa_admin_menu',
			'icon'              => 'dashicons-car',
			'position'          => 56,
			'settings_pages' => array(
				array(
					'page_title'        => esc_html__( 'Autotelex Automotive Dashboard', 'autotelex-automotive' ),
					'menu_title'        => esc_html__( 'Dashboard', 'autotelex-automotive' ),
					'capability_needed' => 'edit_plugins',
					'menu_slug'         => 'aa_admin_menu',
					'renderer'          => function() {
						include_once AA_ABSPATH . 'views/autotelex-automotive-dashboard-view.php';
					},
					'settings_sections' => array(
						array(
							'id'       => 'authentication_settings',
							'name'     => __( 'Authentication settings', 'autotelex-automotive' ),
							'settings' => array(
								'authentication_settings_username',
								'authentication_settings_password',
							),
						),
						array(
							'id' => 'rest_settings',
							'name'     => __( 'REST settings', 'autotelex-automotive' ),
							'settings' => array(
								'rest_remove_listings_on_delete_call',
							),
						),
					),
				),
			),
		);
	}
}
