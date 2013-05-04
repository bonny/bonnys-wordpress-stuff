<?php
/**
 * Simple Front End Edit Buttons
 * Functions and stuff to enable support for small funky edit buttons for posts and widgets
 */

// Simple Front End Edit Buttons = SFEEB
define( "SFEEB_VERSION", "0.3");
define( "SFEEB_URL", wp_make_link_relative(get_stylesheet_directory_uri() . "/ep"));
define( "SFEEB_NAME", "EP Simple Front End Edit Buttons");

/**
 * Hello. Let's get started now, shall we?
 * Hook onto some stuff. Yeah. Let's do that.
 */
add_action('query_vars', "sfeeb_query_vars_edit_prio");
add_filter("parse_request", "sfeeb_parse_request_edit_prio");
add_action('widget_pages_args', "sfeeb_widget_pages_args");
add_action('wp_list_pages', "sfeeb_wp_list_pages");
add_action('page_css_class', "sfeeb_page_css_class");
add_action("wp_head", "sfeeb_wp_head");
add_action("wp_footer", "sfeeb_wp_footer");
add_action("init", "sfeeb_init");
add_filter("get_pages", "sfeeb_get_pages", 10, 2);

// Widget related actions
add_action("dynamic_sidebar_params", "sfeeb_dynamic_sidebar_params");
add_action("widgets_admin_page", "sfeeb_widgets_admin_page");

/**
 * Create widget for related stuff for blog posts
 */
function sfeeb_widgets_admin_page() {

	if (isset($_GET["ep_edit_widget"])) {
		$ep_edit_widget_id = $_GET["ep_edit_widget_id"];
		?>
		<script>
		jQuery(function($) {

			var widgets = $("div.widget");
			widgets.each(function(i, elm) {

				// Find widget that ends with our id
				var elm_id = elm.id;
				if (elm_id.match(/widget-[\d]+_(<?php echo $ep_edit_widget_id ?>)/)) {

					var $elm = $(elm);
					$in = $elm.find(".widget-inside");
					$in.slideDown("slow", function() {
						$elm.effect("highlight", {}, 4000);
					});
					
				}
			});
		});
		</script>
		<?php
	}
}

function sfeeb_dynamic_sidebar_params($info = null) {
	
	if ( current_user_can("edit_theme_options") ) {
	
		// can disable by adding filter sfeeb_show_widget_edit with return false
		$show_edit = apply_filters("sfeeb_show_widget_edit", TRUE);	

		foreach ($info as & $one) {
	
		if ( ! isset( $one["widget_id"] ) || ! $show_edit ) continue;

			$one["before_widget"] .= sprintf('
				<div class="ep_edit_widget"><a href="%2$s"><img src="%3$s" title="Edit widget %1$s"></a></div>
				',
				esc_attr($one["widget_name"]),
				admin_url("widgets.php?ep_edit_widget=1&amp;ep_edit_widget_id=" . $one["widget_id"]),
				SFEEB_URL . "/edit.png"
			);

		}

	}

	return $info;
	
}


// check if ep_prio is set and store in global variable
function sfeeb_get_pages($pages, $arg2) {
	global $sfeeb_arg_is_set_to_show_prio_buttons;
	$sfeeb_arg_is_set_to_show_prio_buttons = FALSE;
	if (isset($arg2["ep_prio"])) {

		$sfeeb_arg_is_set_to_show_prio_buttons = TRUE;
	}
	return $pages;
}


function sfeeb_init() {

	if ( ! is_admin() ) return;
	
	wp_enqueue_script("jquery");
	wp_enqueue_script("jquery-effects-highlight");

}


