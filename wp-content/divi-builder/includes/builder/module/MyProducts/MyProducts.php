<?php

    class MyProducts extends ET_Builder_Module {
    
        public $slug       = 'my_products';
        public $vb_support = 'on';

        public function init() {
            $this->name = esc_html__( 'My Products', 'my-products' );

            add_action( 'wp_enqueue_scripts', function() {
                wp_enqueue_style( 'my-products-css', './products.css' );
            });
        }

        public function get_fields() {
            return array(
                'heading'     => array(
                    'label'           => esc_html__( 'Heading', 'my-products' ),
                    'type'            => 'text',
                    'option_category' => 'basic_option',
                    'description'     => esc_html__( 'Gallery heading here.', 'my-products' ),
                    'toggle_slug'     => 'main_content',
                ),
                'content'     => array(
                    'label'           => esc_html__( 'Content', 'my-products' ),
                    'type'            => 'tiny_mce',
                    'option_category' => 'basic_option',
                    'description'     => esc_html__( 'Gallery footnote here.', 'my-products' ),
                    'toggle_slug'     => 'main_content',
                ),
            );
        }

        public function get_photos() {
            $query = new WP_Query( array( 'post_type' => 'products' ) );
            $posts = $query->posts;

            foreach($posts as $post) {

                if ( has_post_thumbnail( $post->ID ) ) {
                    $image = wp_get_attachment_image_src( 
                        get_post_thumbnail_id( $post->ID ), 
                        'medium'
                    );

                    $photo_image = $image[0];
                    $photo_title = get_the_title( $post->ID );

                    $photos .= sprintf( 
                        '<div><img src="%1$s"><p>%2$s</p></div>',
                        $photo_image,
                        $photo_title
        
                    );

                }

            }

            return $photos;
        }

        public function render( $unprocessed_props, $content, $render_slug ) {
            return sprintf(
                '<h1 class="my-products-heading">%1$s</h1>
                <div class="my-products-footnote">%2$s</div>
                <div class="my-products">%3$s</div>',
                esc_html( $this->props['heading'] ),
                $this->props['content'],
                $this->get_photos()
            );
        }
    }

new MyProducts;