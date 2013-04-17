<?php
global $ep;
?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="<?php echo get_template_directory_uri() ?>/js/libs/jquery-1.8.2.min.js"><\/script>')</script>
		<?php wp_footer(); ?>
		<!--[if (gte IE 6)&(lte IE 8)]>
			<script src="<?php echo get_template_directory_uri() ?>/js/jquery-extra-selectors.js"></script>
			<script src="<?php echo get_template_directory_uri() ?>/js/selectivizr.js"></script>
		<![endif]-->

<!--
<?php echo $wpdb->num_queries; ?> <?php _e('queries'); ?>. <?php timer_stop(1); ?> <?php _e('seconds'); ?>
<?php echo "\nTemplate: " . $ep->get_current_template() . "\n" ?>
-->
	</body>
</html>