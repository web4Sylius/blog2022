<?php
/**
 * Balise de validation Google Search Console, dans le <head> de la page d'accueil.
 */
function divi_child_google_site_verification() {
	if ( is_front_page() ) {
		echo '<meta name="google-site-verification" content="y4UqpSvqFAXVzH4KmPdo-wF4FWVAp4bYwNhjgKDcM8g" />' . "\n";
	}
}
add_action( 'wp_head', 'divi_child_google_site_verification', 1 );
