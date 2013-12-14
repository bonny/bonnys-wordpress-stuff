<?php

/**
 * Adds an "Order" column to all custom post types
 * so custom post types can
 */

namespace EP\admin\custom_post_type_order;

add_action("admin_init", function() {

	$post_types = get_post_types(array(
		"_builtin" => false
	));
	
	add_action("admin_head", __NAMESPACE__ . '\ep_add_order_column_styles');
	
	// Add actions and filters for each non-built-in post type
	foreach ($post_types as $post_type => $val) {
		add_action("manage_edit-{$post_type}_columns", __NAMESPACE__ . '\ep_add_new_header_text_column');
		add_action("manage_{$post_type}_posts_custom_column", __NAMESPACE__ . '\ep_show_order_column');
		add_filter("manage_edit-{$post_type}_sortable_columns", __NAMESPACE__ . '\ep_order_column_register_sortable');
	}

});

/**
 * Add styles to make order column not so wide
 */
function ep_add_order_column_styles() {
	?><style>
		.column-menu_order {
			width: 10%;
		}
	</style><?php
}


/**
* add order column to admin listing screen for header text
*/
function ep_add_new_header_text_column($header_text_columns) {
	$header_text_columns['menu_order'] = "Order";
	return $header_text_columns;
}

/**
* show custom order column values
*/
function ep_show_order_column($name){
	global $post;

	switch ($name) {
		case 'menu_order':
			$order = $post->menu_order;
			echo $order;
		break;
		
		default:
			break;
	}
}

/**
* make column sortable
*/
function ep_order_column_register_sortable($columns){
	$columns['menu_order'] = 'menu_order';
	return $columns;
}