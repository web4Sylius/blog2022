<?php

class Simple_Author_Box_User_Profile {

    function __construct() {


        add_action('init', array($this, 'init'));
    }

    public function init(){
      // Social Links
      add_action('show_user_profile', array($this, 'add_social_area'));
      add_action('edit_user_profile', array($this, 'add_social_area'));

      // Custom Profile Image
      add_action('show_user_profile', array($this, 'add_profile_image'), 9, 1);
      add_action('edit_user_profile', array($this, 'add_profile_image'), 9, 1);

      add_action('personal_options_update', array($this, 'save_user_profile'));
      add_action('edit_user_profile_update', array($this, 'save_user_profile'));

      // Allow HTML in user description.
      remove_filter('pre_user_description', 'wp_filter_kses');
      add_filter('pre_user_description', 'wp_kses_post');
    }


    public function add_social_area($profileuser) {
        $user_id = $profileuser->data->ID;

        $social_links = Simple_Author_Box_Helper::get_user_social_links($user_id);
        $social_icons = apply_filters('sabox_social_icons', Simple_Author_Box_Helper::$social_icons);

	    unset($social_icons['user_email']);

        ?>
        <div class="sab-user-profile-wrapper">
            <h2><?php esc_html_e('Social Media Links (Simple Author Box)', 'simple-author-box'); ?></h2>
            <table class="form-table" id="sabox-social-table">
                <?php
                if (!empty($social_links)) {
                    foreach ($social_links as $social_platform => $social_link) {
                        ?>
                        <tr>
                            <th>
                                <span class="sabox-drag"></span>
                                <select name="sabox-social-icons[]">
                                    <?php foreach ($social_icons as $sabox_social_id => $sabox_social_name) { ?>
                                        <option value="<?php echo esc_attr($sabox_social_id); ?>" <?php selected($sabox_social_id, $social_platform); ?>><?php echo esc_html($sabox_social_name); ?></option>
                                    <?php } ?>
                                </select>
                            </th>
                            <td>
                                <input name="sabox-social-links[]"
                                       type="<?php echo ('whatsapp' == $social_platform || 'phone' == $social_platform) ? 'tel' : 'text'; ?>"
                                       class="regular-text"
                                       value="<?php 
                                       echo esc_attr($social_link);
                                       ?>">
                                <span class="dashicons dashicons-trash"></span>
                            <td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <th>
                            <span class="sabox-drag"></span>
                            <select name="sabox-social-icons[]">
                                <?php foreach ($social_icons as $sabox_social_id => $sabox_social_name) { ?>
                                    <option value="<?php echo esc_attr($sabox_social_id); ?>"><?php echo esc_html($sabox_social_name); ?></option>
                                <?php } ?>
                            </select>
                        </th>
                        <td>
                            <input name="sabox-social-links[]" type="text" class="regular-text" value="">
                            <span class="dashicons dashicons-trash"></span>
                        <td>
                    </tr>
                    <?php
                }

                ?>

            </table>

            <div class="sabox-add-social-link">
                <a href="#"
                   class="button button-primary button-hero"></span><?php esc_html_e('+ Add new social platform', 'simple-author-box'); ?></a>
            </div>
        </div>

        <?php
    }

    public function add_profile_image($user) {

        if (!current_user_can('upload_files')) {
            return;
        }

        $default_url = SIMPLE_AUTHOR_BOX_ASSETS . 'img/default.png';
        $image       = get_user_meta($user->ID, 'sabox-profile-image', true);

        ?>

        <div id="sabox-custom-profile-image">
            <h3><?php esc_html_e('Custom User Profile Image (Simple Author Box)', 'simple-author-box'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="cupp_meta"><?php esc_html_e('Profile Image', 'simple-author-box'); ?></label></th>
                    <td>
                        <div id="sab-current-image">
                            <?php wp_nonce_field('sabox-profile-nonce', 'sabox-profile-nonce'); ?>
                            <img data-default="<?php echo esc_url($default_url); ?>"
                                 src="<?php echo '' != $image ? esc_url($image) : esc_url($default_url); ?>"><br>
                            <input type="text" name="sabox-custom-image" id="sabox-custom-image" class="regular-text"
                                   value="<?php echo esc_attr($image); ?>">
                        </div>
                        <div class="actions">
                            <a href="#" class="button-secondary"
                               id="sabox-remove-image"><?php esc_html_e('Remove Image', 'simple-author-box'); ?></a>
                            <a href="#" class="button-primary"
                               id="sabox-add-image"><?php esc_html_e('Upload Image', 'simple-author-box'); ?></a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <?php
    }

    public function save_user_profile($user_id) {
        if (!isset($_POST['sabox-profile-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sabox-profile-nonce'])), 'sabox-profile-nonce')) {
            return;
        }

        if (isset($_POST['sabox-social-icons']) && isset($_POST['sabox-social-links'])) {
            $sabox_social_links = array_map('sanitize_text_field', wp_unslash($_POST['sabox-social-links']));
            $sabox_social_icons = array_map('sanitize_text_field', wp_unslash($_POST['sabox-social-icons']));
			$social_platforms = apply_filters( 'sabox_social_icons', Simple_Author_Box_Helper::$social_icons );
			$social_links     = array();
			foreach ( $sabox_social_links as $index => $social_link ) {
				if ( $social_link ) {
					$social_platform = isset( $sabox_social_icons[ $index ] ) ? $sabox_social_icons[ $index ] : false;
					if ( $social_platform && isset( $social_platforms[ $social_platform ] ) ) {
						$social_links[$social_platform] = sanitize_text_field($social_link);
					}
				}
            }

            update_user_meta($user_id, 'sabox_social_links', $social_links);

        } else {
            delete_user_meta($user_id, 'sabox_social_links');
        }

        if (!current_user_can('upload_files', $user_id)) {
            return;
        }

        if (isset($_POST['sabox-custom-image']) && '' != $_POST['sabox-custom-image']) {
            update_user_meta($user_id, 'sabox-profile-image', esc_url_raw(wp_unslash($_POST['sabox-custom-image'])));
        } else {
            delete_user_meta($user_id, 'sabox-profile-image');
        }

    }

}

new Simple_Author_Box_User_Profile();
