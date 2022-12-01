<?php
/**
 * Autotelex Automotive functions
 *
 * @package autotelex-automotive
 */

if ( ! function_exists( 'aa_sanitize_int' ) ) {
	/**
	 * Sanitize an integer value.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return int The sanitized value.
	 */
	function aa_sanitize_int( $to_sanitize ): int {
		$type = gettype( $to_sanitize );
		if ( 'integer' === $type || 'boolean' === $type || 'double' === $type || 'string' === $type || is_null( $to_sanitize ) ) {
			return (int) $to_sanitize;
		} else {
			return 0;
		}
	}
}

if ( ! function_exists( 'aa_sanitize_autotelex_bool' ) ) {
	/**
	 * Convert a boolean value from Autotelex.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return bool The sanitized value.
	 */
	function aa_sanitize_autotelex_bool( $to_sanitize ): bool {
		return 'j' === $to_sanitize;
	}
}

if ( ! function_exists( 'aa_sanitize_url_list' ) ) {
	/**
	 * Sanitize a list of URLs seperated by comma's.
	 *
	 * @param mixed $to_sanitize The data to sanitize.
	 *
	 * @return array The sanitized value.
	 */
	function aa_sanitize_url_list( $to_sanitize ): array {
		if ( ! is_string( $to_sanitize ) ) {
			return array();
		}

		$to_sanitize_url_list = explode( ',', $to_sanitize );
		$sanitized_url_list   = array();
		foreach ( $to_sanitize_url_list as $to_sanitize_url ) {
			$filtered_url = filter_var( str_replace( ' ', '', $to_sanitize_url ), FILTER_VALIDATE_URL );
			if ( false !== $filtered_url ) {
				$sanitized_url_list[] = $filtered_url;
			}
		}
		return $sanitized_url_list;
	}
}

if ( ! function_exists( 'aa_get_filename_from_url' ) ) {
	/**
	 * Retrieve the filename from a URL.
	 *
	 * @param string $url The URL.
	 *
	 * @return ?string The filename.
	 */
	function aa_get_filename_from_url( string $url ): ?string {
		$parsed_url = wp_parse_url( $url );
		if ( false === $parsed_url || ! isset( $parsed_url['path'] ) ) {
			return null;
		}
		$name      = pathinfo( $parsed_url['path'], PATHINFO_FILENAME );
		$extension = pathinfo( $parsed_url['path'], PATHINFO_EXTENSION );

		if ( ! isset( $name ) || ! isset( $extension ) ) {
			return null;
		}

		return $name . '.' . $extension;
	}
}

if ( ! function_exists( 'aa_get_attachment_by_url' ) ) {
	/**
	 * Get attachment by looking for aa_attachment_url meta key.
	 *
	 * @param string $url The URL to search for.
	 *
	 * @return ?int Attachment id or null on failure.
	 */
	function aa_get_attachment_by_url( string $url ): ?int {
		$posts = get_posts(
			array(
				'meta_key'   => 'aa_attachment_url',
				'meta_value' => $url,
				'post_type'  => 'attachment',
			)
		);
		if ( count( $posts ) > 0 ) {
			return $posts[0]->ID;
		} else {
			return null;
		}
	}
}

if ( ! function_exists( 'aa_generate_attachment' ) ) {
	/**
	 * Download and create an attachment from a URL.
	 *
	 * @param string $url The URL to download the image from.
	 *
	 * @return ?int The attachment ID or null on failure.
	 */
	function aa_generate_attachment_from_url( string $url ): ?int {
		$image_name = aa_get_filename_from_url( $url );
		if ( null === $image_name ) {
			return null;
		}
		$upload_dir       = wp_upload_dir();
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
		$filename         = basename( $unique_file_name );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			return null;
		}

		$image_data    = wp_remote_get( $url );
		$response_code = wp_remote_retrieve_response_code( $image_data );
		if ( 200 !== $response_code ) {
			return null;
		}

		$response_body = wp_remote_retrieve_body( $image_data );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		global $wp_filesystem;

		$success = $wp_filesystem->put_contents(
			$file,
			$response_body,
			FS_CHMOD_FILE
		);

		if ( false === $success ) {
			return null;
		}

		$wp_filetype = wp_check_filetype( $filename );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'publish',
			'meta_input'     => array(
				'aa_attachment_url' => $url,
			),
		);

		$attach_id = wp_insert_attachment( $attachment, $file );

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}

