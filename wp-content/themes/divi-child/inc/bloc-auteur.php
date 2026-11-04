<?php
/**
 * Bloc auteur en fin d'article (remplace celui de Simple Author Box).
 *
 *   ──────────────────────────────────────────────────────────── (filet or)
 *   (CR)  Cécile · rédactrice Ma Cave à Vin
 *         Biographie du profil. Article relu par Philippe Martin,
 *         conseiller depuis 15 ans.
 *         Tous les articles de Cécile · Qui sommes-nous
 *
 * - Nom, fonction, initiales et relecteur : les mêmes que la signature
 *   sous le titre (inc/byline.php).
 * - Biographie : champ « Renseignements biographiques » du profil.
 * - « Qui sommes-nous » : page de la boutique (le blog n'en a pas).
 */

const DIVI_CHILD_AUTEUR_URL_EQUIPE = 'https://www.ma-cave-a-vin.fr/int,4,qui-sommes-nous.htm';

function divi_child_auteur_box( $post = null ) {
	$post   = get_post( $post );
	$author = $post ? get_userdata( $post->post_author ) : false;
	if ( ! $author ) {
		return '';
	}

	$prenom   = divi_child_byline_short_name( $author );
	$fonction = trim( (string) get_user_meta( $author->ID, DIVI_CHILD_META_FONCTION, true ) );
	$titre    = esc_html( $prenom ) . ( $fonction ? ' · ' . esc_html( $fonction . ' Ma Cave à Vin' ) : '' );

	$bio = trim( (string) get_the_author_meta( 'description', $author->ID ) );
	$bio = $bio ? esc_html( $bio ) : '';

	$relecteur = get_userdata( (int) get_post_meta( $post->ID, DIVI_CHILD_META_RELECTEUR, true ) );
	if ( $relecteur && $relecteur->ID !== $author->ID ) {
		$role = divi_child_byline_role( $relecteur );
		$bio .= ( $bio ? ' ' : '' ) . sprintf(
			'Article relu par %s%s.',
			esc_html( divi_child_byline_full_name( $relecteur ) ),
			$role ? ', ' . esc_html( $role ) : ''
		);
	}

	$liens = array(
		sprintf( '<a href="%s" rel="author">Tous les articles de %s</a>', esc_url( get_author_posts_url( $author->ID ) ), esc_html( $prenom ) ),
		sprintf( '<a href="%s">Qui sommes-nous</a>', esc_url( DIVI_CHILD_AUTEUR_URL_EQUIPE ) ),
	);

	return '<aside class="mcav-auteur" aria-label="À propos de l\'auteur">'
		. '<span class="mcav-auteur__avatar" aria-hidden="true">' . esc_html( divi_child_byline_initials( $author ) ) . '</span>'
		. '<div class="mcav-auteur__texte">'
		. '<p class="mcav-auteur__nom">' . $titre . '</p>'
		. ( $bio ? '<p class="mcav-auteur__bio">' . $bio . '</p>' : '' )
		. '<p class="mcav-auteur__liens">' . implode( ' <span aria-hidden="true">·</span> ', $liens ) . '</p>'
		. '</div>'
		. '</aside>';
}

/**
 * Masque « Nombre de vues » (Post Views Counter) en bas des articles :
 * le compteur continue de compter, il n'est simplement plus affiché.
 */
function divi_child_auteur_masque_vues( $display ) {
	return is_singular( 'post' ) ? false : $display;
}
add_filter( 'pvc_display_views_count', 'divi_child_auteur_masque_vues' );
