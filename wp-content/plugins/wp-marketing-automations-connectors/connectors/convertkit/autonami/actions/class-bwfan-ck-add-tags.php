<?php

final class BWFAN_CK_Add_Tags extends BWFAN_Action {

	private static $ins = null;

	private function __construct() {
		$this->action_name     = __( 'Add Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action adds the selected tags to the subscriber', 'autonami-automations-connectors' );
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
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			return $this->show_fields_error();
		}

		return $this->add_tag_to_subscriber();
	}

	/**
	 * Add a single tag to the subscriber
	 *
	 * @param $subscriber_email
	 * @param $tag_name (single string)
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public function add_tag_to_subscriber() {
		$params       = array(
			'api_secret' => $this->data['api_secret'],
			'email'      => $this->data['email'],
		);
		$final_result = array();

		foreach ( $this->data['tags'] as $tag_id ) {
			$url                     = $this->get_endpoint() . '/' . $tag_id . '/subscribe';
			$res                     = $this->make_wp_requests( $url, $params, array(), BWF_CO::$POST );
			$final_result[ $tag_id ] = $res;
		}

		return $final_result;
	}

	/**
	 * The endpoint to add tag to subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'tags';
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
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array|void
	 */
	public function make_data( $integration_object, $task_meta ) {
		$this->is_action_tag       = true;
		$final_tags                = [];
		$user_input_tags           = $task_meta['data']['tags'];
		$data_to_set               = array();
		$data_to_set['api_secret'] = $integration_object->get_settings( 'api_secret' );
		$data_to_set['email']      = $task_meta['global']['email'];

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
			$newly_created_tags                    = $this->make_new_tags( $new_tags_to_make, $action_data );
			$tags_to_combine                       = array_keys( $newly_created_tags );
			$action_data['processed_data']['tags'] = array_merge( $action_data['processed_data']['tags'], $tags_to_combine );

			/** Update the DB with new tags start */
			WFCO_Common::get_connectors_data();
			$connectors_saved_data       = WFCO_Common::$connectors_saved_data;
			$current_integration_id      = $connectors_saved_data[ $this->connector ]['id'];
			$current_integration_db_tags = $connectors_saved_data[ $this->connector ]['tags'];

			foreach ( $newly_created_tags as $tag_id => $tag_name ) {
				$current_integration_db_tags[ $tag_id ] = $tag_name;
			}
			$connectors_saved_data[ $this->connector ]['tags'] = $current_integration_db_tags;
			WFCO_Common::update_connector_data( $connectors_saved_data[ $this->connector ], $current_integration_id );
			/** Update the DB with new tags end */
		}

		return parent::execute_action( $action_data );
	}

	public function handle_response( $result, $call_object = null ) {
		if ( is_array( $result ) && count( $result ) > 0 ) {
			$failed_tags = array();
			foreach ( $result as $tag_id => $tag_response ) {
				if ( 200 !== $tag_response['response'] ) {
					$failed_tags[ $tag_id ] = $tag_response;
				}
			}
			if ( 0 === count( $failed_tags ) ) {
				return array(
					'status' => 3,
				);
			}

			return array(
				'status'  => 4,
				'message' => __( 'None or Some tags are failed to apply', 'autonami-automations-connectors' ),
			);
		}

		if ( 502 === absint( $result['response'] ) && is_array( $result['body'] ) ) {
			return array(
				'status'  => 4,
				'message' => isset( $result['body'][0] ) ? $result['body'][0] : __( 'Unknown Autonami Error', 'autonami-automations-connectors' ),
			);
		}

		$response_code   = __( '. Response Code: ', 'autonami-automations-connectors' ) . $result['response'];
		$message         = ( is_array( $result['body'] ) && isset( $result['body']['message'] ) ) ? $result['body']['message'] : false;
		$unknown_message = __( 'Unknown API Exception', 'autonami-automations-connectors' );

		return array(
			'status'  => 4,
			'message' => ( false !== $message ? $message : $unknown_message ) . $response_code,
		);
	}

	public function make_new_tags( $new_tags_to_make, $action_data ) {
		$new_tags          = array();
		$tags_already_made = array();
		$connector         = WFCO_Common::get_call_object( $this->connector, 'wfco_ck_create_tags' );
		$connector->set_data( array(
			'api_secret' => $action_data['processed_data']['api_secret'],
			'tags'       => $new_tags_to_make,
		) );
		$new_tags_response = $connector->process();

		foreach ( $new_tags_response as $key1 => $value1 ) {
			if ( 200 === $value1['response'] ) {
				$new_tags[ $value1['body']['id'] ] = $value1['body']['name'];
			} else {
				$tags_already_made[] = $new_tags_to_make[ $key1 ];
			}
		}

		if ( is_array( $tags_already_made ) && count( $tags_already_made ) > 0 ) {
			$result = $this->get_tags_details( $tags_already_made, $action_data );
			if ( is_array( $result ) && count( $result ) > 0 ) {
				foreach ( $result as $tag_id => $tag_name ) {
					$new_tags[ $tag_id ] = $tag_name;
				}
			}
		}

		return $new_tags;
	}

	public function get_tags_details( $tags_already_made, $action_data ) {
		$tags_data = array();
		$connector = WFCO_Common::get_call_object( $this->connector, 'wfco_ck_fetch_tags' );
		$connector->set_data( array(
			'api_secret' => $action_data['processed_data']['api_secret'],
		) );
		$tags_response = $connector->process();
		if ( isset( $tags_response['response'] ) && 200 === $tags_response['response'] && isset( $tags_response['body']['tags'] ) && is_array( $tags_response['body']['tags'] ) ) {
			$tags = $tags_response['body']['tags'];
			foreach ( $tags as $value1 ) {
				if ( in_array( $value1['name'], $tags_already_made, true ) ) {
					$tags_data[ $value1['id'] ] = $value1['name'];
				}
			}
		}

		return $tags_data;
	}

	public function before_executing_task() {
		add_filter( 'bwfan_current_integration_action', [ $this, 'return_confirmation' ], 10, 1 );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_current_integration_action', [ $this, 'return_confirmation' ], 10, 1 );
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
return 'BWFAN_CK_Add_Tags';
