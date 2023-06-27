<?php
/**
 * Settings class
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AASettings' ) ) {
	/**
	 * Autotelex Automotive Settings class
	 *
	 * @class AASettings
	 */
	class AASettings {


		/**
		 * The single instance of the class
		 *
		 * @var AASettings|null
		 */
		protected static ?AASettings $instance = null;

		/**
		 * The instance of the settings class.
		 *
		 * @var Settings
		 */
		private Settings $settings;

		/**
		 * The instance of the settings group class.
		 *
		 * @var SettingsGroup
		 */
		private SettingsGroup $settings_group;

		/**
		 * Autotelex Automotive Settings instance
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return AASettings
		 */
		public static function instance(): AASettings {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * AASettings constructor.
		 */
		public function __construct() {
			include_once AA_ABSPATH . 'includes/settings/settings-init.php';
			include_once AA_ABSPATH . 'includes/aa-settings-config.php';
			initialize_settings_fields();

			$this->settings = SettingsFactory::create_settings( aa_get_settings_config() );
			$this->settings->initialize_settings();
			$this->settings_group = SettingsFactory::create_settings_group( aa_get_settings_screen_config() );

			$this->actions_and_filters();
		}

		/**
		 * Register the settings group settings.
		 *
		 * @return void
		 */
		public function register_settings() {
			$this->settings_group->register( $this->settings );
		}

		/**
		 * Get the instance of the settings class.
		 *
		 * @return Settings The instance of the settings class.
		 */
		public function get_settings(): Settings {
			return $this->settings;
		}

		/**
		 * Add actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'admin_init', array( $this->settings, 'register' ) );
			add_action( 'admin_menu', array( $this, 'register_settings' ) );
			add_action( 'current_screen', array( $this, 'do_custom_actions' ), 99 );
		}

		/**
		 * Execute custom actions.
		 */
		public function do_custom_actions() {
			if ( get_current_screen()->id === 'toplevel_page_aa_admin_menu' ) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- We are passing the nonce to nonce verification.
				if ( isset( $_POST['option_page'] ) && isset( $_POST['action'] ) && 'update' == $_POST['action'] && 'autotelex_automotive_settings' === $_POST['option_page'] && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'autotelex_automotive_settings-options' ) ) {
					$this->settings->update_settings( $_POST );
					$this->settings->save_settings();
					wp_redirect( '/wp-admin/admin.php?page=aa_admin_menu' );
					exit;
				}
			}
		}
	}
}
