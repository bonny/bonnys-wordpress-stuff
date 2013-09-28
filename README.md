# Starter theme for WordPress

A starter theme that I (often) use when creating websites. It's not perfect, but it helps me to get started.


## What does it do?

- Adds some nice helper functions
- Removes unnecessary things from the dashboard, like "Other WordPress News", "QuickPress", "WordPress Blog", "Plugins" and "Incoming Links". Even WordPress themself says no one is using them: http://make.wordpress.org/ui/2013/08/21/3-8-dashboard-plugin/
- more...


## wp-config.php

```php
# Set WP_CONTENT_DIR to the full local path of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets' );

#Set WP_CONTENT_URL to the full URI of this directory (no trailing slash), e.g.
define( 'WP_CONTENT_URL', "http://" . $_SERVER["HTTP_HOST"] . '/assets');
```


- [] This
- [ ] That
- [x] Yup