// add css
function sfeeb_wp_head() {

	global $post;
	if ( is_null($post) ) return;

	if (current_user_can("edit_post", get_the_id())) {
	ob_start();
	?>
	<style type="text/css">
		/* Styles for plugin <?php echo SFEEB_NAME ?> */
		div.sfeeb_edit_wrap {
			margin: 0;
			padding: 0;
			line-height: 1;
			display: inline;
		}
		a.sfeeb_edit,
		.ep_edit_widget {
			opacity: .5;
			-ms-filter: "alpha(opacity=50)";
			margin-right: 1px;
			margin-left: 1px;
			display: inline !important;
			padding: 0 !important;
		}
		a.sfeeb_edit:hover,
		.ep_edit_widget:hover {
			opacity: 1;
			-ms-filter: "alpha(opacity=100)";
		}
		a.sfeeb_edit img,
		.ep_edit_widget img
		 {
			width: 13px;
			height: 13px;
		}

		.ep_edit_widget {
			position: absolute;
			top: 5px;
			right: 5px;
			opacity: .5;
		}

		.widget {
			position: relative;
		}
	</style>
	<?php
	// Kompakta lite och skriv ut
	$out = ob_get_clean();
	$out = preg_replace('![\n\r\t]+!', ' ', $out);
	echo $out;
	}
}


// add scripts, but only for admins
function sfeeb_wp_footer() {

	if (!current_user_can("edit_pages")) {
		return;
	}

	ob_start();
	?>
	
	<script type="text/javascript">
		/* Script for <?php echo SFEEB_NAME ?> */
		jQuery(document).on("click", ".sfeeb_edit_add", function(event) {
		
			// find the id of our post
			// sfeeb_edit sfeeb_edit_add sfeeb_edit_add_post_id_9
			var t = jQuery(this);
			var classes = t.attr("class");
			var match = classes.match(/sfeeb_edit_add_post_id_([\d]+)/);
			if (match.length == 2) {
				var post_id = match[1];
				sfeeb_add_page(post_id);
			}
		
			event.preventDefault();
		});
		function sfeeb_add_page(post_id) {
			var page_title = prompt("Enter name of new page", "Untitled");
			if (page_title) {
				
				var data = {
					"action": 'sfeeb_add_page',
					"pageID": post_id,
					"type": "after",
					"page_title": page_title,
					"post_type": "page"
				};
				
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				jQuery.post(ajaxurl, data, function(response) {
					//console.log(response);
					if (response != "0") {
						document.location = response;
					}
				});
				return false;
			
			} else {
				return false;
			}
		}
	</script>
	<?php
	
}

// modify output of wp_list_pages by adding move up/down-admin-icons
// but only if the list contains sfeeb_is_menu_order, because it's only usable if the pages are sorted that way
function sfeeb_wp_list_pages($output) {

	if (!current_user_can("edit_pages")) {
		return $output;
	}

	if (strpos($output, "sfeeb_is_menu_order") !== false) {

		// find the page id for each li and add our edit prio buttons/icons
		$rows = explode("\n", $output);
		$num_rows = sizeof($rows);
		for ($i=0; $i<$num_rows; $i++) {
			if ( preg_match("/page-item-([0-9]+)/", $rows[$i], $matches) ) {
				// it's a page (not a "ul" or similar, and we got a page id
				$post_id = $matches[1];
				// now add our output/links before the last </a>
				$edit_link = sfeeb_edit("post_id=$post_id");
				$edit_link = ""; // nah...
				$content_to_add = "</a>\n<div class='sfeeb_edit_wrap'>". $edit_link . sfeeb_edit_prio("post_id=$post_id");
				// $content_to_add = "<div class='sfeeb_edit_wrap'>" . $content_to_add . "</div>";
				$lastapos = strrpos($rows[$i], "</a>");
				$rows[$i] = substr_replace($rows[$i], $content_to_add, $lastapos, 0);
				$rows[$i] = str_replace("</a></a>", "</a></div>", $rows[$i]);
			}
		}
		
		// add a row for adding pages
		// $post_id should contain the last page on the list
		$add = sfeeb_edit_add("post_id=$post_id");
		$rows[] = "<li><div class='sfeeb_edit_wrap sfeeb_edit_add_wrap'>$add</div></li>";
		
		$output = implode($rows);
	}

	return $output;
}


