<?php

/**
 * This file is used to markup the shipping form.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/cart-shipping.php.
 *
 * Available global vars:
 *
 * @var $package
 * @var $package_name
 * @var $available_methods
 * @var $chosen_method
 * @var $index
 * @var $show_package_details
 * @var $package_details
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

$show_shipping_calculator = true;
$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
?>
<tr class="woocommerce-shipping-totals shipping">
    <td colspan="2">
        <table class="shop_table shop_table_responsive">
            <tbody>
                <tr>
                    <th><?php echo wp_kses_post( $package_name ); ?></th>
                    <td data-title="<?php echo esc_attr( $package_name ); ?>">
                        <?php if ( $available_methods ) : ?>
                            <ul id="shipping_method" class="woocommerce-shipping-methods">
                                <?php foreach ( $available_methods as $method ) : ?>
                                    <li>
                                        <?php
                                        if ( 1 < count( $available_methods ) ) {
                                            printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // WPCS: XSS ok.
                                        } else {
                                            printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // WPCS: XSS ok.
                                        }
                                        printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // WPCS: XSS ok.
                                        do_action( 'woocommerce_after_shipping_rate', $method, $index );
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <p class="woocommerce-shipping-destination">
                                <?php
                                if ( $formatted_destination ) {
                                    // Translators: $s shipping destination.
                                    printf( esc_html__( 'Shipping to %s.', 'woo-floating-cart' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
                                    $calculator_text = esc_html__( 'Change address', 'woo-floating-cart' );
                                } else {
                                    echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', esc_html__( 'Shipping options will be updated during checkout.', 'woo-floating-cart' ) ) );
                                }
                                ?>
                            </p>

                        <?php
                        elseif ( ! $has_calculated_shipping || ! $formatted_destination ) :
                            echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', esc_html__( 'Enter your address to view shipping options.', 'woo-floating-cart' ) ) );
                        else :
                            // Translators: $s shipping destination.
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'woo-floating-cart' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) ) );
                            $calculator_text = esc_html__( 'Enter a different address', 'woo-floating-cart' );
                        endif;
                        ?>

                        <?php if ( $show_package_details ) : ?>
                            <?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
                        <?php endif; ?>

                        <?php if ( $show_shipping_calculator ) : ?>
                            <?php woocommerce_shipping_calculator( $calculator_text ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
