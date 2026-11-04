<?php
/**
 * Shortcodes du thème.
 *
 * [pied_de_page] : texte du pied de page, à placer dans le module texte du
 *   pied de page (Theme Builder de Divi). Le texte se modifie ici.
 * [annee] : année en cours.
 */

function divi_child_shortcode_annee() {
	return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'annee', 'divi_child_shortcode_annee' );

function divi_child_shortcode_pied_de_page() {
	return sprintf(
		'Depuis 2004, Ma Cave à Vin, Lyon · <a href="tel:+33426030543">04 26 03 05 43</a>, du lundi au vendredi de 9&nbsp;h à 19&nbsp;h · ©&nbsp;2004-%s',
		divi_child_shortcode_annee()
	);
}
add_shortcode( 'pied_de_page', 'divi_child_shortcode_pied_de_page' );
