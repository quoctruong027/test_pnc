<?php

final class BWFAN_CK_Rmv_Tags extends BWFAN_Action {
	private static $ins = null;
	private $tags_count = 0;

	private function __construct() {
		$this->action_name     = __( 'Remove Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the selected tags from the subscriber', 'autonami-automations-connectors' );
		$this->action_priority = 10;
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
				$message = __( 'Select available tags and if unable to locate then sync the connector.', 'autonami-automations-connectors' );
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
	 * The endpoint to remove tag from subscriber.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return BWFCO_ConvertKit::get_endpoint() . 'subscribers';
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

		foreach ( $tags as $tag_key => $tag_value ) {
			/** If tag value found in saved tags and is available */
			if ( is_array( $integration_settings['tags'] ) && in_array( $tag_value, $integration_settings['tags'] ) ) {
				continue;
			}

			/** tag value is tag id, append proper tag value */
			if ( is_array( $integration_settings['tags'] ) && isset( $integration_settings['tags'][ $tag_value ] ) ) {
				unset( $action_data['processed_data']['tags'][ $tag_value ] );
				$action_data['processed_data']['tags'][] = $integration_settings['tags'][ $tag_value ];
				continue;
			}

			/** Sync the tags to DB */
			$fetched_tags = $this->sync_tags( $integration_settings['api_secret'] );

			if ( isset( $fetched_tags['status'] ) ) {
				return $fetched_tags;
			}
			/** Again, try to get the tag IDs */
			foreach ( $tags as $key => $value ) {
				/** tag value is tag id and is available */
				if ( in_array( $value, $fetched_tags ) ) {
					continue;
				}

				/** tag value is tag id, append proper tag value */
				if ( isset( $fetched_tags[ $value ] ) ) {
					unset( $action_data['processed_data']['tags'][ $key ] );
					$action_data['processed_data']['tags'][] = $integration_settings['tags'][ $value ];
					continue;
				}

				/** Unable to get the tag, even in fetched tags */
				return array(
					'status'  => 4,
					'message' => __( 'Tag to remove is not available in ConvertKit. Tag: ' . $tag_value, 'autonami-automations-connectors' )
				);
			}

			break;
		}

		return parent::execute_action( $action_data );
	}

	/**
	 * sync tags in case any tag not available in db and save to db
	 **/
	public function sync_tags( $api_secret ) {
		/** Get the stored sync data */
		$connector = BWFCO_ConvertKit::get_instance();
		if ( false === WFCO_Common::$saved_data ) {
			WFCO_Common::get_connectors_data();
		}
		$data        = WFCO_Common::$connectors_saved_data;
		$slug        = $connector->get_slug();
		$ck_settings = $data[ $slug ];

		/** Check if the last sync is greater than 1 hour */
		$connector_db_id  = $ck_settings['id'];
		$connector_db_row = WFCO_Model_Connectors::get( $connector_db_id );
		$last_sync        = $connector_db_row['last_sync'];
		if ( ! empty( $last_sync ) ) {
			$last_sync = strtotime( $last_sync );
			if ( $last_sync > ( time() - HOUR_IN_SECONDS ) ) {
				$time_to_show = date( 'Y-m-d H:i:s', $last_sync );

				return array(
					'status'  => 4,
					'message' => __( 'Tag is not found in recently synced tags. Last Sync Time: ' . $time_to_show, 'autonami-automations-connectors' )
				);
			}
		}

		/** Fetch the tags via the syncing method of connector */
		$params             = array( 'api_secret' => $api_secret );
		$connector1         = WFCO_Load_Connectors::get_instance();
		$wfco_ck_fetch_tags = $connector1->get_call( 'wfco_ck_fetch_tags' );
		$wfco_ck_fetch_tags->set_data( $params );
		$tags = $wfco_ck_fetch_tags->process();

		if ( ! is_array( $tags ) || ! count( $tags ) > 0 || ! isset( $tags['response'] ) || ! isset( $tags['body'] ) || ! isset( $tags['body']['tags'] ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Convertkit syncing tags failed.', 'autonami-automations-connectors' )
			);
		}

		$saved_tags = array();
		foreach ( $tags['body']['tags'] as $tag_key => $tag ) {
			$saved_tags[ $tag['id'] ] = $tag['name'];
		}

		/** Merge the fetched data with stored sync data */
		$ck_settings['tags'] = $saved_tags;
		WFCO_Common::update_connector_data( $ck_settings, $connector_db_id );
		do_action( 'change_in_connector_data', $this->get_slug() );

		return $saved_tags;
	}

	/**
	 * @param $result
	 * @param $call_object WFCO_CK_Rmv_Tags
	 *
	 * @return array|mixed
	 */
	protected function handle_response( $result, $call_object = null ) {
		if ( isset( $result['status'] ) ) {
			return $result;
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

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_CK_Rmv_Tags';
