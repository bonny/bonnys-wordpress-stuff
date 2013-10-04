<?php

namespace EP\oembed_wrap_div;

/**
 * Wrap a div around each oembeded object.
 *
 * Div gets classes so we can target all oembed divs or just from a specific provider.
 */
function ep_wrap_div_around_oembed( $output, $data, $url ) {

	$return = '<div class="oembed oembed--' . $data->provider_name . '">'.$output.'</div>';

	return $return;
 
}
add_filter('oembed_dataparse', __NAMESPACE__ . '\ep_wrap_div_around_oembed', 90, 3 );
