<?php

/**
 * Class BWFAN_Mautic_Webhook_Received
 */
final class BWFAN_Mautic_Webhook_Received extends BWFAN_Event {
	private static $instance = null;
	private $automation_id = null;
	private $first_name = '';
	private $last_name = '';
	private $phone = '';
	private $email = '';
	private $automation_key = '';
	private $localized_automation_key = '';
	private $contact_id = '';

	private function __construct() {
		$this->optgroup_label         = __( 'Webhook', 'autonami-automations' );
		$this->event_name             = __( 'Webhook Received', 'autonami-automations' );
		$this->event_desc             = __( 'This automation would trigger webhook.', 'autonami-automations' );
		$this->event_merge_tag_groups = array( 'mautic_contact' );
		$this->event_rule_groups      = array( 'bwf_contact' );
		$this->customer_email_tag     = '{{mautic_contact_email}}';
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'bwfan_webhook_admin_enqueue_assets' ), 98 );
		add_action( "bwfan_mautic_connector_sync_call", array( $this, 'before_process_webhook_contact' ), 10 );

		add_action( 'bwfan_webhook_mautic_contact', array( $this, 'process' ), 10, 7 );
	}

	public function before_process_webhook_contact( $args ) {
		$hook  = 'bwfan_webhook_mautic_contact';
		$group = 'mautic';

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
		if ( is_email( $this->email ) ) {
			$user = get_user_by( 'email', $this->email );

			return ( $user instanceof WP_User ) ? $user->ID : false;
		}

		return false;
	}

	public function bwfan_webhook_admin_enqueue_assets() {

		$this->automation_id = isset( $_GET['edit'] ) ? sanitize_text_field( $_GET['edit'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $this->automation_id ) ) {
			$meta = BWFAN_Model_Automationmeta::get_meta( $this->automation_id, 'event_meta' );
			if ( isset( $meta['bwfan_unique_key'] ) && ! empty( $meta['bwfan_unique_key'] ) ) {
				$this->localized_automation_key = $meta['bwfan_unique_key'];
			}
		}

		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'automation_id', $this->automation_id );
		BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'saved_localized_automation_key', $this->localized_automation_key );
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
		if ( isset( $global_data['contact_id'] ) && ! empty( $global_data['contact_id'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'Mautic Contact ID:', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['contact_id'] ); ?>
            </li>
			<?php
		}
		?>
		<?php
		if ( isset( $global_data['first_name'] ) && ! empty( $global_data['first_name'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'First Name:', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['first_name'] ); ?>
            </li>
			<?php
		}
		?>
		<?php
		if ( isset( $global_data['last_name'] ) && ! empty( $global_data['last_name'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'Last Name:', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['last_name'] ); ?>
            </li>
			<?php
		}
		?>
		<?php
		if ( isset( $global_data['email'] ) && ! empty( $global_data['email'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'Email:', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['email'] ); ?>
            </li>
			<?php
		}
		?>
		<?php
		if ( isset( $global_data['phone'] ) && ! empty( $global_data['phone'] ) ) { ?>
            <li>
                <strong><?php esc_html_e( 'Phone:', 'autonami-automations' ); ?> </strong>
				<?php esc_html_e( $global_data['phone'] ); ?>
            </li>
			<?php
		}
		?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php esc_attr_e( $this->get_slug() ); ?>">
            <#
            var eventslug = '<?php esc_html_e( $this->get_slug() ); ?>';
            var eventData = bwfan_events_js_data[eventslug];
            var event_save_unique_key =eventData.saved_localized_automation_key;
            if(event_save_unique_key.length>0){
            eventData.localized_automation_key = event_save_unique_key
            }
            var webhook_url = '<?php esc_attr_e( home_url( '/' ) ); ?>wp-json/autonami/v1/mautic/webhook?bwfan_mautic_id='+eventData.automation_id+'&bwfan_mautic_key='+eventData.localized_automation_key;
            #>
            <div class="bwfan_mt15"></div>
            <label for="bwfan-webhook-url" class="bwfan-label-title"><?php esc_html_e( 'Url', 'autonami-automations-connectors' ); ?></label>
            <div class="bwfan-textarea-box">
                <textarea name="event_meta[bwfan_webhook_url]" class="bwfan-input-wrapper bwfan-webhook-url" id="bwfan-webhook-url" cols="45" rows="2" onclick="select();" readonly>{{webhook_url}}</textarea>
                <input type="hidden" name="event_meta[bwfan_unique_key]" id="bwfan-unique-key" value={{eventData.localized_automation_key}}>
                <div class="clearfix bwfan_field_desc bwfan-pt-5">
                    In case of triggering webhooks from campaigns, this event works differently. Check docs for additional info on this.
                </div>
            </div>
        </script>
		<?php
	}


	/**
	 * Make the required data for the current event and send it asynchronously.
	 *
	 * @param $order_id
	 */
	public function process( $first_name, $last_name, $phone, $email, $contact_id, $automation_key, $automation_id ) {
		$data                   = $this->get_default_data();
		$data['mautic_a_id']    = $automation_id;
		$data['automation_key'] = $automation_key;
		$data['contact_id']     = $contact_id;
		$data['first_name']     = $first_name;
		$data['last_name']      = $last_name;
		$data['phone']          = $phone;
		$data['email']          = $email;

		$this->send_async_call( $data );
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->automation_id  = BWFAN_Common::$events_async_data['mautic_a_id'];
		$this->automation_key = BWFAN_Common::$events_async_data['automation_key'];
		$this->contact_id     = BWFAN_Common::$events_async_data['contact_id'];
		$this->first_name     = BWFAN_Common::$events_async_data['first_name'];
		$this->last_name      = BWFAN_Common::$events_async_data['last_name'];
		$this->phone          = BWFAN_Common::$events_async_data['phone'];
		$this->email          = BWFAN_Common::$events_async_data['email'];

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
			$unique_key_matched    = ( isset( $automation_data['event_meta']['bwfan_unique_key'] ) && $this->automation_key === $automation_data['event_meta']['bwfan_unique_key'] );
			$automation_id_matched = ( absint( $automation_id ) === absint( $this->automation_id ) );
			if ( $unique_key_matched && $automation_id_matched ) {
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
		$data_to_send['global']['mautic_a_id']    = $this->automation_id;
		$data_to_send['global']['automation_key'] = $this->automation_key;
		$data_to_send['global']['contact_id']     = $this->contact_id;
		$data_to_send['global']['first_name']     = $this->first_name;
		$data_to_send['global']['last_name']      = $this->last_name;
		$data_to_send['global']['phone']          = $this->phone;
		$data_to_send['global']['email']          = $this->email;

		return $data_to_send;
	}

	public function set_merge_tags_data( $task_meta ) {
		$merge_data               = [];
		$merge_data['contact_id'] = $task_meta['global']['contact_id'];
		$merge_data['first_name'] = $task_meta['global']['first_name'];
		$merge_data['last_name']  = $task_meta['global']['last_name'];
		$merge_data['phone']      = $task_meta['global']['phone'];
		$merge_data['email']      = $task_meta['global']['email'];
		BWFAN_Merge_Tag_Loader::set_data( $merge_data );
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
	}

}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
$saved_connectors = WFCO_Common::$connectors_saved_data;

if ( empty( $saved_connectors ) ) {
	WFCO_Common::get_connectors_data();
	$saved_connectors = WFCO_Common::$connectors_saved_data;
}

if ( array_key_exists( 'bwfco_mautic', $saved_connectors ) ) {
	return 'BWFAN_Mautic_Webhook_Received';
}