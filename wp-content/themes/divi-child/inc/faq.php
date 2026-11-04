<?php
/**
 * FAQ des articles.
 *
 * - Type de contenu « FAQ » : titre = question, texte = réponse.
 *   Pas de page publique, seulement l'administration.
 * - Dans l'article, on choisit et on ordonne 3 à 5 questions
 *   (encadré « Questions fréquentes »).
 * - Affichage : H2 « Questions fréquentes » + un <details> par question,
 *   avant le H2 « Sources » s'il existe, sinon à la fin. Le H2 entre dans le sommaire.
 * - Données structurées FAQPage, avec exactement les mêmes textes.
 *   Google demande qu'une question ne soit balisée qu'à un seul endroit du site :
 *   chaque FAQ a un article de référence, le seul où elle est balisée
 *   (par défaut, le premier article qui l'a choisie).
 */

const DIVI_CHILD_FAQ_TYPE           = 'mcav_faq';
const DIVI_CHILD_META_FAQ           = '_mcav_faq';           // Article : identifiants des FAQ, dans l'ordre.
const DIVI_CHILD_META_FAQ_LIEN      = '_mcav_faq_lien';      // Article : une ligne par FAQ (pour retrouver les articles d'une FAQ).
const DIVI_CHILD_META_FAQ_REFERENCE = '_mcav_faq_reference'; // FAQ : article où elle est balisée (0 = automatique).
const DIVI_CHILD_FAQ_SLOTS          = 5;
const DIVI_CHILD_FAQ_MIN            = 3;
const DIVI_CHILD_FAQ_MIN_WORDS      = 40;
const DIVI_CHILD_FAQ_MAX_WORDS      = 80;
const DIVI_CHILD_FAQ_TITRE          = 'Questions fréquentes';

/* -------------------------------------------------------------------------
 * Type de contenu
 * ---------------------------------------------------------------------- */

