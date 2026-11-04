<?php
/**
 * Sources des articles.
 *
 * - Type de contenu « Sources » : titre = texte affiché
 *   (ex. « Émile Peynaud, Le Goût du vin, Dunod, 2013 »), plus un lien facultatif.
 *   Pas de page publique, seulement l'administration.
 * - Dans l'article, on choisit et on ordonne les sources (encadré « Sources »).
 * - Affichage : H2 « Sources » + liste numérotée, à la fin de l'article,
 *   après la FAQ (qui se place avant le H2 « Sources »). Le H2 entre dans le sommaire.
 */

const DIVI_CHILD_SOURCE_TYPE      = 'mcav_source';
const DIVI_CHILD_META_SOURCES     = '_mcav_sources';      // Article : identifiants des sources, dans l'ordre.
const DIVI_CHILD_META_SOURCE_LIEN = '_mcav_source_lien';  // Article : une ligne par source (pour retrouver les articles d'une source).
const DIVI_CHILD_META_SOURCE_URL  = '_mcav_source_url';   // Source : lien.
const DIVI_CHILD_SOURCES_SLOTS    = 8;
const DIVI_CHILD_SOURCES_TITRE    = 'Sources';

/* -------------------------------------------------------------------------
 * Type de contenu
 * ---------------------------------------------------------------------- */

