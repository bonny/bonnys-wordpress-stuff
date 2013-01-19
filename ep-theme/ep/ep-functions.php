<?php

/**
 * Misc useful functions go here
 */


/**
 * Does something with a post, using a callback
 * Setups global post variable before running callback
 * And restores it afterwards
 *
 * Example
 * <code>
 * with_post( get_queried_object(), function() {
 *      						
 *      printf('<h1>%1$s</h1>', get_the_title());
 *      
 *  } );
 * </code>
 *
 * @author Pär Thernstrom <https://twitter.com/eskapism>
 * @param ID or WP POST object
 * @param $do callable Function to run
 * @return Mixed Returns the return value of the $do function
 */
function with_post($post_thing, $do) {
	
	// If post_thing is numeric then get the post with that id
	if ( is_numeric( $post_thing ) ) {
		$post_thing = get_post( $post_thing );
	}

	if ( ! is_object( $post_thing) ) return FALSE;
	if ( ! get_class( $post_thing ) === "WP_Post" ) return FALSE;
	if ( ! is_callable( $do ) ) return FALSE;

	// Setup post
	global $post;
	$prev_post = $post;
	$post = $post_thing;
	setup_postdata( $post );

	// Run callback
	$callback_return = call_user_func_array( $do, array() );

	// Restore post
	$post = $prev_post;
	setup_postdata( $post );

	// Return what the callback returns, so we can check things in the template based on the callback
	return $callback_return;

}

 
/**
 * Remove the inlince css coments style thingie
 */
function ep_remove_recent_comments_css() {
	add_filter("show_recent_comments_widget_style", "__return_false");
}


/**
 * EarthPeople debug
 * För jag gillar att skriva ut variabler...
 */
