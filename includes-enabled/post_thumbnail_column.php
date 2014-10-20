<?php

/**
 * Add the featured image to a column in the posts overview screen
 */

namespace EP\admin\post_thumbnail_column;

define("THUMBNAIL_SIZE", 75);

add_filter('manage_posts_columns', __NAMESPACE__ . '\posts_columns', 5);
add_action('manage_posts_custom_column', __NAMESPACE__ . '\posts_custom_columns', 5, 2);
add_filter('manage_pages_columns', __NAMESPACE__ . '\posts_columns', 5);
add_action('manage_pages_custom_column', __NAMESPACE__ . '\posts_custom_columns', 5, 2);
add_action('admin_head', __NAMESPACE__ . '\admin_head');

// Add styles
function admin_head() {
	?>
	<style>
		.column-ep_post_thumbs {
			width: 105px;
		}
	</style>
	<?php
}

// Add column to position after title
function posts_columns($columns) {
	
	if ( ! post_type_supports( get_post_type(), "thumbnail" ) )
		return $columns;

	// $columns = array('ep_post_thumbs' => __('Featured Image') );
	$new_col = array(
		'ep_post_thumbs' => __('Featured Image')
	);

	$title_position = array_search("title", array_keys($columns));
	if (false !== $title_position) {
		$title_position = $title_position + 1;
		$columns = array_slice($columns, 0, $title_position, true) + $new_col + array_slice($columns, $title_position, count($columns)-$title_position, true);
	}

	return $columns;

}

// Output column contents
function posts_custom_columns($column_name, $id){

	if ( $column_name === 'ep_post_thumbs' && has_post_thumbnail() ) {

		printf('%1$s', the_post_thumbnail( array(THUMBNAIL_SIZE, THUMBNAIL_SIZE) ));

	}

}
