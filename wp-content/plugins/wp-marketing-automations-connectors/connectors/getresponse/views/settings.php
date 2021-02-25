<?php
$saved_data    = WFCO_Common::$connectors_saved_data;
$old_data      = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$api_key       = isset( $old_data['api_key'] ) ? $old_data['api_key'] : '';
$selected_list = isset( $old_data['default_list'] ) ? $old_data['default_list'] : '';

?>
<div class="wfco-form-group featured field-input">
	<label for="automation-name"><?php echo esc_html__( 'Enter API Key', 'autonami-automations-connectors' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<input type="text" name="api_key" placeholder="<?php echo esc_attr__( 'Enter API Key', 'autonami-automations-connectors' ); ?>" class="form-control wfco_gr_api_key" required value="<?php echo esc_attr__( $api_key ); ?>">
		</div>
	</div>
</div>
<div class="wfco-form-group featured field-input wfco_gr_select_list_box">
	<label for="automation-name"><?php echo esc_html__( 'Select Default List', 'autonami-automations-connectors' ); ?></label>
	<div class="field-wrap">
		<div class="wrapper">
			<select name="default_list" class="wfco_gr_select_list form-control">
				<option value=""><?php echo esc_html__( 'Choose a list', 'autonami-automations-connectors' ); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="wfco-form-groups wfco_form_submit wfco_gr_next_step">
	<button class="wfco_save_btn_style wfco_fetch_lists"><?php echo esc_html__( 'Next Step', 'autonami-automations-connectors' ); ?></button>
</div>
<div class="wfco-form-groups wfco_form_submit wfco_gr_main_submit">
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
			$('.wfco_gr_main_submit').hide();
			$('.wfco_gr_select_list_box').hide();

			if (!_.isEmpty($('.wfco_gr_api_key').val())) {
				fetch_lists_with_update_ui();
			}

			$('body').on('click', '.wfco_fetch_lists', fetch_lists_with_update_ui);
		});

		function fetch_lists_with_update_ui() {
			disable_fields();

			let ajax = new bwf_ajax();
			var ajax_data = {
				'_wpnonce': bwfanParams.ajax_nonce,
				'api_key': $('.wfco_gr_api_key').val()
			};

			ajax.ajax('get_gr_lists', ajax_data);
			ajax.success = function (result) {
				let selectedList = '<?php echo $selected_list; ?>';
				enable_fields();
				if (_.has(result, 'response')) {
					$('.wfco_gr_next_step').prepend('<p>' + result.response + '</p>');
					return;
				}

				if (!_.isArray(result)) {
					return;
				}

				_.each(result, function (item) {
					let isDefault = (_.isString(item.isDefault) && 'true' === item.isDefault) || true === item.isDefault ? 'selected' : '';
					isDefault = !_.isEmpty(selectedList) && selectedList === item.campaignId ? 'selected' : isDefault;
					$('.wfco_gr_select_list').append('<option ' + isDefault + ' value="' + item.campaignId + '">' + item.name + '</option>');
				});

				$('.wfco_gr_select_list_box').show();
				$('.wfco_fetch_lists').hide();
				$('.wfco_gr_main_submit').show();
			};

			return false;
		}

		function enable_fields() {
			$('.wfco_gr_api_key').removeAttr('disabled');
			$('.wfco_fetch_lists').removeAttr('disabled').text('<?php echo esc_html__( 'Next Step', 'autonami-automations-connectors' ); ?>');
		}

		function disable_fields() {
			$('.wfco_gr_next_step p').remove();
			$('.wfco_gr_api_key').attr('disabled', 'disabled');
			$('.wfco_fetch_lists').attr('disabled', 'disabled').text('<?php echo esc_html__( 'Loading...', 'autonami-automations-connectors' ); ?>');
		}
	})(jQuery);
</script>
