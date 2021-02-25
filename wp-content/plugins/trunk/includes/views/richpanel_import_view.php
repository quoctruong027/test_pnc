<div class="welcome-panel">
	<div style="float: left; width: 120px; text-align: center; padding-top: 30px;">
		<img src="https://cdn.jetcommerce.io/wp-content/uploads/sites/11/2018/04/15150526/High-Res-Logo-Icon-Blue.png" style="width: 80px;" />
	</div>
	<div style="float: left;">
	<h3>Importing your orders and customers to Richpanel</h3>
	<p>
		This tool helps you sync all your orders/subscription (and their respective customers) to Richpanel and can take <strong>up to 20 minutes</strong> to complete. <br />
		It will not affect your website's performance at all since it sends your orders to your Richpanel account in small chunks.  <br /><br />
		  Make sure to <strong>not close this page</strong> while importing. Coffee, maybe?
	</p>
	<?php if ($this->importing) : ?>
		<script type="text/javascript">
		jQuery(document).ready(function($){

			var chunk_pages = <?php echo esc_html_e($this->chunk_pages_order); ?>;
			var chunk_percentage = 100;
			if(chunk_pages > 0){
				var chunk_percentage = (100 / chunk_pages);
			}
			var sync_chunk = function(chunk_page){
				progress_percents = Math.round(chunk_page * chunk_percentage);
				update_importing_message('Please wait... '+progress_percents+'% done');

				$.post("<?php echo esc_html_e(admin_url('admin-ajax.php')); ?>", {'action': 'richpanel_chunk_sync', 'chunk_page': chunk_page}, function(response) {

					new_chunk_page = chunk_page + 1;
					if(new_chunk_page <= chunk_pages){
						setTimeout(function(){
							sync_chunk(new_chunk_page);
						}, 900);
					}else{
						update_importing_message("<span style='color: green;'>Done! Please expect up to 30 minutes for your historical data to appear in Richpanel.</span>");
					}

				});

			}

			var update_importing_message = function(message){
				$('#richpanel_import_status').html(message);
			}

			sync_chunk(0);

		});
		</script>
		<strong id="richpanel_import_status">Syncing...</strong>
	<?php elseif ($this->s_importing) : ?>
		<script type="text/javascript">
		jQuery(document).ready(function($){

			var chunk_pages = <?php echo esc_html_e($this->chunk_pages_subscription); ?>;
			var chunk_percentage = 100;
			if(chunk_pages > 0){
				var chunk_percentage = (100 / chunk_pages);
			}
			var sync_chunk = function(chunk_page){
				progress_percents = Math.round(chunk_page * chunk_percentage);
				update_importing_message('Please wait... '+progress_percents+'% done');

				$.post("<?php echo esc_html_e(admin_url('admin-ajax.php')); ?>", {'action': 'richpanel_subscriptions_sync', 'chunk_page': chunk_page}, function(response) {

					new_chunk_page = chunk_page + 1;
					if(new_chunk_page <= chunk_pages){
						setTimeout(function(){
							sync_chunk(new_chunk_page);
						}, 900);
					}else{
						update_importing_message("<span style='color: green;'>Done! Please expect up to 30 minutes for your historical data to appear in Richpanel.</span>");
					}

				});

			}

			var update_importing_message = function(message){
				$('#richpanel_import_status').html(message);
			}

			sync_chunk(0);

		});
		</script>
		<strong id="richpanel_import_status">Syncing...</strong>
	<?php else : ?>
		<a href="<?php echo esc_html_e(admin_url('tools.php?page=richpanel-import&import=1')); ?>" class="button" style="margin: 2px;">
			<strong>Sync <?php echo esc_attr($this->orders_total); ?> orders now</strong>
		</a>
		<a href="<?php echo esc_html_e(admin_url('tools.php?page=richpanel-import&simport=1')); ?>" class="button" style="margin: 2px;">
			<strong>Sync <?php echo esc_attr($this->subscription_total); ?> subscription now</strong>
		</a>
	<?php endif; ?>
	</div>
<br style="clear: both;" />
<br />
</div>
<div style="color: #888; font-size: 11px; padding: 5px;">
	If you encounter any issues, let us know at <a href="mailto:support@richpanel.com">support@richpanel.com</a>. We'll be happy to assist you!
</div>
