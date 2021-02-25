<?php

final class BWFAN_SL_Message_User extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Sends a message to a user', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action sends a Slack message to the selected user', 'autonami-automations-connectors' );
		$this->action_priority = 5;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'slack_user_options', $data );
		}
	}

	public function get_view_data() {
		$users = WFCO_Common::get_single_connector_data( $this->connector, 'users' );

		return $users;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_slack_user = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'slack_user')) ? data.actionSavedData.data.slack_user : '';
            body = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body')) ? data.actionSavedData.data.body : '';
            #>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select User', 'autonami-automations-connectors' );
				$message = __( 'Select a user and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <select required id="" class="bwfan-single-select bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][slack_user]">
                <option value=""><?php echo esc_html__( 'Choose User', 'autonami-automations-connectors' ); ?></option>
                <#
                if(_.has(data.actionFieldsOptions, 'slack_user_options') && _.isObject(data.actionFieldsOptions.slack_user_options) ) {
                _.each( data.actionFieldsOptions.slack_user_options, function( value, key ){
                selected = (key == selected_slack_user) ? 'selected' : '';
                #>
                <option value="{{key}}" {{selected}}>{{value}}</option>
                <# })
                }
                #>
            </select>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Message', 'autonami-automations-connectors' );
				$message = __( 'Message to be sent to the user selected above.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <div data-element-type="textarea" class="bwfan-<?php echo esc_attr__( $unique_slug ); ?>">
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <textarea required class="bwfan-input-wrapper" rows="4" placeholder="<?php echo esc_attr__( 'Message', 'autonami-automations-connectors' ); ?>" name="bwfan[{{data.action_id}}][data][body]">{{body}}</textarea>
                    <div class="clearfix bwfan_field_desc"><?php esc_html_e( 'HTML markup is not allowed here. If passed will be stripped', 'autonami-automations-connectors' ); ?></div>
                </div>
            </div>
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
		$data_to_set                 = array();
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$data_to_set['user']         = $task_meta['data']['slack_user'];

		$this->add_action();

		$data_to_set['body']  = BWFAN_Common::decode_merge_tags( $task_meta['data']['body'] );
		$data_to_set['email'] = $task_meta['global']['email'];

		$this->remove_action();

		return $data_to_set;
	}

	private function add_action() {
		add_filter( 'bwfan_order_billing_address_separator', [ $this, 'change_br_to_slash_n' ] );
		add_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	private function remove_action() {
		remove_filter( 'bwfan_order_billing_address_params', [ $this, 'change_br_to_slash_n' ] );
		remove_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	public function change_br_to_slash_n( $params ) {
		return "\n";
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_SL_Message_User';
