<?php

/**
 * Modify login screen:
 *  - remove link to wordpress.org
 *  - add support for local client image above login fields
 */

namespace EP\admin\login_screen;

/*
 * Change url of login logo to the url of the site
 * If not then there is a risk that the user gets lost at th wordpress.org site
 */
add_action("login_headerurl", function($url) {
	$url = home_url();
	return $url;
});

/**
 * Change title of a element on logo to say that link goes to homepage of current site
 */
add_action("login_headertitle", function($title) {
	$title = sprintf( __("Back to %s"), home_url() );
	return $title;
});

/*
add_action("login_message", function($message) {
	$message = "Welcome! This site is proudly made by Earth People.";
	return $message;
});
*/

/**
 * Add client logo, if login-client-logo.png exists in theme folder
 */
add_action("login_head", function() {

	$image_filename = "login-client-logo.png";
	$image_and_path = trailingslashit( get_stylesheet_directory() ) . $image_filename;
	
	if ( file_exists( $image_and_path ) ) {
		$image_uri = trailingslashit( get_stylesheet_directory_uri() ) . $image_filename;
		?>
		<style>
			.login h1 a {
				background-image: url(<?php echo $image_uri; ?>);
			}
		</style>
		<?php
	}

});
	
