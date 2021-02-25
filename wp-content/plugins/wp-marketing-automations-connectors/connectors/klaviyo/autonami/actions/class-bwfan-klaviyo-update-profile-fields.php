<?php

final class BWFAN_Klaviyo_Update_Profile_fields extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Update Fields', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds/ updates the custom fields of the contact', 'autonami-automations-connectors' );
		$this->action_priority = 20;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'fields_options', $data['fields'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'lists', $data['lists'] );
		}
	}

	public function get_view_data() {
		$lists  = WFCO_Common::get_single_connector_data( $this->connector, 'lists' );
		$fields = $this->pre_define_fields();

		return [ 'lists' => $lists, 'fields' => $fields ];
	}

	public function pre_define_fields() {
		return [
			'$email'        => 'Email',
			'$first_name'   => 'First Name',
			'$last_name'    => 'Last Name',
			'$organization' => 'Organization',
			'$title'        => 'Title',
			'$city'         => 'City',
			'$region'       => 'Region',
			'$zip'          => 'Zip',
			'$country'      => 'Country',
			'$phone_number' => 'Phone Number',
		];
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
                            class="bwfan-input-wrapper wfacp_ac_custom_field" name="bwfan[{{data.action_id}}][data][predefine_fields][field][{{data.index}}]">
                        <option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
                        <#
                        if(_.has(data.actionFieldsOptions, 'fields_options') && _.isObject(data.actionFieldsOptions.fields_options) ) {
                        _.each( data.actionFieldsOptions.fields_options, function( value, key ){
                        #>
                        <option value="{{key}}">{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
                <div class="bwfan-col-sm-6 bwfan-pr-0">
                    <input required type="text" class="bwfan-input-wrapper bwfan-input-merge-tags" value="" name="bwfan[{{data.action_id}}][data][predefine_fields][field_value][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-1 bwfan-pr-0">
                    <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                </div>
            </div>
        </script>
        <script type="text/html" id="tmpl-action-repeater-ui-<?php echo esc_html__( $unique_slug ); ?>-2">
            <div class="bwfan-input-form clearfix gs-repeater-fields">
                <div class="bwfan-col-sm-5 bwfan-pl-0">
                    <input required type="text" class="bwfan-input-wrapper" value="" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{data.index}}]"/>
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
					echo esc_html__( 'Select Fields', 'autonami-automations-connectors' );
					$message = __( 'Select custom fields to update their value', 'autonami-automations-connectors' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <div class="clearfix bwfan-input-repeater bwfan_mb10">
                    <#
                    repeaterArr = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'predefine_fields')) ? data.actionSavedData.data.predefine_fields : {};
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
                            <select required class="bwfan-input-wrapper wfacp_ac_custom_field" name="bwfan[{{data.action_id}}][data][predefine_fields][field][{{h}}]">
                                <option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
                                <#
                                if(_.has(data.actionFieldsOptions, 'fields_options') && _.isObject(data.actionFieldsOptions.fields_options) ) {
                                _.each( data.actionFieldsOptions.fields_options, function( column_option_value, column_option_key ){
                                selected = (column_option_key == value) ? 'selected' : '';
                                #>
                                <option value="{{column_option_key}}" {{selected}}>{{column_option_value}}</option>
                                <# })
                                }
                                #>
                            </select>
                        </div>
                        <div class="bwfan-col-sm-6 bwfan-pr-0">
                            <input required type="text" class="bwfan-input-wrapper" value="{{repeaterArr.field_value[key]}}" name="bwfan[{{data.action_id}}][data][predefine_fields][field_value][{{h}}]"/>
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
            <br/>
            <br/>
            <div class="bwfan-repeater-wrap">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Enter Custom Field Key/ values', 'woofunnels-connector' ); ?></label>
                <div class="clearfix bwfan-input-repeater bwfan-mb10">
                    <#
                    repeaterArr = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'custom_fields')) ? data.actionSavedData.data.custom_fields : {};
                    repeaterCount = _.size(repeaterArr.field);
                    if(repeaterCount == 0) {
                    repeaterArr = {field:{0:''}, field_value:{0:''}};
                    }

                    if(repeaterCount >= 0) {
                    h=0;
                    _.each( repeaterArr.field, function( value, key ){
                    #>
                    <div class="bwfan-input-form clearfix gs-repeater-fields">
                        <div class="bwfan-col-sm-5 bwfan-pl-0">
                            <input required type="text" class="bwfan-input-wrapper" value="{{repeaterArr.field[key]}}" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{h}}]"/>
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
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-mb10">
                    <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>-2" data-repeater-slug="<?php echo esc_html__( $unique_slug ); ?>-2" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><?php echo esc_html__( 'Add More', 'woofunnels-connector' ); ?></a>
                </div>
            </div>
            <#
            selected_list = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'list_id')) ? data.actionSavedData.data.list_id : '';
            body = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body')) ? data.actionSavedData.data.body : '';
            entered_email = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'email')) ? data.actionSavedData.data.email : '';
            #>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select List', 'autonami-automations-connectors' );
				$message = __( 'Select a List and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <select required id="" class="bwfan-single-select bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][list_id]">
                <option value=""><?php echo esc_html__( 'Choose a Audience', 'autonami-automations-connectors' ); ?></option>
                <#
                if(_.has(data.actionFieldsOptions, 'lists') && _.isObject(data.actionFieldsOptions.lists) ) {
                _.each( data.actionFieldsOptions.lists, function( value, key ){
                selected = (key == selected_list) ? 'selected' : '';
                #>
                <option value="{{key}}" {{selected}}>{{value}}</option>
                <# })
                }
                #>
            </select>
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
                        fields_options: bwfan_set_actions_js_data['<?php echo esc_attr__( $this->get_class_slug() ); ?>']['fields_options']
                    };

                    $this.parents('.bwfan-repeater-wrap').find('.bwfan-input-repeater').append(template({action_id: action_id, index: index, actionFieldsOptions: actionFieldsOptions}));
                    index = index + 1;
                    $this.attr('data-count', index);
                });
                $('body').on('click', '.bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>-2', function (event) {
                    event.preventDefault();
                    var $this = $(this);
                    var index = Number($this.attr('data-count'));
                    var action_id = $this.attr('data-groupid');
                    var template = wp.template('action-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>-2');

                    var actionFieldsOptions = {
                        fields_options: bwfan_set_actions_js_data['<?php echo esc_attr__( $this->get_class_slug() ); ?>']['fields_options']
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
		$data_to_set['list_id'] = $task_meta['data']['list_id'];

		$fields       = $task_meta['data']['predefine_fields']['field'];
		$fields_value = $task_meta['data']['predefine_fields']['field_value'];

		$custom_fields       = $task_meta['data']['custom_fields']['field'];
		$custom_fields_value = $task_meta['data']['custom_fields']['field_value'];

		$profile_fields = array();
		$is_validate    = isset( $task_meta['data']['validate_fields'] ) ? $task_meta['data']['validate_fields'] : '';

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		if ( $data_to_set ) {
			foreach ( $fields as $key1 => $field_alias ) {
				$profile_fields[ $field_alias ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
			}
			foreach ( $custom_fields as $key => $field_alias ) {
				$profile_fields[ $field_alias ] = BWFAN_Common::decode_merge_tags( $custom_fields_value[ $key ] );
			}
		}

		//filter profile fields to remove blank
		if ( 1 === intval( $is_validate ) ) {
			foreach ( $custom_fields as $key => $fields ) {
				if ( empty( $fields ) ) {
					unset( $custom_fields[ $key ] );
				}
			}
		}

		$data_to_set['profile_fields'] = $profile_fields;

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		return $result;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Klaviyo_Update_Profile_fields';
