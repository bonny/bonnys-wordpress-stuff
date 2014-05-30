# Starter theme for WordPress

A starter theme that I (often) use when creating WordPress based websites. It's not perfect, but it helps me to get started.

## Table of Contents

- [What it does](#what-it-does)
- [Helper functions](#helper-functions)
- [wp-config.php](#wp-configphp)
- [Recommended plugins](#recommended-plugins)
- [Maybe todo](#maybe-todo)

## What it does

* Adds some nice [helper functions](#helper-functions).

* Removes all meta boxes from the dashboard, except the "Right now"-box. Even WordPress themself says [no one is using those other meta boxes](http://make.wordpress.org/ui/2013/08/21/3-8-dashboard-plugin/).

* `wp-content` is optional renamed to `assets`. This is to make it easier to keep the full WP-folder out of GIT.

* Hides things in the `head`, like WordPress generator. Makes the site less "bloggy".

* Make links added in the WYSIWYG-editor relative instead of absolute, so when moving from av test/staging server all links won't go to the wrong URL.

* Does not contain to much things in the templates, since it all depends on the site you're doing anyway.

* Automatically loads include files for directory `includes-enabled`. Just place your files there and they will be loaded. We call this *dropins*. We like this system beacause you can push code to remote servers without the need to activate something to make it run. When using plugins, you need to activate the plugin.

* Uses [SUIT naming conventions for CSS](https://github.com/suitcss/suit/blob/master/doc/components.md)

* Finds last modified time of css and js so you easily can append those for cachebusting

* style.css is prepared with the CSS from the HTML5 Boilerplate (including normalize.css)
 
* Client logo on login screen
  Add a file in your theme root called `login-client-logo.png` and it will be shown instead of the WordPress default logo.

## Helper functions

A collection of functions that I find useful:

@TODO: document these better, with examples. Only keep the ones I use.

```php
<?php

/**
 * Get menu from top item
 * @param string $menu_name
 * @return nav item object
 */
function ep_get_nav_menu_top_parent($menu_name){}

/**
 * Get the parent nav item
 * @param $single_menu_item The nav item to look for parent to
 * @param $menu_items array with all nav items, as gotten from wp_get_nav_menu_items
 * @return nav item
 */
function ep_get_nav_item_parent($single_menu_item, $menu_items) {}

/**
 * WordPress WP_QUERY-wrapper to simplify getting and working with posts
 *
 * Does something with posts, using a callback
 * Setups global post variable before running callback
 * And restores it afterwards
  *
 * Examples:
 * https://gist.github.com/bonny/5005579
 *
 * @author Pär Thernstrom <https://twitter.com/eskapism>
 * @param ID, array, string, WP POST, WP_QUERY
 * @param $do callable Function to run for each matching post
 * @param bool $buffer_and_return_output True if output should be buffered and returned
 * @return Mixed Returns the return value of the $do function
 */
function with_posts($post_thing, $do, $buffer_and_return_output = false) {}

/**
 * EarthPeople debug
 * För jag gillar att skriva ut variabler...
 */
function ep_d($var) {}

/**
 * Super Small and Simple WP Template thingie
 * Because I'm tired of throwing global posts around and using setuppostdata 1000 frickin times on a site.
 *
 * Usage:
 *
 * echo ep_get_post(19, "<div class='post-content'>%%CONTENT%%</div>");
 *
 * Which is so much shorter than (or other similar method):
 * $address_post_id = 19;
 * $adress_post = get_post($address_post_id);
 * $adress_content = $adress_post->post_content;
 * $adress_content = apply_filters('the_content', $adress_content);
 * echo "<div class='post-content'>" . $adress_content . "</div>";
 */
function ep_get_post($post_id_or_args, $format) {}

// lägg på siddjup i body + om aktuellt artikel har barn
function ep_body_class($classes, $class) {}

function ep_post_get_first_child() {}

// ger antal barn till den post som just nu visas
function ep_post_childcount() {}

/**
 * Tell how deep down in the hierachy we are
 * @return int depth
 */
function ep_post_depth() {}

/**
 * This is a nifty little finction that makes is possible to
 * format teaser and body differently
 * 
 * It will output:
 * - teaser/text before the read-more-thingie wrapped in div.post-teaser
 * - body/text after the read-more-thingie wrapped in div.post-body
 * - ..but only if each one exists. so you can get teaser + body, or only teaser, or only body
 *
 * @author Pär Thernström 
 *
 */
function ep_teaser_and_body($post_id = NULL) {}

/**
 * Get teaser,
 * but if teaser does not exist get body instead
 * Good for listing/overview views
 */
function ep_get_teaser_or_body() {}

/**
 * Returns both teaser and body
 * Each wrapped in a <div> with class post-teaser and post-body
 * If any of them don't exist, their <div> won't be outputed
 *
 * @author Pär Thernström, November, 2010
 */
function ep_get_teaser_and_body($post_id = NULL) {}

// Filter wp_title() to add the ancenstors
// So for example the title "Medarbetare | Företaget AB" will become "Medarbetare | Om Oss | Företaget AB"
// Do the title modification
// Code from plugin "Breadcrumb Titles For Pages"
// Modified by Pär Thernström
function page_breadcrumb_wptitle( $title, $sep, $seplocation = null ) {}

/**
 * check if current page/post is a child post of page/post with id $page_id
 * @param int $page_id
 * @return bool
 */
function ep_is_child_of($page_id) {}

/*
	check if current page is a subpage
	if it is, the parent page id is returned
	if not, false is returned
*/
function ep_is_subpage() {}

/**
 * Hämtar alla innehåll i content
 * eller bara teaser, eller bara body
 *
 * @param what to get all (default) | teaser | body
 * @return string
 */
function ep_get_the_content($what = "all") {}

/**
 * Returns the content with more tag activated, for global post of for $post if supplied
 * @param string $read_more_string
 * @param post | id $post_arg
 */
function ep_get_the_content_force_more($read_more_string = "", $post_arg = null) {}


```

## wp-config.php

Some things to have in your wp-config.php:

Add this to your `wp-config.php`-file to use `/assets/` instead of `/wp-content/`.

```php

# Set WP_CONTENT_DIR to the full local path of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_DIR', dirname(__FILE__) . '/assets' );

# Set WP_CONTENT_URL to the full URI of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_URL', "http://" . $_SERVER["HTTP_HOST"] . '/assets');

```

```php

// Use this to dynamically set home and siteurls
define('WP_HOME', "http://" . $_SERVER["SERVER_NAME"] . "");
define('WP_SITEURL', "http://" . $_SERVER["SERVER_NAME"] . "/wp");

```

## Recommended plugins

* [Query Monitor](http://wordpress.org/plugins/query-monitor/)
* [Simple Fields](https://wordpress.org/plugins/simple-fields/)
* [Simple History](https://wordpress.org/plugins/simple-history/)
* [Assets Minify](http://wordpress.org/plugins/assetsminify/)
* [Better WordPress Minify](http://wordpress.org/plugins/bwp-minify/)
* [Autoptimize](http://wordpress.org/plugins/autoptimize/)
* [EWWW Image Optimizer](http://wordpress.org/plugins/ewww-image-optimizer/)
* [InfiniteWP Client](http://wordpress.org/plugins/iwp-client/)
* [W3 Total cache](http://wordpress.org/plugins/w3-total-cache/)

## Maybe todo

- show dates in user list
    - http://plugins.svn.wordpress.org/recently-registered/trunk/recently-registered.php
