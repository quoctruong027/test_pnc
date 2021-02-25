<?php

final class BWFAN_WP_HTTP_Post extends BWFAN_Action {

	private static $ins = null;
	public $required_fields = array( 'url', 'custom_fields' );

	protected function __construct() {
		$this->action_name = __( 'HTTP Post', 'autonami-automations-pro' );
		$this->action_desc = __( 'This action sends a HTTP POST request with key value pairs data to the entered URL', 'autonami-automations-pro' );

		$this->action_priority = 10;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug  = $this->get_slug();
		$unique_slug2 = $unique_slug . '_2';
		?>
        <script type="text/html" id="tmpl-action-repeater-ui-<?php echo esc_html__( $unique_slug ); ?>">
            <div class="bwfan-input-form clearfix gs-repeater-fields">
                <div class="bwfan-col-sm-5 bwfan-pl-0">
                    <input required type="text" placeholder="Key" class="bwfan-input-wrapper" value="" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-6 bwfan-p-0">
                    <input required type="text" placeholder="Value" class="bwfan-input-wrapper bwfan-input-merge-tags" value="" name="bwfan[{{data.action_id}}][data][custom_fields][field_value][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-1 bwfan-pr-0">
                    <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                </div>
            </div>
        </script>

        <script type="text/html" id="tmpl-action-repeater-ui-<?php echo esc_html__( $unique_slug2 ); ?>">
            <div class="bwfan-input-form clearfix gs-repeater-fields">
                <div class="bwfan-col-sm-5 bwfan-pl-0">
                    <input required type="text" placeholder="Key" class="bwfan-input-wrapper" value="" name="bwfan[{{data.action_id}}][data][headers][field][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-6 bwfan-p-0">
                    <input required type="text" placeholder="Value" class="bwfan-input-wrapper bwfan-input-merge-tags" value="" name="bwfan[{{data.action_id}}][data][headers][field_value][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-1 bwfan-pr-0">
                    <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                </div>
            </div>
        </script>

        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            entered_url = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'url')) ? data.actionSavedData.data.url : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Enter URL', 'autonami-automations-pro' );
					$message = __( 'Enter a URL where data will be sent.', 'autonami-automations-pro' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <textarea required="" class="bwfan-input-wrapper" rows="3" placeholder="Webhook URL" name="bwfan[{{data.action_id}}][data][url]" spellcheck="false">{{entered_url}}</textarea>
            </div>
            <div class="clearfix bwfan-repeater-wrap bwfan-mb10">
                <label for="" class="bwfan-label-title">
					<?php echo esc_html__( 'Data', 'autonami-automations-pro' ); ?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>

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
                            <input required type="text" placeholder="Key" class="bwfan-input-wrapper" value="{{repeaterArr.field[key]}}" name="bwfan[{{data.action_id}}][data][custom_fields][field][{{h}}]"/>
                        </div>
                        <div class="bwfan-col-sm-6 bwfan-p-0">
                            <input required type="text" placeholder="Value" class="bwfan-input-wrapper" value="{{repeaterArr.field_value[key]}}" name="bwfan[{{data.action_id}}][data][custom_fields][field_value][{{h}}]"/>
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
                    <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui" data-repeater-slug="<?php echo esc_html__( $unique_slug ); ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><i class="dashicons dashicons-plus-alt"></i></a>
                </div>
            </div>
            <div class="clearfix bwfan-repeater-wrap bwfan-mb10">
                <label for="" class="bwfan-label-title">
					<?php echo esc_html__( 'Headers', 'autonami-automations-pro' ); ?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <div class="clearfix bwfan-input-repeater bwfan-mb10">
                    <#
                    repeaterArr2 = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'headers')) ? data.actionSavedData.data.headers : {};
                    repeaterCount2 = _.size(repeaterArr2.field);
                    if(repeaterCount2 == 0) {
                    repeaterArr2 = {field:{0:''}, field_value:{0:''}};
                    }

                    if(repeaterCount2 >= 0) {
                    h=0;
                    _.each( repeaterArr2.field, function( value, key ){
                    #>
                    <div class="bwfan-input-form clearfix gs-repeater-fields">
                        <div class="bwfan-col-sm-5 bwfan-pl-0">
                            <input type="text" placeholder="Key" class="bwfan-input-wrapper" value="{{repeaterArr2.field[key]}}" name="bwfan[{{data.action_id}}][data][headers][field][{{h}}]"/>
                        </div>
                        <div class="bwfan-col-sm-6 bwfan-p-0">
                            <input type="text" placeholder="Value" class="bwfan-input-wrapper" value="{{repeaterArr2.field_value[key]}}" name="bwfan[{{data.action_id}}][data][headers][field_value][{{h}}]"/>
                        </div>
                        <div class="bwfan-col-sm-1 bwfan-pr-0">
                            <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                        </div>
                    </div>
                    <# h++;
                    });
                    }
                    repeaterCount2 = repeaterCount2 + 1;
                    #>
                </div>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-mb-15">
                    <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui" data-repeater-slug="<?php echo esc_html__( $unique_slug2 ); ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount2}}"><i class="dashicons dashicons-plus-alt"></i></a>
                </div>
            </div>

            <div class="clearfix bwfan-mb-15">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Send test data via HTTP Post', 'autonami-automations-pro' ); ?></label>
                <div class="bwfan_field_desc bwfan-mb10"><?php esc_html_e( 'This will POST the key value pairs with dummy data to the specified URL', 'autonami-automations-pro' ); ?></div>
                <input type="button" id="bwfan_test_http_post_btn" class="button" value="<?php esc_html_e( 'Send Now', 'autonami-automations-pro' ); ?>">
            </div>
        </script>

        <script>
            jQuery(document).ready(function ($) {
                /* Send test data to zap */
                $(document).on('click', '#bwfan_test_http_post_btn', function () {
                    var el = $(this);
                    var form_data = $('#bwfan-actions-form-container').bwfan_serializeAndEncode();
                    form_data = bwfan_deserialize_obj(form_data);
                    var group_id = $('.bwfan-selected-action').attr('data-group-id');
                    var data_to_send = form_data.bwfan[group_id];

                    data_to_send.source = BWFAN_Auto.uiDataDetail.trigger.source;
                    data_to_send.event = BWFAN_Auto.uiDataDetail.trigger.event;
                    data_to_send._wpnonce = bwfanParams.ajax_nonce;
                    data_to_send.automation_id = bwfan_automation_data.automation_id;

                    el.prop('disabled', true);

                    var ajax = new bwf_ajax();
                    ajax.ajax('send_test_http_post', data_to_send);

                    ajax.success = function (resp) {
                        el.prop('disabled', false);

                        if (resp.status == true) {
                            let $iziWrap = $("#modal_automation_success");
                            if ($iziWrap.length > 0) {
                                $iziWrap.iziModal('setTitle', resp.msg);
                                $iziWrap.iziModal('open');
                            }

                        } else {
                            swal({
                                type: 'error',
                                title: resp.msg,
                            });
                        }
                    };
                });
            });
        </script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$data_to_set          = array();
		$data_to_set['email'] = $task_meta['global']['email'];
		$data_to_set['url']   = BWFAN_Common::decode_merge_tags( $task_meta['data']['url'] );
		$fields               = $task_meta['data']['custom_fields']['field'];
		$fields_value         = $task_meta['data']['custom_fields']['field_value'];
		$custom_fields        = array();

		foreach ( $fields as $key1 => $field_id ) {
			$custom_fields[ $field_id ] = BWFAN_Common::decode_merge_tags( $fields_value[ $key1 ] );
		}
		$data_to_set['custom_fields'] = $custom_fields;

		$header_fields       = $task_meta['data']['headers']['field'];
		$header_fields_value = $task_meta['data']['headers']['field_value'];
		$header_fields_final = array();

		foreach ( $header_fields as $key1 => $field_id ) {
			$header_fields_final[ $field_id ] = BWFAN_Common::decode_merge_tags( $header_fields_value[ $key1 ] );
		}
		$data_to_set['headers'] = $header_fields_final;

		return $data_to_set;
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action( $action_data ) {
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();

		if ( isset( $result['response'] ) && 200 === $result['response'] ) {
			return array(
				'status' => 3
			);
		}

		$error_message = 'Response Status Code: ' . $result['response'];
		if ( is_array( $result['body'] ) ) {
			$error_message .= isset( $result['body']['message'] ) ? ' Error: ' . $result['body']['message'] : ( isset( $result['body']['msg'] ) ? ' Error: ' . $result['body']['msg'] : '' );
		}

		return array(
			'status'  => 1,
			'message' => __( $error_message, 'autonami-automations-pro' )
		);
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$endpoint_url = $this->data['url'];
		$params_data  = $this->data['custom_fields'];
		$headers      = [];

		if ( isset( $this->data['headers'] ) ) {
			foreach ( $this->data['headers'] as $header_key => $header_value ) {
				if ( empty( $header_key ) || empty( $header_value ) ) {
					continue;
				}
				$headers[ $header_key ] = $header_value;
			}
		}

		$result = $this->make_wp_requests( $endpoint_url, $params_data, $headers, 2 );

		return $result;
	}
}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WP_HTTP_Post';
