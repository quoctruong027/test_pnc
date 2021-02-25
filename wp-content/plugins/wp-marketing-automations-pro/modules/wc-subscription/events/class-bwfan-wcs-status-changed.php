<?php

final class BWFAN_WCS_Status_Changed extends BWFAN_Event {
	private static $instance = null;
	public $subscription = null;
	public $from_status = null;
	public $to_status = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_subscription' );
		$this->event_name             = esc_html__( 'Subscriptions Status Changed', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a subscription status is changed.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_subscription', 'wc_customer', 'wc_subscription_status' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25.1;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'woocommerce_subscription_status_updated', [ $this, 'subscription_status_changed' ], 11, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$integration_data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'from_options', $integration_data );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'to_options', $integration_data );
		}
	}

	public function get_view_data() {
		if ( ! function_exists( 'wcs_get_subscription_statuses' ) ) {
			return array();
		}
		$all_status               = wcs_get_subscription_statuses();
		$all_status['wc-on-hold'] = __( 'On Hold / Suspend', 'autonami-automations-pro' );

		return $all_status;
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            is_validated = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'validate_event')) ? 'checked' : '';
            selected_from_status = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'from')) ? data.eventSavedData.from : '';
            selected_to_status = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'to')) ? data.eventSavedData.to : '';
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-6 bwfan-pl-0">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Subscription Status From', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="event_meta[from]">
                    <option value="wc-any"><?php echo esc_html__( 'Any', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'from_options') && _.isObject(data.eventFieldsOptions.from_options) ) {
                    _.each( data.eventFieldsOptions.from_options, function( value, key ){
                    selected = (key == selected_from_status) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
            <div class="bwfan-col-sm-6 bwfan-pr-0">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Subscription Status To', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="event_meta[to]">
                    <option value="wc-any"><?php echo esc_html__( 'Any', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'to_options') && _.isObject(data.eventFieldsOptions.to_options) ) {
                    _.each( data.eventFieldsOptions.to_options, function( value, key ){
                    selected = (key == selected_to_status) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
            </div>
			<?php
			$this->get_validation_html( $this->get_slug(), 'Validate order status before executing task', 'Validate' );
			?>
        </script>
		<?php
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_subscription( $data );
	}

	public function handle_single_automation_run( $value1, $automation_id ) {
		$is_register_task = false;
		$to_status        = $this->to_status;
		$from_status      = $this->from_status;
		$event_meta       = $value1['event_meta'];
		$from             = str_replace( 'wc-', '', $event_meta['from'] );
		$to               = str_replace( 'wc-', '', $event_meta['to'] );

		if ( 'any' === $from && 'any' === $to ) {
			$is_register_task = true;
		} elseif ( 'any' === $from && $to_status === $to ) {
			$is_register_task = true;
		} elseif ( $from_status === $from && 'any' === $to ) {
			$is_register_task = true;
		} elseif ( $from_status === $from && $to_status === $to ) {
			$is_register_task = true;
		}

		if ( $is_register_task && function_exists( 'wcs_get_subscription_statuses' ) ) {
			$all_statuses   = wcs_get_subscription_statuses();
			$value1['from'] = $all_statuses[ 'wc-' . $from_status ];
			$value1['to']   = $all_statuses[ 'wc-' . $to_status ];

			return parent::handle_single_automation_run( $value1, $automation_id );
		}

		return '';
	}

	public function subscription_status_changed( $subscription, $to_status, $from_status ) {
		$subscription_id = $subscription->get_id();
		$this->process( $subscription_id, $from_status, $to_status );
	}

	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $subscription_id
	 * @param $from_status
	 * @param $to_status
	 */
	public function process( $subscription_id, $from_status, $to_status ) {
		$data                       = $this->get_default_data();
		$data['wc_subscription_id'] = $subscription_id;
		$data['from_status']        = $from_status;
		$data['to_status']          = $to_status;

		$this->send_async_call( $data );
	}

	/**
	 * Returns the current event settings set in the automation at the time of task creation.
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function get_automation_event_data( $value ) {
		$event_meta = $value['event_meta'];
		$event_data = [
			'event_source'   => $value['source'],
			'event_slug'     => $value['event'],
			'validate_event' => ( isset( $value['event_meta']['validate_event'] ) ) ? 1 : 0,
			'from_status'    => $event_meta['from'],
			'to_status'      => $event_meta['to'],
			'from'           => $value['from'],
			'to'             => $value['to'],
		];

		return $event_data;
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

		$data_to_send = $this->get_event_data();

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                                 = [];
		$data_to_send['global']['wc_subscription_id'] = is_object( $this->subscription ) ? $this->subscription->get_id() : '';
		$data_to_send['global']['wc_subscription']    = is_object( $this->subscription ) ? $this->subscription : '';
		$data_to_send['global']['email']              = is_object( $this->subscription ) ? $this->subscription->get_billing_email() : '';
		$data_to_send['global']['user_id']            = is_object( $this->subscription ) ? $this->get_user_id_event() : '';

		return $data_to_send;
	}

	public function get_user_id_event() {
		return $this->subscription->get_user_id();
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
        <li>
            <strong><?php echo esc_html__( 'Subscription ID:', 'autonami-automations-pro' ); ?> </strong>
            <a target="_blank" href="<?php echo get_edit_post_link( $global_data['wc_subscription_id'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo esc_html__( '#' . $global_data['wc_subscription_id'] ); ?></a>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Subscription Email:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['email'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	/**
	 * This function decides if the task has to be executed or not.
	 * The event has validate checkbox in its meta fields.
	 *
	 * @param $task_details
	 *
	 * @return array|mixed
	 */
	public function validate_event( $task_details ) {
		$result                                     = [];
		$task_event                                 = $task_details['event_data']['event_slug'];
		$automation_id                              = $task_details['processed_data']['automation_id'];
		$automation_details                         = BWFAN_Model_Automations::get( $automation_id );
		$current_automation_event                   = $automation_details['event'];
		$current_automation_event_meta              = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'event_meta' );
		$current_automation_event_validation_status = ( isset( $current_automation_event_meta['validate_event'] ) ) ? $current_automation_event_meta['validate_event'] : 0;
		$current_automation_status_from             = $current_automation_event_meta['from'];
		$current_automation_status_to               = $current_automation_event_meta['to'];
		$task_status_from                           = $task_details['event_data']['from_status'];
		$task_status_to                             = $task_details['event_data']['to_status'];

		// Current automation has no checking
		if ( 0 === $current_automation_event_validation_status ) {
			$result = $this->get_automation_event_validation();

			return $result;
		}

		// Current automation event does not match with the event of task when the task was made
		if ( $task_event !== $current_automation_event ) {
			$result = $this->get_automation_event_status();

			return $result;
		}
		if ( ( $current_automation_status_from === $task_status_from ) && ( $current_automation_status_to === $task_status_to ) ) {
			$result = $this->get_automation_event_success();

			return $result;
		}

		$result['status']  = 4;
		$result['message'] = __( 'Subscription Statuses in automation has been changed', 'autonami-automations-pro' );

		return $result;
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$subscription_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription_id' );
		if ( ( empty( $subscription_id ) || intval( $subscription_id ) !== intval( $task_meta['global']['wc_subscription_id'] ) ) && function_exists( 'wcs_get_subscription' ) ) {
			$set_data = array(
				'wc_subscription_id' => intval( $task_meta['global']['wc_subscription_id'] ),
				'email'              => $task_meta['global']['email'],
				'wc_order'           => $task_meta['global']['wc_order'],
				'user_id'            => $task_meta['global']['user_id'],
				'wc_subscription'    => $task_meta['global']['wc_subscription'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return false;
		}

		$subscription_id    = BWFAN_Common::$events_async_data['wc_subscription_id'];
		$from_status        = BWFAN_Common::$events_async_data['from_status'];
		$to_status          = BWFAN_Common::$events_async_data['to_status'];
		$subscription       = wcs_get_subscription( $subscription_id );
		$this->subscription = $subscription;
		$this->from_status  = $from_status;
		$this->to_status    = $to_status;

		return $this->run_automations();
	}

	/**
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->subscription, 'wc_subscription' );
		BWFAN_Core()->rules->setRulesData( $this->from_status, 'wcs_old_status' );
		BWFAN_Core()->rules->setRulesData( $this->event_automation_id, 'automation_id' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->get_email_event(), $this->get_user_id_event() ), 'bwf_customer' );
	}

	public function get_email_event() {
		return $this->subscription->get_billing_email();
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	return 'BWFAN_WCS_Status_Changed';
}
