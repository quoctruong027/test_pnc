<?php

final class BWFAN_WCS_Before_Renewal extends BWFAN_Event {
	private static $instance = null;
	public $subscription = null;
	public $subscription_id = null;

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wc_subscription' );
		$this->event_name             = esc_html__( 'Subscriptions Before Renewal', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs every day and checks for the subscriptions which are about to occur.', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'wc_subscription', 'wc_customer' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->support_lang           = true;
		$this->priority               = 25.3;
		$this->is_time_independent    = true;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'bwfan_subscription_before_renewal_triggered', array( $this, 'capture_subscription' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'last_run', $data['last_run'] );
		}
	}

	public function get_view_data() {

		$last_run = '';
		if ( isset( $_GET['edit'] ) ) {
			$last_run = BWFAN_Model_Automationmeta::get_meta( sanitize_text_field( $_GET['edit'] ), 'last_run' );
			if ( false !== $last_run ) {
				$last_run = date( get_option( 'date_format' ), strtotime( $last_run ) );
			}
		}

		return [
			'last_run' => $last_run,
		];
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            days_entered = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'days_before')) ? data.eventSavedData.days_before : '15';
            hours_entered = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'hours')) ? data.eventSavedData.hours : 11;
            minutes_entered = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'minutes')) ? data.eventSavedData.minutes : '';
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-12 bwfan-p-0">
                <label for="bwfan-days_before" class="bwfan-label-title"><?php echo esc_html__( 'Days before subscription renewal', 'autonami-automations-pro' ); ?></label>
                <input required type="number" name="event_meta[days_before]" id="bwfan-days_before" class="bwfan-input-wrapper" value="{{days_entered}}"/>
            </div>
            <div class="bwfan-col-sm-12 bwfan_mt15 bwfan-p-0">
                <label for="bwfan-hours" class="bwfan-label-title"><?php echo esc_html__( 'Run at following (Store) Time of a Day', 'autonami-automations-pro' ); ?></label>
                <input max="23" min="0" type="number" name="event_meta[hours]" id="bwfan-hours" class="bwfan-input-wrapper bwfan-input-inline" value="{{hours_entered}}" placeholder="<?php echo esc_html__( 'HH', 'autonami-automations-pro' ); ?>"/>
                :
                <input max="59" min="0" type="number" name="event_meta[minutes]" id="bwfan-minutes" class="bwfan-input-wrapper bwfan-input-inline" value="{{minutes_entered}}"
                       placeholder="<?php echo esc_html__( 'MM', 'autonami-automations-pro' ); ?>"/>
                <# if( _.has(data.eventFieldsOptions, 'last_run') && '' != data.eventFieldsOptions.last_run ) { #>
                <div class="clearfix bwfan_field_desc"><?php echo esc_html__( 'This automation last ran on ', 'autonami-automations-pro' ); ?>{{data.eventFieldsOptions.last_run}}</div>
                <# } #>
            </div>
        </script>
		<?php
	}

	/**
	 * This is a time independent event. A cron is run once a day and it makes all the tasks for the current event.
	 *
	 * @param $automation_id
	 * @param $automation_details
	 *
	 * @throws Exception
	 */
	public function make_task_data( $automation_id, $automation_details ) {

		$date_time   = new DateTime();
		$current_day = $date_time->format( 'Y-m-d' );
		$last_run    = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'last_run' );

		if ( false !== $last_run ) {

			$where = [
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => 'last_run',
			];
			$data  = [
				'meta_value' => $current_day,
			];

			BWFAN_Model_Automationmeta::update( $data, $where );
		} else {
			$meta = [
				'bwfan_automation_id' => $automation_id,
				'meta_key'            => 'last_run',
				'meta_value'          => $current_day,
			];
			BWFAN_Model_Automationmeta::insert( $meta );
		}

		$days_before_renewal = $automation_details['meta']['event_meta']['days_before'];
		$date                = new \DateTime();
		$date->modify( '+' . BWFAN_Common::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' ); // get site time
		$date->modify( "+$days_before_renewal days" );

		$day_start = clone $date;
		$day_end   = clone $date;
		$day_start->setTime( 00, 00, 01 );
		$day_end->setTime( 23, 59, 59 );
		$day_start->modify( '-' . BWFAN_Common::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );
		$day_end->modify( '-' . BWFAN_Common::get_timezone_offset() * HOUR_IN_SECONDS . ' seconds' );

		$query = new \WP_Query( [
			'post_type'      => 'shop_subscription',
			'post_status'    => 'wc-active',
			'fields'         => 'ids',
			'posts_per_page' => - 1,
			'no_found_rows'  => true,
			'meta_query'     => [
				[
					'key'     => '_schedule_next_payment',
					'compare' => '>',
					'value'   => $day_start->format( 'Y-m-d H:i:s' ),
				],
				[
					'key'     => '_schedule_next_payment',
					'compare' => '<',
					'value'   => $day_end->format( 'Y-m-d H:i:s' ),
				],
			],
		] );

		if ( ! is_array( $query->posts ) || count( $query->posts ) === 0 ) {
			return;
		}
		foreach ( $query->posts as $subscription_id ) {
			do_action( 'bwfan_subscription_before_renewal_triggered', $subscription_id );
		}
	}

	public function validate_event_data_before_executing_task( $data ) {
		return $this->validate_subscription_status( $data );
	}

	/**
	 * @param $data
	 *
	 * @return bool
     * @todo:validate on task execution
	 */
	protected function validate_subscription_status( $data ) {
		if ( ! isset( $data['wc_subscription_id'] ) || ! function_exists( 'wcs_get_subscription' ) ) {
			return false;
		}

		$subscription = wcs_get_subscription( $data['wc_subscription_id'] );
		if ( ! $subscription instanceof WC_Subscription ) {
			return false;
		}
		$subscription_status = $subscription->get_status();
		if ( 'active' !== $subscription_status ) {
			return false;
		}

		return true;
	}

	public function capture_subscription( $subscription_id ) {
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return;
		}

		$subscription_failed_status = array( 'wc-cancelled', 'wc-expired', 'wc-pending-cancel' );

		$this->subscription_id = $subscription_id;
		$this->subscription    = wcs_get_subscription( $this->subscription_id );
		if ( ! $this->subscription instanceof WC_Subscription ) {
			return;
		}
		$subscription_status = $this->subscription->get_status();
		if ( in_array( $subscription_status, $subscription_failed_status, true ) ) {
			return;
		}
		$this->run_automations();
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
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['wc_subscription_id'] ) ) && function_exists( 'wcs_get_subscription' ) ) {
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
	 * Set up rules data
	 *
	 * @param $value
	 */
	public function pre_executable_actions( $value ) {
		BWFAN_Core()->rules->setRulesData( $this->subscription, 'wc_subscription' );
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
	return 'BWFAN_WCS_Before_Renewal';
}
