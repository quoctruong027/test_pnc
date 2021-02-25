<?php
do_action( 'xlwcty_before_power_pack_page' );

?>
    <h1 class="wp-heading-inline"><?php _e( 'Thank You Pages', 'nextmove-power-pack' ); ?></h1>
    <a style="margin-top:10px;" class="page-title-action" href="<?php echo admin_url( 'post-new.php?post_type=xlwcty_thankyou' ); ?>">
		<?php _e( 'Add New', 'nextmove-power-pack' ); ?>
    </a>
    <a style="margin-top:10px;" class="page-title-action" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=settings' ); ?>">
		<?php _e( 'Settings', 'nextmove-power-pack' ); ?>
    </a>
    <a style="margin-top:10px;" class="page-title-action xlwcty-a-blue" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=xl-thank-you&section=power_pack' ); ?>"
    >
		<?php _e( 'Power Pack', 'nextmove-power-pack' ); ?>
    </a>

    <div class="notice">
        <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . XLWCTY_Common::get_wc_settings_tab_slug() . '' ) . '">' . XLWCTY_FULL_NAME . '</a> listing.', 'nextmove-power-pack' ); ?></p>
    </div>
<?php
$settings         = XLWCTY_PP_Common::get_power_pack_options();
$track_condition  = ( ( isset( $settings['append_user_track_order_email'] ) && 'yes' == $settings['append_user_track_order_email'] ) && ( ! isset( $settings['append_user_track_order_status_email'] ) || empty( $settings['append_user_track_order_status_email'] ) ) ) ? true : false;
$coupon_condition = ( ( isset( $settings['append_coupons_in_email'] ) && 'yes' == $settings['append_coupons_in_email'] ) && ( ! isset( $settings['append_coupon_order_status_email'] ) || empty( $settings['append_coupon_order_status_email'] ) ) ) ? true : false;
if ( $track_condition || $coupon_condition ) {
	?>
    <div class="notice error">
        <p><?php _e( 'You have enabled to embed Coupon/Order receipt button in email but forgot to select customer emails where you want to send them. Please select customer emails also.', 'nextmove-power-pack' ); ?></p>
    </div>
	<?php
}
?>

    <div class="wrap xlwcty_global_option">
        <div class="wrap cmb2-options-page xlwcty_global_option">
            <div class="xlwcty-help-half-left xlwcty-power-pack-wrap">
                <h1 class="wp-heading-inline"><?php _e( 'Settings', 'nextmove-power-pack' ); ?></h1>
                <div class="inside">
					<?php cmb2_metabox_form( 'xlwcty_power_pack_settings', 'xlwcty_power_pack_settings' ); ?>
                </div>
            </div>
        </div>
    </div>
<?php
