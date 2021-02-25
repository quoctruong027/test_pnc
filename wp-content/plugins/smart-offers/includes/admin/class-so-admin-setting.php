<?php
/**
 * Smart Offers Settings
 *
 * @author      StoreApps
 * @since       1.0.0
 * @version     1.1.0
 *
 * @package     smart-offers/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Setting' ) ) {

	class SO_Admin_Setting {

		function __construct() {
			$this->form = $this->so_process_settings_data();
			$this->so_display_settings();
		}

		public function so_display_settings() {

			$hidden_option     = $this->form['so_show_hidden_items'];
			$max_offer_to_show = $this->form['so_max_inline_offer'];
			$update_quantity   = $this->form['so_update_quantity'];

			$so_css_for_accept = get_option( 'so_css_for_accept' );
			$button_style_1    = $this->form['smart_offers_button_style_1'];
			$button_style_2    = $this->form['smart_offers_button_style_2'];
			$button_style_3    = $this->form['smart_offers_button_style_3'];
			$style_skip        = $this->form['so_css_for_skip'];
			?>

			<style type="text/css">
				.form-table th {
					width: 350px;
				}
				.accept_style_wrap {
					margin-left: 2em;
					margin-top: -1.5em;
					font-size: 0.8em;
					line-height: 1em;
				}
				:not(#custom_style).accept_style_wrap .accept_style_container .accept_button_holder a {
					padding: 0.4em 1em 0.4em 1em !important;
				}
				.accept_style_wrap .accept_style_container .accept_button_holder a {
					cursor: pointer;
				}
				#so_accept_button_styles_table,
				#custom_style_form_table {
					margin-left: -0.7em;
				}
				#custom_style {
					margin-top: -2.5em;
					margin-bottom: -1.4em;
				}
				#custom_style a {
					line-height: 1.2em !important;
					width: initial !important;
					height: initial !important;
				}
				#accept_style_1 {
					font-size: 1em;
					margin-top: -1.3em;
				}
			</style>

			<div class="wrap">
				<h1 class="wp-heading-inline">
					<?php echo __( 'Smart Offers Settings & Styles', 'smart-offers' ); ?>
				</h1>
				<form name="smart_offers_settings" id="so_form" method="POST" action="#">
					<table class='form-table'>
						<tbody>
							<tr>
								<th class="titledesc" style="font-size: 1.5em;"><?php echo __( 'Settings', 'smart-offers' ); ?></th>
								<td></td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Show offers for hidden product', 'smart-offers' ); ?></th>
								<td class="forminp">
									<select id="so_show_hidden_items" name="so_show_hidden_items">
										<option value="yes" <?php selected('yes', $hidden_option); ?> ><?php echo __( 'Yes', 'smart-offers' ); ?></option>
										<option value="no" <?php selected('no', $hidden_option); ?> ><?php echo __( 'No', 'smart-offers' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Multiple offers on page? Maximum offers to show...', 'smart-offers' ); ?></th>
								<td class="forminp">
									<input type="number" step="any" min="1" class="short" name="so_max_inline_offer" id="so_max_inline_offer" value="<?php echo $max_offer_to_show; ?>"> 
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Multiple offers on page? Show offer with...', 'smart-offers' ); ?></th>
								<td class="forminp">
									<fieldset>
										<input type="radio" name="so_if_multiple" id="high_price" <?php if (get_option('so_if_multiple') == "high_price") echo 'checked="checked"'; ?> value="high_price" />
										<label class="so_if_multiple" id="high_price" for="high_price"><?php echo __( 'Higher price', 'smart-offers' ); ?></label>
									</fieldset>
									<fieldset>
										<input type="radio" name="so_if_multiple" id="low_price" <?php if (get_option('so_if_multiple') == "low_price") echo 'checked="checked"'; ?> value="low_price" />
										<label class="so_if_multiple" id="low_price" for="low_price"><?php echo __( 'Lower price', 'smart-offers' ); ?></label>
									</fieldset>
									<fieldset>
										<input type="radio" name="so_if_multiple" id="random" <?php if (get_option('so_if_multiple') == "random") echo 'checked="checked"'; ?> value="random" />
										<label class="so_if_multiple" id="random" for="random"><?php echo __( 'Pick one randomly', 'smart-offers' ); ?></label>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Update offered product\'s quantity', 'smart-offers' ); ?></th>
								<td class="forminp">
									<label class="so_update_quantity">
										<input type="checkbox" id="so_update_quantity" name="so_update_quantity" class="checkbox" value="yes"
											<?php if ( $update_quantity == 'yes' ) echo 'checked="checked"'; ?>" />
										<span><?php echo __( 'Allow updating offered product\'s quantity in the cart after an offer is accepted.', 'smart-offers' ); ?></span>
									</label>
									<p class="description"><?php echo __( 'Disabling this will set fixed quantity as 1.', 'smart-offers' ); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" style="font-size: 1.5em;"><?php echo __( 'Styles', 'smart-offers' ); ?></th>
								<td></td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Styles for accept button', 'smart-offers' ); ?></th>
								<td class="forminp">
									<table id="so_accept_button_styles_table" class='form-table'>
										<tbody>
											<td class="forminp" width="100px">
												<input type="radio" name="so_accept_button_styles" id="smart_offers_button_style_1" width="100px"
												<?php 
													if (get_option('so_accept_button_styles') == 'smart_offers_button_style_1' ) {
														echo 'checked="checked"';
													}
												?> 
												value='smart_offers_button_style_1' />
												<div class="accept_style_wrap" id="accept_style_1">
													<div class="accept_style_container">
														<div class="accept_button_holder">
															<a style="<?php echo $button_style_1; ?>"><?php echo  __( 'Button Style 1', 'smart-offers' ); ?></a> 
														</div>
													</div>
												</div>
											</td>
											<td class="forminp" width="100px">
												<input type="radio" name="so_accept_button_styles"  id="smart_offers_button_style_2" width="100px"
												<?php 
													if (get_option('so_accept_button_styles') == 'smart_offers_button_style_2' ) {
														echo 'checked="checked"';
													}
												?>
												value='smart_offers_button_style_2' />
												<div class="accept_style_wrap" id="accept_style_2">
													<div class="accept_style_container">
														<div class="accept_button_holder">
															<a style="<?php echo $button_style_2; ?>"><?php echo  __( 'Button Style 2', 'smart-offers' ); ?></a> 
														</div>
													</div>
												</div>
											</td>
											<td class="forminp" width="100px">
												<input type="radio" name="so_accept_button_styles"  id="smart_offers_button_style_3" width="100px"
												<?php
													if (get_option('so_accept_button_styles') == 'smart_offers_button_style_3' ) {
														echo 'checked="checked"';
													}
												?>
												value='smart_offers_button_style_3' />
												<div class="accept_style_wrap" id="accept_style_3">
													<div class="accept_style_container">
														<div class="accept_button_holder">
															<a style="<?php echo $button_style_3; ?>"><?php echo  __( 'Button Style 3', 'smart-offers' ); ?></a> 
														</div>
													</div>
												</div>
											</td>
											<td class="forminp" width="100px">
												<input type="radio" name="so_accept_button_styles" id="smart_offers_custom_style_button" width="100px"
												<?php
													if ( get_option('so_accept_button_styles') == 'smart_offers_custom_style_button' ) {
														echo 'checked="checked"';
													}
												?>
												value='smart_offers_custom_style_button' />
												<div class="accept_style_wrap" id="custom_style">
													<div class="accept_style_container">
														<div class="accept_button_holder">
															<a style="<?php if ( ! empty( $so_css_for_accept ) ) { echo trim( stripslashes( $so_css_for_accept ) ); } ?>"><?php echo  __( 'Custom style', 'smart-offers' ); ?></a> 
														</div>
													</div>
												</div>
												<div id="custom_button" width="100px"></div>
											</td>
										</tbody>
									</table>
									<table id="custom_style_form_table">
										<tbody>
											<td>
												<div class="custom_style_form">
													<textarea name="so_css_for_accept" id="so_css_for_accept" rows="5" cols="90"><?php if ( ! empty( $so_css_for_accept ) ) { echo trim( stripslashes( $so_css_for_accept ) ); } ?></textarea>
												</div>
											</td>
										</tbody>
									</table>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row"><?php echo __( 'Styles for skip link', 'smart-offers' ); ?></th>
								<td class="forminp">
									<fieldset>
										<textarea name="so_css_for_skip" id="so_css_for_skip" rows="5" cols="90"><?php echo $style_skip; ?></textarea>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>
					<input type="hidden" name="so_settings_form_submit" value="yes"/>
					<p>
						<input type="submit" name="publish" id="es-save-settings" class="button-primary" value="<?php echo __( 'Save Settings', 'smart-offers' ); ?>" />
					</p>
					<?php wp_nonce_field( 'so_settings_form' ); ?>
				</form>
				<script type="text/javascript">
					jQuery(function() {

						var isShowCustomStyleTextArea = function( show ) {
							if ( show ) {
								jQuery('table#custom_style_form_table').slideDown();
							} else {
								jQuery('table#custom_style_form_table').slideUp();
							}
						};

						jQuery(document).ready(function() {
							var show = jQuery('#smart_offers_custom_style_button').is(':checked');
							isShowCustomStyleTextArea( show );
						});

						jQuery("input[name$='so_accept_button_styles']").on('click', function() {
							var radio_value = jQuery(this).val();
							var show = ( radio_value == 'smart_offers_custom_style_button' );
							isShowCustomStyleTextArea( show );
						});

						jQuery("#so_css_for_accept").on('keyup', function(){
							var textarea_value = jQuery(this).val();
							textarea_value = jQuery.trim( textarea_value );
							if ( textarea_value == '' ) {
								jQuery('#custom_style').css('margin', '-1.3em 0 0 2em');
								jQuery('#custom_style').css('font-size', '1em');
							} else {
								jQuery('#custom_style').css('margin', '-2.5em 0 -1.4em 2em');
								jQuery('#custom_style').css('font-size', '0.8em');
							}
							jQuery("#custom_style a").attr('style',textarea_value);
						});

						jQuery('.accept_style_wrap .accept_style_container .accept_button_holder a').on('click', function(){
							var target_element = jQuery(this).closest('td').find('input[name="so_accept_button_styles"]');
							var target_value = target_element.val();
							var show = ( target_value == 'smart_offers_custom_style_button' );
							target_element.attr('checked', 'checked');
							isShowCustomStyleTextArea( show );
						});

					});
				</script>
				<table class='form-table'>
					<tbody>
						<tr>
							<th class="titledesc"><?php echo __( 'Re-import ready offers?', 'smart-offers' ); ?></th>
							<td></td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row" style="width: 610px; font-weight: normal;">
								<?php echo __( 'Smart Offers has 5 designed and ready-to-use offers. By default, those offers are already added.', 'smart-offers' ); ?><br>
								<?php echo __( 'Incase you have deleted them and want to re-import, click this button', 'smart-offers' ); ?>
							</th>
							<td class="forminp">
								<a class="button-primary" id="so-ready-offer-designs" href="<?php echo admin_url('edit.php?post_type=smart_offers&page=so-about&action=so-import'); ?>">
									<?php echo __( 'Import offers now', 'smart-offers' ); ?>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php

		}

		public function so_process_settings_data() {

			$so_errors = array();
			$so_success = '';
			$so_error_found = FALSE;

			$form = array();

			$form['so_show_hidden_items'] = get_option('so_show_hidden_items');
			$form['so_if_multiple']       = get_option('so_if_multiple');
			$form['so_max_inline_offer']  = get_option('so_max_inline_offer');
			$form['so_update_quantity']   = get_option('so_update_quantity');
			
			$form['smart_offers_button_style_1'] = get_option('smart_offers_button_style_1');
			$form['smart_offers_button_style_2'] = get_option('smart_offers_button_style_2');
			$form['smart_offers_button_style_3'] = get_option('smart_offers_button_style_3');
			$form['so_css_for_accept']           = get_option('so_css_for_accept');
			$form['so_css_for_skip']             = get_option('so_css_for_skip');

			// Form submitted, check & update the data in options table
			if ( isset( $_POST['so_settings_form_submit'] ) && $_POST['so_settings_form_submit'] == 'yes' ) {
				// Just security thingy that wordpress offers us
				check_admin_referer('so_settings_form');

				// Fetch submitted Signup Configuration data
				$form['so_show_hidden_items']    = isset( $_POST ['so_show_hidden_items'] ) && $_POST ['so_show_hidden_items'] == 'yes' ? 'yes' : 'no';
				$form['so_if_multiple']          = isset( $_POST ['so_if_multiple'] ) ? $_POST ['so_if_multiple'] : '';
				$form['so_max_inline_offer']     = isset( $_POST ['so_max_inline_offer'] ) ? $_POST ['so_max_inline_offer'] : '';
				$form['so_update_quantity']      = isset( $_POST ['so_update_quantity'] ) && $_POST['so_update_quantity'] == 'yes' ? 'yes' : 'no';
				$form['so_accept_button_styles'] = isset( $_POST ['so_accept_button_styles'] ) ? $_POST ['so_accept_button_styles'] : '';
				$form['so_css_for_accept']       = isset( $_POST ['so_css_for_accept'] ) ? $_POST ['so_css_for_accept'] : '';
				$form['so_css_for_skip']         = isset( $_POST ['so_css_for_skip'] ) ? $_POST ['so_css_for_skip'] : '';

				// No errors found, we can add the settings to the options
				if ( $so_error_found == FALSE ) {
					$action = "";
					$action = $this->so_settings_update( $form );
					if( $action == "sus" ) {
						$so_success = __( 'Your settings have been saved..', 'smart-offers' );
					} else {
						$so_error_found == TRUE;
						$so_errors[] = __( 'Oops, unable to update.', 'smart-offers' );
					}
				}
			}

			if ( $so_error_found == TRUE && isset($so_errors[0]) == TRUE ) {
				?><div class="error fade">
					<p><strong>
						<?php echo $so_errors[0]; ?>
					</strong></p>
				</div><?php
			}

			if ( $so_error_found == FALSE && strlen($so_success) > 0 ) {
				?><div class="notice notice-success is-dismissible">
					<p><strong>
						<?php echo $so_success; ?>
					</strong></p>
				</div><?php
			}

			return $form;
		}

		public function so_settings_update( $form = '' ) {
			if ( ! empty( $form ) ) {
				foreach ( $form as $key => $value ) {
					update_option( $key, $value, 'no' );
				}
			}

			return 'sus';
		}

	}
}

new SO_Admin_Setting();
