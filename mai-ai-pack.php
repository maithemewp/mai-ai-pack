<?php

/**
 * Plugin Name:     Mai AI Pack
 * Plugin URI:      https://bizbudding.com/
 * Description:     Adds AI features to Mai Theme. Requires Mai Engine plugin.
 * Version:         1.0.0
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Must be at the top of the file.
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Main Mai_AI_Pack Class.
 *
 * @since 0.1.0
 */
final class Mai_AI_Pack {
	/**
	 * @var   Mai_AI_Pack The one true Mai_AI_Pack
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_AI_Pack Instance.
	 *
	 * Insures that only one instance of Mai_AI_Pack exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_AI_Pack::setup_constants() Setup the constants needed.
	 * @uses    Mai_AI_Pack::includes() Include the required files.
	 * @uses    Mai_AI_Pack::hooks() Activate, deactivate, etc.
	 * @see     Mai_AI_Pack()
	 * @return  object | Mai_AI_Pack The one true Mai_AI_Pack
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_AI_Pack;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->autoload();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-ai-pack' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-ai-pack' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'MAI_AI_PACK_VERSION' ) ) {
			define( 'MAI_AI_PACK_VERSION', '1.0.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_AI_PACK_PLUGIN_DIR' ) ) {
			define( 'MAI_AI_PACK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_AI_PACK_PLUGIN_URL' ) ) {
			define( 'MAI_AI_PACK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Autoload required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function autoload() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		$plugins_link_hook = 'plugin_action_links_mai-ai-pack/mai-ai-pack.php';
		add_filter( $plugins_link_hook,        [ $this, 'plugins_link' ], 10, 4 );
		add_filter( 'mai_plugin_dependencies', [ $this, 'add_dependencies' ] );
		add_action( 'plugins_loaded',          [ $this, 'includes' ] );
		add_action( 'plugins_loaded',          [ $this, 'updater' ], 12 );

	}

	/**
	 * Return the plugin action links. This will only be called if the plugin is active.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $actions     Associative array of action names to anchor tags
	 * @param string $plugin_file Plugin file name, ie my-plugin/my-plugin.php
	 * @param array  $plugin_data Associative array of plugin data from the plugin file headers
	 * @param string $context     Plugin status context, ie 'all', 'active', 'inactive', 'recently_active'
	 *
	 * @return array Associative array of plugin action links
	 */
	function plugins_link( $actions, $plugin_file, $plugin_data, $context ) {
		if ( class_exists( 'Dappier_Plugin' ) ) {
			$actions['settings'] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dappier' ), __( 'Dappier Settings', 'mai-ai-pack' ) );
		}

		return $actions;
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
	 * Include files.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function includes() {
		// Bail if Mai Engine is not loaded.
		if ( ! class_exists( 'Mai_Engine' ) ) {
			add_action( 'admin_notices', function() {
				printf( '<div class="notice notice-error"><p>%s</p></div>', __( 'Mai AI Pack requires the Mai Engine plugin.', 'mai-ai-pack' ) );
			});
			return;
		}

		// Classes.
		foreach ( glob( MAI_AI_PACK_PLUGIN_DIR . 'classes/*.php' ) as $file ) { include $file; }

		// Includes.
		foreach ( glob( MAI_AI_PACK_PLUGIN_DIR . 'includes/*.php' ) as $file ) { include $file; }

		// Instantiate Dappier classes.
		if ( class_exists( 'Dappier_Plugin' ) ) {
			$dappier = new Mai_AI_Pack_Dappier;
		}
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {
		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = PucFactory::buildUpdateChecker( 'https://github.com/maithemewp/mai-ai-pack/', __FILE__, 'mai-ai-pack' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}
}

/**
 * The main function for that returns Mai_AI_Pack
 *
 * The main function responsible for returning the one true Mai_AI_Pack
 * Instance to functions everywhere.
 *
 * @since 0.1.0
 *
 * @return object|Mai_AI_Pack The one true Mai_AI_Pack Instance.
 */
function mai_ai_pack() {
	return Mai_AI_Pack::instance();
}

/**
 * Get Mai_AI_Pack Running.
 *
 * @since 0.1.0
 *
 * @return void
 */
mai_ai_pack();
