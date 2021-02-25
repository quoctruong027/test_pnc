<?php

final class BWFAN_Mautic_Add_Note extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Add Note to Contact', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds a note to contact', 'autonami-automations-connectors' );
		$this->action_priority = 60;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'note_type_options', $data );
		}
	}

	public function get_view_data() {
		$note_types = array(
			'general' => 'General',
			'email'   => 'Email',
			'call'    => 'Call',
			'meeting' => 'Meeting'
		);

		return $note_types;
	}

	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            note = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'note')) ? data.actionSavedData.data.note : '';
            selected_type = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'note_type')) ? data.actionSavedData.data.note_type : 'general';
            #>

            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Note', 'wp-marketing-automations' ); ?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <textarea class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" id="bwfan-editor" rows="6" placeholder="<?php esc_html_e( 'Note', 'wp-marketing-automations' ); ?>" name="bwfan[{{data.action_id}}][data][note]">{{note}}</textarea>
                <div class="clearfix bwfan_field_desc bwfan-pt-5">
                    Enter note to add to contact.
                </div>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Note Type', 'wp-marketing-automations' ); ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <select required id="" class="bwfan-input-wrapper bwfan-single-select" name="bwfan[{{data.action_id}}][data][note_type]">
                    <option value=""><?php echo esc_html__( 'Choose a Note Type', 'autonami-automations-connectors' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'note_type_options') && _.isObject(data.actionFieldsOptions.note_type_options) ) {
                    _.each( data.actionFieldsOptions.note_type_options, function( value, key ){
                    selected = (key == selected_type) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
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
		$data_to_set                 = array();
		$data_to_set['site_url']     = $integration_object->get_settings( 'site_url' );
		$data_to_set['access_token'] = $integration_object->get_settings( 'access_token' );
		$data_to_set['note']         = BWFAN_Common::decode_merge_tags( $task_meta['data']['note'] );
		$data_to_set['type']         = $task_meta['data']['note_type'];

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['note'] ) && isset( $result['body']['note']['id'] ) && ! empty( $result['body']['note']['id'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Note created & added to Contact successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$result_message  = ( is_array( $result['body'] ) && isset( $result['body']['errors'] ) ) ? $result['body']['errors'][0]['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $result_message ? $result_message : $unknown_message ) . $response_code,
		);
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Mautic_Add_Note';