// add css to a link in a wp_list_pages, but only if it's sorted by menu_order
function sfeeb_page_css_class($classes) {

	global $sfeeb_current_list_pages_sort_order, $sfeeb_arg_is_set_to_show_prio_buttons;
	if ($sfeeb_current_list_pages_sort_order == "menu_order, post_title" || isset($sfeeb_arg_is_set_to_show_prio_buttons) && $sfeeb_arg_is_set_to_show_prio_buttons == TRUE) {
		$classes[] = "sfeeb_is_menu_order";
	}

	return $classes;
}

// store sort_column used, so we can add css later on
function sfeeb_widget_pages_args($args) {
	global $sfeeb_current_list_pages_sort_order;
	$sfeeb_current_list_pages_sort_order = $args["sort_column"];
	return $args;
}

// Let WP listen for some more query args
function sfeeb_query_vars_edit_prio($qvars = null) {

	$qvars = (array) $qvars;
	$qvars[] = 'sfeeb_change_menu_order_post_id';
	$qvars[] = 'sfeeb_change_menu_order_direction';
	$qvars[] = 'sfeeb_change_menu_order_current_url';
	return $qvars;
}

// Look for our edit prio action on template_redirect
function sfeeb_parse_request_edit_prio($args = null) {

	// you must be allowed to edit pages to use this function
	if (!current_user_can("edit_pages")) {
		return $args;
	}

	// visa/kör bara kod om man är inloggad och är inloggad som "par"
	global $current_user, $wpdb; get_currentuserinfo();

	if (
		isset($args->query_vars["sfeeb_change_menu_order_direction"]) 
		&& isset($args->query_vars["sfeeb_change_menu_order_post_id"])
		&& isset($args->query_vars["sfeeb_change_menu_order_current_url"])
	) {

		// fetch all info we need from $_GET-params
		$post_to_update_id = (int) $args->query_vars["sfeeb_change_menu_order_post_id"];
		$direction = $args->query_vars["sfeeb_change_menu_order_direction"];
		$post_node = $post_to_update = get_post($post_to_update_id);
		$url_return = $args->query_vars["sfeeb_change_menu_order_current_url"];

		$post_node = get_post($post_to_update_id);
		
		// Måste leta upp $post_ref_node, som är ned efter om det är ner, den före om upp
		// get all posts with the same parent as our article
		$args = array(
			"post_type" => $post_to_update->post_type,
			"orderby" => "menu_order",
			"order" => "ASC",
			"post_parent" => $post_to_update->post_parent,
			"post_status" => "any",
			"numberposts" => -1
		);
		$posts_any_status = get_posts($args);
	
		// Vanligt problem är att på nya sajter så har alla sidor samma order. Måste vara olika för annars blir det knas
		// Lösning: loopa igenom alla och indexera om?
		// Måste även loopa igenom de som inte är publicerade, för man blir lite förvirrad om dom inte ändrar order...
		$menu_order = 0;
		foreach ($posts_any_status as $one_post) {
			$post_to_save = array(
				"ID" => $one_post->ID,
				"menu_order" => $menu_order++
			);
			wp_update_post( $post_to_save );
		}

		$args = array(
			"post_type" => $post_to_update->post_type,
			"orderby" => "menu_order",
			"order" => "ASC",
			"post_parent" => $post_to_update->post_parent,
			"post_status" => "public",
			"numberposts" => -1
		);
		$posts = get_posts($args);
		
		$found_index = FALSE;
		foreach ($posts as $index => $postobj) {
			if ($postobj->ID === $post_to_update->ID) {
				$found_index = $index;
				break;
			}
		}
		if ($found_index === FALSE) die("Could not change post order.");
		
		if ( "up" == $direction ) {
			$post_ref_node = get_post( $posts[$found_index-1] );
		} elseif ( "down" == $direction ) {
			$post_ref_node = get_post( $posts[$found_index+1] );
		}

		if ( "up" == $direction ) {
		
			// post_node is placed before ref_post_node
			// update menu_order of all pages with a menu order more than or equal ref_node_post and with the same parent as ref_node_post
			// we do this so there will be room for our page if it's the first page
			// so: no move of individial posts yet
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+1 WHERE post_parent = %d", $post_ref_node->post_parent ) );

			// update menu order with +1 for all pages below ref_node, this should fix the problem with "unmovable" pages because of
			// multiple pages with the same menu order (...which is not the fault of this plugin!)
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+1 WHERE menu_order >= %d", $post_ref_node->menu_order+1) );
			
			$post_to_save = array(
				"ID" => $post_node->ID,
				"menu_order" => $post_ref_node->menu_order,
				"post_parent" => $post_ref_node->post_parent
			);
			wp_update_post( $post_to_save );

			//echo "did before";

		} elseif ( "down" == $direction ) {
		
			// post_node is placed after ref_post_node
			
			// update menu_order of all posts with the same parent ref_post_node and with a menu_order of the same as ref_post_node, but do not include ref_post_node
			// +2 since multiple can have same menu order and we want our moved post to have a unique "spot"
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+2 WHERE post_parent = %d AND menu_order >= %d AND id <> %d ", $post_ref_node->post_parent, $post_ref_node->menu_order, $post_ref_node->ID ) );

			// update menu_order of post_node to the same that ref_post_node_had+1
			#$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d, post_parent = %d WHERE ID = %d", $post_ref_node->menu_order+1, $post_ref_node->post_parent, $post_node->ID ) );

			$post_to_save = array(
				"ID" => $post_node->ID,
				"menu_order" => $post_ref_node->menu_order+1,
				"post_parent" => $post_ref_node->post_parent
			);
			wp_update_post( $post_to_save );
			
			//echo "did after";
			
		}




		// redirect back
		wp_redirect($url_return);
		exit;

	} // if query args

	return $args;	
	
} // function


