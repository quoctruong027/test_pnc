<?php

final class BWFAN_GR_Update_Custom_Fields extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Update Custom Fields', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds / updates the custom fields of the contact', 'autonami-automations-connectors' );
		$this->action_priority = 20;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'lists_options', $data['lists'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'custom_fields_options', $data['custom_fields'] );
		}
	}

	public function get_view_data() {
		$custom_fields = WFCO_Common::get_single_connector_data( $this->connector, 'custom_fields' );
		$lists         = WFCO_Common::get_single_connector_data( $this->connector, 'lists' );

		return [ 'custom_fields' => $custom_fields, 'lists' => $lists ];
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
		<script type="text/html" id="tmpl-action-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>">
			<div class="bwfan-input-form clearfix gs-repeater-fields">
				<div class="bwfan-col-sm-5 bwfan-p-0">
					<select data-parent-slug="<?php echo esc_attr__( $unique_slug ); ?>" required
							class="bwfan-input-wrapper wfacp_ac_custom_field" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{data.index}}]">
						<option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
						<#
						if(_.has(data.actionFieldsOptions, 'custom_fields_options') && _.isObject(data.actionFieldsOptions.custom_fields_options) ) {
						_.each( data.actionFieldsOptions.custom_fields_options, function( value, key ){
						#>
						<option value="{{key}}">{{value}}</option>
						<# })
						}
						#>
					</select>
				</div>
				<div class="bwfan-col-sm-6 bwfan-pr-0">
					<input required type="text" class="bwfan-input-wrapper bwfan-input-merge-tags" value="" name="bwfan[{{data.action_id}}][data][custom_fields][field_value][{{data.index}}]"/>
				</div>
				<div class="bwfan-col-sm-1 bwfan-pr-0">
					<span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
				</div>
			</div>
		</script>
		<script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
			<div class="bwfan-repeater-wrap">
				<label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Select Custom Fields', 'autonami-automations-connectors' );
					$message = __( 'Select custom fields to update their value', 'autonami-automations-connectors' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
				</label>
				<div class="clearfix bwfan-input-repeater bwfan_mb10">
					<#
					repeaterArr = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'custom_fields')) ? data.actionSavedData.data.custom_fields : {};
					repeaterCount = _.size(repeaterArr.field);
					validate_field = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'validate_fields')) ? data.actionSavedData.data.validate_fields : 0;

					validate_field = 1=== parseInt(validate_field)?'checked':0;
					if(repeaterCount == 0) {
					repeaterArr = {field:{0:''}, field_value:{0:''}};
					}

					if(repeaterCount >= 0) {
					h=0;
					_.each( repeaterArr.field, function( value, key ){
					#>
					<div class="bwfan-input-form clearfix gs-repeater-fields">
						<div class="bwfan-col-sm-5 bwfan-p-0">
							<select required class="bwfan-input-wrapper wfacp_ac_custom_field" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{h}}]">
								<option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
								<#
								if(_.has(data.actionFieldsOptions, 'custom_fields_options') && _.isObject(data.actionFieldsOptions.custom_fields_options) ) {
								_.each( data.actionFieldsOptions.custom_fields_options, function( column_option_value, column_option_key ){
								selected = (column_option_key == value) ? 'selected' : '';
								#>
								<option value="{{column_option_key}}" {{selected}}>{{column_option_value}}</option>
								<# })
								}
								#>
							</select>
						</div>
						<div class="bwfan-col-sm-6 bwfan-pr-0">
							<input required type="text" class="bwfan-input-wrapper" value="{{repeaterArr.field_value[key]}}" name="bwfan[{{data.action_id}}][data][custom_fields][field_value][{{h}}]"/>
						</div>
						<div class="bwfan-col-sm-1 bwfan-pr-0">
							<span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
						</div>
					</div>
					<# h++;
					});
					}
					repeaterCount = repeaterCount + 1;
					#>
				</div>
				<div class="bwfan-col-sm-12 bwfan-pl-0">
					<a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><?php echo esc_html__( 'Add More', 'autonami-automations-connectors' ); ?></a>
				</div>
			</div>
			<div class="bwfan-col-sm-12 bwfan-pl-0" style="margin-top: 20px;">
				<label class="bwfan-label-title">Select List</label>
				<div>
					<select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][list_id]">
						<option value=""><?php echo esc_html__( 'All Lists', 'autonami-automations-connectors' ); ?></option>
						<#
						selected_list = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'list_id')) ? data.actionSavedData.data.list_id : '';

						if(_.has(data.actionFieldsOptions, 'lists_options') && _.isObject(data.actionFieldsOptions.lists_options) ) {
						_.each( data.actionFieldsOptions.lists_options, function( value, key ){
						selected = (key == selected_list) ? 'selected' : '';
						#>
						<option value="{{key}}" {{selected}}>{{value}}</option>
						<# })
						}
						#>
					</select>
				</div>
				<div class="bwfan_field_desc">Select the list to update custom fields in.</div>
			</div>
			<div class="bwfan-col-sm-12 bwfan-pl-0 bwfan_mt15 bwfan-mb-15">
				<label for="bwfan-validate_fields" class="bwfan-label-title">Advanced</label>
				<input type="checkbox" name="bwfan[{{data.action_id}}][data][validate_fields]" id="bwfan-drip_validate_fields" value="1" class="validate_fields_1" {{validate_field}}>
				<label for="bwfan-drip_validate_fields" class="bwfan-checkbox-label">Do not update custom field(s) when passed value is blank</label>
				<div class="clearfix bwfan_field_desc"></div>
			</div>
		</script>

		<script>
			jQuery(document).ready(function ($) {
				/** Generate repeater UI by calling script template */
				$('body').on('click', '.bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>', function (event) {
					event.preventDefault();
					var $this = $(this);
					var index = Number($this.attr('data-count'));
					var action_id = $this.attr('data-groupid');
					var template = wp.template('action-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>');

					var actionFieldsOptions = {
						custom_fields_options: bwfan_set_actions_js_data['<?php echo esc_attr__( $this->get_class_slug() ); ?>']['custom_fields_options']
					};

					$this.parents('.bwfan-repeater-wrap').find('.bwfan-input-repeater').append(template({action_id: action_id, index: index, actionFieldsOptions: actionFieldsOptions}));
					index = index + 1;
					$this.attr('data-count', index);
				});

			});
		</script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object BWFAN_Integration
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['list_id'] = empty( $task_meta['data']['list_id'] ) ? '' : $task_meta['data']['list_id'];
		$fields                 = $task_meta['data']['custom_fields']['field'];
		$fields_value           = $task_meta['data']['custom_fields']['field_value'];
		$custom_fields          = array();
		$is_validate            = isset( $task_meta['data']['validate_fields'] ) ? $task_meta['data']['validate_fields'] : '';

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		if ( $data_to_set ) {
			foreach ( $fields as $key1 => $field_alias ) {
				$custom_fields[ $field_alias ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
			}
		}

		//filter custom fields to remove blank
		if ( 1 === intval( $is_validate ) ) {
			foreach ( $custom_fields as $key => $fields ) {
				if ( empty( $fields ) ) {
					unset( $custom_fields[ $key ] );
				}
			}
		}

		$data_to_set['custom_fields'] = $custom_fields;

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['code'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Error: ' . $result['body']['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 !== absint( $result['response'] ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'][0] : 'Unknown Error Occurred';

			return array(
				'status'  => 4,
				'message' => __( $message, 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Custom Fields updated successfully!', 'autonami-automations-connectors' ),
			);
		}

		BWFAN_Core()->logger->log( $result, 'failed-' . $this->get_slug() . '-action' );

		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error: Check log failed-' . $this->get_slug() . '-action', 'autonami-automations-connectors' ),
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_GR_Update_Custom_Fields';
