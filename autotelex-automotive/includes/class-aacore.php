<?php
/**
 * Autotelex Automotive Core
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AACore' ) ) {
	/**
	 * WidCol Core class
	 *
	 * @class WidColCore
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
	}
}