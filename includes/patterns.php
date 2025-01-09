<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'init', 'maiai_register_block_patterns' );
/**
 * Registers block patterns.
 *
 * @since 0.1.0
 *
 * @return void
 */
function maiai_register_block_patterns() {
	register_block_pattern( 'mai/ai-search-results', [
		'title'       => __( 'AI Search Results', 'mai-ai-pack' ),
		'description' => _x('A pattern for AI search results.', 'Block pattern description', 'mai-ai-pack'),
		'content'     => file_get_contents( MAI_AI_PACK_PLUGIN_DIR . 'parts/ai-search-results.php' ),
	] );

	register_block_pattern( 'mai/ai-related-posts', [
		'title'       => __( 'AI Related Posts', 'mai-ai-pack' ),
		'description' => _x('A pattern for AI related posts.', 'Block pattern description', 'mai-ai-pack'),
		'content'     => file_get_contents( MAI_AI_PACK_PLUGIN_DIR . 'parts/ai-related-posts.php' ),
	] );
}