/**
 * Returns links with icons for changing the prio
 * @return String
 */
function sfeeb_edit_prio($args = null) {

	if (!current_user_can("edit_pages")) {
		return "";
	}
	
	$defaults = array(
		"post_id" => null // fetch post id from global $post variable
	);
	$args = wp_parse_args($args, $defaults);
	
	$out = "";
	$linkcommon = get_bloginfo("url");
	if ($args["post_id"]) {
		$post_id = $args["post_id"];
	} else {
		global $post;
		$post_id = $post->ID;
	}
		
	$linkcommon = add_query_arg("sfeeb_change_menu_order_post_id", $post_id, $linkcommon);
	$linkcommon = add_query_arg("amp;sfeeb_change_menu_order_current_url", sfeeb_getCurrentPageURL(), $linkcommon);

	// create link for both up and down
	$linkup = add_query_arg("amp;sfeeb_change_menu_order_direction", "up", $linkcommon);
	$linkdown = add_query_arg("amp;sfeeb_change_menu_order_direction", "down", $linkcommon);

	// @todo: fetch icons from plugin path
	$editimgup = SFEEB_URL . "/arrow-up.png";
	$editimgdown = SFEEB_URL . "/arrow-down.png";
	
	$post = get_post($post_id);
	$post_title = $post->post_title;
	$post_title = esc_attr(strip_tags($post_title));

	$out .= "<a title='Move \"$post_title\" up' class='sfeeb_edit sfeeb_edit_prio sfeeb_edit_prio_up' href='$linkup'><img src='$editimgup' alt='Move up' /></a>";
	$out .= "<a title='Move \"$post_title\" down' class='sfeeb_edit sfeeb_edit_prio sfeeb_edit_prio_down' href='$linkdown'><img src='$editimgdown' alt='Move down' /></a>";

	return $out;

}

/**
 * Returns an edit link with an icon
 */
function sfeeb_edit($args = null) {

	if (!current_user_can("edit_pages")) {
		return "";
	}

	$defaults = array(
		"post_id" => null // fetch post id from global $post variable
	);
	$args = wp_parse_args($args, $defaults);

	if ($args["post_id"]) {
		$post_id = $args["post_id"];
	} else {
		global $post;
		$post_id = $post->ID;
	}
	$link = get_edit_post_link($post_id);
	
	$post = get_post($post_id);
	$post_title = $post->post_title;
	$post_title = esc_attr(strip_tags($post_title));

	if (!$link) {
		return;
	} else {
		$editimg = SFEEB_URL . "/edit.png";
		$editimg = "<img src='$editimg' alt='Edit' />";
		return "<a class='sfeeb_edit sfeeb_edit_edit' title='Edit \"$post_title\"' href='$link'>$editimg</a>";
	}	
}

