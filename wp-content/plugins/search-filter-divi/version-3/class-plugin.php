<?php
namespace Search_Filter_Divi_Extension\Version_3;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * The S&F extension instance
	 *
	 * @var \Search_Filter_Pro\Core\Extensions\Extension|false
	 */
	private $extension = null;

	/**
	 * List of all S&F queries.
	 *
	 * @var array
	 */
	private static $query_options = array();

	/**
	 * List of all S&F module types.
	 *
	 * @var array
	 */
	private static $module_types = array();

	/**
	 * The active query ID.
	 *
	 * @var int
	 */
	private $active_query_id = 0;


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
		if ( ! defined( 'SEARCH_FILTER_PRO_VERSION' ) || ! defined( 'SEARCH_FILTER_VERSION' ) ) {
			// Display notice that S&F is required.
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_missing_search_filter_plugin_message' );
			return;
		}

		// V3 uses its own extensions API for mananging updates & versions.
		// Only added in 3.0.5.
		if ( defined( 'SEARCH_FILTER_PRO_VERSION' ) && class_exists( '\Search_Filter_Pro\Core\Extensions' ) ) {
			\Search_Filter_Pro\Core\Extensions::add(
				\Search_Filter_Divi_Extension::SLUG,
				array(
					'file'         => SEARCH_FILTER_DIVI_FILE,
					'id'           => \Search_Filter_Divi_Extension::PLUGIN_UPDATE_ID,
					'version'      => \Search_Filter_Divi_Extension::VERSION,
					'tested_up_to' => \Search_Filter_Divi_Extension::SEARCH_FILTER_PRO_TESTED_UPTO,
				)
			);

			$this->check_for_upgrades();

			if ( version_compare( SEARCH_FILTER_PRO_VERSION, '3.2.0-beta.3', '>=' ) ) {
				if ( ! $this->requirements_met() ) {
					// Display notice that the extension needs to be updated.
					\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_search_filter_is_next_message' );
					self::create_search_filter_admin_notice( 'get_search_filter_is_next_message', 'warning', 'search-filter-divi-missing-required-version' );
					return;
				}
			}
		}

		if ( version_compare( SEARCH_FILTER_PRO_VERSION, \Search_Filter_Divi_Extension::SEARCH_FILTER_PRO_REQUIRED_VERSION, '<' ) ) {
			// Display notice that S&F is required.
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_search_filter_outdated_message' );
			self::create_search_filter_admin_notice( 'get_search_filter_outdated_message', 'warning', 'search-filter-divi-missing-required-version' );
			return;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, \Search_Filter_Divi_Extension::MINIMUM_PHP_VERSION, '<' ) ) {
			self::create_search_filter_admin_notice( 'get_minimum_php_version_message', 'warning', 'search-filter-divi-minimum-php-version' );
			\Search_Filter_Divi_Extension::create_wp_admin_notice( 'get_minimum_php_version_message' );
			return;
		}

		// Our settings are init before after_setup_theme, so we need to add theme here regardless.

		// Add Elementor options to S&F admin UI.
		add_action( 'search-filter/settings/init', array( $this, 'add_integration_option' ), 10 );

		// Hide CSS selector options from query editor.
		add_action( 'search-filter/settings/init', array( $this, 'hide_css_selector_options' ), 10 );
		add_action( 'search-filter/frontend/data/start', array( $this, 'attach_update_query_attributes' ), 10 );

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

		// Init our module types - needs to be on `init` for `__()` to be ready.
		add_action( 'init', array( $this, 'setup_module_types' ), 1 );
		add_action( 'et_builder_modules_loaded', array( $this, 'load_et_builder_assets' ), 1 );

		// Add query option to portfolio, blog and shop modules
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_portfolio', array( $this, 'render_settings_field' ), 100 );
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_blog', array( $this, 'render_settings_field' ), 100 );
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_shop', array( $this, 'render_settings_field' ), 100 );

		// Add classes to portfolio and blog modules, and attach `pre_get_posts`.
		add_filter( 'et_pb_module_shortcode_attributes', array( $this, 'module_attributes' ), 1000, 3 );
		add_filter( 'et_pb_module_shortcode_attributes', array( $this, 'module_shop_attributes' ), 1000, 3 );

		// remove pre_get_posts and add hooks for content above / below the blog module
		add_filter( 'et_module_shortcode_output', array( $this, 'shortcode_output' ), 1000, 3 );
	}

	/**
	 * Attach S&F Query option to portfolio, blog and shop modules
	 */
	public function render_settings_field( $field_unprocessed ) {

		$show_if = array(
			'search_filter_link' => 'yes',
		);

		$fields = array(
			'search_filter_link' => array(
				'label'           => __( 'Link with a Search & Filter?', 'search-filter-divi' ),
				'type'            => 'select',
				'default'         => 'no',
				'option_category' => 'configuration',
				'options'         => array(
					'no'  => 'No',
					'yes' => 'Yes',
				),
				'toggle_slug'     => 'main_content',
				'affects'         => array( 'search_filter_id' ), // ,'posts_number','include_categories',
				'vb_support'      => true,
			),
			'search_filter_id'   => array(
				'label'               => __( 'Choose a Query', 'search-filter-divi' ),
				'type'                => 'select',
				'default'             => '0',
				'option_category'     => 'configuration',
				'options'             => array_merge( array( '' => 'Choose a Search Form' ), $this->get_queries() ),
				// 'show_if'         => $show_if,
				'depends_show_if_not' => 'no',
				'toggle_slug'         => 'main_content',
				'vb_support'          => true,

			),
		);

		return array_merge( $fields, $field_unprocessed );
	}

	/**
	 * Get the queries.
	 *
	 * @return array The queries.
	 */
	public static function get_queries() {

		if ( ! empty( self::$query_options ) ) {
			return self::$query_options;
		}

		$queries = \Search_Filter\Queries::find(
			array(
				'status'     => 'enabled', // Only get enabled queries.
				'number'     => 0, // Get all.
				'meta_query' => array(
					array(
						'key'     => 'query_integration',
						'value'   => array_keys( self::$module_types ),
						'compare' => 'IN',
					),
				),
			),
			'records'
		);

		foreach ( $queries as $query_record ) {
			self::$query_options[ 'query_' . $query_record->get_id() ] = $query_record->get_name();
		}

		return self::$query_options;
	}


	/**
	 * Setup the module types.
	 */
	public function setup_module_types() {
		self::$module_types = array(
			'divi/module' => __( 'Divi Module', 'search-filter-divi' ),
		);
	}

	/**
	 * Add the integration option to the S&F admin UI.
	 */
	public function add_integration_option() {
		// Get the setting.
		$integration_type_setting = \Search_Filter\Queries\Settings::get_setting( 'queryIntegration' );
		if ( ! $integration_type_setting ) {
			return;
		}

		foreach ( self::$module_types as $module_type => $label ) {
			$new_integration_type_option = array(
				'label' => $label,
				'value' => $module_type,
			);
			$integration_type_setting->add_option( $new_integration_type_option );
		}
	}

	/**
	 * Hide the CSS selector options from the query editor.
	 */
	public function hide_css_selector_options() {
		$depends_conditions = array(
			'relation' => 'AND',
			'action'   => 'hide',
			'rules'    => array(
				array(
					'option'  => 'queryIntegration',
					'compare' => '!=',
					'value'   => 'divi/module',
				),
			),
		);

		$query_container = \Search_Filter\Queries\Settings::get_setting( 'queryContainer' );
		if ( $query_container ) {
			$query_container->add_depends_condition( $depends_conditions );
		}

		$query_posts_container = \Search_Filter\Queries\Settings::get_setting( 'queryPostsContainer' );
		if ( $query_posts_container ) {
			$query_posts_container->add_depends_condition( $depends_conditions );
		}

		$pagination_selector = \Search_Filter\Queries\Settings::get_setting( 'queryPaginationSelector' );
		if ( $pagination_selector ) {
			$pagination_selector->add_depends_condition( $depends_conditions );
		}
	}

	/**
	 * Attach the update query attributes to the S&F admin UI.
	 */
	public function attach_update_query_attributes() {
		// Auto set the CSS selector options.
		add_filter( 'search-filter/queries/query/get_attributes', array( $this, 'update_query_attributes' ), 10, 2 );
	}

	/**
	 * Update the query attributes.
	 */
	public function update_query_attributes( $attributes, $query ) {

		$id = $query->get_id();

		if ( $query->get_attribute( 'queryIntegration', true ) !== 'divi/module' ) {
			return $attributes;
		}

		$attributes['queryContainer']          = '.search-filter-query--id-' . $id;
		$attributes['queryPaginationSelector'] = '.search-filter-query--id-' . $id . ' a.page-numbers, .search-filter-query--id-' . $id . ' .pagination a';
		return $attributes;
	}

	public function module_attributes( $props, $attrs, $render_slug ) {

		if ( ( 'et_pb_portfolio' !== $render_slug ) && ( 'et_pb_blog' !== $render_slug ) ) {
			return $props;
		}

		if ( ( ! isset( $props['search_filter_link'] ) ) || ( ! isset( $props['search_filter_id'] ) ) ) {
			return $props;
		}

		if ( 'yes' !== $props['search_filter_link'] ) {
			return $props;
		}

		// The value of search_filter_id will be in the format `query_X`.
		$search_filter_id            = intval( str_replace( 'query_', '', $props['search_filter_id'] ) );
		$search_filter_results_class = 'search-filter-query--id-' . $search_filter_id;

		// if the class is not already there, add it
		if ( strpos( $props['module_class'], $search_filter_results_class ) === false ) {
			$props['module_class'] .= ' ' . $search_filter_results_class . ' '; // add a class for easy Ajax integration
		}

		$this->active_query_id = $search_filter_id;
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 12 );

		return $props;
	}


	public function module_shop_attributes( $props, $attrs, $render_slug ) {

		if ( ( 'et_pb_shop' !== $render_slug ) ) {
			return $props;
		}

		if ( ( ! isset( $props['search_filter_link'] ) ) || ( ! isset( $props['search_filter_id'] ) ) ) {
			return $props;
		}

		if ( 'yes' !== $props['search_filter_link'] ) {
			return $props;
		}

		// the value of search_filter_id will be something like `search-filter-id-X` where X is the ID
		$search_filter_id      = intval( str_replace( 'search-filter-id-', '', $props['search_filter_id'] ) );
		$props['module_class'] = ' search-filter-results-' . $search_filter_id . ' '; // add a class for easy Ajax integration
		$this->active_query_id = $search_filter_id;

		// filter the query
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 12 );

		// add cache=false to `products` shortcode
		add_filter( 'shortcode_atts_products', array( $this, 'products_shortcode_attributes' ), 30 );

		return $props;
	}
	public function products_shortcode_attributes( $out, $pairs, $atts ) {

		// remove products shortcode caching
		$out['cache'] = false;
		remove_filter( 'shortcode_atts_products', array( $this, 'products_shortcode_attributes' ), 30 );
		return $out;
	}

	public function filter_query( $query ) {

		// Attach S&F to the query
		if ( 0 !== $this->active_query_id ) {
			$query->set( 'search_filter_query_id', $this->active_query_id );
		}
	}

	public function shortcode_output( $output, $render_slug, $module ) {

		if ( ( 'et_pb_portfolio' !== $render_slug ) && ( 'et_pb_blog' !== $render_slug ) && ( 'et_pb_shop' !== $render_slug ) ) {
			return $output;
		}

		// shortcode output means the query has been run, so remove pre_get_posts
		remove_action( 'pre_get_posts', array( $this, 'filter_query' ), 12 );

		return $output;
	}

	/**
	 * Load all our assets when in the editor for previews.
	 *
	 * Lazy loading not currently supported so we need to add everything.
	 *
	 * @return void
	 */
	public static function load_et_builder_assets() {

		if ( ! et_core_is_fb_enabled() ) {
			return;
		}

		// Enqueue all the SVGs when in preview mode.
		\Search_Filter\Core\Icons::load();

		$all_frontend_assets = \Search_Filter\Frontend::get_registered_assets();

		$all_frontend_assets = \Search_Filter\Frontend::get_registered_assets();
		foreach ( $all_frontend_assets as $asset_name => $frontend_asset ) {
			if ( ! empty( $frontend_asset['script']['src'] ) ) {
				wp_register_script( $asset_name, $frontend_asset['script']['src'], $frontend_asset['script']['dependencies'], $frontend_asset['script']['version'], array( 'in_footer', true ) );
				wp_enqueue_script( $asset_name );
			}
			if ( ! empty( $frontend_asset['style']['src'] ) ) {
				wp_register_style( $asset_name, $frontend_asset['style']['src'], $frontend_asset['style']['dependencies'], $frontend_asset['style']['version'], $frontend_asset['style']['media'] );
				wp_enqueue_style( $asset_name );
			}
		}
	}

	/**
	 * Get the extension object.
	 */
	private function get_extension() {
		if ( $this->extension ) {
			return $this->extension;
		}
		$this->extension = \Search_Filter_Pro\Core\Extensions::get( \Search_Filter_Divi_Extension::SLUG );
		return $this->extension;
	}


	/**
	 * Check if the requirements are met.
	 *
	 * @return bool
	 */
	private function requirements_met() {
		$extension = $this->get_extension();
		if ( ! $extension ) {
			return false;
		}

		return $extension->requirements_met();
	}
	/**
	 * Checks to see whether we need to run any upgrades.
	 *
	 * If not, update the registered version.
	 */
	private function check_for_upgrades() {
		$extension = $this->get_extension();
		if ( ! $extension ) {
			return;
		}
		if ( $this->extension->has_upgraded() ) {
			$upgrades_complete = $this->run_upgrades();
			if ( $upgrades_complete ) {
				$this->extension->upgrade();
			}
		}
	}

	/**
	 * Run the upgrades.
	 *
	 * @return bool
	 */
	private function run_upgrades() {
		$upgrades = array(
			// '1.2.4' => 'upgrade_to_1_2_4',
		);

		$upgrades_completed = 0;
		$total_upgrades     = 0;

		$registered_version = $this->extension->get_registered_version();
		if ( ! $registered_version ) {
			$registered_version = '0.0.0';
		}
		foreach ( $upgrades as $version => $method ) {
			if ( version_compare( $registered_version, $version, '<' ) ) {
				++$total_upgrades;
				$upgraded = $this->$method();
				if ( $upgraded ) {
					++$upgrades_completed;
				}
			}
		}
		return $upgrades_completed === $total_upgrades;
	}

	/**
	 * Create a search filter admin notice.
	 *
	 * @param string $message The message.
	 * @param string $type The type.
	 * @param string $id The ID.
	 */
	private static function create_search_filter_admin_notice( $message_callback, $type, $id ) {
		add_action(
			'search-filter/core/notices/get_notices',
			function () use ( $message_callback, $type, $id ) {
				$message = call_user_func( array( \Search_Filter_Divi_Extension::class, $message_callback ) );
				\Search_Filter\Core\Notices::add_notice( $message, $type, $id );
			}
		);
	}
}
