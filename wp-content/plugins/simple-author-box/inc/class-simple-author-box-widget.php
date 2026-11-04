<?php

class Simple_Author_Box_Widget_LITE extends WP_Widget {

    var $defaults;

    function __construct() {

        $widget_ops  = array(
            'classname' => 'simple_author_box_widget_lite',
            'description' => esc_html__('Use this widget to display Simple Author Box', 'simple-author-box')
        );

        $control_ops = array( 'id_base' => 'simple_author_box_widget_lite' );
        parent::__construct( 'simple_author_box_widget_lite', esc_html__('Simple Author Box', 'simple-author-box'), $widget_ops, $control_ops );

        $this->defaults = array(
            'title'   => esc_html__('About Author', 'simple-author-box'),
            'authors' => 'auto',
        );

    }


    function widget( $args, $instance ) {

        $instance      = wp_parse_args((array)$instance, $this->defaults);
        $sabox_options = Simple_Author_Box_Helper::get_option('saboxplugin_options');
        $template      = Simple_Author_Box_Helper::get_template();

        if ('auto' != $instance['authors']) {
            $sabox_author_id = $instance['authors'];
        } else {
            global $post;
            if(!empty($post)){
                $sabox_author_id = $post->post_author;
            } else {
                $sabox_author_id = 0;
            }
        }

        echo '<style type="text/css">';
        wpsabox_wp_kses_wf(Simple_Author_Box_Helper::generate_inline_css());
        echo '</style>';

        wpsabox_wp_kses_wf($args['before_widget']);
        if ( '' != $instance['title'] ) {
            wpsabox_wp_kses_wf($args['before_title'] . esc_html($instance['title']) . $args['after_title']);
        }
        include($template);
        wpsabox_wp_kses_wf($args['after_widget']);

    }

    function update( $new_instance, $old_instance ) {
        $instance            = $old_instance;
        $instance['title']   = sanitize_text_field( $new_instance['title'] );
        $instance['authors'] = sanitize_text_field( $new_instance['authors'] );

        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args((array)$instance, $this->defaults);

        $result = count_users();

        $authors = array();

        foreach ( $result['avail_roles'] as $role => $count )
        {
            if ( $count > 0 ) {

                $args = array(
                    'role' => $role
                );

                $users_result = get_users( $args );
                $authors = array_merge($authors, $users_result);
            }
        }

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'simple-author-box'); ?>:</label>
            <input id="<?php echo esc_attr($this->get_field_id('title')); ?>" type="text"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>"
                   class="widefat"/>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('authors')); ?>"><?php esc_html_e('Choose author/user', 'simple-author-box'); ?>
                :</label>
            <select name="<?php echo esc_attr($this->get_field_name('authors')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('authors')); ?>" class="widefat">
                <option value="auto" ><?php esc_html_e('Autodetect', 'simple-author-box'); ?></option>
                <?php foreach ($authors as $author) : ?>
                    <option value="<?php echo absint($author->ID); ?>" <?php selected($author->ID, $instance['authors']); ?>><?php echo  esc_html($author->data->display_name) . ' ('. esc_html($author->data->user_login) .')'; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
        do_action('sab_widget_add_opts', $this, $instance);
    }

}
