<?php
/**
 * Autotelex Automotive functions
 *
 * @package autotelex-automotive
 */

if ( ! function_exists( 'sanitize_int' ) ) {
	/**
	 * Sanitize an integer value.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return int The sanitized value.
	 */
	function sanitize_int( $to_sanitize ): int {
		$type = gettype( $to_sanitize );
		if ( 'integer' === $type || 'boolean' === $type || 'double' === $type || 'string' === $type || is_null( $to_sanitize ) ) {
			return (int) $to_sanitize;
		} else {
			return 0;
		}
	}
}

if ( ! function_exists( 'sanitize_autotelex_bool' ) ) {
	/**
	 * Convert a boolean value from Autotelex.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return bool The sanitized value.
	 */
	function sanitize_autotelex_bool( $to_sanitize ): bool {
		return 'j' === $to_sanitize;
	}
}

if ( ! function_exists( 'sanitize_url_list' ) ) {
	/**
	 * Sanitize a list of URLs seperated by comma's.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return array The sanitized value.
	 */
	function sanitize_url_list( $to_sanitize ): array {
		if ( ! is_string( $to_sanitize ) ) {
			return array();
		}

		$to_sanitize_url_list = explode( ',', $to_sanitize );
		$sanitized_url_list   = array();
		foreach ( $to_sanitize_url_list as $to_sanitize_url ) {
			$filtered_url = filter_var( $to_sanitize_url, FILTER_VALIDATE_URL );
			if ( false !== $filtered_url ) {
				$sanitized_url_list[] = $filtered_url;
			}
		}
		return $sanitized_url_list;
	}
}
