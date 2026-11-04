<?php
/**
 * Image Carousel Divi - Rating Notice Handler
 *
 * This file contains the logic for displaying a rating notice in the WordPress admin
 * after the plugin has been active for 7 days. It manages plugin activation time,
 * installation checks, and user dismissal logic.
 *
 * @package Image_Carousel_Divi
 */

/**
 * Handles the rating notice functionality for the Image Carousel Divi plugin.
 *
 * This class sets up the rating notice, manages activation and installation time,
 * and provides logic for dismissing the notice.
 */
class Lwp_Image_Carousel_Rating {

	/**
	 * Lwp_Image_Carousel_Rating constructor.
	 *
	 * Registers the necessary WordPress hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_installation_time' ) );
		add_action( 'admin_init', array( $this, 'spare_me' ), 5 );
	}

	/**
	 * Sets the activation time of the plugin.
	 *
	 * This function is called when the plugin is activated.
	 */
	public function activation_time() {
		$get_activation_time = strtotime( 'now' );
		add_option( 'lwp_image_carousel_activation_time', $get_activation_time );
	}

	/**
	 * Checks the installation time of the plugin.
	 *
	 * This function is called on 'admin_init' hook.
	 */
	public function check_installation_time() {
		$install_date = get_option( 'lwp_image_carousel_activation_time' );
		$spare_me     = get_option( 'lwp_image_carousel_spare_me' );
		$past_date    = strtotime( '-7 days' );
		if ( false === $install_date ) {
			return;
		}
		if ( $past_date >= $install_date && false === $spare_me ) {
			add_action( 'admin_notices', array( $this, 'rating_admin_notice' ) );
		}
	}

	/**
	 * Displays a rating admin notice.
	 *
	 * This function is called when the plugin has been active for 7 days.
	 */
	public function rating_admin_notice() {
		$screen          = get_current_screen();
		$allowed_screens = array( 'dashboard', 'plugins' );
		if ( ! in_array( $screen->base, $allowed_screens, true ) ) {
			return;
		}

		$nonce        = wp_create_nonce( 'lwp_image_carousel_nonce' );
		$dont_disturb = esc_url( get_admin_url() . '?lwp_image_carousel_spare_me=1&lwp_image_carousel_nonce=' . $nonce );
		$dont_show    = esc_url( get_admin_url() . '?lwp_image_carousel_spare_me=1&lwp_image_carousel_nonce=' . $nonce );
		$plugin_info  = 'Divi Image Carousel';
		$reviewurl    = esc_url( 'https://wordpress.org/support/plugin/image-carousel-divi/reviews/?filter=5' );

		printf(
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			// translators: 1: Plugin name, 2: Review URL, 3: Don't disturb URL, 4: Don't show again URL.
			__(
				'<div class="wrap notice notice-info">
                    <div style="margin:10px 0px;">
                        Hello! Seems like you are using <strong>%1$s</strong> plugin to build your Divi website - Thanks a lot! Could you please do us a BIG favor and give it a 5-star rating on WordPress? This boosts our motivation and helps other users make a comfortable decision while choosing the plugin.
                    </div>
                    <div class="button-group" style="margin:10px 0px;">
                        <a href="%2$s" class="button button-primary" target="_blank" style="margin-right:10px;">Ok, you deserve it</a>
                        <span class="dashicons dashicons-smiley"></span>
                        <a href="%3$s" class="button button-link" style="margin-right:10px; margin-left:3px;">I already did</a>
                        <a href="%4$s" class="button button-link">Don\'t show this again.</a>
                    </div>
                </div>',
				'divi-image-carousel'
			),
			esc_html( $plugin_info ),
			esc_url( $reviewurl ),
			esc_url( $dont_disturb ),
			esc_url( $dont_show )
		);
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Spare-me logic to avoid showing the notice again.
	 */
	public function spare_me() {
		if ( isset( $_GET['lwp_image_carousel_spare_me'] ) && ! empty( $_GET['lwp_image_carousel_spare_me'] ) ) {
			$spare_me = sanitize_text_field( wp_unslash( $_GET['lwp_image_carousel_spare_me'] ) );
			if ( '1' === $spare_me && isset( $_GET['lwp_image_carousel_nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['lwp_image_carousel_nonce'] ), 'lwp_image_carousel_nonce' ) ) {
				add_option( 'lwp_image_carousel_spare_me', true );
			}
		}
	}
}
