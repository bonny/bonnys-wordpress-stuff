<?php

/**
 * @author Pär Thernström
 */
class EP {

	// Cache group to use for the wp_cache_* functions
	var $cache_group = "ep";
	var $cache_namespace_key = null;

	// Bool to detect if debug should be outputed
	var $is_debug = false;

	/**
	 * Init class
	 */
	function init() {

		$this->detect_debug();
		
		$this->load_functions();

		$this->setup_cache();
		
		$this->add_actions_and_filters();
		
		$this->add_admin_actions_and_filters();

		$this->add_debug_info_to_footer();

		$this->load_external_helpers();


	}

	/**
	 * Setup cache namespace key, as explained here:
	 * http://core.trac.wordpress.org/ticket/4476
	 * 
	 * When using wp_cache_set and wp_cache_get, do like this:
	 *
	 * $my_key = "foo_" . $this->cache_namespace_key . "_12345";
	 * $my_value = wp_cache_get( $my_key, $this->cache_group );
	 * wp_cache_set( $my_key, $my_vals, $this->cache_group );
	 *
	 */
	function setup_cache() {

		// Get previos saved namespace key
		$this->cache_namespace_key = wp_cache_get( 'cache_namespace_key', $this->cache_group );

		// If not set, initialize it
		if ( $this->cache_namespace_key === false )
			wp_cache_set( 'cache_namespace_key', 1, $this->cache_group );

	}

	/**
	 * Increment cache group key, so next time caches are used they are freshed ("emptied")
	 * Use when caches need to be cleared, and you have keys that are dynamically created,
	 * or if you for some reason need to clear the whole cache for the group
	 */
	function cache_incr() {

		$this->cache_namespace_key = wp_cache_incr( 'cache_namespace_key', 1, $this->cache_group );
		echo "<br>new cache namespace key: " . $this->cache_namespace_key;

	}


	/**
	 * Load helper functions
	 */
	function load_functions() {

		require_once(dirname(__FILE__) . "/ep/ep-functions.php");
		require_once(dirname(__FILE__) . "/ep/ep-simple-front-end-edit-buttons.php");

	}

	/**
	 * Load external helpers
	 * Put a PHP file in includes-enabled/ and it will be loaded
	 * Files are loaded in alphabetical order
	 */
	function load_external_helpers() {

		// find and include files in bugs directory
		$files = glob( get_stylesheet_directory() . "/includes-enabled/*");
		foreach ($files as $filepath) {
			// Use load_template so $post and other globals are automatically set
			load_template($filepath, true, true );
		}

	}

	/**
	 * Detect debug mode
	 * Default if on a *.ep-domain, otherwise can be activated with querystring like:
	 * example.com/?ep-enable-debug=1
	 */
	function detect_debug() {

		$is_debug = false;

		if ( preg_match('/.ep$/', $_SERVER["HTTP_HOST"] ) ) {

			// if domain has top level domain ep, like domain.ep | example.ep | site.ep | *.ep then enable debug mode
			$is_debug = true;

		} else if ( isset( $_GET["ep-enable-debug"] ) && $_GET["ep-enable-debug"] ) {	

			// if debug flag is set
			$is_debug = true;

		}

		$this->is_debug = $is_debug;

	}

	/**
	 * Actions and filters that are to be run during all request
	 */
	function add_actions_and_filters() {

		// Make jquery load from google CDN
		// add_action('template_redirect', array($this, 'load_jquery_from_cdn'));

		// Load our scripts and styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_and_scripts') );

		// Remove junk from head
		$this->cleanup_head();

		// Remove WordPress-logo from admin bar
		add_action('wp_before_admin_bar_render', array($this, 'admin_bar_remove_wp_logo'), 0);

		// Remove title from inserted attachmentents
		add_action('wp_get_attachment_image_attributes', 'remove_attachment_title_attr');

		// Remove that incredibly stupid auto wordpress > WordPress "correct"
		remove_filter( 'the_title', 'capital_P_dangit', 11 );
		remove_filter( 'the_content', 'capital_P_dangit', 11 );
		remove_filter( 'comment_text', 'capital_P_dangit', 31 );

		// Remove WPML-generator tag
		$this->sitepress_remove_generator();

		// Add things to head-stuff, like titles, tagss
		add_filter('wp_title', array($this, "add_tagline_to_title_if_front_or_home"), 10, 3);
		add_filter('body_class', "add_page_slug_to_body_class");
		add_filter('body_class', array($this, "add_dev_to_body_class"));
		add_filter('wp_headers', array($this, 'remove_x_pingback_header'));

		// Makes the function var_template_include() work
		add_filter( 'template_include', array($this, 'var_template_include'), 1000 );

	}