/**
 * Returns an add link with an icon
 */
function sfeeb_edit_add($args = null) {

	if (!current_user_can("edit_pages")) {
		return "";
	}

	$defaults = array(
		"post_id" => null // fetch post id from global $post variable
	);
	$args = wp_parse_args($args, $defaults);

	if ($args["post_id"]) {
		$post_id = $args["post_id"];
	} else {
		global $post;
		$post_id = $post->ID;
	}
	$link = get_edit_post_link($post_id);

	$post = get_post($post_id);
	$post_title = $post->post_title;
	$post_title = esc_attr(strip_tags($post_title));

	$img = SFEEB_URL . "/add.png";
	$img = "<img src='$img' alt='Add' />";
	return "<a class='sfeeb_edit sfeeb_edit_add sfeeb_edit_add_post_id_$post_id' title='Add a new page after \"$post_title\"' href='$link'>$img</a>";
}


/**
 * Get the URL we currently are at
 */
function sfeeb_getCurrentPageURL() {
	// http://stackoverflow.com/questions/189113/how-do-i-get-current-page-full-url-in-php
	$curpageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$curpageURL.= "s";}
	$curpageURL.= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
	$curpageURL.= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
	$curpageURL.= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $curpageURL;
}

/**
 * Code from plugin CMS Tree Page View
 * http://wordpress.org/extend/plugins/cms-tree-page-view/
 * Used with permission! :)
 */
add_action('wp_ajax_sfeeb_add_page', 'sfeeb_add_page');
function sfeeb_add_page() {

	global $wpdb;

	$type = $_POST["type"];
	$pageID = (int) $_POST["pageID"];
	$page_title = trim($_POST["page_title"]);
	$post_type = $_POST["post_type"];

	if (!$page_title) { $page_title = __("New page", 'simple-front-end-edit-buttons'); }

	$ref_post = get_post($pageID);

	if ("after" == $type) {

		/*
			add page under/below ref_post
		*/

		// update menu_order of all pages below our page
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+2 WHERE post_parent = %d AND menu_order >= %d AND id <> %d ", $ref_post->post_parent, $ref_post->menu_order, $ref_post->ID ) );		
		
		// create a new page and then goto it
		$post_new = array();
		$post_new["menu_order"] = $ref_post->menu_order+1;
		$post_new["post_parent"] = $ref_post->post_parent;
		$post_new["post_type"] = "page";
		$post_new["post_status"] = "draft";
		$post_new["post_title"] = $page_title;
		$post_new["post_content"] = "";
		$post_new["post_type"] = $post_type;
		$newPostID = wp_insert_post($post_new);

	} else if ( "inside" == $type ) {

		/*
			add page inside ref_post
		*/

		// update menu_order, so our new post is the only one with order 0
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+1 WHERE post_parent = %d", $ref_post->ID) );		

		$post_new = array();
		$post_new["menu_order"] = 0;
		$post_new["post_parent"] = $ref_post->ID;
		$post_new["post_type"] = "page";
		$post_new["post_status"] = "draft";
		$post_new["post_title"] = $page_title;
		$post_new["post_content"] = "";
		$post_new["post_type"] = $post_type;
		$newPostID = wp_insert_post($post_new);

	}
	
	if ($newPostID) {
		// return editlink for the newly created page
		$editLink = get_edit_post_link($newPostID, '');
		if ($wpml_lang) {
			$editLink = add_query_arg("amp;lang", $wpml_lang, $editLink);
		}
		echo $editLink;
	} else {
		// fail, tell js
		echo "0";
	}
	#print_r($post_new);
	exit;
}

