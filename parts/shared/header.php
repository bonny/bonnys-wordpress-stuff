
<header>
	<h1><a href="/"><?php bloginfo( 'name' ); ?></a></h1>
	<?php bloginfo( 'description' ); ?>
	<?php get_search_form(); ?>

	<nav class="nav nav--primary">
		<h2>Menu</h2>
		<?php
		echo "<ul>";
		wp_list_pages( array( 'depth' => 2,'sort_column' => 'menu_order','title_li' => '' ) );
		echo "</ul>";
		?>
	</nav>
</header>
