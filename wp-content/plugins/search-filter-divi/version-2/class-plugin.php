<?php
namespace Search_Filter_Divi_Extension\Version_2;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Plugin {

	/**
	 * Whether the plugin can load.
	 *
	 * @var boolean
	 */
	public static $can_load = false;

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct() {

		// Check for existence of the S&F plugin first.
		if ( ! defined( 'SEARCH_FILTER_VERSION' ) ) {
			// Display notice that S&F is required.
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_missing_search_filter_plugin_message' );
			return;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, \Search_Filter_Divi_Extension::MINIMUM_PHP_VERSION, '<' ) ) {
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_minimum_php_version_message' );
			return;
		}

		// The Divi constant ET_CORE_VERSION is not available until after_setup_theme.
		add_action( 'after_setup_theme', array( $this, 'run_ready_process' ), 20 );
	}


	/**
	 * After the theme is setup, check if Divi and S&F is installed and active.
	 */
	public function run_ready_process() {
		// Check if Divi and S&F is installed and active.
		if ( ! defined( 'ET_CORE_VERSION' ) ) {
			// Display notice that Divi is required.
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_missing_main_plugin_message' );
			return;
		}

		// Check for required GenerateBlocks version.
		if ( ! defined( 'ET_CORE_VERSION' ) || ! version_compare( ET_CORE_VERSION, \Search_Filter_Divi_Extension::MINIMUM_DIVI_VERSION, '>=' ) ) {
			self::create_search_filter_admin_notice( 'get_minimum_divi_version_message', 'warning', 'search-filter-divi-minimum-divi-version' );
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_minimum_divi_version_message' );
			return;
		}

		self::$can_load = true;

		// Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Plugin Updater
		add_action( 'admin_init', array( $this, 'update_plugin_handler' ), 0 );

		new Query();
	}
	/**
	 * Load Scripts
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function enqueue_scripts() {

		wp_register_script( 'search-filter-divi', SEARCH_FILTER_DIVI_URL . 'version-2/scripts/search-filter-divi.js', array( 'jquery' ), \Search_Filter_Divi_Extension::VERSION );
		wp_localize_script(
			'search-filter-divi',
			'SF_LDATA',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'home_url' => home_url( '/' ),
			)
		);
		wp_enqueue_script( 'search-filter-divi' );
	}

	/**
	 * Handle plugin updates
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function update_plugin_handler() {

		// setup the updater
		$edd_updater = new Updater(
			\Search_Filter_Divi_Extension::PLUGIN_UPDATE_URL,
			SEARCH_FILTER_DIVI_FILE,
			array(
				'version' => \Search_Filter_Divi_Extension::VERSION,
				'license' => 'search-filter-extension-free',
				'item_id' => \Search_Filter_Divi_Extension::PLUGIN_UPDATE_ID,       // ID of the product
				'author'  => 'Search & Filter', // author of this plugin
				'beta'    => false,
			)
		);
	}
}
