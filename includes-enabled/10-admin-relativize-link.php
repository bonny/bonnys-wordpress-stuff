<?php

namespace EP\admin\relativize_links;

/**
 * Makes the permalink for a post/page/custom post type more futureproof by creating
 * relative paths instead of absolute paths.
 * This is a benefit when developing a website on several domains, so you don't have to change all
 * links from http://beta.example.com/ to http://example.com/.
 * Only applies when adding links in tidy editor, because to many places assume absolute urls
 */
function relativize_links() {

	/*
	Notes about some disabled filters:
	- "theme_root_uri" led to problems enqueing styles when wp is in subdir
	- "wp_get_attachment_url" will have problem with photon from the jetpack plugin
	*/

	$arr_filters = array(
		"post_link", 
		"post_type_link", 
		"page_link", 
		"term_link", 
		"tag_link", 
		"category_link"
	);

 	foreach ( $arr_filters as $filter_name ) {
 		add_filter( $filter_name, 'wp_make_link_relative' );
 	}

}

add_action("admin_init", __NAMESPACE__ . '\relativize_links');
