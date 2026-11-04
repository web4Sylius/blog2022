<?php

class Simple_Author_Box_Admin_Page
{

    private $tab;
    private $options;
    private $settings;
    private $sections;
    private $views_path;

    function __construct()
    {
        $this->views_path = SIMPLE_AUTHOR_BOX_PATH . 'inc/admin/';

        $default_sections = array();


        $default_sections['visibility'] = array(
            'label' => __('Visibility', 'simple-author-box')
        );

        $default_sections['elements'] = array(
            'label' => __('Elements', 'simple-author-box')
        );

        $default_sections['appearance-options'] = array(
            'label' => __('Appearance', 'simple-author-box')
        );


        $default_sections['author-tabs'] = array(
            'label' => __('Author Box Tabs', 'simple-author-box')
        );

        $default_sections['color-options'] = array(
            'label' => __('Colors', 'simple-author-box')
        );

        // todo: temporarily removed
        /*
        $default_sections['themes'] = array(
            'label' => __('Themes', 'simple-author-box')
        );
        */

        $default_sections['typography-options'] = array(
            'label' => __('Typography', 'simple-author-box')
        );

        $default_sections['guest-author-options'] = array(
            'label' => __('Guest Author', 'simple-author-box'),
        );

        $default_sections['advanced'] = array(
            'label' => __('Advanced', 'simple-author-box')
        );

        $settings = array(
            'visibility' => array(
                'sab_autoinsert'     => array(
                    'label'       => __('Manually insert the Simple Author Box', 'simple-author-box'),
                    'description' => __('When turned ON, the author box will no longer be automatically added to your post. You\'ll need to manually add it using shortcodes or a PHP function.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'plugin_code'        => array(
                    'label'     => __('PHP Code', 'simple-author-box'),
                    'description'     => __('If you want to manually insert the Simple Author Box in your template file (single post view), you can use the following code snippet', 'simple-author-box'),
                    'type'      => 'readonly',
                    'value'     => '&lt;?php if ( function_exists( \'wpsabox_author_box\' ) ) echo wpsabox_author_box(); ?&gt;',
                    'condition' => 'sab_autoinsert',
                ),
                'plugin_shortcode'   => array(
                    'label'     => __('Shortcode', 'simple-author-box'),
                    'description'     => __('If you want to manually insert the Simple Author Box in your post content, you can use the following shortcode', 'simple-author-box'),
                    'type'      => 'readonly',
                    'value'     => '[simple-author-box]',
                    'condition' => 'sab_autoinsert',
                ),
                'sab_no_description' => array(
                    'label'       => __('Hide the author box if author description is empty', 'simple-author-box'),
                    'description' => __('When turned ON, the author box will not appear for users without a description', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_hide_on_archive' => array(
                    'label'       => __('Hide the author box on archives', 'simple-author-box'),
                    'description' => __('When turned ON, the author box will be removed on archives.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_position' => array(
                    'label'       => __('Author box position', 'simple-author-box'),
                    'description' => __('Select where you want to show the author box ( before or after the content )', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        'top'    => __('Before Content', 'simple-author-box'),
                        'bottom' => __('After Content', 'simple-author-box'),
                    ),
                    'default'     => 'bottom',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_author_link' => array(
                    'label'       => __('Author name should link to', 'simple-author-box'),
                    'description' => __('Use the drop-down to select where the author name should link to. You can also select None to remove the link.', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        'author-page'    => __('Author\'s Page', 'simple-author-box'),
                        'author-website' => __('Author\'s Website', 'simple-author-box'),
                        'none'           => __('None', 'simple-author-box'),
                    ),
                    'default'     => 'author-page',
                    'profeature'  => true,
                    'group'       => 'saboxplugin_options',
                ),
                'sab_author_link_noffolow' => array(
                    'label'       => __('Add "nofollow" attribute on author name link', 'simple-author-box'),
                    'description' => __('Toggling this to ON will make the author name link have the no-follow parameter added.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'default'     => '0',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_visibility' => array(
                    'label'       => __('Show author box on', 'simple-author-box'),
                    'description' => __('Choose which post types to display the author box on', 'simple-author-box'),
                    'type'        => 'multiplecheckbox',
                    'handle'      => array('Simple_Author_Box_Helper', 'get_custom_post_type'),
                    'default'     => array('post'),
                    'profeature'  => true,
                    'group'       => 'saboxplugin_options',
                ),
                'sab_tax_visibility' => array(
                    'label'       => __('Show author box on', 'simple-author-box'),
                    'description' => __('Choose which taxonomy page to display the author box on', 'simple-author-box'),
                    'type'        => 'multiplecheckbox',
                    'handle'      => array('Simple_Author_Box_Helper', 'get_taxonomy_types'),
                    'default'     => array(''),
                    'profeature'  => true,
                    'group'       => 'saboxplugin_options',
                ),
                'sabox_author_different_description' => array(
                    'label'       => __('Use custom biography', 'simple-author-box'),
                    'description' => __('When turned ON a textarea for custom biography will be available when editing a user\'s profile. The Custom Biography will be displayed inside the Author Box so you can use WordPress\' default biography field for the author page, for example.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                )
            ),
            'elements' => array(
                'sab_email'        => array(
                    'label'       => __('Show author email', 'simple-author-box'),
                    'description' => __('When turned ON, the plugin will add an email option next to the social icons.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_link_target'  => array(
                    'label'       => __('Open social icon links in a new tab', 'simple-author-box'),
                    'description' => __('When turned ON, the author’s social links will open in a new tab.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_hide_socials' => array(
                    'label'       => __('Hide the social icons on author box', 'simple-author-box'),
                    'description' => __('When turned ON, the author’s social icons will be hidden.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
            ),
            'appearance-options'    => array(
                'sab_box_margin_top'         => array(
                    'label'       => __('Top margin of author box', 'simple-author-box'),
                    'description' => __('Choose how much space to add above the author box', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 100,
                        'increment' => 1,
                    ),
                    'default'     => '0',
                ),
                'sab_box_margin_bottom'      => array(
                    'label'       => __('Bottom margin of author box', 'simple-author-box'),
                    'description' => __('Choose how much space to add below the author box', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 100,
                        'increment' => 1,
                    ),
                    'default'     => '0',
                ),
                'sab_box_padding_top_bottom' => array(
                    'label'       => __('Padding top and bottom of author box', 'simple-author-box'),
                    'description' => __('This controls the padding top & bottom of the author box', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 100,
                        'increment' => 1,
                    ),
                    'default'     => '0',
                ),
                'sab_box_padding_left_right' => array(
                    'label'       => __('Padding left and right of author box', 'simple-author-box'),
                    'description' => __('This controls the padding left & right of the author box', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 100,
                        'increment' => 1,
                    ),
                    'default'     => '0',
                ),
                'sab_box_border_width'       => array(
                    'label'       => __('Border Width', 'simple-author-box'),
                    'description' => __('This controls the border width of the author box', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 100,
                        'increment' => 1,
                    ),
                    'default'     => '1',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_avatar_size'         => array(
                    'label'       => __('Author avatar image size', 'simple-author-box'),
                    'description' => __('Choose the size of the author’s avatar image', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 0,
                        'max'       => 264,
                        'increment' => 1,
                    ),
                    'default'     => '100',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_avatar_style'           => array(
                    'label'       => __('Author avatar image style', 'simple-author-box'),
                    'description' => __('Change the shape of the author’s avatar image', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        0 => __('Square', 'simple-author-box'),
                        1 => __('Circle', 'simple-author-box')
                    ),
                    'choices_pro'     => array(
                        2 => __('Fancy', 'simple-author-box'),
                        3 => __('Elypse', 'simple-author-box'),
                        4 => __('Shear', 'simple-author-box'),
                        5 => __('Speed', 'simple-author-box')
                    ),
                    'default'     => '0',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_avatar_hover'           => array(
                    'label'       => __('Rotate effect on author avatar hover', 'simple-author-box'),
                    'description' => __('When turned ON, this adds a rotate effect when hovering over the author\'s avatar', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'sab_avatar_style',
                ),
                'sab_web'                    => array(
                    'label'       => __('Show author website', 'simple-author-box'),
                    'description' => __('When turned ON, the box will include the author\'s website', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),

                'sab_web_target' => array(
                    'label'       => __('Open author website link in a new tab', 'simple-author-box'),
                    'description' => __('If you check this the author\'s link will open in a new tab', 'simple-author-box'),
                    'type'        => 'toggle',
                    'condition'   => 'sab_web',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_web_rel'    => array(
                    'label'       => __('Add "nofollow" attribute on author website link', 'simple-author-box'),
                    'description' => __('Toggling this to ON will make the author website have the no-follow parameter added.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'condition'   => 'sab_web',
                    'group'       => 'saboxplugin_options',
                ),

                'sab_web_position'    => array(
                    'label'       => __('Author website position', 'simple-author-box'),
                    'description' => __('Select where you want to show the website ( left or right )', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        0 => __('Left', 'simple-author-box'),
                        1 => __('Right', 'simple-author-box'),
                    ),
                    'default'     => '0',
                    'condition'   => 'sab_web',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_colored'         => array(
                    'label'       => __('Social icons type', 'simple-author-box'),
                    'description' => __('Colored background adds a background behind the social icon symbol', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        0 => __('Symbols', 'simple-author-box'),
                        1 => __('Colored', 'simple-author-box'),
                    ),
                    'default'     => '0',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_icons_style'     => array(
                    'label'       => __('Social icons style', 'simple-author-box'),
                    'description' => __('Select the shape of social icons\' container', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        0 => __('Squares', 'simple-author-box'),
                        1 => __('Circle', 'simple-author-box'),
                    ),
                    'default'     => '0',
                    'condition'   => 'sab_colored',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_social_hover'    => array(
                    'label'       => __('Rotate effect on social icons hover (works only for circle icons)', 'simple-author-box'),
                    'description' => __('Add a rotate effect when you hover on social icons hover', 'simple-author-box'),
                    'type'        => 'toggle',
                    'condition'   => 'sab_colored',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_long_shadow' => array(
                    'label'       => __('Use flat long shadow effect', 'simple-author-box'),
                    'description' => __('Check this if you want a flat shadow for social icons', 'simple-author-box'),
                    'type'        => 'toggle',
                    'condition'   => 'sab_colored',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_thin_border' => array(
                    'label'       => __('Show a thin border on colored social icons', 'simple-author-box'),
                    'description' => __('Add a border to social icons container.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'condition'   => 'sab_colored',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_border_type' => array(
                    'label'       => __('Author box border style', 'simple-author-box'),
                    'description' => __('Choose which border style the author box should have', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        'none'      => __('None', 'simple-author-box'),
                        'dotted'    => __('Dotted', 'simple-author-box'),
                        'dashed'    => __('Dashed', 'simple-author-box'),
                        'solid'     => __('Solid', 'simple-author-box'),
                        'double'    => __('Double', 'simple-author-box'),
                        'groove'    => __('Groove', 'simple-author-box'),
                        'ridge'     => __('Ridge', 'simple-author-box'),
                        'inset'     => __('Inset', 'simple-author-box'),
                        'outset'    => __('Outset', 'simple-author-box'),
                    ),
                    'default'     =>  'solid',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_border_location' => array(
                    'label'       => __('Select author box border', 'simple-author-box'),
                    'description' => __('Choose which border the author box should have', 'simple-author-box'),
                    'type'        => 'multiplecheckbox',
                    'choices'     => array(
                        'top'      => __('Top', 'simple-author-box'),
                        'bottom'   => __('Bottom', 'simple-author-box'),
                        'left'     => __('Left', 'simple-author-box'),
                        'right'    => __('Right', 'simple-author-box'),
                    ),
                    'default'     => array('top', 'bottom', 'left', 'right'),
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_hide_brackets' => array(
                    'label'       => __('Hide brackets in the job title', 'simple-author-box'),
                    'description' => __('When turned ON brackets won\'t be shown', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_bg_image' => array(
                    'label'       => __('Background image', 'simple-author-box'),
                    'description' => __('Set a background image for the Simple Author Box', 'simple-author-box'),
                    'default'     => '',
                    'type'        => 'image',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_hide_theme_author_box' => array(
                    'label'       => __('Hide existing author box', 'simple-author-box'),
                    'description' => __('If your theme already has an author box or other element you wish to hide when Simple Author Box is displayed, use the picker to select it or enter it\'s CSS selector in the box.', 'simple-author-box'),
                    'default'     => '',
                    'type'        => 'picker',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                )
            ),
            'color-options'         => array(
                'sab_box_author_color'   => array(
                    'label'       => __('Author name color', 'simple-author-box'),
                    'description' => __('Select the color for author\'s name text', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_web_color'      => array(
                    'label'       => __('Author website link color', 'simple-author-box'),
                    'description' => __('Select the color for author\'s website link', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'sab_web',
                ),
                'sab_box_border'         => array(
                    'label'       => __('Border color', 'simple-author-box'),
                    'description' => __('Select the color for author box border', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_icons_back'     => array(
                    'label'       => __('Background color of social icons bar', 'simple-author-box'),
                    'description' => __('Select the color for the social icons bar background', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_author_back'    => array(
                    'label'       => __('Background color of author box', 'simple-author-box'),
                    'description' => __('Select the color for the author box background', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_author_p_color' => array(
                    'label'       => __('Color of author box paragraphs', 'simple-author-box'),
                    'description' => __('Select the color for the author box paragraphs', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_author_a_color' => array(
                    'label'       => __('Color of author box links', 'simple-author-box'),
                    'description' => __('Select the color for the author box links', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_box_icons_color'    => array(
                    'label'       => __('Social icons color (for symbols only)', 'simple-author-box'),
                    'description' => __('Select the color for social icons when using the symbols only social icon type', 'simple-author-box'),
                    'type'        => 'color',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_border_color' => array(
                    'label'       => __('Author box border color', 'simple-author-box'),
                    'description' => __('Choose which color the author box border should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#eee',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                )
            ),
            'typography-options'    => array(
                'sab_box_subset'    => array(
                    'label'       => __('Bunny font characters subset', 'simple-author-box'),
                    'description' => __('Some Bunny Fonts do not support all non-latin character subsets', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => Simple_Author_Box_Helper::get_bunny_font_subsets(),
                    'default'     => 'none',
                ),
                'sab_box_name_font' => array(
                    'label'       => __('Author name font family', 'simple-author-box'),
                    'description' => __('Select the font family for the author\'s name', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => Simple_Author_Box_Helper::get_bunny_fonts(),
                    'default'     => 'None',
                ),
                'sab_box_job_font' => array(
                    'label'       => __('Author job title font family', 'simple-author-box'),
                    'description' => __('Select the font family for the author\'s job title', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => Simple_Author_Box_Helper::get_bunny_fonts(),
                    'default'     => 'None',
                    'profeature'  => true,
                ),
                'sab_box_web_font'  => array(
                    'label'       => __('Author website font family', 'simple-author-box'),
                    'description' => __('Select the font family for the author\'s website', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => Simple_Author_Box_Helper::get_bunny_fonts(),
                    'default'     => 'None',
                    'condition'   => 'sab_web',
                ),
                'sab_box_desc_font' => array(
                    'label'       => __('Author description font family', 'simple-author-box'),
                    'description' => __('Select the font family for the author\'s description', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => Simple_Author_Box_Helper::get_bunny_fonts(),
                    'default'     => 'None',
                ),
                'sab_box_name_size' => array(
                    'label'       => __('Author name font size', 'simple-author-box'),
                    'description' => __('Default font size for author name is 18px.', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 10,
                        'max'       => 50,
                        'increment' => 1,
                    ),
                    'default'     => '18',
                ),
                'sab_box_job_font_size' => array(
                    'label'       => __('Author job title font size', 'simple-author-box'),
                    'description' => __('Default font size for author job title is 12px.', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 10,
                        'max'       => 50,
                        'increment' => 1,
                    ),
                    'default'     => '12',
                    'profeature'  => true,
                ),
                'sab_box_web_size'  => array(
                    'label'       => __('Author website font size', 'simple-author-box'),
                    'description' => __('Default font size for author website is 14px.', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 10,
                        'max'       => 50,
                        'increment' => 1,
                    ),
                    'default'     => '14',
                    'condition'   => 'sab_web',
                ),
                'sab_box_desc_size' => array(
                    'label'       => __('Author description font size', 'simple-author-box'),
                    'description' => __('Default font size for author description is 14px.', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 10,
                        'max'       => 50,
                        'increment' => 1,
                    ),
                    'default'     => '14',
                ),
                'sab_box_icon_size' => array(
                    'label'       => __('Size of social icons', 'simple-author-box'),
                    'description' => __('Default font size for social icons is 18px.', 'simple-author-box'),
                    'type'        => 'slider',
                    'choices'     => array(
                        'min'       => 10,
                        'max'       => 50,
                        'increment' => 1,
                    ),
                    'default'     => '18',
                ),
                'sab_desc_style'    => array(
                    'label'       => __('Author description font style', 'simple-author-box'),
                    'description' => __('Select the font style for the author\'s description', 'simple-author-box'),
                    'type'        => 'select',
                    'choices'     => array(
                        0 => __('Normal', 'simple-author-box'),
                        1 => __('Italic', 'simple-author-box'),
                    ),
                    'default'     => '0',
                    'group'       => 'saboxplugin_options',
                ),
            ),
            'author-tabs' => array(
                'sab_tab_about_title' => array(
                    'label'       => __('Title of the about tab', 'simple-author-box'),
                    'description' => __('Title displayed on the about tab button', 'simple-author-box'),
                    'default'     => 'About the Author',
                    'type'        => 'text',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_show_latest_posts' => array(
                    'label'       => __('Show tab with latest posts by this author', 'simple-author-box'),
                    'description' => __('When enabled, an extra tab will be displayed in the author box with recent articles published by this author on the website', 'simple-author-box'),
                    'type'        => 'toggle',
                    'default'     => 0,
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_latest_title' => array(
                    'label'       => __('Title of the latest posts tab', 'simple-author-box'),
                    'description' => __('Title displayed on the latest posts tab button', 'simple-author-box'),
                    'default'     => 'Latest Posts',
                    'type'        => 'text',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'sab_show_latest_posts',
                    'profeature'  => true,
                ),
                'sab_show_custom_html' => array(
                    'label'       => __('Show tab with Custom HTML', 'simple-author-box'),
                    'description' => __('When enabled, an extra tab will be displayed in the author box with custom HTML', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_custom_html_title' => array(
                    'label'       => __('Title of the Custom HTML tab', 'simple-author-box'),
                    'description' => __('Title displayed on the Custom HTML tab button', 'simple-author-box'),
                    'default'     => 'More info',
                    'type'        => 'text',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'sab_show_custom_html',
                    'profeature'  => true,
                ),
                'sab_tab_custom_html_body' => array(
                    'label'       => __('Content of the Custom HTML Tab', 'simple-author-box'),
                    'description' => __('This is the content of the Custom HTML tab. You can use the %%author_html%% tag to print the content from the Simple Author Box Custom HTML box that appears on the Author\'s profile page', 'simple-author-box'),
                    'default'     => '%%author_html%%',
                    'type'        => 'textarea',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'sab_show_custom_html',
                    'profeature'  => true,
                ),
                'sab_tab_background_color' => array(
                    'label'       => __('Tab background color', 'simple-author-box'),
                    'description' => __('Choose which background color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#e4e4e4',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_text_color' => array(
                    'label'       => __('Tab text color', 'simple-author-box'),
                    'description' => __('Choose which text color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#222222',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_border_color' => array(
                    'label'       => __('Tab border color', 'simple-author-box'),
                    'description' => __('Choose which bottom border color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#c7c7c7',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_hover_background_color' => array(
                    'label'       => __('Tab hover background color', 'simple-author-box'),
                    'description' => __('Choose which hover background color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#efefef',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_hover_text_color' => array(
                    'label'       => __('Tab hover text color', 'simple-author-box'),
                    'description' => __('Choose which hover text color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#222222',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
                'sab_tab_hover_border_color' => array(
                    'label'       => __('Tab hover border color', 'simple-author-box'),
                    'description' => __('Choose which hover bottom border color the author box tabs should have', 'simple-author-box'),
                    'type'        => 'color',
                    'default'     => '#c7c7c7',
                    'group'       => 'saboxplugin_options',
                    'profeature'  => true,
                ),
            ),
            // todo: temporarily removed
            /*
            'themes' => array(
                'themes' => array('type' => 'themes')
            ),
            */
            'guest-author-options' => array(
                'enable_guest_authors'   => array(
                    'label'       => __('Enable Guest Authors', 'simple-author-box'),
                    'description' => __('When turned ON, you will be able to select guest authors for each post.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                     'profeature'  => true,
                ),
                'co_authors'             => array(
                    'label'       => __('Use Guest Authors as co-authors', 'simple-author-box'),
                    'description' => __('If you check this the guest authors will be transformed in co authors and they will be shown next under author.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'enable_guest_authors',
                    'profeature'  => true,
                ),
                'co_authors_custom'      => array(
                    'label'       => __('Enable custom "Co-Authors text?"', 'simple-author-box'),
                    'description' => __('If you check this the guest authors will be transformed in co authors and they will be shown next under author.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'enable_guest_authors',
                    'profeature'  => true,
                ),
                'co_authors_custom_text' => array(
                    'label'       => __('Co-Authors custom text', 'simple-author-box'),
                    'description' => __('If you check this the guest authors will be transformed in co authors and they will be shown next under author.', 'simple-author-box'),
                    'type'        => 'textarea',
                    'group'       => 'saboxplugin_options',
                    'condition'   => 'co_authors_custom',
                    'default'     => '',
                    'profeature'  => true,
                ),
            ),
            'advanced' => array(
                'advanced' => array('type' => 'advanced'),
                'sab_footer_inline_style' => array(
                    'label'       => __('Load generated inline style to footer', 'simple-author-box'),
                    'description' => __('This option is useful ONLY if you run a plugin that optimizes your CSS delivery or moves your stylesheets to the footer, to get a higher score on speed testing services. However, the plugin style is loaded only on single post and single page.', 'simple-author-box'),
                    'type'        => 'toggle',
                    'group'       => 'saboxplugin_options',
                ),
                'sab_custom_css' => array(
                    'label'       => __('Custom CSS', 'simple-author-box'),
                    'description' => __('If you want to change the appearance of your author box beyond provided options, add custom CSS here.', 'simple-author-box'),
                    'default'     => '',
                    'type'        => 'textarea',
                    'group'       => 'saboxplugin_options',
                )
            ),

        );


        $this->settings = apply_filters('sabox_admin_settings', $settings);
        $this->sections = apply_filters('sabox_admin_sections', $default_sections);

        $this->get_all_options();

        add_action('admin_menu', array($this, 'menu_page'));
        add_action('admin_init', array($this, 'save_settings'));

    }

    private function get_all_options()
    {

        $this->options = Simple_Author_Box_Helper::get_option('saboxplugin_options');

        $sab_box_margin_top = Simple_Author_Box_Helper::get_option('sab_box_margin_top');
        if ($sab_box_margin_top) {
            $this->options['sab_box_margin_top'] = $sab_box_margin_top;
        }

        $sab_box_margin_bottom = Simple_Author_Box_Helper::get_option('sab_box_margin_bottom');
        if ($sab_box_margin_bottom) {
            $this->options['sab_box_margin_bottom'] = $sab_box_margin_bottom;
        }

        $sab_box_icon_size = Simple_Author_Box_Helper::get_option('sab_box_icon_size');
        if ($sab_box_icon_size) {
            $this->options['sab_box_icon_size'] = $sab_box_icon_size;
        }

        $sab_box_author_font_size = Simple_Author_Box_Helper::get_option('sab_box_name_size');
        if ($sab_box_author_font_size) {
            $this->options['sab_box_name_size'] = $sab_box_author_font_size;
        }

        $sab_box_web_size = Simple_Author_Box_Helper::get_option('sab_box_web_size');
        if ($sab_box_web_size) {
            $this->options['sab_box_web_size'] = $sab_box_web_size;
        }

        $sab_box_name_font = Simple_Author_Box_Helper::get_option('sab_box_name_font');
        if ($sab_box_name_font) {
            $this->options['sab_box_name_font'] = $sab_box_name_font;
        }

        $sab_box_subset = Simple_Author_Box_Helper::get_option('sab_box_subset');
        if ($sab_box_subset) {
            $this->options['sab_box_subset'] = $sab_box_subset;
        }

        $sab_box_desc_font = Simple_Author_Box_Helper::get_option('sab_box_desc_font');
        if ($sab_box_desc_font) {
            $this->options['sab_box_desc_font'] = $sab_box_desc_font;
        }

        $sab_box_web_font = Simple_Author_Box_Helper::get_option('sab_box_web_font');
        if ($sab_box_web_font) {
            $this->options['sab_box_web_font'] = $sab_box_web_font;
        }

        $sab_box_desc_size = Simple_Author_Box_Helper::get_option('sab_box_desc_size');
        if ($sab_box_desc_size) {
            $this->options['sab_box_desc_size'] = $sab_box_desc_size;
        }

        $this->options['sab_box_padding_top_bottom'] = Simple_Author_Box_Helper::get_option('sab_box_padding_top_bottom');
        $this->options['sab_box_padding_left_right'] = Simple_Author_Box_Helper::get_option('sab_box_padding_left_right');
    }

    public function menu_page()
    {
            add_theme_page(__('Simple Author Box', 'simple-author-box'), __('Simple Author Box', 'simple-author-box'), 'manage_options', 'simple-author-box', array(
                $this,
                'setting_page',
            ));
    }

    public function setting_page()
    {
        ?>
        <div class="masthead">
            <div class="wrap sabox-wrap">
                <div class="sabox-masthead-left">
                    <div class="wp-heading-inline">
                        <?php
                        echo '<img src="' . esc_url(SIMPLE_AUTHOR_BOX_ASSETS) . 'img/simple-author-box-logo.png' . '" title="' . esc_html__('Simple Author Box', 'simple-author-box') . '" class="sab-logo">';
                        ?>
                    </div>
                </div>
                <div class="wp-clearfix"></div>
            </div>
        </div>

        <!--/.masthead-->

        <div class="sabox-content">
            <?php

            if (get_transient('sab_error_msg')) {
                wpsabox_wp_kses_wf(get_transient('sab_error_msg'));
            }
            ?>
            <div class="sabox-left">

                <div class="open-upsell" data-feature="sidebar-box" id="sidebar-box-ad"><a href="#" class="open-upsell" data-feature="sidebar-box">
                      <span class="dashicons dashicons-star-filled"></span><br>Get <b>PRO</b> for only $9.99</a>
                    </div>
                <div class="epfw-tab-wrapper nav-tab-wrapper wp-clearfix">
                        <?php foreach ($this->sections as $id => $section) { ?>
                            <?php
                            $class = 'epfw-tab nav-tab';

                            if (isset($section['link'])) {
                                $url   = $section['link'];
                                $class .= ' epfw-tab-link';
                            } else {
                                $url = '#' . $id;
                            }

                            if (isset($section['class'])) {
                                $class .= ' ' . $section['class'];
                            }

                            ?>
                            <a class="<?php echo esc_attr($class); ?>" href="<?php echo esc_url($url); ?>"><?php echo wp_kses_post($section['label']); ?></a>
                        <?php } ?>

                        <a class="epfw-tab nav-tab not-tab" target="_blank" href="https://wordpress.org/support/plugin/simple-author-box/">Support</a>
                        <a class="epfw-tab nav-tab not-tab open-upsell" data-feature="tab" href="#">Get PRO <span class="dashicons dashicons-star-filled"></span></a>
                    </div>
            </div><!-- sabox-left -->

            <div class="sabox-right">

                <div class="sabox-wrap">
                        <div class="sabox-preview">
                            <?php do_action('sab_admin_preview') ?>
                        </div>
                    <?php do_action('sab_admin_old_premium') ?>


                    <form method="post" id="sabox-container" enctype="multipart/form-data">
                        <?php
                        wp_nonce_field('sabox-plugin-settings', 'sabox_plugin_settings_page');

                        foreach ($this->settings as $tab_name => $fields) {
                            if(!array_key_exists($tab_name, $this->sections)){
                                continue;
                            }

                            echo '<div class="epfw-turn-into-tab" id="' . esc_attr($tab_name) . '-tab">';
                            echo '<h3 class="sab-tab-title">';
                            echo esc_html($this->sections[$tab_name]['label']);
                            if($tab_name == 'themes'){
                                esc_html($this->generate_pro_label('themes'));
                            }
                            echo '</h3>';

                            if($tab_name == 'typography-options' || $tab_name == 'appearance-options' || $tab_name == 'visibility') {
                                echo '<div class="sab-tab-save">';
                                    submit_button(esc_html__('Save Settings', 'simple-author-box'), 'button button-primary button-hero', '', false);
                                echo '</div>';
                            }
                            echo '<table class="form-table sabox-table">';

                            foreach ($fields as $field_name => $field) {
                                $this->generate_setting_field($field_name, $field);
                            }

                            echo '</table>';
                            if($tab_name != 'license' && $tab_name != 'support'){
                                echo '<div class="textright">';
                                submit_button(esc_html__('Save Settings', 'simple-author-box'), 'button button-primary button-hero', '', false);
                                echo '</div>';
                            }
                            echo '</div>';
                        }



                        ?>
                    </form>

                </div>
            </div><!-- sabox-right -->
        </div><!-- sabox-content -->

    <?php
      wpsabox_wp_kses_wf($this->pro_dialog());
    }

    function pro_dialog()
    {
      $out = '';

      $out .= '<div id="sab-pro-dialog" style="display: none;" title="WP Simple Author Box PRO is here!"><span class="ui-helper-hidden-accessible"><input type="text"/></span>';


      $out .= '<div class="center logo"><a href="https://wpauthorbox.com/?ref=sab-free-pricing-table" target="_blank"><img src="' . SIMPLE_AUTHOR_BOX_ASSETS . 'img/simple-author-box-logo.png' . '" alt="WP Author Box PRO" title="WP Author Box PRO"></a><br>';

      $out .= '<span>Limited PRO Launch Discount - <b>LIFETIME prices</b>! Pay once &amp; use forever!</span>';
      $out .= '</div>';

      $out .= '<table id="sab-pro-table">';
      $out .= '<tr>';
      $out .= '<td class="center">Lifetime Personal License</td>';
      $out .= '<td class="center">Lifetime Team License</td>';
      $out .= '<td class="center">Lifetime Agency License</td>';
      $out .= '</tr>';

      $out .= '<tr class="prices">';
      $out .= '<td class="center"><del>$49 /year</del><br><span>$59</span> /lifetime</td>';
      $out .= '<td class="center"><del>$89 /year</del><br><span>$69</span> /lifetime</td>';
      $out .= '<td class="center"><del>$199 /year</del><br><span>$99</span> /lifetime</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span><b>1 Site License</b></td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span><b>3 Sites License</b></td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span><b>100 Sites License</b></td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>+50 Extra Customization Options</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>+50 Extra Customization Options</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>+50 Extra Customization Options</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Gutenberg Block &amp; Widgets</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Gutenberg Block &amp; Widgets</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Gutenberg Block &amp; Widgets</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Post Co-authors &amp; Guest Authors</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Post Co-authors &amp; Guest Authors</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Post Co-authors &amp; Guest Authors</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Color Themes</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Color Themes</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Color Themes</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-no"></span>Licenses &amp; Sites Manager</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Licenses &amp; Sites Manager</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Licenses &amp; Sites Manager</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-no"></span>White-label Mode</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>White-label Mode</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>White-label Mode</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>';
      $out .= '<td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>';
      $out .= '<td><span class="dashicons dashicons-yes"></span>Full Plugin Rebranding</td>';
      $out .= '</tr>';

      $out .= '<tr>';
      $out .= '<td><a class="button button-buy" data-href-org="https://wpauthorbox.com/buy/?product=personal-lifetime&ref=pricing-table" href="https://wpauthorbox.com/buy/?product=personal-lifetime&ref=pricing-table" target="_blank">Lifetime license<br>$59 - BUY NOW</a>
      </td>';
      $out .= '<td><a class="button button-buy" data-href-org="https://wpauthorbox.com/buy/?product=team-lifetime&ref=pricing-table" href="https://wpauthorbox.com/buy/?product=team-lifetime&ref=pricing-table" target="_blank">Lifetime license<br>$69 - BUY NOW</a></td>';
      $out .= '<td><a class="button button-buy" data-href-org="https://wpauthorbox.com/buy/?product=agency-launch&ref=pricing-table" href="https://wpauthorbox.com/buy/?product=agency-launch&ref=pricing-table" target="_blank">Lifetime license<br>$99 - BUY NOW</a></td>';
      $out .= '</tr>';

      $out .= '</table>';

      $out .= '<div class="center upsell-footer-2">Need the plugin only for a <b>short period of time</b>? <a class="button-buy" data-href-org="https://wpauthorbox.com/buy/?product=personal-monthly&ref=pricing-table" href="https://wpauthorbox.com/buy/?product=personal-monthly&ref=pricing-table" target="_blank"><b>Get it for ONLY $9.99</b><small> /month</small></a> &amp; cancel any time!</div>';

      $out .= '<div class="center footer"><b>100% No-Risk Money Back Guarantee!</b> If you don\'t like the plugin over the next 7 days, we will happily refund 100% of your money. No questions asked! Payments are processed by our merchant of records - <a href="https://paddle.com/" target="_blank">Paddle</a>.</div></div>';

      return $out;
    } // pro_dialog

    // validate import file after upload
    public function validate_import_file()
    {
        if (isset($_POST['sabox_plugin_settings_page']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sabox_plugin_settings_page'])), 'sabox-plugin-settings')) {

        }

        if (empty($_POST) || empty($_FILES['sab_settings_import']) || empty($_FILES['sab_settings_import']['tmp_name']) ) {
            return new WP_Error(1, 'No import file uploaded.');
        }

        $plugin_name = 'Simple Author Box';
        $uploaded_file = sanitize_file_name($_FILES['sab_settings_import']);

        if (mime_content_type($uploaded_file['tmp_name']) == 'text/plain' && substr($uploaded_file['name'], -4, 4) != '.txt') {
            return new WP_Error(1, 'Please upload a <i>TXT</i> file generated by ' . $plugin_name . ' plugin.');
        }

        if ($uploaded_file['size'] < 500) {
            return new WP_Error(1, 'Uploaded file is too small. Please verify that you have uploaded the right export file.');
        }

        if ($uploaded_file['size'] > 100000) {
            return new WP_Error(1, 'Uploaded file is too large to process. Please verify that you have uploaded the right export file.');
        }

        $content = file_get_contents($uploaded_file['tmp_name']);
        $content = json_decode($content, true);
        if (
            !isset($content['type']) || !isset($content['version']) || !isset($content['data']) ||
            $content['type'] != 'SAB' || !is_array($content['data']) || sizeof($content['data']) < 20
        ) {
            return new WP_Error(1, 'Uploaded file is not a ' . $plugin_name . ' export file. Please verify that you have uploaded the right file.');
        }

        return $content;
    } // validate_import_file

    public function save_settings()
    {
        if (isset($_POST['sabox_plugin_settings_page']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sabox_plugin_settings_page'])), 'sabox-plugin-settings') && isset($_POST['submit-import'])) { // import settings
            unset($_POST['submit-import']);

            $import_data = $this->validate_import_file();

            if (is_wp_error($import_data)) {
                set_transient('sab_error_msg', '<div class="sab-alert sab-alert-info"><strong>' . $import_data->get_error_message() . '</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
            } else {
                $options = $import_data['data'];
                update_option('saboxplugin_options', $options);
                set_transient('sab_error_msg', '<div class="sab-alert sab-alert-info"><strong>Settings have been imported.</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
                wp_safe_redirect(admin_url('/themes.php?page=simple-author-box'));
            }
        } else if (isset($_POST['sabox_plugin_settings_page']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sabox_plugin_settings_page'])), 'sabox-plugin-settings')) {
           if(isset($_POST['sabox-settings'])){
                $settings = wp_unslash($_POST['sabox-settings']); //phpcs:ignore
                array_walk_recursive($settings, 'sanitize_text_field');
            } else {
                $settings = array();
            }

            $groups   = array();


            foreach ($this->settings as $tab => $setting_fields) {
                foreach ($setting_fields as $key => $setting) {
                    if (isset($setting['group'])) {
                        if (!isset($groups[$setting['group']])) {
                            $groups[$setting['group']] = get_option($setting['group'], array());
                        }

                        if (!isset($settings[$setting['group']][$key]) && isset($groups[$setting['group']][$key])) {
                            $groups[$setting['group']][$key] = '0';
                        }

                        if (isset($settings[$setting['group']][$key])) {
                            $groups[$setting['group']][$key] = $this->sanitize_fields($setting, $settings[$setting['group']][$key]);
                        }

                        if(isset($setting['profeature'])){
                            unset($groups[$setting['group']][$key]);
                        }

                    } else {
                        if(isset($setting['profeature'])){
                            delete_option($key);
                        }

                        $current_value = get_option($key);
                        if (isset($settings[$key])) {
                            $value = $this->sanitize_fields($setting, $settings[$key]);
                            if ($current_value != $value) {
                               update_option($key, $value);
                            }
                        }
                    }
                }
            }

            foreach ($groups as $key => $values) {
                update_option($key, $values);
            }

            do_action('sabox_save_settings');
            set_transient('sab_error_msg', '<div class="sab-alert sab-alert-info sab-saved"><strong>Settings Saved</strong><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', 1);
            Simple_Author_Box_Helper::reset_options();
            $this->get_all_options();
        }
    }


    private function sanitize_fields($setting, $value)
    {
        $default_sanitizers = array(
            'toggle' => 'absint',
            'slider' => 'absint',
            'color'  => 'sanitize_hex_color',
        );

        if (isset($setting['sanitize']) && function_exists($setting['sanitize'])) {
            $value = call_user_func($setting['sanitize'], $value);
        } elseif (isset($default_sanitizers[$setting['type']]) && function_exists($default_sanitizers[$setting['type']])) {
            $value = call_user_func($default_sanitizers[$setting['type']], $value);
        } elseif ('select' == $setting['type']) {
            if (isset($setting['choices'][$value])) {
                $value = $value;
            } else {
                $value = $setting['default'];
            }
        } elseif ('multiplecheckbox' == $setting['type']) {
            foreach ($value as $key) {
                if (!isset($setting['choices'][$key])) {
                    unset($value[$key]);
                }
            }
        } else {
            $value = sanitize_text_field($value);
        }

        return $value;
    }

    function generate_pro_label($feature)
    {
      $out = '<a title="This feature is available in the PRO version. Click for details." href="#" data-feature="' . esc_attr($feature) .'" class="pro-label open-upsell">PRO</a>';

      return $out;
    }


    private function get_options()
    {
        if ($this->options == null) {
            if (class_exists('Simple_Author_Box_Helper')) {
                $this->options = Simple_Author_Box_Helper::get_option('saboxplugin_options');
            } else {
                $this->options = get_option('saboxplugin_options');
            }
        }

        return $this->options;
    }

    public function themes_tab()
    {
        $icons = array_slice(Simple_Author_Box_Helper::$social_icons, 0, 8);
        $html = '';

        $themes = Simple_Author_Box_Helper::get_themes();
        foreach ($themes as $tid => $theme) {
            $html .= '<div class="sab-theme-thumbnail open-upsell" data-theme="' . $tid . '" style="border-color:' . $theme['sab_box_border'] . '">';
            if ('1' == $this->options['sab_show_latest_posts'] || '1' == $this->options['sab_show_custom_html']) {
                $html .= '<div class="saboxplugin-tabs-wrapper">';
                $html .= '<ul>';
                $html .= '<li style="color:' . $theme['sab_tab_text_color'] . '; background-color:' . $theme['sab_tab_background_color'] . ';" data-tab="about">About the Author</li>';
                $html .= '<li style="color:' . $theme['sab_tab_text_color'] . '; background-color:' . $theme['sab_tab_background_color'] . ';' . ('1' == $this->options['sab_show_latest_posts'] ? '' : 'display:none') . '" data-tab="latest_posts">Latest posts</li>';
                $html .= '<li style="color:' . $theme['sab_tab_text_color'] . '; background-color:' . $theme['sab_tab_background_color'] . ';' . ('1' == $this->options['sab_show_custom_html'] ? '' : 'display:none') . '" data-tab="other" data-tab="latest_posts">Other</li>';
                $html .= '</div>';
            }
            $html .= '<div class="sab-theme-gravatar">' . get_avatar(false, '40', '', '', array('extra_attr' => 'itemprop="image"')) . '</div>';
            if ($tid == 'none') {
                $name = 'Default';
            } else {
                $name = ucfirst($tid);
            }
            $html .= '<div class="sab-theme-author" style="color:' . $theme['sab_box_author_color'] . '">' . $name . '</div>';
            $html .= '<div class="sab-theme-author-bio">Sed non elit aliquam, tempor nisl vitae.</div>';
            $html .= '<div class="sab-theme-social-bg" style="background-color:' . $theme['sab_box_icons_back'] . '">';
            foreach ($icons as $icon => $name) {
                $html .= '<div class="saboxplugin-icon" style="color: ' . $theme['sab_box_web_color'] . '; fill: ' . $theme['sab_box_web_color'] . ';">' . Simple_Author_Box_Social::icon_to_svg($icon) . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    public function advanced_tab()
    {
        global $simple_author_box_admin;

        $html = '';
        $html .= '<table class="form-table sabox-table"><tbody>';

        $html .= '<tr valign="top" class="">';
        $html .= '<th scope="row">Export Settings' . $simple_author_box_admin->generate_pro_label('export-settings');
        $html .= '<span class="epfw-description">All settings are exported except license details. You can safely transfer (export and then import) settings between different domains/sites.</span></th>';
        $html .= '<td align="right">';
        $html .= '<a class="button button-primary open-upsell" href="#">Export Settings</a>';
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr valign="top" class="">';
        $html .= '<th scope="row">Import Settings' . $simple_author_box_admin->generate_pro_label('import-settings');
        $html .= '<span class="epfw-description">All settings are imported and overwritten except license details.</span></th>';
        $html .= '<td align="right">';

        $html .= '<div class="open-upsell">';
        $html .= '<div class="file-upload">';
        $html .= '<label class="file-upload__label" for="sab_settings_import">Select File</label><input class="file-upload__input" type="file" name="sab_settings_import" id="sab_settings_import" accept=".txt"></div><input type="submit" name="submit-import" id="submit-import" class="button button-primary" data-confirm="Are you sure you want to import settings? All current settings will be overwritten. There is NO UNDO!" value="Import Settings"></td>';
        $html .= '</div>';
        $html .= '</tr>';

        $html .= '<tr valign="top" class="">';
        $html .= '<th scope="row">Reset All Settings' . $simple_author_box_admin->generate_pro_label('reset-settings');
        $html .= '<span class="epfw-description">All settings are reset to default values except license details. There is NO undo.</span></th>';
        $html .= '<td align="right">';
        $html .= '<a class="button button-primary open-upsell" href="#">Reset Settings</a>';
        $html .= '</td>';
        $html .= '</tr>';

        $html .= '</table>';

        return $html;
    }


    private function generate_setting_field($field_name, $field)
    {
        $class = '';
        $name  = 'sabox-settings[';
        if (isset($field['group'])) {
            $name .= $field['group'] . '][' . esc_attr($field_name) . ']';
        } else {
            $name .= esc_attr($field_name) . ']';
        }

        if ($field_name == 'themes') {
            wpsabox_wp_kses_wf('<td colspan="2">' . $this->themes_tab() . '</td>');
            return;
        }

        if ($field_name == 'advanced') {
            wpsabox_wp_kses_wf('<td colspan="2">' . $this->advanced_tab() . '</td>');
            return;
        }

        if (isset($field['condition'])) {
            $class = 'show_conditional show_if_' . $field['condition'] . ' hide';
        }
        echo '<tr valign="top" class="' . esc_attr($class) . '">';

        $field_types = ['toggle', 'select', 'hidden', 'text', 'textarea', 'readonly', 'slider', 'color', 'picker', 'image', 'multiplecheckbox', 'radio-group'];

        if(!in_array($field['type'], $field_types)){
            do_action("sabox_field_{$field['type']}_output", $field_name, $field);
            return;
        }

        if ($field['type'] == 'hidden') {
            $value = isset($this->options[$field_name]) ? $this->options[$field_name] : 'none';
            echo '<input type="hidden" class="sabox-text" id="' . esc_attr($field_name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" />';
            return;
        }
        echo '<th scope="row">';

        echo esc_html($field['label']);
        if (!empty($field['profeature'])) {
          echo '<a title="This feature is available in the PRO version. Click for details." href="#" data-feature="' . esc_attr($field_name) .'" class="open-upsell pro-label">PRO</a>';
        }
        if (isset($field['description'])) {
            echo '<span class="epfw-description">' . esc_html($field['description']) . '</span>';
        }
        echo '</th>';
        echo '<td>';

        $profeature = false;
        if(array_key_exists('profeature', $field) && $field['profeature'] == true){
            $profeature = true;
        }

        if($profeature){
            echo '<div class="open-upsell open-upsell-block" data-feature="' . esc_attr($field_name) . '">';
        }
        switch ($field['type']) {
            case 'toggle':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : '0';
                echo '<div class="checkbox_switch">';
                echo '<div class="onoffswitch">';
                echo '<input type="checkbox" id="' . esc_attr($field_name) . '" name="' . esc_attr($name) . '" class="onoffswitch-checkbox saboxfield" ' . checked(1, $value, false) . ' value="1">';
                echo '<label class="onoffswitch-label" for="' . esc_attr($field_name) . '"></label>';
                echo '</div>';
                echo '</div>';
                break;
            case 'select':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : $field['default'];

                    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($name) . '" class="saboxfield" ' . ($profeature?'disabled':'') . '>';

                foreach ($field['choices'] as $key => $choice) {
                    echo '<option value="' . esc_attr($key) . '" ' . selected($key, $value, false) . '>' . esc_html($choice) . '</option>';
                }

                if(array_key_exists('choices_pro', $field)){
                    foreach ($field['choices_pro'] as $key => $choice) {
                        echo '<option class="pro-option" value="' . esc_attr($key) . '" ' . selected($key, $value, false) . '>' . esc_html($choice) . '</option>';
                    }
                }
                echo '</select>';
                break;
            case 'text':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : $field['default'];
                echo '<input type="text" class="sabox-text" id="' . esc_attr($field_name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ' . ($profeature?'disabled':'') . ' />';
                break;
            case 'textarea':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : $field['default'];
                echo '<textarea rows="3" cols="50"  id="' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" name="' . esc_attr($name) . '" class="saboxfield sabox-text" ' . ($profeature?'disabled':'') . '>' . esc_textarea($value) . '</textarea>';
                break;
            case 'readonly':
                echo '<textarea clas="regular-text" rows="3" cols="50" onclick="this.focus();this.select();" readonly="readonly">' . esc_attr($field['value']) . '</textarea>';
                break;
            case 'slider':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : $field['default'];
                echo '<div class="sabox-slider-container slider-container">';
                echo '<input type="text" id="' . esc_attr($field_name) . '" class="saboxfield" name="' . esc_attr($name) . '" data-min="' . absint($field['choices']['min']) . '" data-max="' . absint($field['choices']['max']) . '" data-step="' . absint($field['choices']['increment']) . '" value="' . esc_attr($value) . 'px">';
                echo '<div class="sabox-slider"></div>';
                echo '</div>';
                break;
            case 'color':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : '';
                echo '<div class="sabox-colorpicker">';
                echo '<input id="' . esc_attr($field_name) . '" class="saboxfield sabox-color" ' . ($profeature?'disabled':'') . ' name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
                echo '</div>';
                break;
            case 'picker':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : '';
                echo '<div class="sab-element-picker-wrapper">';
                echo '<a href="#" data-element="' . esc_attr($field_name) . '" class="button-primary ' . ($profeature?'sab-element-picker-disabled':'sab-element-picker') . '">Pick Author Box Element </button></a>';
                echo '<input placeholder="Enter element CSS path or use picker" type="text" id="' . esc_attr($field_name) . '" class="saboxfield" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
                echo '</div>';
                break;
            case 'image':
                $value = isset($this->options[$field_name]) ? $this->options[$field_name] : '';
                echo '<div class="sab-element-image-wrapper">';
                if (!empty($value)) {
                    echo '<span class="sabox-preview-area" id="background-preview"><img src="' . esc_url($value) . '" />&nbsp;<a href="javascript: void(0);" class="sab-remove-image">Remove Image</a></span>';
                } else {
                    echo '<span class="sabox-preview-area" id="background-preview"><span class="sabox-preview-area-placeholder">Select an image from our 400,000+ images gallery, or upload your own</span></span>';
                }

                echo '<div class="sab-image-picker">';
                echo '<a href="#" data-element="' . esc_attr($field_name) . '" class="button-primary sab-image-upload">Open images gallery</button></a>';
                echo '<input type="text" id="' . esc_attr($field_name) . '" class="saboxfield sabox_upload_image_input" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" />';
                echo '</div>';
                echo '</div>';
                break;
            case 'multiplecheckbox':
                echo '<div class="sabox-multicheckbox">';
                if (!isset($field['choices']) && isset($field['handle']) && is_array($field['handle'])) {
                    if (class_exists($field['handle'][0])) {
                        $class            = $field['handle'][0];
                        $method           = $field['handle'][1];
                        $field['choices'] = $class::$method();
                    }
                }

                if (!isset($field['default'])) {
                    $field['default'] = array_keys($field['choices']);
                }

                $values = isset($this->options[$field_name]) ? $this->options[$field_name] : $field['default'];

                if (is_array($values)) {
                    $checked = $values;
                } else {
                    $checked = array();
                }

                foreach ($field['choices'] as $key => $choice) {
                    echo '<div>';
                    echo '<input id="' . esc_attr($key) . '-' . esc_attr($field_name) . '" type="checkbox" value="' . esc_attr($key) . '" ' . esc_html(checked(1, in_array($key, $checked), false)) . ' name="' . esc_attr($name) . '[]">';
                    echo '<label for="' . esc_attr($key) . '-' . esc_attr($field_name) . '" class="checkbox-label">' . esc_attr($choice) . '</label>';
                    echo '</div>';
                }
                echo '</div>';
                break;
            case 'radio-group':
                echo '<div class="sabox-radio-group">';
                echo '<fieldset>';
                foreach ($field['choices'] as $key => $choice) {
                    echo '<input type="radio" id="' . esc_attr($field_name . '_' . $key) . '" name="' . esc_attr($name) . '" class="saboxfield" ' . checked($key, $this->options[$field_name], false) . ' value="' . esc_attr($key) . '">';
                    echo '<label for="' . esc_attr($field_name . '_' . $key) . '">' . esc_attr($choice) . '</label>';
                }
                echo '</fieldset>';
                echo '</div>';
                break;
            default:
                do_action("sabox_field_{$field['type']}_output", $field_name, $field);
                break;
        }
        if($profeature){
            echo '</div>';
        }

        echo '</td>';
        echo '</tr>';
    }
}
