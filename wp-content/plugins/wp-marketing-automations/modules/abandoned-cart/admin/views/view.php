<?php

$menu_arr = [];
$menus    = $this->get_menu();

foreach ( $menus as $menu_id => $menu ) {
	$menu_url = add_query_arg( [
		'page'       => 'autonami',
		'tab'        => 'carts',
		'ab_section' => $menu_id,
	], admin_url( 'admin.php' ) );

	$class = ( $this->section === $menu_id ) ? 'current' : '';

	$menu_arr[ sanitize_title( $menu['name'] ) ] = array(
		'label' => $menu['name'],
		'class' => $class,
		'href'  => $menu_url,
	);
}
$cart_enabled    = true;
$global_settings = BWFAN_Common::get_global_settings();
if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
	$cart_enabled = false;
}
?>
<style>
    li.bwfan_selected_menu a {
        font-weight: bold;
    }
</style>
<div class="wrap bwfan_global bwfan_global_settings bwfan_tab_carts">
	<?php BWFAN_Core()->admin->make_main_tabs_ui(); ?>
    <div id="poststuff">
        <div class="inside">
			<?php
			if ( false === $cart_enabled ) {
				$enable_link = add_query_arg( [
					'page'            => 'autonami',
					'tab'             => 'carts',
					'enable_tracking' => 'cart',
					'cart_nonce'      => wp_create_nonce( 'bwfan_tab_cart_tracking_enable' ),
				], admin_url( 'admin.php' ) );
				?>
                <div class="bwfan_highlight_center">
                    <div class="bwfan_heading"><?php esc_attr_e( 'Oops! Unable to capture emails, Cart Tracking is disabled.', 'wp-marketing-automations' ); ?></div>
                    <div class="bwfan_clear_20"></div>
                    <div class="bwfan_content">
                        <p><?php esc_attr_e( 'Click on the button below to go to Settings > Carts to enable cart tracking. Once activated, you will be able to capture emails as soon buyer enters it.', 'wp-marketing-automations' ); ?></p>
                    </div>
                    <div class="bwfan_clear_30"></div>
                    <a class="bwfan_btn_blue_big bwfan_enable_tracking" href="<?php echo esc_url_raw( $enable_link ); ?>">Enable Tracking</a>
                </div>
				<?php
			} else {
				?>
                <div class="wp-filter">
                    <ul class="filter-links">
						<?php
						foreach ( $menu_arr as $menu ) {
							echo "<li><a href='{$menu['href']}' class='{$menu['class']}'>{$menu['label']}</a></li>"; //phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
                    </ul>
                </div>
                <div class="bwfan_clearfix">
					<?php
					if ( isset( $menus[ $this->section ] ) ) {
						$template = $menus[ $this->section ]['template'];
						include $template;
					}

					do_action( 'bwfan_abandoned_cart_admin_view' );
					?>
                </div>
				<?php
			}
			?>
        </div>
    </div>
</div>
