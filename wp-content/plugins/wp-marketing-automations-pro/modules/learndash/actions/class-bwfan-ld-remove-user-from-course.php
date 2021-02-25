<?php

final class BWFAN_LD_Remove_User_From_Course extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Remove User from Course(s)', 'autonami-automations-pro' );
		$this->action_desc     = __( 'This action removes a user from a selected courses', 'wp-marketing-automations' );
		$this->required_fields = array( 'user_id', 'courses' );
		$this->action_priority = 10;
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>
        <script type="text/html" id="tmpl-action-<?php esc_attr_e( $this->get_slug() ); ?>">
            <#
            searched_courses = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'searched_courses')) ? data.actionSavedData.data.searched_courses : '';
            selected_courses = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'courses')) ? data.actionSavedData.data.courses : {};
            #>

            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Courses', 'wp-marketing-automations' ); ?></label>
                <select required id="" data-search="sfwd-courses" data-search-text="<?php esc_attr_e( 'Select Course', 'wp-marketing-automations' ); ?>" class="bwfan-select2ajax-single bwfan-course-search bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][courses][]" multiple>
                    <#
                    if(_.size(searched_courses) >0) {
                    temp_selected_course = JSON.parse(searched_courses);
                    _.each( selected_courses, function( v ){
                    #>
                    <option value="{{v}}" selected>{{temp_selected_course[v]}}</option>
                    <#
                    })
                    }
                    #>
                </select>
                <input type="hidden" class="bwfan_searched_course_name" name="bwfan[{{data.action_id}}][data][searched_courses]" value="{{searched_courses}}"/>
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
		$data_to_set = array(
			'courses' => $task_meta['data']['courses'],
			'user_id' => $task_meta['global']['user_id'],
		);

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
				'status' => 3,
			);
		}

		return array(
			'status'  => 4,
			'message' => ( is_array( $result ) && isset( $result['bwfan_response'] ) ) ? $result['bwfan_response'] : __( 'User couldn\'t be removed from the course.', 'autonami-automations-pro' ),
		);
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

		foreach ( $this->data['courses'] as $course_id ) {
			ld_update_course_access( $this->data['user_id'], $course_id, true );
		}

		return true;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_LD_Remove_User_From_Course';
