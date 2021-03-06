<?php

final class BWFAN_GF_Form_Submit extends BWFAN_Event {
	private static $instance = null;
	public $form_id = 0;
	public $form_title = '';
	public $entry = [];
	public $fields = [];
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'gforms' );
		$this->event_name             = esc_html__( 'Form Submits', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a form is submitted', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'gforms', 'bwf_contact' );
		$this->optgroup_label         = esc_html__( 'Form', 'autonami-automations-pro' );
		$this->priority               = 10;
		$this->customer_email_tag     = '';
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
		add_action( 'wp_ajax_bwfan_get_gforms_form_fields', array( $this, 'bwfan_get_gforms_form_fields' ) );
		add_action( 'gform_after_submission', array( $this, 'process' ), 10, 2 );
		add_filter( 'bwfan_all_event_js_data', array( $this, 'add_form_data' ), 10, 2 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'form_options', $data );
		}
	}

	public function get_view_data() {
		$forms   = GFAPI::get_forms();
		$options = [];

		foreach ( $forms as $form ) {
			$options[ $form['id'] ] = $form['title'];
		}

		return $options;
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_form_id = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'form_id')) ? data.eventSavedData.form_id : '';
            selected_field_map = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'email_map')) ? data.eventSavedData.email_map : '';
            #>
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mt-15 bwfan-mb-15">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Form', 'autonami-automations-pro' ); ?></label>
                <select id="bwfan-gf_form_submit_form_id" class="bwfan-input-wrapper" name="event_meta[form_id]">
                    <option value=""><?php esc_html_e( 'Choose Form', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'form_options') && _.isObject(data.eventFieldsOptions.form_options) ) {
                    _.each( data.eventFieldsOptions.form_options, function( value, key ){
                    selected =(key == selected_form_id)?'selected':'';
                    #>
                    <option value="{{key}}" {{selected}}>{{value}}</option>
                    <# })
                    } #>
                </select>
            </div>

            <#
            show_mapping = !_.isEmpty(selected_form_id)?'block':'none';
            #>
            <div class="bwfan-gf-forms-map bwfan-col-sm-12 bwfan-p-0">
                <div class="bwfan_spinner bwfan_hide"></div>
                <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-gf-field-map" style="display:{{show_mapping}}">
                    <label for="" class="bwfan-label-title"><?php esc_html_e( 'Map Email field if exists (optional)', 'autonami-automations-pro' ); ?></label>
                    <select id="bwfan-gf_email_field_map" class="bwfan-input-wrapper" name="event_meta[email_map]">
                        <option value=""><?php esc_html_e( 'None', 'autonami-automations-pro' ); ?></option>
                        <#
                        _.each( bwfan_events_js_data['gf_form_submit']['selected_form_fields'], function( value, key ){
                        selected =(key == selected_field_map)?'selected':'';
                        #>
                        <option value="{{key}}" {{selected}}>{{value}}</option>
                        <# })
                        #>
                    </select>
                </div>
            </div>
        </script>
        <script>
            jQuery(document).on('change', '#bwfan-gf_form_submit_form_id', function () {
                var selected_id = jQuery(this).val();
                bwfan_events_js_data['gf_form_submit']['selected_id'] = selected_id;
                if (_.isEmpty(selected_id)) {
                    jQuery(".bwfan-gf-field-map").hide();
                    return false;
                }
                jQuery('.bwfan-gf-forms-map .bwfan_spinner').removeClass('bwfan_hide');
                jQuery(".bwfan-gf-field-map").hide();
                jQuery.ajax({
                    method: 'post',
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    datatype: "JSON",
                    data: {
                        action: 'bwfan_get_gforms_form_fields',
                        id: selected_id,
                    },
                    success: function (response) {
                        jQuery('.bwfan-gf-forms-map .bwfan_spinner').addClass('bwfan_hide');
                        jQuery(".bwfan-gf-field-map").show();
                        update_email_field_map(response.fields);
                        _
                        bwfan_events_js_data['gf_form_submit']['selected_form_fields'] = response.fields;
                    }
                });
            });

            function update_email_field_map(fields) {
                jQuery("#bwfan-gf_email_field_map").html('');
                var option = '<option value="">None</option>';
                if (_.size(fields) > 0 && _.isObject(fields)) {
                    _.each(fields, function (v, e) {
                        option += '<option value="' + e + '">' + v + '</option>';
                    });
                }
                jQuery("#bwfan-gf_email_field_map").html(option);
            }

            jQuery('body').on('bwfan-change-rule', function (e, v) {
                if ('gf_form_field' !== v.value) {
                    return;
                }

                var options = '';

                _.each(bwfan_events_js_data['gf_form_submit']['selected_form_fields'], function (value, key) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });

                v.scope.find('.bwfan_gform_form_fields').html(options);
            });

            jQuery('body').on('bwfan-selected-merge-tag', function (e, v) {
                if ('gf_form_field' !== v.tag) {
                    return;
                }

                var options = '';
                var i = 1;
                var selected = '';

                _.each(bwfan_events_js_data['gf_form_submit']['selected_form_fields'], function (value, key) {
                    selected = (i == 1) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                    i++;
                });

                jQuery('.bwfan_gf_form_fields').html(options);
                jQuery('.bwfan_tag_select').trigger('change');
            });
        </script>
		<?php
	}

	public function bwfan_get_gforms_form_fields() {
		$form_id = absint( sanitize_text_field( $_POST['id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification

		if ( empty( $form_id ) ) {
			wp_send_json( array(
				'fields' => array(),
			) );
		}
		$form   = GFAPI::get_form( $form_id );
		$fields = [];
		foreach ( $form['fields'] as $field ) {
			$inputs = $field->get_entry_inputs();
			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					if ( isset( $input['isHidden'] ) && $input['isHidden'] ) {
						continue;
					}
					$fields[ $input['id'] ] = $field->label . ': ' . $input['label'];
				}
			} else {
				$fields[ $field->id ] = $field->label;
			}
		}

		wp_send_json( array(
			'fields' => $fields,
		) );
	}

	public function process( $entry, $form ) {
		$data               = $this->get_default_data();
		$data['entry']      = $entry;
		$data['form_id']    = $form['id'];
		$data['form_title'] = $form['title'];
		$fields             = [];

		foreach ( $form['fields'] as $field ) {
			$inputs = $field->get_entry_inputs();
			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					if ( isset( $input['isHidden'] ) && $input['isHidden'] ) {
						continue;
					}
					$fields[ $input['id'] ] = $field->label . ': ' . $input['label'];
				}
			} else {
				$fields[ $field->id ] = $field->label;
			}
		}

		$data['fields'] = $fields;

		$this->send_async_call( $data );
	}

	public function add_form_data( $event_js_data, $automation_meta ) {
		if ( ! isset( $automation_meta['event_meta'] ) || ! isset( $event_js_data['gf_form_submit'] ) || ! isset( $automation_meta['event_meta']['form_id'] ) ) {
			return $event_js_data;
		}

		if ( isset( $automation_meta['event'] ) && ! empty( $automation_meta['event'] ) && 'gf_form_submit' !== $automation_meta['event'] ) {
			return $event_js_data;
		}

		$event_js_data['gf_form_submit']['selected_id'] = $automation_meta['event_meta']['form_id'];

		$form   = GFAPI::get_form( $automation_meta['event_meta']['form_id'] );
		$fields = [];

		foreach ( $form['fields'] as $field ) {
			$inputs = $field->get_entry_inputs();
			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					if ( isset( $input['isHidden'] ) && $input['isHidden'] ) {
						continue;
					}
					$fields[ $input['id'] ] = $field->label . ': ' . $input['label'];
				}
			} else {
				$fields[ $field->id ] = $field->label;
			}
		}

		$event_js_data['gf_form_submit']['selected_form_fields'] = $fields;

		return $event_js_data;
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		$email_map = $automation_data['event_meta']['email_map'];

		$this->email = ( ! empty( $email_map ) && isset( $this->entry[ $email_map ] ) && is_email( $this->entry[ $email_map ] ) ) ? $this->entry[ $email_map ] : '';

		BWFAN_Core()->rules->setRulesData( $this->form_id, 'form_id' );
		BWFAN_Core()->rules->setRulesData( $this->form_title, 'form_title' );
		BWFAN_Core()->rules->setRulesData( $this->entry, 'entry' );
		BWFAN_Core()->rules->setRulesData( $this->fields, 'fields' );
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->email, $this->get_user_id_event() ), 'bwf_customer' );
	}

	/**
	 * Registers the tasks for current event.
	 *
	 * @param $automation_id
	 * @param $integration_data
	 * @param $event_data
	 */
	public function register_tasks( $automation_id, $integration_data, $event_data ) {
		if ( ! is_array( $integration_data ) ) {
			return;
		}

		$data_to_send = $this->get_event_data();

		$this->create_tasks( $automation_id, $integration_data, $event_data, $data_to_send );
	}

	public function get_event_data() {
		$data_to_send                         = [];
		$data_to_send['global']['form_id']    = $this->form_id;
		$data_to_send['global']['form_title'] = $this->form_title;
		$data_to_send['global']['entry']      = $this->entry;
		$data_to_send['global']['fields']     = $this->fields;
		$data_to_send['global']['email']      = $this->email;

		return $data_to_send;
	}

	/**
	 * Make the view data for the current event which will be shown in task listing screen.
	 *
	 * @param $global_data
	 *
	 * @return false|string
	 */
	public function get_task_view( $global_data ) {
		ob_start();
		?>
        <li>
            <strong><?php echo esc_html__( 'Form ID:', 'autonami-automations-pro' ); ?> </strong>
            <span><?php echo esc_html__( $global_data['form_id'] ); ?></span>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Form Title:', 'autonami-automations-pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['form_title'] ); ?>
        </li>
		<?php
		return ob_get_clean();
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'form_id' );
		if ( ( empty( $get_data ) || intval( $get_data ) !== intval( $task_meta['global']['form_id'] ) ) ) {
			$set_data = array(
				'form_id'    => intval( $task_meta['global']['form_id'] ),
				'form_title' => $task_meta['global']['form_title'],
				'entry'      => $task_meta['global']['entry'],
				'fields'     => $task_meta['global']['fields'],
				'email'      => $task_meta['global']['email'],
			);
			BWFAN_Merge_Tag_Loader::set_data( $set_data );
		}
	}

	/**
	 * Capture the async data for the current event.
	 * @return array|bool
	 */
	public function capture_async_data() {
		$this->form_id    = BWFAN_Common::$events_async_data['form_id'];
		$this->form_title = BWFAN_Common::$events_async_data['form_title'];
		$this->entry      = BWFAN_Common::$events_async_data['entry'];
		$this->fields     = BWFAN_Common::$events_async_data['fields'];
		$this->email      = BWFAN_Common::$events_async_data['email'];

		return $this->run_automations();
	}

	public function get_email_event() {
		return is_email( $this->email ) ? $this->email : false;
	}

	public function get_user_id_event() {
		if ( is_email( $this->email ) ) {
			$user = get_user_by( 'email', $this->email );

			return ( $user instanceof WP_User ) ? $user->ID : false;
		}

		return false;
	}

	/**
	 * Validating form id after submission with the selected form id in the event
	 *
	 * @param $automations_arr
	 *
	 * @return mixed
	 */
	public function validate_event_data_before_creating_task( $automations_arr ) {
		$automations_arr_temp = $automations_arr;
		foreach ( $automations_arr as $automation_id => $automation_data ) {
			if ( absint( $this->form_id ) !== absint( $automation_data['event_meta']['form_id'] ) ) {
				unset( $automations_arr_temp[ $automation_id ] );
			}
		}

		return $automations_arr_temp;
	}


}

/**
 * Register this event to a source.
 * This will show the current event in dropdown in single automation screen.
 */
if ( bwfan_is_gforms_active() ) {
	return 'BWFAN_GF_Form_Submit';
}
