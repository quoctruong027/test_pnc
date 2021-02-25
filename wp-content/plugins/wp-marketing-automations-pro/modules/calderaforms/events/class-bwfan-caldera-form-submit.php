<?php

final class BWFAN_Caldera_Form_Submit extends BWFAN_Event {
	private static $instance = null;
	public $form_id = 0;
	public $form_title = '';
	public $fields = [];
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'calderaforms' );
		$this->event_name             = esc_html__( 'Form Submits', 'wp-marketing-automations' );
		$this->event_desc             = esc_html__( 'This event runs after a form is submitted', 'wp-marketing-automations' );
		$this->event_rule_groups      = array( 'calderaforms', 'bwf_contact' );
		$this->optgroup_label         = esc_html__( 'Form', 'wp-marketing-automations' );
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
		add_action( 'wp_ajax_bwfan_get_caldera_form_fields', array( $this, 'bwfan_get_caldera_form_fields' ) );
		add_action( 'caldera_forms_submit_complete', array( $this, 'process' ), 999, 4 );
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
		$options = [];

		$forms = Caldera_Forms_Forms::get_forms( true );

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[ $form['ID'] ] = $form['name'];
			}
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
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Form', 'wp-marketing-automations' ); ?></label>
                <select id="bwfan-caldera_form_submit_form_id" class="bwfan-input-wrapper" name="event_meta[form_id]">
                    <option value=""><?php esc_html_e( 'Choose Form', 'wp-marketing-automations' ); ?></option>
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
            <div class="bwfan-caldera-forms-map bwfan-col-sm-12 bwfan-p-0 bwfan-mt-5">
                <div class="bwfan_spinner bwfan_hide"></div>
                <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-caldera-field-map" style="display:{{show_mapping}}">
                    <label for="" class="bwfan-label-title">
						<?php esc_html_e( 'Select Email Field', 'wp-marketing-automations' ); ?>
                        <div class="bwfan_tooltip" data-size="2xl">
                            <span class="bwfan_tooltip_text" data-position="top"><?php esc_html_e( 'Map the email field to be used by appropriate Rules and Actions.', 'wp-marketing-automations' ); ?></span>
                        </div>
                    </label>
                    <select id="bwfan-caldera_email_field_map" class="bwfan-input-wrapper" name="event_meta[email_map]">
                        <option value=""><?php esc_html_e( 'none', 'wp-marketing-automations' ); ?></option>
                        <#
                        _.each( bwfan_events_js_data['caldera_form_submit']['selected_form_fields'], function( value, key ){
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
            jQuery(document).on('change', '#bwfan-caldera_form_submit_form_id', function () {
                var selected_id = jQuery(this).val();
                bwfan_events_js_data['caldera_form_submit']['selected_id'] = selected_id;
                if (_.isEmpty(selected_id)) {
                    jQuery(".bwfan-caldera-field-map").hide();
                    return false;
                }
                jQuery(".bwfan-caldera-forms-map .bwfan_spinner").removeClass('bwfan_hide');
                jQuery(".bwfan-caldera-field-map").hide();
                jQuery.ajax({
                    method: 'post',
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    datatype: "JSON",
                    data: {
                        action: 'bwfan_get_caldera_form_fields',
                        id: selected_id,
                    },
                    success: function (response) {
                        jQuery(".bwfan-caldera-forms-map .bwfan_spinner").addClass('bwfan_hide');
                        jQuery(".bwfan-caldera-field-map").show();
                        update_caldera_email_field_map(response.fields);
                        bwfan_events_js_data['caldera_form_submit']['selected_form_fields'] = response.fields;
                    }
                });
            });

            function update_caldera_email_field_map(fields) {
                jQuery("#bwfan-caldera_email_field_map").html('');
                var option = '<option value="">none</option>';
                if (_.size(fields) > 0 && _.isObject(fields)) {
                    _.each(fields, function (v, e) {
                        option += '<option value="' + e + '">' + v + '</option>';
                    });
                }
                jQuery("#bwfan-caldera_email_field_map").html(option);
            }

            jQuery('body').on('bwfan-change-rule', function (e, v) {
                if ('caldera_form_field' !== v.value) {
                    return;
                }

                var options = '';

                _.each(bwfan_events_js_data['caldera_form_submit']['selected_form_fields'], function (value, key) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });
                v.scope.find('.bwfan_caldera_form_fields').html(options);
            });

            jQuery('body').on('bwfan-selected-merge-tag', function (e, v) {
                if ('caldera_form_field' !== v.tag) {
                    return;
                }

                var options = '';
                var i = 1;
                var selected = '';

                _.each(bwfan_events_js_data['caldera_form_submit']['selected_form_fields'], function (value, key) {
                    selected = (i == 1) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                    i++;
                });
                jQuery('.bwfan_caldera_form_fields').html(options);
                jQuery('.bwfan_tag_select').trigger('change');
            });
        </script>
		<?php
	}

	public function bwfan_get_caldera_form_fields() {
		$form_id = sanitize_text_field( $_POST['id'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$fields  = [];
		if ( ! empty( $form_id ) ) {
			$fields = $this->get_form_fields( $form_id );
		}

		wp_send_json( array(
			'fields' => $fields,
		) );
	}

	public function get_form_fields( $form_id ) {

		if ( empty( $form_id ) ) {
			return array();
		}

		$form = Caldera_Forms_Forms::get_form( $form_id );

		if ( empty( $form['fields'] ) ) {
			return array();
		}

		$form_fields = $form['fields'];

		$fields = array();

		$invalid_types = [ 'html', 'button' ];

		foreach ( $form_fields as $field ) {

			if ( in_array( $field['type'], $invalid_types ) || false !== strpos( $field['type'], 'file' ) ) {
				continue;
			}

			$fields[ $field['slug'] ] = $field['label'];
		}

		return $fields;
	}

	public function process( $form, $referrer, $process_id, $entryid ) {
		$data            = $this->get_default_data();
		$data['form_id'] = $form['ID'];
		$data['fields']  = $this->get_submitted_form_values( $form, $entryid );

		$data['form_title'] = $form['name'];
		$this->send_async_call( $data );
	}

	/**
	 * @param $form
	 * @param $entryid
	 *
	 * @return array
	 */
	public function get_submitted_form_values( $form, $entryid ) {
		$entry  = new Caldera_Forms_Entry( $form, $entryid );
		$fields = $entry->get_fields();
		$data   = [];
		foreach ( $fields as $field ) {
			if ( $field->slug === 'submit' ) {
				continue;
			}
			$data[ $field->slug ] = $field->value;
		}

		return $data;
	}

	public function add_form_data( $event_js_data, $automation_meta ) {
		if ( ! isset( $automation_meta['event_meta'] ) || ! isset( $event_js_data['caldera_form_submit'] ) || ! isset( $automation_meta['event_meta']['form_id'] ) ) {
			return $event_js_data;
		}

		if ( isset( $automation_meta['event'] ) && ! empty( $automation_meta['event'] ) && 'caldera_form_submit' !== $automation_meta['event'] ) {
			return $event_js_data;
		}

		$event_js_data['caldera_form_submit']['selected_id'] = $automation_meta['event_meta']['form_id'];
		$fields                                              = $this->get_form_fields( $automation_meta['event_meta']['form_id'] );

		$event_js_data['caldera_form_submit']['selected_form_fields'] = $fields;

		return $event_js_data;
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {
		$email_map   = $automation_data['event_meta']['email_map'];
		$this->email = ( ! empty( $email_map ) && isset( $this->fields[ $email_map ] ) && is_email( $this->fields[ $email_map ] ) ) ? $this->fields[ $email_map ] : '';
		BWFAN_Core()->rules->setRulesData( $this->form_id, 'form_id' );
		BWFAN_Core()->rules->setRulesData( $this->form_title, 'form_title' );
		BWFAN_Core()->rules->setRulesData( $this->fields, 'fields' );
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->email, $this->get_user_id_event() ), 'bwf_customer' );
	}

	public function get_user_id_event() {
		if ( is_email( $this->email ) ) {
			$user = get_user_by( 'email', $this->email );

			return ( $user instanceof WP_User ) ? $user->ID : false;
		}

		return false;
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
            <strong><?php echo esc_html__( 'Form ID:', 'autonami - automations - pro' ); ?> </strong>
            <span><?php echo esc_html__( $global_data['form_id'] ); ?></span>
        </li>
        <li>
            <strong><?php echo esc_html__( 'Form Title:', 'autonami - automations - pro' ); ?> </strong>
			<?php echo esc_html__( $global_data['form_title'] ); ?>
        </li>
		<?php
		if ( isset( $global_data['fields'] ) && is_array( $global_data['fields'] ) && count( $global_data['fields'] ) > 0 ) {
			$h = 0;
			foreach ( $global_data['fields'] as $key => $value ) {
				if ( ! empty( $value ) ) {
					?>
                    <li>
                        <strong><?php echo esc_html__( 'Field ', 'autonami - automations - pro' ) . '(' . $key; ?>): </strong>
						<?php echo is_array( $value ) ? implode( ', ', $value ) : $value; ?>
                    </li>
					<?php
					$h ++;
				}
				if ( 2 <= $h ) {
					break;
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Set global data for all the merge tags which are supported by this event.
	 *
	 * @param $task_meta
	 */
	public function set_merge_tags_data( $task_meta ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data( 'form_id' );
		if ( empty( $get_data ) || $get_data !== $task_meta['global']['form_id'] ) {
			$set_data = array(
				'form_id'    => $task_meta['global']['form_id'],
				'form_title' => $task_meta['global']['form_title'],
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
		$this->fields     = BWFAN_Common::$events_async_data['fields'];

		return $this->run_automations();
	}

	public function get_email_event() {
		return is_email( $this->email ) ? $this->email : false;
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
if ( bwfan_is_caldera_forms_active() ) {
	return 'BWFAN_Caldera_Form_Submit';
}
