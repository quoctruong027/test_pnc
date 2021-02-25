<!-- <script type="text/javascript"> -->
console.log('Setting cookie');
<?php foreach ($this->cookie_to_set as $cookie) : ?>
	console.log('Setting cookie in loop');
	<?php if (isset($cookie['key'])) : ?>
		console.log('Setting cookie in if');
		window.richpanelCookie.set("<?php echo esc_html_e($cookie['key']); ?>", "<?php echo esc_html_e($cookie['value']); ?>", {
			expires: <?php echo esc_html_e($cookie['expiry']); ?>,
			domain: "<?php echo esc_html_e($cookie['domain']); ?>",
			path: "<?php echo esc_html_e($cookie['path']); ?>",
			secure: false
		})
	<?php endif; ?>
<?php endforeach; ?>
<!-- </script> -->
