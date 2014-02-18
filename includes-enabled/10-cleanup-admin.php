<?php

/**
 * Cleanup in admin area
 */

namespace EP\admin\cleanup;

/**
 * Cleanup dashboard by removing dashboards meta boxes
 */
add_action("admin_init", function() {

	// remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
	remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');  // quick press
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');  // recent drafts
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	remove_meta_box('dashboard_primary', 'dashboard', 'normal');
	remove_meta_box('dashboard_secondary', 'dashboard', 'normal');

	// Remove metabox for WPML
	$wpml_dasbhboard_widget_id = "icl_dashboard_widget";
	remove_meta_box( $wpml_dasbhboard_widget_id, "dashboard", "normal" );

});

/**
 * Remove x pingback from headers
 */
add_filter('wp_headers', function($headers) {
	
	unset($headers['X-Pingback']);
	return $headers;
	
});


/**
 * Removes the sitepress/wpml generator tag from head
 */
add_action("admin_init", function() {

	global $sitepress;

	if (isset($sitepress) && is_object($sitepress)) {
		remove_filter("wp_head", array($sitepress, "meta_generator_tag"));
	}

});

/**
 * Remove "Thank you for creating with WordPress"-text in bottom
 */
add_action("admin_init", function() {
	add_filter("admin_footer_text", "__return_false");
});

/**
 * Cleanup admin menu by removing menu items like posts and comments
 */
add_action("admin_menu", function() {

	// Remove (blog) posts menu
	remove_menu_page("edit.php");
	
	// Remove comments menu
	remove_menu_page("edit-comments.php");

});

/**
 * Remove WordPress-logo from admin bar
 */
add_action('wp_before_admin_bar_render', function() {

	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');


}, 0);

