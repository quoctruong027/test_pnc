<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$global_settings        = BWFAN_Common::get_global_settings();
$sandbox_mode           = ( isset( $global_settings['bwfan_sandbox_mode'] ) && 1 === intval( $global_settings['bwfan_sandbox_mode'] ) ) ? 'checked' : '';
$make_logs              = ( isset( $global_settings['bwfan_make_logs'] ) && 1 === intval( $global_settings['bwfan_make_logs'] ) ) ? 'checked' : '';
$selected_email_service = ( isset( $global_settings['bwfan_email_service'] ) ) ? $global_settings['bwfan_email_service'] : null;
$bitly_url_shortner     = ( isset( $global_settings['bitly_url_shortner'] ) ) ? true : false;
$email_services         = [];
$email_services         = apply_filters( 'bwfan_email_services', $email_services );
$url_services           = apply_filters( 'bwfan_url_services', array() );
$autonami_integrations  = BWFAN_Core()->integration->get_integrations();
$all_events             = BWFAN_Load_Sources::$all_events;
$global_email_settings  = BWFAN_Common::get_global_email_settings();

$menu_arr = array(
	'abandonment' => array(
		'label' => __( 'Carts', 'wp-marketing-automations' ),
		'class' => 'current',
	),
	'unsubscribe' => array(
		'label' => __( 'OptIn', 'wp-marketing-automations' ),
	),
	'other'       => array(
		'label' => __( 'Advanced', 'wp-marketing-automations' ),
	),
	'tools'       => array(
		'label' => __( 'Tools', 'wp-marketing-automations' ),
	),
);
$menu_arr = apply_filters( 'bwfan_global_setting_menu_arr', $menu_arr, $global_settings );

