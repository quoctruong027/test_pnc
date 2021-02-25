<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$analytics                             = BWFAN_Abandoned_Cart_Analytics::get_instance();
$total_orders_placed                   = $analytics->get_total_orders_placed();
$total_carts_generated                 = $analytics->get_total_cart_generated();
$abandoned_carts                       = ( ( $total_carts_generated - $total_orders_placed ) < 0 ) ? 0 : ( $total_carts_generated - $total_orders_placed );
$captured_cart                         = $analytics->get_captured_cart();
$recovered_cart                        = $analytics->get_recovered_cart();
$lost_cart                             = $analytics->get_lost_cart();
$recovery_percentage                   = $analytics->get_recovery_rate( $captured_cart['count'], $recovered_cart['count'] );
$abandoned_cart_data                   = $analytics->line_chart_data( $captured_cart );
$abandoned_cart_data['data_1']         = $recovered_cart['data'];
$abandoned_cart_data['data_2']         = $lost_cart['data'];
$abandoned_cart_data['revenue_1']      = $recovered_cart['revenue'];
$abandoned_cart_data['line_label_1']   = __( 'Recoverable Cart', 'wp-marketing-automations' );
$abandoned_cart_data['line_label_2']   = __( 'Recovered Cart', 'wp-marketing-automations' );
$abandoned_cart_data['line_label_3']   = __( 'Lost Cart', 'wp-marketing-automations' );
$abandoned_cart_data['line_revenue_1'] = __( 'Potential Revenue', 'wp-marketing-automations' );
$abandoned_cart_data['line_revenue_2'] = __( 'Recovered Revenue', 'wp-marketing-automations' );

$global_settings = BWFAN_Common::get_global_settings();
$lost_time       = ( isset( $global_settings['bwfan_ab_mark_lost_cart'] ) ) ? $global_settings['bwfan_ab_mark_lost_cart'] : 15;
$lost_time       = absint( $lost_time );
$lost_time       = ( 1 === $lost_time ) ? $lost_time . ' day' : $lost_time . ' days';
?>
<script>
    var bwfan_abandoned_cart_data =<?php echo wp_json_encode( $abandoned_cart_data ); ?>;
</script>
<div class="bwfan_abandoned_filter" style="margin: 10px 0px">
	<?php
	$menus = $analytics->get_filter_menu();
	foreach ( $menus as $menu ) {
		$default_menu_class = ( isset( $menu['current'] ) ) ? 'bwfan_btn_round_active' : '';
		$class              = $menu['class'] . ' ' . $default_menu_class;
		echo '<a class="bwfan_btn_round_sec ' . esc_attr__( $class ) . '" href="' . $menu['link'] . '">' . esc_html__( $menu['name'] ) . '</a>'; //phpcs:ignore WordPress.Security.EscapeOutput
	}
	$show_range = ( $analytics->is_date_rage_set() ) ? 'bwfanc_show_date_range_search_form' : 'bwfanc_hide_date_range_search_form';
	?>
    <div class="wfacp_date_rage_container <?php esc_html_e( $show_range ); ?>">
        <form action="<?php esc_html_e( admin_url( 'admin.php' ) ); ?>">
            <input type="text" name="date_range_first" id="date_range_first" value="<?php esc_attr_e( $analytics->get_start_date() ); ?>" class="wfacp_date_range" autocomplete="off" required/>
            <input type="text" name="date_range_second" id="date_range_second" value="<?php esc_attr_e( $analytics->get_end_date() ); ?>" class="wfacp_date_range" autocomplete="off" required/>
            <input type="hidden" name="bwfanc_date_range_nonce" value="<?php esc_attr_e( wp_create_nonce( 'bwfanc_date_range_nonce' ) ); ?>"/>
            <input type="hidden" name="page" value="autonami"/>
            <input type="hidden" name="tab" value="carts"/>
            <input type="submit" class="button button-secondary" value="Submit"/>
        </form>
    </div>
</div>
<div class="bwfan_clear_10"></div>
<div class="bwfan_abcart_two-parts">
    <div class="bwfan_abcart_page-width">
        <div class="bwfan_abcart_part1">
            <div class="bwfan_abcart_row">
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Carts Initiated', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Total cart sessions started', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php esc_html_e( strval( $total_carts_generated ) ); ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Abandoned Carts', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Cart sessions that did not convert into orders', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php esc_html_e( strval( $abandoned_carts ) ); ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Recoverable Carts', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Abandoned cart sessions where emails were captured which can recover', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php esc_html_e( strval( $captured_cart['count'] ) ); ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Potential Revenue', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Sum total of all recoverable carts value', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php echo wc_price( $captured_cart['sum'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bwfan_abcart_clearfix"></div>
        </div>
        <div class="bwfan_abcart_part1">
            <div class="bwfan_abcart_row">
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Recovered Carts', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Abandoned cart sessions which were recovered', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php esc_html_e( strval( $recovered_cart['count'] ) ); ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Recovered Revenue', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Sum total of all recovered carts value', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php echo wc_price( $recovered_cart['sum'] ); //phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Recovery Rate', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Percentage of recovered carts', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php echo sprintf( '%.2f', $recovery_percentage ) . '<small>%</small>'; //phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-3">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Lost Carts', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Abandoned cart sessions where emails were captured and order were not placed in ' . $lost_time, 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <div class="bwfan_h3"><?php esc_html_e( strval( $lost_cart['count'] ) ); ?></div>
                            <div class="bwfan_abcart_clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bwfan_abcart_clearfix"></div>
        </div>
        <div class="bwfan_abcart_part1">
            <div class="bwfan_abcart_row">
                <div class="bwfan_abcart_column-6">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Carts', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Carts chart over this time period', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <canvas id="bwfan_abandoned_chart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="bwfan_abcart_column-6">
                    <div class="bwfan_abcart_report_box">
                        <div class="bwfan_abcart_report_box_top">
                            <div class="bwfan_h3"><?php esc_html_e( 'Revenue', 'wp-marketing-automations' ); ?>
                                <div class="bwfan_tooltip" data-size="2xl">
                                    <span class="bwfan_tooltip_text" data-position="top"><?php echo esc_html__( 'Revenue chart over this time period', 'wp-marketing-automations' ); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="bwfan_abcart_report_box_bottom">
                            <canvas id="bwfan_abandoned_chart_revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
