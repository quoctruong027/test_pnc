<?php

final class BWFAN_LD_Add_User_From_Group extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Remove User from Group', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action removed a user from a selected group', 'wp-marketing-automations' );
		$this->required_fields = array( 'user_id', 'group_id' );
		$this->action_priority = 20;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'groups', $data );
		}
	}

	public function get_view_data() {
		$groups = get_posts( array(
			'post_type'        => 'groups',
			'posts_per_page'   => - 1,
			'status'           => 'publish',
			'suppress_filters' => false
		) );

		$group_array = array();
		foreach ( $groups as $group ) {
			$group_array[ $group->ID ] = $group->post_title;
		}

		return $group_array;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_group = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'group')) ? data.actionSavedData.data.group : '';
            #>
            <div class="bwfan-<?php echo esc_html__( $this->get_slug() ); ?>">
                <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Group', 'autonami-automations-pro' ); ?></label>
                <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][group]">
                    <option value=""><?php echo esc_html__( 'Choose any Group', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.actionFieldsOptions, 'groups') && _.isObject(data.actionFieldsOptions.groups) ) {
                    _.each( data.actionFieldsOptions.groups, function( value, key ){
                    selected = (key == selected_group) ? 'selected' : '';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    }
                    #>
                </select>
                <div class="clearfix bwfan_field_desc bwfan-mb20">Select the group from which you want to remove user</div>
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
		$data_to_set             = array();
		$data_to_set['user_id']  = $task_meta['global']['user_id'];
		$data_to_set['group_id'] = $task_meta['data']['group'];

		if ( empty( $data_to_set['user_id'] ) ) {
			$email                  = ( isset( $task_meta['global']['email'] ) && is_email( $task_meta['global']['email'] ) ) ? $task_meta['global']['email'] : '';
			$user                   = is_email( $email ) ? get_user_by( 'email', $email ) : '';
			$data_to_set['user_id'] = $user instanceof WP_User ? $user->ID : 0;
		}

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
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();

		if ( true === $result ) {
			return array(
				'status'  => 3,
				'message' => 'User removed from group successfully!'
			);
		}

		return $result;
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		$group_id = absint( $this->data['group_id'] );
		if ( empty( $group_id ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Group wasn\'t selected', 'autonami-automations-pro' ),
			);
		}

		$user_id = $this->data['user_id'];

		$user = get_userdata( $user_id );
		if ( false === $user ) {
			return array(
				'status'  => 4,
				'message' => __( 'User doesn\'t exists', 'autonami-automations-pro' ),
			);
		}

		ld_update_group_access( $user_id, $group_id, true );

		return true;
	}
}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_LD_Add_User_From_Group';