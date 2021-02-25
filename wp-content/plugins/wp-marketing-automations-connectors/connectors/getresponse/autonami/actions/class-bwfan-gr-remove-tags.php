<?php

final class BWFAN_GR_Remove_Tags extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the selected tags to the contact', 'autonami-automations-connectors' );
		$this->action_priority = 40;
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
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'tags_options', $data['tags'] );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'list_options', $data['lists'] );
		}
	}

	public function get_view_data() {
		$lists = WFCO_Common::get_single_connector_data( $this->connector, 'lists' );
		$tags  = WFCO_Common::get_single_connector_data( $this->connector, 'tags' );

		return [ 'tags' => $tags, 'lists' => $lists ];
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-action-<?php echo esc_attr__( $unique_slug ); ?>">
            <#
            selected_list = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'list_id')) ? data.actionSavedData.data.list_id : '';
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
                    <input type="button" class="button bwfan-tag-add" value="Select">
                </div>
                <ul class="tagchecklist" role="list"></ul>
                <select style="display: none" name="bwfan[{{data.action_id}}][data][tags][]" multiple class="bwfan_add_tags_final_value" data-name="tags" data-action="<?php echo $unique_slug ?>">
                </select>
            </div>
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mb-15">
                <label class="bwfan-label-title">Select List</label>
                <div>
                    <select required id="" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][list_id]">
                        <option value=""><?php echo esc_html__( 'Default List', 'autonami-automations-connectors' ); ?></option>
                        <#
                        if(_.has(data.actionFieldsOptions, 'list_options') && _.isObject(data.actionFieldsOptions.list_options) ) {
                        _.each( data.actionFieldsOptions.list_options, function( value, key ){
                        selected = (key == selected_list) ? 'selected' : '';
                        #>
                        <option value="{{key}}" {{selected}}>{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
                <div class="bwfan_field_desc bwfan-mb10">Select the list to remove tags from.</div>
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
		$this->is_action_tag    = true;
		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['list_id'] = empty( $task_meta['data']['list_id'] ) ? $integration_object->get_settings( 'default_list' ) : $task_meta['data']['list_id'];

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

		/** Decode Merge Tags in GetResponse Tags */
		$final_tags      = [];
		$user_input_tags = $task_meta['data']['tags'];
		foreach ( $user_input_tags as $tag_value ) {
			$tags_response = BWFAN_Common::decode_merge_tags( $tag_value );
			$tags          = json_decode( $tags_response );
			if ( is_array( $tags ) && count( $tags ) > 0 ) {
				foreach ( $tags as $single_tag ) {
					$final_tags[] = $single_tag;
				}
				continue;
			}
			$final_tags[] = $tags_response;
		}

		/** Separate already created tags from new tags to make */
		$tags_to_remove   = [];
		$tags_in_settings = $integration_object->get_settings( 'tags' );
		foreach ( $final_tags as $tag_key => $tag_value ) {
			/** If nothing in DB */
			if ( empty( $tags_in_settings ) ) {
				$tags_to_remove = $final_tags;
				break;
			}

			/** tag value is tag id and is available */
			if ( isset( $tags_in_settings[ $tag_value ] ) ) {
				$tags_to_remove[] = $tags_in_settings[ $tag_value ];
				continue;
			}

			$tags_to_remove[] = $tag_value;
		}

		$data_to_set['tags'] = $tags_to_remove;

		return $data_to_set;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['code'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Error: ' . $result['body']['message'], 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && ( 200 !== absint( $result['response'] ) ) ) {
			$message = ( 502 === absint( $result['response'] ) ) ? $result['body'][0] : __( 'Unknown Error Occurred', 'autonami-automations-connectors' );

			return array(
				'status'  => 4,
				'message' => __( $message, 'autonami-automations-connectors' ),
			);
		}

		if ( isset( $result['response'] ) && 200 === absint( $result['response'] ) ) {
			return array(
				'status'  => 3,
				'message' => isset( $result['body']['result_message'] ) ? $result['body']['result_message'] : __( 'Tags removed successfully!', 'autonami-automations-connectors' ),
			);
		}

		BWFAN_Core()->logger->log( $result, 'failed-' . $this->get_slug() . '-action' );

		return array(
			'status'  => 4,
			'message' => __( 'Unknown Error: Check log failed-' . $this->get_slug() . '-action', 'autonami-automations-connectors' ),
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
return 'BWFAN_GR_Remove_Tags';
