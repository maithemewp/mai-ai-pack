<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

add_action( 'init', 'maiai_register_block_patterns', 30 );
/**
 * Registers block patterns.
 *
 * @since 0.2.0
 *
 * @return void
 */
function maiai_register_block_patterns() {
	// Define the pattern categories.
	$categories = [
		'mai-ai'         => __( 'Mai AI (All Patterns)', 'mai-ai-pack' ),
		'mai-ai-general' => __( 'Mai AI (General)', 'mai-ai-pack' ),
		'mai-ai-section' => __( 'Mai AI (Full Width Sections)', 'mai-ai-pack' ),
		'mai-ai-related' => __( 'Mai AI (Related Posts)', 'mai-ai-pack' ),
		'mai-ai-search'  => __( 'Mai AI (Search Results)', 'mai-ai-pack' ),
	];

	// Register the pattern categories.
	foreach ( $categories as $slug => $label ) {
		register_block_pattern_category( $slug, [ 'label' => $label ] );
	}

	// Define the patterns.
	$dir = MAI_AI_PACK_PLUGIN_DIR . 'patterns/';

	// Define patterns.
	$patterns = [
		[
			'slug'        => 'general-1',
			'title'       => __( 'AskAI General 1', 'mai-ai-pack' ),
			'description' => _x('A general pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-general' ],
		],
		[
			'slug'        => 'general-2',
			'title'       => __( 'AskAI General 2', 'mai-ai-pack' ),
			'description' => _x('A general pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-general' ],
		],
		[
			'slug'        => 'general-3',
			'title'       => __( 'AskAI General 3', 'mai-ai-pack' ),
			'description' => _x('A general pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-general' ],
		],
		[
			'slug'        => 'general-4',
			'title'       => __( 'AskAI General 4', 'mai-ai-pack' ),
			'description' => _x('A general pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-general' ],
		],
		[
			'slug'        => 'related-1',
			'title'       => __( 'AskAI Related Posts 1', 'mai-ai-pack' ),
			'description' => _x('A pattern for AI powered related posts.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-related' ],
		],
		[
			'slug'        => 'related-2',
			'title'       => __( 'AskAI Related Posts 2', 'mai-ai-pack' ),
			'description' => _x('A pattern for AI powered related posts.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-related' ],
		],
		[
			'slug'        => 'section-1',
			'title'       => __( 'AskAI Section 1', 'mai-ai-pack' ),
			'description' => _x('A full width section pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-section' ],
		],
		[
			'slug'        => 'section-2',
			'title'       => __( 'AskAI Section 2', 'mai-ai-pack' ),
			'description' => _x('A full width section pattern including the Dappier AskAI block.', 'Block pattern description', 'mai-ai-pack'),
			'categories'  => [ 'mai-ai', 'mai-ai-section' ],
		],
	];

	// Register patterns.
	foreach ( $patterns as $pattern ) {
		register_block_pattern( 'mai/' . $pattern['slug'], [
			'title'       => $pattern['title'],
			'description' => $pattern['description'],
			'content'     => file_get_contents( $dir . $pattern['slug'] . '.html' ),
			'categories'  => $pattern['categories'],
			'keywords'    => [ 'mai', 'ai', 'ask', 'dappier' ],
		] );
	}
}
