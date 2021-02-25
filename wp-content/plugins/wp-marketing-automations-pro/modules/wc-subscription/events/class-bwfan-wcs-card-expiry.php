<?php

final class BWFAN_WCS_Card_Expiry extends BWFAN_Event {
	private static $instance = null;
	public $expire_token_id = null;
	public $expire_token_user_id = null;
	public $expire_token_meta = [];
	private $data = [];

	private function __construct() {
		$this->event_merge_tag_groups = array( 'wc_customer', 'wcs_card_expiry' );
		$this->event_rule_groups      = array( 'wc_customer' );
		$this->optgroup_label         = esc_html__( 'Subscription', 'autonami-automations-pro' );
		$this->event_name             = esc_html__( 'Customer Before Card Expiry', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs every day and checks for user\'s saved cards which are about to expire.', 'autonami-automations-pro' );
		$this->priority               = 55;
		$this->is_time_independent    = true;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'bwfan_card_expiry_reached', [ $this, 'run_event' ] );
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
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		$user_id = $global_data['expire_token_user_id'];
		$user    = get_user_by( 'ID', $user_id );

		if ( false === $user ) {
			return esc_html__( 'Customer not exists', 'autonami-automations-pro' );
		}
		ob_start();
		?>
        <li>
            <strong><?php echo esc_html__( 'Name:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $user->display_name ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Email:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $user->user_email ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Card:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( 'xxxx-xxxx-xxxx-' . $global_data['expire_token_meta']['last4'] ); ?>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Expiry:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['expire_token_meta']['expiry_month'] . '/' . $global_data['expire_token_meta']['expiry_year'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	public function get_view( $db_eventmeta_saved_value ) {
		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            days_before=(_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'days_before')) ? data.eventSavedData.days_before : '15';
            hours_entered=(_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'hours')) ? data.eventSavedData.hours : 11;
            minutes_entered=(_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'minutes')) ? data.eventSavedData.minutes : '';
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-12 bwfan-p-0">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Days before card expiry', 'autonami-automations-pro' ); ?></label>
                <input type="number" required id="" class="bwfan-input-wrapper" name="event_meta[days_before]" value="{{days_before}}">
            </div>
            <div class="bwfan-col-sm-12 bwfan_mt15 bwfan-p-0">
                <label for="bwfan-hours" class="bwfan-label-title"><?php echo esc_html__( 'Run at following (Store) Time of a Day', 'autonami-automations-pro' ); ?></label>
                <input max="23" min="0" type="number" name="event_meta[hours]" id="bwfan-hours" class="bwfan-input-wrapper bwfan-input-inline" value="{{hours_entered}}" placeholder="<?php echo esc_html__( 'HH', 'autonami-automations-pro' ); ?>"/>
                :
                <input max="59" min="0" type="number" name="event_meta[minutes]" id="bwfan-minutes" class="bwfan-input-wrapper bwfan-input-inline" value="{{minutes_entered}}" placeholder="<?php echo esc_html__( 'MM', 'autonami-automations-pro' ); ?>"/>
                <# if( _.has(data.eventFieldsOptions, 'last_run') && '' != data.eventFieldsOptions.last_run ) { #>
                <div class="clearfix bwfan_field_desc"><?php echo esc_html__( 'This automation last ran on ', 'autonami-automations-pro' ); ?>{{data.eventFieldsOptions.last_run}}</div>
                <# } #>
            </div>
        </script>
		<?php
	}

	public function run_event( $expire_token ) {
		if ( empty( $expire_token ) ) {
			return;
		}

		$this->process( $expire_token );
	}

	private function process( $expire_token ) {
		global $wpdb;

		$sql        = $wpdb->prepare( "select * from $wpdb->payment_tokenmeta where payment_token_id= %s;", $expire_token['token_id'] );
		$token_data = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $token_data ) ) {
			return;
		}

		$token_meta = [];
		foreach ( $token_data as $token ) {
			$meta_key                = $token['meta_key'];
			$meta_value              = $token['meta_value'];
			$token_meta[ $meta_key ] = $meta_value;
		}

		$data                         = $this->get_default_data();
		$data['expire_token_id']      = $expire_token['token_id'];
		$data['expire_token_user_id'] = $expire_token['user_id'];
		$data['expire_token_meta']    = $token_meta;

		$this->send_async_call( $data );
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
		$data_to_send                                   = [];
		$data_to_send['global']['expire_token_id']      = $this->expire_token_id;
		$data_to_send['global']['expire_token_user_id'] = $this->expire_token_user_id;
		$data_to_send['global']['expire_token_meta']    = $this->expire_token_meta;

		return $data_to_send;
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$meta    = $task_meta['global']['expire_token_meta'];
		$user_id = $task_meta['global']['expire_token_user_id'];
		$user    = get_user_by( 'ID', $user_id );

		if ( false === $user ) {
			return;
		}

		$set_data = array(
			'credit_card_type'         => $meta['card_type'],
			'credit_card_expiry_month' => $meta['expiry_month'],
			'credit_card_expiry_year'  => $meta['expiry_year'],
			'credit_card_last4'        => $meta['last4'],
			'email'                    => $user->user_email,
			'user_id'                  => $user_id,
		);

		BWFAN_Merge_Tag_Loader::set_data( $set_data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->expire_token_id      = BWFAN_Common::$events_async_data['expire_token_id'];
		$this->expire_token_user_id = BWFAN_Common::$events_async_data['expire_token_user_id'];
		$this->expire_token_meta    = BWFAN_Common::$events_async_data['expire_token_meta'];

		return $this->run_automations();
	}

	public function get_email_event() {
		if ( ! empty( absint( $this->expire_token_user_id ) ) ) {
			$user = get_user_by( 'id', absint( $this->expire_token_user_id ) );

			return ( $user instanceof WP_User ) ? $user->user_email : false;
		}

		return false;
	}

	public function get_user_id_event() {
		return ! empty( absint( $this->expire_token_user_id ) ) ? absint( $this->expire_token_user_id ) : false;
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
		global $wpdb;

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

		$days_before = isset( $automation_details['meta']['event_meta']['days_before'] ) ? $automation_details['meta']['event_meta']['days_before'] : 0;
		$date        = new DateTime();
		BWFAN_Common::convert_from_gmt( $date ); // get cards based on the sites timezone
		$date->modify( "+$days_before days" );
		$key = $date->format( 'Y' ) . '-' . $date->format( 'm' );

		if ( isset( $this->data[ $key ] ) ) {
			return;
		}

		$sql = "SELECT token_id,user_id FROM {$wpdb->prefix}woocommerce_payment_tokens as tokens
			LEFT JOIN {$wpdb->payment_tokenmeta} AS m1 ON tokens.token_id = m1.payment_token_id
			LEFT JOIN {$wpdb->payment_tokenmeta} AS m2 ON tokens.token_id = m2.payment_token_id
			WHERE tokens.type = 'CC'
			AND m1.meta_key = 'expiry_year'
			AND m1.meta_value = '{$date->format('Y')}'
			AND m2.meta_key = 'expiry_month'
			AND m2.meta_value = '{$date->format('m')}'
			AND tokens.token !=''
		";

		$this->data[ $key ] = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $this->data[ $key ] ) ) {
			return;
		}
		foreach ( $this->data[ $key ] as $token ) {
			do_action( 'bwfan_card_expiry_reached', $token );
		}
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	return 'BWFAN_WCS_Card_Expiry';
}
