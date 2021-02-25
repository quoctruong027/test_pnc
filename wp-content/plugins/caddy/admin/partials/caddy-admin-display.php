<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.madebytribe.com
 * @since      1.0.0
 *
 * @package    Caddy
 * @subpackage Caddy/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

$caddy_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'settings';

$caddy_tabs_name = array(
	'settings' => array(
		'tab_name' => __( 'Settings', 'caddy' ),
		'tab_icon' => 'cc-admin-icon-equalizer',
	),
	'styles'   => array(
		'tab_name' => __( 'Styles', 'caddy' ),
		'tab_icon' => 'cc-admin-icon-droplet',
	),
);
/**
 * Filters the caddy tab names.
 *
 * @param array $caddy_tabs_name Caddy tab names.
 *
 * @since 1.3.0
 *
 */
$caddy_tabs = apply_filters( 'caddy_tab_names', $caddy_tabs_name );

if ( isset( $_POST['cc_submit_hidden'] ) && $_POST['cc_submit_hidden'] == 'Y' ) {

	// UPDATE SETTINGS OPTIONS
	if ( 'settings' === $caddy_tab ) {
		$cc_product_recommendation = isset( $_POST['cc_product_recommendation'] ) ? sanitize_text_field( $_POST['cc_product_recommendation'] ) : 'disabled';
		update_option( 'cc_product_recommendation', $cc_product_recommendation );

		$cc_free_shipping_amount = ! empty( $_POST['cc_free_shipping_amount'] ) ? intval( $_POST['cc_free_shipping_amount'] ) : '';
		update_option( 'cc_free_shipping_amount', $cc_free_shipping_amount );

		$cc_shipping_country = ! empty( $_POST['cc_shipping_country'] ) ? sanitize_text_field( $_POST['cc_shipping_country'] ) : '';
		update_option( 'cc_shipping_country', $cc_shipping_country );

		$cc_disable_branding = ! empty( $_POST['cc_disable_branding'] ) ? sanitize_text_field( $_POST['cc_disable_branding'] ) : 'disabled';
		update_option( 'cc_disable_branding', $cc_disable_branding );

	} elseif ( 'styles' === $caddy_tab ) {

		$cc_custom_css = ! empty( $_POST['cc_custom_css'] ) ? sanitize_textarea_field( $_POST['cc_custom_css'] ) : '';
		update_option( 'cc_custom_css', $cc_custom_css );

	}
	?>
	<div class="updated">
		<p><strong><?php echo esc_html( __( 'Settings saved.', 'caddy' ) ); ?></strong></p>
	</div>
<?php } ?>

<div class="wrap">
	<img src="<?php echo plugin_dir_url( __DIR__ ) ?>img/caddy-b-logo.svg" width="110" height="32" class="cc-logo">
	<div class="cc-version"><?php echo CADDY_VERSION; ?></div>
	<h2 class="nav-tab-wrapper">
		<?php
		foreach ( $caddy_tabs as $key => $value ) {
			$active_tab_class = ( $key == $caddy_tab ) ? ' nav-tab-active' : '';
			?>
			<a class="nav-tab<?php echo $active_tab_class; ?>" href="?page=caddy&amp;tab=<?php echo $key; ?>"><i class="<?php echo $value['tab_icon']; ?>"></i>&nbsp;<?php echo
				$value['tab_name']; ?></a>
		<?php } ?>
	</h2>
	<?php
	$cc_dismiss_welcome_notice = get_option( 'cc_dismiss_welcome_notice', true );
	if ( 'yes' !== $cc_dismiss_welcome_notice ) {
		?>
		<div class="notice cc-welcome-notice is-dismissible" data-cc-dismissible-notice="welcome">
			<img src="<?php echo plugin_dir_url( __DIR__ ) ?>img/celebrate.png" width="65" height="65" class="cc-celebrate">
			<div class="cc-notice-text">
				<h3 class="cc-notice-heading"><?php _e( 'Welcome &amp; thanks for using Caddy!', 'caddy' ); ?></h3>
				<?php
				echo sprintf(
					'<p>%1$s <a href="%2$s" target="_blank">%3$s</a> %4$s <a href="%5$s" target="_blank">%6$s</a>. %7$s <a href="%8$s" target="_blank">%9$s</a> %10$s. <i>%11$s</i></p>',
					esc_html( 'To get started, we recommend reading through our' ),
					esc_url( 'https://help.usecaddy.com/category/104-getting-started/?utm_source=welcome-notice&amp;utm_medium=plugin&amp;utm_campaign=plugin-links' ),
					esc_html( 'getting started' ),
					esc_html( 'help docs. For tips on growing your store, check out and subscribe to our' ),
					esc_url( 'https://usecaddy.com/blog/?utm_source=welcome-notice&amp;utm_medium=plugin&amp;utm_campaign=plugin-links' ),
					esc_html( 'blog' ),
					esc_html( 'If you have any questions or need help, don\'t hesitate to' ),
					esc_url( 'https://usecaddy.com/contact-us/?utm_source=welcome-notice&amp;utm_medium=plugin&amp;utm_campaign=plugin-links' ),
					esc_html( 'reach out' ),
					esc_html( 'to us' ),
					esc_html( '- The Caddy Team' )
				);
				?>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'cc_before_setting_options' ); ?>
	<div class="cc-settings-wrap">
		<form name="caddy-form" id="caddy-form" method="post" action="">
			<input type="hidden" name="cc_submit_hidden" value="Y">
			<div class="cc-settings-container">
				<?php
				//Include tab screen files
				do_action( 'caddy_admin_tab_screen' );
				?>
			</div>
			<p class="submit cc-primary-save">
				<input type="submit" name="Submit" class="button-primary cc-primary-save-btn" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
			</p>
		</form>
		<div class="cc-notices-container">

			<?php do_action( 'cc_upgrade_to_premium' ); ?>

			<div class="cc-box cc-links">
				<h3><?php echo esc_html( __( 'Caddy Quick Links', 'caddy' ) ); ?></h3>
				<ul>
					<li>
						<a href="https://help.usecaddy.com/?utm_source=quick-links&amp;utm_medium=plugin&amp;utm_campaign=plugin-links" target="_blank"><?php echo esc_html( __( 'Read the documentation', 'caddy' ) ); ?></a>
					</li>
					<li>
						<a href="https://usecaddy.com/my-account/?utm_source=quick-links&amp;utm_medium=plugin&amp;utm_campaign=plugin-links" target="_blank"><?php echo esc_html( __( 'Register / Log into your account', 'caddy' ) ); ?></a>
					</li>
					<li>
						<a href="https://usecaddy.com/feedback/?utm_source=quick-links&amp;utm_medium=plugin&amp;utm_campaign=plugin-links" target="_blank"><?php echo esc_html( __( 'Leave feedback', 'caddy' ) ); ?></a>
					</li>
					<li>
						<a href="mailto:success@usecaddy.com"><?php echo esc_html( __( 'Contact support', 'caddy' ) ); ?></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<?php do_action( 'cc_after_setting_options' ); ?>
</div>