function ep_d($var) {

	printf('%1$sVariable is of type <strong>"%2$s"</strong> with value:%1$s', "\n", gettype($var));
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

/**
 * Super Small and Simple WP Template thingie
 * Because I'm tired of throwing global posts around and using setuppostdata 1000 frickin times on a site.
 *
 * Usage:
 *
 * echo ep_get_post(19, "<div class='post-content'>%%CONTENT%%</div>");
 *
 * Which is so much shorter than (or other similar method):
 * $address_post_id = 19;
 * $adress_post = get_post($address_post_id);
 * $adress_content = $adress_post->post_content;
 * $adress_content = apply_filters('the_content', $adress_content);
 * echo "<div class='post-content'>" . $adress_content . "</div>";
 */
function ep_get_post($post_id_or_args, $format) {

	$arr_wp_query_options = array(
		"post_type" => "any"
	);

	if (is_integer($post_id_or_args)) {
		// If post_id is an integer then just show that one
		$arr_wp_query_options["post__in"] = array($post_id_or_args);
	} else {
		// if post_id is an array or string then we except a wp_query-options-array
		$arr_wp_query_options = wp_parse_args( $post_id_or_args, $arr_wp_query_options );
	}
	#ep_d($post_id_or_args);
	#ep_d($arr_wp_query_options);

	$output_combined = "";
	$format_org = $format;

	$custom_query = new WP_Query($arr_wp_query_options);
	while($custom_query->have_posts()) : $custom_query->the_post();

		$content = "";
		$format = $format_org;
		// only get content if %%CONTENT%% exists. getting the_content multiple times can have strange effects sometimes
		if (strpos($format, "%%CONTENT%%") !== FALSE) {
			$content = get_the_content();
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
		}
		if (strpos($format, "%%CONTENT_DIV%%") !== FALSE) {
			$content_div = ep_get_the_content("body");
			if ($content_div) {
				$content_div = "<div class='post-body'>$content_div</div>";
			} else {
				$content_div = "";
			}
			$format = str_replace("%%CONTENT_DIV%%", $content_div, $format);
		}
	
		// only teaser
		if (strpos($format, "%%TEASER%%") !== FALSE) {
			$teaser = ep_get_the_content("teaser");
			$format = str_replace("%%TEASER%%", $teaser, $format);
		}
		if (strpos($format, "%%TEASER_DIV%%") !== FALSE) {
			$teaser = trim(ep_get_the_content("teaser"));
			if ($teaser) {
				$teaser = "<div class='post-teaser'>$teaser</div>";
			} else {
				$teaser = "";
			}
			$format = str_replace("%%TEASER_DIV%%", $teaser, $format);
		}
	
		// content with teaser and body marked in source
		if (strpos($format, "%%EP_CONTENT%%") !== FALSE) {
			$ep_content = ep_get_teaser_and_body(get_the_id());
			$format = str_replace("%%ep_CONTENT%%", $ep_content, $format);
		}
		
		$title = get_the_title();
		$permalink = get_permalink();
	
		ob_start(); edit_post_link(); $edit_link = ob_get_clean();
		
		$ep_edit = ep_get_edit();
		$ep_edit_prio = ep_get_edit_prio();
		$ep_edit_add = sfeeb_edit_add();
		
		$format = str_replace("%%ID%%", get_the_ID(), $format);
		$format = str_replace("%%TITLE%%", $title, $format);
		$format = str_replace("%%CONTENT%%", $content, $format);
		$format = str_replace("%%PERMALINK%%", $permalink, $format);
		$format = str_replace("%%EDIT%%", $edit_link, $format);
		$format = str_replace("%%EP_EDIT%%", $ep_edit, $format);
		$format = str_replace("%%EP_EDIT_PRIO%%", $ep_edit_prio, $format);
		$format = str_replace("%%EP_EDIT_ADD%%", $ep_edit_add, $format);
		
		// functions to get values form simple fields
		// %%SF_IMAGE_4_1_0_size%% = output the first image from field 1 from field group 4 and get thumbnail "size"
		preg_match_all('/%%SF_IMAGE_(\d+)_(\d+)_(\d+)_(.+)%%/', $format, $matches);
		// %%SF_IMAGE_4_1_0_full%%
		#ep_d($matches);
		if ($matches) {
			// For each match = for each %%SF_IMAGE...
			for ($i = 0; $i < sizeof($matches[0]); $i++) {
				$full_match 		= $matches[0][$i];
				$fieldgroup_id 		= $matches[1][$i];
				$field_id 			= $matches[2][$i];
				$imagenum_to_output	= $matches[3][$i];
				$image_size			= $matches[4][$i];
				$image_tag 			= "";
				$values = (array) simple_fields_get_post_group_values(get_the_id(), $fieldgroup_id, false, 2);
				if ( isset($values[$imagenum_to_output][$field_id]) ) {
					// image exist at that position
					$image_tag = wp_get_attachment_image($values[$imagenum_to_output][$field_id], $image_size);
				}
				$format = str_replace($full_match, $image_tag, $format);
			}
		}

		// Get a simple text value from simple fields
		// %%SF_IMAGE_field_name%%
		preg_match_all('/%%SF_TEXT_(.+)%%/', $format, $matches);
		if ($matches) {
			// For each match = for each %%SF_TEXT...
			for ($i = 0; $i < sizeof($matches[0]); $i++) {
				$full_match = $matches[0][$i];
				$field_name	= $matches[1][$i];
				$text_value = simple_fields_value($field_name);
				$format = str_replace($full_match, $text_value, $format);
			}
		}

		$output_combined .= $format;
		
	endwhile; // end while have_posts

	wp_reset_postdata();

	return $output_combined;
}


// lägg på siddjup i body + om aktuellt artikel har barn
function ep_body_class($classes, $class) {
	global $post, $wp_query;
	$queried_object = $wp_query->get_queried_object();
	$child_count = 0;
	if (isset($queried_object)) {
		$parents = get_post_ancestors($post->ID);
		$children = get_children(array(
			"post_parent" => $post->ID,
			"post_type" => $queried_object->post_type,
			"post_status" => "publish"
		));
		$child_count = sizeof($children);
	}
	$classes[] = "post-childcount-$child_count";
	if ($child_count) {
		$classes[] = "post-has-children";
	} else {
		$classes[] = "post-no-children";
	}
	
	// skriv ut depth också
	$depth = 0;
	$parents = get_post_ancestors($post);
	$top_parent_id = $post->ID;
	if ($parents) {
		foreach ($parents as $one_parent_id) {
			$parent_post = get_post($parent_post);
			if ($parent_post->post_parent) {
				$top_parent_id = $one_parent_id;
				$depth++;
			}
		}
	}

	$classes[] = "post-depth-$depth";
	$classes[] = "post-top-id-$top_parent_id";
	
	return $classes;
}


function ep_post_get_first_child() {
	global $wp_query;
	$queried_object = $wp_query->get_queried_object();
	$args = array(
		"post_parent" => $queried_object->ID,
		"post_type" => $queried_object->post_type,
		"post_status" => "publish",
		"orderby" => "menu_order",
		"order" => "ASC",
	);
	$children = get_children($args);
	if (!$children) {
		return false;
	}
	return current($children);
}


// ger antal barn till den post som just nu visas
function ep_post_childcount() {
	global $wp_query;
	$queried_object = $wp_query->get_queried_object();
	$child_count = 0;
	if (isset($queried_object)) {
		$parents = get_post_ancestors($queried_object->ID);
		$args = array(
			"post_parent" => $queried_object->ID,
			"post_type" => $queried_object->post_type,
			"post_status" => "publish"
		);
		$children = get_children($args);
		$child_count = sizeof($children);
	}
	return $child_count;
}

/**
 * Tell how deep down in the hierachy we are
 * @return int depth
 */
function ep_post_depth() {
	global $post;
	$depth = 0;
	$parents = get_post_ancestors($post);
	if ($parents) {
		foreach ($parents as $one_parent_id) {
			$parent_post = get_post($parent_post);
			if ($parent_post->post_parent) {
				$depth++;
			}
		}
	}
	return $depth;
}

/**
 * This is a nifty little finction that makes is possible to
 * format teaser and body differently
 * 
 * It will output:
 * - teaser/text before the read-more-thingie wrapped in div.post-teaser
 * - body/text after the read-more-thingie wrapped in div.post-body
 * - ..but only if each one exists. so you can get teaser + body, or only teaser, or only body
 *
 * @author Pär Thernström 
 *
 */
function ep_teaser_and_body($post_id = NULL) {

	global $post, $more;
	$post_org = $post;
	$more_org = $more;
		
	if (!$post_id) {
		$post_id = $post->ID;
	}

	$post = get_post($post_id);
	setup_postdata($post);
	
	// Get teaser/text before the "read me"
	$content = $post->post_content;
	if ( preg_match('/<!--more(.*?)?-->/', $content, $matches) ) {
		// more-tag exists
		$more = 0;
		ob_start();
		the_content("", true);
		$teaser = ob_get_clean();
	} else {
		$teaser = "";
	}
	
	// Get the content/text after "read me"
	// funkar
	$more = 1;
	ob_start();
	the_content(NULL, true);
	$body = ob_get_clean();
	if ($teaser) {
		$teaser = "<div class='post-teaser'>$teaser</div>";
	}

	if ($body) {
		$body = "<div class='post-body'>$body</div>";
	}

	$post = $post_org;
	$more = $more_org;
	setup_postdata($post);
	echo $teaser . $body;

}
	
/**
 Get teaser,
 but if teaser does not exist get body instead
 Good for listing/overview views
 */
function ep_get_teaser_or_body() {

	$content = ep_get_the_content("teaser");
	if (empty($content)) {
		$content = ep_get_the_content("body");
	}
	return $content;
}


/**
 * Returns both teaser and body
 * Each wrapped in a <div> with class post-teaser and post-body
 * If any of them don't exist, their <div> won't be outputed
 *
 * @author Pär Thernström, November, 2010
 */
function ep_get_teaser_and_body($post_id = NULL) {

	$out = "";
	
	global $post;
	$post_org = $post;

	if (!$post_id) {
		$post_id = $post->ID;
	}

	global $post;
	$post_org = $post;
	$post = get_post($post_id);
	setup_postdata($post);

	// only posts that are published are allowed
	if ($post->post_status != "publish") {
		return "";
	}
	
	// get content, except teaser
	$body = get_the_content(null, true); // true = stripteaser
	if ($body) {
		$body = preg_replace('/<span id="more-[\d]+"><\/span>/', "", $body);
		
		// apply filters on the content
		$body = apply_filters('the_content', $body);
		$body = str_replace(']]>', ']]&gt;', $body);

		$body = "<div class='post-body'>\n\n$body</div>";
	}
	// get teaser, only teaser..
	$teaser = "";
	$content = get_the_content(null, false); // true = stripteaser
	$arr = preg_split('/<span id="more-[\d]+"><\/span>/', $content);
	if (sizeof($arr) == 2) {
		// vi har en teaser
		// should we apply filters?
		$teaser = $arr[0];
		$teaser = apply_filters('the_content', $teaser);
		$teaser = str_replace(']]>', ']]&gt;', $teaser);
		$teaser = "<div class='post-teaser'>\n\n" . $teaser . "</div>";
	}

	$out .= $teaser . $body;

	//echo "yyy{$out}yyy";

	$post = $post_org;
	setup_postdata($post);

	return $out;
}


// Filter wp_title() to add the ancenstors
// So for example the title "Medarbetare | Företaget AB" will become "Medarbetare | Om Oss | Företaget AB"
// Do the title modification
// Code from plugin "Breadcrumb Titles For Pages"
// Modified by Pär Thernström
function page_breadcrumb_wptitle( $title, $sep, $seplocation = null ) {
	global $wp_query;

	// Posts don't have parents
	if ( !is_page() )
		return $title;

	$post = $wp_query->get_queried_object();

	// If this is a top level Page, then there's nothing to modify
	if ( 0 == $post->post_parent )
		return $title;

	$prefix = " $sep ";

	// Figure out where the seperator is since the filter doesn't pass $seplocation pre-WordPress 2.7
	if ( null === $seplocation )
		$seplocation = ( $prefix === substr( $title, strlen( $prefix ) * -1 ) ) ? 'right' : 'left';

	// Copy the ancestors value so we can modify it
	$ancestors = $post->ancestors;

	// Prepend the current page onto the front of the list so it shows up in the title
	array_unshift( $ancestors, $post->ID );

	// If the blog title is not on the right (i.e. the left), then we need to flip the order
	if ( 'right' != $seplocation )
		$ancestors = array_reverse( $ancestors );


	// Get all of the titles
	$titles = array();
	$number_of_ancestors = sizeof($ancestors);
	$loop_num = 0;
	foreach ( $ancestors as $ancestor ) {

		// fetch from menu label if we have my plugin installed
		// @todo: should check if plugin is activated too...
		// but only if it's the last item
		if ($loop_num == 0) {
			// the first item = let it use custom title through for example simple seo
			$ancestortitle = get_the_title($ancestor);
			$ancestortitle = apply_filters( 'single_post_title', $ancestortitle );
		} else {
			$use_custom_page_title = (bool) get_post_meta($ancestor, "_simple_seo_use_custom_menu_label", true);
			if ($use_custom_page_title) {
				$ancestortitle = get_post_meta($ancestor, "_simple_seo_custom_menu_label_value", true);
			} else {
				$ancestortitle = get_the_title($ancestor);
				#$ancestortitle = apply_filters( 'single_post_title', $ancestortitle );
			}
		}


		#$ancestortitle = get_the_title($ancestor);
		#$ancestortitle_after_filter = apply_filters( 'single_post_title', $ancestortitle );
		#if (!$ancestortitle_after_filter) {
		#	$ancestortitle_after_filter = $ancestortitle;
		#}
	
		$titles[] = strip_tags( $ancestortitle );

		$loop_num++;
	}

	// Create the breadcrumb list
	$title = implode( $prefix, $titles );

 	// Determines position of the separator
	if ( 'right' == $seplocation )
		$title = $title . $prefix;
	else
		$title = $prefix . $title;

	// remove | if at first position
	$regexp = "/^($prefix)/";
	$regexp = str_replace("|", '\|', $regexp);
	$title = preg_replace($regexp, "", $title);

	return $title;
}

/**
 * check if current page/post is a child post of page/post with id $page_id
 * @param int $page_id
 * @return bool
 */
function ep_is_child_of($page_id) {
	global $post;
	$is_child = false;
	$parents = get_post_ancestors($post);
	if ($parents) {
		foreach ($parents as $one_parent_id) {
			if ($one_parent_id == $page_id) {
				$is_child = true;
				break;
			}
		}
	}
	return $is_child;
};

/*
	check if current page is a subpage
	if it is, the parent page id is returned
	if not, false is returned
*/
function ep_is_subpage() {
	global $post;                                 // load details about this page
        if ( is_page() && $post->post_parent ) {      // test to see if the page has a parent
               $parentID = $post->post_parent;        // the ID of the parent is this
               return $parentID;                      // return the ID
        } else {                                      // there is no parent so...
               return false;                          // ...the answer to the question is false
        };
};

function ep_edit_add() {
	echo sfeeb_edit_add();
}
function ep_get_edit_add() {
	return sfeeb_edit_add();
}

function ep_edit_prio() {
	echo sfeeb_edit_prio();
}

function ep_get_edit_prio() {
	return sfeeb_edit_prio();
}

function ep_get_edit() {
	return sfeeb_edit();	
}

function ep_edit() {
	echo sfeeb_edit();	
}



/**
 * Hämtar alla innehåll i content
 * eller bara teaser, eller bara body
 *
 * @param what to get all (default) | teaser | body
 * @return string
 */
function ep_get_the_content($what = "all") {
	
	global $more;
	$more_org = $more;
	$more = 1;
	
	$content = get_the_content();
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);

	// <span id="more-2"></span>
	// 2 = sidans id?
	// force_balance_tags
	// plocka ut allt, före, eller efter more
	if ($what != "all") {
		
		$arr = preg_split('/<span id="more-[\d]+"><\/span>/', $content);
		#echo "content: $content";
		#echo "size: " . sizeof($arr);
		#d($arr);
		#exit;
		if (sizeof($arr) == 1) {

			// fanns inte både ock
			// söker vi body men bara teaser finns borde vi ge tillbaka inget liksom
			// eller allt räknas som body, inget som teaser?
			if ($what == "teaser") {
				return "";
			} elseif ($what == "body") {
				return $content;
			}
			
		} elseif (sizeof($arr) == 2) {
			// verkar ha gått vägen
			$arr[0] = force_balance_tags($arr[0]);
			$arr[1] = force_balance_tags($arr[1]);
			
			if ($what == "teaser") {
				return $arr[0];
			} elseif ($what == "body") {
				return $arr[1];
			}
			
		}
	
	}

	$more = $more_org;

	return $content;
}


