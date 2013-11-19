<?php

/**
 * Add class to wp_list_pages that tell us if the page has childs/sub pages.
 * Makes it possible to style the parent
 *
 * This seems to be enabled by default in WP 3.6 or something
 * Commit here: https://github.com/WordPress/WordPress/commit/57608093192948e4e8f3d005a332b8c824f86e44
 */

namespace EP\page_css_class;

if ( is_admin() )
	return;

add_filter('page_css_class', __NAMESPACE__ . '\add_page_css_has_children', 10, 5);
add_action('save_post', __NAMESPACE__ . '\delete_add_page_css_has_children_cache' );

/**
 * For wp list pages: 
 * add a class that tell us if the page has childs/sub pages. makes it possible to style the parent
 * $css_class, $page, $depth, $args, $current_page
 */
function add_page_css_has_children($css_class, $page, $depth, $args, $current_page) {

	global $ep;
	if ( empty($ep) )
		return $css_class;

	$cache_key = "add_page_css_has_children_" . $ep->cache_namespace_key . "_" . md5( json_encode(func_get_args()) );

	$children = wp_cache_get( $cache_key, $ep->cache_group );

	if ( false === $children ) {
		$children = get_children("post_parent=$page->ID&post_type[]=page&post_type[]=company&post_status=publish");
		wp_cache_set( $cache_key, $children, $ep->cache_group);
	}

	if ( $children ) {
		$css_class[] = "has-children";
	}

	return $css_class;
}

/**
 * Clear the cache
 * Called when post is saved
 */
function delete_add_page_css_has_children_cache($post_id) {

	global $ep;
	$ep->cache_incr();

}
