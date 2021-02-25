<?php

//namespace ElementorPro\Classes;

use ElementorPro\Plugin;

final class BWFAN_Elementor_Form_Submit extends BWFAN_Event {
	private static $instance = null;
	public $form_id = 0;
	public $page_id = 0;
	public $form_title = '';
	public $fields = [];
	public $entry = [];
	public $email = '';

	private function __construct() {
		$this->event_merge_tag_groups = array( 'elementor-forms' );
		$this->event_name             = esc_html__( 'Form Submits', 'autonami-automations-pro' );
		$this->event_desc             = esc_html__( 'This event runs after a form is submitted', 'autonami-automations-pro' );
		$this->event_rule_groups      = array( 'elementor-forms', 'bwf_contact' );
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
		add_action( 'wp_ajax_bwfan_get_elementor_page_forms', array( $this, 'bwfan_get_elementor_page_forms' ) );
		add_action( 'elementor_pro/forms/new_record', array( $this, 'process' ), 10, 2 );
		add_filter( 'bwfan_all_event_js_data', array( $this, 'add_form_data' ), 10, 2 );
		add_action( 'in_admin_footer', array( $this, 'bwfan_elementor_form_js' ), 88 );
	}

	/**
	 * Localize data for html fields for the current event.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$data = $this->get_view_data();

			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'page_options', $data['page_options'] );
			BWFAN_Core()->admin->set_events_js_data( $this->get_slug(), 'form_options', $data['form_options'] );
		}
	}


	/**
	 *  load additional script only when elementor_popup_form_submit event selected
	 */
	public function bwfan_elementor_form_js() {
		//you can check if this is the right page
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'autonami' && isset( $_GET['section'] ) && $_GET['section'] === 'automation' ) {
			?>
            <script type='text/javascript'>
                jQuery(document).ready(function () {
                    jQuery('body').on('bwfan-change-rule', function (e, v) {
                        var event = (_.has(BWFAN_Auto, 'uiDataDetail') && _.has(BWFAN_Auto.uiDataDetail, 'trigger') && _.has(BWFAN_Auto.uiDataDetail.trigger, 'event')) ? BWFAN_Auto.uiDataDetail.trigger.event : '';
                        if (event === '' || event !== 'elementor_form_submit') {
                            return;
                        }

                        if ('elementor_form_field' !== v.value) {
                            return;
                        }
                        var options = '';
                        page_form_fields = bwfan_events_js_data['elementor_form_submit']['page_form_fields'];
                        _.each(page_form_fields, function (value, key) {
                            if (!_.isEmpty(value['field_label'])) {
                                options += '<option value="' + value['field_slug'] + '">' + value['field_label'] + '</option>';
                            }
                        });
                        v.scope.find('.bwfan_elementor_form_fields').html(options);
                    });

                    jQuery('body').on('bwfan-selected-merge-tag', function (e, v) {
                        var event = (_.has(BWFAN_Auto, 'uiDataDetail') && _.has(BWFAN_Auto.uiDataDetail, 'trigger') && _.has(BWFAN_Auto.uiDataDetail.trigger, 'event')) ? BWFAN_Auto.uiDataDetail.trigger.event : '';
                        if (event === '' || event !== 'elementor_form_submit') {
                            return;
                        }
                        if ('elementor_form_field' !== v.tag) {
                            return;
                        }

                        var options = '';
                        var i = 1;
                        var selected = '';

                        _.each(bwfan_events_js_data['elementor_form_submit']['selected_form_fields'], function (value, key) {
                            selected = (i == 1) ? 'selected' : '';
                            options += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                            i++;
                        });

                        jQuery('.bwfan_elementor_form_fields').html(options);
                        jQuery('.bwfan_tag_select').trigger('change');
                    });
                });
            </script>
			<?php
		}
	}

	public function get_view_data() {
		$event_view_data                 = array();
		$post_data                       = BWFAN_Elementor_Common::get_elementor_form_by_type( false, true );
		$event_view_data['page_options'] = empty( $post_data ) ? '' : $post_data;
		$event_form_page_data            = array();

		foreach ( $post_data as $key => $group ) {
			foreach ( $group['posts'] as $page ) {
				$event_form_page_data[ $key ][ $page['post_id'] ] = $this->get_page_form_data( $page['post_id'] );
			}
		}
		$event_view_data['form_options'] = $event_form_page_data;

		return $event_view_data;

	}

	/**
	 * Get page form details
	 *
	 * @param int $post_id
	 *
	 * @return array|void
	 */
	public function get_page_form_data( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			return array();
		}

		if ( ! class_exists( 'ElementorPro\Plugin' ) ) {
			return;
		}

		$document = Plugin::elementor()->documents->get( $post_id );
		if ( empty( $document ) ) {
			return array();
		}

		$data = $document->get_elements_data();

		/** Get Forms for top level form widget (Global Widget) */
		$forms = array();
		if ( isset( $data[0]['widgetType'] ) && 'form' === $data[0]['widgetType'] ) {
			$forms[0] = $data[0];
		}

		/** Get Forms from Elementor Data Iterator, if no top level form present */
		if ( empty( $forms ) ) {
			Plugin::elementor()->db->iterate_data( $data, function ( $element ) use ( &$forms ) {
				if ( isset( $element['widgetType'] ) && 'form' === $element['widgetType'] ) {
					$forms[] = $element;
				}

				return $element;
			} );
		}

		$elem_post_fields = array();
		foreach ( $forms as $form_key => $elem_fields ) {
			if ( ! isset( $elem_fields['settings'] ) ) {
				continue;
			}
			$elem_post_fields[ $form_key ]['form_name']   = $elem_fields['settings']['form_name'];
			$elem_post_fields[ $form_key ]['form_fields'] = $elem_fields['settings']['form_fields'];
			$elem_post_fields[ $form_key ]['form_id']     = $elem_fields['id'];

			if ( empty( $elem_post_fields[ $form_key ]['form_fields'] ) ) {
				continue;
			}
			foreach ( $elem_post_fields[ $form_key ]['form_fields'] as $field_key => $form_field ) {
				$field_data = $form_field;
				if ( 'hidden' === $field_data['field_type'] ) {
					continue;
				}

				unset( $elem_post_fields[ $form_key ]['form_fields'][ $field_key ] );
				$elem_post_fields[ $form_key ]['form_fields'][ $field_key ]['field_type']  = $form_field['field_type'];
				$elem_post_fields[ $form_key ]['form_fields'][ $field_key ]['field_label'] = $form_field['field_label'];
				$elem_post_fields[ $form_key ]['form_fields'][ $field_key ]['field_slug']  = $form_field['custom_id'];
			}
		}

		return $elem_post_fields;
	}

	public function maybe_get_global_form_wp_id( $page_id, $form_id ) {
		$forms = BWFAN_Elementor_Common::get_forms_by_global_form_page( $page_id );
		if ( empty( $forms ) ) {
			return false;
		}

		foreach ( $forms as $form ) {
			if ( $form_id === $form['id'] ) {
				return absint( $form['widget_wp_id'] );
			}
		}

		return false;
	}

	public function get_form_fields( $form_id, $page_id ) {
		if ( empty( $form_id ) || empty( $page_id ) ) {
			return array();
		}

		/** If global form, then set both form_id and page_id as global_form_id */
		$global_form_id = $this->maybe_get_global_form_wp_id( $page_id, $form_id );
		if ( false !== $global_form_id ) {
			$form_id = $global_form_id;
			$page_id = $global_form_id;
		}

		$page_forms = $this->get_page_form_data( $page_id );
		if ( ! is_array( $page_forms ) || empty( $page_forms ) ) {
			return array();
		}

		$current_form = false;
		foreach ( $page_forms as $form ) {
			if ( $form_id === ( is_numeric( $form['form_id'] ) ? absint( $form['form_id'] ) : $form['form_id'] ) ) {
				$current_form = $form;
				break;
			}
		}

		if ( empty( $current_form ) ) {
			return array();
		}

		$fields = [];
		foreach ( $current_form['form_fields'] as $key => $field ) {
			if ( 'hidden' === $field['field_type'] || ! isset( $field['field_slug'] ) ) {
				continue;
			}
			$fields[ $key ] = $field;
		}

		return array(
			$form_id => $fields
		);
	}

	/**
	 * Show the html fields for the current event.
	 */
	public function get_view( $db_eventmeta_saved_value ) {

		?>
        <script type="text/html" id="tmpl-event-<?php echo esc_html__( $this->get_slug() ); ?>">
            <#
            selected_page_id = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'page_id')) ? data.eventSavedData.page_id : '';
            selected_form_id = (_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'form_id')) ? data.eventSavedData.form_id : '';
            selected_field_map =(_.has(data, 'eventSavedData') &&_.has(data.eventSavedData, 'email_map')) ? data.eventSavedData.email_map : '';
            form_options = (_.has(data, 'eventFieldsOptions') &&_.has(data.eventFieldsOptions, 'form_options')) ? data.eventFieldsOptions.form_options:{};
            selected_page_forms='';
            selected_form_fields ='';
            page_form ='';
            if(_.size(form_options)>0){
            _.each(form_options, function(group, group_key) {
            _.each(group,function(value, key){
            if(key === selected_page_id){
            page_form = value;
            }
            });
            });
            }
            #>

            <#
            // get form fields
            if(_.isArray(page_form)){
            _.each(page_form,function(value,key){
            if(value['form_id'] === selected_form_id){
            selected_page_forms =value['form_fields'];
            }
            });
            }
            bwfan_events_js_data['elementor_form_submit']['page_form_fields'] = selected_page_forms;
            #>
            <div class="bwfan_mt15"></div>
            <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-mb-15">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Page', 'autonami-automations-pro' ); ?></label>
                <select id="bwfan-elementor_page_submit_page_id" class="bwfan-input-wrapper" name="event_meta[page_id]">
                    <option value=""><?php esc_html_e( 'Choose Page', 'autonami-automations-pro' ); ?></option>
                    <#
                    if(_.has(data.eventFieldsOptions, 'page_options') && _.isObject(data.eventFieldsOptions.page_options) ) {
                    _.each( data.eventFieldsOptions.page_options, function( post_type, key ){
                    #>
                    <optgroup label="{{post_type['title']}}">
                        <#
                        _.each( post_type['posts'], function( value, key ){
                        selected =(value['post_id'] == selected_page_id)?'selected':'';
                        #>
                        <option value="{{value['post_id']}}" {{selected}}>{{value['post_title']}}</option>
                        <# }) #>
                    </optgroup>
                    <# })
                    } #>
                </select>
            </div>
            <#
            show_form_select = _.size(page_form)>0?'block':'none';
            select_form_name = _.size(page_form)>0?'name=event_meta[form_id]':'';
            #>
            <div class="bwfan-elementor-form-section bwfan-col-sm-12 bwfan-p-0 bwfan-mb-15">
                <div class="bwfan_spinner bwfan_hide"></div>
                <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-elementor-forms " style="display:{{show_form_select}}">
                    <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Form', 'autonami-automations-pro' ); ?></label>
                    <select id="bwfan-elementor_page_submit_form_id" class="bwfan-input-wrapper" data-name="event_meta[form_id]" {{select_form_name}}>
                        <option value=""><?php esc_html_e( 'Choose Form', 'autonami-automations-pro' ); ?></option>
                        <#
                        _.each( page_form, function( value, key ){
                        selected =(value['form_id'] == selected_form_id)?'selected':'';
                        #>
                        <option value="{{value['form_id']}}" {{selected}}>{{value['form_name']}}</option>
                        <# })
                        #>
                    </select>
                </div>
            </div>

            <#
            show_form_hidden = _.size(page_form)>0?'none':'block';
            hidden_form_name = _.size(page_form)>0?'':'name=event_meta[form_id]';
            #>
            <input type="hidden" data-name="event_meta[form_id]" {{hidden_form_name}} value="{{selected_form_id}}" id="bwfan-elementor_form_id" style="display:{{show_form_hidden}}"/>

            <#
            show_mapping = !_.isEmpty(selected_form_id)?'block':'none';
            #>
            <div class="bwfan-elementor-forms-map bwfan-col-sm-12 bwfan-p-0">
                <div class="bwfan_spinner bwfan_hide"></div>
                <div class="bwfan-col-sm-12 bwfan-p-0 bwfan-elementor-field-map" style="display:{{show_mapping}}">
                    <label for="" class="bwfan-label-title">
						<?php esc_html_e( 'Select Email Field', 'autonami-automations-pro' ); ?>
                        <div class="bwfan_tooltip" data-size="2xl">
                            <span class="bwfan_tooltip_text" data-position="top">Map the email field to be used by appropriate Rules and Actions.</span>
                        </div>
                    </label>
                    <select id="bwfan-elementor_email_field_map" class="bwfan-input-wrapper" name="event_meta[email_map]">
                        <option value=""><?php esc_html_e( 'None', 'autonami-automations-pro' ); ?></option>
                        <#
                        _.each( bwfan_events_js_data['elementor_form_submit']['selected_form_fields'], function( value, key ){
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
            //on change elementor page
            jQuery(document).on('change', '#bwfan-elementor_page_submit_page_id', function () {
                var selected_id = jQuery(this).val();
                bwfan_events_js_data['elementor_form_submit']['page_selected_id'] = selected_id;
                if (_.isEmpty(selected_id)) {
                    jQuery("#bwfan-elementor_form_id").val('');
                    jQuery(".bwfan-elementor-forms").hide();
                    jQuery("#bwfan-elementor_page_submit_form_id").removeAttr('name');
                    jQuery("#bwfan-elementor_form_id").show();
                    jQuery("#bwfan-elementor_form_id").attr('name', 'event_meta[form_id]');
                    bwfan_events_js_data['elementor_form_submit']['page_form_fields'] = '';
                    jQuery(".bwfan-elementor-field-map").hide();
                    update_elementor_email_field_map([]);
                    return;
                }
                jQuery.ajax({
                    method: 'post',
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    datatype: "JSON",
                    data: {
                        action: 'bwfan_get_elementor_page_forms',
                        id: selected_id,
                    },
                    success: function (response) {
                        bwfan_events_js_data['elementor_form_submit']['all_form_data'] = [];

                        jQuery('.bwfan-elementor-form-section .bwfan_spinner').removeClass('bwfan_hide');
                        jQuery(".bwfan-elementor-field-map").hide();
                        option_html = '<option value="">Choose Form</option>';
                        _.each(response.forms, function (value, key) {
                                if (!_.isEmpty(value['form_id']) || 0 !== parseInt(value['form_id'])) {
                                    option_html += '<option value=' + value["form_id"] + '>' + value["form_name"] + '</option>';
                                }
                            }
                        )

                        //get the selected form fields on change
                        setTimeout(function () {
                            jQuery('.bwfan-elementor-form-section .bwfan_spinner').addClass('bwfan_hide');
                            jQuery(".bwfan-elementor-forms").show();
                            jQuery("#bwfan-elementor_page_submit_form_id").attr('name', 'event_meta[form_id]');
                            jQuery("#bwfan-elementor_form_id").hide();
                            jQuery("#bwfan-elementor_form_id").removeAttr('name');
                            jQuery("#bwfan-elementor_page_submit_form_id").html(option_html);
                        }, 500);
                        bwfan_events_js_data['elementor_form_submit']['all_form_data'] = response.forms;
                    }
                });
            });

            //on change elementor page form
            jQuery(document).on('change', '#bwfan-elementor_page_submit_form_id', function () {
                var form_selected_id = jQuery(this).val();
                var page_selected_id = jQuery("#bwfan-elementor_page_submit_page_id option:selected").val();
                bwfan_events_js_data['elementor_form_submit']['form_selected_id'] = form_selected_id;
                if (_.isEmpty(form_selected_id)) {
                    jQuery("#bwfan-elementor_form_id").val('');
                    bwfan_events_js_data['elementor_form_submit']['page_form_fields'] = [];
                    jQuery(".bwfan-elementor-field-map").hide();
                    update_elementor_email_field_map([]);
                } else {
                    _.each(bwfan_events_js_data['elementor_form_submit']['all_form_data'], function (form) {
                        if (form_selected_id == form["form_id"]) {
                            page_form_fields = _.filter(form['form_fields'], function (field) {
                                return 'hidden' != field['field_type'] && !_.isUndefined(field['field_slug']);
                            });
                            selected_form_fields = {};
                            _.each(page_form_fields, function (field, index) {
                                selected_form_fields[field['field_slug']] = field['field_label'];
                            });

                            bwfan_events_js_data['elementor_form_submit']['page_form_fields'] = page_form_fields;
                            bwfan_events_js_data['elementor_form_submit']['selected_form_fields'] = selected_form_fields;
                            update_elementor_email_field_map(selected_form_fields);
                            jQuery(".bwfan-elementor-forms-map .bwfan_spinner").addClass('bwfan_hide');
                            jQuery(".bwfan-elementor-field-map").show();
                            return;
                        }
                    });

                }
            });

            function update_elementor_email_field_map(fields) {
                jQuery("#bwfan-elementor_email_field_map").html('');
                var option = '<option value="">None</option>';
                if (_.size(fields) > 0 && (_.isObject(fields) || _.isArray(fields))) {
                    _.each(fields, function (v, e) {
                        option += '<option value="' + e + '">' + v + '</option>';
                    });
                }
                jQuery("#bwfan-elementor_email_field_map").html(option);
            }

        </script>
		<?php
	}

	public function bwfan_get_elementor_page_forms() {
		$page_id        = absint( sanitize_text_field( $_POST['id'] ) ); // WordPress.CSRF.NonceVerification.NoNonceVerification
		$page_form_data = $this->get_page_form_data( $page_id );

		if ( empty( $page_form_data ) ) {
			wp_send_json( array(
				'forms' => '',
			) );
		}

		wp_send_json( array(
			'forms' => $page_form_data,
		) );
	}

	public function process( $record, $ajax_handler ) {
		$form_id   = $record->get_form_settings( 'id' );
		$page_id   = absint( $_POST['queried_id'] );
		$form_name = $record->get_form_settings( 'form_name' );

		/** If Global Widget exists in Popup */
		if ( BWFAN_Elementor_Common::is_popup( absint( $_POST['post_id'] ) ) ) {
			$page_id = absint( $_POST['post_id'] );
		}

		$data               = $this->get_default_data();
		$data['form_id']    = $form_id;
		$data['form_title'] = $form_name;
		$data['entry']      = $record->get( 'sent_data' );
		$data['page_id']    = $page_id;
		$data['fields']     = $this->get_form_fields( $form_id, $page_id );

		$this->send_async_call( $data );
	}

	public function add_form_data( $event_js_data, $automation_meta ) {
		if ( ! isset( $automation_meta['event_meta'] ) || ! isset( $event_js_data['elementor_form_submit'] ) || ! isset( $automation_meta['event_meta']['page_id'] ) || ! isset( $automation_meta['event_meta']['form_id'] ) ) {
			return $event_js_data;
		}

		if ( isset( $automation_meta['event'] ) && ! empty( $automation_meta['event'] ) && 'elementor_form_submit' !== $automation_meta['event'] ) {
			return $event_js_data;
		}
		$selected_fields                                            = array();
		$event_js_data['elementor_form_submit']['page_selected_id'] = $automation_meta['event_meta']['page_id'];
		$event_js_data['elementor_form_submit']['form_selected_id'] = $automation_meta['event_meta']['form_id'];
		$form_fields                                                = $this->get_form_fields( $automation_meta['event_meta']['form_id'], $automation_meta['event_meta']['page_id'] );

		$page_field = array();
		if ( ! empty( $form_fields ) ) {
			foreach ( $form_fields as $key => $fields ) {
				$page_field[] = $fields;
				foreach ( $fields as $fie ) {
					if ( 'hidden' === $fie['field_type'] ) {
						continue;
					}
					$selected_fields[ $fie['field_slug'] ] = $fie['field_label'];
				}
			}
		}

		$event_js_data['elementor_form_submit']['page_form_fields']     = $page_field;
		$event_js_data['elementor_form_submit']['selected_form_fields'] = $selected_fields;

		return $event_js_data;
	}

	/**
	 * Set up rules data
	 *
	 * @param $automation_data
	 */
	public function pre_executable_actions( $automation_data ) {

		$email_map   = isset( $automation_data['event_meta']['email_map'] ) ? $automation_data['event_meta']['email_map'] : false;
		$this->email = ( ! empty( $email_map ) && isset( $this->entry[ $email_map ] ) && is_email( $this->entry[ $email_map ] ) ) ? $this->entry[ $email_map ] : ( isset( $this->entry['email'] ) && is_email( $this->entry['email'] ) ? $this->entry['email'] : '' );

		BWFAN_Core()->rules->setRulesData( $this->form_id, 'form_id' );
		BWFAN_Core()->rules->setRulesData( $this->form_title, 'form_title' );
		BWFAN_Core()->rules->setRulesData( $this->fields, 'fields' );
		BWFAN_Core()->rules->setRulesData( $this->page_id, 'page_id' );
		BWFAN_Core()->rules->setRulesData( $this->entry, 'entry' );
		BWFAN_Core()->rules->setRulesData( $this->email, 'email' );
		BWFAN_Core()->rules->setRulesData( BWFAN_Common::get_bwf_customer( $this->email, $this->get_user_id_event() ), 'bwf_customer' );
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
		$data_to_send['global']['page_id']    = $this->page_id;
		$data_to_send['global']['entry']      = $this->entry;
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
		if ( isset( $global_data['page_id'] ) && ! empty( $global_data['page_id'] ) ) {
			?>

            <li>
                <strong><?php echo esc_html__( 'Page :', 'autonami-automations-pro' ); ?> </strong>
                <a href="<?php echo get_edit_post_link( $global_data['page_id'] ); ?>" target="_blank"><?php echo esc_html__( get_the_title( $global_data['page_id'] ) ); ?></a>
            </li>
		<?php } ?>
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
				'form_id'    => $task_meta['global']['form_id'],
				'page_id'    => absint( $task_meta['global']['page_id'] ),
				'form_title' => $task_meta['global']['form_title'],
				'fields'     => $task_meta['global']['fields'],
				'entry'      => $task_meta['global']['entry'],
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
		$this->page_id    = BWFAN_Common::$events_async_data['page_id'];
		$this->form_title = BWFAN_Common::$events_async_data['form_title'];
		$this->fields     = BWFAN_Common::$events_async_data['fields'];
		$this->entry      = BWFAN_Common::$events_async_data['entry'];

		return $this->run_automations();
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
			$form_wp_id    = $this->maybe_get_global_form_wp_id( $this->page_id, $this->form_id );
			$form_id       = ( false !== $form_wp_id ) ? absint( $form_wp_id ) : $this->form_id;
			$match_form_id = is_numeric( $automation_data['event_meta']['form_id'] ) ? absint( $automation_data['event_meta']['form_id'] ) : $automation_data['event_meta']['form_id'];

			if ( $form_id !== $match_form_id ) {
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
if ( bwfan_is_elementorpro_active() ) {
	return 'BWFAN_Elementor_Form_Submit';
}