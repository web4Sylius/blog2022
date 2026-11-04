<?php
namespace Search_Filter_Divi_Extension\Version_3;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Extension extends \DiviExtension {
	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = \Search_Filter_Divi_Extension::SLUG;
	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = \Search_Filter_Divi_Extension::SLUG;
	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = \Search_Filter_Divi_Extension::VERSION;

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = '', $args = array() ) {

		// Bail early if we've detected the conditions are not met to load.
		if ( ! Plugin::$can_load ) {
			return;
		}

		// Divi loads files from this dir and url, lets treat `version 3` as its own Divi extension.
		$this->plugin_dir     = SEARCH_FILTER_DIVI_PATH . 'version-3/';
		$this->plugin_dir_url = SEARCH_FILTER_DIVI_URL . 'version-3/';

		// Call the parent constructor.
		parent::__construct( $name, $args );
	}
}
