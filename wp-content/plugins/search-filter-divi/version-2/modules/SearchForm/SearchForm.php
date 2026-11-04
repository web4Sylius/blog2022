<?php
namespace Search_Filter_Divi_Extension\Version_2\Modules;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SearchForm extends \ET_Builder_Module {

	public $slug       = 'sfdivi_search_filter_form';
	public $vb_support = 'on';
	public $search_form_options;

	protected $module_credits = array(
		'module_uri' => 'https://searchandfilter.com/',
		'author'     => 'Code Amp',
		'author_uri' => 'https://codeamp.com/',
	);

	public function init() {
		$this->name = __( 'Search & Filter Form', 'search-filter-divi' );
		/*
		$this->whitelisted_fields = array(
			'search_form',
		);*/
		$this->advanced_fields        = array(
			'fonts' => array(
				'search_form' => array(
					'label'       => __( 'Search & Filter Form', 'search-filter-divi' ),
					'css'         => array(
						'main' => '%%order_class%% .sf_divi_select_text',
					),
					'line_height' => array( 'default' => '1em' ),
					'font_size'   => array( 'default' => '14px' ),
				),
			),
		);
		$this->settings_modal_toggles = array(
			'general' => array(
				'toggles' => array(
					'sg1' => __( 'Search & Filter Settings', 'search-filter-divi' ),
				),
			),
		);
	}

	public function get_fields() {

		$search_form_options = $this->get_search_form_options();

		return array(
			'search_form' => array(
				'label'           => esc_html__( 'Choose a Search Form', 'sfdivi-search-filter-divi' ),
				'type'            => 'select',
				'option_category' => 'basic_option',
				'description'     => __( 'Display your Search Form.', 'sfdivi-search-filter-divi' ),
				'toggle_slug'     => 'sg1',

				'options'         => array_merge( array( '' => 'Choose a Search Form' ), $search_form_options ),
				// 'options'     => $search_form_options,
			),
		);
	}

	public function get_search_form_options() {
		if ( empty( $this->search_form_options ) ) {

			$custom_posts = new \WP_Query( 'post_type=search-filter-widget&post_status=publish&posts_per_page=-1' );

			$this->search_form_options = array();
			foreach ( $custom_posts->posts as $post ) {
				$this->search_form_options[ 'search-filter-id-' . $post->ID ] = get_the_title( $post->ID );
			}

			wp_reset_postdata();
		}

		return $this->search_form_options;
	}

	public function render( $attrs, $content, $render_slug ) {
		$search_filter_id = intval( str_replace( 'search-filter-id-', '', $this->props['search_form'] ) );
		return do_shortcode( '[searchandfilter id="' . $search_filter_id . '"]' );
	}
}
new SearchForm();
