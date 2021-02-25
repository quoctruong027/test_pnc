<?php
$plugin_url = wp_nonce_url( add_query_arg( array(
	'action' => 'install-plugin',
	'plugin' => $plugin_slug,
	'from'   => 'import',
), self_admin_url( 'update.php' ) ), 'install-plugin_' . $plugin_slug );
 ?>

	<div class="updated" id="xl_notice_type_2" data-plugin="nextmove" data-plugin-slug="<?php echo $plugin_slug; ?>">
		<div class="xl_upsell_area">
			<div class="xl_skew_arow"></div>
			<div class="upsell_left_abs">
				<div class="xl_plugin_logo">
					<img src="<?php echo WFOCU_PLUGIN_URL . '/admin/assets/img/nextmove.png'; ?>" alt="NextMove Logo">
					<div id="plugin-filter" class="upsell_xl_plugin_btn plugin-card plugin-card-<?php echo $plugin_slug; ?>">
						<a v-on:click="wfocu_next_move_process($event)" class="install-now button" data-slug="<?php echo $plugin_slug; ?>" href="<?php echo $plugin_url; ?>" aria-label="Install WooCommerce Thank You Page – NextMove Lite"
						   data-name="Install WooCommerce Thank You Page – NextMove Lite">Install Next Move</a>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
