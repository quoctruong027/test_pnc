<?php

final class BWFAN_Wp_Sendemail extends BWFAN_Action {

	private static $ins = null;
	public $is_preview = false;
	public $preview_body = '';

	protected function __construct() {
		$this->action_name     = __( 'Send Email', 'wp-marketing-automations' );
		$this->action_desc     = __( 'This action sends an email to a user', 'autonami-automations-connectors' );
		$this->required_fields = array( 'subject', 'body', 'email', 'from_email', 'from_name' );

		add_filter( 'admin_body_class', array( $this, 'add_email_preview_class' ) );
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
			$data = [];

			$data['raw_template'] = __( 'Rich Text Template', 'wp-marketing-automations' );
			if ( bwfan_is_woocommerce_active() ) {
				$data['wc_template'] = __( 'WooCommerce Template', 'wp-marketing-automations' );
			}
			$data['raw'] = __( 'Raw HTML Template', 'wp-marketing-automations' );

			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'template_options', $data );
		}
	}

	public function add_unsubscribe_merge_tag( $text ) {
		if ( isset( $this->data['promotional_email'] ) && 0 === absint( $this->data['promotional_email'] ) ) {
			return $text;
		}

		// add separator if there is footer text
		if ( trim( $text ) ) {
			$text .= apply_filters( 'bwfan_woo_email_footer_separator', ' - ' );
		}

		$global_settings  = BWFAN_Common::get_global_settings();
		$unsubscribe_link = BWFAN_Common::decode_merge_tags( '{{unsubscribe_link}}' );
		$text             .= '<a href="' . $unsubscribe_link . '">' . $global_settings['bwfan_unsubscribe_email_label'] . '</a>';

		return $text;
	}

	public function add_unsubscribe_query_args( $link ) {
		if ( empty( $this->data ) ) {
			return $link;
		}
		if ( isset( $this->data['email'] ) ) {
			$link = add_query_arg( array(
				'subscriber_recipient' => $this->data['email'],
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

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php esc_html_e( $unique_slug ); ?>">
            <#
            selected_event_src = BWFAN_Auto.uiDataDetail.trigger.source;
            selected_event = BWFAN_Auto.uiDataDetail.trigger.event;

            email_merge_tag = '';
            email_sub = '';
            email_body = '';

            ae = bwfan_automation_data.all_triggers_events;

            if(_.has(ae, selected_event_src) &&
            _.has(ae[selected_event_src], selected_event) &&
            _.has(ae[selected_event_src][selected_event], 'customer_email_tag')) {
            email_merge_tag = ae[selected_event_src][selected_event].customer_email_tag;
            }

            if(selected_event=='ab_cart_abandoned'){
            email_sub = 'We\'re still holding the cart for you';
            email_body = '<p>Hi {{cart_billing_first_name}},</p>' +
            "<p>I noticed that you were trying to purchase but couldn\'t complete the process.</p>" +
            "<p> {{cart_items template='cart-table'}} </p>"+
            '<p>We have reserved the cart for you, <a href="{{cart_recovery_link}}">Click here</a> to complete your purchase.</p>' +
            '<p>If you have any questions, feel free to get in touch with us.</p>' +
            '<p>Hit reply and I\'ll be happy to answer your questions.</p>' +
            '<p>Thanks!</p>';
            }

            selected_template = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'template')) ? data.actionSavedData.data.template : 'raw_template';

            is_enable_wysiwyg = ('raw' != selected_template) ? '' : 'bwfan-display-none';
            is_enable_textarea = ('raw' == selected_template) ? '' : 'bwfan-display-none';

            email_heading = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'email_heading')) ? data.actionSavedData.data.email_heading : '';
            to = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'to')) ? data.actionSavedData.data.to : email_merge_tag;
            subject = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'subject')) ? data.actionSavedData.data.subject : email_sub;
            body = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body')) ? data.actionSavedData.data.body : email_body;
            body_raw = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body_raw')) ? data.actionSavedData.data.body_raw : email_body;

            show_email_heading = (selected_template=='wc_template' || selected_template=='') ? '' : 'bwfan-display-none';
            is_promotional = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'promotional_email')) ? 'checked' : '';
            is_append_utm = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'append_utm')) ? 'checked' : '';
            show_utm_parameters = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'append_utm')) ? '' : 'bwfan-display-none';

            entered_utm_source = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_source')) ? data.actionSavedData.data.utm_source : '';
            entered_utm_medium = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_medium')) ? data.actionSavedData.data.utm_medium : '';
            entered_utm_campaign = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_campaign')) ? data.actionSavedData.data.utm_campaign : '';
            entered_utm_term = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_term')) ? data.actionSavedData.data.utm_term : '';

            #>
            <div data-element-type="bwfan-editor" data-temp="{{selected_template}}" class="bwfan-<?php esc_html_e( $unique_slug ); ?>">
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'Template', 'wp-marketing-automations' ); ?>
					<?php
					$message = "<strong>Rich Text Template:</strong> " . __( "Use this template for more control over the email body. Rich text style is auto-applied.", 'wp-marketing-automations' );
					$message .= "<br/><br/><strong>WooCommerce Template:</strong> " . __( "Use native WooCommerce header/footer template. Write content inside the email body using a combination of text and code.", 'wp-marketing-automations' );
					$message .= "<br/><br/><strong>Raw HTML Template:</strong> " . __( "Use this template for complete control by pasting any Custom HTML/CSS (usually designed using an external email editor).", 'wp-marketing-automations' );
					echo $this->add_description( $message, '3xl', 'right', false ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <select required id="bwfan_email_template" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][template]">
                        <#
                        if(_.has(data.actionFieldsOptions, 'template_options') && _.isObject(data.actionFieldsOptions.template_options) ) {
                        _.each( data.actionFieldsOptions.template_options, function( value, key ){
                        selected = (key == selected_template) ? 'selected' : '';
                        #>
                        <option value="{{key}}" {{selected}}>{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'To', 'wp-marketing-automations' ); ?>
					<?php
					$message = __( 'Receiver email address', 'wp-marketing-automations' );
					echo $this->add_description( esc_html__( $message ), 'xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][to]" placeholder="E.g. customer_email@gmail.com" value="{{to}}"/>
                </div>
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'Subject', 'wp-marketing-automations' ); ?>
					<?php
					$message = __( 'Email subject', 'wp-marketing-automations' );
					echo $this->add_description( esc_html__( $message ), 'm', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <input required type="text" id='bwfan_email_subject' class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][subject]" placeholder="Enter Subject" value="{{subject}}"/>
                </div>
                <div class="bwfan_email_template {{show_email_heading}}">
                    <label for="" class="bwfan-label-title">
						<?php esc_html_e( 'Email Heading', 'wp-marketing-automations' ); ?>
						<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </label>
                    <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                        <input type="text" id='bwfan_email_heading' class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][email_heading]" placeholder="Your Store Name" value="{{email_heading}}"/>
                    </div>
                </div>
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Body', 'wp-marketing-automations' ); ?></label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15 bwfan-email-wysiwyg {{is_enable_wysiwyg}}">
                    <textarea class="bwfan-input-wrapper" id="bwfan-editor" rows="6" placeholder="<?php esc_html_e( 'Email Message', 'wp-marketing-automations' ); ?>" name="bwfan[{{data.action_id}}][data][body]">{{body}}</textarea>
                </div>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15 bwfan-email-textarea {{is_enable_textarea}}">
                    <textarea class="bwfan-input-wrapper" id="bwfan-raw_textarea" rows="15" placeholder="<?php esc_html_e( 'Email Message with HTML/CSS', 'wp-marketing-automations' ); ?>" name="bwfan[{{data.action_id}}][data][body_raw]">{{body_raw}}</textarea>
                </div>

                <div class="bwfan_preview_email_container bwfan-mb-15">
                    <a href="javascript:void(0);" class="bwfan_preview_email"><?php esc_html_e( 'Generate Preview', 'wp-marketing-automations' ); ?></a>
                </div>

                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Send Test Mail', 'wp-marketing-automations' ); ?></label>
                <div class="bwfan_send_test_email bwfan-mb-15">
                    <input type="email" name="test_email" id="bwfan_test_email">
                    <input type="button" id="bwfan_test_email_btn" class="button bwfan-btn-inner" value="<?php esc_html_e( 'Send', 'wp-marketing-automations' ); ?>">
                </div>

                <div class="bwfan-input-form bwfan-row-sep"></div>
                <label for="" class="bwfan-label-title">Other</label>
                <div class="bwfan_email_tracking bwfan-mb-15">
                    <label for="bwfan_promotional_email">
                        <input type="checkbox" name="bwfan[{{data.action_id}}][data][promotional_email]" id="bwfan_promotional_email" value="1" {{is_promotional}}/>
						<?php
						esc_html_e( 'Mark as Promotional', 'wp-marketing-automations' );
						$message = __( "Email marked as promotional will not be send to the unsubscribers.", 'wp-marketing-automations' );
						echo $this->add_description( esc_html__( $message ), 'xl' ); //phpcs:ignore WordPress.Security.EscapeOutput
						?>
                    </label>
                </div>
				<?php
				do_action( 'bwfan_' . $this->get_slug() . '_setting_html', $this )
				?>
            </div>
        </script>
        <script>
            jQuery(document).ready(function ($) {
                /** Email heading functionality for woocommerce */
                $('body').on('change', '#bwfan_email_template', function (event) {
                    var $this = jQuery(this);
                    var selected_template = $this.val();
                    $this.parents('.bwfan-wp_sendemail').attr('data-temp', selected_template);
                    if ('raw' === selected_template) {
                        jQuery('.bwfan_email_template').hide();
                        jQuery('.bwfan-email-wysiwyg').hide();
                        jQuery('.bwfan-email-textarea').show();
                    } else if ('raw_template' === selected_template) {
                        jQuery('.bwfan_email_template').hide();
                        jQuery('.bwfan-email-wysiwyg').show();
                        jQuery('.bwfan-email-textarea').hide();
                    } else {
                        jQuery('.bwfan_email_template').show();
                        jQuery('.bwfan-email-wysiwyg').show();
                        jQuery('.bwfan-email-textarea').hide();
                    }
                });

                /** UTM parameters functionality */
                $('body').on('change', '#bwfan_append_utm', function (event) {
                    var $this = jQuery(this);
                    if ($this.is(":checked")) {
                        jQuery('.bwfan_utm_sources').show();
                    } else {
                        jQuery('.bwfan_utm_sources').hide();
                    }
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
		$global_email_settings = BWFAN_Common::get_global_email_settings();
		$data_to_set           = array(
			'subject'           => BWFAN_Common::decode_merge_tags( $task_meta['data']['subject'] ),
			'email'             => BWFAN_Common::decode_merge_tags( $task_meta['data']['to'] ),
			'name'              => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
			'email_heading'     => BWFAN_Common::decode_merge_tags( $task_meta['data']['email_heading'] ),
			'template'          => $task_meta['data']['template'],
			'promotional_email' => ( isset( $task_meta['data']['promotional_email'] ) ) ? 1 : 0,
			'append_utm'        => ( isset( $task_meta['data']['append_utm'] ) ) ? 1 : 0,
			'utm_source'        => ( isset( $task_meta['data']['utm_source'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_source'] ) : '',
			'utm_medium'        => ( isset( $task_meta['data']['utm_medium'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_medium'] ) : '',
			'utm_campaign'      => ( isset( $task_meta['data']['utm_campaign'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_campaign'] ) : '',
			'utm_term'          => ( isset( $task_meta['data']['utm_term'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['utm_term'] ) : '',
			'event'             => $task_meta['event_data']['event_slug'],
			'body'              => ( 'raw' === $task_meta['data']['template'] ) ? $task_meta['data']['body_raw'] : $task_meta['data']['body'],
			'from_email'        => $global_email_settings['bwfan_email_from'],
			'from_name'         => $global_email_settings['bwfan_email_from_name'],
			'reply_to_email'    => $global_email_settings['bwfan_email_reply_to'],
		);

		$data_to_set['body'] = stripslashes( $data_to_set['body'] );
		if ( true === $this->is_preview ) {
			$this->preview_body  = $data_to_set['body'];
			$data_to_set['body'] = BWFAN_Common::decode_merge_tags( $data_to_set['body'] );
			$data_to_set['body'] = apply_filters( 'bwfan_before_send_email_body', $data_to_set['body'], $data_to_set );
			$data_to_set['body'] = $this->email_content( $data_to_set );
			$data_to_set['body'] = BWFAN_Common::bwfan_correct_protocol_url( $data_to_set['body'] );
		}

		return apply_filters( 'bwfan_sendemail_make_data', $data_to_set, $task_meta );
	}

	public function email_content( $data ) {
		$body = '';
		if ( method_exists( $this, 'email_body_' . $data['template'] ) ) {
			$body = call_user_func( [ $this, 'email_body_' . $data['template'] ], $data );
		}

		return $body;
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
		global $wpdb;
		$this->set_data( $action_data['processed_data'] );
		$this->data['task_id'] = $action_data['task_id'];
		$sql_query             = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query             = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' ); // WPCS: unprepared SQL OK
		$gids                  = BWFAN_Model_Taskmeta::get_results( $sql_query );
		$this->data['gid']     = '';

		if ( ! empty( $gids ) && is_array( $gids ) ) {
			foreach ( $gids as $gid ) {
				$this->data['gid'] = $gid['meta_value'];

			}
		}

		if ( 1 === absint( $this->data['promotional_email'] ) && ( false === apply_filters( 'bwfan_force_promotional_email', false, $this->data ) ) ) {
			$to     = trim( stripslashes( $this->data['email'] ) );
			$emails = explode( ',', $to );

			$emails = array_map( function ( $email ) {
				return trim( $email );
			}, $emails );

			$where             = array(
				'recipient' => $emails,
				'mode'      => 1,
			);
			$check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where, false );

			if ( ! empty( $check_unsubscribe ) && is_array( $check_unsubscribe ) ) {
				$check_unsubscribe = array_map( function ( $unsubscribe_row ) {
					return $unsubscribe_row['recipient'];
				}, $check_unsubscribe );

				$unsubscribed_emails = implode( ', ', array_unique( $check_unsubscribe ) );

				return array(
					'status'  => 4,
					'message' => __( 'User(s) are already unsubscribed, with email(s): ' . $unsubscribed_emails, 'wp-marketing-automations' ),
				);
			}
		}

		$result = $this->process();
		if ( true === $result ) {
			return array(
				'status' => 3,
			);
		}

		if ( is_array( $result ) && isset( $result['message'] ) ) {
			return array(
				'status'  => 4,
				'message' => $result['message'],
			);
		}

		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error occurred during Send Email', 'wp-marketing-automations' ),
		);
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->send_email();
	}

	/**
	 * Send an Email.
	 *
	 * subject, body , email are required.
	 *
	 * @return array|bool
	 */
	public function send_email() {
		$to        = trim( stripslashes( $this->data['email'] ) );
		$subject   = stripslashes( $this->data['subject'] );
		$headers   = [];
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'From: ' . $this->data['from_name'] . ' <' . $this->data['from_email'] . '>';
		$headers[] = 'Content-type:text/html;charset=UTF-8';
		if ( isset( $this->data['reply_to_email'] ) && ! empty( $this->data['reply_to_email'] ) ) {
			$headers[] = 'Reply-To:  ' . $this->data['reply_to_email'];
		}

		if ( empty( $subject ) ) {
			return array(
				'message' => __( 'Email subject missing. Please provide subject to send email.', 'wp-marketing-automations' ),
			);
		}
		if ( empty( $to ) ) {
			return array(
				'message' => __( 'Recipient email missing. Please provide email to send email.', 'wp-marketing-automations' ),
			);
		}

		/** Send Email */
		$global_settings = BWFAN_Common::get_global_settings();
		$emails          = explode( ',', $to );
		$emails          = array_map( function ( $email ) {
			return trim( $email );
		}, $emails );

		if ( true === $this->is_preview ) {
			$this->data['body'] = $this->preview_body;
		}

		$body = $this->data['body'];

		/** Set content type to prevent conflict with other plugins who are using 'wp_mail_content_type' filter */
		add_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ), 999 );

		if ( ! isset( $global_settings['bwfan_email_service'] ) || 'wp' === $global_settings['bwfan_email_service'] ) {
			foreach ( $emails as $email ) {
				$this->data['email'] = $email;
				$this->data['body']  = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				$this->data['body']  = apply_filters( 'bwfan_before_send_email_body', $this->data['body'], $this->data );
				$this->data['body']  = $this->email_content( $this->data );
				$this->data['body']  = BWFAN_Common::bwfan_correct_protocol_url( $this->data['body'] );
				$res                 = wp_mail( $email, $subject, $this->data['body'], $headers );
				$this->data['body']  = $body; // Set the original body to use correct body in email.
			}
		} else {
			// Every connector which registers itself for email service must have send_email() in its integration class.
			foreach ( $emails as $email ) {
				$this->data['email']    = $email;
				$this->data['body']     = BWFAN_Common::decode_merge_tags( $this->data['body'] );
				$this->data['body']     = apply_filters( 'bwfan_before_send_email_body', $this->data['body'], $this->data );
				$this->data['body']     = $this->email_content( $this->data );
				$this->data['body']     = BWFAN_Common::bwfan_correct_protocol_url( $this->data['body'] );
				$autonami_integrations  = BWFAN_Core()->integration->get_integrations();
				$selected_email_service = $global_settings['bwfan_email_service'];
				$res                    = isset( $autonami_integrations[ $selected_email_service ] ) ? $autonami_integrations[ $selected_email_service ]->send_email( $email, $subject, $this->data['body'], $headers ) : wp_mail( $email, $subject, $this->data['body'], $headers );
				$this->data['body']     = $body; // Set the original body to use correct body in email.
			}
		}

		remove_filter( 'wp_mail_content_type', array( $this, 'set_email_content_type' ), 999 );

		if ( ! $res ) {
			return $this->maybe_get_failed_mail_error();
		}

		return true;
	}

	public function maybe_get_failed_mail_error() {
		global $phpmailer;

		if ( ! class_exists( '\WPMailSMTP\MailCatcher' ) ) {
			return false;
		}

		if ( ! ( $phpmailer instanceof \WPMailSMTP\MailCatcher ) ) {
			return false;
		}

		$debug_log = get_option( 'wp_mail_smtp_debug', false );
		if ( empty( $debug_log ) || ! is_array( $debug_log ) ) {
			return false;
		}

		return array( 'message' => $debug_log[0] );
	}

	public function set_email_content_type( $content_type ) {
		return 'text/html';
	}

	public function before_executing_task() {
		add_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		add_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		add_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		add_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_change_tasks_retry_limit', [ $this, 'modify_retry_limit' ], 99 );
		remove_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		remove_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		remove_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function modify_retry_limit( $retry_data ) {
		$retry_data[] = DAY_IN_SECONDS;

		return $retry_data;
	}

	public function add_email_preview_class( $classes ) {
		if ( isset( $_GET['section'] ) && 'preview_email' === $_GET['section'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$classes .= ' bwfan_preview_email';
		}

		return $classes;
	}

	/**
	 * Outputs WC template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_wc_template( $data ) {
		$email_body    = $data['body'];
		$email_heading = $data['email_heading'];
		$mailer        = WC()->mailer();

		// If promotional checkbox is not checked, then remove {{unsubscribe_link}} merge tag
		if ( isset( $data['promotional_email'] ) && 0 === absint( $data['promotional_email'] ) ) {
			remove_filter( 'woocommerce_email_footer_text', array( $this, 'add_unsubscribe_merge_tag' ) );
		}

		ob_start();
		$mailer->email_header( $email_heading );
		echo $email_body; //phpcs:ignore WordPress.Security.EscapeOutput
		$mailer->email_footer();
		$email_body            = ob_get_clean();
		$email_abstract_object = new WC_Email();

		return apply_filters( 'woocommerce_mail_content', $email_abstract_object->style_inline( wptexturize( $email_body ) ) );
	}

	/**
	 * Outputs Custom template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_raw_template( $data ) {
		$email_body = $this->prepare_email_content( $data['body'] );

		ob_start();
		include BWFAN_PLUGIN_DIR . '/templates/email-styles.php';
		$css = ob_get_clean();

		if ( BWFAN_Common::supports_emogrifier() ) {
			$emogrifier_class = '\\Pelago\\Emogrifier';
			if ( ! class_exists( $emogrifier_class ) ) {
				include_once BWFAN_PLUGIN_DIR . '/libraries/class-emogrifier.php';
			}
			try {
				/** @var \Pelago\Emogrifier $emogrifier */
				$emogrifier = new $emogrifier_class( $email_body, $css );
				$email_body = $emogrifier->emogrify();
			} catch ( Exception $e ) {
				BWFAN_Core()->logger->log( $e->getMessage(), 'send_email_emogrifier' );
			}
		} else {
			$email_body = '<style type="text/css">' . $css . '</style>' . $email_body;
		}

		return $email_body;
	}

	/**
	 * Outputs RAW HTML/CSS template email body
	 *
	 * @param $data
	 *
	 * @return string
	 */
	protected function email_body_raw( $data ) {
		return $data['body'];
	}

	/**
	 * @param $content
	 *
	 * @return string|null
	 */
	private function prepare_email_content( $content ) {
		$has_body      = stripos( $content, '<body' ) !== false;
		$preview_class = $this->is_preview ? 'bwfan_email_preview' : '';

		/** Check if body tag exists */
		if ( ! $has_body ) {
			return '<html><head></head><body><div id="body_content" class="' . $preview_class . '">' . $content . '</div></body></html>';
		}

		$pattern     = "/<body(.*?)>(.*?)<\/body>/is";
		$replacement = '<body$1><div id="body_content" class="' . $preview_class . '">$2</div></body>';

		return preg_replace( $pattern, $replacement, $content );
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Wp_Sendemail';
