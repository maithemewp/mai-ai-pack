<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_AI_Pack_Dappier {
	/**
	 * Construct the class.
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_filter( 'mai_plugin_dependencies',                         [ $this, 'add_dependencies' ] );
		add_filter( 'acf/load_field/key=mai_grid_block_query_by',      [ $this, 'add_related_choice' ] );
		add_filter( 'acf/load_field/key=mai_grid_block_posts_orderby', [ $this, 'hide_orderby_field' ] );
		add_filter( 'acf/load_field/key=mai_grid_block_posts_order',   [ $this, 'hide_order_field' ] );
		add_filter( 'mai_post_grid_query_args',                        [ $this, 'handle_query_args' ], 10, 2 );

	}

	/**
	 * Add dependencies.
	 *
	 * @since 0.1.0
	 *
	 * @param array $dependencies The dependencies.
	 *
	 * @return array
	 */
	function add_dependencies( $dependencies ) {
		$dependencies[] = [
			'name'     => 'Dappier for WordPress',
			'host'     => 'github',
			'url'      => 'https://dappier.com/',
			'uri'      => 'DappierAI/dappier-wordpress',
			'slug'     => 'dappier-wordpress/dappier-wordpress.php',
			'branch'   => 'production',
			'required' => true,
			'token'    => null,
		];

		return $dependencies;
	}

	/**
	 * Adds Related as an "Get Entries By" choice.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The existing field array.
	 *
	 * @return array
	 */
	function add_related_choice( $field ) {
		$field['choices'][ 'dappier_related' ] = __( 'Related (Dappier)', 'mai-elasticpress' );

		return $field;
	}

	/**
	 * Hides "Order" field if querying by Related.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The existing field array.
	 *
	 * @return array
	 */
	function hide_orderby_field( $field ) {
		$field['conditional_logic'][] = [
			'field'    => 'mai_grid_block_query_by',
			'operator' => '!=',
			'value'    => 'dappier_related',
		];

		return $field;
	}

	/**
	 * Hides "Order" field if querying by Related.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The existing field array.
	 *
	 * @return array
	 */
	function hide_order_field( $field ) {
		$field['conditional_logic'][] = [
			'field'    => 'mai_grid_block_query_by',
			'operator' => '!=',
			'value'    => 'dappier_related',
		];

		return $field;
	}

	/**
	 * Handles the query args.
	 *
	 * @since 0.1.0
	 *
	 * @param array $query_args The query args.
	 * @param array $args       The block args.
	 *
	 * @return array
	 */
	function handle_query_args( $query_args, $args ) {
		// Bail if not querying by Dappier related posts.
		if ( 'dappier_related' !== $args['query_by'] ) {
			return $query_args;
		}

		// Bail if not configured.
		if ( ! dappier_is_configured() ) {
			return $query_args;
		}

		// Get the API key and datamodel ID. We know we have values because we checked above.
		$api_key      = dappier_get_option( 'api_key' );
		$datamodel_id = dappier_get_option( 'datamodel_id' );
		$permalink    = get_permalink();

		// Check transient cache first.
		$cache_key = 'dappier_related_' . md5( $permalink );
		$response  = get_transient( $cache_key );

		// If not cached, get the Dappier data.
		if ( false === $response ) {
			// Get the Dappier data.
			$endpoint = "https://api.dappier.com/app/datamodel/{$datamodel_id}";
			$response = wp_remote_post( $endpoint, [
				'headers' => [
					'Authorization' => "Bearer {$api_key}",
					'Content-Type'  => 'application/json',
				],
				'body' => [
					'query' => $permalink,
				],
			] );

			// Cache the response for 5 minutes.
			set_transient( $cache_key, $response, 5 * MINUTE_IN_SECONDS );

			// Get the response code.
			$code = wp_remote_retrieve_response_code( $response );

			// Bail if there's an error.
			if ( 200 !== $code ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$message = isset( $response['response']['message'] ) ? $response['response']['message'] : 'Unknown error';
					error_log( 'Dappier API request failed: ' . $code . ' ' . $message );
				}
			}

			return $query_args;
		}

		// Get the body.
		$body = wp_remote_retrieve_body( $response );

		// Bail if no body.
		if ( ! $body ) {
			return $query_args;
		}

		// Process the body as needed.
		// ...

		return $query_args;
	}
}
