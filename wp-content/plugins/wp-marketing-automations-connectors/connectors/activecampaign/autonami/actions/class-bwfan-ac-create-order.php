<?php

final class BWFAN_AC_Create_Order extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create Order', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a new WooCommerce order', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_product_purchased',
		);
		$this->action_priority = 40;
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
					$message = __( 'Select connection which will be used to create order on active campaign and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
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

		/**
		 * @var $order WC_Order;
		 */
		$order                                  = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		$data_to_set['externalid']              = $order->get_user_id();
		$data_to_set['order']['externalid']     = $order->get_ID();
		$data_to_set['order']['source']         = 1;
		$data_to_set['order']['email']          = $task_meta['global']['email'];
		$data_to_set['order']['orderNumber']    = $order->get_order_number();
		$data_to_set['order']['orderUrl']       = $order->get_checkout_order_received_url();
		$data_to_set['order']['orderDate']      = $order->get_date_created()->format( 'c' );
		$data_to_set['order']['shippingMethod'] = $order->get_shipping_method();
		$data_to_set['order']['totalPrice']     = intval( $order->get_total() ) * 100;
		$data_to_set['order']['currency']       = get_woocommerce_currency();
		$data_to_set['order']['connectionid']   = $task_meta['data']['connection_id'];

		/**
		 * @var $item_data WC_Order_Item_Product
		 */
		foreach ( $order->get_items() as $item_id => $item_data ) {
			$item                 = new WC_Order_Item_Product( $item_id );
			$product_name         = $item->get_name();
			$product_id           = $item_data->get_product_id();
			$product_variation_id = $item_data->get_variation_id();
			if ( 0 !== $product_variation_id ) {
				$product_id = $product_variation_id;
			}

			$item_quantity = $item_data->get_quantity(); // Get the item quantity
			$item_total    = $item_data->get_total(); // Get the item line total

			$data_to_set['order']['orderProducts'][] = array(
				'externalid' => $product_id,
				'name'       => $product_name,
				'price'      => $item_total,
				'quantity'   => $item_quantity,
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
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Order successfully created', 'autonami-automations-connectors' ),
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

return 'BWFAN_AC_Create_Order';
