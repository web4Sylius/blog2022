<?php
namespace Search_Filter_Divi_Extension\Version_2;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Query {

	protected static $instance = null;

	private $search_form_options = array();
	private $active_query_id     = 0;

	private $current_query = 0;

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function __construct() {

		$this->init();
	}
	public function init() {

		// add S&F option to portoflio and blog module settings
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_portfolio', array( $this, 'render_settings_field' ) );
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_blog', array( $this, 'render_settings_field' ) );
		add_filter( 'et_pb_all_fields_unprocessed_et_pb_shop', array( $this, 'render_settings_field' ), 1000 );

		// we used to do it a different way for shop, but lets unify all for consistent experience
		// add_filter( 'et_pb_all_fields_unprocessed_et_pb_shop', array($this, 'render_shop_settings_field'), 1000 );

		// add S&F class to portfolio and blog modules, attach pre_get_posts
		add_filter( 'et_pb_module_shortcode_attributes', array( $this, 'module_attributes' ), 1000, 3 );
		add_filter( 'et_pb_module_shortcode_attributes', array( $this, 'module_shop_attributes' ), 1000, 3 );

		// remove pre_get_posts and add hooks for content above / below the blog module
		add_filter( 'et_module_shortcode_output', array( $this, 'shortcode_output' ), 1000, 3 );

		// add Divi option to `display method` in S&F settings
		add_filter( 'search_filter_admin_option_display_results', array( $this, 'search_filter_admin_option_display_results' ), 10, 2 );

		// override the ajax container, and pagination selectors in search form attributes
		add_filter( 'search_filter_form_attributes', array( $this, 'search_filter_form_attributes' ), 10, 2 );

		// retrieving wp_query information from this action
		add_filter( 'et_builder_blog_query', array( $this, 'get_blog_query' ), 10, 2 );
	}


	public function filter_query( $query ) {

		// attach S&F to the query
		if ( 0 !== $this->active_query_id ) {
			$query->set( 'search_filter_id', $this->active_query_id );

			// pagination stops working on the homepage, so we have to set these vars again
			if ( is_front_page() || is_home() ) {
				$sfpaged = 1;
				if ( isset( $_GET['sf_paged'] ) ) {
					$sfpaged = intval( $_GET['sf_paged'] );
					global $paged;
					$paged = $sfpaged;
					set_query_var( 'paged', $paged ); // some plugins / themes use `get_query_var`, they often shouldn't
				}
			}
		}
		$this->active_query_id = 0;
	}

	public function shortcode_output( $output, $render_slug, $module ) {


		if ( ( 'et_pb_portfolio' !== $render_slug ) && ( 'et_pb_blog' !== $render_slug ) && ( 'et_pb_shop' !== $render_slug ) ) {
			return $output;
		}

		// shortcode output means the query has been run, so remove pre_get_posts
		remove_action( 'pre_get_posts', array( $this, 'filter_query' ), 1000 );
		remove_filter( 'woocommerce_shortcode_products_query', array( $this, 'woocommerce_shortcode_cache_breaker' ), 1000 );

		// Check if output is a string, if not return it as is - this seems to occur in the editor with this hook
		// whereas the frontend seems to pass in a string.
		if ( ! is_string( $output ) ) {
			return $output;
		}
		// Now allow actions to insert content
		ob_start();
		do_action( 'search_filter_divi_module_start_render', $this->current_query );
		$pre_render_html = ob_get_clean();

		ob_start();
		do_action( 'search_filter_divi_module_end_render', $this->current_query );
		$after_render_html = ob_get_clean();

		// injecting the pre_render_html inside the ajax container
		$output = preg_replace( '~<div class="et_pb_ajax_pagination_container">~', $pre_render_html . '<div class="et_pb_ajax_pagination_container">', $output );

		// find the last closing div
		$closing_div  = '</div>';
		$last_div_pos = strrpos( $output, $closing_div );
		if ( $last_div_pos !== false ) {
			// and insert html just before
			$output = substr_replace( $output, $after_render_html . $closing_div, $last_div_pos, strlen( $closing_div ) );
		}

		return $output;
	}

	public function get_blog_query( $wp_query ) {
		$this->current_query = $wp_query;
		return $wp_query;
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

		// the value of search_filter_id will be something like `search-filter-id-X` where X is the ID
		$search_filter_id            = intval( str_replace( 'search-filter-id-', '', $props['search_filter_id'] ) );
		$search_filter_results_class = 'search-filter-results-' . $search_filter_id;

		// if the class is not already there, add it
		if ( strpos( $props['module_class'], $search_filter_results_class ) === false ) {
			$props['module_class'] .= ' ' . $search_filter_results_class . ' '; // add a class for easy Ajax integration
		}

		$this->active_query_id = $search_filter_id;
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 1000 );

