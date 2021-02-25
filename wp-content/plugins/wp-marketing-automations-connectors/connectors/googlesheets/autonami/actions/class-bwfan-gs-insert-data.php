<?php

class BWFAN_GS_Insert_Data extends BWFAN_Action {

	private static $instance = null;

	public function __construct() {
		$this->action_name = __( 'Insert Row', 'autonami-automations-connectors' );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
	}

	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$integration_data = $this->get_view_data();
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'worksheet_column_options', $integration_data );
		}
	}

	public function get_view_data() {
		$columns = array(
			'A' => 'A',
			'B' => 'B',
			'C' => 'C',
			'D' => 'D',
			'E' => 'E',
			'F' => 'F',
			'G' => 'G',
			'H' => 'H',
			'I' => 'I',
			'J' => 'J',
			'K' => 'K',
			'L' => 'L',
			'M' => 'M',
			'N' => 'N',
			'O' => 'O',
			'P' => 'P',
			'Q' => 'Q',
			'R' => 'R',
			'S' => 'S',
			'T' => 'T',
			'U' => 'U',
			'V' => 'V',
			'W' => 'W',
			'X' => 'X',
			'Y' => 'Y',
			'Z' => 'Z',
		);

		return $columns;
	}

	public function get_view() {
		$unique_slug = $this->get_slug();
		?>
        <script type="text/html" id="tmpl-repeater-ui-<?php echo $unique_slug; ?>">
            <div class="bwfan-input-form clearfix gs-repeater-fields">
                <div class="bwfan-col-sm-3 bwfan-pl-0">
                    <select data-element-type="bwfan-select" data-parent-groupid="{{data.action_id}}" data-parent-slug="<?php echo $unique_slug; ?>" required
                            class="bwfan-field-select bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][worksheet_data][column][{{data.index}}]">
                        <option value=""><?php echo __( 'Column', 'autonami-automations-connectors' ); ?></option>
                        <#
                        if(_.has(data.actionFieldsOptions, 'worksheet_column_options') && _.isObject(data.actionFieldsOptions.worksheet_column_options) ) {
                        _.each( data.actionFieldsOptions.worksheet_column_options, function( value, key ){
                        #>
                        <option value="{{key}}">{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
                <div class="bwfan-col-sm-8 bwfan-p-0">
                    <input required type="text" placeholder="Value" class="bwfan-input-wrapper bwfan-input-merge-tags" value="" name="bwfan[{{data.action_id}}][data][worksheet_data][value][{{data.index}}]"/>
                </div>
                <div class="bwfan-col-sm-1 bwfan-pr-0">
                    <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                </div>
            </div>
        </script>

        <script type="text/html" id="tmpl-action-<?php echo $unique_slug; ?>">
            <#
            spreadsheet_id = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'spreadsheet_id')) ? data.actionSavedData.data.spreadsheet_id : '';
            worksheet_title = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'worksheet_title')) ? data.actionSavedData.data.worksheet_title : '';
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php echo __( 'Enter Google SpreadSheet ID', 'autonami-automations-connectors' ); ?></label>
                <div class="bwfan-col-sm-9 bwfan-pl-0">
                    <input required type="text" value="{{spreadsheet_id}}" class="bwfan-input-wrapper wfco_gs_get_spreadsheet_id" name="bwfan[{{data.action_id}}][data][spreadsheet_id]"/>
                </div>
                <div class="bwfan-col-sm-3 bwfan-pl-0">
                    <a href="#" class="button wfco_<?php echo $unique_slug; ?>_get_worksheets"><?php echo __( 'Get Sheets', 'autonami-automations-connectors' ); ?></a>
                </div>
            </div>

			<# if('' != spreadsheet_id && _.has(data.actionSavedData, 'ajax-data') && '<?php echo $unique_slug; ?>'===data.actionSavedData.action_slug){ #>
            <input type="hidden" name="bwfan[{{data.action_id}}][ajax-data]" value="{{JSON.stringify(data.actionSavedData['ajax-data'])}}" />
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php
					echo __( 'Select Worksheet', 'autonami-automations-connectors' );
					$message = __( 'Select work sheet where data needs to insert.', 'autonami-automations-connectors' );
					echo $this->add_description( $message );
					?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0">
                    <select class="bwfan-input-wrapper" id="wfco_select_worksheet" name="bwfan[{{data.action_id}}][data][worksheet_title]">
                        <#
                        if(_.has(data.actionSavedData, 'ajax-data') && _.has(data.actionSavedData['ajax-data'], 'worksheet_title_options') &&
                        _.isObject(data.actionSavedData['ajax-data'].worksheet_title_options) ) {
                        _.each( data.actionSavedData['ajax-data'].worksheet_title_options, function( value, key ){
                        selected = (value == worksheet_title) ? 'selected' : '';
                        #>
                        <option value="{{value}}" {{selected}}>{{value}}</option>
                        <# })
                        }
                        #>
                    </select>
                </div>
            </div>

            <label for="" class="bwfan-label-title">
				<?php
				echo __( 'Data', 'autonami-automations-connectors' );
				$message = __( 'Add the data against the columns.', 'autonami-automations-connectors' );
				echo $this->add_description( $message, 'l', 'right' );
				echo $this->inline_merge_tag_invoke();
				?>
            </label>

            <div class="clearfix bwfan-input-repeater">
                <#
                repeaterArr = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'worksheet_data')) ? data.actionSavedData.data.worksheet_data : {};
                repeaterCount = _.size(repeaterArr.column);
                if(repeaterCount == 0) {
                repeaterArr = {column:{0:''}, value:{0:''}};
                }

                if(repeaterCount >= 0) {
                h=0;
                _.each( repeaterArr.column, function( value, key ){
                #>
                <div class="bwfan-input-form clearfix gs-repeater-fields">
                    <div class="bwfan-col-sm-3 bwfan-pl-0">
                        <select data-element-type="bwfan-select" data-parent-groupid="{{data.action_id}}" data-parent-slug="<?php echo $unique_slug; ?>" required
                                class="bwfan-field-select bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][worksheet_data][column][{{h}}]">
                            <option value=""><?php echo __( 'Column', 'autonami-automations-connectors' ); ?></option>
                            <#
                            if(_.has(data.actionFieldsOptions, 'worksheet_column_options') && _.isObject(data.actionFieldsOptions.worksheet_column_options) ) {
                            _.each( data.actionFieldsOptions.worksheet_column_options, function( column_option_value, column_option_key ){
                            selected = (column_option_key == value) ? 'selected' : '';
                            #>
                            <option value="{{column_option_key}}" {{selected}}>{{column_option_value}}</option>
                            <# })
                            }
                            #>
                        </select>
                    </div>
                    <div class="bwfan-col-sm-8 bwfan-p-0">
                        <input required type="text" placeholder="Value" class="bwfan-input-wrapper bwfan-input-merge-tags" value="{{repeaterArr.value[key]}}" name="bwfan[{{data.action_id}}][data][worksheet_data][value][{{h}}]"/>
                    </div>
                    <div class="bwfan-col-sm-1 bwfan-pr-0">
                        <span class="bwfan-remove-repeater-field" data-groupid="{{data.action_id}}">&#10006;</span>
                    </div>
                </div>
                <# h++;
                });
                }
                repeaterCount = repeaterCount + 1;
                #>
            </div>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-mb10 bwfan_mt10">
                <a href="#" class="bwfan-add-repeater-data bwfan-repeater-ui-<?php echo $unique_slug; ?>" data-groupid="{{data.action_id}}" data-count="{{repeaterCount}}"><i class="dashicons dashicons-plus-alt"></i></a>
            </div>
            <# } #>
        </script>

        <script>
            jQuery(document).ready(function ($) {
                /** Remove repeated UI */
                $('body').on('click', 'span.bwfan-remove-repeater-field', function (event) {
                    event.preventDefault();
                    $(this).closest('.gs-repeater-fields').remove();
                });

                /** Generate repeater UI by calling script template */
                $('body').on('click', '.bwfan-repeater-ui-<?php echo $unique_slug; ?>', function (event) {
                    event.preventDefault();
                    var $this = $(this);
                    var index = Number($this.attr('data-count'));
                    var action_id = $this.attr('data-groupid');
                    var template = wp.template('repeater-ui-<?php echo $unique_slug; ?>');
                    var actionFieldsOptions = {
                        worksheet_column_options: bwfan_set_actions_js_data['<?php echo $this->get_class_slug(); ?>']['worksheet_column_options']
                    };

                    $this.parent().parent().find('.bwfan-input-repeater').append(template({action_id: action_id, index: index, actionFieldsOptions: actionFieldsOptions}));
                    index = index + 1;
                    $this.attr('data-count', index);
                });

                /** Fetch all worksheets of a given spreadsheet id */
                $('body').on('click', '.wfco_<?php echo $unique_slug; ?>_get_worksheets', function (event) {
                    event.preventDefault();
                    var $this = $(this);
                    var spreadsheet_id = $this.parent().parent().find('.wfco_gs_get_spreadsheet_id').val();
                    var error_generated;

                    /** Check if spreadsheet id is provided or not */
                    if (typeof spreadsheet_id == 'undefined' || '' === spreadsheet_id) {

                        error_generated = $this.parent().parent().parent().find('div.error.wfco_error').html();
                        if ('undefined' == typeof error_generated) {
                            $this.parent().parent().after("<div class='error wfco_error'></div>");
                        }

                        $('.error.wfco_error').fadeIn().html('<?php echo __( 'Please enter spreadsheet ID', 'autonami-automations-connectors' ); ?>');
                        setTimeout(function () {
                            $('.error.wfco_error').fadeOut("slow");
                        }, 2500);

                        return;
                    }

                    $this.addClass('wfco_loading');
                    $this.parent().parent().after('<img style="padding:4px 0 0 5px" class="wp_spinner_gif" src="<?php echo includes_url() . 'images/spinner.gif'; ?>">');
                    var worksheet_title = $this.closest('#bwfan-actions-form').find('#wfco_select_worksheet').val();
                    if (typeof worksheet_title == 'undefined' || worksheet_title == null) {
                        worksheet_title = 0;
                    }

                    /** Ajax call to fetch all worksheets of spreadsheet  */
                    $.ajax({
                        url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                        type: "POST",
                        data: {
                            'action': 'wfco_gs_get_worksheets',
                            'id': spreadsheet_id,
                            '_wpnonce': bwfanParams.ajax_nonce
                        },
                        success: function (result) {
                            $this.removeClass('wfco_loading');
                            $('.wp_spinner_gif').remove();

                            if (0 == result.success) {
                                error_generated = $this.parent().parent().parent().find('div.error.wfco_error').html();
                                if ('undefined' == typeof error_generated) {
                                    $this.parent().parent().after("<div class='error wfco_error'></div>");
                                }

                                $('.error.wfco_error').fadeIn().html(result.result);
                                setTimeout(function () {
                                    $('.error.wfco_error').fadeOut("slow");
                                }, 2500);

                                return;
                            }

                            var worksheets = {};
                            $.each(result.result, function (key, value) {
                                worksheets[value] = value;
                            });

                            var data_values = {worksheet_title: worksheet_title, spreadsheet_id: spreadsheet_id};
                            var data_options = {worksheet_title_options: worksheets};

                            BWFAN_Actions.recreate_action_ui_ajax($('#bwfan-actions-form'), data_values, data_options);
                        }
                    });
                });
            });
        </script>
		<?php
	}

	public function make_data( $integration_object, $task_meta ) {
		$data_to_set                    = array();
		$data_to_set['spreadsheet_id']  = $task_meta['data']['spreadsheet_id'];
		$data_to_set['worksheet_title'] = $task_meta['data']['worksheet_title'];
		$this->add_action();
		foreach ( $task_meta['data']['worksheet_data']['column'] as $key => $value ) {
			$data_to_set['worksheet_data'][ $value ] = BWFAN_Common::decode_merge_tags( $task_meta['data']['worksheet_data']['value'][ $key ] );
		}
		$this->remove_action();

		return $data_to_set;
	}

	private function add_action() {
		add_filter( 'bwfan_order_billing_address_separator', [ $this, 'change_br_to_slash_n' ] );
		add_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	private function remove_action() {
		remove_filter( 'bwfan_order_billing_address_params', [ $this, 'change_br_to_slash_n' ] );
		remove_filter( 'bwfan_order_shipping_address_separator', [ $this, 'change_br_to_slash_n' ] );
	}

	public function execute_action( $action_data ) {
		$load_connector = WFCO_Load_Connectors::get_instance();
		$call           = $load_connector->get_call( 'wfco_gs_insert_data' );
		if ( is_null( $call ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'No insert call found', 'autonami-automations-connectors' ),
			);
		}

		$call->set_data( $action_data['processed_data'] );
		$result = $call->process();

		if ( false === $result ) {
			return array(
				'status'  => 4,
				'message' => __( 'No client call found', 'autonami-automations-connectors' ),
			);
		}

		if ( true === $result ) {
			return array(
				'status'  => 3,
				'message' => __( 'Data successfully added in the google spreadsheet.', 'autonami-automations-connectors' ),
			);
		}

		$codes = BWFAN_Google_Sheets_Integration::get_permanent_failure_error_codes();
		if ( in_array( $result[0], $codes ) ) {
			if ( 403 == $result[0] && strpos( $result[1], 'Limit' ) !== false ) {
				return array(
					'status'  => 0,
					'message' => $result[1],
				);
			}

			return array(
				'status'  => 4,
				'message' => $result[1],
			);
		}

		return array(
			'status'  => 0,
			'message' => $result[1],
		);
	}

	public function change_br_to_slash_n( $params ) {
		return "\n";
	}

}

return 'BWFAN_GS_Insert_Data';
