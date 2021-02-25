<?php

final class BWFAN_Keap_Update_Contact_Fields extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Update Contact Fields', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds / updates the custom fields of the contact', 'autonami-automations-connectors' );
		$this->action_priority = 40;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'custom_fields_options', $data );
		}
	}

	public function get_view_data() {
		$custom_fields = WFCO_Common::get_single_connector_data( $this->connector, 'custom_fields' );
		//$optional_fields = WFCO_Common::get_single_connector_data( $this->connector, 'optional_fields' );
		$optional_fields = [];

		foreach ( $optional_fields as $field ) {
			if ( 'custom_fields' !== $field ) {
				$custom_fields[ $field ] = $field;
			}
		}

		return $custom_fields;
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
                            class="bwfan-input-wrapper wfacp_keap_custom_field" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{data.index}}]">
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
                    if(repeaterCount == 0) {
                    repeaterArr = {field:{0:''}, field_value:{0:''}};
                    }

                    if(repeaterCount >= 0) {
                    h=0;
                    _.each( repeaterArr.field, function( key, value ){
                    #>
                    <div class="bwfan-input-form clearfix gs-repeater-fields">
                        <div class="bwfan-col-sm-5 bwfan-p-0">
                            <select required class="bwfan-input-wrapper wfacp_keap_custom_field" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{h}}]">
                                <option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
                                <#
                                if(_.has(data.actionFieldsOptions, 'custom_fields_options') && _.isObject(data.actionFieldsOptions.custom_fields_options) ) {
                                _.each( data.actionFieldsOptions.custom_fields_options, function( column_option_value, column_option_key ){
                                selected = (column_option_key == key) ? 'selected' : '';
                                #>
                                <option value="{{column_option_key}}" {{selected}}>{{column_option_value}}</option>
                                <# })
                                }
                                #>
                            </select>
                        </div>
                        <div class="bwfan-col-sm-6 bwfan-pr-0">
                            <input required type="text" class="bwfan-input-wrapper" value="{{repeaterArr.field_value[value]}}" name="bwfan[{{data.action_id}}][data][custom_fields][field_value][{{h}}]"/>
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
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-mb10">
                    <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><?php echo esc_html__( 'Add More', 'autonami-automations-connectors' ); ?></a>
                </div>
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
		$data_to_set                 = array();
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$fields                      = $task_meta['data']['custom_fields']['field'];
		$fields_value                = $task_meta['data']['custom_fields']['field_value'];
		$custom_fields               = array();

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		if ( $data_to_set ) {
			foreach ( $fields as $key1 => $field_key ) {
				$custom_fields[ $field_key ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
			}
		}

		$optional_fields = array();
		foreach ( $custom_fields as $key => $field ) {
			if ( 0 === absint( $key ) ) {
				$optional_fields[ $key ] = $field;
				unset( $custom_fields[ $key ] );
			}
		}

		$custom_fields_formatted = array();
		foreach ( $custom_fields as $key => $field ) {
			$custom_fields_formatted[] = array(
				'id'      => $key,
				'content' => $field
			);
		}

		$data_to_set['custom_fields']   = $custom_fields_formatted;
		$data_to_set['optional_fields'] = $optional_fields;

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Custom fields updated successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$fault_message   = isset( $result['body']['fault'] ) ? $result['body']['fault']['faultstring'] : false;
		$message         = isset( $result['body']['message'] ) ? $result['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $fault_message ? $fault_message : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Keap_Update_Contact_Fields';
