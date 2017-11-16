<?php

namespace LogBook;

use WP_REST_Server;
use WP_Error;

class Rest_Logs_Controller extends \WP_REST_Posts_Controller
{
	public function __construct()
	{
		$this->post_type = 'logbook';
		$this->namespace = 'logbook/v1';
		$this->rest_base = 'logs';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request )
	{
		$response = parent::get_items( $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response;
	}

	public function prepare_item_for_response( $post, $request )
	{
		$log = Log::get_the_log( $post );
		unset( $log['meta']['cli-command'] ); // For security reason.
		$response = rest_ensure_response( $log );

		return $response;
	}

	public function check_read_permission( $post ) {
		return true;
	}

	public function permission_callback()
	{
		$token = get_option( 'logbook-api-token' );

		if ( ! empty( $_SERVER['HTTP_X_LOGBOOK_API_TOKEN'] ) ) {
			if ( $token === sha1( $_SERVER['HTTP_X_LOGBOOK_API_TOKEN'] ) ) {
				return true;
			}
		}

		return new WP_Error(
			'logbook_rest_error',
			'You don\'t have permission to access this API.',
			array( 'status' => 401 )
		);
	}
}
