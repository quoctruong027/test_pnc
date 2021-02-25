<?php

final class BWFAN_AC_Update_Deal_CustomFields extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Update Deal Custom Fields', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action updates the custom fields of the deal', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_order_note_added',
			'wc_order_status_change',
			'wc_product_purchased',
			'wc_product_refunded',
			'wc_product_stock_reduced',
		);
		$this->action_priority = 90;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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
		return WFCO_Common::get_single_connector_data( $this->connector, 'deal_custom_fields' );
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>">
            <div class="bwfan-input-form clearfix gs-repeater-fields">
                <div class="bwfan-col-sm-5 bwfan-p-0">
                    <select data-parent-slug="<?php echo esc_attr__( $unique_slug ); ?>" required class="bwfan-input-wrapper wfacp_ac_custom_field" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{data.index}}]">
                        <option value=""><?php echo esc_html__( 'Choose Field', 'autonami-automations-connectors' ); ?></option>
                        <#
                        if(_.has(data.actionFieldsOptions, 'custom_fields_options') && _.isObject(data.actionFieldsOptions.custom_fields_options) ) {
                        _.each( data.actionFieldsOptions.custom_fields_options, function( value, key ){
                        #>
                        <option value="{{key}}">{{_.has(value,'title')?value.title:value}}</option>
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
					$message = __( 'Select available fields to update and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
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
                                <option value="{{column_option_key}}" {{selected}}>{{_.has(column_option_value,'title')?column_option_value.title:column_option_value}}</option>
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
                    <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><?php esc_html_e( 'Add More', 'autonami-automations-connectors' ); ?></a>
                </div>
            </div>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan_mt15 bwfan-mb-15">
                <label for="bwfan-validate_fields" class="bwfan-label-title">Advanced</label>
                <input type="checkbox" name="bwfan[{{data.action_id}}][data][validate_fields]" id="bwfan-ac_validate_fields" value="1" class="validate_fields_1" {{validate_field}}>
                <label for="bwfan-ac_validate_fields" class="bwfan-checkbox-label">Do not update custom field(s) when passed value is blank</label>
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
                    var template = wp.template('repeater-ui-<?php echo esc_attr__( $unique_slug ); ?>');

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
		$data_to_set['api_url'] = $integration_object->get_settings( 'api_url' );
		$fields                 = $task_meta['data']['custom_fields']['field'];
		$fields_value           = $task_meta['data']['custom_fields']['field_value'];
		$custom_fields          = array();
		$is_validate            = isset( $task_meta['data']['validate_fields'] ) ? $task_meta['data']['validate_fields'] : 0;

		add_filter( 'bwfan_order_date_format', [ $this, 'get_ymd' ] );
		foreach ( $fields as $key1 => $field_id ) {
			$custom_fields[ $field_id ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
		}
		remove_filter( 'bwfan_order_date_format', [ $this, 'get_ymd' ] );

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

	public function get_ymd() {
		return 'Y-m-d';
	}

	public function execute_action( $action_data ) {
		// dynamic filter, it will be like 'bwfan_get_deal_id_wc' or 'bwfan_get_deal_id_edd'. Created to get deal id from order meta.
		$deal_id = apply_filters( 'bwfan_get_deal_id_' . $action_data['event_data']['event_source'], false, $action_data );

		if ( false === $deal_id ) {
			return array(
				'status'  => 4,
				'message' => __( 'No Deal found in order', 'autonami-automations-connectors' )
			);
		}

		$action_data['processed_data']['deal_id'] = $deal_id;

		return parent::execute_action( $action_data );
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		$updated_fields = $result['updated'];
		$failed_fields  = $result['failed'];

		if ( count( $updated_fields ) > 0 ) {
			$message = __( 'Custom fields updated successfully.', 'autonami-automations-connectors' );
			$message = count( $failed_fields ) > 0 ? $message . __( ' But unable to update ' . count( $failed_fields ) . ' fields.', 'autonami-automations-connectors' ) : $message;

			return array(
				'status'  => 3,
				'message' => $message,
			);
		}

		if ( isset( $result['response'] ) && 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unable to update custom fields', 'autonami-automations-connectors' )
		);
	}

}

return 'BWFAN_AC_Update_Deal_CustomFields';
