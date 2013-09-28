<?php

class EP {

	var 
		$cache_group = "ep",
		$is_debug
	;

	function init() {

		// Load our helper functions
		$this->load_functions();

		// Determine debug mode
		$this->detect_debug();

		// Add actions and filters
		$this->add_common_actions_and_filters();
		$this->add_admin_actions_and_filters();

	}

	// Detect debug mode
	// Default if on a *.ep-domain, otherwise can be activated with querystring like:
	// example.com/?ep-enable-debug=1
	function detect_debug() {

		$is_debug = false;

		// is domain.ep | example.ep | site.ep | *.ep
		if ( preg_match('/.ep$/', $_SERVER["HTTP_HOST"] ) ) {
			$is_debug = true;
		} else if ( isset( $_GET["ep-enable-debug"] ) && $_GET["ep-enable-debug"] ) {	
			$is_debug = true;
		}

		$this->is_debug = $is_debug;

	}

	// Actions and filters that are to be run during all request
	function add_common_actions_and_filters() {

		// Add menus, post types, and similar
		add_filter("init", array($this, "add_menus"));
		add_filter("init", array($this, "add_post_types"));

		// Make jquery load from google CDN
		add_action('template_redirect', array($this, 'load_jquery_from_cdn'));

		// Load our scripts and styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts') );

		// Remove junk from head-tag
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

		// Remove title from inserted attachmentents
		add_action('wp_get_attachment_image_attributes', 'remove_attachment_title_attr');

		// Remove that incredibly stupid auto wordpress > WordPress "correct"
		remove_filter( 'the_title', 'capital_P_dangit', 11 );
		remove_filter( 'the_content', 'capital_P_dangit', 11 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );

		// Remove WPML-generator tag
		global $sitepress; if (isset($sitepress) && is_object($sitepress)) remove_filter("wp_head", array($sitepress, "meta_generator_tag"));

		// Add things to head-stuff, like titles, tagss
		add_action("wp_head", array($this, "add_open_graph_tags"));		
		add_filter('wp_title', array($this, "add_tagline_to_title_if_front_or_home"), 10, 3);
		add_filter('body_class', "add_page_slug_to_body_class");
		add_filter('body_class', array($this, "add_dev_to_body_class"));
		add_filter('wp_headers', array($this, 'remove_x_pingback_header'));

		// Add classes to wp_list_pages that tell us if the page has childs/sub pages. makes it possible to style the parent
		add_filter('page_css_class', array($this, 'add_page_css_has_children'), 10, 5);
		add_action('save_post', array($this, 'delete_add_page_css_has_children_cache' ));
		add_action('delete_post', array($this, 'delete_add_page_css_has_children_cache'));

		// Makes the function var_template_include() work
		add_filter( 'template_include', array($this, 'var_template_include'), 1000 );

	}

	function add_dev_to_body_class($classes) {
		if ($this->is_debug) $classes[] = "ep-is-dev";
		return $classes;
	}

	// Actions and filters that are to be run on admin pages
	function add_admin_actions_and_filters() {

		if ( ! is_admin() )
			return;

		// Remove junk from dashboard, like recent comments & rss feeds
		add_action("admin_init", array($this, "cleanup_dashboard"));

		// Remove menu items like posts and comments
		// add_action("admin_menu", array($this, "cleanup_admin_menu"));
		
		// Make links in add link-popup become relative
		add_filter("admin_init", "relativize_links" );

		// Remove "Thank you for creating with WordPress"-text in bottom
		add_filter("admin_footer_text", "__return_false");

		// Set a custom order for the menu, for example pages above posts
		add_filter('custom_menu_order', '__return_true');
		add_filter('menu_order', array($this, "set_menu_order"));	

	} // end add admin actions


	// Remove x pingback from headers
	function remove_x_pingback_header($headers) {
	    unset($headers['X-Pingback']);
	    return $headers;
	}


	/**
	 * Cleanup dashboard by removing dashboards meta boxes
	 */
	function cleanup_dashboard() {

		// remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');  // quick press
		remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');  // recent drafts
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
		remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
		remove_meta_box('dashboard_primary', 'dashboard', 'normal');
		remove_meta_box('dashboard_secondary', 'dashboard', 'normal');

	}

	/**
	 * Cleanup menu by reoving posts and comments for example
	 */
	function cleanup_admin_menu() {

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
		if (post_password_required($post)) return;

		setup_postdata($post);
		?>
		<meta property="og:title" content="<?php the_title() ?>">
		<meta property="og:site_name" content="<?php bloginfo('name') ?>">
		<?php $excerpt = get_the_excerpt(); if ($excerpt) { ?>
		<meta property="og:description" content="<?php echo esc_attr($excerpt); ?>">
		<meta name="description" content="<?php echo esc_attr($excerpt) ?>">
		<?php } ?>
		<meta property="og:url" content="<?php echo get_permalink() ?>"/>	
		<meta property="og:type" content="<?php
		if (is_single() || is_page() && !is_home() && !is_front_page()) {
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
	function add_tagline_to_title_if_front_or_home($title, $sep, $seplocation) {
		
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
			"edit.php?post_type=page",
			"edit.php",
			"separator2",
			// pages not added here will be added last automatically
		);
		return $menu_order;
	}


	/**
	 * Load jquery from CDN with local fallback
	 * http://beneverard.co.uk/blog/wordpress-loading-jquery-correctly-version-2/
	 */
	function load_jquery_from_cdn() {
		
		// only use this method is we're not in wp-admin
		if ( ! is_admin() ) {

			// deregister the original version of jQuery
			wp_deregister_script('jquery');
			
			// register it again, this time with no file path
			wp_register_script('jquery', '', FALSE, '1.10.1', TRUE);
			
			// add it back into the queue
			wp_enqueue_script('jquery');
		
		}
	}

	// Add our scripts for this site
	// Automatically cache busts them, based on last changed date on any file
	function enqueue_styles_and_scripts() {

		// find modification time of the latest js or css file, max one folder down
		$files = array_merge( glob( plugin_dir_path( __FILE__ ) . "*.{css,js}", GLOB_BRACE ), glob( plugin_dir_path( __FILE__ ) . "*/*.{css,js}", GLOB_BRACE ) );
		$files = array_combine($files, array_map("filemtime", $files));
		arsort($files);		
		$latest_file_time = $files[key($files)];
		
		// queue styles
		wp_enqueue_style("style_screen", get_template_directory_uri() . '/style.css', null, $latest_file_time);

		// queue scripts
		wp_enqueue_script("ep_scripts", get_template_directory_uri() . '/js/ep/scripts.js', null, $latest_file_time, TRUE);

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

	// return the name of the current template
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

		$cache_key = "add_page_css_has_children_" . md5( json_encode(func_get_args()) );
		$children = wp_cache_get( $cache_key, $this->cache_group );

		if ( false === $children ) {
			$children = get_children("post_parent=$page->ID&post_type[]=page&post_type[]=company&post_status=publish");
			wp_cache_set( $cache_key, $children, $this->cache_group);
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

