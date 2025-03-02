<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */
/* 
THREEK ==  360Kompakt
*/
define( 'THREEK_THEME_URL', get_stylesheet_directory_uri() );
define( 'THREEK_THEME_PATH', get_stylesheet_directory() );
define( 'THREEK_VERSION', '1.0.0' );

function threek_enqueue_child_theme_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'threek-style', THREEK_THEME_URL . '/build/main.css', ['parent-style'], filemtime( THREEK_THEME_PATH . '/build/main.css' ) );
    wp_enqueue_script( 'threek-slider', THREEK_THEME_URL . '/build/slider.js', [], filemtime( THREEK_THEME_PATH . '/build/slider.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'threek_enqueue_child_theme_styles' );

function backend_assets() {
	wp_enqueue_script( 
        'threek-be-js', 
        THREEK_THEME_URL . '/build/backend.js', 
        ['wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-api', 'wp-polyfill'], 
        filemtime( THREEK_THEME_PATH . '/build/backend.js' ), 
        true 
    );
}
add_action('admin_enqueue_scripts', 'backend_assets');

add_image_size( 'widget-slider-770', 770, 450, true );
add_image_size( 'widget-slider-450', 450, 263, true );

function add_custom_sizes_to_gutenberg( $sizes ) {
  return array_merge( $sizes, [
    'widget-slider-770' => __('Slider 770', 'threek'),
    'widget-slider-450' => __('Slider 450', 'threek'),
  ] );
}
add_filter( 'image_size_names_choose', 'add_custom_sizes_to_gutenberg' );


// includes
require_once THREEK_THEME_PATH . '/classes/CheckedBy.php';
add_action( 'init', function() {
    new \Threek\CheckedBy;
} );

require_once THREEK_THEME_PATH . '/shortcodes.php';


// Change 404 Page Title
add_filter( 'generate_404_title','generate_custom_404_title' );
function generate_custom_404_title()
{
      return __('<center>Nichts gefunden</center>', 'threek');
}


// Change 404 Page Text
add_filter( 'generate_404_text','generate_custom_404_text' );
function generate_custom_404_text()
{
      return __('<center>Haben Sie sich verirrt? Nutzen Sie unsere Suche oder klicken Sie auf einen unserer neuesten Beiträge.</center>', 'threek');
}


// Change 404 Page Search Form
function wpdocs_my_search_form( $form ) {
	$form = '<form role="search" method="get" action="/" class="wp-block-search__button-inside wp-block-search__text-button wp-block-search"><label for="wp-block-search__input-1" class="wp-block-search__label screen-reader-text">Suchen</label><div class="wp-block-search__inside-wrapper " ><input type="search" id="wp-block-search__input-1" class="wp-block-search__input wp-block-search__input " name="s" value="" placeholder="Suchen..."  required /><button type="submit" class="wp-block-search__button wp-element-button">Suchen</button></div></form>';

	return $form;
}
add_filter( 'get_search_form', 'wpdocs_my_search_form' );


// Author Box
function show_author_box(){ 

    global $post;  
    $author_id = get_post_field('post_author' , $post->ID);
    
    // Check if is not 404 Page
    if(!is_404()){
    ?>
<div class="author-box">
    <div class="author-box-avatar">
        <img alt=<?php _e("Autorenfoto", "threek"); ?> title=<?php _e("Autorenfoto", "threek"); ?>
            src=<?php echo get_avatar_url($author_id); ?> />
    </div>
    <div class="author-box-meta">
        <div class="author-box_name"><?php echo '<span>'. get_the_author() . '</span>'; ?></div>
        <div class="author-box_bio">
            <?php echo get_the_author_meta("description", $author_id); ?>
        </div>
    </div>
    </div>
    <?php 
    }
}

add_action('generate_after_content', 'show_author_box', 10);

// Headline on home page 
add_action( 'generate_before_main_content', function() {
	if ( is_front_page() && is_home() ) {
	?>
    <div class="home-headline">
        <div class="wp-block-group__inner-container">
            <h2><?php _e('Aktuelle Beiträge', 'threek'); ?></h2>
        </div>
    </div>
    <?php
	}
} );

// Featured posts on home page
add_action( 'generate_after_header', function() {
    if ( is_front_page() && is_home() ) {

        $args = array(
            'cat'      => '224',
            'posts_per_page' => '3'
        );
        
        $featuredPosts = new WP_Query($args);

        ?> <section class="posts-list featured"> <?php

        if($featuredPosts->have_posts()){
        while ($featuredPosts->have_posts()) : $featuredPosts->the_post();
            get_template_part('template-parts/custom-post-loop');
        endwhile;
        }

        ?>
    </section> <?php
    }
});


// Recommended posts on post single
add_action( 'generate_after_content', function() {

    global $post;
    $categories = get_the_category();
    $category_id = get_cat_ID($categories[0]->name);

    $args = array(
        'cat'      => $category_id,
        'posts_per_page' => '3'
    );

    $featuredPosts = new WP_Query($args);

    ?>

    <h3 class="recommended-headline">
        <?php _e('Weitere Beiträge dieser Kategorie', 'threek'); ?>
    </h3>

    <section class="posts-list recommended">
        <?php

        if($featuredPosts->have_posts() && is_single()){

            while ($featuredPosts->have_posts()) : $featuredPosts->the_post();

                get_template_part('template-parts/custom-post-loop');
            
            endwhile;
            
        }
    ?>
    </section> <?php
 }, 20);
