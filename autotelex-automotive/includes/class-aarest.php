<?php
/**
 * Autotelex Automotive REST
 *
 * @package autotelex-automotive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AARest' ) ) {
	/**
	 * Autotelex Automotive REST class.
	 */
	class AARest {


		/**
		 * Add REST API endpoint.
		 *
		 * @return void
		 */
		public function add_rest_api_endpoint(): void {
			register_rest_route(
				'autotelex-automotive/v1',
				'/manage',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'manage_stock' ),
					'args'                => array(
						'actie'                           => array(
							'required'          => true,
							'type'              => 'string',
							'validate_callback' => array( $this, 'validate_actie' ),
							'sanitize_callback' => array( $this, 'sanitize_actie' ),
						),
						'voertuignr'                      => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'kenteken'                        => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'verkoopprijs_particulier_bedrag' => array(
							'required'          => false,
							'type'              => 'int',
							'sanitize_callback' => 'aa_sanitize_int',
						),
						'opmerkingen'                     => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'titel'                           => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => array( $this, 'sanitize_titel' ),
						),
						'verkocht'                        => array(
							'required'          => true,
							'type'              => 'bool',
							'validate_callback' => array( $this, 'validate_verkocht' ),
							'sanitize_callback' => 'aa_sanitize_autotelex_bool',
						),
						'afbeeldingen'                    => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'aa_sanitize_url_list',
						),
					),
					'permission_callback' => array( $this, 'check_permission' ),
				)
			);
		}

		/**
		 * Manage stock inventory for Automotive theme.
		 *
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response The response.
		 */
		public function manage_stock( WP_REST_Request $request ): WP_REST_Response {
			$action = $request->get_param( 'actie' );
			if ( 'add' === $action ) {
				$return_value = $this->add_listing( $request );
			} elseif ( 'change' === $action ) {
				$return_value = $this->change_listing( $request );
			} else {
				$return_value = $this->delete_listing( $request );
			}
			if ( $return_value ) {
				return rest_ensure_response( new WP_REST_Response( '', 200 ) );
			} else {
				return rest_ensure_response( new WP_REST_Response( '', 500 ) );
			}
		}

		/**
		 * Verify permissions for the API endpoint.
		 *
		 * @return bool Whether the request has the right HTTP permissions set.
		 */
		public function check_permission(): bool {
			if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
				return false;
			}

			$option   = get_option( 'autotelex_automotive_settings' );
			$username = $option['authentication_settings_username'];
			$password = $option['authentication_settings_password'];
			return $username === $_SERVER['PHP_AUTH_USER'] && $password === $_SERVER['PHP_AUTH_PW'];
		}

		/**
		 * Handle the add action for the API endpoint.
		 *
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return bool
		 */
		private function add_listing( WP_REST_Request $request ): bool {
			if ( $this->listing_exists( $request->get_param( 'voertuignr' ) ) ) {
				return false;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => $request->get_param( 'titel' ),
					'post_content' => $request->get_param( 'opmerkingen' ),
					'post_status'  => 'publish',
					'post_type'    => 'listings',
					'meta_input'   => array(
						'aa_unique_id'    => $request->get_param( 'voertuignr' ),
						'listing_options' => serialize(
							array(
								'price' => array(
									'value'    => is_null( $request->get_param( 'verkoopprijs_particulier_bedrag' ) ) ? '' : $request->get_param( 'verkoopprijs_particulier_bedrag' ),
									'original' => '',
								),
							)
						),
					),
				)
			);
			if ( 0 === $post_id ) {
				return false;
			}
			$this->update_attachment_data_for_post( get_post( $post_id ), $request->get_param( 'afbeeldingen' ) );
			return true;
		}

		/**
		 * Handle the change action for the API endpoint.
		 *
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return bool
		 */
		private function change_listing( WP_REST_Request $request ): bool {
			$post = $this->get_listing_by_meta_id( $request->get_param( 'voertuignr' ) );
			if ( is_null( $post ) ) {
				return false;
			}

			$listing_options = unserialize( get_post_meta( $post->ID, 'listing_options', true ) );
			if ( ! is_null( $request->get_param( 'verkoopprijs_particulier_bedrag' ) ) ) {
				if ( ! isset( $listing_options['price'] ) ) {
					$listing_options['price'] = array();
				}
				$listing_options['price']['value'] = $request->get_param( 'verkoopprijs_particulier_bedrag' );
			}

			$new_post_data = array(
				'post_title'   => $request->get_param( 'titel' ),
				'post_content' => $request->get_param( 'opmerkingen' ),
			);
			$new_meta_data = array(
				'listing_options' => serialize( $listing_options ),
			);

			$new_post_data = array_filter(
				$new_post_data,
				function( $element ) {
					return ! is_null( $element );
				}
			);

			$new_meta_data = array_filter(
				$new_meta_data,
				function ( $element ) {
					return ! is_null( $element );
				}
			);

			$new_post_data['meta_input'] = $new_meta_data;
			$new_post_data['ID']         = $post->ID;

			wp_update_post(
				$new_post_data
			);

			$this->update_attachment_data_for_post( $post, $request->get_param( 'afbeeldingen' ) );
			return true;
		}

		/**
		 * Update the attachment meta data for posts.
		 *
		 * @param WP_Post $post The post to add the attachments to.
		 * @param array   $attachment_urls The URLs of the attachments to add.
		 *
		 * @return void
		 */
		private function update_attachment_data_for_post( WP_Post $post, array $attachment_urls ) {
			$attachments_to_add = array();
			foreach ( $attachment_urls as $attachment_url ) {
				$attachment_id = aa_get_attachment_by_url( $attachment_url );
				if ( null === $attachment_id ) {
					$attachment_id = aa_generate_attachment_from_url( $attachment_url );
				}
				if ( null !== $attachment_id ) {
					$attachments_to_add[] = $attachment_id;
				}
			}

			update_post_meta( $post->ID, 'gallery_images', $attachments_to_add );
		}

		/**
		 * Handle the delete action for the API endpoint.
		 *
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return bool Whether the listing was deleted successfully.
		 */
		private function delete_listing( WP_REST_Request $request ): bool {
			$post = $this->get_listing_by_meta_id( $request->get_param( 'voertuignr' ) );
			if ( is_null( $post ) ) {
				return false;
			}

			$deleted_post = wp_delete_post( $post->ID, true );
			if ( false === $deleted_post || null === $deleted_post ) {
				return false;
			}

			return true;
		}

		/**
		 * Verify whether a listing already exists (based on the value of aa_unique_id).
		 *
		 * @param string $meta_id The meta ID value.
		 *
		 * @return bool True when the post exists already, false otherwise.
		 */
		private function listing_exists( string $meta_id ): bool {
			$posts = get_posts(
				array(
					'meta_key'   => 'aa_unique_id',
					'meta_value' => $meta_id,
					'post_type'  => 'listings',
				)
			);
			return count( $posts ) > 0;
		}

		/**
		 * Get a listing by its meta id value (of aa_unique_id).
		 *
		 * @param string $meta_id The meta ID value.
		 *
		 * @return WP_Post|null The found posts or null on failure.
		 */
		private function get_listing_by_meta_id( string $meta_id ): ?WP_Post {
			$posts = get_posts(
				array(
					'meta_key'   => 'aa_unique_id',
					'meta_value' => $meta_id,
					'post_type'  => 'listings',
				)
			);
			if ( count( $posts ) === 1 ) {
				return $posts[0];
			} else {
				return null;
			}
		}

		/**
		 * Sanitize actie REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the actie parameter was validated correctly.
		 */
		public function validate_actie( $param, WP_REST_Request $request, string $key ): bool {
			return 'add' === $param || 'change' === $param || 'delete' === $param;
		}

		/**
		 * Sanitize actie REST parameter.
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return string Sanitized REST parameter for actie.
		 */
		public function sanitize_actie( $value, WP_REST_Request $request, string $param ): string {
			return strtolower( (string) $value );
		}

		/**
		 * Validate verkocht REST parameter.
		 *
		 * @param mixed           $param   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $key     The key of the parameter.
		 *
		 * @return bool Whether the verkocht parameter was validated correctly.
		 */
		public function validate_verkocht( $param, WP_REST_Request $request, string $key ): bool {
			return 'j' === $param || 'n' === $param;
		}

		/**
		 * Sanitize titel REST parameter
		 *
		 * @param mixed           $value   The value of the REST parameter.
		 * @param WP_REST_Request $request The request.
		 * @param string          $param   The parameter name.
		 *
		 * @return string Sanitized REST parameter for titel.
		 */
		public function sanitize_titel( $value, WP_REST_Request $request, string $param ): string {
			return wp_strip_all_tags( sanitize_text_field( $value ) );
		}
	}
}
