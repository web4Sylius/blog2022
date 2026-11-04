<?php
/**
 * Encadré « Sortie vers la boutique » dans les articles.
 *
 *   ┌────────────────────────────────────────────────────────────┐
 *   │ Titre en gras                          [ Voir les caves… ] │
 *   │ Texte. Lire aussi : lien vers un article                   │
 *   └────────────────────────────────────────────────────────────┘
 *
 * - Saisi dans l'article (encadré « Encadré boutique »).
 * - Placé avant le H2 choisi ; par défaut avant l'avant-dernier H2
 *   (juste avant la dernière partie, en général avant la conclusion).
 * - N'apparaît que si le titre et le lien du bouton sont remplis.
 */

const DIVI_CHILD_META_CTA = '_mcav_cta';

/** Valeurs de l'encadré, complétées par les valeurs par défaut. */
function divi_child_cta_get( $post_id ) {
	$cta = get_post_meta( $post_id, DIVI_CHILD_META_CTA, true );
	return wp_parse_args(
		is_array( $cta ) ? $cta : array(),
		array(
			'titre'          => '',
			'texte'          => '',
			'bouton_libelle' => '',
			'bouton_url'     => '',
			'lire_post'      => 0,
			'lire_libelle'   => '',
			'position'       => 0,
		)
	);
}

/** Titres H2 bruts d'un contenu (même logique que le sommaire). */
function divi_child_cta_headings( $content ) {
	preg_match_all( '#<h2(?:\s[^>]*)?>(.*?)</h2>#is', (string) $content, $m );
	return array_map(
		function ( $h ) {
			return trim( wp_strip_all_tags( html_entity_decode( $h, ENT_QUOTES, 'UTF-8' ) ) );
		},
		$m[1]
	);
}

/** Par défaut : avant l'avant-dernier H2 (7e sur 8), ou à la fin s'il y a moins de 2 H2. */
function divi_child_cta_default_position( $count ) {
	return $count >= 2 ? $count - 1 : -1;
}

/* -------------------------------------------------------------------------
 * Administration
 * ---------------------------------------------------------------------- */

function divi_child_cta_add_meta_box() {
	add_meta_box( 'mcav_cta', 'Encadré boutique', 'divi_child_cta_meta_box', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'divi_child_cta_add_meta_box' );

function divi_child_cta_meta_box( $post ) {
	wp_nonce_field( 'mcav_cta', 'mcav_cta_nonce' );
	$cta      = divi_child_cta_get( $post->ID );
	$headings = divi_child_cta_headings( $post->post_content );
	$default  = divi_child_cta_default_position( count( $headings ) );
	$articles = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'exclude'        => array( $post->ID ),
			'fields'         => 'ids',
		)
	);
	$field = function ( $name, $label, $value, $placeholder = '', $type = 'text' ) {
		printf(
			'<p><label for="mcav_cta_%1$s"><strong>%2$s</strong></label><br><input type="%5$s" name="mcav_cta[%1$s]" id="mcav_cta_%1$s" value="%3$s" placeholder="%4$s" style="width:100%%"></p>',
			esc_attr( $name ),
			esc_html( $label ),
			esc_attr( $value ),
			esc_attr( $placeholder ),
			esc_attr( $type )
		);
	};
	?>
	<p class="description">L'encadré s'affiche quand le titre et le lien du bouton sont remplis.</p>
	<?php
	$field( 'titre', 'Titre', $cta['titre'], 'Une cave naturelle ou une cave de vieillissement réunit ces conditions' );
	?>
	<p>
		<label for="mcav_cta_texte"><strong>Texte</strong></label><br>
		<textarea name="mcav_cta[texte]" id="mcav_cta_texte" rows="2" style="width:100%"
			placeholder="Température et hygrométrie régulées, sans vibrations."><?php echo esc_textarea( $cta['texte'] ); ?></textarea>
	</p>
	<?php
	$field( 'bouton_libelle', 'Texte du bouton', $cta['bouton_libelle'], 'Voir les caves de vieillissement' );
	$field( 'bouton_url', 'Lien du bouton (page de la boutique)', $cta['bouton_url'], 'https://www.ma-cave-a-vin.fr/…', 'url' );
	?>
	<p>
		<label for="mcav_cta_lire_post"><strong>« Lire aussi » (facultatif)</strong></label><br>
		<select name="mcav_cta[lire_post]" id="mcav_cta_lire_post" style="max-width:100%">
			<option value="0">— Aucun —</option>
			<?php foreach ( $articles as $id ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>"<?php selected( (int) $cta['lire_post'], $id ); ?>><?php echo esc_html( wp_html_excerpt( get_the_title( $id ), 90, '…' ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
	$field( 'lire_libelle', 'Texte du lien « Lire aussi » (sinon : titre de l\'article)', $cta['lire_libelle'], 'vieillissement, service ou multi-températures ?' );
	?>
	<p>
		<label for="mcav_cta_position"><strong>Emplacement</strong></label><br>
		<select name="mcav_cta[position]" id="mcav_cta_position" style="max-width:100%">
			<option value="0"<?php selected( (int) $cta['position'], 0 ); ?>>
				Automatique<?php echo $default > 0 ? esc_html( ' (avant « ' . wp_html_excerpt( $headings[ $default - 1 ], 60, '…' ) . ' »)' ) : ' (fin de l\'article)'; ?>
			</option>
			<?php foreach ( $headings as $i => $heading ) : ?>
				<option value="<?php echo esc_attr( $i + 1 ); ?>"<?php selected( (int) $cta['position'], $i + 1 ); ?>>
					Avant « <?php echo esc_html( wp_html_excerpt( $heading, 70, '…' ) ); ?> »
				</option>
			<?php endforeach; ?>
			<option value="-1"<?php selected( (int) $cta['position'], -1 ); ?>>À la fin de l'article</option>
		</select>
		<br><span class="description">La liste des titres se met à jour après l'enregistrement de l'article.</span>
	</p>
	<?php
}

