# Starter theme for WordPress

A starter theme that I (often) use when creating WordPress based websites. It's not perfect, but it helps me to get started.

## What it does

* Adds some nice helper functions. (Todo: create page with info.)

* Removes all meta boxes from the dashboard, except the "Right now"-box. Even WordPress themself says [no one is using those other meta boxes](http://make.wordpress.org/ui/2013/08/21/3-8-dashboard-plugin/).

* `wp-content` is optional renamed to `assets`. This is to make it easier to keep the full WP-folder out of GIT.

* Hides things in the `head`, like WordPress generator. Makes the site less "bloggy".

* Make links added in the WYSIWYG-editor relative instead of absolute, so when moving from av test/staging server all links won't go to the wrong URL.

* Does not contain to much things in the templates, since it all depends on the site you're doing anyway.

* Automatically loads include files for directory `includes-enabled`. Just place your files there and they will be loaded. We call this *dropins*. We like this system beacause you can push code to remote servers without the need to activate something to make it run. When using plugins, you need to activate the plugin.

* Uses [SUIT naming conventions for CSS](https://github.com/suitcss/suit/blob/master/doc/components.md)

* Finds last modified time of css and js so you easily can append those for cachebusting
- style.css is prepared with the CSS from the HTML5 Boilerplate (including normalize.css)

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

## Maybe todo

- show dates in user list
    - http://plugins.svn.wordpress.org/recently-registered/trunk/recently-registered.php

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