if ( ! function_exists( 'aa_xml_to_array' ) ) {
	/**
	 * Convert a SimpleXMLElement to an array.
	 *
	 * @param SimpleXMLElement $xml The SimpleXMLElement to convert.
	 *
	 * @return array[] The SimpleXMLElement in array format.
	 */
	function aa_xml_to_array( SimpleXMLElement $xml ): array {
		$parser = function ( SimpleXMLElement $xml, array $collection = array() ) use ( &$parser ) {
			$nodes      = $xml->children();
			$attributes = $xml->attributes();

			if ( 0 !== count( $attributes ) ) {
				foreach ( $attributes as $attr_name => $attr_value ) {
					$collection['attributes'][ $attr_name ] = strval( $attr_value );
				}
			}

			if ( 0 === $nodes->count() ) {
				return strval( $xml );
			}

			foreach ( $nodes as $node_name => $node_value ) {
				if ( count( $node_value->xpath( '../' . $node_name ) ) < 2 ) {
					$collection[ $node_name ] = $parser( $node_value );
					continue;
				}

				$collection[ $node_name ][] = $parser( $node_value );
			}

			return $collection;
		};

		return array(
			$xml->getName() => $parser( $xml ),
		);
	}
}

if ( ! function_exists( 'aa_pre_process_xml_array' ) ) {
	/**
	 * Pre-process the XML array by removing the attributes key.
	 *
	 * @param array $xml_array An XML array with possible attributes keys.
	 *
	 * @return array An XML array without attribute keys.
	 */
	function aa_pre_process_xml_array( array $xml_array ): array {
		$return_value = array();
		foreach ( $xml_array as $key => $value ) {
			if ( 'attributes' === $key && is_array( $value ) ) {
				foreach ( $value as $key_of_value => $value_of_value ) {
					if ( ! isset( $xml_array[ $key_of_value ] ) ) {
						$return_value[ $key_of_value ] = $value_of_value;
					}
				}
			} elseif ( is_array( $value ) ) {
				$return_value[ $key ] = aa_pre_process_xml_array( $value );
			} else {
				$return_value[ $key ] = $value;
			}
		}
		return $return_value;
	}
}

if ( ! function_exists( 'aa_remove_voertuig' ) ) {
	/**
	 * Remove the global voertuig key from an array.
	 *
	 * @param array $xml_array The array with a possible global voertuig key.
	 *
	 * @return array The value of $xml_array['voertuig'] if it exists, $xml_array otherwise.
	 */
	function aa_remove_voertuig( array $xml_array ): array {
		if ( isset( $xml_array['voertuig'] ) && is_array( $xml_array['voertuig'] ) ) {
			return $xml_array['voertuig'];
		}
		return $xml_array;
	}
}

if ( ! function_exists( 'aa_format_afbeeldingen' ) ) {
	/**
	 * Restructure the JSON in the afbeeldingen key to correspond to the Autotelex schema.
	 *
	 * @param array $xml_array An array of elements.
	 *
	 * @return array The same array of elements but if the afbeeldingen key existed a converted list of images.
	 */
	function aa_format_afbeeldingen( array $xml_array ): array {
		if ( isset( $xml_array['afbeeldingen'] ) && is_array( $xml_array['afbeeldingen'] ) && isset( $xml_array['afbeeldingen']['afbeelding'] ) && is_array( $xml_array['afbeeldingen']['afbeelding'] ) ) {
			$return_value = array();
			foreach ( $xml_array['afbeeldingen']['afbeelding'] as $afbeelding ) {
				if ( isset( $afbeelding['url'] ) && is_string( $afbeelding['url'] ) ) {
					$return_value[] = $afbeelding['url'];
				}
			}
			$xml_array['afbeeldingen'] = implode( ',', $return_value );
			return $xml_array;
		}
		return $xml_array;
	}
}
