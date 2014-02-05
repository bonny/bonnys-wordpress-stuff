# Starter theme for WordPress

A starter theme that I (often) use when creating WordPress based websites. It's not perfect, but it helps me to get started.


## What does it do?

- Adds some nice helper functions
- Removes all meta boxes from the dashboard, except the "Right now"-box. Even WordPress themself says [no one is using those other meta boxes](http://make.wordpress.org/ui/2013/08/21/3-8-dashboard-plugin/).
- wp-content is optional renamed to assets
- Hides things in the ```head```, like WordPress generator. Makes the site less "bloggy" and hopefully a visitor won't go "oh, it's a wordpress site...!"
- Make links added in the WYSIWYG-editor relative instead of absolute, so when moving from av test/staging server all links won't go to the wrong URL
- Does not contain to much things in the templates, since it all depends on the site you're doing anyway.
- Automatically loads include files for directory ``ìncludes-enabled```. Just place your files there and they will be loaded.
- uses [SUIT naming conventions for CSS](https://github.com/suitcss/suit/blob/master/doc/components.md)
- finds last modified time of css and js so you easily can append those for cachebusting
- style.css is prepared with the CSS from the HTML5 Boilerplate (including normalize.css)

## wp-config.php

Add this to your ```wp-config.php```-file to rename use ```/assets/``` instead of ```/wp-content/``.

```php

# Set WP_CONTENT_DIR to the full local path of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_DIR', dirname(__FILE__) . '/assets' );

#Set WP_CONTENT_URL to the full URI of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_URL', "http://" . $_SERVER["HTTP_HOST"] . '/assets');

```


# Generella funderingar

- mappan där man lägger filer och de automagiskt körs. kanske mapp "plugins-enabled", "plugins-disabled". Ref apaches configs. dock inte plugins pga är inte plugins. extension-available, extensions-enabled
- enkelt, överskådligt. inte göra för mkt, men ge många bra grunder och funktioner etc.
- gömma wp så mkt som möjligt. iaf inte fronta med det.

- ta bort id på wp_nav_menu som standard, för mer clean kod

– add_post_types » add_custom_post_types för tydlighet & sökbarhet
– exampelposttypes för diverse saker, t.ex. "texts" som jag ofta använder
    – bättre supports, t.ex. template
– sfeed_edit etc. inte använda inline css
– lägg in dummy template?

- datum i användartabellen:
    - http://plugins.svn.wordpress.org/recently-registered/trunk/recently-registered.php


// find and include files in bugs directory
$files = glob( get_stylesheet_directory() . "/bugs/*");
foreach ($files as $filepath) {
	include_once($filepath);
}
