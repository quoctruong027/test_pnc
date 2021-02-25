<?php

final class BWFAN_Mailchimp_Add_Cart extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Abandoned Cart', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds the cart in Mailchimp', 'autonami-automations-connectors' );
		$this->action_priority = 70;
		$this->included_events = array( 'ab_cart_abandoned' );
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'store_options', $data );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'default_store', $this->get_default_store() );
		}
	}

	public function get_default_store() {
		return WFCO_Common::get_single_connector_data( $this->connector, 'default_store' );
	}

	public function get_view_data() {
		return WFCO_Common::get_single_connector_data( $this->connector, 'stores' );
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
		<script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
			<#
			selected_store_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'store_id')) ? data.actionSavedData.data.store_id : '';
			default_store = _.has(data.actionFieldsOptions, 'default_store') ? data.actionFieldsOptions.default_store : '';
			#>
			<label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select Store', 'autonami-automations-connectors' );
				$message = __( 'Select store to add order', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</label>
			<select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][store_id]">
				<# selected = (default_store == selected_store_id) ? 'selected' : ''; #>
				<option value="{{default_store}}" {{selected}}><?php echo esc_html__( 'Default Store', 'autonami-automations-connectors' ); ?></option>
				<#
				if(_.has(data.actionFieldsOptions, 'store_options') && _.isObject(data.actionFieldsOptions.store_options) ) {
				_.each( data.actionFieldsOptions.store_options, function( value, key ){
				selected = (key == selected_store_id) ? 'selected' : '';
				#>
				<option value="{{key}}" {{selected}}>{{value}}</option>
				<# })
				}
				#>
			</select>
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
		$data_to_set             = array();
		$data_to_set['api_key']  = $integration_object->get_settings( 'api_key' );
		$data_to_set['store_id'] = $task_meta['data']['store_id'];

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		$data_to_set = $this->fill_abandoned_data( $data_to_set, $task_meta );

		return $data_to_set;
	}

	public function fill_abandoned_data( $data_to_set, $task_meta ) {
		$abandoned_row_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );

		// get automation data and set track id
		$automation_id   = $task_meta['automation_id'];
		$automation_meta = BWFAN_Core()->automations->get_automation_data_meta( $automation_id );
		$a_track_id      = isset( $automation_meta['a_track_id'] ) ? $automation_meta['a_track_id'] : 0;
		$t_track_id      = $a_track_id . '_' . $task_meta['group_id'] . '_' . $task_meta['action_id'];

		// WooCommerce Cart object
		$cart_items       = maybe_unserialize( $abandoned_row_details['items'] );
		$abandoned_date   = get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $abandoned_row_details['created_time'] ) ) );
		$checkout_details = json_decode( $abandoned_row_details['checkout_data'], true );

		$checkout_lang = isset( $checkout_details['lang'] ) ? $checkout_details['lang'] : '';

		$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );

		if ( method_exists( 'BWFAN_Common', 'get_permalink_by_language' ) ) {
			$url = BWFAN_Common::get_permalink_by_language( $checkout_page_id, $checkout_lang );
		} else {
			$url = get_permalink( $checkout_page_id );
		}

		$cart_url = add_query_arg( array(
			'bwfan-ab-id'   => $abandoned_row_details['token'],
			'track-id'      => $t_track_id,
			'automation-id' => $automation_id,
		), $url );

		$data_to_set['bwfan_ab_id']    = $abandoned_row_details['token'];
		$data_to_set['abandoned_date'] = $abandoned_date;
		$data_to_set['cart_url']       = $cart_url;
		$data_to_set['cart_items']     = $cart_items;
		$data_to_set['checkout_data']  = json_decode( $abandoned_row_details['checkout_data'], true );
		$data_to_set['cart_total']     = $abandoned_row_details['total'];

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Cart Added Successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$error_title     = ( is_array( $result['body'] ) && isset( $result['body']['detail'] ) ) ? $result['body']['detail'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $error_title ? $error_title : $unknown_message ) . $response_code,
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Mailchimp_Add_Cart';
