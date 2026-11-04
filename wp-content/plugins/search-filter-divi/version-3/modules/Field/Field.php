<?php

namespace Search_Filter_Divi_Extension\Version_3\Modules;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Field extends \ET_Builder_Module {

	public $slug          = 'search_filter_field';
	public $vb_support    = 'on';
	public $field_options = array();

	protected $module_credits = array(
		'module_uri' => 'https://searchandfilter.com/',
		'author'     => 'Code Amp',
		'author_uri' => 'https://searchandfilter.com/',
	);

	public function init() {
		$this->name = __( 'Search & Filter Field', 'search-filter-divi' );

		// It's not possible to add dependencies to our module scripts, so we need to patch
		// it by modiifying the global $wp_scripts object.

		add_action(
			'wp_enqueue_scripts',
			function () {
				global $wp_scripts;

				$module_handle = 'search-filter-divi-frontend-bundle';
				$dependency    = 'search-filter-frontend';

				if ( ! isset( $wp_scripts->registered[ $module_handle ] ) ) {
					return;
				}

				if ( isset( $wp_scripts->registered[ $module_handle ]->deps ) ) {
					// Merge to avoid duplicates
					$wp_scripts->registered[ $module_handle ]->deps = array_unique(
						array_merge(
							$wp_scripts->registered[ $module_handle ]->deps,
							array( $dependency )
						)
					);
				}
			},
			100
		);

		$this->advanced_fields = array(
			'margin_padding' => array(
				'css' => array(
					'main'    => '%%order_class%%',
					'padding' => '%%order_class%% .search-filter-divi-field-container',
					'margin'  => '%%order_class%%',
				),
			),
			'borders'        => array(
				'default' => array(
					'css' => array(
						'main' => array(
							'border_radii'        => '%%order_class%% .search-filter-divi-field-container',
							'border_styles'       => '%%order_class%% .search-filter-divi-field-container',
							'border_radii_hover'  => '%%order_class%% .search-filter-divi-field-container:hover',
							'border_styles_hover' => '%%order_class%% .search-filter-divi-field-container:hover',
						),
					),
				),
			),
			'background'     => array(
				'css'                           => array(
					'main'  => '%%order_class%% .search-filter-divi-field-container',
					'hover' => '%%order_class%% .search-filter-divi-field-container:hover',
				),
				'use_background_image_parallax' => false,
				'use_background_video'          => false,
			),
			'box_shadow'     => array(
				'default' => array(
					'css' => array(
						'main'    => '%%order_class%% .search-filter-divi-field-container',
						'overlay' => 'inset',
					),
				),
			),
			'transform'      => array(
				'css' => array(
					'main'  => '%%order_class%% .search-filter-divi-field-container',
					'hover' => '%%order_class%% .search-filter-divi-field-container:hover',

				),
			),
			'filters'        => array(
				'css' => array(
					'main'  => '%%order_class%% .search-filter-divi-field-container',
					'hover' => '%%order_class%% .search-filter-divi-field-container:hover',
				),
			),
			'max_width'      => array(
				'use_max_width'        => false,
				'use_width'            => false,
				'use_module_alignment' => false,
			),
			'height'         => array(
				'use_height'     => false,
				'use_max_height' => false,
				'use_min_height' => false,
			),
			// 'fonts'          => false,
			'text'           => false,
			'button'         => false,
			'link_options'   => false,
			'fonts'          => array(
				'field_id' => array(
					'label'       => __( 'Search & Filter Field', 'search-filter-divi' ),
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

		$field_options = $this->get_field_options();

		return array(
			'field_id' => array(
				'label'           => esc_html__( 'Choose a Field', 'sfdivi-search-filter-divi' ),
				'type'            => 'select',
				'option_category' => 'basic_option',
				'description'     => __( 'Display your Field.', 'sfdivi-search-filter-divi' ),
				'toggle_slug'     => 'sg1',
				'options'         => array_merge( array( '' => 'Choose a Field' ), $field_options ),
			),
		);
	}

	/**
	 * Get the fields.
	 *
	 * @return array The fields.
	 */
	public function get_field_options() {
		if ( empty( $this->field_options ) ) {
			$fields = \Search_Filter\Fields::find(
				array(
					'status' => 'enabled', // Only get enabled queries.
					'number' => 0, // Get all.
				),
				'records'
			);

			foreach ( $fields as $field_record ) {
				$this->field_options[ 'field_' . $field_record->get_id() ] = $field_record->get_name();
			}
		}

		return $this->field_options;
	}

	public function render( $attrs, $content, $render_slug ) {
		$field_id = intval( str_replace( 'field_', '', $this->props['field_id'] ) );
		return '<div class="search-filter-divi-field-container">' . do_shortcode( '[searchandfilter field="' . $field_id . '"]' ) . '</div>';
	}
}

new Field();
