<?php
/**
 * Template For Smart Offers Order Bump
 *
 * @package     templates
 * @author      StoreApps
 * @version     1.0.0
 * @since       Smart Offers 3.10.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order_bump_style = 'style-4';
$lead_text        = get_post_meta( $offer_id, 'so_order_bump_lead_text', true );
$intro_text       = get_post_meta( $offer_id, 'so_order_bump_intro_text', true );
$body_text        = get_post_meta( $offer_id, 'so_order_bump_body_text', true );
$attachment_id    = get_post_meta( $offer_id, 'so_order_bump_attachment_id', true );

$offer_accepted       = isset( $is_offer_accepted ) ? $is_offer_accepted : false;
$post_content         = isset( $post_content ) ? $post_content : '';
$offer_image_class    = empty( $attachment_id ) ? 'so-no-order-bump-image' : ''; // CSS class to handle no impage present case.

$offered_product      = get_post_meta( $offer_id, 'target_product_ids', true );
$offered_product_type = '';
if( !empty($offered_product) ){
	$offered_prod_instance = wc_get_product($offered_product);
	if( ( $offered_prod_instance instanceof WC_Product ) ) {
		$offered_product_type = $offered_prod_instance->get_type();
	}
}
$is_variable = in_array( $offered_product_type, array( 'variable', 'variable-subscription' ), true );
?>
<div class="so-order-bump so-order-bump-<?php echo esc_attr( $order_bump_style );?> <?php echo $offer_image_class;?>">
	<div class="so-order-bump-container">
		<div class="so-order-bump-lead">
			<div class="so-order-bump-checkbox-wrapper">
				<input type="hidden" name="so-order-bumps-data[<?php echo $offer_id; ?>]" value="<?php echo $offer_accepted ? 'yes' : 'no'; ?>"><input type="checkbox" id="so-order-bump-checkbox-<?php echo esc_attr( $offer_id ); ?>" name="so-order-bump-checkbox[]" class="so-order-bump-checkbox" value="<?php echo esc_attr( $offer_id ); ?>" <?php echo $offer_accepted ? 'checked="checked"' : ''; ?>><label class="so-order-bump-lead-label" for="so-order-bump-checkbox-<?php echo esc_attr( $offer_id ); ?>"><span class="so-order-bump-checkmark"></span><span class="so-order-bump-lead-text"><?php echo ! empty( $lead_text ) ? $lead_text : __( 'Yes! I want it', 'smart-offers' ); ?></span></label>
			</div>
		</div>
		<div class="so-order-bump-content-wrapper">
			<?php 
			if( ! empty( $attachment_id ) ) {
				$attachment_image = wp_get_attachment_image_src( $attachment_id );
				if( is_array( $attachment_image ) && ! empty( $attachment_image ) ) {
					$attachment_url = $attachment_image[0];
					?>
					<div class="so-order-bump-image-wrapper">
						<img class="so_product_image so-order-bump-image" src="<?php echo esc_url( $attachment_url );?>">
					</div>
					<?php
				}
			}
		?>
		<div class="so-container">
			<div class="so-order-bump-body">
				<div class="so-order-bump-intro-price-wrapper">
					<div class="so-order-bump-intro">
						<?php
							echo ! empty( $intro_text ) ? $intro_text : __( 'ONE TIME OFFER!', 'smart-offers' );
						?>
					</div>
					<div class="so-order-bump-product-price">
						<?php
							// If product is not variable then show price on load else it would be shown when user make option selection.
							if( false === $is_variable ) {
								echo do_shortcode( '[so_price]' );
							}
						?>
					</div>
				</div>
				<div class="so-order-bump-body-text">
					<?php
						echo ! empty( $body_text ) ? $body_text : __( 'You can have access to this exclusive offer by ticking the box above. Click and add it to your order now. This offer is available only now.', 'smart-offers' );
					?>
				</div>
			</div>
			<div class="so-order-bump-variation-options-container">
				<div class="so-order-bump-variation-options">
					<?php echo $post_content; ?>
				</div>
			</div>	
			<div class="so-order-bump-cta-wrapper">
				<button type="button" class="button so-order-bump-cta" <?php echo $is_variable ? 'disabled="disabled"' : ''; ?>><?php echo ! empty( $lead_text ) ? $lead_text : __( 'Yes! I want it', 'smart-offers' ); ?></button>
			</div>
		</div>
		</div>
	</div>
</div>
<?php
