<?php
require_once( __DIR__ . '/wcct-deal-shortcode-table.php' );
do_action( 'wcct-deal-before-page' );

?>
<h1 class="wp-heading-inline"><?php _e( 'Deal Pages', 'finale-woocommerce-deal-pages' ); ?></h1>
<a style="margin-top: 10px;" class="page-title-action wcct_deal_back_to_link" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ); ?>"
><?php _e( 'Finale Campaigns', 'finale-woocommerce-deal-pages' ); ?></a>
<a style="margin-top: 10px;" class="page-title-action" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ); ?>"
><?php _e( 'Index Campaigns', 'finale-woocommerce-deal-pages' ); ?></a>
<?php
$wcct_deal = Finale_deal_batch_processing::instance();
$array     = $wcct_deal->get_campaign_by_index();
if ( empty( $array ) ) {
	echo wpautop( __( sprintf( 'No Shortcode Available, You have to index atleast one campaign to create shortcode. <a href="%s">%s</a> ', admin_url( 'admin.php?page=wc-settings&tab=xl-countdown-timer&section=deal_pages' ), 'Index now' ), 'finale-woocommerce-deal-pages' ) );

	return;
}
?>
<a href="<?php echo admin_url( 'post-new.php?post_type=wcct-deal-shortcode' ); ?>"
   class="page-title-action xlwcty-a-blue"><?php _e( 'Add New', 'finale-woocommerce-deal-pages' ); ?></a>

<div id="poststuff">
    <div class="inside">
        <div class="wcct_options_page_col2_wrap">
            <div class="wcct_options_page_left_wrap">
				<?php
				$table = new WCCT_Batch_Shortcode_Post_Table();
				$table->prepare_items();
				$table->display();
				?>
            </div>
            <div class="wcct_options_page_right_wrap shortcode_side_margin">
				<?php do_action( 'wcct_deal_page_shortcode_page_right_content' ); ?>
            </div>
        </div>

    </div>
</div>
