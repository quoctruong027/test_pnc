<?php

final class BWFAN_Ontraport_Add_Tags extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds the selected tags to the contact', 'autonami-automations-connectors' );
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
				echo esc_html__( 'Add Tags', 'autonami-automations-connectors' );
				$message = __( 'Add available tags and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
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
		$this->is_action_tag         = true;
		$final_tags                  = [];
		$user_input_tags             = $task_meta['data']['tags'];
		$data_to_set                 = array();
		$data_to_set['app_id'] = $integration_object->get_settings( 'app_id' );
		$data_to_set['api_key']     = $integration_object->get_settings( 'api_key' );

		/** Set Email if global email is empty */
		$data_to_set['email'] = $task_meta['global']['email'];
		if ( empty( $data_to_set['email'] ) ) {
			$user = ! empty( $task_meta['global']['user_id'] ) ? get_user_by( 'ID', $task_meta['global']['user_id'] ) : false;

			$data_to_set['email'] = $user instanceof WP_User ? $user->user_email : '';
		}

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
		$data_to_set['tags'] = $final_tags;
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
		$integration          = BWFAN_Core()->integration->get_integration( $action_data['integration_slug'] );
		$integration_settings = $integration->get_settings();
		$tags                 = $action_data['processed_data']['tags'];
		$new_tags_to_make     = array();

		foreach ( $tags as $tag_key => $tag_value ) {
			/** If nothing in DB */
			if ( ! isset( $integration_settings['tags'] ) ) {
				unset( $action_data['processed_data']['tags'][ $tag_key ] );
				$new_tags_to_make[] = $tag_value;
				continue;
			}

			/** tag value is tag id and is available */
			if ( isset( $integration_settings['tags'][ $tag_value ] ) ) {
				continue;
			}

			/** If tag value found in saved tags, append proper tag id */
			$saved_tag_id = array_search( $tag_value, $integration_settings['tags'] );
			if ( false !== $saved_tag_id ) {
				unset( $action_data['processed_data']['tags'][ $tag_key ] );
				$action_data['processed_data']['tags'][] = $saved_tag_id;
				continue;
			}

			/** Else new tag */
			unset( $action_data['processed_data']['tags'][ $tag_key ] );
			$new_tags_to_make[] = $tag_value;
		}

		if ( is_array( $new_tags_to_make ) && count( $new_tags_to_make ) > 0 ) {
			$newly_created_tags = $this->make_new_tags( $new_tags_to_make, $action_data );
			if ( is_array( $newly_created_tags ) && count( $newly_created_tags ) > 0 ) {
				$tags_to_combine                       = array_flip( $newly_created_tags );
				$action_data['processed_data']['tags'] = array_merge( $action_data['processed_data']['tags'], $tags_to_combine );
			}
		}
		return parent::execute_action( $action_data );
	}

	public function make_new_tags( $new_tags_to_make, $action_data ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_ontraport_create_tag' );
		if ( is_null( $call_class ) ) {
			return false;
		}
		$action_data['processed_data']['new_tags'] = $new_tags_to_make;
		$call_class->set_data( $action_data['processed_data'] );
		$response = $call_class->process();
		if ( false === $response ) {
			return false;
		}

		return $response;
	}

	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
		}

		if ( isset( $result['body']['data'] ) && isset( $result['body']['data'] ) && ! empty( $result['body']['data'] ) ) {
			return array(
				'status'  => 3,
				'message' => __( 'Tags added to contact successfully!', 'autonami-automations-connectors' ),
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
return 'BWFAN_Ontraport_Add_Tags';
