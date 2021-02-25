<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin settings screen of the plugin.
 *
 * @link       https://www.madebytribe.com
 * @since      1.0.0
 *
 * @package    Caddy
 * @subpackage Caddy/admin/partials
 */
?>

<?php
// GET SETTINGS OPTIONS
$cc_product_recommendation = get_option( 'cc_product_recommendation' );
$cc_product_recommendation = ( 'disabled' !== $cc_product_recommendation ) ? 'checked' : '';

$cc_free_shipping_amount = get_option( 'cc_free_shipping_amount' );

$cc_shipping_country = get_option( 'cc_shipping_country' );
$cc_shipping_country = ! empty( $cc_shipping_country ) ? esc_attr( $cc_shipping_country ) : '';

$cc_disable_branding = get_option( 'cc_disable_branding' );
$cc_disable_branding = ( 'disabled' !== $cc_disable_branding ) ? 'checked' : '';

?>

<?php do_action( 'caddy_before_product_recommendations_section' ); ?>
<h2><i class="cc-admin-icon-star-full section-icons"></i>&nbsp;<?php echo esc_html( __( 'Product Recommendations', 'caddy' ) ); ?></h2>
<p><?php echo esc_html( __( 'Displays recommendations as defined in your product up-sell settings.', 'caddy' ) ); ?></p>
<table class="form-table">
	<tbody>
	<?php do_action( 'caddy_before_product_recommendations_row' ); ?>
	<tr>
		<th scope="row">
			<label for="cc_product_recommendation"><?php echo esc_html( __( 'Enable recommendations', 'caddy' ) ); ?></label>
		</th>
		<td>
			<div class="cc-toggle cc-toggle--size-small">
				<input type="checkbox" name="cc_product_recommendation" id="cc_product_recommendation" value="enabled" <?php echo $cc_product_recommendation; ?>>
				<label for="cc_product_recommendation">
					<div class="cc-toggle__switch" data-checked="On" data-unchecked="Off"></div>
				</label>
			</div>
		</td>
	</tr>
	<?php do_action( 'caddy_after_product_recommendations_row' ); ?>
	</tbody>
</table>
<?php do_action( 'caddy_after_product_recommendations_section' ); ?>

<h2><i class="cc-admin-icon-truck section-icons"></i>&nbsp;<?php echo esc_html( __( 'Free Shipping Meter', 'caddy' ) ); ?></h2>
<p><?php echo esc_html( __( 'Displays a free shipping meter in Caddy. This requires a free shipping method configured within WooCommerce', 'caddy' ) ); ?> <a
			href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=wc-settings&tab=shipping">shipping settings</a>. </p>
<table class="form-table">
	<tbody>
	<?php do_action( 'caddy_before_free_shipping_section' ); ?>
	<tr>
		<th scope="row">
			<label for="cc_free_shipping_amount"><?php echo esc_html( __( 'Trigger amount', 'caddy' ) ); ?></label>
		</th>
		<td>
			<input type="number" name="cc_free_shipping_amount" id="cc_free_shipping_amount" value="<?php echo $cc_free_shipping_amount; ?>" />
			<p class="description"><?php echo esc_html( __( 'Set an amount to enable the free shipping meter.', 'caddy' ) ); ?>
				<strong><?php echo esc_html( __( 'Leave blank to disable.', 'caddy' ) );
					?></strong></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="cc_shipping_country"><?php echo esc_html( __( 'Free shipping country', 'caddy' ) ); ?></label>
		</th>
		<td>
			<?php
			$wc_countries      = new WC_Countries();
			$wc_base_country   = WC()->countries->get_base_country();
			$wc_countries_list = $wc_countries->get_countries();
			if ( ! empty( $wc_countries_list ) ) {
				$selected_country = ! empty( $cc_shipping_country ) ? $cc_shipping_country : $wc_base_country;
				?>
				<select name="cc_shipping_country" id="cc_shipping_country">
					<?php foreach ( $wc_countries_list as $key => $value ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_country, $key ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>
			<?php } ?>
		</td>
	</tr>
	<?php do_action( 'caddy_after_free_shipping_section' ); ?>
	</tbody>
</table>

<?php do_action( 'caddy_before_messaging_section' ); ?>
<h2><i class="cc-admin-icon-pencil section-icons"></i>&nbsp;<?php echo esc_html( __( 'Messaging', 'caddy' ) ); ?></h2>
<p><?php echo esc_html( __( 'Customize the messaging within the Caddy front-end.', 'caddy' ) ); ?></p>
<table class="form-table">
	<tbody>
	<?php do_action( 'caddy_before_caddy_branding_row' ); ?>
	<tr>
		<th scope="row">
			<label for="cc_disable_branding"><?php echo esc_html( __( 'Powered by Caddy branding', 'caddy' ) ); ?></label>
		</th>
		<td>
			<div class="cc-toggle cc-toggle--size-small">
				<input type="checkbox" name="cc_disable_branding" id="cc_disable_branding" value="enabled" <?php echo $cc_disable_branding; ?>>
				<label for="cc_disable_branding">
					<div class="cc-toggle__switch" data-checked="On" data-unchecked="Off"></div>
					<div class="cc-toggle__label-text"><?php echo esc_html( __( 'We appreciate your support! :)', 'caddy' ) ); ?></div>
				</label>
			</div>
		</td>
	</tr>
	<?php do_action( 'caddy_after_caddy_branding_row' ); ?>
	</tbody>
</table>
<?php do_action( 'caddy_after_messaging_section' ); ?>
