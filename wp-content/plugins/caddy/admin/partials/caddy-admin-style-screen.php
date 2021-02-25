<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin style screen of the plugin.
 *
 * @link       https://www.madebytribe.com
 * @since      1.0.0
 *
 * @package    Caddy
 * @subpackage Caddy/admin/partials
 */
?>

<?php
// GET STYLE OPTIONS
$cc_custom_css = get_option( 'cc_custom_css' );
$cc_custom_css = ! empty( $cc_custom_css ) ? esc_html( stripslashes( $cc_custom_css ) ) : '';
?>

<?php do_action( 'caddy_before_color_selectors_section' ); ?>
<h2><i class="cc-admin-icon-droplet section-icons"></i>&nbsp;<?php echo esc_html( __( 'Color Selectors', 'caddy' ) ); ?></h2>
<p><?php echo esc_html( __( 'Caddy style general customization options.', 'caddy' ) ); ?></p>
<table class="form-table cc-style-table">
	<tbody>
	<?php do_action( 'caddy_before_custom_css_row' ); ?>
	<tr>
		<th scope="row">
			<label for="cc_custom_css"><?php echo esc_html( __( 'Custom CSS', 'caddy' ) ); ?></label>
		</th>
		<td class="color-picker">
			<label><textarea name="cc_custom_css" id="cc_custom_css" rows="10" cols="50"><?php echo $cc_custom_css; ?></textarea></label>
		</td>
	</tr>
	<?php do_action( 'caddy_after_custom_css_row' ); ?>
	</tbody>
</table>
<?php do_action( 'caddy_after_color_selectors_section' ); ?>
