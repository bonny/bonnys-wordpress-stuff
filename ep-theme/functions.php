<?php

class EP {

	function init() {

		// General setup and cleanup and make things work the way i want and like
		$this->load_functions();
		add_filter("admin_init", "relativize_links" );
		add_action('template_redirect', array($this, 'setup_jquery'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts') );
		add_action('wp_get_attachment_image_attributes', 'remove_attachment_title_attr');
		add_filter('body_class', "add_slug_to_body_class");
		add_action("widgets_init", "ep_remove_recent_comments_css");

		add_filter('wp_headers', array($this, 'remove_x_pingback'));
		remove_filter("wp_head", "wp_generator");
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');

		global $sitepress; if (isset($sitepress) && is_object($sitepress)) remove_filter("wp_head", array($sitepress, "meta_generator_tag"));
		add_filter('wp_title', array($this, "add_tagline_to_title"), 10, 3);
		add_action("wp_head", array($this, "add_open_graph_tags"));
		add_action("admin_init", array($this, "cleanup_dashboard"));
		// add_action("admin_menu", array($this, "cleanup_menu"));

		// Add classes to wp_list_pages that tell us if the page has childs/sub pages. makes it possible to style the parent
		add_filter('page_css_class', array($this, 'add_page_css_has_children'), 10, 5);
		add_action('save_post', array($this, 'delete_add_page_css_has_children_cache' ));
		add_action('delete_post', array($this, 'delete_add_page_css_has_children_cache'));

		// Remove "Thank you for creating with WordPress"-text in bottom
		add_filter("admin_footer_text", "__return_false");

		// Set a custom order for the menu, for example pages above posts
		add_filter('custom_menu_order', '__return_true');
		add_filter('menu_order', array($this, "set_menu_order"));

		// Add menus, post types, and similar
		add_filter("init", array($this, "add_menus"));
		add_filter("init", array($this, "add_post_types"));

		// Add debug info
		add_filter( 'template_include', array($this, 'var_template_include'), 1000 );

	}

	// Remove x pingback from headers
	function remove_x_pingback($headers) {
	    unset($headers['X-Pingback']);
	    return $headers;
	}

	/**
	 * Shortcut for __(). Works like __(), but adds local text domain.
	 * @param $str string Text
	 */
	function __($str) {
		return __($str, $this->text_domain);
	}

	/**
	 * Cleanup dashboard by removing dashboards meta boxes
	 */
	function cleanup_dashboard() {

		#remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');  // quick press
		#remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');  // recent drafts

		remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
		remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
		remove_meta_box('dashboard_primary', 'dashboard', 'normal');
		remove_meta_box('dashboard_secondary', 'dashboard', 'normal');

	}

	/**
	 * Cleanup menu by reoving posts and comments for example
	 */
	function cleanup_menu() {

		// Remove (blog) posts menu
		remove_menu_page("edit.php");
		// Remove comments menu
		remove_menu_page("edit-comments.php");

	}

	/**
	 * Add open graph tags to the head + regular meta description
	 * Some resources:
	 * http://yoast.com/facebook-open-graph-protocol/
	 * http://ogp.me
	 */
	function add_open_graph_tags() {

		global $post;
		if (is_null($post)) return;

		setup_postdata($post);
		?>
		<meta property="og:title" content="<?php the_title() ?>">
		<meta property="og:site_name" content="<?php bloginfo('name') ?>">
		<?php $excerpt = get_the_excerpt(); if ($excerpt) { ?>
		<meta property="og:description" content="<?php echo esc_attr($excerpt); ?>">
		<meta name="description" content="<?php echo esc_attr($excerpt) ?>">
		<?php } ?>
		<meta property="og:url" content="<?php echo home_url(get_permalink()) ?>"/>	
		<meta property="og:type" content="<?php
		if (is_single() || is_page()) {
			echo "article";
		} else {
			echo "website";
		}
		?>">
		<?php
		// find and output image
		$image = FALSE;
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
	
	/**
	 * Add tagline to title if we are of front page or home
	 */
	function add_tagline_to_title($title, $sep, $seplocation) {
		
		$title .= get_bloginfo( 'name' );

		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title = "$title $sep $site_description";
		}
		
		return $title;
	}

	/**
	 * Change the order of the menu. Perhaps we don't want posts to be so damn high up.
	 * http://codex.wordpress.org/Plugin_API/Filter_Reference/menu_order
	 */
	function set_menu_order($menu_order) {
		$menu_order = array(
			"index.php",
			"separator1",
			"edit.php?post_type=candidates",
			"edit.php?post_type=jobs",
			"edit.php?post_type=page",
			// pages not added here will be added last automatically
		);
		return $menu_order;
	}


	/**
	 * Load jquery from CDN with local fallback
	 * http://beneverard.co.uk/blog/wordpress-loading-jquery-correctly-version-2/
	 */
	function setup_jquery() {
		
		// only use this method is we're not in wp-admin
		if (!is_admin()) {

			// deregister the original version of jQuery
			wp_deregister_script('jquery');
			
			// register it again, this time with no file path
			wp_register_script('jquery', '', FALSE, '1.8.2', TRUE);
			
			// add it back into the queue
			wp_enqueue_script('jquery');
		
		}
	}

	function enqueue_styles_and_scripts() {

		wp_enqueue_style("style_screen", get_template_directory_uri() . '/style.css');
		wp_enqueue_script("ep_scripts", get_template_directory_uri() . '/js/scripts.js', array("jquery"), 1, TRUE);
		
	}	


	function load_functions() {
		require_once(dirname(__FILE__) . "/ep/ep-functions.php");
		require_once(dirname(__FILE__) . "/ep/ep-simple-front-end-edit-buttons.php");
	}


	function add_post_types() {

		/*
		register_post_type("smakmatare", array(
			"label" 		=> __("Smakmätare", "jn"),
			"public"	 	=> FALSE,
			"menu_position"	=> 5,
			"has_archive" 	=> FALSE,
			"show_in_nav_menus" => TRUE,
			"show_ui" => TRUE
		));
		*/

	}

	function add_menus() {

		/*
		register_nav_menus(array(
			"nav_primary" => "Huvudmeny"
		));
		*/

	}

	// Code to get current template. Found here:
	// http://wordpress.stackexchange.com/questions/10537/get-name-of-the-current-template-file
	function var_template_include( $t ){
	    $GLOBALS['current_theme_template'] = basename($t);
	    return $t;
	}

	function get_current_template( $echo = false ) {
	    if( !isset( $GLOBALS['current_theme_template'] ) )
	        return false;
	    if( $echo )
	        echo $GLOBALS['current_theme_template'];
	    else
	        return $GLOBALS['current_theme_template'];
	}

	// for wp list pages: add a class that tell us if the page has childs/sub pages. makes it possible to style the parent
	// $css_class, $page, $depth, $args, $current_page
	function add_page_css_has_children($css_class, $page, $depth, $args, $current_page) {

		$cache_key = $page->ID;
		$cache_group = "ep_add_page_css_has_children";
		$children = wp_cache_get( $cache_key, $cache_group );

		if ( false === $children ) {
			$children = get_children("post_parent=$page->ID&post_type[]=page&post_type[]=company&post_status=publish");
			wp_cache_set( $cache_key, $children);
		}

		if ( $children ) {
			$css_class[] = "page_has_children";
		}

		return $css_class;
	}

	function delete_add_page_css_has_children_cache($post_id) {
		wp_cache_delete( $post_id, "ep_add_page_css_has_children" );
	}


} // end class

$GLOBALS["ep"] = new EP();
$GLOBALS["ep"]->init();

