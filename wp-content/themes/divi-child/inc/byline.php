<?php
/**
 * Bloc auteur sous le titre des articles :
 * « Par Cécile, rédactrice · Relu par Philippe, conseiller depuis 15 ans »
 * « Publié le 16 octobre 2026 · Mis à jour le 3 novembre 2026 · 8 min »
 *
 * - Fonction et année de début : champs du profil utilisateur.
 * - Relecteur : choisi dans l'article (encadré « Relecteur »).
 */

const DIVI_CHILD_META_FONCTION  = 'mcav_fonction';
const DIVI_CHILD_META_DEPUIS    = 'mcav_depuis';
const DIVI_CHILD_META_RELECTEUR = '_mcav_relecteur';

/* -------------------------------------------------------------------------
 * Profil utilisateur : fonction + année de début
 * ---------------------------------------------------------------------- */

function divi_child_byline_user_fields( $user ) {
	?>
	<h2>Signature des articles</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="mcav_fonction">Fonction affichée</label></th>
			<td>
				<input type="text" name="mcav_fonction" id="mcav_fonction" class="regular-text"
					value="<?php echo esc_attr( get_user_meta( $user->ID, DIVI_CHILD_META_FONCTION, true ) ); ?>">
				<p class="description">Ex. : rédactrice, conseiller, sommelier. Laisser vide pour ne rien afficher.</p>
			</td>
		</tr>
		<tr>
			<th><label for="mcav_depuis">En poste depuis (année)</label></th>
			<td>
				<input type="number" name="mcav_depuis" id="mcav_depuis" class="small-text" min="1950" max="<?php echo esc_attr( wp_date( 'Y' ) ); ?>"
					value="<?php echo esc_attr( get_user_meta( $user->ID, DIVI_CHILD_META_DEPUIS, true ) ); ?>">
				<p class="description">Affiche « depuis X ans ». Laisser vide pour ne rien afficher.</p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'divi_child_byline_user_fields' );
add_action( 'edit_user_profile', 'divi_child_byline_user_fields' );

function divi_child_byline_save_user_fields( $user_id ) {
	// La vérification du nonce est faite par WordPress sur l'écran de profil.
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( isset( $_POST['mcav_fonction'] ) ) {
		update_user_meta( $user_id, DIVI_CHILD_META_FONCTION, sanitize_text_field( wp_unslash( $_POST['mcav_fonction'] ) ) );
	}
	if ( isset( $_POST['mcav_depuis'] ) ) {
		$year = absint( $_POST['mcav_depuis'] );
		update_user_meta( $user_id, DIVI_CHILD_META_DEPUIS, $year >= 1950 && $year <= (int) wp_date( 'Y' ) ? $year : '' );
	}
}
add_action( 'personal_options_update', 'divi_child_byline_save_user_fields' );
add_action( 'edit_user_profile_update', 'divi_child_byline_save_user_fields' );

/* -------------------------------------------------------------------------
 * Article : choix du relecteur
 * ---------------------------------------------------------------------- */

