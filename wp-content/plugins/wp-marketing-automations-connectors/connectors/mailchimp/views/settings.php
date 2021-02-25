<?php
$saved_data     = WFCO_Common::$connectors_saved_data;
$old_data       = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$api_key        = isset( $old_data['api_key'] ) ? $old_data['api_key'] : '';
$selected_list  = isset( $old_data['default_list'] ) ? $old_data['default_list'] : '';
$selected_store = isset( $old_data['default_store'] ) ? $old_data['default_store'] : '';

?>
<div class="wfco-form-group featured field-input">
	<label for="automation-name"><?php echo esc_html__( 'Enter API Key', 'autonami-automations-connectors' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<input type="text" name="api_key" placeholder="<?php echo esc_attr__( 'Enter API Key', 'autonami-automations-connectors' ); ?>" class="form-control wfco_mailchimp_api_key" required value="<?php echo esc_attr__( $api_key ); ?>">
		</div>
	</div>
</div>
<div class="wfco-form-group featured field-input wfco_mailchimp_select_list_box">
	<label for="automation-name"><?php echo esc_html__( 'Select Default List', 'autonami-automations-connectors' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<select name="default_list" class="wfco_mailchimp_select_list form-control">
				<option value=""><?php echo esc_html__( 'Choose a list', 'autonami-automations-connectors' ); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="wfco-form-group featured field-input wfco_mailchimp_select_store_box">
	<label for="automation-name"><?php echo esc_html__( 'Select Default Store', 'autonami-automations-connectors' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<select name="default_store" class="wfco_mailchimp_select_store form-control">
				<option value=""><?php echo esc_html__( 'Choose a Store', 'autonami-automations-connectors' ); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="wfco-form-groups wfco_form_submit wfco_mailchimp_next_step">
	<button class="wfco_save_btn_style wfco_mailchimp_fetch_lists"><?php echo esc_html__( 'Next Step', 'autonami-automations-connectors' ); ?></button>
</div>
<div class="wfco-form-groups wfco_form_submit wfco_mailchimp_main_submit">
	<?php
	if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
		?>
		<input type="hidden" name="edit_nonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
		<input type="hidden" name="id" value="<?php echo esc_attr__( $old_data['id'] ); ?>"/>
		<input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
		<input type="submit" class="wfco_update_btn_style wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Update', 'autonami-automations-connectors' ); ?>">
	<?php } else { ?>
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr__( wp_create_nonce( 'wfco-connector' ) ); ?>">
		<input type="hidden" name="wfco_connector" value="<?php echo esc_attr__( $this->get_slug() ); ?>"/>
		<input type="submit" class="wfco_save_btn_style" name="autoresponderSubmit" value="<?php echo esc_attr__( 'Save', 'autonami-automations-connectors' ); ?>">
	<?php } ?>
</div>
<div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>

<script>
	(function ($) {
		$(document).ready(function () {
			$('.wfco_mailchimp_main_submit').hide();
			$('.wfco_mailchimp_select_list_box').hide();
			$('.wfco_mailchimp_select_store_box').hide();

			if (!_.isEmpty($('.wfco_mailchimp_api_key').val())) {
				fetch_lists_with_update_ui();
			}

			$('body').on('click', '.wfco_mailchimp_fetch_lists', fetch_lists_with_update_ui);
		});

		function fetch_lists_with_update_ui() {
			if ($('.wfco_mailchimp_select_list_box').css('display') !== 'none') {
				if ('' === $('.wfco_mailchimp_select_list').val()) {
					$('.wfco_mailchimp_next_step').prepend('<p><?php _e('Select a list to continue', 'autonami-automations-connectors'); ?></p>');
				} else {
					fetch_stores();
				}

				return false;
			}

			disable_fields();

			let ajax = new bwf_ajax();
			var ajax_data = {
				'_wpnonce': bwfanParams.ajax_nonce,
				'api_key': $('.wfco_mailchimp_api_key').val()
			};

			ajax.ajax('get_mailchimp_lists', ajax_data);
			ajax.success = function (result) {
				enable_fields();
				if (_.has(result, 'status') && (false === result.status || 'failed' === result.status)) {
					$('.wfco_mailchimp_next_step').prepend('<p>' + result.message + '</p>');
					return;
				}

				_.each(result, function (item, key) {
					let selected = (key === '<?php echo $selected_list; ?>') ? 'selected' : '';
					selected = (1 === _.size(result)) ? 'selected' : selected;
					$('.wfco_mailchimp_select_list').append('<option ' + selected + ' value="' + key + '">' + item + '</option>');
				});

				//_.each(stores, function (item, key) {
				//	let selected = (key === '<?php //echo $selected_store; ?>//') ? 'selected' : '';
				//	selected = (1 === _.size(result)) ? 'selected' : selected;
				//	$('.wfco_mailchimp_select_store').append('<option ' + selected + ' value="' + key + '">' + item + '</option>');
				//});

				$('.wfco_mailchimp_select_list_box').show();
				//$('.wfco_mailchimp_select_store_box').show();
				//$('.wfco_mailchimp_fetch_lists').hide();
				//$('.wfco_mailchimp_main_submit').show();
			};

			return false;
		}

		function fetch_stores() {
			disable_fields();

			let ajax = new bwf_ajax();
			var ajax_data = {
				'_wpnonce': bwfanParams.ajax_nonce,
				'api_key': $('.wfco_mailchimp_api_key').val(),
				'list_id': $('.wfco_mailchimp_select_list').val()
			};

			ajax.ajax('get_mailchimp_stores', ajax_data);
			ajax.success = function (result) {
				enable_fields();
				if (_.has(result, 'status') && (false === result.status || 'failed' === result.status)) {
					$('.wfco_mailchimp_next_step').prepend('<p>' + result.message + '</p>');
					return;
				}

				_.each(result, function (item, key) {
					let selected = (key === '<?php echo $selected_store; ?>') ? 'selected' : '';
					selected = (1 === _.size(result)) ? 'selected' : selected;
					$('.wfco_mailchimp_select_store').append('<option ' + selected + ' value="' + key + '">' + item + '</option>');
				});

				//_.each(stores, function (item, key) {
				//	let selected = (key === '<?php //echo $selected_store; ?>//') ? 'selected' : '';
				//	selected = (1 === _.size(result)) ? 'selected' : selected;
				//	$('.wfco_mailchimp_select_store').append('<option ' + selected + ' value="' + key + '">' + item + '</option>');
				//});

				$('.wfco_mailchimp_select_store_box').show();
				//$('.wfco_mailchimp_select_store_box').show();
				$('.wfco_mailchimp_fetch_lists').hide();
				$('.wfco_mailchimp_main_submit').show();
			};
		}

		function enable_fields() {
			$('.wfco_mailchimp_api_key').removeAttr('disabled');
			$('.wfco_mailchimp_fetch_lists').removeAttr('disabled').text('<?php echo esc_html__( 'Next Step', 'autonami-automations-connectors' ); ?>');
		}

		function disable_fields() {
			$('.wfco_mailchimp_next_step p').remove();
			$('.wfco_mailchimp_api_key').attr('disabled', 'disabled');
			$('.wfco_mailchimp_fetch_lists').attr('disabled', 'disabled').text('<?php echo esc_html__( 'Loading...', 'autonami-automations-connectors' ); ?>');
		}
	})(jQuery);
</script>
