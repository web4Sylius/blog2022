<?php
/**
 * Thème enfant Divi – Ma Cave à Vin.
 *
 * Personnalisations déplacées depuis Divi/functions.php.
 */

/**
 * À l'activation, reprend les réglages du Customizer de Divi
 * (emplacement des menus, CSS additionnel, widgets), que WordPress
 * stocke séparément pour chaque thème.
 */
function divi_child_copy_parent_theme_mods( $old_name, $old_theme = false ) {
	$child_mods = get_option( 'theme_mods_' . get_stylesheet() );
	$child_mods = is_array( $child_mods ) ? $child_mods : array();

	// Une seule fois : ne pas écraser des réglages faits ensuite sur le thème enfant.
	if ( ! empty( $child_mods['divi_child_parent_mods_imported'] ) ) {
		return;
	}

	$parent_mods = get_option( 'theme_mods_' . get_template() );
	$parent_mods = is_array( $parent_mods ) ? $parent_mods : array();

	// WordPress a pu créer quelques réglages à l'activation (menus) : on complète sans les écraser.
	$merged = array_merge( $parent_mods, $child_mods );
	$merged['divi_child_parent_mods_imported'] = 1;

	update_option( 'theme_mods_' . get_stylesheet(), $merged );
}
add_action( 'after_switch_theme', 'divi_child_copy_parent_theme_mods', 10, 2 );

function divi_filtergrid_orderby_views( $query ) {
    if ( $query->is_main_query() && is_post_type_archive( 'post' ) ) {
        $query->set( 'orderby', 'post_views' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'divi_filtergrid_orderby_views' );

function update_seo_meta_data() {
    $args = array(
        'post_type' => 'post', // Remplacez par le type de contenu correspondant à vos articles si nécessaire
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        // Mise à jour du fil d'Ariane
        $breadcrumb = get_the_title($post->ID); // Utilise le titre de l'article comme fil d'Ariane
        update_post_meta($post->ID, '_yoast_wpseo_breadcrumb', $breadcrumb);

        // Mise à jour de l'auteur
        $author_id = get_post_field('post_author', $post->ID);
        update_post_meta($post->ID, '_yoast_wpseo_primary_author', $author_id);
    }
}

add_action('admin_init', 'update_seo_meta_data');

// Fonction pour récupérer le nombre de vues d'un article
function get_post_views($post_id) {
    $views = pvc_get_post_views($post_id);
    return intval($views);
}

// Fonction de comparaison pour le tri des articles
function compare_post_views($a, $b) {
    $views_a = get_post_views($a->ID);
    $views_b = get_post_views($b->ID);

    if ($views_a == $views_b) {
        return 0;
    }

    return ($views_a > $views_b) ? -1 : 1;
}

// Modification de la requête pour trier les articles par nombre de vues
function sort_posts_by_views($query) {
    if ($query->is_main_query() && !is_admin() && is_post_type_archive('post')) {
        $query->set('meta_key', 'pvc_views');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'DESC');
    }
}
add_action('pre_get_posts', 'sort_posts_by_views');

function custom_image_alt_tag($attr, $attachment = null) {
    if (is_singular('post') && isset($attr['alt']) && empty($attr['alt'])) {
        global $post;
        $attr['alt'] = $post->post_title;
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'custom_image_alt_tag', 10, 2);

function wp_modify_image_alt_tags( $content ) {
    global $post;
    preg_match_all('/<img (.*?)\/>/', $content, $matches);
    
    if ( isset($matches[1]) && !empty($matches[1]) ) {
        foreach ($matches[1] as $image) {
            if ( strpos($image, 'alt=') === false ) {
                $new_image = str_replace('<img', '<img alt="' . esc_attr( get_the_title($post->ID) ) . '"', $image);
                $content = str_replace($image, $new_image, $content);
            }
        }
    }
    
    return $content;
}
add_filter('the_content', 'wp_modify_image_alt_tags');

function remove_divi_sidebar() {
    if ( is_singular( 'post' ) ) { // Supprime la sidebar uniquement sur les pages de type article
        remove_action( 'et_before_main_content', 'et_divi_show_sidebar' );
        remove_action( 'et_after_main_content', 'et_divi_show_sidebar' );
    }
}
add_action( 'wp', 'remove_divi_sidebar' );


add_action('rest_api_init', 'register_rest_images' );
function register_rest_images(){
    register_rest_field( array('post'),
        'featured_image_url',
        array(
            'get_callback'    => 'get_rest_featured_image',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function get_rest_featured_image( $object, $field_name, $request ) {
    if( $object['featured_media'] ){
        $img = wp_get_attachment_image_src( $object['featured_media'], 'full' );
        return $img[0];
    }
    return false;
}