function divi_child_sources_register() {
	register_post_type(
		DIVI_CHILD_SOURCE_TYPE,
		array(
			'labels'          => array(
				'name'               => 'Sources',
				'singular_name'      => 'Source',
				'menu_name'          => 'Sources',
				'add_new'            => 'Ajouter une source',
				'add_new_item'       => 'Ajouter une source',
				'edit_item'          => 'Modifier la source',
				'new_item'           => 'Nouvelle source',
				'search_items'       => 'Rechercher une source',
				'not_found'          => 'Aucune source',
				'not_found_in_trash' => 'Aucune source dans la corbeille',
				'all_items'          => 'Toutes les sources',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 7,
			'menu_icon'       => 'dashicons-book-alt',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'divi_child_sources_register' );

function divi_child_sources_title_placeholder( $text, $post ) {
	return DIVI_CHILD_SOURCE_TYPE === $post->post_type ? 'Texte affiché : auteur, titre, éditeur, année' : $text;
}
add_filter( 'enter_title_here', 'divi_child_sources_title_placeholder', 10, 2 );

/* -------------------------------------------------------------------------
 * Données
 * ---------------------------------------------------------------------- */

function divi_child_sources_url( $source_id ) {
	return (string) get_post_meta( $source_id, DIVI_CHILD_META_SOURCE_URL, true );
}

/** Identifiants des sources choisies dans un article, dans l'ordre. */
function divi_child_sources_ids( $post_id ) {
	$ids = get_post_meta( $post_id, DIVI_CHILD_META_SOURCES, true );
	return is_array( $ids ) ? array_values( array_filter( array_map( 'absint', $ids ) ) ) : array();
}

/** Sources publiées d'un article, dans l'ordre. */
function divi_child_sources_published( $post_id ) {
	$sources = array();
	foreach ( divi_child_sources_ids( $post_id ) as $id ) {
		$source = get_post( $id );
		if ( $source && DIVI_CHILD_SOURCE_TYPE === $source->post_type && 'publish' === $source->post_status ) {
			$sources[] = $source;
		}
	}
	return $sources;
}

/** Articles qui citent une source. */
function divi_child_sources_articles( $source_id, $status = 'any' ) {
	return get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => $status,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'meta_key'       => DIVI_CHILD_META_SOURCE_LIEN, // phpcs:ignore WordPress.DB.SlowDBQuery -- administration et petit volume.
			'meta_value'     => (string) $source_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

function divi_child_sources_html( $post_id ) {
	$sources = divi_child_sources_published( $post_id );
	if ( ! $sources ) {
		return '';
	}
	$html = '<section class="mcav-sources"><h2>' . esc_html( DIVI_CHILD_SOURCES_TITRE ) . '</h2><ol>';
	foreach ( $sources as $source ) {
		$texte = esc_html( trim( $source->post_title ) );
		$url   = divi_child_sources_url( $source->ID );
		$html .= '<li>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . $texte . '</a>' : $texte ) . '</li>';
	}
	return $html . '</ol></section>';
}

/** Ajoute les sources à la fin d'un contenu. */
function divi_child_sources_insert( $content, $post_id ) {
	return $content . divi_child_sources_html( $post_id );
}

function divi_child_sources_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Une seule liste, même si the_content passe plusieurs fois.
	if ( false !== strpos( $content, 'class="mcav-sources"' ) ) {
		return $content;
	}
	$post = get_post();
	return $post ? divi_child_sources_insert( $content, $post->ID ) : $content;
}
// Avant la FAQ (15), qui se place avant le H2 « Sources », et avant le sommaire (20).
add_filter( 'the_content', 'divi_child_sources_filter', 14 );

/* -------------------------------------------------------------------------
 * Administration : article
 * ---------------------------------------------------------------------- */

function divi_child_sources_add_meta_boxes() {
	add_meta_box( 'mcav_sources', DIVI_CHILD_SOURCES_TITRE, 'divi_child_sources_article_box', 'post', 'normal', 'high' );
	add_meta_box( 'mcav_source_url', 'Lien et articles', 'divi_child_sources_source_box', DIVI_CHILD_SOURCE_TYPE, 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'divi_child_sources_add_meta_boxes' );

function divi_child_sources_article_box( $post ) {
	wp_nonce_field( 'mcav_sources', 'mcav_sources_nonce' );
	$choisies = divi_child_sources_ids( $post->ID );
	$toutes   = get_posts(
		array(
			'post_type'      => DIVI_CHILD_SOURCE_TYPE,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	?>
	<p class="description">
		Liste numérotée à la fin de l'article, après la FAQ.
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . DIVI_CHILD_SOURCE_TYPE ) ); ?>" target="_blank">Créer une source</a>
		(puis recharger cette page).
	</p>
	<?php
	if ( preg_match( '#<h2[^>]*>\s*(?:<[^>]+>\s*)*sources?\b#iu', $post->post_content ) ) {
		echo '<p style="color:#b32d2e"><strong>Le texte de l\'article contient encore des sources écrites à la main : les supprimer pour ne pas les afficher deux fois.</strong></p>';
	}
	for ( $i = 0; $i < DIVI_CHILD_SOURCES_SLOTS; $i++ ) {
		$actuel = $choisies[ $i ] ?? 0;
		?>
		<p style="margin:6px 0">
			<label for="mcav_sources_<?php echo (int) $i; ?>" class="screen-reader-text">Source <?php echo (int) ( $i + 1 ); ?></label>
			<?php echo (int) ( $i + 1 ); ?>.
			<select name="mcav_sources[]" id="mcav_sources_<?php echo (int) $i; ?>" style="max-width:90%">
				<option value="0">—</option>
				<?php foreach ( $toutes as $source ) : ?>
					<option value="<?php echo (int) $source->ID; ?>"<?php selected( $actuel, $source->ID ); ?>>
						<?php
						echo esc_html(
							wp_html_excerpt( $source->post_title, 100, '…' )
							. ( divi_child_sources_url( $source->ID ) ? '' : ' (sans lien)' )
							. ( 'publish' === $source->post_status ? '' : ' (brouillon)' )
						);
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}

function divi_child_sources_save_article( $post_id ) {
	if ( ! isset( $_POST['mcav_sources_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_sources_nonce'] ) ), 'mcav_sources' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| wp_is_post_revision( $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['mcav_sources'] ?? array() ) ) ) ) );
	$ids = array_values(
		array_filter(
			$ids,
			function ( $id ) {
				return DIVI_CHILD_SOURCE_TYPE === get_post_type( $id );
			}
		)
	);

	delete_post_meta( $post_id, DIVI_CHILD_META_SOURCE_LIEN );
	if ( $ids ) {
		update_post_meta( $post_id, DIVI_CHILD_META_SOURCES, $ids );
		foreach ( $ids as $id ) {
			add_post_meta( $post_id, DIVI_CHILD_META_SOURCE_LIEN, (string) $id );
		}
	} else {
		delete_post_meta( $post_id, DIVI_CHILD_META_SOURCES );
	}
}
add_action( 'save_post_post', 'divi_child_sources_save_article' );

/* -------------------------------------------------------------------------
 * Administration : source
 * ---------------------------------------------------------------------- */

function divi_child_sources_source_box( $source ) {
	wp_nonce_field( 'mcav_source_url', 'mcav_source_url_nonce' );
	$articles = divi_child_sources_articles( $source->ID );
	?>
	<p>
		<label for="mcav_source_url"><strong>Lien (facultatif)</strong></label><br>
		<input type="url" name="mcav_source_url" id="mcav_source_url" style="width:100%"
			value="<?php echo esc_attr( divi_child_sources_url( $source->ID ) ); ?>" placeholder="https://…">
		<span class="description">Le texte (titre ci-dessus) devient un lien vers cette adresse.</span>
	</p>
	<?php if ( $articles ) : ?>
		<p><strong>Citée dans :</strong></p>
		<ul style="list-style:disc;margin-left:1.4em">
			<?php foreach ( $articles as $id ) : ?>
				<li>
					<a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
					<?php echo 'publish' === get_post_status( $id ) ? '' : ' (non publié)'; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p>Aucun article ne cite encore cette source. Elle se choisit dans l'encadré « <?php echo esc_html( DIVI_CHILD_SOURCES_TITRE ); ?> » des articles.</p>
		<?php
	endif;
}

function divi_child_sources_save_source( $source_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $source_id ) ) {
		return;
	}
	if ( isset( $_POST['mcav_source_url_nonce'], $_POST['mcav_source_url'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_source_url_nonce'] ) ), 'mcav_source_url' )
		&& current_user_can( 'edit_post', $source_id ) ) {
		$url = esc_url_raw( trim( wp_unslash( $_POST['mcav_source_url'] ) ), array( 'http', 'https' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- nettoyé par esc_url_raw.
		if ( $url ) {
			update_post_meta( $source_id, DIVI_CHILD_META_SOURCE_URL, $url );
		} else {
			delete_post_meta( $source_id, DIVI_CHILD_META_SOURCE_URL );
		}
	}
	// Le texte ou le lien a pu changer : rafraîchir les articles en cache (WP Rocket).
	if ( function_exists( 'rocket_clean_post' ) ) {
		foreach ( divi_child_sources_articles( $source_id, 'publish' ) as $article ) {
			rocket_clean_post( $article );
		}
	}
}
add_action( 'save_post_' . DIVI_CHILD_SOURCE_TYPE, 'divi_child_sources_save_source' );

/** Colonnes de la liste des sources : lien et nombre d'articles. */
function divi_child_sources_columns( $columns ) {
	$date = $columns['date'] ?? null;
	unset( $columns['date'] );
	$columns['mcav_source_url']      = 'Lien';
	$columns['mcav_source_articles'] = 'Articles';
	if ( $date ) {
		$columns['date'] = $date;
	}
	return $columns;
}
add_filter( 'manage_' . DIVI_CHILD_SOURCE_TYPE . '_posts_columns', 'divi_child_sources_columns' );

function divi_child_sources_column( $column, $source_id ) {
	if ( 'mcav_source_url' === $column ) {
		$url = divi_child_sources_url( $source_id );
		echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $url, PHP_URL_HOST ) ) . '</a>' : '—';
	} elseif ( 'mcav_source_articles' === $column ) {
		echo (int) count( divi_child_sources_articles( $source_id ) );
	}
}
add_action( 'manage_' . DIVI_CHILD_SOURCE_TYPE . '_posts_custom_column', 'divi_child_sources_column', 10, 2 );
