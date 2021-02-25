<?php

/**
 * This file is used to markup the checkout form.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/checkout/checkout.php.
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

<div class="xt_woofc-checkout-wrap">

	<?php do_action( 'woocommerce_before_checkout_form', WC()->checkout() ); ?>

    <form name="checkout" method="post" class="checkout woocommerce-checkout xt_woofc-checkout-form"
          action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<?php if ( WC()->checkout()->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="col1-set" id="customer_details">
                <div class="col-1">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
                </div>

                <div class="col-2">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
                </div>
            </div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>

		<?php woocommerce_checkout_payment(); ?>

    </form>

	<?php do_action( 'woocommerce_after_checkout_form', WC()->checkout() ); ?>

</div>
