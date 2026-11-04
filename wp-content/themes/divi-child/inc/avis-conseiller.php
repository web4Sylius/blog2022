<?php
/**
 * Encadré « L'avis du conseiller » dans les articles.
 *
 *   (DM)  « Ce que l'on observe chez nos clients… »
 *         Denis Martin, conseiller Ma Cave à Vin depuis 10 ans
 *
 * - Saisi dans l'article (encadré « L'avis du conseiller »).
 * - Conseiller : au choix, par défaut le relecteur de l'article.
 * - Placé avant le H2 choisi, par défaut au milieu de l'article.
 * - Fonction et ancienneté : profil utilisateur (voir inc/byline.php).
 */

const DIVI_CHILD_META_AVIS_TEXTE     = '_mcav_avis_texte';
const DIVI_CHILD_META_AVIS_CONSEILLER = '_mcav_avis_conseiller';
const DIVI_CHILD_META_AVIS_POSITION   = '_mcav_avis_position';

/** Titres H2 d'un contenu, en texte brut. */
function divi_child_avis_headings( $content ) {
	preg_match_all( '#<h2(?:\s[^>]*)?>(.*?)</h2>#is', (string) $content, $m );
	return array_map(
		function ( $h ) {
			return trim( wp_strip_all_tags( html_entity_decode( $h, ENT_QUOTES, 'UTF-8' ) ) );
		},
		$m[1]
	);
}

/** Position par défaut : avant le H2 du milieu (5e sur 8). */
function divi_child_avis_default_position( $count ) {
	return $count ? (int) floor( $count / 2 ) + 1 : 0;
}

/* -------------------------------------------------------------------------
 * Administration
 * ---------------------------------------------------------------------- */

function divi_child_avis_add_meta_box() {
	add_meta_box( 'mcav_avis', "L'avis du conseiller", 'divi_child_avis_meta_box', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'divi_child_avis_add_meta_box' );

function divi_child_avis_meta_box( $post ) {
	wp_nonce_field( 'mcav_avis', 'mcav_avis_nonce' );
	$texte      = get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_TEXTE, true );
	$conseiller = (int) get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_CONSEILLER, true );
	$position   = (int) get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_POSITION, true );
	$headings   = divi_child_avis_headings( divi_child_faq_content_for_headings( $post ) );
	$default    = divi_child_avis_default_position( count( $headings ) );
	?>
	<p>
		<label for="mcav_avis_texte"><strong>Texte de l'avis</strong></label><br>
		<textarea name="mcav_avis_texte" id="mcav_avis_texte" rows="3" style="width:100%"
			placeholder="Ce que l'on observe chez nos clients : une erreur fréquente, un cas concret…"><?php echo esc_textarea( $texte ); ?></textarea>
		<span class="description">Sans guillemets : ils sont ajoutés automatiquement. Laisser vide pour ne rien afficher.</span>
	</p>
	<p>
		<label for="mcav_avis_conseiller"><strong>Conseiller</strong></label><br>
		<?php
		wp_dropdown_users(
			array(
				'name'              => 'mcav_avis_conseiller',
				'id'                => 'mcav_avis_conseiller',
				'selected'          => $conseiller,
				'show_option_none'  => '— Le relecteur de l\'article —',
				'option_none_value' => 0,
				'capability'        => 'edit_posts',
				'show'              => 'display_name',
			)
		);
		?>
	</p>
	<p>
		<label for="mcav_avis_position"><strong>Emplacement</strong></label><br>
		<select name="mcav_avis_position" id="mcav_avis_position" style="max-width:100%">
			<option value="0"<?php selected( $position, 0 ); ?>>
				Automatique : milieu de l'article<?php echo $default ? esc_html( ' (avant « ' . wp_html_excerpt( $headings[ $default - 1 ], 60, '…' ) . ' »)' ) : ''; ?>
			</option>
			<?php foreach ( $headings as $i => $heading ) : ?>
				<option value="<?php echo esc_attr( $i + 1 ); ?>"<?php selected( $position, $i + 1 ); ?>>
					Avant « <?php echo esc_html( wp_html_excerpt( $heading, 70, '…' ) ); ?> »
				</option>
			<?php endforeach; ?>
			<option value="-1"<?php selected( $position, -1 ); ?>>À la fin de l'article</option>
		</select>
		<br><span class="description">La liste des titres se met à jour après l'enregistrement de l'article.</span>
	</p>
	<?php
}