	/**
	 * Removes the sitepress/wpml generator tag from head
	 */
	function sitepress_remove_generator() {
		global $sitepress;
		if (isset($sitepress) && is_object($sitepress))
			remove_filter("wp_head", array($sitepress, "meta_generator_tag"));
	}

	/**
	 * Remves the WordPress-logo from the admin bar
	 * Called from action wp_get_attachment_image_attributes
	 */
	function admin_bar_remove_wp_logo() {

		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('wp-logo');

	}

	/**
	 * Actions and filters that are run on admin pages
	 */
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


	/**
	 * Add class .ep-is-dev to the body element if we are in debug mode
	 */
	function add_dev_to_body_class($classes) {
		if ($this->is_debug) $classes[] = "ep-is-dev";
		return $classes;
	}


	/**
	 * Remove x pingback from headers
	 */
	function remove_x_pingback_header($headers) {
	    unset($headers['X-Pingback']);
	    return $headers;
	}

	/**
	 * Cleanup head by removing lots of things
	 */
	function cleanup_head() {

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
			wp_register_script('jquery', '', false, '1.10.1', true);
			
			// add it back into the queue
			wp_enqueue_script('jquery');
		
		}
	}

	/**
	 * Add scripts for this site
	 * Automatically cache busts them, based on last changed date on any file
	 */
	function enqueue_styles_and_scripts() {

		// find modification time of the latest js or css file, max one folder down
		$files = array_merge( glob( plugin_dir_path( __FILE__ ) . "*.{css,js}", GLOB_BRACE ), glob( plugin_dir_path( __FILE__ ) . "*/*.{css,js}", GLOB_BRACE ) );
		$files = array_combine($files, array_map("filemtime", $files));
		arsort($files);		
		$latest_file_time = $files[key($files)];
		
		// queue styles
		wp_enqueue_style("style_screen", get_template_directory_uri() . '/style.css', null, $latest_file_time);

		// queue scripts
		wp_enqueue_script("ep_scripts", get_template_directory_uri() . '/js/ep/scripts.js', null, $latest_file_time, true);

	}	


	/**
	 * Sets a global variable to track the current template being used
	 * Needed for get_current_template()
	 *
	 * Code to get current template. Found here:
	 * http://wordpress.stackexchange.com/questions/10537/get-name-of-the-current-template-file
	 */
	function var_template_include( $t ){
	    $GLOBALS['current_theme_template'] = basename($t);
	    return $t;
	}

	/**
	 * Return the name of the current template
	 * @return string Template file name
	 */
	function get_current_template( $echo = false ) {
	    
	    if( !isset( $GLOBALS['current_theme_template'] ) )
	        return false;
	    
	    if ( $echo )
	        echo $GLOBALS['current_theme_template'];
	    else
	        return $GLOBALS['current_theme_template'];

	}

	/**
	 * Show debug info in the footer, if ep_debug is detected/activated
	 */
	function add_debug_info_to_footer() {

		add_action("wp_footer", function() {
			global $wpdb;
			?>
			<div class="ep-debug ep-debug-footer">
				<?php echo $this->get_current_template() ?>:
				<?php echo $wpdb->num_queries; ?> <?php _e('queries'); ?>,
				generated in <?php timer_stop(1); ?> seconds, 
				<?php echo round( memory_get_peak_usage() / 1024 / 1024, 2 ) ?> MB peak memory usage.
			</div>
			<?php			
		});

	}


} // end class

$GLOBALS["ep"] = new EP();
$GLOBALS["ep"]->init();

