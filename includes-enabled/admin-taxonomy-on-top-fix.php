<?php
/**
 * By default hierarchical taxonimies 
 * moves checked items out of the hierarchy and to the top of the list
 * and that is very annoying, so we tell WP to keep items in place
 */
namespace EP\admin\taxonomies;

add_filter( 'wp_terms_checklist_args', __NAMESPACE__ . '\modify_wp_terms_checklist_args', 10, 2 );
function modify_wp_terms_checklist_args( $args, $post_id ) {

	// May wanna check $args['taxonomy'] here to do this only for some taxonomies
	$args['checked_ontop'] = false;

	return $args;

}
