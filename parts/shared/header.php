
<header>
	<h1><a href="/"><?php bloginfo( 'name' ); ?></a></h1>
	<?php bloginfo( 'description' ); ?>
	<?php get_search_form(); ?>

	<h2>Menu</h2>
	<?php
	wp_list_pages( array( 'depth' => 1,'sort_column' => 'menu_order','title_li' => '' ) );
	?>
</header>
