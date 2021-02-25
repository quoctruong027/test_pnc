<?php
/**
 * html code for settings tab
 */
?>
<section id="content2" class="tab_section">
	<div class="tab_inner_container">
		<form method="post" id="wc_ast_settings_form" action="" enctype="multipart/form-data">
			<?php #nonce?>
			<div class="outer_form_table border_0">		
				<table class="form-table heading-table">
					<tbody>
						<tr valign="top">
							<td>
								<h3 style=""><?php _e( 'General Settings', 'woo-advanced-shipment-tracking' ); ?></h3>
							</td>
						</tr>
					</tbody>
				</table>
				<?php $this->get_html( $this->get_settings_data() );?>						
				<hr>
				<div class="submit">								
					<button name="save" class="button-primary woocommerce-save-button btn_ast2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
					<div class="spinner"></div>								
					<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' );?>
					<input type="hidden" name="action" value="wc_ast_settings_form_update">
				</div>
			</div>			
		</form>		
		<div class="border_0">						
			<table class="form-table settings-form-table">
				<tbody>
					<tr valign="top">
						<th class="titledesc">
							<label><?php _e( 'Tracking Display Customizer', 'woo-advanced-shipment-tracking' ); ?></label>
						</th>
						<td class="tracking-info-customizer-td">
							<table class="form-table">
								<tr>
									<td>
										<span style=""><?php _e( 'Use a customizer with a preview to customize the tracking info display on customer order emails and my-account.', 'woo-advanced-shipment-tracking' ); ?></span>
									</td>
									<td>
										<a href="<?php echo wcast_initialise_customizer_settings::get_customizer_url('ast_tracking_display_panel','settings') ?>" class="button-primary btn_ast_transparent btn_large launch_customizer_btn"><?php _e( 'Launch Customizer', 'woo-advanced-shipment-tracking' ); ?></a>
									</td>
								</tr>	
							</table>
						</td>							
					</tr>
					<tr>
						<th class="titledesc">
							<label><?php _e( 'Custom Order Statuses', 'woo-advanced-shipment-tracking' ); ?></label>
						</th>
						<td class="custom-order-statuses-td">
							<?php require_once( 'admin_options_osm.php' ); ?>		
						</td>	
					</tr>
				</tbody>
			</table>
		</div>									
		<?php do_action('ast_generat_settings_end'); ?>	
	</div>	
	<?php //include 'zorem_admin_sidebar.php';?>
</section>