/**
 * Simple wrapper for native get_template_part()
 * Allows you to pass in an array of parts and output them in your theme
 * e.g. <?php get_template_parts(array('part-1', 'part-2')); ?>
 *
 * @param 	array 
 * @return 	void
 * @author 	Keir Whitaker
 **/
function get_template_parts( $parts = array() ) {
	foreach( $parts as $part ) {
		get_template_part( $part );
	};
}

/**
 * Pass in a path and get back the page ID
 * e.g. get_page_id_from_path('about/terms-and-conditions');
 *
 * @param 	string 
 * @return 	integer
 * @author 	Keir Whitaker
 **/
function get_page_id_from_path( $path ) {
	$page = get_page_by_path( $path );
	if( $page ) {
		return $page->ID;
	} else {
		return null;
	};
}

/**
 * Append page slugs to the body class
 * NB: Requires init via add_filter('body_class', 'add_slug_to_body_class');
 *
 * @param 	array 
 * @return 	array
 * @author 	Keir Whitaker
 */
function add_slug_to_body_class( $classes ) {
	global $post;
   
	if( is_home() ) {			
		$key = array_search( 'blog', $classes );
		if($key > -1) {
			unset( $classes[$key] );
		};
	} elseif( is_page() ) {
		$classes[] = sanitize_html_class( $post->post_name );
	} elseif(is_singular()) {
		$classes[] = sanitize_html_class( $post->post_name );
	};

	return $classes;
}

