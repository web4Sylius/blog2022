<?php
/**
 * Bloc « Pour aller plus loin » en fin d'article, juste avant le bloc auteur.
 *
 *   Pour aller plus loin
 *   [ Article choisi 1 ] [ Article choisi 2 ] [ Article choisi 3 ]
 *
 * - Dans l'article, on choisit 3 articles publiés (encadré « Pour aller plus loin »).
 * - Si au moins un article est choisi, le bloc remplace la grille d'articles
 *   (module « Blog » de Divi) placée dans le contenu de l'article.
 *   Sans choix, rien ne change : la grille reste affichée.
 */

const DIVI_CHILD_META_PLUS_LOIN      = '_mcav_plus_loin';      // Article : identifiants choisis, dans l'ordre.
const DIVI_CHILD_META_PLUS_LOIN_LIEN = '_mcav_plus_loin_lien'; // Article : une ligne par lien (pour retrouver qui pointe vers un article).
const DIVI_CHILD_PLUS_LOIN_SLOTS     = 3;
const DIVI_CHILD_PLUS_LOIN_TITRE     = 'Pour aller plus loin';

/* -------------------------------------------------------------------------
 * Données
 * ---------------------------------------------------------------------- */

/** Identifiants choisis dans un article, dans l'ordre. */
function divi_child_plus_loin_ids( $post_id ) {
	$ids = get_post_meta( $post_id, DIVI_CHILD_META_PLUS_LOIN, true );
	return is_array( $ids ) ? array_values( array_filter( array_map( 'absint', $ids ) ) ) : array();
}

/** Articles choisis encore publiés, dans l'ordre. */
function divi_child_plus_loin_published( $post_id ) {
	$articles = array();
	foreach ( divi_child_plus_loin_ids( $post_id ) as $id ) {
		$article = get_post( $id );
		if ( $article && 'post' === $article->post_type && 'publish' === $article->post_status && (int) $post_id !== $article->ID ) {
			$articles[] = $article;
		}
	}
	return $articles;
}