?>
<div class="wrap bwfan_global bwfan_global_settings">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div class=" bwfan_global_settings_wrap">
        <div id="bwfan_global_setting_vue">
            <div class="bwfan-product-widget-tabs">
                <div class="bwfan-product-widget-container">
                    <div class="bwfan-product-tabs bwfan-tabs-style-line" role="tablist">
                        <div class="wp-filter">
                            <ul class="filter-links bwfan_focus_tabs">
								<?php
								foreach ( $menu_arr as $key => $menu ) {
									$class = ( isset( $menu['class'] ) && ! empty( $menu['class'] ) ) ? sanitize_text_field( $menu['class'] ) : '';
									printf( "<li class='bwfan-tab-title %s' id='tab-%s'><a href='javascript:void(0)'>%s</a></li>", esc_attr( $class ), esc_attr( $key ), esc_html( $menu['label'] ) );
								}
								?>
                            </ul>
                        </div>
                        <div class="bwfan-product-tabs-content-wrapper">
                            <div class="bwfan_global_setting_inner" id="bwfan_global_setting">
                                <form class="bwfan_forms_wrap bwfan_forms_global_settings" data-bwf-action="global_settings_save">
                                    <fieldset class="bwfan-tab-content" setting-id="tab-unsubscribe">
										<?php
										if ( class_exists( 'WooCommerce' ) ) {
											?>
                                            <h3><?php echo esc_html__( 'OptIn Settings', 'wp-marketing-automations' ); ?></h3>
                                            <div class="form-group field-input">
                                                <label><?php esc_html_e( 'Enable Optin For Marketing Emails', 'wp-marketing-automations' ); ?></label>
                                                <div class="field-wrap">
                                                    <div class="wrapper">
                                                        <input class="bwfan_user_consent" type="checkbox" name="bwfan_user_consent" value="1" <?php echo empty( $global_settings['bwfan_user_consent'] ) ? '' : 'checked'; ?> />
                                                        <span class=""><?php esc_html_e( 'Show an opt in at checkout to ask for the consent of marketing emails.', 'wp-marketing-automations' ); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bwfan_user_consent_html <?php echo empty( $global_settings['bwfan_user_consent'] ) ? 'bwfan_hide' : ''; ?>">
                                                <div class="form-group field-input">
                                                    <label><?php esc_html_e( 'Optin Text', 'wp-marketing-automations' ); ?></label>
                                                    <div class="field-wrap">
                                                        <div class="wrapper">
                                                            <textarea cols="40" rows="3" name="bwfan_user_consent_message"><?php echo $global_settings['bwfan_user_consent_message'];//phpcs:ignore WordPress.Security.EscapeOutput
	                                                            ?></textarea>
                                                        </div>
                                                        <span class="hint"><?php esc_html_e( 'Customize the description for marketing consent optin at checkout.', 'wp-marketing-automations' ); ?></span>
                                                    </div>
                                                </div>
                                                <div class="form-group field-input">
                                                    <label><?php esc_html_e( 'Optin Position', 'wp-marketing-automations' ); ?></label>
                                                    <div class="field-wrap">
                                                        <div class="wrapper">
                                                            <select name="bwfan_user_consent_position">
                                                                <?php $selected_position = isset($global_settings['bwfan_user_consent_position']) && !empty($global_settings['bwfan_user_consent_position']) ? $global_settings['bwfan_user_consent_position'] : ''; ?>
                                                                <option value="below_term" <?php echo $selected_position==="below_term"?'selected':''; ?> >Below Terms & Condition</option>
                                                                <option value="below_email" <?php echo $selected_position==="below_email"?'selected':'';?> >Below Email Field</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group field-input">
                                                    <label><?php esc_html_e( 'Optin Behavior for EU Customers', 'wp-marketing-automations' ); ?></label>
                                                    <div class="field-wrap">
                                                        <div class="wrapper">
                                                            <select name="bwfan_user_consent_eu">
                                                                <option value="1" <?php echo empty( $global_settings['bwfan_user_consent_eu'] ) ? '' : 'selected'; ?>><?php esc_html_e( 'Checked', 'wp-marketing-automations' ); ?></option>
                                                                <option value="0" <?php echo empty( $global_settings['bwfan_user_consent_eu'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Unchecked', 'wp-marketing-automations' ); ?></option>
                                                            </select>
                                                        </div>
                                                        <span class="hint"><?php esc_html_e( 'To respect GPDR it is recommended to keep it unchecked.', 'wp-marketing-automations' ); ?></span>
                                                    </div>
                                                </div>
                                                <div class="form-group field-input">
                                                    <label><?php esc_html_e( 'Optin Behavior for Non-EU Customers', 'wp-marketing-automations' ); ?></label>
                                                    <div class="field-wrap">
                                                        <div class="wrapper">
                                                            <select name="bwfan_user_consent_non_eu">
                                                                <option value="1" <?php echo empty( $global_settings['bwfan_user_consent_non_eu'] ) ? '' : 'selected'; ?>><?php esc_html_e( 'Checked', 'wp-marketing-automations' ); ?></option>
                                                                <option value="0" <?php echo empty( $global_settings['bwfan_user_consent_non_eu'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Unchecked', 'wp-marketing-automations' ); ?></option>
                                                            </select>
                                                        </div>
                                                        <span class="hint"><?php esc_html_e( 'To respect GPDR it is recommended to keep it unchecked.', 'wp-marketing-automations' ); ?></span>
                                                    </div>
                                                </div>
                                            </div>
											<?php
										}
										?>
                                        <h3><?php echo esc_html__( 'Unsubscribe Settings', 'wp-marketing-automations' ); ?></h3>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Select Unsubscribe Page', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <select required data-search-text="<?php esc_html_e( 'Select Page', 'wp-marketing-automations' ); ?>" class="bwfan-select-unsubscribe-page" name="bwfan_unsubscribe_page">
                                                        <option value=""><?php esc_html_e( 'Choose Page', 'wp-marketing-automations' ); ?></option>
														<?php
														if ( isset( $global_settings['bwfan_unsubscribe_page'] ) && ! empty( $global_settings['bwfan_unsubscribe_page'] ) ) {
															$page  = absint( $global_settings['bwfan_unsubscribe_page'] );
															$title = get_the_title( $page );
															echo '<option value="' . esc_html( $page ) . '" selected>' . esc_html( $title ) . '</option>';
														}
														?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Unsubscribe Button', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" value="[bwfan_unsubscribe_button]" readonly onclick="select()"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Displays unsubscribe button. Use attribute "label" to add custom text: [bwfan_unsubscribe_button label="Custom Text"]', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Subscriber Recipient', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" value="[bwfan_subscriber_recipient]" readonly onclick="select()"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Displays subscriber email or phone number.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Subscriber Name', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" value="[bwfan_subscriber_name]" readonly onclick="select()"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Displays subscriber name.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Unsubscribe Button Label In Email', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" name="bwfan_unsubscribe_email_label" value="<?php echo ( isset( $global_settings['bwfan_unsubscribe_email_label'] ) && ! empty( $global_settings['bwfan_unsubscribe_email_label'] ) ) ? esc_html( $global_settings['bwfan_unsubscribe_email_label'] ) : 'Unsubscribe'; ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Text displayed in email for unsubscribe.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Error message on unsubscribe button', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" name="bwfan_unsubscribe_data_error" value="<?php echo ( isset( $global_settings['bwfan_unsubscribe_data_error'] ) && ! empty( $global_settings['bwfan_unsubscribe_data_error'] ) ) ? esc_html( $global_settings['bwfan_unsubscribe_data_error'] ) : 'Invalid Data'; ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Error message displayed when invalid data passed while unsubscribing.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Success message on unsubscribe button', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" name="bwfan_unsubscribe_data_success" value="<?php echo ( isset( $global_settings['bwfan_unsubscribe_data_success'] ) && ! empty( $global_settings['bwfan_unsubscribe_data_success'] ) ) ? esc_html( $global_settings['bwfan_unsubscribe_data_success'] ) : 'Success'; ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Success message displayed when user is successfully unsubscribed.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <fieldset class="bwfan-tab-content" setting-id="tab-other">
                                        <h3><?php esc_html_e( 'Email', 'wp-marketing-automations' ); ?></h3>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( '"From" Name', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" placeholder="'From' Name" name="bwfan_email_from_name" value="<?php esc_attr_e( $global_email_settings['bwfan_email_from_name'] ); ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_attr_e( 'Name that will appear in email sent.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( '"From" Address', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" placeholder="'From' Email Address" name="bwfan_email_from" value="<?php echo sanitize_email( $global_email_settings['bwfan_email_from'] ); ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_attr_e( 'Email address from the email will be sent from.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( '"Reply To" Address', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="text" placeholder="'Reply To' Address" name="bwfan_email_reply_to" value="<?php echo sanitize_email( $global_email_settings['bwfan_email_reply_to'] ); ?>"/>
                                                </div>
                                                <span class="hint"><?php esc_attr_e( 'Email address where user\'s reply will be sent to.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
                                        <h3><?php esc_html_e( 'Advanced', 'wp-marketing-automations' ); ?></h3>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Enable Sandbox mode', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="checkbox" name="bwfan_sandbox_mode" value="1" <?php echo esc_html( $sandbox_mode ); ?>/>
                                                    <span class=""><?php esc_html_e( 'Put Autonami mode in sandbox mode.', 'wp-marketing-automations' ); ?></span>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'In sandbox mode Automations won\'t create new Tasks and existing Tasks won\'t run.', 'wp-marketing-automations' ); ?></span>
                                            </div>
                                        </div>
										<?php
										if ( count( $email_services ) > 1 ) {
											?>
                                            <div class="form-group field-input">
                                                <label><?php esc_html_e( 'Select Email Service', 'wp-marketing-automations' ); ?></label>
                                                <div class="field-wrap">
                                                    <div class="wrapper">
                                                        <select name="bwfan_email_service" class="bwfan-input-wrapper">
															<?php
															foreach ( $email_services as $service ) {
																$integration = BWFAN_Core()->integration->get_integration( $service );
																if ( is_null( $integration ) ) {
																	continue;
																}
																$service_name     = $integration->get_name();
																$selected_service = ( ! is_null( $selected_email_service ) && $selected_email_service === $service ) ? 'selected' : '';
																echo '<option value="' . esc_attr( $service ) . '" ' . esc_attr( $selected_service ) . '>' . esc_html( $service_name ) . '</option>';
															}
															?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <span class="hint"><?php esc_html_e( 'Emails will be sent via selected service', 'wp-marketing-automations' ); ?></span>
                                            </div>
											<?php
										} else {
											echo '<input type="hidden" name="bwfan_email_service" value="' . esc_html__( $email_services[0] ) . '">';
										}
										?>
                                        <div class="form-group field-input">
                                            <label><?php esc_html_e( 'Enable Logging', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap">
                                                <div class="wrapper">
                                                    <input type="checkbox" name="bwfan_make_logs" value="1" <?php echo esc_html( $make_logs ); ?>/>
                                                    <span class=""><?php esc_html_e( 'A tool for debugging for devs.', 'wp-marketing-automations' ); ?></span>
                                                </div>
                                            </div>
                                        </div>
										<?php
										if ( class_exists( 'WooCommerce' ) ) {
											?>
                                            <div class="form-group field-input">
                                                <label><?php esc_html_e( 'Delete Autonami generated coupons after expiry', 'wp-marketing-automations' ); ?></label>
                                                <div class="field-wrap">
                                                    <div class="wrapper">
                                                        <input type="number" placeholder="xx days" name="bwfan_delete_autonami_generated_coupons_time" value="<?php esc_attr_e( $global_settings['bwfan_delete_autonami_generated_coupons_time'] ); ?>" onclick="select()" min="0" max="30"/>
                                                    </div>
                                                    <span class="hint"><?php esc_attr_e( 'Delete Autonami generated coupons after xx days of expiry (Max 30 days).', 'wp-marketing-automations' ); ?></span>
                                                </div>
                                            </div>
											<?php
										}
										?>
										<?php /*<div class="form-group field-input">
                                            <label><?php esc_html_e( 'Do not run following events', 'wp-marketing-automations' ); ?></label>
                                            <div class="field-wrap field-inline-checkbox">
												<?php
												foreach ( $all_events as $opt_group => $event ) {
													echo '<label>' . esc_html( $opt_group ) . '</label>';
													echo '<div class="bwfan_clear"></div>';
													foreach ( $event as $key => $value ) {
														$checked_event = '';
														if ( isset( $global_settings[ 'bwfan_stop_event_' . $key ] ) && ! empty( $global_settings[ 'bwfan_stop_event_' . $key ] ) ) {
															$checked_event = 'checked';
														}

														echo '<div class="inline-checkbox-wrap">';
														echo '<label for="bwfan_stop_event_' . esc_attr( $key ) . '"><input type="checkbox" id="bwfan_stop_event_' . esc_attr( $key ) . '" value="1" name="bwfan_stop_event_' . esc_attr( $key ) . '" value="1" ' . esc_attr( $checked_event ) . '/>' . esc_html( $value ) . '</label>';
														echo '</div>';
													}
												}
												?>
                                            </div>
                                        </div>*/ ?>
                                    </fieldset>
                                    <fieldset class="bwfan-tab-content" setting-id="tab-tools">
                                        <table class="bwfan_status_table bwfan_status_table--tools widefat bwfan_tools_table" cellspacing="0">
                                            <tbody class="tools">
                                            <tr class="">
                                                <th>
                                                    <label><?php esc_html_e( 'Run All Queued Tasks', 'wp-marketing-automations' ); ?></label>
                                                    <p class="description"><?php esc_html_e( 'This will schedule all the queued tasks to run now', 'wp-marketing-automations' ); ?></p>
                                                </th>
                                                <td class="run-tool bwfan_body">
                                                    <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="run_all_tasks" data-inputs=""><?php esc_html_e( 'Schedule', 'wp-marketing-automations' ); ?></a>
                                                </td>
                                            </tr>
                                            <tr class="">
                                                <th>
                                                    <label><?php esc_html_e( 'Delete All Completed Tasks', 'wp-marketing-automations' ); ?></label>
                                                    <p class="description"><?php esc_html_e( 'This will schedule the deletion of all the so far completed tasks.', 'wp-marketing-automations' ); ?></p>
                                                </th>
                                                <td class="run-tool bwfan_body">
                                                    <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="delete_completed_tasks" data-inputs=""><?php esc_html_e( 'Delete', 'wp-marketing-automations' ); ?></a>
                                                </td>
                                            </tr>
                                            <tr class="">
                                                <th>
                                                    <label><?php esc_html_e( 'Delete All Failed Tasks', 'wp-marketing-automations' ); ?></label>
                                                    <p class="description"><?php esc_html_e( 'This will schedule the deletion of all the so far failed tasks.', 'wp-marketing-automations' ); ?></p>
                                                </th>
                                                <td class="run-tool bwfan_body">
                                                    <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="delete_failed_tasks" data-inputs=""><?php esc_html_e( 'Delete', 'wp-marketing-automations' ); ?></a>
                                                </td>
                                            </tr>
											<?php
											if ( class_exists( 'WooCommerce' ) ) {
												?>
                                                <tr class="">
                                                    <th>
                                                        <label><?php esc_html_e( 'Delete Autonami Generated Expired Coupons', 'wp-marketing-automations' ); ?></label>
                                                        <p class="description"><?php esc_html_e( 'This will schedule the deletion of all the expired coupons generated by Autonami', 'wp-marketing-automations' ); ?></p>
                                                    </th>
                                                    <td class="run-tool bwfan_body">
                                                        <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="delete_expired_coupons" data-inputs=""><?php esc_html_e( 'Delete', 'wp-marketing-automations' ); ?></a>
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th>
                                                        <label><?php esc_html_e( 'Delete Lost Carts', 'wp-marketing-automations' ); ?></label>
                                                        <p class="description"><?php esc_html_e( 'This will schedule the deletion of all lost carts in the system', 'wp-marketing-automations' ); ?></p>
                                                    </th>
                                                    <td class="run-tool bwfan_body">
                                                        <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="delete_lost_carts" data-inputs=""><?php esc_html_e( 'Delete', 'wp-marketing-automations' ); ?></a>
                                                    </td>
                                                </tr>
												<?php
											}
											?>
                                            <tr class="">
                                                <th>
                                                    <label><?php esc_html_e( 'Test Autonami Endpoints', 'wp-marketing-automations' ); ?></label>
                                                    <p class="description"><?php esc_html_e( 'This tool verifies Autonami endpoints', 'wp-marketing-automations' ); ?></p>
                                                </th>
                                                <td class="run-tool bwfan_body">
                                                    <a href="javascript:void(0);" class="bwfan_btn_blue bwfan_save_btn_style bwfan_run_tool" data-type="test_connection" data-inputs=""><?php esc_html_e( 'Run', 'wp-marketing-automations' ); ?></a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </fieldset>
									<?php
									do_action( 'bwfan_global_setting_page', $global_settings );
									?>
                                    <input type="submit" name="bwfan_global_settings_save" class="bwfan-display-none">
                                </form>
                                <div style="display: none" id="modal-global-settings_success" data-iziModal-icon="icon-home">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bwfan_form_button bwfan_body">
                <a href="javascript:void(0)" class="bwfan_btn_blue bwfan_save_btn_style bwfan_save_global_settings"><?php esc_html_e( 'Save Settings', 'wp-marketing-automations' ); ?></a>
            </div>
        </div>
        <div class="bwfan_clear"></div>
    </div>
</div>
