<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

/**
 * Dappier class.
 *
 * @since 0.1.0
 */
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
		add_filter( 'mai_template-parts_config',                       [ $this, 'add_content_areas' ] );
		add_filter( 'acf/load_fields',                                 [ $this, 'add_mpg_fields' ], 10, 2 );
		add_filter( 'acf/load_field/key=mai_grid_block_query_by',      [ $this, 'add_mpg_choices' ] );
		add_filter( 'mai_grid_wp_query_defaults',                      [ $this, 'add_wp_query_defaults' ] );
		add_filter( 'acf/load_field/key=mai_grid_block_posts_orderby', [ $this, 'add_hide_conditional_logic' ] );
		add_filter( 'acf/load_field/key=mai_grid_block_posts_order',   [ $this, 'add_hide_conditional_logic' ] );
		add_filter( 'mai_post_grid_query_args',                        [ $this, 'handle_query_args' ], 10, 2 );
		add_filter( 'dappier_askai_attributes',                        [ $this, 'add_askai_attributes' ] );
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
	 * Adds content areas.
	 *
	 * @since 0.1.0
	 *
	 * @param array $config The existing config.
	 *
	 * @return array
	 */
	function add_content_areas( $config ) {
		$config['ai-search-results'] = [
			'hook'      => 'genesis_loop',
			'priority'  => 5,
			'default'   => file_get_contents( MAI_AI_PACK_PLUGIN_DIR . 'parts/ai-search-results.php' ),
			'condition' => function() {
				return is_search();
			},
		];

		$config['ai-related-posts'] = [
			'hook'      => 'genesis_after_entry_content',
			'priority'  => 10,
			'default'   => file_get_contents( MAI_AI_PACK_PLUGIN_DIR . 'parts/ai-related-posts.php' ),
			'condition' => function() {
				return is_singular( 'post' );
			},
		];

		return $config;
	}

	/**
	 * Filters the $fields array.
	 *
	 * @since 0.1.0
	 *
	 * @param array $fields The array of fields.
	 * @param array $parent The parent field group.
	 *
	 * @return array
	 */
	function add_mpg_fields( $fields, $parent ) {
		// Bail if not in admin.
		if ( ! is_admin() ) {
			return $fields;
		}

		// Bail if not the mai_post_grid_field_group.
		if ( ! isset( $parent['key'] ) || 'mai_post_grid_field_group' !== $parent['key'] ) {
			return $fields;
		}

		// Loop through the fields.
		foreach ( $fields as $index => $field ) {
			// Skip if not the mai_post_grid_clone field.
			if ( 'mai_post_grid_clone' !== $field['key'] ) {
				continue;
			}

			// Get the sub fields.
			$sub_fields  = $field['sub_fields'];
			$sub_keys    = wp_list_pluck( $sub_fields, 'key' );
			$query_index = array_search( 'mai_grid_block_query_by', $sub_keys );
			$new_field   = [
				'key'     => 'mai_grid_block_posts_orderby_dappier',
				'name'    => 'orderby_dappier',
				'type'    => 'select',
				'choices' => [
					'semantic'             => __( 'Ordered by Relevance', 'mai-ai-pack' ),
					'trending'             => __( 'Ordered by Trending', 'mai-ai-pack' ),
					'most_recent_semantic' => __( 'Ordered by Date', 'mai-ai-pack' ),
				],
				'conditional_logic' => [
					[
						'field'    => 'mai_grid_block_query_by',
						'operator' => '==',
						'value'    => 'dappier_related',
					],
				],
			];

			// Insert $new_field after the query_by field in sub_fields.
			array_splice( $sub_fields, $query_index + 1, 0, [ $new_field ] );

			// Update the sub_fields.
			$fields[ $index ]['sub_fields'] = $sub_fields;

			// Reindex the array.
			$fields = array_values( $fields );

			// We're done.
			return $fields;
		}

		return $fields;
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
	function add_mpg_choices( $field ) {
		$field['choices'][ 'dappier_related' ] = __( 'Related (by Dappier AI)', 'mai-ai-pack' );

		// $field['choices'][ 'dappier_semantic' ]             = __( 'Related (Dappier)', 'mai-ai-pack' );
		// $field['choices'][ 'dappier_trending' ]             = __( 'Related Trending (Dappier)', 'mai-ai-pack' );
		// $field['choices'][ 'dappier_most_recent_semantic' ] = __( 'Related Recent (Dappier)', 'mai-ai-pack' );

		return $field;
	}

	/**
	 * Adds defaults to the WP Query.
	 * This is necessary because the `orderby_dappier` field
	 * would not be passed to other filters otherwise.
	 *
	 * @since 0.1.0
	 *
	 * @param array $defaults The defaults.
	 *
	 * @return array
	 */
	function add_wp_query_defaults( $defaults ) {
		$defaults['orderby_dappier'] = 'semantic';

		return $defaults;
	}

	/**
	 * Adds conditional logic to hide if query by is dapper_related in Mai Post Grid.
	 *
	 * @since TBD
	 *
	 * @param array $field The existing field array.
	 *
	 * @return array
	 */
	function add_hide_conditional_logic( $field ) {
		if ( ! is_admin() ) {
			return $field;
		}

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
		// Bail if in admin.
		if ( is_admin() ) {
			return $query_args;
		}

		// Bail if not querying by Dappier related posts.
		if ( 'dappier_related' !== $args['query_by'] ) {
			return $query_args;
		}

		// Bail if not configured.
		if ( ! function_exists( 'dappier_is_configured' ) || ! dappier_is_configured() ) {
			return $query_args;
		}

		// Get the API key and datamodel ID. We know we have values because we checked above.
		$api_key        = dappier_get_option( 'api_key' );
		$external_dm_id = dappier_get_option( 'external_dm_id' );
		$algorithm      = isset( $args['orderby_dappier'] ) ? $args['orderby_dappier'] : 'semantic';
		$permalink      = get_permalink();
		$cache_key      = 'dappier_related_' . md5( $permalink );
		$response       = get_transient( $cache_key );

		// If not cached, get the Dappier data.
		if ( false === $response ) {
			/**
			 * Build the Dappier args.
			 *
			 * @link https://docs.dappier.com/api-reference/endpoint/ai-recommendations
			 *
			 * @param string  required `query`            Natural language query, keyword or URL. If URL is specified, our AI analyzes the page context,
			 *                                            summarizes and provides semantic recommendations based on the content.
			 * @param integer optional `num_articles_ref` Minimum number of articles from the ref domain. The rest of the articles will come from other sites within the RAG model.
			 *                                            Defaults to 0.
			 * @param string  required `ref`              Site domain of where AI recommendations are being displayed. Example format: dappier.com
			 * @param string  optional `similarity_top_k` Number of results to return.
			 * @param string  optional `search_algorithm` Search algorithm for retrieving articles.
			 *                                           'semantic':             (default) retrieves contextually relevant articles based on query/URL content
			 *                                           'most_recent_semantic': semantic search with most recent articles by publication date
			 *                                           'most_recent':          retrieves most recent articles by publication date
			 *                                           'trending':             retrieves articles relevant to trending keywords in past 24 hours
			 */
			$api_args = [
				'headers' => [
					'Authorization' => "Bearer {$api_key}",
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode( [
					'query'            => $permalink,
					'similarity_top_k' => $query_args['posts_per_page'],
					'search_algorithm' => $algorithm,
				] ),
			];

			// Get the Dappier data.
			$endpoint = "https://api.dappier.com/app/datamodel/{$external_dm_id}";
			$response = wp_remote_post( $endpoint, $api_args );

			// Cache the response for 5 minutes.
			set_transient( $cache_key, $response, 5 * MINUTE_IN_SECONDS );

			// Get the response code.
			$code = wp_remote_retrieve_response_code( $response );

			// Bail if there's an error.
			if ( 200 !== $code ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$message = isset( $response['response']['message'] ) ? $response['response']['message'] : __( 'Unknown error', 'mai-ai-pack' );
					error_log( 'Dappier API request failed: ' . $code . ' ' . $message . ' | ' . $endpoint );
				}

				return $query_args;
			}
		}

		// Get the body.
		$body = wp_remote_retrieve_body( $response );
		$body = json_decode( $body );

		// Bail if no body.
		if ( ! $body ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$message = isset( $response['response']['message'] ) ? $response['response']['message'] : __( 'Unknown error', 'mai-ai-pack' );
				error_log( 'Dappier API request missing body: ' . $code . ' ' . $message . ' | ' . $endpoint );
			}

			return $query_args;
		}

		$results = isset( $body->results ) ? $body->results : [];

		// Bail if no results.
		if ( ! $results ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$message = isset( $response['response']['message'] ) ? $response['response']['message'] : __( 'Unknown error', 'mai-ai-pack' );
				error_log( 'Dappier API request missing results: ' . $code . ' ' . $message . ' | ' . $endpoint );
			}

			return $query_args;
		}

		// Get post IDs.
		$post_ids = wp_list_pluck( $results, 'content_id' );

		// Bail if no IDs.
		if ( ! $post_ids ) {
			return $query_args;
		}

		// Set IDs.
		$query_args['post__in'] = $post_ids;
		$query_args['orderby']  = 'post__in';

		// Unset unnecessary stuff.
		unset( $query_args['tax_query'] );
		unset( $query_args['meta_query'] );

		return $query_args;
	}

	/**
	 * Sets defaults to Mai Theme styles.
	 *
	 * @since 0.1.0
	 *
	 * @param array $attributes The attributes.
	 *
	 * @return array
	 */
	function add_askai_attributes( $attributes ) {
		$defaults = [
			'mainBackgroundColor'             => 'var(--color-alt)',
			'themeColor'                      => 'var(--color-primary)',
			'promptSuggestionBackgroundColor' => 'var(--button-secondary-background,var(--color-secondary))',
			'promptSuggestionTextColor'       => 'var(--button-secondary-color)',
			'messageBackgroundColor'          => 'var(--color-background)',
			'messageTextColor'                => 'var(--color-body)',
			'titleColor'                      => 'var(--color-heading)',
			'containerRadius'                 => 'var(--border-radius)',
			'elementRadius'                   => 'var(--button-border-radius,var(--border-radius))',
			'fontSizeHeaderMobile'            => 'var(--font-size-lg)',
			'fontSizeDefaultMobile'           => 'var(--font-size-base)',
			'fontSizeHeaderDesktop'           => 'var(--font-size-lg)',
			'fontSizeDefaultDesktop'          => 'var(--font-size-base)',
		];

		// Loop through the defaults and set them if not already set.
		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $attributes[ $key ] ) || in_array( $attributes[ $key ], [ '', 'inherit' ] ) ) {
				$attributes[ $key ] = $value;
			}
		}

		return $attributes;
	}
}
