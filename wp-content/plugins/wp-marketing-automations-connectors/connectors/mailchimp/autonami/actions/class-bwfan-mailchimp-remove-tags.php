<?php

final class BWFAN_Mailchimp_Remove_Tags extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the selected tags to the contact', 'autonami-automations-connectors' );
		$this->action_priority = 20;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'tags_options', $data );
		}
	}

	public function get_view_data() {
		$tags = WFCO_Common::get_single_connector_data( $this->connector, 'tags' );

		return $tags;
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_tags = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'tags')) ? data.actionSavedData.data.tags : {};

            if(_.has(data.actionFieldsOptions, 'tags_options') && _.isObject(data.actionFieldsOptions.tags_options) ) {
            tags_options_clone = data.actionFieldsOptions.tags_options;

            if( _.size(selected_tags) > 0 ) {
            diffTags = _.difference(selected_tags,_.keys(tags_options_clone));

            if(_.size(diffTags) > 0) {
            _.each( diffTags, function( value, key ){
            tags_options_clone[value] = value;
            });

            }
            }
            }
            #>
            <label for="" class="bwfan-label-title">
				<?php
				echo esc_html__( 'Select Tags', 'autonami-automations-connectors' );
				$message = __( 'Remove available tags and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <div class="bwfan_add_tags">
                <div class="bwfan_tags_wrap">
                    <input list="tags" type="text" id="new-tag-bwfan_tag" class="bwfan-input-wrapper" autocomplete="on">
                    <input type="button" class="button bwfan-tag-add" value="Add">
                </div>
                <ul class="tagchecklist" role="list"></ul>
                <select style="display: none" name="bwfan[{{data.action_id}}][data][tags][]" multiple class="bwfan_add_tags_final_value" data-name="tags" data-action="<?php echo $unique_slug ?>">
                </select>
            </div>
        </script>
		<?php
	}

	/**
	 * Overrides the parent class method to return new array type values
	 *
	 * @param $dynamic_array
	 * @param $integration_data
	 *
	 * @return array
	 */
	public function parse_merge_tags( $dynamic_array, $integration_data ) {
		return $this->parse_tags_fields( $dynamic_array, $integration_data );
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
		$this->is_action_tag = true;

		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['list_id'] = empty( $task_meta['data']['list_id'] ) ? $integration_object->get_settings( 'default_list' ) : $task_meta['data']['list_id'];
		$data_to_set['email']   = $this->maybe_get_email( $task_meta );

		$final_tags      = [];
		$user_input_tags = $task_meta['data']['tags'];
		$available_tags  = $integration_object->get_settings( 'tags' );

		foreach ( $user_input_tags as $tag_value ) {
			$tags_response = BWFAN_Common::decode_merge_tags( $tag_value );
			$tags          = json_decode( $tags_response );
			if ( is_array( $tags ) && count( $tags ) > 0 ) {
				foreach ( $tags as $single_tag ) {
					$final_tags[] = isset( $available_tags[ $single_tag ] ) ? $available_tags[ $single_tag ] : $single_tag;
				}
				continue;
			}
			$final_tags[] = isset( $available_tags[ $tags_response ] ) ? $available_tags[ $tags_response ] : $tags_response;
		}
		$data_to_set['tags'] = $final_tags;

		return $data_to_set;
	}

	protected function maybe_get_email( $task_meta ) {
		if ( is_email( $task_meta['global']['email'] ) ) {
			return $task_meta['global']['email'];
		}

		$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

		return $user instanceof WP_User ? $user->user_email : '';
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['response'] ) && 204 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Tags removed successfully!', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$error_title     = ( is_array( $result['body'] ) && isset( $result['body']['title'] ) ) ? $result['body']['title'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $error_title ? $error_title : $unknown_message ) . $response_code,
		);
	}

	public function before_executing_task() {
		add_filter( 'bwfan_current_integration_action', [ $this, 'return_confirmation' ], 10, 1 );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_current_integration_action', [ $this, 'return_confirmation' ], 10 );
	}

	public function return_confirmation( $bool ) {
		if ( $this->is_action_tag ) {
			$bool = true;
		}

		return $bool;
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_Mailchimp_Remove_Tags';