function divi_child_avis_save_post( $post_id ) {
	if ( ! isset( $_POST['mcav_avis_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_avis_nonce'] ) ), 'mcav_avis' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$texte = isset( $_POST['mcav_avis_texte'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mcav_avis_texte'] ) ) : '';
	// Retire les guillemets saisis à la main : l'affichage ajoute les siens.
	$texte = trim( preg_replace( '/^[«"“\s]+|[»"”\s]+$/u', '', $texte ) );
	if ( '' === $texte ) {
		delete_post_meta( $post_id, DIVI_CHILD_META_AVIS_TEXTE );
	} else {
		update_post_meta( $post_id, DIVI_CHILD_META_AVIS_TEXTE, $texte );
	}

	$conseiller = isset( $_POST['mcav_avis_conseiller'] ) ? absint( $_POST['mcav_avis_conseiller'] ) : 0;
	update_post_meta( $post_id, DIVI_CHILD_META_AVIS_CONSEILLER, get_userdata( $conseiller ) ? $conseiller : 0 );

	$position = isset( $_POST['mcav_avis_position'] ) ? (int) $_POST['mcav_avis_position'] : 0;
	update_post_meta( $post_id, DIVI_CHILD_META_AVIS_POSITION, max( -1, $position ) );
}
add_action( 'save_post_post', 'divi_child_avis_save_post' );

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

/** « conseiller Ma Cave à Vin depuis 10 ans ». */
function divi_child_avis_role( $user ) {
	$role  = trim( (string) get_user_meta( $user->ID, DIVI_CHILD_META_FONCTION, true ) );
	$since = (int) get_user_meta( $user->ID, DIVI_CHILD_META_DEPUIS, true );
	$years = $since ? (int) wp_date( 'Y' ) - $since : 0;

	$parts = array();
	if ( $role ) {
		$parts[] = $role . ' Ma Cave à Vin';
	}
	if ( $years >= 1 ) {
		$parts[] = sprintf( 'depuis %d %s', $years, 1 === $years ? 'an' : 'ans' );
	}
	return implode( ' ', $parts );
}

/** HTML de l'encadré, ou '' si pas d'avis ou pas de conseiller. */
function divi_child_avis_html( $post ) {
	$texte = trim( (string) get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_TEXTE, true ) );
	if ( '' === $texte ) {
		return '';
	}

	$user_id = (int) get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_CONSEILLER, true );
	if ( ! $user_id && defined( 'DIVI_CHILD_META_RELECTEUR' ) ) {
		$user_id = (int) get_post_meta( $post->ID, DIVI_CHILD_META_RELECTEUR, true );
	}
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return '';
	}

	$role = divi_child_avis_role( $user );

	return '<aside class="mcav-avis" aria-label="L\'avis du conseiller">'
		. '<span class="mcav-avis__avatar" aria-hidden="true">' . esc_html( divi_child_byline_initials( $user ) ) . '</span>'
		. '<figure class="mcav-avis__body">'
		. '<blockquote class="mcav-avis__quote"><p>« ' . nl2br( esc_html( $texte ) ) . ' »</p></blockquote>'
		. '<figcaption class="mcav-avis__author"><strong>' . esc_html( divi_child_byline_full_name( $user ) ) . '</strong>'
		. ( $role ? ', ' . esc_html( $role ) : '' ) . '</figcaption>'
		. '</figure>'
		. '</aside>';
}

function divi_child_avis_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Un seul encadré, même si the_content passe plusieurs fois.
	if ( false !== strpos( $content, 'class="mcav-avis"' ) ) {
		return $content;
	}

	$post = get_post();
	$avis = $post ? divi_child_avis_html( $post ) : '';
	if ( '' === $avis ) {
		return $content;
	}

	// Positions des H2 du contenu affiché.
	preg_match_all( '#<h2[\s>]#i', $content, $m, PREG_OFFSET_CAPTURE );
	$offsets  = wp_list_pluck( $m[0], 1 );
	$position = (int) get_post_meta( $post->ID, DIVI_CHILD_META_AVIS_POSITION, true );
	if ( 0 === $position ) {
		$position = divi_child_avis_default_position( count( $offsets ) );
	}

	// Avant le H2 demandé ; à la fin si « à la fin », sans H2, ou si ce H2 n'existe plus.
	if ( $position < 1 || ! isset( $offsets[ $position - 1 ] ) ) {
		return $content . $avis;
	}
	return substr_replace( $content, $avis, $offsets[ $position - 1 ], 0 );
}
// Après le sommaire (20), qui ajoute les id aux H2.
add_filter( 'the_content', 'divi_child_avis_filter', 25 );