function divi_child_faq_register() {
	register_post_type(
		DIVI_CHILD_FAQ_TYPE,
		array(
			'labels'          => array(
				'name'               => 'FAQ',
				'singular_name'      => 'Question',
				'menu_name'          => 'FAQ',
				'add_new'            => 'Ajouter une question',
				'add_new_item'       => 'Ajouter une question',
				'edit_item'          => 'Modifier la question',
				'new_item'           => 'Nouvelle question',
				'search_items'       => 'Rechercher une question',
				'not_found'          => 'Aucune question',
				'not_found_in_trash' => 'Aucune question dans la corbeille',
				'all_items'          => 'Toutes les questions',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 6,
			'menu_icon'       => 'dashicons-editor-help',
			'supports'        => array( 'title', 'editor', 'revisions' ),
			'capability_type' => 'post',
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'divi_child_faq_register' );

function divi_child_faq_title_placeholder( $text, $post ) {
	return DIVI_CHILD_FAQ_TYPE === $post->post_type ? 'La question, telle qu\'on la pose (PAA, service client)' : $text;
}
add_filter( 'enter_title_here', 'divi_child_faq_title_placeholder', 10, 2 );

/* -------------------------------------------------------------------------
 * Données
 * ---------------------------------------------------------------------- */

/** Balises gardées dans les réponses (celles que Google accepte dans FAQPage). */
function divi_child_faq_allowed_html() {
	return array(
		'a'      => array( 'href' => true ),
		'p'      => array(),
		'br'     => array(),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
	);
}

/** Question (texte brut) : la même à l'écran et dans FAQPage. */
function divi_child_faq_question( $faq ) {
	return trim( preg_replace( '/\s+/u', ' ', $faq->post_title ) );
}

/** Réponse (HTML simple) : la même à l'écran et dans FAQPage. */
function divi_child_faq_reponse( $faq ) {
	return trim( wp_kses( wpautop( $faq->post_content ), divi_child_faq_allowed_html() ) );
}

function divi_child_faq_mots( $faq ) {
	$texte = html_entity_decode( wp_strip_all_tags( divi_child_faq_reponse( $faq ) ), ENT_QUOTES, 'UTF-8' );
	return count( preg_split( '/\s+/u', $texte, -1, PREG_SPLIT_NO_EMPTY ) );
}

/** Identifiants des FAQ choisies dans un article, dans l'ordre. */
function divi_child_faq_ids( $post_id ) {
	$ids = get_post_meta( $post_id, DIVI_CHILD_META_FAQ, true );
	return is_array( $ids ) ? array_values( array_filter( array_map( 'absint', $ids ) ) ) : array();
}

/** FAQ publiées d'un article, dans l'ordre. */
function divi_child_faq_published( $post_id ) {
	$faqs = array();
	foreach ( divi_child_faq_ids( $post_id ) as $id ) {
		$faq = get_post( $id );
		if ( $faq && DIVI_CHILD_FAQ_TYPE === $faq->post_type && 'publish' === $faq->post_status ) {
			$faqs[] = $faq;
		}
	}
	return $faqs;
}

/** Articles qui utilisent une FAQ (les plus anciens d'abord). */
function divi_child_faq_articles( $faq_id, $status = 'any' ) {
	return get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => $status,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'meta_key'       => DIVI_CHILD_META_FAQ_LIEN, // phpcs:ignore WordPress.DB.SlowDBQuery -- administration et petit volume.
			'meta_value'     => (string) $faq_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Article où la FAQ est balisée : l'article de référence s'il est publié et
 * utilise toujours la FAQ, sinon le plus ancien article publié qui l'utilise.
 */
function divi_child_faq_reference( $faq_id ) {
	$publies = divi_child_faq_articles( $faq_id, 'publish' );
	$ref     = (int) get_post_meta( $faq_id, DIVI_CHILD_META_FAQ_REFERENCE, true );
	if ( $ref && in_array( $ref, $publies, true ) ) {
		return $ref;
	}
	return $publies ? (int) $publies[0] : 0;
}

/** Vide le cache WP Rocket des articles qui utilisent une FAQ. */
function divi_child_faq_purge( $faq_id, $sauf = 0 ) {
	if ( ! function_exists( 'rocket_clean_post' ) ) {
		return;
	}
	foreach ( divi_child_faq_articles( $faq_id, 'publish' ) as $article ) {
		if ( (int) $article !== (int) $sauf ) {
			rocket_clean_post( $article );
		}
	}
}

/* -------------------------------------------------------------------------
 * Affichage
 * ---------------------------------------------------------------------- */

function divi_child_faq_html( $post_id ) {
	$faqs = divi_child_faq_published( $post_id );
	if ( ! $faqs ) {
		return '';
	}
	$html = '<section class="mcav-faq"><h2>' . esc_html( DIVI_CHILD_FAQ_TITRE ) . '</h2>';
	foreach ( $faqs as $faq ) {
		$html .= '<details><summary>' . esc_html( divi_child_faq_question( $faq ) ) . '</summary>'
			. '<div class="mcav-faq__reponse">' . divi_child_faq_reponse( $faq ) . '</div>' // Déjà filtrée par wp_kses.
			. '</details>';
	}
	return $html . '</section>';
}

/** Ajoute la FAQ à un contenu : avant le H2 « Sources » (ou Références, Bibliographie), sinon à la fin. */
function divi_child_faq_insert( $content, $post_id ) {
	$html = divi_child_faq_html( $post_id );
	if ( '' === $html ) {
		return $content;
	}
	if ( preg_match_all( '#<h2(?:\s[^>]*)?>(.*?)</h2>#is', $content, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
		foreach ( $m as $h2 ) {
			$label = trim( wp_strip_all_tags( html_entity_decode( $h2[1][0], ENT_QUOTES, 'UTF-8' ) ) );
			if ( preg_match( '/^(sources?|r[ée]f[ée]rences|bibliographie)\b/iu', $label ) ) {
				return substr_replace( $content, $html, $h2[0][1], 0 );
			}
		}
	}
	return $content . $html;
}

/**
 * Contenu d'un article avec sa FAQ, pour la liste des H2 dans les encadrés
 * « Avis du conseiller » et « Encadré boutique » (mêmes positions qu'à l'affichage).
 */
function divi_child_faq_content_for_headings( $post ) {
	return divi_child_faq_insert( $post->post_content, $post->ID );
}

function divi_child_faq_filter( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	// Une seule FAQ, même si the_content passe plusieurs fois.
	if ( false !== strpos( $content, 'class="mcav-faq"' ) ) {
		return $content;
	}
	$post = get_post();
	return $post ? divi_child_faq_insert( $content, $post->ID ) : $content;
}
// Avant le sommaire (20) pour qu'il reprenne le H2 « Questions fréquentes ».
add_filter( 'the_content', 'divi_child_faq_filter', 15 );

function divi_child_faq_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$post_id  = get_queried_object_id();
	$entities = array();
	foreach ( divi_child_faq_published( $post_id ) as $faq ) {
		// Balisée seulement dans son article de référence.
		if ( divi_child_faq_reference( $faq->ID ) !== $post_id ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => divi_child_faq_question( $faq ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => divi_child_faq_reponse( $faq ),
			),
		);
	}
	if ( ! $entities ) {
		return;
	}
	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
	echo '<script type="application/ld+json">'
		. wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG )
		. "</script>\n";
}
add_action( 'wp_head', 'divi_child_faq_schema' );

/* -------------------------------------------------------------------------
 * Administration : article
 * ---------------------------------------------------------------------- */

function divi_child_faq_add_meta_boxes() {
	add_meta_box( 'mcav_faq', DIVI_CHILD_FAQ_TITRE, 'divi_child_faq_article_box', 'post', 'normal', 'high' );
	add_meta_box( 'mcav_faq_reference', 'Articles qui utilisent cette question', 'divi_child_faq_reference_box', DIVI_CHILD_FAQ_TYPE, 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'divi_child_faq_add_meta_boxes' );

function divi_child_faq_article_box( $post ) {
	wp_nonce_field( 'mcav_faq', 'mcav_faq_nonce' );
	$choisies = divi_child_faq_ids( $post->ID );
	$toutes   = get_posts(
		array(
			'post_type'      => DIVI_CHILD_FAQ_TYPE,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	?>
	<p class="description">
		3 à 5 vraies questions (PAA, service client), affichées avant « Sources » ou à la fin de l'article.
		<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . DIVI_CHILD_FAQ_TYPE ) ); ?>" target="_blank">Créer une question</a>
		(puis recharger cette page).
	</p>
	<?php
	if ( preg_match( '#<h2[^>]*>\s*(?:<[^>]+>\s*)*(questions\s+fr[ée]quentes|faq)\b#iu', $post->post_content ) ) {
		echo '<p style="color:#b32d2e"><strong>Le texte de l\'article contient encore une FAQ écrite à la main : la supprimer pour ne pas l\'afficher deux fois.</strong></p>';
	}
	for ( $i = 0; $i < DIVI_CHILD_FAQ_SLOTS; $i++ ) {
		$actuel = $choisies[ $i ] ?? 0;
		?>
		<p style="margin:6px 0">
			<label for="mcav_faq_<?php echo (int) $i; ?>" class="screen-reader-text">Question <?php echo (int) ( $i + 1 ); ?></label>
			<?php echo (int) ( $i + 1 ); ?>.
			<select name="mcav_faq[]" id="mcav_faq_<?php echo (int) $i; ?>" style="max-width:90%">
				<option value="0">—</option>
				<?php foreach ( $toutes as $faq ) : ?>
					<option value="<?php echo (int) $faq->ID; ?>"<?php selected( $actuel, $faq->ID ); ?>>
						<?php echo esc_html( wp_html_excerpt( divi_child_faq_question( $faq ), 100, '…' ) . ( 'publish' === $faq->post_status ? '' : ' (brouillon)' ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	if ( ! $choisies ) {
		return;
	}
	?>
	<hr>
	<ol style="margin-left:1.4em">
		<?php
		foreach ( $choisies as $id ) :
			$faq = get_post( $id );
			if ( ! $faq || DIVI_CHILD_FAQ_TYPE !== $faq->post_type ) {
				continue;
			}
			$mots = divi_child_faq_mots( $faq );
			$ref  = divi_child_faq_reference( $faq->ID );
			?>
			<li>
				<a href="<?php echo esc_url( get_edit_post_link( $faq->ID ) ); ?>"><?php echo esc_html( divi_child_faq_question( $faq ) ); ?></a> —
				<?php
				if ( 'publish' !== $faq->post_status ) {
					echo '<span style="color:#b32d2e">non publiée, pas affichée</span>';
				} elseif ( $mots < DIVI_CHILD_FAQ_MIN_WORDS || $mots > DIVI_CHILD_FAQ_MAX_WORDS ) {
					printf( '<span style="color:#996800">%d mots (viser %d à %d)</span>', (int) $mots, DIVI_CHILD_FAQ_MIN_WORDS, DIVI_CHILD_FAQ_MAX_WORDS );
				} else {
					printf( '<span style="color:#00a32a">%d mots ✓</span>', (int) $mots );
				}
				if ( $ref === $post->ID ) {
					echo ' — balisée ici';
				} elseif ( $ref ) {
					printf( ' — balisée dans <a href="%s">%s</a>', esc_url( get_edit_post_link( $ref ) ), esc_html( wp_html_excerpt( get_the_title( $ref ), 60, '…' ) ) );
				}
				?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
	if ( count( $choisies ) < DIVI_CHILD_FAQ_MIN ) {
		printf( '<p style="color:#b32d2e">%d question(s) : en choisir au moins %d.</p>', count( $choisies ), DIVI_CHILD_FAQ_MIN );
	}
	echo '<p class="description">Contrôles mis à jour après l\'enregistrement de l\'article.</p>';
}

function divi_child_faq_save_article( $post_id ) {
	if ( ! isset( $_POST['mcav_faq_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_faq_nonce'] ) ), 'mcav_faq' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| wp_is_post_revision( $post_id )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$avant = divi_child_faq_ids( $post_id );
	$ids   = array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['mcav_faq'] ?? array() ) ) ) ) );
	$ids   = array_values(
		array_filter(
			$ids,
			function ( $id ) {
				return DIVI_CHILD_FAQ_TYPE === get_post_type( $id );
			}
		)
	);

	delete_post_meta( $post_id, DIVI_CHILD_META_FAQ_LIEN );
	if ( $ids ) {
		update_post_meta( $post_id, DIVI_CHILD_META_FAQ, $ids );
		foreach ( $ids as $id ) {
			add_post_meta( $post_id, DIVI_CHILD_META_FAQ_LIEN, (string) $id );
		}
	} else {
		delete_post_meta( $post_id, DIVI_CHILD_META_FAQ );
	}

	// Première fois qu'une question est choisie : cet article devient sa référence.
	foreach ( array_diff( $ids, $avant ) as $id ) {
		if ( ! (int) get_post_meta( $id, DIVI_CHILD_META_FAQ_REFERENCE, true ) ) {
			update_post_meta( $id, DIVI_CHILD_META_FAQ_REFERENCE, $post_id );
			divi_child_faq_purge( $id, $post_id );
		}
	}
	// Question retirée de son article de référence : un autre article prend le relais.
	foreach ( array_diff( $avant, $ids ) as $id ) {
		if ( (int) get_post_meta( $id, DIVI_CHILD_META_FAQ_REFERENCE, true ) === $post_id ) {
			delete_post_meta( $id, DIVI_CHILD_META_FAQ_REFERENCE );
			divi_child_faq_purge( $id, $post_id );
		}
	}
}
add_action( 'save_post_post', 'divi_child_faq_save_article' );

/* -------------------------------------------------------------------------
 * Administration : question
 * ---------------------------------------------------------------------- */

function divi_child_faq_reference_box( $faq ) {
	wp_nonce_field( 'mcav_faq_reference', 'mcav_faq_reference_nonce' );
	$articles = divi_child_faq_articles( $faq->ID );
	$stockee  = (int) get_post_meta( $faq->ID, DIVI_CHILD_META_FAQ_REFERENCE, true );
	$effectif = divi_child_faq_reference( $faq->ID );
	$mots     = divi_child_faq_mots( $faq );
	?>
	<p>
		Réponse : <strong><?php echo (int) $mots; ?> mots</strong>
		(viser <?php echo (int) DIVI_CHILD_FAQ_MIN_WORDS; ?> à <?php echo (int) DIVI_CHILD_FAQ_MAX_WORDS; ?>).
		Liens, gras, italique et listes sont repris ; le reste de la mise en forme est retiré.
	</p>
	<?php
	if ( ! $articles ) {
		echo '<p>Aucun article n\'utilise encore cette question. Elle se choisit dans l\'encadré « ' . esc_html( DIVI_CHILD_FAQ_TITRE ) . ' » des articles.</p>';
		return;
	}
	?>
	<p class="description">
		La question s'affiche dans tous ces articles, mais Google demande de ne la baliser (données structurées FAQPage)
		que dans un seul : choisir l'article le plus pertinent.
	</p>
	<fieldset>
		<?php foreach ( $articles as $id ) : ?>
			<label style="display:block;margin:4px 0">
				<input type="radio" name="mcav_faq_reference" value="<?php echo (int) $id; ?>"<?php checked( $stockee, $id ); ?>>
				<a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
				<?php echo 'publish' === get_post_status( $id ) ? '' : ' (non publié)'; ?>
				<?php echo $effectif === (int) $id ? ' — <strong>balisée ici</strong>' : ''; ?>
			</label>
		<?php endforeach; ?>
		<label style="display:block;margin:4px 0">
			<input type="radio" name="mcav_faq_reference" value="0"<?php checked( in_array( $stockee, $articles, true ) ? $stockee : 0, 0 ); ?>>
			Automatique (le plus ancien article publié)
		</label>
	</fieldset>
	<?php
}

function divi_child_faq_save_question( $faq_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $faq_id ) ) {
		return;
	}
	if ( isset( $_POST['mcav_faq_reference_nonce'], $_POST['mcav_faq_reference'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mcav_faq_reference_nonce'] ) ), 'mcav_faq_reference' )
		&& current_user_can( 'edit_post', $faq_id ) ) {
		$ref = absint( $_POST['mcav_faq_reference'] );
		if ( $ref ) {
			update_post_meta( $faq_id, DIVI_CHILD_META_FAQ_REFERENCE, $ref );
		} else {
			delete_post_meta( $faq_id, DIVI_CHILD_META_FAQ_REFERENCE );
		}
	}
	// La question ou la réponse a pu changer : rafraîchir les articles en cache.
	divi_child_faq_purge( $faq_id );
}
add_action( 'save_post_' . DIVI_CHILD_FAQ_TYPE, 'divi_child_faq_save_question' );

/** Colonnes de la liste des questions : nombre de mots et d'articles. */
function divi_child_faq_columns( $columns ) {
	$date = $columns['date'] ?? null;
	unset( $columns['date'] );
	$columns['mcav_faq_mots']     = 'Mots';
	$columns['mcav_faq_articles'] = 'Articles';
	if ( $date ) {
		$columns['date'] = $date;
	}
	return $columns;
}
add_filter( 'manage_' . DIVI_CHILD_FAQ_TYPE . '_posts_columns', 'divi_child_faq_columns' );

function divi_child_faq_column( $column, $faq_id ) {
	if ( 'mcav_faq_mots' === $column ) {
		$mots = divi_child_faq_mots( get_post( $faq_id ) );
		$ok   = $mots >= DIVI_CHILD_FAQ_MIN_WORDS && $mots <= DIVI_CHILD_FAQ_MAX_WORDS;
		printf( '<span style="color:%s">%d</span>', $ok ? '#00a32a' : '#996800', (int) $mots );
	} elseif ( 'mcav_faq_articles' === $column ) {
		echo (int) count( divi_child_faq_articles( $faq_id ) );
	}
}
add_action( 'manage_' . DIVI_CHILD_FAQ_TYPE . '_posts_custom_column', 'divi_child_faq_column', 10, 2 );
