<?php

/**
 * Cleanup <head> by removing lots of things
 */

namespace EP\frontend\cleanup;

add_action("init", function() {

	remove_filter("wp_head", "wp_generator");
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links_extra', 3 );
	remove_action('wp_head', 'index_rel_link' ); // index link
	remove_action('wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0 );
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	remove_action('wp_head', 'rel_canonical');
	add_filter("show_recent_comments_widget_style", "__return_false");

});

