<?php
$text_start_when       = __( 'START WHEN', 'wp-marketing-automations' );
$text_select_trigger   = __( 'Select An Event', 'wp-marketing-automations' );
$text_select_condition = __( 'Select Conditions', 'wp-marketing-automations' );
$text_what_next        = __( 'WHAT\'S NEXT?', 'wp-marketing-automations' );
$text_add_condition    = __( 'Add Condition', 'wp-marketing-automations' );
$text_add_action       = __( 'Add Action', 'wp-marketing-automations' );
$text_select_action    = __( 'Select Action', 'wp-marketing-automations' );
$text_if               = __( 'IF', 'wp-marketing-automations' );
$text_yes              = __( 'YES', 'wp-marketing-automations' );
$text_no               = __( 'NO', 'wp-marketing-automations' );
$text_then             = __( 'THEN', 'wp-marketing-automations' );
$text_end              = __( 'END', 'wp-marketing-automations' );

$hard_array = array(
	'select_action'    => $text_select_action,
	'select_condition' => $text_select_condition,
);
?>
    <script>
        var bwfan_hard_texts = <?php echo wp_json_encode( $hard_array ); //phpcs:ignore WordPress.Security.EscapeOutput ?>;
    </script>

    <!-- Add trigger template -->
    <script type="text/html" id="tmpl-add_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_add_trigger">
							<?php esc_html_e( $text_select_trigger ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Select trigger template -->
    <script type="text/html" id="tmpl-select_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_trigger">
							<?php esc_html_e( $text_select_trigger ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Selected trigger template -->
    <script type="text/html" id="tmpl-selected_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_trigger">
                            <div class="bwfan_name_wrap">
                                <div class="bwfan_small_name">{{data.trigger_name.integration}}</div>
                                {{data.trigger_name.action}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add"><i class="dashicons dashicons-plus"/></div>
            </div>
        </div>
    </script>

    <!-- Select condition template -->
    <script type="text/html" id="tmpl-select_condition">
        <div class="workflow_item workflow_type_condition" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_if ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_condition item_condition_default">
							<?php esc_html_e( $text_select_condition ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert bwfan_no_select"><?php esc_html_e( $text_yes ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_then ); ?></div>
                    <div class="item_wrap_conditions">
                        <# _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        var timerHtml = '';
                        if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                        timerHtml = '<i class="dashicons dashicons-clock"></i>';
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="action_text">
                                <# if(_.has(value, 'action_name') == true) { #>
                                <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                {{{timerHtml}}}{{value.action_name.action}}
                                <# } else { print(bwfan_hard_texts.select_action) }
                                #>
                            </div>
                            <div class="item_actions"><i class="dashicons dashicons-admin-generic"></i></div>
                            <div class="item_actions_list">
                                <ul>
                                    <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                    <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action"><i class="dashicons dashicons-plus"></i><?php esc_html_e( $text_add_action ); ?></div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert item_wrap_html_right bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
        </div>
    </script>

    <!-- Selected condition template -->
    <script type="text/html" id="tmpl-selected_condition">
        <div class="workflow_item workflow_type_condition" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_if ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_condition {{('' == data.rulesHtml) ? 'item_condition_default' : ''}}">
                            <# print(data.rulesHtml); #>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert bwfan_no_select"><?php esc_html_e( $text_yes ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_then ); ?></div>
                    <div class="item_wrap_conditions">
                        <# _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        var timerHtml = '';
                        if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                        timerHtml = '<i class="dashicons dashicons-clock"></i>';
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="action_text">
                                <# if(_.has(value, 'action_name') == true) { #>
                                <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                {{{timerHtml}}}{{value.action_name.action}}
                                <# } else { print(bwfan_hard_texts.select_action) }
                                #>
                            </div>
                            <div class="item_actions"><i class="dashicons dashicons-admin-generic"></i></div>
                            <div class="item_actions_list">
                                <ul>
                                    <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                    <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action"><i class="dashicons dashicons-plus"></i><?php esc_html_e( $text_add_action ); ?></div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert item_wrap_html_right bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
        </div>
    </script>

    <!-- End html template -->
    <script type="text/html" id="tmpl-end_html">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
            </div>
        </div>
    </script>

    <!-- Vertical line gap template -->
    <script type="text/html" id="tmpl-vertical_line_gap">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
    </script>

    <!-- No html template -->
    <script type="text/html" id="tmpl-no_html">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html bwfan_no_select"><?php esc_html_e( $text_no ); ?></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add"><i class="dashicons dashicons-plus"/></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
    </script>

    <!-- Add block template -->
    <script type="text/html" id="tmpl-add_block">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add"><i class="dashicons dashicons-plus"/></div>
            </div>
        </div>
    </script>

    <!-- Select action template -->
    <script type="text/html" id="tmpl-select_action">
        <div class="item_wrap_single item_modify_action" data-action="{{data.action_id}}">
            <div class="action_text"><?php esc_html_e( $text_select_action ); ?></div>
            <div class="item_actions bwfan_hide_hard"><i class="dashicons dashicons-admin-generic"></i></div>
            <div class="item_actions_list">
                <ul>
                    <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                    <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                </ul>
            </div>
        </div>
    </script>

    <!-- Add action template -->
    <script type="text/html" id="tmpl-add_action">
        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
        <# } #>
        <div class="item_wrap_single item_add_action"><i class="dashicons dashicons-plus"></i><?php esc_html_e( $text_add_action ); ?></div>
    </script>

    <!-- Action only template -->
    <script type="text/html" id="tmpl-action_only">
        <div class="workflow_item workflow_type_action" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_then ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <# _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        var timerHtml = '';
                        if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                        timerHtml = '<i class="dashicons dashicons-clock"></i>';
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="action_text">
                                <# if(_.has(value, 'action_name') == true) { #>
                                <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                {{{timerHtml}}}{{value.action_name.action}}
                                <# } else { print(bwfan_hard_texts.select_action) }
                                #>
                            </div>
                            <div class="item_actions"><i class="dashicons dashicons-admin-generic"></i></div>
                            <div class="item_actions_list">
                                <ul>
                                    <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                    <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action"><i class="dashicons dashicons-plus"></i><?php esc_html_e( $text_add_action ); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </script>


    <!-- Sidebar views -->

    <!-- Condition Rule selection -->
    <script type="text/html" id="tmpl-rule_data">
        <div class="automation_data" data-type="condition" data-node="{{data.group_id}}">
            <p>Modify Rules data here</p>
            <a class="button button-primary item_assign_manual_rules_data" href="javascript:void(0)">Add manual rules</a>
        </div>
    </script>

    <script type="text/html" id="tmpl-single-action-html">
        <div class="item_wrap_single item_show_close bwfan-select-action" data-group-id="{{data.group_id}}" data-filled="0">
            Select Action
        </div>
    </script>

    <script type="text/html" id="tmpl-bwfan-events-form-container">
        <form action="" method="post" data-type="events" id="bwfan-events-form-container">
            <div id="bwfan-events-form" class="bwfan-right-content-container">

            </div>
            <input type="submit" value="<?php esc_html_e( 'Save Event', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/html" id="tmpl-bwfan-actions-form-container">
        <form action="" method="post" data-type="actions" id="bwfan-actions-form-container">
            <div id="bwfan-actions-form" class="bwfan-right-content-container">

            </div>
            <input type="hidden" id="bwfan_group_id" name="bwfan_group_id"/>
            <input type="hidden" id="bwfan_action_id" name="bwfan_action_id"/>
            <input type="hidden" id="bwfan_temp_action_slug" name="bwfan_temp_action_slug"/>
            <input type="submit" value="<?php esc_html_e( 'Save Action', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/html" id="tmpl-bwfan-condition-form-container">
        <form action="" method="post" data-type="rules" id="bwfan-condition-form-container" class="bwfan_rules_form">
            <div id="bwfan-condition-form" class="bwfan-rules-builder bwfan-right-content-container">

            </div>
            <div class="rules-basic-actions-wrapper">
                <button class="button  bwfan-add-rule-group">Add another condition</button>
            </div>
            <input type="hidden" id="bwfan_action_id" name="bwfan_action_id"/>
            <input type="hidden" id="bwfan_group_id" name="bwfan_group_id" value="{{data.groupid}}"/>
            <input type="submit" value="<?php esc_html_e( 'Save Rules', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/template" id="bwfan-rule-template">
		<?php include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/views/metabox-rules-rule-template-basic.php'; ?>
    </script>

    <script type="text/html" id="tmpl-bwfan-sidebar-top">
        <#
        heading = '';
        desc = '';
        if(_.has(data.head,'integration') == true) {
        heading = data.head.integration + ': ' + data.head.action;
        } else if(_.has(data,'head') == true && data.head != '') {
        heading = data.head;
        }
        #>
        <div class="wr_tw_h">{{heading}}</div>
        <div class="wr_tw_d"><# (_.has(data,'desc') == true && data.desc != '') ? print(data.desc) : '' #></div>
    </script>

    <script type="text/html" id="tmpl-bwfan-no-action-found">
        <p><?php esc_html_e( 'Selected action is not present.', 'wp-marketing-automations' ); ?></p>
    </script>

    <script type="text/html" id="tmpl-bwfan-tags-select-html">
        <# _.each( data.tags, function( value, key ){ #>
        <option selected>{{value}}</option>
        <# }); #>
    </script>

    <script type="text/html" id="tmpl-bwfan-tags-cancel-ui">
        <# _.each( data.tags, function( value, key ){
        var tagVal = (_.has(data.tagNames,value)) ? data.tagNames[value] : value;
        #>
        <li data-key="{{value}}">
            <button type="button" class="ntdelbutton">
                <span class="remove-tag-icon" aria-hidden="true"></span>
            </button>
            {{tagVal}}
        </li>
        <# }); #>
    </script>

    <script type="text/html" id="tmpl-bwfan-copied-action">
        <i class="dashicons dashicons-admin-page"></i>
        <div class="bwfan_discard_blue_font"><?php esc_html_e( 'You have a copied action', 'wp-marketing-automations' ); ?></div>
        <div class="bwfan_discard_grey_font">({{data.integration}} > {{data.action}})</div>
        <a href="javascript:void(0)" class="bwfan_discard_btn_cross"><i class="dashicons dashicons-no-alt"></i> <?php esc_html_e( 'Discard', 'wp-marketing-automations' ); ?></a>
    </script>
<?php
