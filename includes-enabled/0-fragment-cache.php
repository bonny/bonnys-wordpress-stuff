<?php
/**
 * As found here
 * https://github.com/ryanburnette/fragment-cache
 * Usage:
 *  <?php echo fragment_cache('my_block_tag', DAY_IN_SECONDS, function() { ?>
 * 
 * [...]
 * 
 * <?php } ?>
 *
 * "Clearing the cache is why I have a filter attached to the line where the $key variable is created. I attach a randomly generated number to the key, then have a button I put in the webmaster’s admin area that says “clear cache.” By clicking it they are regenerating the randomly generated number. The old transients are cleared upon timeout. Deleting the transient works, but that would require keeping up with what transients you are creating and deleting those or some other fanciness. I just use the random number because it’s quick and easy.
 *
 * I also have a couple lines in there that bypasses the whole function if the current user is logged in. I usually develop while logged into WordPress. So, that’s why that is there. It’s easy enough to remove those lines if they are not needed.""
  *
  * Notes:
  * - When W3TC is installed and using Object Cache then this cache also gets flushed during object cache flush, and also when a page is saved.
  * - When using no object cache = using plan old school transients then fragment cache does not get flushed using save, so that's why we must increment cache key
  *
  * @param string $key
  * @param int $ttl
  * @param callable $function to execute
  * @return string output buffered during execution of callable
  */
function fragment_cache($key, $ttl, $function) {

	$key = apply_filters('fragment_cache_prefix','fragment_cache_') . $key;
	$output = get_transient($key);
	
	if ( false === $output ) {

		ob_start();

		if ( is_callable($function) ) {
			call_user_func($function);
		}

		$output = ob_get_clean();

		set_transient($key, $output, $ttl);

	}

	return $output;

}

/**
 * Append cache incrementor.
 * Default is 0. Updated with fragment_cache_incr() on save_post and so on.
 */
add_filter("fragment_cache_prefix", function($key) {

	$key_incrementor = get_option( "fragment_cache_key_incrementor", 0 );
	$key = "{$key}{$key_incrementor}";
	
	return $key;

});


// Actions to increment cache on
$fragment_cache_increment_actions = array(
	"clean_post_cache",
	"comment_post",
	"edit_comment",
	"delete_comment",
	"wp_set_comment_status",
	"trackback_post",
	"pingback_post"
);

foreach ( $fragment_cache_increment_actions as $one_action ) {

	add_action( $one_action, function() {
		fragment_cache_incr();
	} );

}

function fragment_cache_incr() {
	update_option( "fragment_cache_key_incrementor", time() );
}

// test cache
/*
add_filter("the_content", function($content) {

	$my_content = "<div class='clearfix' style='background:lightyellow;padding:1em'>fragment cache test";

	// do something slow
	$my_content .= fragment_cache("test5", HOUR_IN_SECONDS, function() {
		$more_content = "<p>Sleeping for 5 seconds</p>";
		sleep(5);
		$more_content .= "<p>Done sleeping at " . date("Y-m-d H:i:s") . "!";
		echo $more_content;
	});

	$my_content .= "</div>";

	return $my_content . $content;

});
*/
