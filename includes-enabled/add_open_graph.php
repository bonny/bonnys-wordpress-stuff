<?php

/**
 * Add open graph tags to the head + regular meta description
 * Some resources:
 * http://yoast.com/facebook-open-graph-protocol/
 * http://ogp.me
 */

namespace EP\frontend\add_open_graph;

// Don't run on admin pages
if ( is_admin() )
	return;

/**
 * Output og tags inside <head>
 */
function add_open_graph_tags() {

	global $post;

	if (is_null($post))
		return;

	if (post_password_required($post)) 
		return;

	setup_postdata($post);
	
	$og_type = "website";
	if ( is_single() || is_page() && ! is_home() && ! is_front_page() ) {
		$og_type = "article";
	}

	?>
	<meta property="og:title" content="<?php the_title() ?>">
	<meta property="og:site_name" content="<?php bloginfo('name') ?>">
	<?php
	$excerpt = get_the_excerpt();
	if ($excerpt) {
		?> 
		<meta property="og:description" content="<?php echo esc_attr($excerpt); ?>">
		<meta name="description" content="<?php echo esc_attr($excerpt) ?>"><?php
	} ?> 
	<meta property="og:url" content="<?php echo get_permalink() ?>"/>	
	<meta property="og:type" content="<?php echo $og_type ?>">
	<?php
	// find and output image
	$image = false;
	if (has_post_thumbnail()) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), "medium");
		$image = $image[0];
	} else {
		// no post thumbnail, so check simple fields
		// $image = simple_fields_value("image");
	}
	if ($image) { ?>
	<meta property="og:image" content="<?php echo home_url($image) ?>">
	<?php
	}

}
add_action("wp_head", __NAMESPACE__ . '\add_open_graph_tags');
