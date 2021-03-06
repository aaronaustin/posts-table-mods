<?php
/*Plugin Name: Post Table Mods
Description: Modifies the main posts table.
Version: 1.0.8
License: GPLv2
GitHub Plugin URI: https://github.com/aaronaustin/posts-table-mods
*/

//This mod adds columns for acf fields start_date and end_date
//CUSTOMIZE COLUMNS IN LISTING
// Add the custom columns to the main post type:
add_filter( 'manage_posts_columns', 'set_custom_edit_posts_columns' );
function set_custom_edit_posts_columns($columns) {
    unset( $columns['author'] );
    unset( $columns['categories'] );
    unset( $columns['comments'] );
    unset( $columns['tags'] );
    unset( $columns['date'] );
	// $columns['start_date'] = __( 'Start Date', 'start_date' );
    $columns['main_category'] =__('Category', 'categories');
    $columns['display_location'] =__('Display', 'display');
	$columns['post_date'] =__('Date', 'date');
	

    return $columns;
}

// Add the data to the custom columns for the main post type:
    //TODO: if post is event, put event Date, otherwise - publish date
add_action( 'manage_posts_custom_column' , 'custom_posts_column', 10, 2 );
function custom_posts_column( $column, $post_id ) {
    switch ( $column ) {
		case 'post_date' :
            $event_date = date("Y-m-d", strtotime(get_post_meta( $post_id , 'event_start_datetime' , true )));
            $event_time = date("D • g:i a", strtotime(get_post_meta( $post_id , 'event_start_datetime' , true )));
            $pub_date = get_the_date("Y-m-d", $post_id);
            $pub_time = get_the_date("D • g:i a", $post_id);
            $category = get_the_category($post_id);            // var_dump($category[0]->slug);
			if ($category[0]->slug === 'event' || $category[0]->slug === 'worship'){
				// echo 'E: '.date("Y-m-d <\b\\r><\s\m\a\l\l\> D \&\m\i\d\d\o\\t\; g:i a <\/\s\m\a\l\l>", strtotime($event_date));
				echo '<small>Event Date</small><br>'.$event_date.'<br><small>'.$event_time.'</small>';
			}
			else {
                echo date($date);
				echo '<small>Pub Date</small><br>'.$pub_date.'<br><small>'.$pub_time.'</small>';
			}
            break;
        case 'main_category' :
            $arrs[] = get_the_category($post_id);
            $arrs[] = get_the_terms($post_id, 'media');
            $arrs[] = get_the_terms($post_id, 'slide');
            $all_categories = array();

            foreach($arrs as $arr) {
                if(is_array($arr)) {
                    $all_categories = array_merge($all_categories, $arr);
                }
            }
            foreach ($all_categories as $cat) {
                echo '<a class="btn-tag '. $cat->slug .'" href="edit.php?taxonomy='.$cat->taxonomy.'&amp;term='. $cat->slug .'">'.$cat->name.'</a>';
            }
            break;
        case 'display_location' :
            $display_categories = get_the_terms($post_id, 'display');
            foreach ($display_categories as $cat) {
                echo '<a class="btn-tag '. $cat->slug .'" href="edit.php?taxonomy='.$cat->taxonomy.'&amp;term='. $cat->slug .'">'.$cat->name.'</a>';
            }
            break;
    }
}

//make custom columns sortable
add_filter( 'manage_edit-post_sortable_columns', 'set_custom_post_sortable_columns' );
function set_custom_post_sortable_columns( $columns ) {
    $columns['post_date'] = 'post_date';
    return $columns;
}

//hacky sorting by using the path_custom field which saves differently based on category
//according to pub date or start date depending on category
add_action( 'pre_get_posts', 'post_custom_orderby' );
function post_custom_orderby( $query ) {
  if ( ! is_admin() )
    return;
  $orderby = $query->get( 'orderby');
  if ( 'post_date' == $orderby ) {
    // $query->set( 'meta_key', 'path_custom');
    $query->set('meta_query', array(
            'date_clause' => array(
                array('key' => 'path_custom',
                    'type' => 'DATE'
                )
            )
            ));
    $query->set( 'orderby', 'meta_value');
  }
}

//add category links to admin
add_action('admin_footer', 'add_category_links_to_posts_screen');
function add_category_links_to_posts_screen(){
    echo 'Test this out!';
}

//Add custom css for post table mods
add_action('admin_head', 'registerCustomAdminCss');
function registerCustomAdminCss(){
	$src = plugins_url('style.css',__FILE__ );
	$handle = "customAdminCss";
	wp_enqueue_style($handle, $src, array(), false, false);
}

function modify_read_more_link() {
 return '...<a class="more-link" href="/' . get_field('path') . '">Read More</a>';
}
add_filter( 'the_content_more_link', 'modify_read_more_link' );

function child_theme_setup() {
	// override parent theme's 'more' text for excerpts
	remove_filter( 'excerpt_more', 'twentyseventeen_excerpt_more' ); 
}
add_action( 'after_setup_theme', 'child_theme_setup' );


add_filter( 'excerpt_more', 'twentyseventeen_excerpt_more' );

function new_excerpt_more($more) {
 global $post;
 return '...<a class="moretag" 
 href="'. get_field('front_URL', 'option').'/'.get_field('path', $post->ID) . '">Read More</a>';

}
add_filter('excerpt_more', 'new_excerpt_more');

function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


?>