function divi_child_cta_save_post( $post_id ) {
	if ( ! isset( $_POST['mcav_cta_nonce'], $_POST['mcav_cta'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_cta_nonce'] ) ), 'mcav_cta' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in  = (array) wp_unslash( $_POST['mcav_cta'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- nettoyé champ par champ.
	$cta = array(
		'titre'          => sanitize_text_field( $in['titre'] ?? '' ),
		'texte'          => sanitize_textarea_field( $in['texte'] ?? '' ),
		'bouton_libelle' => sanitize_text_field( $in['bouton_libelle'] ?? '' ),
		'bouton_url'     => esc_url_raw( trim( $in['bouton_url'] ?? '' ), array( 'http', 'https' ) ),
		'lire_post'      => absint( $in['lire_post'] ?? 0 ),
		'lire_libelle'   => sanitize_text_field( $in['lire_libelle'] ?? '' ),
		'position'       => max( -1, (int) ( $in['position'] ?? 0 ) ),
	);
	if ( '' === $cta['titre'] && '' === $cta['bouton_url'] && '' === $cta['texte'] ) {
		delete_post_meta( $post_id, DIVI_CHILD_META_CTA );
	} else {
		update_post_meta( $post_id, DIVI_CHILD_META_CTA, $cta );
	}
}
add_action( 'save_post_post', 'divi_child_cta_save_post' );

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

function divi_child_cta_html( $post ) {
	$cta = divi_child_cta_get( $post->ID );
	if ( '' === $cta['titre'] || '' === $cta['bouton_url'] ) {
		return '';
	}

	$texte = $cta['texte'] ? esc_html( $cta['texte'] ) : '';

	$lire = $cta['lire_post'] ? get_post( $cta['lire_post'] ) : null;
	if ( $lire && 'publish' === $lire->post_status ) {
		$libelle = $cta['lire_libelle'] ? $cta['lire_libelle'] : get_the_title( $lire );
		$texte  .= ( $texte ? ' ' : '' ) . sprintf(
			'Lire aussi&nbsp;: <a href="%s">%s</a>',
			esc_url( get_permalink( $lire ) ),
			esc_html( $libelle )
		);
	}

	$bouton = $cta['bouton_libelle'] ? $cta['bouton_libelle'] : 'Voir la boutique';

	return '<aside class="mcav-cta" aria-label="Ma Cave à Vin, la boutique">'
		. '<div class="mcav-cta__text">'
		. '<p class="mcav-cta__title">' . esc_html( $cta['titre'] ) . '</p>'
		. ( $texte ? '<p class="mcav-cta__desc">' . $texte . '</p>' : '' ) // phpcs:ignore WordPress.Security.EscapeOutput -- échappé plus haut.
		. '</div>'
		. '<a class="mcav-cta__button" href="' . esc_url( $cta['bouton_url'] ) . '">' . esc_html( $bouton ) . '</a>'
		. '</aside>';
}

function divi_child_cta_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Un seul encadré, même si the_content passe plusieurs fois.
	if ( false !== strpos( $content, 'class="mcav-cta"' ) ) {
		return $content;
	}

	$post = get_post();
	$html = $post ? divi_child_cta_html( $post ) : '';
	if ( '' === $html ) {
		return $content;
	}

	preg_match_all( '#<h2[\s>]#i', $content, $m, PREG_OFFSET_CAPTURE );
	$offsets  = wp_list_pluck( $m[0], 1 );
	$position = (int) divi_child_cta_get( $post->ID )['position'];
	if ( 0 === $position ) {
		$position = divi_child_cta_default_position( count( $offsets ) );
	}

	// Avant le H2 demandé ; à la fin si « à la fin » ou si ce H2 n'existe plus.
	if ( $position < 1 || ! isset( $offsets[ $position - 1 ] ) ) {
		return $content . $html;
	}
	return substr_replace( $content, $html, $offsets[ $position - 1 ], 0 );
}
// Après le sommaire (20) et l'avis du conseiller (25).
add_filter( 'the_content', 'divi_child_cta_filter', 30 );
