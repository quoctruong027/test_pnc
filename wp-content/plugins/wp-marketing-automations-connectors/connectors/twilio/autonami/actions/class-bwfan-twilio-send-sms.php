<?php

class BWFAN_Twilio_Send_SMS extends BWFAN_Action {

	private static $instance = null;
	private $progress = false;

	public function __construct() {
		$this->action_name = __( 'Send SMS', 'autonami-automations-connectors' );
		$this->action_desc = __( 'This action sends a SMS via Twilio', 'autonami-automations-connectors' );
	}

	/**
	 * @return BWFAN_Twilio_Send_SMS|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_filter( 'bwfan_modify_send_sms_body', [ $this, 'shorten_link' ], 15, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		wp_enqueue_media();
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_event = BWFAN_Auto.uiDataDetail.trigger.event;
            source = BWFAN_Auto.uiDataDetail.trigger.source;
            phone_merge_tag = '';
            sms_body = '';
            sms_to = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_to')) ? data.actionSavedData.data.sms_to : phone_merge_tag;
            sms_body = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_body')) ? data.actionSavedData.data.sms_body : sms_body;

            custom_attach_img = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'attach_custom_img')) ? data.actionSavedData.data.attach_custom_img : [];
            custom_attach_img1 = !_.isEmpty(custom_attach_img)?JSON.parse(custom_attach_img):'';
            sms_is_promotional = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'promotional_sms')) ? 'checked' : '';
            sms_is_order_first = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'order_first')) ? 'checked' : '';
            sms_is_append_utm = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_append_utm')) ? 'checked' : '';
            sms_show_utm_parameters = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_append_utm')) ? '' : 'bwfan-display-none';

            sms_entered_utm_source = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_utm_source')) ? data.actionSavedData.data.sms_utm_source : '';
            sms_entered_utm_medium = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_utm_medium')) ? data.actionSavedData.data.sms_utm_medium : '';
            sms_entered_utm_campaign = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_utm_campaign')) ? data.actionSavedData.data.sms_utm_campaign : '';
            sms_entered_utm_term = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'sms_utm_term')) ? data.actionSavedData.data.sms_utm_term : '';

            defVal = ( 'checked' === sms_is_order_first ) ? 2 : ( _.size( custom_attach_img1 ) > 0 ? 3 : 1 );
            #>
            <div data-element-type="bwfan-editor" class="bwfan-<?php echo esc_attr__( $unique_slug ); ?>">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'To', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php echo esc_attr__( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][sms_to]" placeholder="E.g. +919999999999" value="{{sms_to}}"/>
                </div>

                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'SMS Body', 'autonami-automations-connectors' );
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <textarea class="bwfan-input-wrapper" id="bwfan-textarea" placeholder="<?php echo esc_attr__( 'SMS Body', 'autonami-automations-connectors' ); ?>" name="bwfan[{{data.action_id}}][data][sms_body]">{{sms_body}}</textarea>
                </div>

                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Add Image', 'autonami-automations-connectors' );
					?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0">
                    <label for="bwfan_image_option_none" class="bwfan-label-title-normal">
                        <input type="radio" name="bwfan_attach_image_select" id="bwfan_image_option_none" selected value="none" {{defVal=== 1 ? 'checked' : ''}}/>
						<?php esc_html_e( 'None', 'autonami-automations-connectors' ); ?>
                    </label>
                    <# if('wc' === source ) { #>
                    <label for="bwfan_image_option_order_highest" class="bwfan-label-title-normal">
                        <input type="radio" name="bwfan_attach_image_select" id="bwfan_image_option_order_highest" selected value="highest_order" {{defVal=== 2 ? 'checked' : ''}}/>
						<?php
						esc_html_e( 'Product image', 'autonami-automations-connectors' );
						$message = __( "This will attach the image of highest price product of a cart/ order in the message", "autonami-automations-connectors" );
						echo $this->add_description( $message, 'l', 'right' );
						?>
                    </label>
                    <# } #>
                    <label for="bwfan_image_option_custom" class="bwfan-label-title-normal">
                        <input type="radio" name="bwfan_attach_image_select" id="bwfan_image_option_custom" selected value="custom_image" {{defVal=== 3 ? 'checked' : ''}}/>
						<?php
						esc_html_e( 'Custom image', 'autonami-automations-connectors' );
						$message = __( "Custom image (JPG, PNG & GIF only)", "autonami-automations-connectors" );
						echo $this->add_description( $message, 'l', 'right' );
						?>
                    </label>
                </div>
                <# if('wc' === source ) { #>

                <input type="checkbox" class="bwfan_hide_hard" name="bwfan[{{data.action_id}}][data][order_first]" id="bwfan_order_first" value="1" {{defVal=== 2 ? 'checked' : ''}}/>
                <# } #>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mt-10 bwfan-mb-10 bwfan_attach_custom_img_box {{defVal === 3 ? '' : 'hidden'}}" style="margin-top:10px;margin-bottom:10px">
                    <div class="form-group bwfan-twilio-uploader">
                        <#
                        if(_.size(custom_attach_img1)>0 && _.isArray(custom_attach_img1) && !_.isEmpty(custom_attach_img1)){

                        _.each(custom_attach_img1,function(value){ #>
                        <div class="bwfan-inline-img-remove">
                            <img src="{{value}}" id="bwfan-cus-img">
                            <a href="javascript:void(0)" class="bwfan-inline-img-remove-icon" id="">x</a>
                        </div>
                        <# });
                        }
                        #>
                        <input type="hidden" name="bwfan[{{data.action_id}}][data][attach_custom_img]" id="bwfan-cus-img-url" value="{{custom_attach_img}}">

                        <input type='button' class="button-primary bwfan-btn-clear" value="<?php esc_attr_e( 'Select an image', 'autonami-automations-connectors' ); ?>" id="bwfan_media_manager"/>
                    </div>
                </div>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <div class="clearfix bwfan_field_desc">
						<?php esc_html_e( 'Images can only be send to US &amp; Canada phone numbers by using a Twilio phone number that is MMS-enabled.', 'wp-marketing-automations' ); ?><br/>
                        <a href="https://support.twilio.com/hc/en-us/articles/223179808-Sending-and-receiving-MMS-messages" target="_blank"><?php esc_html_e( 'Click here', 'wp-marketing-automations' ); ?></a>
						<?php esc_html_e( ' to understand more.', 'wp-marketing-automations' ); ?>
                    </div>
                </div>

                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <label for="" class="bwfan-label-title"><?php esc_html_e( 'Send Test SMS', 'autonami-automations-connectors' ); ?></label>
                    <div class="bwfan_send_test_sms">
                        <input type="text" name="test_sms" id="bwfan_test_sms">
                        <input type="button" class="button bwfan-btn-inner" id="bwfan_test_sms_btn" value="<?php esc_html_e( 'Send', 'autonami-automations-connectors' ); ?>">
                    </div>
                    <div class="clearfix bwfan_field_desc">
						<?php esc_html_e( 'Enter Mobile no with country code', 'wp-marketing-automations' ); ?>
                    </div>
                </div>

                <div class="bwfan_sms_tracking bwfan-mb-15">
                    <label for="bwfan_promotional_sms" class="bwfan-label-title-normal">
                        <input type="checkbox" name="bwfan[{{data.action_id}}][data][promotional_sms]" id="bwfan_promotional_sms" value="1" {{sms_is_promotional}}/>
						<?php
						echo esc_html__( 'Mark as Promotional', 'autonami-automations-connectors' );
						$message = __( 'SMS marked as promotional will not be send to the unsubscribers.', 'autonami-automations-connectors' );
						echo $this->add_description( $message, 'xl' ); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
                    </label>
                    <label for="bwfan_append_utm" class="bwfan-label-title-normal">
                        <input type="checkbox" name="bwfan[{{data.action_id}}][data][sms_append_utm]" id="bwfan_append_utm" value="1" {{sms_is_append_utm}}/>
						<?php
						echo esc_html__( 'Add UTM parameters to the links', 'autonami-automations-connectors' );
						$message = __( 'Add UTM parameters in all the links present in the sms.', 'autonami-automations-connectors' );
						echo $this->add_description( $message, 'xl' ); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
                    </label>
                    <div class="bwfan_utm_sources {{sms_show_utm_parameters}}">
                        <div class="bwfan-input-form clearfix">
                            <div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__( 'UTM Source', 'autonami-automations-connectors' ); ?></span></div>
                            <div class="bwfan-col-sm-8 bwfan-pr-0">
                                <input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_source]" value="{{sms_entered_utm_source}}"/></div>
                        </div>
                        <div class="bwfan-input-form clearfix">
                            <div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__( 'UTM Medium', 'autonami-automations-connectors' ); ?></span></div>
                            <div class="bwfan-col-sm-8 bwfan-pr-0">
                                <input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_medium]" value="{{sms_entered_utm_medium}}"/></div>
                        </div>
                        <div class="bwfan-input-form clearfix">
                            <div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__( 'UTM Campaign', 'autonami-automations-connectors' ); ?></span></div>
                            <div class="bwfan-col-sm-8 bwfan-pr-0">
                                <input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_campaign]" value="{{sms_entered_utm_campaign}}"/></div>
                        </div>
                        <div class="bwfan-input-form clearfix">
                            <div class="bwfan-col-sm-4 bwfan-pl-0"><span class="bwfan_label_input"><?php echo esc_html__( 'UTM Term', 'autonami-automations-connectors' ); ?></span></div>
                            <div class="bwfan-col-sm-8 bwfan-pr-0">
                                <input type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][sms_utm_term]" value="{{sms_entered_utm_term}}"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </script>
        <script>
            jQuery(document).on('change', 'input[name="bwfan_attach_image_select"]', function () {
                if (jQuery(this).val() === 'highest_order') {
                    jQuery('#bwfan_order_first').prop('checked', true);
                    jQuery('.bwfan_attach_custom_img_box').hide();
                } else if (jQuery(this).val() === 'custom_image') {
                    jQuery('.bwfan_attach_custom_img_box').show();
                    jQuery('#bwfan_order_first').prop('checked', false);
                } else {
                    jQuery('.bwfan_attach_custom_img_box').hide();
                    jQuery('#bwfan_order_first').prop('checked', false);
                }
            });

            jQuery(document).on('click', '#bwfan_media_manager', function (e) {
                var $this = jQuery(this), imgUpload = true;

                /** Checking if image is already set */
                var cus_img_url = _.isEmpty(jQuery("#bwfan-cus-img-url").val()) ? [] : JSON.parse(jQuery("#bwfan-cus-img-url").val());

