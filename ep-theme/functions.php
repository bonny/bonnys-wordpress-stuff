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
		remove_filter("wp_head", "wp_generator");

		// Add menus, post types, and similar
		add_filter("init", array($this, "add_menus"));
		add_filter("init", array($this, "add_post_types"));

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



