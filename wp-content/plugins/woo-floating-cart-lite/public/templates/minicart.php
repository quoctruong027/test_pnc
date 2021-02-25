<?php

/**
 * This file is used to markup the mini-cart.
 *
 * This template can be overridden by copying it to yourtheme/woo-floating-cart/minicart.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="<?php echo esc_attr(xt_woofc_class()); ?>" <?php xt_woofc_attributes(); ?>>

	<?php do_action( 'xt_woofc_before_cart' ); ?>

	<?php xt_woo_floating_cart()->get_template( 'parts/cart' ); ?>

	<?php do_action( 'xt_woofc_after_cart' ); ?>

</div>