<?php

/*----------------------------------------------------------------------------------------------------------
	Uninstall Simple Author Box plugin - deletes plugin data in database
-----------------------------------------------------------------------------------------------------------*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

$sabox_options = array(
    'sab_autoinsert'         => '0',
    'sab_show_latest_posts'  => '0',
    'sab_show_custom_html'   => '0',
    'sab_no_description'     => '0',
    'sab_email'              => '0',
    'sab_link_target'        => '0',
    'sab_hide_socials'       => '0',
    'sab_hide_on_archive'    => '0',
    'sab_box_border_width'   => '1',
    'sab_avatar_style'       => '0',
    'sab_avatar_size'        => '100',
    'sab_avatar_hover'       => '0',
    'sab_web'                => '0',
    'sab_web_target'         => '0',
    'sab_web_rel'            => '0',
    'sab_web_position'       => '0',
    'sab_colored'            => '0',
    'sab_icons_style'        => '0',
    'sab_social_hover'       => '0',
    'sab_box_long_shadow'    => '0',
    'sab_box_thin_border'    => '0',
    'sab_box_author_color'   => '',
    'sab_box_web_color'      => '',
    'sab_box_border'         => '',
    'sab_box_icons_back'     => '',
    'sab_box_author_back'    => '',
    'sab_box_author_p_color' => '',
    'sab_box_author_a_color' => '',
    'sab_box_icons_color'    => '',
    'sab_footer_inline_style' => '',
    'sab_box_margin_top'         => '0',
    'sab_box_margin_bottom'      => '0',
    'sab_box_padding_top_bottom' => '0',
    'sab_box_padding_left_right' => '0',
    'sab_box_subset'             => 'none',
    'sab_box_name_font'          => 'None',
    'sab_box_web_font'           => 'None',
    'sab_box_desc_font'          => 'None',
    'sab_box_name_size'          => '18',
    'sab_box_web_size'           => '14',
    'sab_box_desc_size'          => '14',
    'sab_box_icon_size'          => '18',
    'sab_desc_style'             => '0',
    'sab_whitelabel' => 0,
    'sab_box_job_font' => '',
    'sab_box_job_font_size' => '',
    'saboxplugin_options' => '',
    'sab_pointers' => ''
);

foreach($sabox_options as $sabox_option => $value){
    delete_option($sabox_option);
}
