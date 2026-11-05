<?php
/**
 * Thème enfant Divi – Ma Cave à Vin.
 *
 * Personnalisations déplacées depuis Divi/functions.php.
 */

/**
 * À l'activation, reprend les réglages du Customizer de Divi
 * (emplacement des menus, CSS additionnel, widgets), que WordPress
 * stocke séparément pour chaque thème.
 */
function divi_child_copy_parent_theme_mods( $old_name, $old_theme = false ) {
	$child_mods = get_option( 'theme_mods_' . get_stylesheet() );
	$child_mods = is_array( $child_mods ) ? $child_mods : array();

	// Une seule fois : ne pas écraser des réglages faits ensuite sur le thème enfant.
	if ( ! empty( $child_mods['divi_child_parent_mods_imported'] ) ) {
		return;
	}

	$parent_mods = get_option( 'theme_mods_' . get_template() );
	$parent_mods = is_array( $parent_mods ) ? $parent_mods : array();

	// WordPress a pu créer quelques réglages à l'activation (menus) : on complète sans les écraser.
	$merged = array_merge( $parent_mods, $child_mods );
	$merged['divi_child_parent_mods_imported'] = 1;

	update_option( 'theme_mods_' . get_stylesheet(), $merged );
}
add_action( 'after_switch_theme', 'divi_child_copy_parent_theme_mods', 10, 2 );

/**
 * Charge les personnalisations après le functions.php de Divi.
 *
 * Tant que le serveur a un ancien Divi/functions.php qui contient encore ce
 * code, on ne le charge pas une seconde fois (sinon : « Cannot redeclare »).
 */
function divi_child_load_custom_functions() {
	if ( function_exists( 'divi_filtergrid_orderby_views' ) ) {
		return;
	}
	require_once __DIR__ . '/inc/custom-functions.php';
}
add_action( 'after_setup_theme', 'divi_child_load_custom_functions', 0 );

// Shortcodes : [pied_de_page] (texte du pied de page) et [annee].
require_once __DIR__ . '/inc/shortcodes.php';

// Bloc auteur / relecteur / dates sous le titre des articles.
require_once __DIR__ . '/inc/byline.php';

// Bloc auteur en fin d'article (utilise inc/byline.php).
require_once __DIR__ . '/inc/bloc-auteur.php';

// Encadré « L'avis du conseiller » (utilise les profils de inc/byline.php).
require_once __DIR__ . '/inc/avis-conseiller.php';

// Encadré de sortie vers la boutique.
require_once __DIR__ . '/inc/encadre-boutique.php';

// FAQ : type de contenu « FAQ », choisi dans les articles (affichage + FAQPage).
require_once __DIR__ . '/inc/faq.php';

// Sources : type de contenu « Sources », choisi dans les articles (liste en fin d'article).
require_once __DIR__ . '/inc/sources.php';

// « Pour aller plus loin » : 3 articles choisis, avant le bloc auteur (remplace la grille Divi).
require_once __DIR__ . '/inc/pour-aller-plus-loin.php';

// Sommaire automatique construit à partir des H2 des articles.
require_once __DIR__ . '/inc/toc.php';

/**
 * Divi charge style.css de l'enfant avec sa propre version (ex. ?ver=4.14.4),
 * qui ne change jamais : les navigateurs gardent l'ancien CSS en cache.
 * On met à la place la date de modification du fichier.
 */
function divi_child_style_version() {
	$styles = wp_styles();
	$file   = get_stylesheet_directory() . '/style.css';
	if ( isset( $styles->registered['divi-style'] ) && file_exists( $file ) ) {
		$styles->registered['divi-style']->ver = (string) filemtime( $file );
	}
}
add_action( 'wp_enqueue_scripts', 'divi_child_style_version', 20 );
