<?php
/**
 * Texte du bas de page, à la place des crédits de Divi :
 * « Depuis 2004, Ma Cave à Vin, Lyon · 04 26 03 05 43, du lundi au vendredi
 *   de 9 h à 19 h · © 2004-2026 » (année mise à jour automatiquement).
 *
 * Divi permet de remplacer sa fonction et_get_footer_credits() : le thème
 * enfant est chargé avant lui, donc cette version est prise en priorité,
 * et le réglage « Crédits du pied de page » de Divi n'est plus utilisé.
 */

function et_get_footer_credits() {
	return sprintf(
		'<div id="footer-info">Depuis 2004, Ma Cave à Vin, Lyon · <a href="tel:+33426030543">04 26 03 05 43</a>, du lundi au vendredi de 9&nbsp;h à 19&nbsp;h · ©&nbsp;2004-%s</div>',
		esc_html( wp_date( 'Y' ) )
	);
}
