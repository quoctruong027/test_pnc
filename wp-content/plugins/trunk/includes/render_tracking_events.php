<script type="text/javascript">
<?php foreach ($this->events_queue as $event) : ?>
	<?php if ('track' == $event['method']) : ?>
		<?php if (isset($event['event'])) : ?>
			richpanel.track("<?php echo esc_html_e($event['event']); ?>", <?php echo isset($event['properties']) ? wp_json_encode($event['properties']) : 'null'; ?>, <?php echo isset($event['userProperties']) ? wp_json_encode($event['userProperties']) : 'null'; ?>);
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; ?>
</script>
