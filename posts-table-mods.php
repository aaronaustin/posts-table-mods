<?php
/*Plugin Name: Post Table Mods
Description: Modifies the main posts table.
Version: 1.0.1
License: GPLv2
GitHub Plugin URI: https://github.com/aaronaustin/posts-table-mods
*/

//This mod adds columns for acf fields start_date and end_date
//CUSTOMIZE COLUMNS IN LISTING
// Add the custom columns to the main post type:
add_filter( 'manage_posts_columns', 'set_custom_edit_posts_columns' );
function set_custom_edit_posts_columns($columns) {
    unset( $columns['author'] );
    // unset( $columns['categories'] );
    unset( $columns['comments'] );
    unset( $columns['tags'] );
    unset( $columns['date'] );
	// $columns['start_date'] = __( 'Start Date', 'start_date' );
	$columns['date'] =__('Date', 'date');

    return $columns;
}

// Add the data to the custom columns for the main post type:
add_action( 'manage_posts_custom_column' , 'custom_posts_column', 10, 2 );
function custom_posts_column( $column, $post_id ) {
    switch ( $column ) {
		case 'start_date' :
			$date = get_post_meta( $post_id , 'start_date' , true );
			if ($date){
				echo date("Y-m-d <\b\\r><\s\m\a\l\l\> D \@ g:i a <\/\s\m\a\l\l>", strtotime($date));
			}
			else {
				echo '';
			}
            break;
    }
}

//make custom columns sortable
add_filter( 'manage_edit-post_sortable_columns', 'set_custom_post_sortable_columns' );
function set_custom_post_sortable_columns( $columns ) {
    $columns['start_date'] = 'start_date';
    return $columns;
}

add_action( 'pre_get_posts', 'post_custom_orderby' );
function post_custom_orderby( $query ) {
  if ( ! is_admin() )
    return;
  $orderby = $query->get( 'orderby');
  if ( 'start_date' == $orderby ) {
    $query->set( 'meta_key', 'start_date');
    $query->set( 'orderby', 'meta_value' );
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
 return '<a class="more-link" href="' . get_field('path') . '">Read More...</a>';
}
add_filter( 'the_content_more_link', 'modify_read_more_link' );

function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


?>
