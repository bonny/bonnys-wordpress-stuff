<?php

namespace EP\frontend\oembed_wrap_div;

/**
 * Wrap a div around each oembeded object.
 *
 * Div gets classes so we can target all oembed divs or just from a specific provider.
 *
 * For example soundcloud will get this div around the embedded iframe:
 * <div class="oembed oembed--SoundCloud">
 */
function wrap_div_around_oembed( $output, $data, $url ) {

	$return = '<div class="oembed oembed--' . $data->provider_name . '">'.$output.'</div>';

	return $return;
 
}
add_filter('oembed_dataparse', __NAMESPACE__ . '\wrap_div_around_oembed', 90, 3 );

/**
 * Also modifies Jetpack's embed for youtube and vimeo
 */
function wrap_div_around_jetpack_video($html) {
	/*
	HTML looks like this:
	<p><span class='embed-youtube' style='text-align:center; display: block;'><iframe class='youtube-player' type='text/html' width='640' height='390' src='http://www.youtube.com/embed/Q3994QWoKzE?version=3&#038;rel=1&#038;fs=1&#038;showsearch=0&#038;showinfo=1&#038;iv_load_policy=1&#038;wmode=transparent' frameborder='0'></iframe></span></p>
	*/
	$html = str_replace("class='embed-youtube'", "class='embed-youtube oembed oembed--YouTube'", $html);
	$html = str_replace("class='embed-vimeo'", "class='embed-youtube oembed oembed--Vimeo'", $html);
	return $html;
}
add_filter( 'video_embed_html', __NAMESPACE__ . '\wrap_div_around_jetpack_video' );

