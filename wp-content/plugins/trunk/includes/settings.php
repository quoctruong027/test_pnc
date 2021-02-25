<div class="wrap">
	<h2><?php esc_html_e( 'Richpanel Settings', 'richpanel' ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'richpanel' );
		do_settings_sections( 'richpanel' );
		submit_button();
		?>
	</form>
</div>
