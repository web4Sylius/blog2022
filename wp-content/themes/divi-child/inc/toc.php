<?php
/**
 * Sommaire automatique des articles.
 *
 * - Construit à partir des H2 du contenu, inséré juste avant le premier H2.
 * - Ajoute un id à chaque H2 (ancre) : Google peut afficher des liens
 *   vers les sections dans les résultats de recherche.
 * - Ouvert sur ordinateur, replié sur mobile (<details>).
 * - Seulement si l'article a au moins 3 H2 (filtre divi_child_toc_min_headings).
 */

function divi_child_toc_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Le filtre the_content peut passer plusieurs fois (Divi, plugins SEO) : un seul sommaire.
	if ( false !== strpos( $content, 'class="mcav-toc"' ) ) {
		return $content;
	}

	if ( ! preg_match_all( '#<h2(\s[^>]*)?>(.*?)</h2>#is', $content, $matches, PREG_SET_ORDER ) ) {
		return $content;
	}
	if ( count( $matches ) < (int) apply_filters( 'divi_child_toc_min_headings', 3 ) ) {
		return $content;
	}

	$used  = array();
	$items = array();

	foreach ( $matches as $match ) {
		$attrs = isset( $match[1] ) ? $match[1] : '';
		$label = trim( wp_strip_all_tags( html_entity_decode( $match[2], ENT_QUOTES, 'UTF-8' ) ) );
		if ( '' === $label ) {
			continue;
		}

		// Garde l'id existant, sinon en crée un lisible : « les-aromes-qui-apparaissent-avec-lage ».
		if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $id_match ) ) {
			$id = $id_match[2];
		} else {
			$base = sanitize_title( $label );
			$base = $base ? $base : 'section';
			$id   = $base;
			for ( $n = 2; isset( $used[ $id ] ); $n++ ) {
				$id = $base . '-' . $n;
			}
			$heading = '<h2 id="' . esc_attr( $id ) . '"' . $attrs . '>' . $match[2] . '</h2>';
			$content = preg_replace( '/' . preg_quote( $match[0], '/' ) . '/', $heading, $content, 1 );
		}
		$used[ $id ] = true;
		$items[]     = sprintf( '<li><a href="#%s">%s</a></li>', esc_attr( $id ), esc_html( $label ) );
	}

	if ( count( $items ) < (int) apply_filters( 'divi_child_toc_min_headings', 3 ) ) {
		return $content;
	}

	$toc = '<nav class="mcav-toc" aria-label="Sommaire">'
		. '<details open>'
		. '<summary>Sommaire</summary>'
		. '<ol>' . implode( '', $items ) . '</ol>'
		. '</details>'
		. '</nav>'
		// Replie le sommaire sur mobile (point de rupture Divi : 980 px).
		// « nowprocket » : WP Rocket ne doit pas retarder ce script, sinon le sommaire resterait ouvert.
		. '<script nowprocket>(function(){var d=document.currentScript.previousElementSibling.querySelector("details");'
		. 'if(d&&window.matchMedia("(max-width: 980px)").matches){d.removeAttribute("open");}})();</script>';

	// Insère le sommaire juste avant le premier H2 (après l'introduction).
	$first = strpos( $content, '<h2' );
	return false === $first ? $content : substr_replace( $content, $toc, $first, 0 );
}
add_filter( 'the_content', 'divi_child_toc_filter', 20 );