function divi_child_byline_add_meta_box() {
	add_meta_box( 'mcav_relecteur', 'Relecteur', 'divi_child_byline_meta_box', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'divi_child_byline_add_meta_box' );

function divi_child_byline_meta_box( $post ) {
	wp_nonce_field( 'mcav_relecteur', 'mcav_relecteur_nonce' );
	wp_dropdown_users(
		array(
			'name'              => 'mcav_relecteur',
			'selected'          => (int) get_post_meta( $post->ID, DIVI_CHILD_META_RELECTEUR, true ),
			'show_option_none'  => '— Aucun relecteur —',
			'option_none_value' => 0,
			'capability'        => 'edit_posts',
			'show'              => 'display_name',
		)
	);
	echo '<p class="description">Affiché sous le titre : « Relu par… ». La fonction vient du profil du relecteur.</p>';
}

function divi_child_byline_save_post( $post_id ) {
	if ( ! isset( $_POST['mcav_relecteur_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_relecteur_nonce'] ) ), 'mcav_relecteur' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$reviewer = isset( $_POST['mcav_relecteur'] ) ? absint( $_POST['mcav_relecteur'] ) : 0;
	if ( $reviewer && get_userdata( $reviewer ) ) {
		update_post_meta( $post_id, DIVI_CHILD_META_RELECTEUR, $reviewer );
	} else {
		delete_post_meta( $post_id, DIVI_CHILD_META_RELECTEUR );
	}
}
add_action( 'save_post_post', 'divi_child_byline_save_post' );

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

/** Prénom si renseigné, sinon nom d'affichage. */
function divi_child_byline_short_name( $user ) {
	return $user->first_name ? $user->first_name : $user->display_name;
}

/** « rédactrice », « conseiller depuis 15 ans », ou ''. */
function divi_child_byline_role( $user ) {
	$role  = trim( (string) get_user_meta( $user->ID, DIVI_CHILD_META_FONCTION, true ) );
	$since = (int) get_user_meta( $user->ID, DIVI_CHILD_META_DEPUIS, true );
	$years = $since ? (int) wp_date( 'Y' ) - $since : 0;

	if ( $years >= 1 ) {
		$role .= ( $role ? ' ' : '' ) . sprintf( 'depuis %d %s', $years, 1 === $years ? 'an' : 'ans' );
	}
	return $role;
}

/** Initiales pour la pastille : prénom + nom, sinon première lettre. */
function divi_child_byline_initials( $user ) {
	$parts = array_filter( array( $user->first_name, $user->last_name ) );
	if ( ! $parts ) {
		$parts = array( $user->display_name );
	}
	$initials = '';
	foreach ( $parts as $part ) {
		$initials .= mb_strtoupper( mb_substr( trim( $part ), 0, 1 ) );
	}
	return $initials;
}

/** Temps de lecture en minutes (200 mots/min), shortcodes Divi exclus. */
function divi_child_byline_reading_time( $post ) {
	$text  = preg_replace( '/\[\/?[a-zA-Z0-9_-]+[^\]]*\]/', ' ', $post->post_content );
	$words = preg_split( '/\s+/u', trim( wp_strip_all_tags( $text ) ), -1, PREG_SPLIT_NO_EMPTY );
	$wpm   = (int) apply_filters( 'divi_child_byline_words_per_minute', 200 );
	return max( 1, (int) ceil( count( $words ) / max( 1, $wpm ) ) );
}

function divi_child_byline_person( $label, $user, $link = false ) {
	$name = esc_html( divi_child_byline_short_name( $user ) );
	if ( $link ) {
		$name = sprintf( '<a href="%s" rel="author">%s</a>', esc_url( get_author_posts_url( $user->ID ) ), $name );
	}
	$role = divi_child_byline_role( $user );
	return sprintf(
		'%s <strong class="mcav-byline__name">%s</strong>%s',
		esc_html( $label ),
		$name,
		$role ? ', ' . esc_html( $role ) : ''
	);
}

function divi_child_byline( $post = null ) {
	$post   = get_post( $post );
	$author = $post ? get_userdata( $post->post_author ) : false;
	if ( ! $author ) {
		return;
	}

	$people = array( divi_child_byline_person( 'Par', $author, true ) );

	$reviewer = get_userdata( (int) get_post_meta( $post->ID, DIVI_CHILD_META_RELECTEUR, true ) );
	if ( $reviewer && $reviewer->ID !== $author->ID ) {
		$people[] = divi_child_byline_person( 'Relu par', $reviewer, count_user_posts( $reviewer->ID, 'post', true ) > 0 );
	}

	$dates = array(
		sprintf( 'Publié le <time datetime="%s">%s</time>', esc_attr( get_the_date( 'c', $post ) ), esc_html( get_the_date( 'j F Y', $post ) ) ),
	);
	// « Mis à jour » seulement si la modification date d'un autre jour que la publication.
	if ( get_the_modified_date( 'Ymd', $post ) > get_the_date( 'Ymd', $post ) ) {
		$dates[] = sprintf( 'Mis à jour le <time datetime="%s">%s</time>', esc_attr( get_the_modified_date( 'c', $post ) ), esc_html( get_the_modified_date( 'j F Y', $post ) ) );
	}
	$dates[] = esc_html( divi_child_byline_reading_time( $post ) . ' min' );

	$sep = ' <span class="mcav-byline__sep" aria-hidden="true">·</span> ';
	?>
	<div class="mcav-byline">
		<div class="mcav-byline__people">
			<span class="mcav-byline__avatar" aria-hidden="true"><?php echo esc_html( divi_child_byline_initials( $author ) ); ?></span>
			<span><?php echo implode( $sep, $people ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé plus haut. ?></span>
		</div>
		<div class="mcav-byline__dates"><?php echo implode( $sep, $dates ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé plus haut. ?></div>
	</div>
	<?php
}
