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
		 * Add actions and filters.
		 */
		public function actions_and_filters(): void {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 99 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		/**
		 * Add Autotelex Automotive menu page.
		 */
		public function add_menu_page(): void {
			add_menu_page(
				esc_html__( 'Autotelex Automotive', 'autotelex-automotive' ),
				esc_html__( 'Autotelex Automotive', 'autotelex-automotive' ),
				'edit_plugins',
				'autotelex_automotive_admin_menu',
				null,
				'dashicons-car',
				56
			);
			add_submenu_page(
				'autotelex_automotive_admin_menu',
				esc_html__( 'Autotelex Automotive settings', 'autotelex-automotive' ),
				esc_html__( 'Dashboard', 'autotelex-automotive' ),
				'edit_plugins',
				'autotelex_automotive_admin_menu',
				array( $this, 'menu_dashboard_callback' )
			);
		}

		/**
		 * Register Autotelex Automotive settings.
		 */
		public function register_settings(): void {
			register_setting(
				'autotelex_automotive_settings',
				'autotelex_automotive_settings',
				array( $this, 'validate_settings' )
			);
			add_settings_section(
				'authentication_settings',
				__( 'Authentication', 'autotelex-automotive' ),
				array( $this, 'authentication_settings_callback' ),
				'autotelex_automotive_settings'
			);
			add_settings_field(
				'authentication_settings_username',
				__( 'Username', 'autotelex-automotive' ),
				array( $this, 'authentication_settings_username_renderer' ),
				'autotelex_automotive_settings',
				'authentication_settings'
			);
			add_settings_field(
				'authentication_settings_password',
				__( 'Password', 'autotelex-automotive' ),
				array( $this, 'authentication_settings_password_renderer' ),
				'autotelex_automotive_settings',
				'authentication_settings'
			);
		}

		/**
		 * Validate and sanitize settings before saving.
		 *
		 * @param array $input Non sanitized settings.
		 *
		 * @return array Sanitized and validated settings.
		 */
		public function validate_settings( array $input ): array {
			$output                                     = array();
			$output['authentication_settings_username'] = sanitize_text_field( (string) $input['authentication_settings_username'] );
			$output['authentication_settings_password'] = sanitize_text_field( (string) $input['authentication_settings_password'] );
			return $output;
		}

		/**
		 * Print the authentication settings header.
		 *
		 * @return void
		 */
		public function authentication_settings_callback(): void {
			echo esc_html( __( 'Authentication settings', 'autotelex-automotive' ) );
		}

		/**
		 * Render the username field.
		 *
		 * @return void
		 */
		public function authentication_settings_username_renderer(): void {
			$options = get_option( 'autotelex_automotive_settings' ); ?>
			<input type='text' name='autotelex_automotive_settings[authentication_settings_username]' value="<?php echo esc_attr( $options['authentication_settings_username'] ); ?>">
			<?php
		}

		/**
		 * Render the password field.
		 *
		 * @return void
		 */
		public function authentication_settings_password_renderer(): void {
			$options = get_option( 'autotelex_automotive_settings' );
			?>
			<input type='text' name='autotelex_automotive_settings[authentication_settings_password]' value="<?php echo esc_attr( $options['authentication_settings_password'] ); ?>">
			<?php
		}

		/**
		 * Render the settings menu.
		 *
		 * @return void
		 */
		public function menu_dashboard_callback(): void {
			include_once AA_ABSPATH . 'views/autotelex-automotive-dashboard-view.php';
		}
	}
}
