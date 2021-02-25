<?php

final class BWFAN_AC_Create_Deal_Note extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Create Deal Note', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action creates a note against a deal. Note: Deal should be created first', 'autonami-automations-connectors' );
		$this->included_events = array(
			'wc_new_order',
			'wc_order_note_added',
			'wc_order_status_change',
			'wc_product_purchased',
			'wc_product_refunded',
			'wc_product_stock_reduced',
		);
		$this->action_priority = 55;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            entered_deal_note = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'deal_note')) ? data.actionSavedData.data.deal_note : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo esc_html__( 'Deal Description', 'autonami-automations-connectors' );
					$message = __( 'Add description of the note to be created.', 'autonami-automations-connectors' );
					echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <textarea required class="bwfan-input-wrapper" rows="4" placeholder="<?php echo esc_attr__( 'Message', 'autonami-automations-connectors' ); ?>" name="bwfan[{{data.action_id}}][data][deal_note]">{{entered_deal_note}}</textarea>
            </div>
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
		$data_to_set              = array();
		$data_to_set['api_key']   = $integration_object->get_settings( 'api_key' );
		$data_to_set['api_url']   = $integration_object->get_settings( 'api_url' );
		$data_to_set['email']     = $task_meta['global']['email'];
		$data_to_set['deal_note'] = ( ! empty( $task_meta['data']['deal_note'] ) ) ? BWFAN_Common::decode_merge_tags( $task_meta['data']['deal_note'] ) : '';

		return $data_to_set;
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
		$deal_id = 0;
		if ( isset( $action_data['processed_data']['order_id'] ) ) {
			$order_id = $action_data['processed_data']['order_id'];
			$deal_id  = get_post_meta( $order_id, '_bwfan_ac_deal_id', true );
			$deal_id  = absint( $deal_id );
		}
		// dynamic filter, it will be like 'bwfan_get_deal_id_wc' or 'bwfan_get_deal_id_edd'. Created to get deal id from order meta.
		$deal_id = apply_filters( 'bwfan_get_deal_id_' . $action_data['event_data']['event_source'], $deal_id, $action_data );
		if ( false === $deal_id ) {
			return [
				'bwfan_custom_message' => __( 'Deal Not Created', 'autonami-automations-connectors' ),
			];
		}

		$action_data['processed_data']['deal_id'] = $deal_id;

		return parent::execute_action( $action_data );
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['note'] ) && is_array( $result['body']['note'] ) && count( $result['body']['note'] ) > 0 ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Deal note successfully created', 'autonami-automations-connectors' ),
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

return 'BWFAN_AC_Create_Deal_Note';
