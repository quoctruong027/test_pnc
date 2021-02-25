<?php

final class BWFAN_AC_Create_Abandoned_Cart extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create Abandoned Cart', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a WooCommerce abandoned cart', 'autonami-automations-connectors' );
		$this->included_events = array( 'ab_cart_abandoned' );
		$this->action_priority = 35;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'connection_options', $data );
		}
	}

	public function get_view_data() {
		$connections = WFCO_Common::get_single_connector_data( $this->connector, 'connections' );

		return $connections;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_connection_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'connection_id')) ? data.actionSavedData.data.connection_id : '';

            if(_.has(data.actionFieldsOptions, 'connection_options') && _.isObject(data.actionFieldsOptions.connection_options) ) {
            connection_options = data.actionFieldsOptions.connection_options;
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Select Connection', 'autonami-automations-connectors' );
					$message = __( 'Select connection which will be used to create abandoned cart on active campaign and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][connection_id]">
                    <option value=""><?php echo esc_html__( 'Choose Connection', 'autonami-automations-connectors' ); ?></option>
                    <#
                    _.each( connection_options, function( value, key ){
                    selected = (key == selected_connection_id) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}} (#{{key}})</option>
                    <# })
                    #>
                </select>
            </div>
            <#
            }
            #>

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
		$data_to_set                 = array();
		$data_to_set['api_key']      = $integration_object->get_settings( 'api_key' );
		$data_to_set['api_url']      = $integration_object->get_settings( 'api_url' );
		$data_to_set['email']        = $task_meta['global']['email'];
		$data_to_set['connectionid'] = $task_meta['data']['connection_id'];
		$abandoned_row_details       = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );

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

		$data_to_set['externalid']                   = $abandoned_row_details['user_id'];
		$data_to_set['order']['externalcheckoutid']  = $abandoned_row_details['ID'];
		$data_to_set['order']['source']              = 1;
		$data_to_set['order']['email']               = $task_meta['global']['email'];
		$data_to_set['order']['orderUrl']            = $cart_url;
		$data_to_set['order']['externalCreatedDate'] = $abandoned_date;
		$data_to_set['order']['abandonedDate']       = $abandoned_date;
		$data_to_set['order']['totalPrice']          = intval( $abandoned_row_details['total'] ) * 100;
		$data_to_set['order']['currency']            = get_woocommerce_currency();
		$data_to_set['order']['connectionid']        = $task_meta['data']['connection_id'];

		foreach ( $cart_items as $item_data ) {
			$product_id    = ( isset( $item_data['product_id'] ) ) ? $item_data['product_id'] : 0;
			$quantity      = ( isset( $item_data['quantity'] ) ) ? $item_data['quantity'] : 0;
			$_pf           = new WC_Product_Factory();
			$product       = $_pf->get_product( $product_id );
			$product_name  = BWFAN_Common::get_name( $product );
			$product_price = $product->get_price();

			$data_to_set['order']['orderProducts'][] = array(
				'externalid' => $product_id,
				'name'       => $product_name,
				'price'      => $product_price,
				'quantity'   => $quantity,
			);
		}

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['ecomOrder'] ) && is_array( $result['body']['ecomOrder'] ) && count( $result['body']['ecomOrder'] ) > 0 ) {

			if ( isset( $result['body']['ecomOrder']['orderNumber'] ) && $result['body']['ecomOrder']['id'] ) {
				update_post_meta( $result['body']['ecomOrder']['orderNumber'], '_bwfan_ac_create_order_id', $result['body']['ecomOrder']['id'] );
			}

			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Abandoned Order successfully created', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$result_message  = ( is_array( $result['body'] ) && isset( $result['body']['result_message'] ) ) ? $result['body']['result_message'] : false;
		$message         = ( is_array( $result['body'] ) && isset( $result['body']['message'] ) ) ? $result['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : ( false !== $message ? $message : $unknown_message ) ) . $response_code,
		);
	}


}

return 'BWFAN_AC_Create_Abandoned_Cart';