/**
 * Get the category id from a category name
 *
 * @param 	string 
 * @return 	string
 * @author 	Keir Whitaker
 */
function get_category_id( $cat_name ){
	$term = get_term_by( 'name', $cat_name, 'category' );
	return $term->term_id;
}


/**
 * Remove image title attributes. Thank you Google and search result number one or two: http://www.kevinleary.net/remove-title-attributes-images-wordpress/
 *
 * Remove the "title" attribute from all image attachments and functions
 * using the wp_get_attachment_image() function
 *
 * @param $attr An array of attributes for the <img />
 * @return $attr Filtered attributes without the title
 */
function remove_attachment_title_attr( $attr ) {
	unset($attr['title']);
	return $attr;
}

/**
 * Makes the permalink for a post/page/custom post type more futureproof by creating
 * relative paths instead of absolute paths.
 * This is a benefit when developing a website on several domains, so you don't have to change all
 * links from http://beta.example.com/ to http://example.com/.
 */
function relativize_links() {
	$arr_filters = array(
		"post_link", 
		"post_type_link", 
		"page_link", 
		// "theme_root_uri", // when i have some problem enqueing styles when wp is in subdir
		"wp_get_attachment_url", 
		"term_link", 
		"tag_link", 
		"category_link"
	);
 	foreach ($arr_filters as $filter_name) {
 		add_filter($filter_name, 'wp_make_link_relative');
 	}
}

/**
 * Custom callback for outputting comments 
 *
 * @return void
 * @author Keir Whitaker
 */
function starkers_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment; 
	?>
	<?php if ( $comment->comment_approved == '1' ): ?>	
	<li>
		<article id="comment-<?php comment_ID() ?>">
			<?php echo get_avatar( $comment ); ?>
			<h4><?php comment_author_link() ?></h4>
			<time><a href="#comment-<?php comment_ID() ?>" pubdate><?php comment_date() ?> at <?php comment_time() ?></a></time>
			<?php comment_text() ?>
		</article>
	<?php endif; ?>
	</li>
	<?php 
}

