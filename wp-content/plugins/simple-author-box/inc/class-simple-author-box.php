<?php

/**
 * Our main plugin class
 */
if (!defined('ABSPATH')) exit;

class Simple_Author_Box
{

    private static $instance = null;
    private $options;
    public $version;
    public $themes;
    /**
     * Function constructor
     */
    function __construct()
    {

        $this->load_dependencies();
        $this->define_admin_hooks();

        add_action('init', array($this, 'define_public_hooks'));
        add_action('widgets_init', array($this, 'sab_lite_register_widget'));

        add_action('wp_ajax_sab_dismiss_pointer', array($this, 'dismiss_pointer_ajax'));
        register_activation_hook(SIMPLE_AUTHOR_BOX_SLUG, array($this, 'activate_plugin'));
        add_action('in_admin_footer', array($this, 'admin_footer_js'));

        add_filter('admin_footer_text', array($this, 'admin_footer_text'));

        $current_version = $this->get_plugin_version();
        $installed_version = get_option('sab_version');
        if(false === $installed_version || true === version_compare($installed_version, $current_version, '<')){
            update_option('sab_version', $current_version);
            $this->reset_pointers();
        }
    }

    function sab_picker_footer_js()
    {
        echo '<style>.saboxplugin-wrap{
            display:none;
        }';
    }

    function admin_footer_js()
    {
        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function() {
                jQuery('#wp-admin-bar-edit-profile').after('<li id="wp-admin-bar-edit-profile"><a class="ab-item" href="' + jQuery('#wp-admin-bar-edit-profile > a').attr('href') + '#sabox-custom-profile-image">Edit SAB Profile</a></li>');
            });
        </script>
        <?php
    }

    public function admin_footer_text($text)
    {
        if (!$this->is_plugin_page()) {
            return $text;
        }

        $text = '<i class="wfsab-footer"><a href="' . $this->generate_web_link('admin_footer') . '" title="' . __('Visit Simple Author Box page for more info', 'simple-author-box') . '" target="_blank">Simple Author Box</a> v' . $this->get_plugin_version() . '. Please <a target="_blank" href="https://wordpress.org/support/plugin/simple-author-box/reviews/#new-post" title="Rate the plugin">rate the plugin <span>★★★★★</span></a> to help us spread the word. Thank you from the Simple Author Box team!</i>';

        return $text;
    } // admin_footer_text

    public function generate_web_link($placement = '', $page = '/', $params = array(), $anchor = '')
    {
        $base_url = 'https://wpauthorbox.com';

        if ('/' != $page) {
            $page = '/' . trim($page, '/') . '/';
        }
        if ($page == '//') {
            $page = '/';
        }

        $parts = array_merge(array('utm_source' => 'sab-free', 'utm_content' => $placement), $params);

        if (!empty($anchor)) {
            $anchor = '#' . trim($anchor, '#');
        }

        $out = $base_url . $page . '?' . http_build_query($parts, '', '&amp;') . $anchor;

        return $out;
    } // generate_web_link

    public function is_plugin_page()
    {
        $current_screen = get_current_screen();

        if ($current_screen->id == 'appearance_page_simple-author-box') {
            return true;
        } else {
            return false;
        }
    } // is_plugin_page

    function activate_plugin()
    {
        $this->reset_pointers();
    }

    static function reset_pointers()
    {
        $pointers = array();
        $sab_plugin_name = apply_filters('sab_plugin_name', __('Simple Author Box', 'simple-author-box'));
        $pointers['welcome'] = array('target' => '#menu-appearance', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing the <b style="font-weight: 800; font-variant: small-caps;">' . $sab_plugin_name . '</b> plugin! Please open <a href="' . admin_url('themes.php?page=simple-author-box') . '">Appearance - ' . $sab_plugin_name . '</a> to configure your author box.');
        update_option(SIMPLE_AUTHOR_POINTERS, $pointers);
    } // reset_pointers

    static function dismiss_pointer_ajax()
    {
        delete_option(SIMPLE_AUTHOR_POINTERS);
    }

    // Register Simple Author Box widget
    public function sab_lite_register_widget()
    {
        register_widget('Simple_Author_Box_Widget_LITE');
    }

    /**
     * Get plugin version
     *
     * @since 5.0
     *
     * @return int plugin version
     *
     */
    public function get_plugin_version()
    {
        if (isset($this->version)) {
            return $this->version;
        }

        $plugin_data = get_file_data(SIMPLE_AUTHOR_BOX_FILE, array('version' => 'Version'), 'plugin');
        $this->version = $plugin_data['version'];

        return $plugin_data['version'];
    } // get_plugin_version

    /**
     * Singleton pattern
     *
     * @return void
     */
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function load_dependencies()
    {

        require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-social.php';
        require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-helper.php';
        require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/functions.php';
        require_once SIMPLE_AUTHOR_BOX_PATH . '/inc/elementor/class-simple-author-box-elementor-check.php';
        require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-widget.php';

        if (apply_filters('sabox_remove_lite_block', true)) {
            require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-block.php';
        }

        // everything below this line gets loaded only in the admin back-end
        if (is_admin()) {
            require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-admin-page.php';
            require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-user-profile.php';
            require_once SIMPLE_AUTHOR_BOX_PATH . 'inc/class-simple-author-box-previewer.php';
        }
    }


    /**
     * Admin hooks
     *
     * @return void
     */
    private function define_admin_hooks()
    {

        /**
         * everything hooked here loads on both front-end & back-end
         */
        add_filter('get_avatar', array($this, 'replace_gravatar_image'), 10, 6);
        add_filter('amp_post_template_data', array($this, 'sab_amp_css')); // @since 2.0.7

        /**
         * Only load when we're in the admin panel
         */
        if (is_admin()) {
            add_action('init', array($this, 'initialize_admin'));
            add_action('admin_enqueue_scripts', array($this, 'admin_style_and_scripts'));
            add_filter('user_contactmethods', array($this, 'add_extra_fields'));
            add_filter('plugin_action_links_' . SIMPLE_AUTHOR_BOX_SLUG, array($this, 'settings_link'));
        }
    }

    public function initialize_admin()
    {
        global $simple_author_box_admin;
        // Class that handles admin page
        $simple_author_box_admin = new Simple_Author_Box_Admin_Page();

        // Class that handles author box previewer
        new Simple_Author_Box_Previewer();
    }


    /**
     * See this: https://codex.wordpress.org/Plugin_API/Filter_Reference/get_avatar
     *
     * Custom function to overwrite WordPress's get_avatar function
     *
     * @param [type] $avatar
     * @param [type] $id_or_email
     * @param [type] $size
     * @param [type] $default
     * @param [type] $alt
     * @param [type] $args
     *
     * @return void
     */
    public function replace_gravatar_image($avatar, $id_or_email, $size, $default, $alt, $args = array())
    {

        // Process the user identifier.
        $user = false;
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', absint($id_or_email));
        } elseif (is_string($id_or_email)) {

            $user = get_user_by('email', $id_or_email);
        } elseif ($id_or_email instanceof WP_User) {
            // User Object
            $user = $id_or_email;
        } elseif ($id_or_email instanceof WP_Post) {
            // Post Object
            $user = get_user_by('id', (int) $id_or_email->post_author);
        } elseif ($id_or_email instanceof WP_Comment) {

            if (!empty($id_or_email->user_id)) {
                $user = get_user_by('id', (int) $id_or_email->user_id);
            }
        }

        if (!$user || is_wp_error($user)) {
            return $avatar;
        }

        $custom_profile_image = get_user_meta($user->ID, 'sabox-profile-image', true);
        $class                = array('avatar', 'avatar-' . (int) $args['size'], 'photo');

        if (!$args['found_avatar'] || $args['force_default']) {
            $class[] = 'avatar-default';
        }

        if ($args['class']) {
            if (is_array($args['class'])) {
                $class = array_merge($class, $args['class']);
            } else {
                $class[] = $args['class'];
            }
        }

        $class[] = 'sab-custom-avatar';

        if ('' !== $custom_profile_image && true !== $args['force_default']) {

            $avatar = sprintf(
                "<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
                esc_attr($args['alt']),
                esc_url($custom_profile_image),
                esc_url($custom_profile_image) . ' 2x',
                esc_attr(join(' ', $class)),
                (int) $args['height'],
                (int) $args['width'],
                $args['extra_attr']
            );
        }

        return $avatar;
    }

    public function define_public_hooks()
    {

        $this->options = Simple_Author_Box_Helper::get_option('saboxplugin_options');

        add_action('wp_enqueue_scripts', array($this, 'saboxplugin_author_box_style'), 10);
        add_shortcode('simple-author-box', array($this, 'shortcode'));
        add_filter('sabox_hide_social_icons', array($this, 'show_social_media_icons'), 10, 2);
        add_filter('sabox_check_if_show', array($this, 'check_if_show_archive'), 10);

        if ('0' == $this->options['sab_autoinsert']) {
            add_filter('the_content', 'wpsabox_author_box');
        }

        if (isset($this->options['sab_footer_inline_style']) && '0' == $this->options['sab_footer_inline_style']) {
            add_action(
                'wp_footer',
                array(
                    $this,
                    'inline_style',
                ),
                13
            );
        } else {
            add_action('wp_head', array($this, 'inline_style'), 15);
        }
    }

    public function settings_link($links)
    {
        if (is_array($links)) {
            $sab = sprintf('<a href="%s">%s</a>', admin_url('themes.php?page=simple-author-box'), __('Configure', 'simple-author-box'));
            $buy = sprintf('<a href="%s"><b>%s</b></a>', admin_url('themes.php?page=simple-author-box#get-pro'), __('Get PRO', 'simple-author-box'));

            array_unshift($links, $buy);
            array_unshift($links, $sab);
        }

        return $links;
    }

    public function admin_style_and_scripts($hook)
    {

        $suffix = '';
        if (SIMPLE_AUTHOR_SCRIPT_DEBUG) {
            $suffix = '';
        }



        // globally loaded
        wp_enqueue_style('sabox-css', SIMPLE_AUTHOR_BOX_ASSETS . 'css/sabox.css', array(), SIMPLE_AUTHOR_BOX_VERSION);


        // loaded only on plugin page
        if ('appearance_page_simple-author-box' == $hook) {
            // todo: maybe uncomment later
            delete_option(SIMPLE_AUTHOR_POINTERS);

            // Styles
            wp_enqueue_style('saboxplugin-admin-style', SIMPLE_AUTHOR_BOX_ASSETS . 'css/sabox-admin-style' . $suffix . '.css', array(), SIMPLE_AUTHOR_BOX_VERSION);
            wp_enqueue_style('saboxplugin-admin-font', 'https://fonts.bunny.net/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap');

            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('saboxplugin-sweetalert', SIMPLE_AUTHOR_BOX_ASSETS . 'css/sweetalert2.min.css', array(), SIMPLE_AUTHOR_BOX_VERSION);

            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-dialog');

            // Scripts
            wp_enqueue_script(
                'sabox-admin-js',
                SIMPLE_AUTHOR_BOX_ASSETS . 'js/sabox-admin.js',
                array(
                    'jquery-ui-slider',
                    'wp-color-picker',
                ),
                SIMPLE_AUTHOR_BOX_VERSION,
                true
            );

            wp_enqueue_script(
                'sabox-sweetalert',
                SIMPLE_AUTHOR_BOX_ASSETS . 'js/sweetalert2.min.js',
                array(
                    'jquery-ui-slider',
                    'wp-color-picker',
                ),
                SIMPLE_AUTHOR_BOX_VERSION,
                true
            );

            $sabox_admin['sab_is_plugin_page'] = true;

            wp_localize_script('sabox-sweetalert', 'sabox_admin', $sabox_admin);

            wp_dequeue_style('uiStyleSheet');
            wp_dequeue_style('wpcufpnAdmin');
            wp_dequeue_style('unifStyleSheet');
            wp_dequeue_style('wpcufpn_codemirror');
            wp_dequeue_style('wpcufpn_codemirrorTheme');
            wp_dequeue_style('collapse-admin-css');
            wp_dequeue_style('jquery-ui-css');
            wp_dequeue_style('tribe-common-admin');
            wp_dequeue_style('file-manager__jquery-ui-css');
            wp_dequeue_style('file-manager__jquery-ui-css-theme');
            wp_dequeue_style('wpmegmaps-jqueryui');
            wp_dequeue_style('wp-botwatch-css');
            wp_dequeue_style('njt-filebird-admin');
            wp_dequeue_style('ihc_jquery-ui.min.css');
            wp_dequeue_style('badgeos-juqery-autocomplete-css');
            wp_dequeue_style('mainwp');
            wp_dequeue_style('mainwp-responsive-layouts');
            wp_dequeue_style('jquery-ui-style');
            wp_dequeue_style('additional_style');
            wp_dequeue_style('wobd-jqueryui-style');
            wp_dequeue_style('wpdp-style3');
            wp_dequeue_style('jquery_smoothness_ui');
            wp_dequeue_style('uap_main_admin_style');
            wp_dequeue_style('uap_font_awesome');
            wp_dequeue_style('uap_jquery-ui.min.css');
        // loaded only on user profile page
        } elseif ('profile.php' == $hook || 'user-edit.php' == $hook) {

            wp_enqueue_style('saboxplugin-admin-style', SIMPLE_AUTHOR_BOX_ASSETS . 'css/sabox-admin-style' . $suffix . '.css');

            wp_enqueue_media();
            wp_enqueue_editor();
            wp_enqueue_script('sabox-admin-editor-js', SIMPLE_AUTHOR_BOX_ASSETS . 'js/sabox-editor.js', array(), false, true);
            $sabox_js_helper = array();
            $social_icons    = apply_filters('sabox_social_icons', Simple_Author_Box_Helper::$social_icons);
            unset($social_icons['user_email']);

            $sabox_js_helper['socialIcons'] = $social_icons;
            wp_localize_script('sabox-admin-editor-js', 'SABHelper', $sabox_js_helper);
        }

        $pointers = get_option(SIMPLE_AUTHOR_POINTERS);

        if ($pointers && 'appearance_page_simple-author-box' != $hook) {
            $pointers['_nonce_dismiss_pointer'] = wp_create_nonce('sab_dismiss_pointer');
            $pointers['plugin_name'] = apply_filters('sab_plugin_name', __('Simple Author Box', 'simple-author-box'));
            wp_enqueue_script('sab-pointers', SIMPLE_AUTHOR_BOX_ASSETS . 'js/sab-pointers.js', array(), false, true);
            wp_enqueue_script('wp-pointer');
            wp_enqueue_style('wp-pointer');
            wp_localize_script('wp-pointer', 'sab_pointers', $pointers);
        }
    }

    public function add_extra_fields($extra_fields)
    {

        unset($extra_fields['aim']);
        unset($extra_fields['jabber']);
        unset($extra_fields['yim']);

        return $extra_fields;
    }

    /*----------------------------------------------------------------------------------------------------------
		Adding the author box main CSS
	-----------------------------------------------------------------------------------------------------------*/
    public function saboxplugin_author_box_style()
    {

        $suffix = '.min';
        if (SIMPLE_AUTHOR_SCRIPT_DEBUG) {
            $suffix = '';
        }

        $sab_protocol   = is_ssl() ? 'https' : 'http';
        $sab_box_subset = Simple_Author_Box_Helper::get_option('sab_box_subset');

        /**
         * Check for duplicate font families, remove duplicates & re-work the font enqueue procedure
         *
         * @since 2.0.4
         */
        if ('none' != strtolower($sab_box_subset)) {
            $sab_subset = '&amp;subset=' . strtolower($sab_box_subset);
        } else {
            $sab_subset = '&amp;subset=latin';
        }

        $sab_author_font = Simple_Author_Box_Helper::get_option('sab_box_name_font');
        $sab_desc_font   = Simple_Author_Box_Helper::get_option('sab_box_desc_font');
        $sab_web_font    = Simple_Author_Box_Helper::get_option('sab_box_web_font');

        $bunny_fonts = array();

        if ($sab_author_font && 'none' != strtolower($sab_author_font)) {
            $bunny_fonts[] = str_replace(' ', '+', esc_attr($sab_author_font));
        }

        if ($sab_desc_font && 'none' != strtolower($sab_desc_font)) {
            $bunny_fonts[] = str_replace(' ', '+', esc_attr($sab_desc_font));
        }

        if ('1' == $this->options['sab_web'] && $sab_web_font && 'none' != strtolower($sab_web_font)) {
            $bunny_fonts[] = str_replace(' ', '+', esc_attr($sab_web_font));
        }

        $bunny_fonts = apply_filters('sabox_bunny_fonts', $bunny_fonts);

        $bunny_fonts = array_unique($bunny_fonts);

        if (!empty($bunny_fonts)) { // let's check the array's not empty before actually loading; we want to avoid loading 'none' font-familes
            $final_bunny_fonts = array();

            foreach ($bunny_fonts as $v) {
                $final_bunny_fonts[] = $v . ':400,700,400italic,700italic';
            }

            wp_register_style('sab-font', $sab_protocol . '://fonts.bunny.net/css?family=' . implode('|', $final_bunny_fonts) . $sab_subset, array(), null);
        }
        /**
         * end changes introduced in 2.0.4
         */

        if (!is_single() && !is_page() && !is_author() && !is_archive()) {
            return;
        }

        if (!empty($bunny_fonts)) {
            wp_enqueue_style('sab-font');
        }
    }


    public function inline_style()
    {

        if (!is_single() && !is_page() && !is_author() && !is_archive()) {
            return;
        }

        $style = '<style type="text/css">';
        $style .= Simple_Author_Box_Helper::generate_inline_css();
        $style .= '</style>';

        wpsabox_wp_kses_wf($style);
    }

    public function shortcode($atts)
    {
        $defaults = array(
            'ids' => '',
        );

        $atts = wp_parse_args($atts, $defaults);

        if ('' != $atts['ids']) {


            if ('all' != $atts['ids']) {
                $ids = explode(',', $atts['ids']);
            } else {
                $ids = get_users(array('fields' => 'ID'));
            }

            ob_start();
            $sabox_options = Simple_Author_Box_Helper::get_option('saboxplugin_options');
            if (!empty($ids)) {
                foreach ($ids as $user_id) {

                    $template        = Simple_Author_Box_Helper::get_template();
                    $sabox_author_id = $user_id;
                    echo '<div class="sabox-plus-item">';
                    include($template);
                    echo '</div>';
                }
            }

            $html = ob_get_clean();
        } else {
            $html = wpsabox_author_box();
        }

        return $html;
    }


    public function show_social_media_icons($return, $user)
    {
        if (isset($user->roles) && in_array('sab-guest-author', (array) $user->roles)) {
            return false;
        }

        return true;
    }

    public function check_if_show_archive()
    {

        if (!is_single() && !is_author() && !is_archive()) {
            return false;
        }

        if (1 == $this->options['sab_hide_on_archive'] && !is_single()) {
            return false;
        }

        return true;
    }

    /**
     * AMP compatibility
     * @since 2.0
     *
     * @param $data
     *
     * @return mixed
     */

    function sab_amp_css($data)
    {

        $options = Simple_Author_Box_Helper::get_option('saboxplugin_options');

        $icon_size = absint(Simple_Author_Box_Helper::get_option('sab_box_icon_size'));
        if ('1' == $options['sab_colored']) {
            $icon_size = $icon_size * 2;
        }

        $data['post_amp_styles'] = array(
            '.saboxplugin-wrap .saboxplugin-gravatar'                                      => array(
                'float: left',
                'padding:0 20px 20px 20px',
            ),
            '.saboxplugin-wrap .saboxplugin-gravatar img'                                  => array(
                'max-width: 100px',
                'height: auto',
            ),
            '.saboxplugin-wrap .saboxplugin-authorname'                                    => array(
                'font-size: 18px',
                'line-height: 1',
                'margin: 20px 0 0 20px',
                'display: block',
            ),
            '.saboxplugin-wrap .saboxplugin-authorname a'                                  => array(
                'text-decoration: none',
            ),
            '.saboxplugin-wrap .saboxplugin-desc'                                          => array(
                'display: block',
                'margin: 5px 20px',
            ),
            '.saboxplugin-wrap .saboxplugin-desc a'                                        => array(
                'text-decoration: none',
            ),
            '.saboxplugin-wrap .saboxplugin-desc p'                                        => array(
                'margin: 5px 0 12px 0',
                'font-size: ' . absint(Simple_Author_Box_Helper::get_option('sab_box_desc_size')) . 'px',
                'line-height: ' . absint(Simple_Author_Box_Helper::get_option('sab_box_desc_size') + 7) . 'px',
            ),
            '.saboxplugin-wrap .saboxplugin-web'                                           => array(
                'margin: 0 20px 15px',
                'text-align: left',
            ),
            '.saboxplugin-wrap .saboxplugin-socials'                                       => array(
                'position: relative',
                'display: block',
                'background: #fcfcfc',
                'padding: 5px',
                'border-top: 1px solid #eee;',
            ),
            '.saboxplugin-wrap .saboxplugin-socials a'                                     => array(
                'text-decoration: none',
                'box-shadow: none',
                'padding: 0',
                'margin: 0',
                'border: 0',
                'transition: opacity 0.4s',
                '-webkit-transition: opacity 0.4s',
                '-moz-transition: opacity 0.4s',
                '-o-transition: opacity 0.4s',
                'display: inline-block',
            ),
            '.saboxplugin-wrap .saboxplugin-socials .saboxplugin-icon-grey'                => array(
                'display: inline-block',
                'vertical-align: middle',
                'margin: 10px 5px',
                'color: #444',
                'fill: #444',
            ),
            '.saboxplugin-wrap .saboxplugin-socials a svg'                                 => array(
                'width:' . absint($icon_size) . 'px;',
                'height:' . absint($icon_size) . 'px',
                'display:block'
            ),
            '.saboxplugin-wrap .saboxplugin-socials.sabox-colored .saboxplugin-icon-color' => array(
                'color: #FFF',
                'margin: 5px',
                'vertical-align: middle',
                'display: inline-block',
            ),
            '.saboxplugin-wrap .clearfix'                                                  => array(
                'clear:both;',
            ),
            '.saboxplugin-wrap .saboxplugin-socials a svg .st2'                            => array(
                'fill: #fff;'
            ),
            '.saboxplugin-wrap .saboxplugin-socials a svg .st1'                            => array(
                'fill: rgba( 0, 0, 0, .3 );'
            ),
            'img.sab-custom-avatar'                                                        => array(
                'max-width:75px;'
            ),
            // custom paddings & margins
            '.saboxplugin-wrap'                                                            => array(
                'margin-top: ' . absint(Simple_Author_Box_Helper::get_option('sab_box_margin_top')) . 'px',
                'margin-bottom: ' . absint(Simple_Author_Box_Helper::get_option('sab_box_margin_bottom')) . 'px',
                'padding: ' . absint(Simple_Author_Box_Helper::get_option('sab_box_padding_top_bottom')) . 'px ' . absint(Simple_Author_Box_Helper::get_option('sab_box_padding_left_right')) . 'px',
                'box-sizing: border-box',
                'border: 1px solid #EEE',
                'width: 100%',
                'clear: both',
                'overflow : hidden',
                'word-wrap: break-word',
                'position: relative',
            ),
            '.sab-edit-settings'                                                           => array(
                'display: none;',
            ),
            '.sab-profile-edit'                                                            => array(
                'display: none;',
            ),
        );

        if ('1' == $options['sab_colored'] && '1' != $options['sab_box_long_shadow']) {
            $data['post_amp_styles']['.saboxplugin-wrap .saboxplugin-socials .saboxplugin-icon-color .st1'] = array(
                'display: none;',
            );
        }

        return $data;
    }
}
