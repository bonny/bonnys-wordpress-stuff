<?php
global $ep;
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo get_template_directory_uri() ?>/js/jquery/jquery.min.js"><\/script>')</script>

<!--[if (gte IE 6)&(lte IE 8)]>
	<script src="<?php echo get_template_directory_uri() ?>/js/jquery-extra-selectors.js"></script>
	<script src="<?php echo get_template_directory_uri() ?>/js/selectivizr.js"></script>
<![endif]-->

<?php wp_footer(); ?>

<div class="ep-debug ep-debug-footer">
<?php echo $ep->get_current_template() ?>:
<?php echo $wpdb->num_queries; ?> <?php _e('queries'); ?>,
generated in <?php timer_stop(1); ?> seconds, 
<?php echo round( memory_get_peak_usage() / 1024 / 1024, 2 ) ?> MB peak memory usage.
</div>

</body>
</html>