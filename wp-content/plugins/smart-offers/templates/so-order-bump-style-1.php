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

$order_bump_style = 'style-1';
$lead_text        = get_post_meta( $offer_id, 'so_order_bump_lead_text', true );
$body_text        = get_post_meta( $offer_id, 'so_order_bump_body_text', true );
$attachment_id    = get_post_meta( $offer_id, 'so_order_bump_attachment_id', true );

$offer_accepted = isset( $is_offer_accepted ) ? $is_offer_accepted : false;
$post_content   = isset( $post_content ) ? $post_content : '';

$offered_product      = get_post_meta( $offer_id, 'target_product_ids', true );
$offered_product_type = '';
if( !empty($offered_product) ){
	$offered_prod_instance = wc_get_product($offered_product);
	if( ( $offered_prod_instance instanceof WC_Product ) ) {
		$offered_product_type = $offered_prod_instance->get_type();
	}
}

if( ! wp_style_is( 'font-awesome' ) ) {
	wp_enqueue_style( 'font-awesome' );
}
?>
<div class="so-order-bump so-order-bump-<?php echo esc_attr( $order_bump_style );?> so-offered-product-type-<?php echo $offered_product_type;?>">
	<div class="so-order-bump-container">
		<div class="so-order-bump-lead">
			<div class="so-order-bump-lead-icon">
				<i class="fa fa-hand-o-right"></i>
			</div>
			<div class="so-order-bump-checkbox-wrapper">
				<input type="hidden" name="so-order-bumps-data[<?php echo $offer_id; ?>]" value="<?php echo $offer_accepted ? 'yes' : 'no'; ?>"><input type="checkbox" id="so-order-bump-checkbox-<?php echo esc_attr( $offer_id ); ?>" name="so-order-bump-checkbox[]" class="so-order-bump-checkbox" value="<?php echo esc_attr( $offer_id ); ?>" <?php echo $offer_accepted ? 'checked="checked"' : ''; ?>><label class="so-order-bump-lead-label" for="so-order-bump-checkbox-<?php echo esc_attr( $offer_id ); ?>"><span class="so-order-bump-checkmark"></span><span class="so-order-bump-lead-text"><?php echo ! empty( $lead_text ) ? $lead_text : __( 'Yes! I want it', 'smart-offers' ); ?></span></label>
			</div>
		</div>
		<div class="so-order-bump-content-wrapper"><div class="so-order-bump-body">
			<div class="so-order-bump-body-text">
				<?php
					echo ! empty( $body_text ) ? $body_text : __( 'You can have access to this exclusive offer by ticking the box above. Click and add it to your order now. This offer is available only now.', 'smart-offers' );
				?>
			</div>
			<div class="so-order-bump-content">
				<?php echo $post_content; ?>
			</div>
		</div><div class="so-order-bump-product-price"><?php echo do_shortcode( '[so_price]' ); ?></div>
		</div>
	</div>
</div>
<?php
