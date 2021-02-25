<?php if ($this->has_events_in_cookie) : ?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$.get("<?php echo esc_html_e(add_query_arg('richpanel_clear', 1)); ?>", function(response) {  });
	});
	</script>
<?php endif; ?>
