<?php

// If this file is called directly, busted!
if (!defined('ABSPATH')) {
    exit;
}

/*----------------------------------------------------------------------------------------------------------
	Adding the author box to the end of your single post
-----------------------------------------------------------------------------------------------------------*/
if (!function_exists('wpsabox_author_box')) {


    function wpsabox_author_box($saboxmeta = null, $user_id = null)
    {
        global $post;
        $sabox_options        = Simple_Author_Box_Helper::get_option('saboxplugin_options');

        $show = (is_single() && isset($post->post_type) && $post->post_type == 'post') || is_author() || (is_archive() && 1 != $sabox_options['sab_hide_on_archive']);

        /**
         * Hook: sabox_check_if_show.
         *
         * @hooked Simple_Author_Box::check_if_show_archive - 10
         */

        if (is_archive()) {
            $show = apply_filters('sabox_check_if_show', $show);
        }

        if ($show) {

            global $post;

            $template = Simple_Author_Box_Helper::get_template();

            ob_start();
            $sabox_options        = Simple_Author_Box_Helper::get_option('saboxplugin_options');
            $sabox_author_id      = $user_id ? $user_id : $post->post_author;
            $show_post_author_box = apply_filters('sabox_check_if_show_post_author_box', true, $sabox_options);

            do_action('sabox_before_author_box', $sabox_options);

            if ($show_post_author_box) {
                include($template);
            }

            do_action('sabox_after_author_box', $sabox_options);

            $sabox  = ob_get_clean();
            $return = $saboxmeta . $sabox;

            // Filter returning HTML of the Author Box
            $saboxmeta = apply_filters('sabox_return_html', $return, $sabox, $saboxmeta);
        }

        return $saboxmeta;
    }
}

