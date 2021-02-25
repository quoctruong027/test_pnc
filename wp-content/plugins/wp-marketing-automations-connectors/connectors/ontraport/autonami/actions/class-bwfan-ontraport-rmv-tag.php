<?php

final class BWFAN_Ontraport_Rmv_Tag extends BWFAN_Action {

	private static $instance = null;

	private function __construct() {
		$this->action_name     = __( 'Remove Tags', 'autonami-automations-connectors' );
		$this->action_desc     = __( 'This action removes the selected tags from the contact', 'autonami-automations-connectors' );
		$this->action_priority = 30;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Ontraport_Rmv_Tag|null
	 */
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
		$final_tags             = [];
		$user_input_tags        = $task_meta['data']['tags'];
		$data_to_set            = array();
		$data_to_set['api_key'] = $integration_object->get_settings( 'api_key' );
		$data_to_set['app_id'] = $integration_object->get_settings( 'app_id' );
		$data_to_set['email']   = $task_meta['global']['email'];

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
		$data_to_set['remove_tags'] = $final_tags;
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
		$all_integration      = BWFAN_Core()->integration->get_integration( $action_data['integration_slug'] );
		$integration_settings = $all_integration->get_settings();
		$tags                 = $action_data['processed_data']['remove_tags'];
		$search_tags = array();
		foreach ( $tags as $tag_key => $tag_value ) {
			/** If nothing in DB */
			if ( ! isset( $integration_settings['tags'] ) ) {
				continue;
			}
			/** tag value is tag id and is available */
			if ( isset( $integration_settings['tags'][ $tag_value ] ) ) {
				continue;
			}

			/** If tag value found in saved tags, append proper tag id */
			$saved_tag_id = array_search( $tag_value, $integration_settings['tags'] );
			if ( false !== $saved_tag_id ) {
				unset( $action_data['processed_data']['remove_tags'][ $tag_key ] );
				$action_data['processed_data']['remove_tags'][] = $saved_tag_id;
				continue;
			}
			/** Else search tag */
			unset( $action_data['processed_data']['remove_tags'][ $tag_key ] );
			$search_tags[] = $tag_value;
		}

		if ( is_array( $search_tags ) && count( $search_tags ) > 0 ) {
			$search_tags = $this->search_tags( $search_tags, $action_data );
			if ( is_array( $search_tags ) && count( $search_tags ) > 0 ) {
				$tags_to_combine                       = array_flip( $search_tags );
				$action_data['processed_data']['remove_tags'] = array_merge( $action_data['processed_data']['remove_tags'], $tags_to_combine );
			}
		}
		return parent::execute_action( $action_data );
	}


	public function search_tags($tags,$action_data){
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call_class     = $load_connector->get_call( 'wfco_ontraport_search_tag_by_name' );
		if ( is_null( $call_class ) ) {
			return false;
		}

		if(!is_array($tags) && count($tags)==0){
			return false;
		}
		$search_tag11 = array();
		foreach($tags as $search_tag){
			/** $action_data['processed_data'] contains APP ID and APP Key, So going to pass this directly to call */
			$action_data['processed_data']['tag'] = $search_tag;
			$call_class->set_data( $action_data['processed_data'] );
			$response = $call_class->process();
			if( false !== $response ){
				$search_tag11[ $response ] = $response;
				do_action( 'wfco_ontraport_tag_created', $response, $search_tag );
			}
		}
		return $search_tag11;
	}

  protected function handle_response( $result, $call_object = null ) {
    if ( isset( $result['status'] ) ) {
      return $result;
    }


    if(isset($result['bwfan_custom_message'])){
      return array(
        'status'  => 4,
        'message' => $result['bwfan_custom_message'],
      );
    }

    if ( isset( $result['bwfan_success_message'] ) && 1=== intval($result['bwfan_success_message'])  ) {
      return array(
        'status'  => 3,
        'message' => __( 'Tags removed from contact successfully!', 'autonami-automations-connectors' ),
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

return 'BWFAN_Ontraport_Rmv_Tag';
