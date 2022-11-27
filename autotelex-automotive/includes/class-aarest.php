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
							'sanitize_callback' => 'sanitize_int',
						),
						'opmerkingen'                     => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'titel'                           => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'verkocht'                        => array(
							'required'          => true,
							'type'              => 'bool',
							'validate_callback' => array( $this, 'validate_verkocht' ),
							'sanitize_callback' => 'sanitize_autotelex_bool',
						),
						'afbeeldingen'                    => array(
							'required'          => false,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_url_list',
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
				$this->add_listing( $request );
			} elseif ( 'change' === $action ) {
				$this->change_listing( $request );
			} else {
				$this->delete_listing( $request );
			}
			return rest_ensure_response( new WP_REST_Response( '', 200 ) );
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
			$post_id = wp_insert_post(
				array(
					'post_title'   => $request->get_param( 'titel' ),
					'post_content' => $request->get_param( 'opmerkingen' ),
					'post_status'  => 'publish',
					'post_type'    => 'listings',
					'meta_input'   => array(
						'aa_unique_id' => $request->get_param( 'voertuignr' ),
					),
				)
			);
			if ( 0 === $post_id || 'WP_Error' === gettype( $post_id ) ) {
				return false;
			}
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

			return true;
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
			if ( gettype( $deleted_post ) !== 'WP_Post' ) {
				return false;
			}

			return true;
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
					'meta_query' => array(
						array(
							'key'     => 'aa_unique_id',
							'value'   => $meta_id,
							'compare' => '=',
						),
					),
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
		 * Sanitize verkocht REST parameter.
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
	}
}