		return $props;
	}

	public function products_shortcode_attributes( $out, $pairs, $atts ) {

		// remove products shortcode caching
		$out['cache'] = false;
		remove_filter( 'shortcode_atts_products', array( $this, 'products_shortcode_attributes' ), 1000, 3 );
		return $out;
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
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 1000 );

		// add cache=false to `products` shortcode
		add_filter( 'shortcode_atts_products', array( $this, 'products_shortcode_attributes' ), 1000, 3 );

		return $props;
	}

	/*
	 * Attach S&F settings to portfolio and blog modules (search form choice)
	*/
	public function render_settings_field( $field_unprocessed ) {

		$show_if = array(
			'search_filter_link' => 'yes',
		);

		$fields = array(
			'search_filter_link' => array(
				'label'           => __( 'Link with a Search & Filter Form?', 'search-filter-divi' ),
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
				'label'               => __( 'Choose a Search Form', 'search-filter-divi' ),
				'type'                => 'select',
				'default'             => '0',
				'option_category'     => 'configuration',
				'options'             => array_merge( array( '' => 'Choose a Search Form' ), $this->get_search_form_options() ),
				// 'show_if'         => $show_if,
				'depends_show_if_not' => 'no',
				'toggle_slug'         => 'main_content',
				'vb_support'          => true,

			),
		);

		return array_merge( $fields, $field_unprocessed );
	}
	public function render_shop_settings_field( $field_unprocessed ) {

		$show_if = array(
			'type' => 'search_filter',
		);

		if ( isset( $field_unprocessed['type'] ) ) {

			if ( isset( $field_unprocessed['type']['options'] ) ) {
				if ( is_array( $field_unprocessed['type']['options'] ) ) {
					$field_unprocessed['type']['options']['search_filter'] = 'Search & Filter Results';
				}
			}
		}

		$fields = array(
			'search_filter_id' => array(
				'label'           => __( 'Choose a Search Form', 'search-filter-divi' ),
				'type'            => 'select',
				'default'         => '1',
				'option_category' => 'configuration',
				'options'         => array_merge( array( '' => 'Choose a Search Form' ), $this->get_search_form_options() ),
				'show_if'         => $show_if,
				'toggle_slug'     => 'main_content',

			),
		);

		return $fields + $field_unprocessed;
	}
	/*
	 * Override the settings for the ajax container and pagination
	*/
	public function search_filter_form_attributes( $attributes, $sfid ) {

		if ( isset( $attributes['data-display-result-method'] ) ) {
			if ( $attributes['data-display-result-method'] == 'divi_post_module' && ! function_exists( 'wp_pagenavi' ) ) {
				$attributes['data-ajax-target']         = '.search-filter-results-' . $sfid;
				$attributes['data-ajax-links-selector'] = '.pagination a';
			} elseif ( $attributes['data-display-result-method'] == 'divi_post_module' && function_exists( 'wp_pagenavi' ) ) {
				$attributes['data-ajax-target']         = '.search-filter-results-' . $sfid;
				$attributes['data-ajax-links-selector'] = '.wp-pagenavi a';
			} elseif ( $attributes['data-display-result-method'] == 'divi_shop_module' ) {
				$attributes['data-ajax-target']         = '.search-filter-results-' . $sfid;
				$attributes['data-ajax-links-selector'] = '.page-numbers a';
			} elseif ( $attributes['data-display-result-method'] == 'custom_woocommerce_store' ) {
				$attributes['data-ajax-target']         = '.search-filter-results-' . $sfid;
				$attributes['data-ajax-links-selector'] = '.page-numbers a';
			} elseif ( $attributes['data-display-result-method'] == 'post_type_archive' ) {
				$attributes['data-ajax-target']         = '.search-filter-results-' . $sfid;
				$attributes['data-ajax-links-selector'] = '.pagination a';
			}
		}

		return $attributes;
	}

	/*
	 * Add a `display result` method for divi
	*/
	public function search_filter_admin_option_display_results( $display_results_options ) {

		$display_results_options['divi_post_module'] = array(
			'label'       => __( 'Divi Blog / Portfolio Module' ),
			'description' =>
				'<p>' . __( 'Search Results will displayed using a Divi Blog or Portfolio.', 'search-filter-divi' ) . '</p>' .
				'<p>' . __( 'Remember to update the setting in your module to use the Search &amp; Filter Query.', 'search-filter-divi' ) . '</p>',
			'base'        => 'shortcode',
		);
		$display_results_options['divi_shop_module'] = array(
			'label'       => __( 'Divi Shop Module' ),
			'description' =>
				'<p>' . __( 'Search Results will displayed using a Divi Shop Module.', 'search-filter-divi' ) . '</p>' .
				'<p>' . __( 'Remember to update the setting in your module to use the Search &amp; Filter Query.', 'search-filter-divi' ) . '</p>',
			'base'        => 'shortcode',
		);

		return $display_results_options;
	}
	/*
	 * Get available search forms as key => label array, for populating a `select`
	*/
	private function get_search_form_options() {

		if ( empty( $this->search_form_options ) ) {

			$custom_posts = new \WP_Query( 'post_type=search-filter-widget&post_status=publish&posts_per_page=-1' );

			if ( $custom_posts->post_count > 0 ) {
				foreach ( $custom_posts->posts as $post ) {
					$this->search_form_options[ 'search-filter-id-' . $post->ID ] = get_post_field( 'post_name', $post->ID );
				}
			}
		}

		return $this->search_form_options;
	}
}
