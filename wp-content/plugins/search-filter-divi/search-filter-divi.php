<?php
/**
 * Plugin Name: Search & Filter - Divi Extension
 * Description: A bridge Plugin enabling easy integration of the Divi Blog, Portfolio and Shop modules with Search & Filter Pro
 * Plugin URI:  https://searchandfilter.com
 * Version:     2.0.0
 * Author:      Search & Filter
 * Author URI:  https://searchandfilter.com
 * Update URI:  https://searchandfilter.com
 * Text Domain: search-filter-divi
 * Requires at least: 6.7
 * Tested up to: 6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'constants.php';
require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

if ( ! defined( 'SEARCH_FILTER_DIVI_URL' ) ) {
	define( 'SEARCH_FILTER_DIVI_URL', plugin_dir_url( __FILE__ ) );
}

// Define the base file.
if ( ! defined( 'SEARCH_FILTER_DIVI_PATH' ) ) {
	define( 'SEARCH_FILTER_DIVI_PATH', plugin_dir_path( __FILE__ ) );
}

// Define the base file.
if ( ! defined( 'SEARCH_FILTER_DIVI_FILE' ) ) {
	define( 'SEARCH_FILTER_DIVI_FILE', __FILE__ );
}
class Search_Filter_Divi_Extension {

	/**
	 * Plugin Version
	 *
	 * @since 2.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '2.0.0';
	/**
	 * Plugin Slug
	 *
	 * @since 2.0.0
	 *
	 * @var string The plugin version.
	 */
	const SLUG = 'search-filter-divi';

	/**
	 * Plugin Update ID
	 *
	 * @since 2.0.0
	 *
	 * @var int The plugin update ID.
	 */
	const PLUGIN_UPDATE_ID = 197854;
	/**
	 * Minimum Divi Version
	 *
	 * @since 2.0.0
	 *
	 * @var string Minimum Divi version required to run the plugin.
	 */
	const MINIMUM_DIVI_VERSION = '4.27.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 2.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Plugin Update ID
	 *
	 * @since 2.0.0
	 *
	 * @var int The plugin update ID.
	 */
	const SEARCH_FILTER_PRO_REQUIRED_VERSION = '3.2.0';
	/**
	 * Search & Filter Pro Tested Up To
	 *
	 * @since 2.0.0
	 *
	 * @var string The Search & Filter Pro tested up to version.
	 */
	const SEARCH_FILTER_PRO_TESTED_UPTO = '3.2.0';
	/**
	 * Plugin Update URL
	 *
	 * @since 2.0.0
	 *
	 * @var string The plugin update URL.
	 */
	const PLUGIN_UPDATE_URL = 'https://searchandfilter.com';

	/**
	 * Plugin Instance
	 *
	 * @since 2.0.0
	 *
	 * @var Plugin The instance of the plugin (v2 or v3).
	 */
	protected static $plugin = null;

	/**
	 * Divi Extension Instance
	 *
	 * @since 2.0.0
	 *
	 * @var Plugin The instance of the Divi Extension.
	 */
	protected static $extension = null;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'i18n' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'initialize_plugin' ), 9 );
		add_action( 'divi_extensions_init', array( __CLASS__, 'initialize_divi_extension' ) );
	}
	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 */
	public static function i18n() {
		load_plugin_textdomain( 'search-filter-divi' );
	}
	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Divi (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 */
	public static function initialize_plugin() {

		if ( self::is_search_filter_version( 2 ) ) {
			self::$extension = new \Search_Filter_Divi_Extension\Version_2\Plugin();
		} elseif ( self::is_search_filter_version( 3 ) ) {
			self::$extension = new \Search_Filter_Divi_Extension\Version_3\Plugin();
		} else {
			// S&F not loaded at all.
			self::create_wp_admin_notice( 'get_missing_search_filter_plugin_message' );
		}
	}
	/**
	 * Initialize the Divi Extension
	 *
	 * Divi expects official extensions to be loaded a certain way, including on this hook
	 * `divi_extensions_init` using an instance of the `DiviExtension` class.
	 *
	 * Fired by `divi_extensions_init` action hook.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 */
	public static function initialize_divi_extension() {

		if ( self::is_search_filter_version( 2 ) ) {
			self::$extension = new \Search_Filter_Divi_Extension\Version_2\Extension( self::SLUG );
		} elseif ( self::is_search_filter_version( 3 ) ) {
			self::$extension = new \Search_Filter_Divi_Extension\Version_3\Extension( self::SLUG );
		}
	}

	/**
	 * Detects whether to load the version 2 or 3 integration.
	 *
	 * @param int $version
	 * @return bool Whether the version is matched or not.
	 */
	private static function is_search_filter_version( $version ) {

		// Test for version 2.
		if ( $version === 2 ) {
			if ( ! defined( 'SEARCH_FILTER_VERSION' ) ) {
				return false;
			}
			if ( version_compare( SEARCH_FILTER_VERSION, '2.0.0', '>=' ) && version_compare( SEARCH_FILTER_VERSION, '3.0.0-beta-1', '<' ) ) {
				return true;
			}
		} elseif ( $version === 3 ) {
			if ( ! defined( 'SEARCH_FILTER_PRO_VERSION' ) ) {
				return false;
			}
			if ( version_compare( SEARCH_FILTER_PRO_VERSION, '3.0.0-beta-2', '>=' ) ) {
				return true;
			}
		}
		return false;
	}



	/**
	 * Admin notice.
	 *
	 * Create a wp-admin notice from a message callback.
	 *
	 * @param string $message_callback The message callback.
	 */
	public static function create_wp_admin_notice( $message_callback ) {
		add_action(
			'admin_notices',
			function () use ( $message_callback ) {
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
				echo '<div class="notice notice-error is-dismissible"><p>';
				$message = call_user_func( array( __CLASS__, $message_callback ) );
				echo wp_kses_post( $message );
				echo '</p></div>';
			}
		);
	}

	/**
	 * Get the message for when the site doesn't have Beaver Builder installed or activated.
	 *
	 * @return string
	 */
	public static function get_missing_search_filter_plugin_message() {
		return sprintf(
			// translators: 1: Search & Filter Plugin name 2: Extension name.
			esc_html__( '%1$s needs to be installed and activated for the %2$s to work.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter Pro', 'search-filter-divi' ) . '</strong>',
			'<strong>' . esc_html__( 'Search & Filter - Divi Extension', 'search-filter-divi' ) . '</strong>',
		);
	}


	/**
	 * Get the message for when Search & Filter is next version.
	 *
	 * @return string
	 */
	public static function get_search_filter_is_next_message() {
		return sprintf(
			// translators: 1: Extension name 2: Search & Filter Plugin name.
			esc_html__( 'The %1$s needs to be be updated work with %2$s version %3$s or higher.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter - Divi Extension', 'search-filter-divi' ) . '</strong>',
			'<strong>' . esc_html__( 'Search & Filter Pro', 'search-filter-divi' ) . '</strong>',
			SEARCH_FILTER_PRO_VERSION,
		);
	}

	/**
	 * Get the message for the main plugin missing.
	 *
	 * @return string
	 */
	public static function get_missing_main_plugin_message() {
		return sprintf(
			// translators: 1: Plugin name 2: Divi.
			esc_html__( '%1$s requires %2$s to be installed and activated.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter - Divi Extension', 'search-filter-divi' ) . '</strong>',
			'<strong>' . esc_html__( 'Divi', 'search-filter-divi' ) . '</strong>'
		);
	}

	/**
	 * Get the message for the minimum Divi version.
	 *
	 * @return string
	 */
	public static function get_minimum_divi_version_message() {
		return sprintf(
			// translators: 1: Plugin name 2: Divi 3: Required Divi version.
			esc_html__( '%1$s requires %2$s version %3$s or greater.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter - Divi Extension', 'search-filter-divi' ) . '</strong>',
			'<strong>' . esc_html__( 'Divi', 'search-filter-divi' ) . '</strong>',
			self::MINIMUM_DIVI_VERSION
		);
	}

	/**
	 * Get the message for the minimum PHP version.
	 *
	 * @return string
	 */
	public static function get_minimum_php_version_message() {
		return sprintf(
			// translators: 1: Plugin name 2: PHP 3: Required PHP version.
			esc_html__( 'The %1$s requires %2$s version %3$s or greater.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter - Divi Extension', 'search-filter-divi' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'search-filter-divi' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);
	}

	/**
	 * Get the message for Search & Filter being outdated.
	 *
	 * @return string
	 */
	public static function get_search_filter_outdated_message() {
		return sprintf(
			// translators: 1: Search & Filter Plugin name 2: Required version 3: Extension name
			esc_html__( '%1$s needs to be be updated to version %2$s or higher for the %3$s to work.', 'search-filter-divi' ),
			'<strong>' . esc_html__( 'Search & Filter Pro', 'search-filter-divi' ) . '</strong>',
			self::SEARCH_FILTER_PRO_REQUIRED_VERSION,
			'<strong>' . esc_html__( 'Divi Extension', 'search-filter-divi' ) . '</strong>',
		);
	}
}

Search_Filter_Divi_Extension::init();