                if (_.size(_.filter(cus_img_url)) > 0) {
                    imgUpload = false;
                    $this.parents('.bwfan-twilio-uploader').append('<p class="bwfan_img_notice">Note: Only one image is allowed.</p>');
                    setTimeout(function () {
                        jQuery('.bwfan_img_notice').fadeOut();
                    }, 800);
                    setTimeout(function () {
                        jQuery('.bwfan_img_notice').remove();
                    }, 1200);
                }

                if (true === imgUpload) {
                    $this.attr('disabled', 'disabled');
                    e.preventDefault();
                    var image_frame;
                    if (image_frame) {
                        image_frame.open();
                    }
                    // Define image_frame as wp.media object
                    image_frame = wp.media({
                        title: 'Select Media',
                        multiple: false,
                        library: {
                            type: 'image',
                        }
                    });

                    image_frame.on('close', function () {
                        // On close, get selections and save to the hidden input
                        // plus other AJAX stuff to refresh the image preview
                        var selection = image_frame.state().get('selection');
                        var gallery_urls = new Array();
                        var my_index = 0;

                        selection.each(function (attachment) {
                            gallery_urls[my_index] = attachment['attributes']['url'];
                            my_index++;
                        });
                        var urls = gallery_urls.join(",");
                        var cus_img_url = _.isEmpty(jQuery("#bwfan-cus-img-url").val()) ? [] : JSON.parse(jQuery("#bwfan-cus-img-url").val());

                        cus_img_url.push(urls);
                        var html = '';
                        cus_img_url = JSON.stringify(cus_img_url);
                        setTimeout(function () {
                            jQuery("#bwfan-cus-img-url").val(cus_img_url);
                            if ("" !== urls) {
                                html += "<div class='bwfan-inline-img-remove'>";
                                html += "<img src='" + urls + "' id='bwfan-cus-img'>";
                                html += "<a href='#' class='bwfan-inline-img-remove-icon' id=''>x</a>";
                                html += "</div>";
                                jQuery("#bwfan_media_manager").before(html);
                            }
                            $this.removeAttr('disabled');
                        }, 2000);
                        $this.removeAttr('disabled');
                    });

                    image_frame.on('open', function () {
                        var selection = image_frame.state().get('selection');
                    });

                    image_frame.open();
                }
            });

            jQuery(document).on("click", ".bwfan-inline-img-remove-icon", function (e) {
                e.preventDefault();
                var remove_image_link = jQuery(this).siblings('img').attr('src');
                var all_custom_img = JSON.parse(jQuery("#bwfan-cus-img-url").val());
                all_custom_img = _.reject(all_custom_img, function (url) {
                    return url == remove_image_link;
                });
                jQuery("#bwfan-cus-img-url").val(JSON.stringify(all_custom_img));
                jQuery(this).closest(".bwfan-inline-img-remove").remove();
            });

            jQuery(document).on('click', '#bwfan_test_sms_btn', function () {
                var smsInputElem = jQuery('#bwfan_test_sms');
                var el = jQuery(this);
                el.prop('disabled', true);
                smsInputElem.prop('disabled', true);
                var sms = smsInputElem.val();
                var form_data = jQuery('#bwfan-actions-form-container').bwfan_serializeAndEncode();
                form_data = bwfan_deserialize_obj(form_data);
                var group_id = jQuery('.bwfan-selected-action').attr('data-group-id');
                var data_to_send = form_data.bwfan[group_id];
                data_to_send.source = BWFAN_Auto.uiDataDetail.trigger.source;
                data_to_send.event = BWFAN_Auto.uiDataDetail.trigger.event;
                data_to_send._wpnonce = bwfanParams.ajax_nonce;
                data_to_send.automation_id = bwfan_automation_data.automation_id;
                data_to_send.data['sms_to'] = sms;
                var ajax = new bwf_ajax();
                ajax.ajax('test_sms', data_to_send);

                ajax.success = function (resp) {
                    el.prop('disabled', false);
                    smsInputElem.prop('disabled', false);

                    if (resp.status == true) {
                        var $iziWrap = jQuery("#modal_automation_success");

                        if ($iziWrap.length > 0) {
                            $iziWrap.iziModal('setTitle', resp.msg);
                            $iziWrap.iziModal('open');
                        }
                    } else {
                        swal({
                            type: 'error',
                            title: window.bwfan.texts.sync_oops_title,
                            text: resp.msg
                        });
                    }
                };
            });


        </script>
		<?php
	}

	public function make_data( $integration_object, $task_meta ) {
		$this->add_action();
		$this->progress = true;

		$data_to_set = array(
			'name'              => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
			'promotional_sms'   => ( isset( $task_meta['data']['promotional_sms'] ) ) ? 1 : 0,
			'order_first'       => ( isset( $task_meta['data']['order_first'] ) ) ? 1 : 0,
			'append_utm'        => ( isset( $task_meta['data']['sms_append_utm'] ) ) ? 1 : 0,
			'attach_custom_img' => ( isset( $task_meta['data']['attach_custom_img'] ) ) ? json_decode( $task_meta['data']['attach_custom_img'] ) : 0,
			'phone'             => ( isset( $task_meta['data']['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['sms_to'] ) : '',
			'event'             => ( isset( $task_meta['event_data'] ) && isset( $task_meta['event_data']['event_slug'] ) ) ? $task_meta['event_data']['event_slug'] : '',
			'sms_body'          => $task_meta['data']['sms_body'],
		);

		if ( isset( $task_meta['data']['sms_utm_source'] ) && ! empty( $task_meta['data']['sms_utm_source'] ) ) {
			$data_to_set['utm_source'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['sms_utm_source'] );
		}
		if ( isset( $task_meta['data']['sms_utm_medium'] ) && ! empty( $task_meta['data']['sms_utm_medium'] ) ) {
			$data_to_set['utm_medium'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['sms_utm_medium'] );
		}
		if ( isset( $task_meta['data']['sms_utm_campaign'] ) && ! empty( $task_meta['data']['sms_utm_campaign'] ) ) {
			$data_to_set['utm_campaign'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['sms_utm_campaign'] );
		}
		if ( isset( $task_meta['data']['sms_utm_term'] ) && ! empty( $task_meta['data']['sms_utm_term'] ) ) {
			$data_to_set['utm_term'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['sms_utm_term'] );
		}

		if ( isset( $task_meta['global'] ) && isset( $task_meta['global']['order_id'] ) ) {
			$data_to_set['order_id'] = $task_meta['global']['order_id'];
		} elseif ( isset( $task_meta['global'] ) && isset( $task_meta['global']['cart_abandoned_id'] ) ) {
			$data_to_set['cart_abandoned_id'] = $task_meta['global']['cart_abandoned_id'];
		}

		/** If promotional checkbox is not checked, then empty the {{unsubscribe_link}} merge tag */
		if ( isset( $data_to_set['promotional_sms'] ) && 0 === absint( $data_to_set['promotional_sms'] ) ) {
			$data_to_set['sms_body'] = str_replace( '{{unsubscribe_link}}', '', $data_to_set['sms_body'] );
		}

		$data_to_set['sms_body'] = stripslashes( $data_to_set['sms_body'] );

		$this->remove_action();

		return $data_to_set;
	}

	private function add_action() {
		add_filter( 'bwfan_order_billing_address_separator', [ $this, 'change_br_to_slash_n' ] );
		add_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	private function remove_action() {
		remove_filter( 'bwfan_order_billing_address_params', [ $this, 'change_br_to_slash_n' ] );
		remove_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	public function shorten_link( $body, $data ) {
		if ( true === $this->progress ) {
			$body = preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', [ $this, 'shorten_urls' ], $body );
		}

		return $body;
	}

	public function execute_action( $action_data ) {
		global $wpdb;
		$this->set_data( $action_data['processed_data'] );
		$this->data['task_id'] = $action_data['task_id'];

		/** Attaching track id */
		$sql_query         = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query         = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' ); //phpcs:ignore WordPress.DB.PreparedSQL
		$gids              = BWFAN_Model_Taskmeta::get_results( $sql_query );
		$this->data['gid'] = '';
		if ( ! empty( $gids ) && is_array( $gids ) ) {
			foreach ( $gids as $gid ) {
				$this->data['gid'] = $gid['meta_value'];
			}
		}

		/** Validating promotional sms */
		if ( 1 === absint( $this->data['promotional_sms'] ) && ( false === apply_filters( 'bwfan_force_promotional_sms', false, $this->data ) ) ) {
			$where             = array(
				'recipient' => $this->data['phone'],
				'mode'      => 2,
			);
			$check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where );

			if ( ! empty( $check_unsubscribe ) ) {
				$this->progress = false;

				return array(
					'status'  => 4,
					'message' => __( 'User is already unsubscribed', 'autonami-automations-connectors' ),
				);
			}
		}

		/** Validating connector */
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_twilio_send_sms' );
		if ( is_null( $call_class ) ) {
			$this->progress = false;

			return array(
				'status'  => 4,
				'message' => __( 'Send SMS call not found', 'autonami-automations-connectors' ),
			);
		}

		$integration               = BWFAN_Twilio_Integration::get_instance();
		$this->data['account_sid'] = $integration->get_settings( 'account_sid' );
		$this->data['auth_token']  = $integration->get_settings( 'auth_token' );
		$this->data['twilio_no']   = $integration->get_settings( 'twilio_no' );

		$media_urls = array();
		$products   = array();


		/** WC order case */
		if ( ! empty( $this->data['order_id'] ) ) {
			$order_details = wc_get_order( $this->data['order_id'] );

			/** Appending country code */
			$country = $order_details->get_billing_country();
			if ( ! empty( $country ) ) {
				$this->data['country_code'] = $country;
			}

			/** Attach order's product image */
			if ( 1 === absint( $this->data['order_first'] ) ) {
				$items    = $order_details->get_items();
				$products = array();
				foreach ( $items as $item ) {
					$product_id = $item->get_product_id();

					if ( absint( $product_id ) > 0 ) {
						$product = wc_get_product( $product_id );
						if ( ! $product instanceof WC_Product ) {
							continue;
						}
						if ( empty( $product->get_image_id() ) ) {
							continue;
						}
						$products[ $product_id ]['product_id'] = $product_id;
						$products[ $product_id ]['price']      = $product->get_price();
						$products[ $product_id ]['image_id']   = $product->get_image_id();
					}
				}
			}
		} elseif ( ! empty( $this->data['cart_abandoned_id'] ) ) {
			/** Cart abandonment case */
			$cart_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );

			/** Appending country code in case available */
			$checkout_data = json_decode( $cart_details['checkout_data'], true );
			if ( is_array( $checkout_data ) && isset( $checkout_data['fields'] ) && isset( $checkout_data['fields']['billing_country'] ) && ! empty( $checkout_data['fields']['billing_country'] ) ) {
				$this->data['country_code'] = $checkout_data['fields']['billing_country'];
			}

			/** Attach order's product image */
			if ( 1 === absint( $this->data['order_first'] ) ) {
				$items = maybe_unserialize( $cart_details['items'] );

				foreach ( $items as $item ) {
					$product = $item['data'];
					if ( ! $product instanceof WC_Product ) {
						continue;
					}
					if ( empty( $product->get_image_id() ) ) {
						continue;
					}

					$product_id = $product->get_id();

					$products[ $product_id ]['product_id'] = $product_id;
					$products[ $product_id ]['price']      = $product->get_price();
					$products[ $product_id ]['image_id']   = $product->get_image_id();
				}
			}
		}

		if ( count( $products ) > 0 ) {
			uasort( $products, function ( $b1, $b2 ) {
				return $b1['price'] <= $b2['price'];
			} );
			$new_products = array_values( array_slice( $products, 0, 1 ) );

			$img_id  = $new_products[0]['image_id'];
			$img_url = wp_get_attachment_image_src( $img_id, 'large' );
			if ( ! empty( $img_url ) ) {
				$media_urls[] = $img_url[0];
			}
		}

		if ( isset( $this->data['attach_custom_img'] ) && ! empty( $this->data['attach_custom_img'] ) ) {
			$media_urls = array_merge( $media_urls, $this->data['attach_custom_img'] );
		}

		if ( ! empty( $media_urls ) ) {
			$media_urls = array_filter( $media_urls );

			/** Passing just single image */
			$this->data['mediaUrl'] = $media_urls[0];
		}

		$call_class->set_data( $this->data );
		$response = $call_class->process();
		if ( is_array( $response ) && 200 === $response['response'] && is_null( $response['body']['error_message'] ) ) {
			$this->progress = false;

			return array(
				'status'  => 3,
				'message' => __( 'SMS sent successfully.', 'autonami-automations-connectors' ),
			);
		}

		$message = __( 'SMS could not be sent. ', 'autonami-automations-connectors' );
		$status  = 4;

		if ( isset( $response['body']['errors'] ) && isset( $response['body']['errors'][0] ) && isset( $response['body']['errors'][0]['message'] ) ) {
			$message = $response['body']['errors'][0]['message'];
		} elseif ( isset( $response['body']['message'] ) ) {
			$message = $response['body']['message'];
		} elseif ( isset( $response['body']['error_message'] ) ) {
			$status  = 0;
			$message = $response['body']['error_message'];
		} elseif ( isset( $response['bwfan_response'] ) && ! empty( $response['bwfan_response'] ) ) {
			$message = $response['bwfan_response'];
		} elseif ( is_array( $response['body'] ) && isset( $response['body'][0] ) && is_string( $response['body'][0] ) ) {
			$message = $message . $response['body'][0];
		}
		$this->progress = false;

		return array(
			'status'  => $status,
			'message' => $message,
		);
	}

	public function add_unsubscribe_query_args( $link ) {
		if ( empty( $this->data ) ) {
			return $link;
		}
		if ( isset( $this->data['phone'] ) ) {
			$link = add_query_arg( array(
				'subscriber_recipient' => $this->data['phone'],
			), $link );
		}
		if ( isset( $this->data['name'] ) ) {
			$link = add_query_arg( array(
				'subscriber_name' => $this->data['name'],
			), $link );
		}

		return $link;
	}

	public function skip_name_email( $flag ) {
		return true;
	}

	public function before_executing_task() {
		add_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		add_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		add_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		remove_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		remove_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function modify_retry_limit( $retry_data ) {
		$retry_data[] = DAY_IN_SECONDS;

		return $retry_data;
	}

	public function change_br_to_slash_n( $params ) {
		return "\n";
	}

	protected function shorten_urls( $matches ) {
		$string = $matches[0];

		return do_shortcode( '[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]' );
	}


}

return 'BWFAN_Twilio_Send_SMS';
