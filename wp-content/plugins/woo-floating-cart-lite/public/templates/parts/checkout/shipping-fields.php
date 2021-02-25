<?php

/**
 * This file is used to markup the checkout shipping fields.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/checkout/shipping-fields.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     1.3.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

        <h3 id="ship-to-different-address">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input id="ship-to-different-address-checkbox"
                       class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?>
                       type="checkbox" name="ship_to_different_address" value="1"/>
                <span><?php esc_html_e( 'Ship to a different address?', 'woo-floating-cart' ); ?></span>
            </label>
        </h3>

        <div class="shipping_address" style="display:none">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', WC()->checkout() ); ?>

            <div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = WC()->checkout()->get_checkout_fields( 'shipping' );

				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
				}
				?>
            </div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', WC()->checkout() ); ?>

        </div>

	<?php endif; ?>
</div>