function wpsabox_wp_kses_wf($html)
{
    add_filter('safe_style_css', function ($styles) {
        $styles_wf = array(
            'text-align',
            'margin',
            'color',
            'float',
            'border',
            'background',
            'background-color',
            'border-bottom',
            'border-bottom-color',
            'border-bottom-style',
            'border-bottom-width',
            'border-collapse',
            'border-color',
            'border-left',
            'border-left-color',
            'border-left-style',
            'border-left-width',
            'border-right',
            'border-right-color',
            'border-right-style',
            'border-right-width',
            'border-spacing',
            'border-style',
            'border-top',
            'border-top-color',
            'border-top-style',
            'border-top-width',
            'border-width',
            'caption-side',
            'clear',
            'cursor',
            'direction',
            'font',
            'font-family',
            'font-size',
            'font-style',
            'font-variant',
            'font-weight',
            'height',
            'letter-spacing',
            'line-height',
            'margin-bottom',
            'margin-left',
            'margin-right',
            'margin-top',
            'overflow',
            'padding',
            'padding-bottom',
            'padding-left',
            'padding-right',
            'padding-top',
            'text-decoration',
            'text-indent',
            'vertical-align',
            'width',
            'display',
        );

        foreach ($styles_wf as $style_wf) {
            $styles[] = $style_wf;
        }
        return $styles;
    });

    $allowed_tags = wp_kses_allowed_html('post');
    $allowed_tags['input'] = array(
        'type' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'checked' => true,
        'disabled' => true,
        'name' => true,
        'size' => true,
        'placeholder' => true,
        'value' => true,
        'data-*' => true,
        'size' => true,
        'disabled' => true
    );

    $allowed_tags['textarea'] = array(
        'type' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'checked' => true,
        'disabled' => true,
        'name' => true,
        'size' => true,
        'placeholder' => true,
        'value' => true,
        'data-*' => true,
        'cols' => true,
        'rows' => true,
        'disabled' => true,
        'autocomplete' => true
    );

    $allowed_tags['select'] = array(
        'type' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'checked' => true,
        'disabled' => true,
        'name' => true,
        'size' => true,
        'placeholder' => true,
        'value' => true,
        'data-*' => true,
        'multiple' => true,
        'disabled' => true
    );

    $allowed_tags['option'] = array(
        'type' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'checked' => true,
        'disabled' => true,
        'name' => true,
        'size' => true,
        'placeholder' => true,
        'value' => true,
        'selected' => true,
        'data-*' => true
    );
    $allowed_tags['optgroup'] = array(
        'type' => true,
        'style' => true,
        'class' => true,
        'id' => true,
        'checked' => true,
        'disabled' => true,
        'name' => true,
        'size' => true,
        'placeholder' => true,
        'value' => true,
        'selected' => true,
        'data-*' => true,
        'label' => true
    );

    $allowed_tags['a'] = array(
        'href' => true,
        'data-*' => true,
        'class' => true,
        'style' => true,
        'id' => true,
        'target' => true,
        'data-*' => true,
        'role' => true,
        'aria-controls' => true,
        'aria-selected' => true,
        'disabled' => true,
        'rel' => true,
        'title' => true
    );

    $allowed_tags['div'] = array(
        'style' => true,
        'class' => true,
        'id' => true,
        'data-*' => true,
        'role' => true,
        'aria-labelledby' => true,
        'value' => true,
        'aria-modal' => true,
        'tabindex' => true
    );

    $allowed_tags['li'] = array(
        'style' => true,
        'class' => true,
        'id' => true,
        'data-*' => true,
        'role' => true,
        'aria-labelledby' => true,
        'value' => true,
        'aria-modal' => true,
        'tabindex' => true
    );

    $allowed_tags['span'] = array(
        'style' => true,
        'class' => true,
        'id' => true,
        'data-*' => true,
        'aria-hidden' => true
    );

    $allowed_tags['style'] = array(
        'class' => true,
        'id' => true,
        'type' => true
    );

    $allowed_tags['fieldset'] = array(
        'class' => true,
        'id' => true,
        'type' => true
    );

    $allowed_tags['link'] = array(
        'class' => true,
        'id' => true,
        'type' => true,
        'rel' => true,
        'href' => true,
        'media' => true
    );

    $allowed_tags['form'] = array(
        'style' => true,
        'class' => true,
        'id' => true,
        'method' => true,
        'action' => true,
        'data-*' => true
    );

    $allowed_tags['script'] = array(
        'class' => true,
        'id' => true,
        'type' => true,
        'src' => true
    );

    $allowed_tags['path'] = array(
        'class' => true,
        'id' => true,
        'fill' => true,
        'd' => true
    );

    $allowed_tags['svg'] = array(
        'class' => true,
        'id' => true,
        'aria-hidden' => true,
        'role' => true,
        'xmlns' => true,
        'viewBox' => true,
        'viewbox' => true,
        'width' => true,
        'height' => true,
        'focusable' => true,
        'style' => true,
        'xml:space' => true
    );

    $allowed_tags['defs'] = array(
        'class' => true,
        'id' => true,
    );

    $allowed_tags['filter'] = array(
        'class' => true,
        'id' => true,
    );

    $allowed_tags['rect'] = array(
        'class' => true,
        'x' => true,
        'y' => true,
        'width' => true,
        'height' => true,
        'fill' => true
    );

    $allowed_tags['polygon'] = array(
        'class' => true,
        'points' => true,
        'width' => true,
        'height' => true,
        'fill' => true
    );

    echo wp_kses($html, $allowed_tags);

    add_filter('safe_style_css', function ($styles) {
        $styles_wf = array(
            'text-align',
            'margin',
            'color',
            'float',
            'border',
            'background',
            'background-color',
            'border-bottom',
            'border-bottom-color',
            'border-bottom-style',
            'border-bottom-width',
            'border-collapse',
            'border-color',
            'border-left',
            'border-left-color',
            'border-left-style',
            'border-left-width',
            'border-right',
            'border-right-color',
            'border-right-style',
            'border-right-width',
            'border-spacing',
            'border-style',
            'border-top',
            'border-top-color',
            'border-top-style',
            'border-top-width',
            'border-width',
            'caption-side',
            'clear',
            'cursor',
            'direction',
            'font',
            'font-family',
            'font-size',
            'font-style',
            'font-variant',
            'font-weight',
            'height',
            'letter-spacing',
            'line-height',
            'margin-bottom',
            'margin-left',
            'margin-right',
            'margin-top',
            'overflow',
            'padding',
            'padding-bottom',
            'padding-left',
            'padding-right',
            'padding-top',
            'text-decoration',
            'text-indent',
            'vertical-align',
            'width'
        );

        foreach ($styles_wf as $style_wf) {
            if (($key = array_search($style_wf, $styles)) !== false) {
                unset($styles[$key]);
            }
        }
        return $styles;
    });
}

//return notice if user hasn't filled Biographical Info
function sab_user_description_notice()
{
    $user_id         = get_current_user_id();
    $user            = get_userdata($user_id);
    $user_descrition = $user->description;
    $user_roles      = $user->roles;
    if (!$user_descrition && in_array('author', $user_roles)) {

?>
        <div class="notice notice-info is-dismissible">
            <p><?php esc_html_e('Please complete Biographical Info', 'simple-author-box'); ?></p>
        </div>
    <?php
    }
}

add_action('admin_notices', 'sab_user_description_notice');


//return notice if user hasn't filled any social profiles
function sab_user_social_notice()
{
    $user_id     = get_current_user_id();
    $user_social = get_user_meta($user_id, 'sabox_social_links');
    $user        = get_userdata($user_id);
    $user_roles  = $user->roles;

    if (!$user_social && in_array('author', $user_roles)) {

    ?>
        <div class="notice notice-info is-dismissible">
            <p><?php esc_html_e('Please enter a social profile', 'simple-author-box'); ?></p>
        </div>
<?php
    }
}

add_action('admin_notices', 'sab_user_social_notice');