/** Articles qui pointent vers un article donné. */
function divi_child_plus_loin_referents( $post_id ) {
	return get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => DIVI_CHILD_META_PLUS_LOIN_LIEN, // phpcs:ignore WordPress.DB.SlowDBQuery -- enregistrement d'article uniquement.
			'meta_value'     => (string) $post_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

function divi_child_plus_loin_html( $post = null ) {
	$post     = get_post( $post );
	$articles = $post ? divi_child_plus_loin_published( $post->ID ) : array();
	if ( ! $articles ) {
		return '';
	}
	$html = '<nav class="mcav-plus-loin" aria-labelledby="mcav-plus-loin-titre">'
		. '<h2 id="mcav-plus-loin-titre">' . esc_html( DIVI_CHILD_PLUS_LOIN_TITRE ) . '</h2><ul>';
	foreach ( $articles as $article ) {
		$html .= '<li><a href="' . esc_url( get_permalink( $article ) ) . '">' . esc_html( get_the_title( $article ) ) . '</a></li>';
	}
	return $html . '</ul></nav>';
}

/**
 * Masque la grille d'articles (module « Blog » de Divi) du contenu de l'article
 * quand le bloc « Pour aller plus loin » la remplace.
 * Seulement pendant le contenu de l'article affiché : l'en-tête et le pied de page
 * du Theme Builder ne sont pas touchés.
 */
function divi_child_plus_loin_contenu_debut( $content ) {
	$GLOBALS['divi_child_plus_loin_masque_blog'] = is_singular( 'post' ) && in_the_loop() && is_main_query()
		&& get_the_ID() === get_queried_object_id()
		&& divi_child_plus_loin_published( get_queried_object_id() );
	return $content;
}
add_filter( 'the_content', 'divi_child_plus_loin_contenu_debut', 1 );

function divi_child_plus_loin_contenu_fin( $content ) {
	$GLOBALS['divi_child_plus_loin_masque_blog'] = false;
	return $content;
}
add_filter( 'the_content', 'divi_child_plus_loin_contenu_fin', 99 );

function divi_child_plus_loin_masque_blog( $output ) {
	return empty( $GLOBALS['divi_child_plus_loin_masque_blog'] ) ? $output : '';
}
add_filter( 'et_pb_blog_shortcode_output', 'divi_child_plus_loin_masque_blog' );

/* -------------------------------------------------------------------------
 * Administration
 * ---------------------------------------------------------------------- */

function divi_child_plus_loin_add_meta_box() {
	add_meta_box( 'mcav_plus_loin', DIVI_CHILD_PLUS_LOIN_TITRE, 'divi_child_plus_loin_box', 'post', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'divi_child_plus_loin_add_meta_box' );

function divi_child_plus_loin_box( $post ) {
	wp_nonce_field( 'mcav_plus_loin', 'mcav_plus_loin_nonce' );
	$choisis = divi_child_plus_loin_ids( $post->ID );
	$tous    = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post__not_in'   => array( $post->ID ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams -- administration.
		)
	);
	?>
	<p class="description">
		Trois liens vers des articles publiés, juste avant le bloc auteur.
		Dès qu'un article est choisi, ce bloc remplace la grille d'articles (module « Blog ») de la fin de l'article.
	</p>
	<?php for ( $i = 0; $i < DIVI_CHILD_PLUS_LOIN_SLOTS; $i++ ) : ?>
		<p style="margin:6px 0">
			<label for="mcav_plus_loin_<?php echo (int) $i; ?>" class="screen-reader-text">Article <?php echo (int) ( $i + 1 ); ?></label>
			<?php echo (int) ( $i + 1 ); ?>.
			<select name="mcav_plus_loin[]" id="mcav_plus_loin_<?php echo (int) $i; ?>" style="max-width:90%">
				<option value="0">—</option>
				<?php foreach ( $tous as $article ) : ?>
					<option value="<?php echo (int) $article->ID; ?>"<?php selected( $choisis[ $i ] ?? 0, $article->ID ); ?>>
						<?php echo esc_html( wp_html_excerpt( get_the_title( $article ), 110, '…' ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	endfor;
}

function divi_child_plus_loin_save( $post_id ) {
	if ( ! isset( $_POST['mcav_plus_loin_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_plus_loin_nonce'] ) ), 'mcav_plus_loin' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| wp_is_post_revision( $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['mcav_plus_loin'] ?? array() ) ) ) ) );
	$ids = array_slice(
		array_values(
			array_filter(
				$ids,
				function ( $id ) use ( $post_id ) {
					return $id !== $post_id && 'post' === get_post_type( $id );
				}
			)
		),
		0,
		DIVI_CHILD_PLUS_LOIN_SLOTS
	);

	delete_post_meta( $post_id, DIVI_CHILD_META_PLUS_LOIN_LIEN );
	if ( $ids ) {
		update_post_meta( $post_id, DIVI_CHILD_META_PLUS_LOIN, $ids );
		foreach ( $ids as $id ) {
			add_post_meta( $post_id, DIVI_CHILD_META_PLUS_LOIN_LIEN, (string) $id );
		}
	} else {
		delete_post_meta( $post_id, DIVI_CHILD_META_PLUS_LOIN );
	}
}
add_action( 'save_post_post', 'divi_child_plus_loin_save' );

/**
 * Un article lié change (titre, adresse, dépublication) :
 * rafraîchir en cache (WP Rocket) les articles qui pointent vers lui.
 */
function divi_child_plus_loin_vide_cache( $post_id ) {
	if ( ! function_exists( 'rocket_clean_post' ) || wp_is_post_revision( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
		return;
	}
	foreach ( divi_child_plus_loin_referents( $post_id ) as $article ) {
		rocket_clean_post( $article );
	}
}
add_action( 'post_updated', 'divi_child_plus_loin_vide_cache' );
add_action( 'trashed_post', 'divi_child_plus_loin_vide_cache' );
