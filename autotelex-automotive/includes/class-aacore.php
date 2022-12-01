<?php
/**
 * Autotelex Automotive Core
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-content/plugins/autotelex-automotive/includes/class-aarest.php';
require_once ABSPATH . 'wp-content/plugins/autotelex-automotive/includes/class-aasettings.php';

if ( ! class_exists( 'AACore' ) ) {
	/**
	 * Autotelex Automotive core class.
	 *
	 * @class AACore
	 */
	class AACore {


		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public string $version = '0.0.1';

		/**
		 * The single instance of the class
		 *
		 * @var AACore|null
		 */
		private static ?AACore $instance = null;

		/**
		 * Holds the AARest class.
		 *
		 * @var AARest
		 */
		private AARest $rest;

		/**
		 * Holds the AASettings class.
		 *
		 * @var AASettings
		 */
		private AASettings $settings;

		/**
		 * Autotelex Automotive Core
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return AACore
		 */
		public static function instance(): AACore {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->rest     = new AARest();
			$this->settings = new AASettings();
			$this->define_constants();
			$this->actions_and_filters();
			$this->settings->actions_and_filters();
		}

		/**
		 * Define constants of the plugin.
		 */
		private function define_constants(): void {
			$this->define( 'AA_ABSPATH', dirname( AA_PLUGIN_FILE ) . '/' );
			$this->define( 'AA_FULLNAME', 'autotelex-automotive' );
		}

		/**
		 * Define if not already set.
		 *
		 * @param string $name  the name.
		 * @param string $value the value.
		 */
		private static function define( string $name, string $value ): void {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Initialise Autotelex Automotive Core.
		 */
		public function init(): void {
			$this->initialise_localisation();
			do_action( 'autotelex_automotive_init' );
		}

		/**
		 * Initialise the localisation of the plugin.
		 */
		private function initialise_localisation(): void {
			load_plugin_textdomain( 'autotelex-automotive', false, plugin_basename( dirname( AA_PLUGIN_FILE ) ) . '/languages/' );
		}

		/**
		 * Add pluggable support to functions.
		 */
		public function pluggable(): void {
			include_once AA_ABSPATH . 'includes/aa-functions.php';
		}

		/**
		 * Convert XML in a REST request for this plugin to JSON.
		 *
		 * @param mixed           $result The result to return.
		 * @param WP_REST_Server  $server The WP REST Server handling the result.
		 * @param WP_REST_Request $request The WP_REST_Request with possibly XML data.
		 *
		 * @return mixed Either the result or a WP_REST_Response with a request containing JSON data instead of XML.
		 */
		public function filter_http_request( $result, WP_REST_Server $server, WP_REST_Request $request ) {
			$content_type = $request->get_content_type();

			if ( str_starts_with( $request->get_route(), '/autotelex-automotive' ) && str_starts_with( $request->get_body(), '<?xml' ) && isset( $content_type['value'] ) && aa_is_xml_content_type( $content_type['value'] ) ) {
				$xml_obj = simplexml_load_string( $request->get_body() );
				if ( false !== $xml_obj ) {
					$properties_as_array = aa_xml_to_array( $xml_obj );
					$properties_as_array = aa_pre_process_xml_array( $properties_as_array );
					$properties_as_array = aa_remove_voertuig( $properties_as_array );
					$properties_as_array = aa_format_afbeeldingen( $properties_as_array );
					$request->set_header( 'content-type', 'application/json' );
					$request->set_body( wp_json_encode( $properties_as_array ) );
					return $server->dispatch( $request );
				}
			}
			return $result;
		}

		/**
		 * Add actions and filters.
		 */
		private function actions_and_filters(): void {
			add_action( 'after_setup_theme', array( $this, 'pluggable' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action(
				'rest_api_init',
				array( $this->rest, 'add_rest_api_endpoint' )
			);
			add_filter( 'rest_pre_dispatch', array( $this, 'filter_http_request' ), 10, 3 );
		}
	}
}
