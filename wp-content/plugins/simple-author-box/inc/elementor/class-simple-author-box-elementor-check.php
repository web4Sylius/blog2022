<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

final class SAB_Elementor_Check {

    /**
     * Plugin Version
     *
     * @since 1.2.0
     * @var string The plugin version.
     */
    const VERSION = '2.2.2';

    /**
     * Minimum Elementor Version
     *
     * @since 1.2.0
     * @var string Minimum Elementor version required to run the elementor block.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.4.5';

    /**
     * Minimum PHP Version
     *
     * @since 1.2.0
     * @var string Minimum PHP version required to run the elementor block.
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Constructor
     *
     * @since  1.0.0
     * @access public
     */
    public function __construct() {

        // Init Plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init() {

        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_elementor_version'));
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
            return;
        }

        // Once we get here, We have passed all validation checks so we can safely include our elementor block activation
        require_once(SIMPLE_AUTHOR_BOX_PATH.'inc/elementor/class-simple-author-box-elementor-widget-activation.php');
    }


    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @since  1.0.0
     * @access public
     */
    public function admin_notice_minimum_elementor_version() {
         // phpcs:ignore as no nonce check is needed for this notice
        if (isset($_GET['activate'])) { // phpcs:ignore
            unset($_GET['activate']); 
        }

        /* translators: %1$s is replaced with the widget name, %2$s with Elementor name and %3$s with minimum elementor version */
        $message = sprintf(esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'simple-author-box'),
            '<strong>' . esc_html__('Simple Author Box Elementor widget', 'simple-author-box') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'simple-author-box') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        wpsabox_wp_kses_wf('<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>');
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since  1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version() {
        // phpcs:ignore as no nonce check is needed for this notice
        if (isset($_GET['activate'])) { // phpcs:ignore
            unset($_GET['activate']); 
        }

        /* translators: %1$s is replaced with the widget name, %2$s with the PHP name and %3$s with minimum elementor version */
        $message = sprintf(esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'simple-author-box'),
            '<strong>' . esc_html__('Simple Author Box Elementor widget', 'simple-author-box') . '</strong>',
            '<strong>' . esc_html__('PHP', 'simple-author-box') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        wpsabox_wp_kses_wf('<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>');
    }
}

new SAB_Elementor_Check();
