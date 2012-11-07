<?php

class EP {

	function init() {

		// General setup and cleanup and make things work the way i want and like
		$this->load_functions();
		add_filter("init", "relativize_links" );
		add_action('template_redirect', array($this, 'setup_jquery'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts') );
		add_action('wp_get_attachment_image_attributes', 'remove_attachment_title_attr');
		add_filter( 'body_class', "add_slug_to_body_class" );
		add_action("widgets_init", "ep_remove_recent_comments_css");
		remove_filter("wp_head", "wp_generator");
		global $sitepress; if (isset($sitepress) && is_object($sitepress)) remove_filter("wp_head", array($sitepress, "meta_generator_tag"));
		add_filter('wp_title', array($this, "add_tagline_to_title"), 10, 3);
		add_action("wp_head", array($this, "add_open_graph_tags"));

		// Set a custom order for the menu, for example pages above posts
		add_filter('custom_menu_order', '__return_true');
		add_filter('menu_order', array($this, "set_menu_order"));

		// Add menus, post types, and similar
		add_filter("init", array($this, "add_menus"));
		add_filter("init", array($this, "add_post_types"));

	}

	/**
	 * Add open graph tags to the head + regular meta description
	 * Some resources:
	 * http://yoast.com/facebook-open-graph-protocol/
	 * http://ogp.me
	 */
	function add_open_graph_tags() {
		global $post;
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
			$image = simple_fields_value("image");
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

}
$ep = new EP();
$ep->init();



