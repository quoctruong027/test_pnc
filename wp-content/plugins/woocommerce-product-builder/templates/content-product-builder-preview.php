<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$message_success = $settings->get_message_success();
$back_url        = get_the_permalink();

global $post;
$post_id = is_woopb_shortcode() ? $id : $post->ID;
?>

<div class="woocommerce-product-builder">
    <form method="POST"
          action="<?php echo apply_filters( 'woopb_redirect_link_after_add_to_cart', wc_get_cart_url() ) ?>"
          class="woopb-form">
		<?php wp_nonce_field( '_woopb_add_to_woocommerce', '_nonce' ) ?>
        <input type="hidden" name="woopb_id" value="<?php echo esc_attr( $post_id ) ?>"/>
        <h2><?php esc_html_e( 'Your chosen list', 'woocommerce-product-builder' ); ?></h2>
		<?php
		if ( is_array( $products ) && count( $products ) ) {
			?>
            <table class="woocommerce-product-builder-table">
                <thead>
                <tr>
                    <th width="15%"></th>
                    <th width="35%"><?php esc_html_e( 'Product', 'woocommerce-product-builder' ) ?></th>
                    <th width="15%"><?php esc_html_e( 'Price', 'woocommerce-product-builder' ) ?></th>
                    <th width="10%"><?php esc_html_e( 'Total', 'woocommerce-product-builder' ) ?></th>
                    <th width="6%"></th>
                    <th width="5%"></th>

                </tr>
                </thead>
                <tbody>
				<?php
				$index = 1;
				$total = $final_total = 0;
				foreach ( $products as $step_id => $items ) {
					foreach ( $items as $product_id => $quantity ) {
						$product       = wc_get_product( $product_id );
						$product_title = $product->get_title();
						$prd_des       = $product->get_short_description();
						if ( ! empty( get_the_post_thumbnail( $product_id ) ) ) {
							$prd_thumbnail = get_the_post_thumbnail( $product_id, 'thumbnail' );
						} else {
							$prd_thumbnail = wc_placeholder_img();
						}
						$product_price = $product->get_price();
						?>
                        <tr>

                            <td><?php echo $prd_thumbnail; ?></td>
                            <td>
                                <a target="_blank" href="<?php echo get_permalink( $product_id ); ?>"
                                   class="vi-chosen_title"><?php echo esc_html( $product_title ); ?></a>
                                x <?php echo esc_html( $quantity ) ?>
                            </td>
                            <td><?php echo $product->get_price_html() ?></td>

                            <td class="woopb-total">
		                        <?php echo wc_price( ( $product_price * $quantity ) ) ?>
                            </td>
                            <td>
		                        <?php do_action( 'link_external_button', $product_id ) ?>
                            </td>
                            <td>
		                        <?php
		                        $param = get_post_meta( $post_id, 'woopb-param', true );
		                        if ( ! isset( $param['require_product'] ) || ! $param['require_product'] ) {
			                        $arg_remove = array( 'stepp' => $step_id, 'product_id' => $product_id, 'post_id' => $post_id );
			                        ?>
                                    <a class="woopb-step-product-added-remove"
                                       href="<?php echo wp_nonce_url( add_query_arg( $arg_remove ), '_woopb_remove_product_step', '_nonce' ) ?>"></a>
		                        <?php } ?>
                            </td>
                        </tr>
						<?php
						$total       = $total + intval( $product_price );
						$final_total = $final_total + intval( $product_price ) * intval( $quantity );
					}
				} ?>
                </tbody>
                <tfoot>
                <tr class="woopb-total-preview-custom">
                    <th colspan="3"
                        style="text-align: center"><?php esc_html_e( 'Total', 'woocommerce-product-builder' ) ?></th>
                    <th colspan="3"><?php printf( wc_price( $final_total ) ) ?></th>
                </tr>
                </tfoot>
				<?php //do_action( 'woopb_after_preview_table', $final_total ); ?>
            </table>
			<?php
			if ( $settings->get_share_link_enable() ) {
				?>
                <div class="woopb-share">
                    <label><?php esc_html_e( 'Share', 'woocommerce-product-builder' ) ?></label>
                    <div class="woopb-field">
                        <input type="text" class="woopb-share-link" readonly value="<?php echo esc_url( $settings->get_share_link() ) ?>">
                    </div>
                </div>
			<?php } ?>
            <a href="<?php echo esc_url( $back_url ); ?>"
               class="woopb-button"><?php esc_attr_e( 'Back', 'woocommerce-product-builder' ) ?></a>

			<?php
			$btn = " <button class='woopb-button woopb-button-primary'>" . __( 'Add to cart', 'woocommerce-product-builder' ) . "</button>";
			printf( apply_filters( 'woopb_add_to_cart_button', $btn ) );

			$settings = new VI_WPRODUCTBUILDER_Data();
			if ( $settings->enable_email() ) { ?>

                <a id="vi_wpb_sendtofriend"
                   class="woopb-button"><?php esc_attr_e( 'Send email to your friend', 'woocommerce-product-builder' ) ?></a>
			<?php } ?>
			<?php
		}

		?>
    </form>
</div>
