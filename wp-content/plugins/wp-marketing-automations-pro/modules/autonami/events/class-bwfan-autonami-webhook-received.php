<?php

/**
 * Class BWFAN_Autonami_Webhook_Received
 */
final class BWFAN_Autonami_Webhook_Received extends BWFAN_Event {
	private static $instance = null;
	private $automation_id = null;
	private $automation_key = '';
	private $localized_automation_key = '';

	private $webhook_data = array();
	private $referer = '';
	private $received_at = 0;
	private $email = '';
	private $email_map_key = '';

	private function __construct() {
		$this->optgroup_label         = __( 'Autonami', 'autonami-automations' );
		$this->event_name             = __( 'Webhook Received', 'autonami-automations' );
		$this->event_desc             = __( 'This event runs after a webhook URL receives the data', 'autonami-automations' );
		$this->event_merge_tag_groups = array( 'wp_webhook' );
		$this->event_rule_groups      = array();
	}

	public function bwfan_add_webhook_endpoint() {
		register_rest_route( 'autonami/v1', '/webhook(?:/(?P<bwfan_autonami_webhook_id>\d+))?', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bwfan_capture_async_events' ),
			'permission_callback' => '__return_true',
			'args'                => [
				'bwfan_autonami_webhook_id',
				'bwfan_autonami_webhook_key',
			],
		) );
	}

	public function bwfan_capture_async_events( WP_REST_Request $request ) {
		$request_params = $request->get_params();

		//check if url params is empty or not
		if ( empty( $request_params ) ) {
			return;
		}

		//check request params contain both the key and id
		if ( ( ! isset( $request_params['bwfan_autonami_webhook_key'] ) && empty( $request_params['bwfan_autonami_webhook_key'] ) ) && ( ! isset( $request_params['bwfan_autonami_webhook_id'] ) && empty( $request_params['bwfan_autonami_webhook_id'] ) ) ) {
			return;
		}

		//get automation key using automation id
		$automation_id  = $request_params['bwfan_autonami_webhook_id'];
		$meta           = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$automation_key = $meta['bwfan_unique_key'];

		//check if the automation key exist in database
		if ( empty( $automation_key ) ) {
			return;
		}

		//validate automation key
		if ( $automation_key !== $request_params['bwfan_autonami_webhook_key'] ) {
			return;
		}

		$webhook_data = $request_params;
		unset( $webhook_data['bwfan_autonami_webhook_key'] );
		unset( $webhook_data['bwfan_autonami_webhook_id'] );

		/** Set Webhook Data in args */
		$args = array(
			'webhook_data' => $webhook_data,
			'received_at'  => ( new DateTime() )->getTimestamp(),
			'referer'      => $request->get_header( 'referer' ),
		);

		/** Save the webhook data in event meta of automation */
		$meta            = array_replace( $meta, $args );
		$data_to_update  = array( 'meta_value' => maybe_serialize( $meta ) );
		$where_to_update = array(
			'bwfan_automation_id' => $automation_id,
			'meta_key'            => 'event_meta'
		);
		BWFAN_Model_Automationmeta::update( $data_to_update, $where_to_update );

		/** Only run this when automation is active. (Check is necessary because of Capture Data perspective) */
		$automation_data = BWFAN_Model_Automations::get( $automation_id );
		if ( 1 === absint( $automation_data['status'] ) ) {
			do_action( 'bwfan_wp_connector_sync_call', $meta );
		}

		/** Send back 200 response */
		wp_send_json( array( 'success' => true ) );
	}

	public function load_hooks() {
		add_action( 'rest_api_init', array( $this, 'bwfan_add_webhook_endpoint' ) );
		add_action( "bwfan_wp_connector_sync_call", array( $this, 'before_process_webhook' ), 10 );
		add_action( 'bwfan_webhook_autonami', array( $this, 'process' ), 10, 5 );
		add_action( 'wp_ajax_bwfan_get_refresh_data', array( $this, 'send_latest_webhook_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'bwfan_webhook_admin_enqueue_assets' ), 98 );
	}

	public function before_process_webhook( $args ) {
		$hook  = 'bwfan_webhook_autonami';
		$group = 'autonami';

		if ( bwf_has_action_scheduled( $hook, $args, $group ) ) {
			return;
		}
		bwf_schedule_single_action( time(), $hook, $args, $group );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_email_event() {
		return is_email( $this->email ) ? $this->email : false;
	}

	public function get_user_id_event() {
		$user = is_email( $this->email ) ? get_user_by( 'email', $this->email ) : false;

		return ( $user instanceof WP_User ) ? $user->ID : false;
	}

	public function bwfan_webhook_admin_enqueue_assets() {
		$this->automation_id = isset( $_GET['edit'] ) ? sanitize_text_field( $_GET['edit'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		$meta                = BWFAN_Model_Automationmeta::get_meta( $this->automation_id, 'event_meta' );
		if ( ! empty( $this->automation_id ) ) {
			if ( isset( $meta['bwfan_unique_key'] ) && ! empty( $meta['bwfan_unique_key'] ) ) {
				$this->localized_automation_key = $meta['bwfan_unique_key'];
			}
		}

		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'automation_id', $this->automation_id );
		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'saved_localized_automation_key', $this->localized_automation_key );

		/** Set Event UI Data, if webhook data received */
		if ( isset( $meta['webhook_data'] ) ) {
			$webhook_data_received_time = date( 'm-d-Y h:i A', absint( $meta['received_at'] ) );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'webhook_data', $meta['webhook_data'] );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'webhook_data_received_formatted', $webhook_data_received_time );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'webhook_data_received', absint( $meta['received_at'] ) );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'webhook_referer', $meta['referer'] );
			if ( isset( $meta['bwfan_email_map_key'] ) ) {
				BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'bwfan_email_map_key', $meta['bwfan_email_map_key'] );
			}
		}
	}

	public function send_latest_webhook_data() {
		$nonce_check_failed = ( ! isset( $_POST['nonce'] ) || false === wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'bwfan-action-admin' ) );
		if ( $nonce_check_failed || ! isset( $_POST['automation_id'] ) ) {
			wp_send_json( array(
				'status' => 'failed'
			) );
		}

		$automation_id = absint( sanitize_text_field( $_POST['automation_id'] ) );
		$meta          = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		if ( ! isset( $meta['bwfan_unique_key'] ) || ! isset( $meta['webhook_data'] ) ) {
			wp_send_json( array(
				'status' => 'failed'
			) );
		}

		$payload = array(
			'saved_localized_automation_key'  => $meta['bwfan_unique_key'],
			'webhook_data'                    => $meta['webhook_data'],
			'webhook_data_received_formatted' => date( 'm-d-Y h:i A', absint( $meta['received_at'] ) ),
			'webhook_data_received'           => $meta['received_at'],
			'webhook_referer'                 => $meta['referer']
		);
		if ( isset( $meta['bwfan_email_map_key'] ) ) {
			$payload['bwfan_email_map_key'] = $meta['bwfan_email_map_key'];
		}

		wp_send_json( $payload );
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();

		?>
		<?php
		if ( isset( $global_data['email'] ) && ! empty( $global_data['email'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'Email: ', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['email'] ); ?>
            </li>
			<?php
		}

		if ( ! isset( $global_data['webhook_data'] ) || empty( $global_data['webhook_data'] ) || ! is_array( $global_data['webhook_data'] ) ) {
			return ob_get_clean();
		}

		$i = 0;
		foreach ( $global_data['webhook_data'] as $key => $value ) {
			if ( $key === $global_data['email_map_key'] ) {
				continue;
			}

			if ( $i >= 2 ) {
				break;
			}

			?>
            <li>
                <strong><?php echo $key; ?>: </strong><?php echo $value; ?>
            </li>
			<?php
			$i ++;
		}

		return ob_get_clean();
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_event_meta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php esc_attr_e( $this->get_slug() ); ?>">
            <div class="bwfan_wp_webhook_wrapper">
                <#
                var eventslug = '<?php esc_html_e( $this->get_slug() ); ?>';
                var eventData = bwfan_events_js_data[eventslug];
                var event_save_unique_key = eventData.saved_localized_automation_key;
                if(event_save_unique_key.length>0){
                eventData.localized_automation_key = event_save_unique_key
                }
                var webhook_url = '<?php esc_attr_e( home_url( '/' ) ); ?>';
                webhook_url = webhook_url + 'wp-json/autonami/v1/webhook?bwfan_autonami_webhook_id='+eventData.automation_id+'&bwfan_autonami_webhook_key='+eventData.localized_automation_key;

                var webhook_data = _.has(eventData, 'webhook_data') ? eventData.webhook_data : false;
                var webhook_data_received = _.has(eventData, 'webhook_data_received') ? eventData.webhook_data_received : false;
                var webhook_data_received_formatted = _.has(eventData, 'webhook_data_received_formatted') ? eventData.webhook_data_received_formatted : false;
                var webhook_referer = _.has(eventData, 'webhook_referer') ? eventData.webhook_referer : false;
                var bwfan_email_map_key = _.has(eventData, 'bwfan_email_map_key') ? eventData.bwfan_email_map_key : '';
                #>
                <div class="bwfan_mt15"></div>
                <label for="bwfan-webhook-url" class="bwfan-label-title"><?php esc_html_e( 'Custom Webhook URL', 'autonami-automations-pro' ); ?></label>
                <div class="bwfan-textarea-box">
                    <textarea name="event_meta[bwfan_webhook_url]" class="bwfan-input-wrapper bwfan-webhook-url" id="bwfan-webhook-url" cols="45" rows="2" onclick="select();" readonly>{{webhook_url}}</textarea>
                    <input type="hidden" name="event_meta[bwfan_unique_key]" id="bwfan-unique-key" value={{eventData.localized_automation_key}}>
                    <div class="clearfix bwfan_field_desc bwfan-pt-5">
                        Use this custom webhook URL to send requests to.
                    </div>
                </div>

                <# if( false === webhook_data ) { #>
                <div class="bwfan_mt20"></div>
                <div class="clearfix bwfan-mb-15">
                    <label for="" class="bwfan-label-title"><?php esc_html_e( 'Find Data', 'autonami-automations-pro' ); ?></label>
                </div>
                <# } #>

                <!-- Test Data Display -->
                <#
                if( false !== webhook_data ) { #>
                <div class="bwfan_mt20"></div>
                <label for="bwfan-webhook-url" class="bwfan-label-title"><?php esc_html_e( 'Data Found', 'autonami-automations-pro' ); ?></label>
                <table style="border: 1px solid #dadada;width: 100%;text-align: left; padding: 8px 15px;">
                    <# _.each( webhook_data, function( value, key ){ #>
                    <tr style="border-bottom: 1px solid #aaa;">
                        <td style="padding: 5px 0;"><b>{{key}}</b></td>
                        <td>{{value}}</td>
                    </tr>
                    <input type="hidden" value="{{value}}" name="event_meta[webhook_data][{{key}}]"/>
                    <# }); #>
                </table>
                <div class="bwfan_field_desc">
                    Received at {{webhook_data_received_formatted}}
                </div>
                <input type="hidden" value="{{webhook_referer}}" name="event_meta[referer]"/>
                <input type="hidden" value="{{webhook_data_received}}" name="event_meta[received_at]"/>
                <div class="bwfan_mt10"></div>
                <a class="button bwfan_refresh_webhook_data_button"><?php esc_html_e( 'Refresh Data', 'autonami-automations-pro' ); ?></a>

                <!-- Select Email Map Field -->
                <div class="bwfan_mt20"></div>
                <label for="bwfan-webhook-url" class="bwfan-label-title">
					<?php esc_html_e( 'Select email field to map', 'autonami-automations-pro' ); ?>
                    <div class="bwfan_tooltip" data-size="2xl">
                        <span class="bwfan_tooltip_text" data-position="top"><?php esc_html_e( 'Map the email field to be used by appropriate Rules and Actions.', 'wp-marketing-automations' ); ?></span>
                    </div>
                </label>
                <select class="bwfan-input-wrapper" id="bwfan_email_map_key_dropdown" name="event_meta[bwfan_email_map_key]">
                    <option value="">Select Data Key</option>
                    <# _.each( webhook_data, function( value, key ){
                    selected = key == bwfan_email_map_key ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{key}}</option>
                    <# }); #>
                </select>
                <!-- END: Select Email Map Field -->
                <# } else { #>
                <div align="center">
                    <img width="50" src="<?php esc_attr_e( BWFAN_PRO_PLUGIN_URL . '/admin/assets/webhook.png' ); ?>"/>
                    <label class="bwfan-label-title bwfan_mt10">Test Your Event</label>
                    <div class="bwfan_mt5 bwfan_mb10">Send a request to the webhook URL.</div>
                    <a class="button bwfan_refresh_webhook_data_button"><?php esc_html_e( 'Test Webhook', 'autonami-automations-pro' ); ?></a>
                    <div class="bwfan_mt10 bwfan_webhook_error"></div>
                </div>
                <# } #>
                <!-- END: Test Data Display -->
            </div>
        </script>

        <script>
            /** To make sure that the email map value is not destroyed on mounting of another admin event or action component */
            jQuery('body').on('change', '#bwfan_email_map_key_dropdown', function () {
                bwfan_events_js_data['autonami_webhook_received']['bwfan_email_map_key'] = jQuery('#bwfan_email_map_key_dropdown').val();
            });

            /** TO Pass the saved fields data to the merge tags */
            jQuery('body').on('bwfan-selected-merge-tag', function (e, v) {
                if ('wp_webhook_data' !== v.tag) {
                    return;
                }

                var options = '';
                var i = 1;
                var selected = '';

                _.each(bwfan_events_js_data['autonami_webhook_received']['webhook_data'], function (value, key) {
                    selected = (i == 1) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + selected + '>' + key + '</option>';
                    i++;
                });

                jQuery('.bwfan_wp_webhook_keys').html(options);
                jQuery('.bwfan_tag_select').trigger('change');
            });

            /** Get latest data, store it in 'bwfan_events_js_data' and Refresh the view to load the latest data */
            jQuery('body').on('click', '.bwfan_refresh_webhook_data_button', function () {
                const automation_id = bwfan_events_js_data['autonami_webhook_received']['automation_id'];
                const payload = {
                    automation_id: automation_id,
                    action: 'bwfan_get_refresh_data',
                    nonce: bwfanParams.ajax_nonce
                };

                const thisButton = jQuery(this);
                thisButton.addClass('bwfan_btn_spin_blue');
                jQuery.post(bwfanParams.ajax_url, payload, function (data) {
                    thisButton.removeClass('bwfan_btn_spin_blue');

                    if (!_.isObject(data) || (_.has(data, 'status') || false === data.status) || !_.has(data, 'webhook_data')) {
                        /** Failure, no data fetched */
                        jQuery('.bwfan_webhook_error').html('No Data Found! Test Again.');
                        return;
                    }

                    _.each(data, function (value, key) {
                        bwfan_events_js_data['autonami_webhook_received'][key] = value;
                    });

                    let $iziWrap = jQuery("#modal_automation_success");
                    if ($iziWrap.length > 0) {
                        $iziWrap.iziModal('setTitle', 'Latest Data Loaded');
                        $iziWrap.iziModal('open');
                    }

                    jQuery('.bwfan_wp_webhook_wrapper').remove();
                    BWFAN_Actions.create_event_meta_ui('<?php esc_attr_e( $this->get_slug() ); ?>');
                });
            });
        </script>
		<?php
	}

	public function pre_executable_actions( $automation_data ) {
		$email_map           = $automation_data['event_meta']['bwfan_email_map_key'];
		$this->email_map_key = ! empty( $email_map ) ? $email_map : '';
		$this->email         = ( ! empty( $email_map ) && isset( $this->webhook_data[ $email_map ] ) && is_email( $this->webhook_data[ $email_map ] ) ) ? $this->webhook_data[ $email_map ] : '';
	}


	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 */
	public function process( $webhook_url, $webhook_key, $webhook_data, $referer, $received_at ) {
		$data                   = $this->get_default_data();
		$data['automation_key'] = $webhook_key;
		$data['webhook_data']   = $webhook_data;
		$data['referer']        = $referer;
		$data['received_at']    = $received_at;

		$this->send_async_call( $data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->automation_key = BWFAN_Common::$events_async_data['automation_key'];
		$this->webhook_data   = BWFAN_Common::$events_async_data['webhook_data'];
		$this->referer        = BWFAN_Common::$events_async_data['referer'];
		$this->received_at    = BWFAN_Common::$events_async_data['received_at'];

		return $this->run_automations();
	}

	/**
	 * Check if the Mautic Key retrieved from request params is equal to unique key of automation
	 * @return array|bool
	 */
	public function run_automations() {
		BWFAN_Core()->public->load_active_automations( $this->get_slug() );
		if ( ! is_array( $this->automations_arr ) || count( $this->automations_arr ) === 0 ) {
			if ( $this->sync_start_time > 0 ) {
				/** Sync process */
				BWFAN_Core()->logger->log( 'Sync #' . $this->sync_id . '. No active automations found for Event ' . $this->get_slug(), 'sync' );

				return false;
			}
			BWFAN_Core()->logger->log( 'Async callback: No active automations found. Event - ' . $this->get_slug(), $this->log_type );

			return false;
		}

		$automation_actions = [];

		foreach ( $this->automations_arr as $automation_id => $automation_data ) {
			if ( $this->get_slug() !== $automation_data['event'] || 0 !== intval( $automation_data['requires_update'] ) ) {
				continue;
			}
			//check if the automation_key match with the post data
			if ( isset( $automation_data['event_meta']['bwfan_unique_key'] ) && $this->automation_key === $automation_data['event_meta']['bwfan_unique_key'] ) {
				$ran_actions = $this->handle_single_automation_run( $automation_data, $automation_id );
			}


			$automation_actions[ $automation_id ] = $ran_actions;
		}

		return $automation_actions;
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}

		$meta = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );

		if ( '' === $meta || ! is_array( $meta ) ) {
			return;
		}

		$data_to_send = $this->get_event_data();
		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                             = [];
		$data_to_send['global']['automation_key'] = $this->automation_key;
		$data_to_send['global']['webhook_data']   = $this->webhook_data;
		$data_to_send['global']['received_at']    = $this->received_at;
		$data_to_send['global']['referer']        = $this->referer;
		$data_to_send['global']['email']          = $this->email;
		$data_to_send['global']['email_map_key']  = $this->email_map_key;


		return $data_to_send;
	}

	public function set_merge_tags_data( $task_meta ) {
		$merge_data                 = [];
		$merge_data['webhook_data'] = $task_meta['global']['webhook_data'];
		$merge_data['received_at']  = $task_meta['global']['received_at'];
		$merge_data['referer']      = $task_meta['global']['referer'];
		BWFAN_Merge_Tag_Loader::set_data( $merge_data );
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */

return 'BWFAN_Autonami_Webhook_Received';
