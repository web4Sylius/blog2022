<?php
/**
 * Encadré « En bref » en haut des articles.
 *
 *   EN BREF
 *   Résumé de l'article en quelques lignes…
 *   • Point clé 1
 *   • Point clé 2
 *
 * - Saisi dans l'article (encadré « En bref », en tête des encadrés).
 * - Affiché juste sous l'image du haut de l'article, avant le sommaire.
 * - Sans image : en tête de l'article.
 */

const DIVI_CHILD_META_BREF_TEXTE  = '_mcav_bref_texte';
const DIVI_CHILD_META_BREF_POINTS = '_mcav_bref_points';

/** Balises permises dans le résumé et les points clés. */
function divi_child_bref_allowed_tags() {
	return array(
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
		'a'      => array( 'href' => true ),
	);
}

/* -------------------------------------------------------------------------
 * Administration
 * ---------------------------------------------------------------------- */

function divi_child_bref_add_meta_box() {
	add_meta_box( 'mcav_bref', 'En bref', 'divi_child_bref_meta_box', 'post', 'normal', 'high' );
}
// Avant les autres encadrés de l'article (priorité 5 < 10).
add_action( 'add_meta_boxes', 'divi_child_bref_add_meta_box', 5 );

function divi_child_bref_meta_box( $post ) {
	wp_nonce_field( 'mcav_bref', 'mcav_bref_nonce' );
	$texte  = get_post_meta( $post->ID, DIVI_CHILD_META_BREF_TEXTE, true );
	$points = get_post_meta( $post->ID, DIVI_CHILD_META_BREF_POINTS, true );
	?>
	<p class="description">Affiché sous l'image du haut de l'article, avant le sommaire. Laisser vide pour ne rien afficher.</p>
	<p>
		<label for="mcav_bref_texte"><strong>Résumé</strong></label><br>
		<textarea name="mcav_bref_texte" id="mcav_bref_texte" rows="4" style="width:100%"
			placeholder="La réponse à la question de l'article, en 3 ou 4 phrases."><?php echo esc_textarea( $texte ); ?></textarea>
	</p>
	<p>
		<label for="mcav_bref_points"><strong>Points clés</strong> (un par ligne, facultatif)</label><br>
		<textarea name="mcav_bref_points" id="mcav_bref_points" rows="4" style="width:100%"
			placeholder="Température stable : 10 à 14 °C&#10;Hygrométrie : 70 à 80 %"><?php echo esc_textarea( $points ); ?></textarea>
		<span class="description">Mise en forme possible : &lt;strong&gt;gras&lt;/strong&gt;, &lt;em&gt;italique&lt;/em&gt;, &lt;a href="…"&gt;lien&lt;/a&gt;.</span>
	</p>
	<?php
}

function divi_child_bref_save_post( $post_id ) {
	if ( ! isset( $_POST['mcav_bref_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_bref_nonce'] ) ), 'mcav_bref' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'mcav_bref_texte'  => DIVI_CHILD_META_BREF_TEXTE,
		'mcav_bref_points' => DIVI_CHILD_META_BREF_POINTS,
	);
	foreach ( $fields as $field => $meta ) {
		$value = isset( $_POST[ $field ] ) ? wp_kses( wp_unslash( $_POST[ $field ] ), divi_child_bref_allowed_tags() ) : '';
		$value = trim( $value );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta );
		} else {
			update_post_meta( $post_id, $meta, $value );
		}
	}
}
add_action( 'save_post_post', 'divi_child_bref_save_post' );

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

/** HTML de l'encadré, ou '' si rien n'est saisi. */
function divi_child_bref_html( $post ) {
	$allowed = divi_child_bref_allowed_tags();
	$texte   = trim( (string) get_post_meta( $post->ID, DIVI_CHILD_META_BREF_TEXTE, true ) );
	$lignes  = preg_split( '/\R/u', (string) get_post_meta( $post->ID, DIVI_CHILD_META_BREF_POINTS, true ) );

	$points = array();
	foreach ( $lignes as $ligne ) {
		// Retire les puces saisies à la main (« - », « • », « * »).
		$ligne = trim( preg_replace( '/^\s*[-•*]\s*/u', '', $ligne ) );
		if ( '' !== $ligne ) {
			$points[] = '<li>' . wp_kses( $ligne, $allowed ) . '</li>';
		}
	}

	if ( '' === $texte && ! $points ) {
		return '';
	}

	$html = '<aside class="mcav-bref" aria-label="En bref">'
		. '<p class="mcav-bref__titre">En bref</p>';
	if ( '' !== $texte ) {
		$html .= '<div class="mcav-bref__texte">' . wpautop( wp_kses( $texte, $allowed ) ) . '</div>';
	}
	if ( $points ) {
		$html .= '<ul class="mcav-bref__points">' . implode( '', $points ) . '</ul>';
	}
	return $html . '</aside>';
}

/**
 * Position juste après l'image du haut de l'article (module Image de Divi,
 * <figure>, légende WordPress ou image seule dans un paragraphe).
 * Renvoie 0 (tout en haut) s'il n'y a pas d'image avant le premier H2.
 */
function divi_child_bref_position( $content ) {
	$img = stripos( $content, '<img' );
	$h2  = stripos( $content, '<h2' );
	if ( false === $img || ( false !== $h2 && $h2 < $img ) ) {
		return 0;
	}
	$before = substr( $content, 0, $img );

	// Conteneur ouvert avant l'image et pas encore refermé : on se place après sa fermeture.
	$containers = array(
		'#<figure[\s>]#i'                             => '</figure>',
		'#<div[^>]*class="[^"]*\bet_pb_image\b#i'     => '</div>',
		'#<div[^>]*class="[^"]*\bwp-caption\b#i'      => '</div>',
		'#<p[\s>]#i'                                  => '</p>',
	);
	foreach ( $containers as $open => $close ) {
		if ( ! preg_match_all( $open, $before, $m, PREG_OFFSET_CAPTURE ) ) {
			continue;
		}
		$start = end( $m[0] )[1];
		if ( false !== stripos( substr( $before, $start ), $close ) ) {
			continue;
		}
		$end = stripos( $content, $close, $img );
		if ( false !== $end ) {
			return $end + strlen( $close );
		}
	}

	// Image seule (éventuellement dans un lien).
	$end = strpos( $content, '>', $img );
	if ( false === $end ) {
		return 0;
	}
	$end++;
	if ( preg_match( '#^\s*</a>#i', substr( $content, $end ), $m ) ) {
		$end += strlen( $m[0] );
	}
	return $end;
}

function divi_child_bref_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Un seul encadré, même si the_content passe plusieurs fois.
	if ( false !== strpos( $content, 'class="mcav-bref"' ) ) {
		return $content;
	}

	$post = get_post();
	$bref = $post ? divi_child_bref_html( $post ) : '';
	if ( '' === $bref ) {
		return $content;
	}

	// Toujours avant le sommaire, même si l'image est placée plus bas.
	$position = divi_child_bref_position( $content );
	$toc      = strpos( $content, '<nav class="mcav-toc"' );
	if ( false !== $toc && $toc < $position ) {
		$position = $toc;
	}
	return substr_replace( $content, $bref, $position, 0 );
}
// Après le sommaire (20) : il est déjà en place et l'encadré se cale avant lui.
add_filter( 'the_content', 'divi_child_bref_filter', 22 );
