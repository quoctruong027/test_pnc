<?php
if ( ! defined( 'WFACP_TEMPLATE_DIR' ) ) {
	return '';
}

$instance = wfacp_template();

$payment_buttons = $instance->get_smart_buttons();
if ( empty( $payment_buttons ) ) {
	return;
}


$or_title     = apply_filters( 'wfacp_smart_button_or_text', __( 'OR', 'woofunnels-aero-checkout' ) );
$legend_title = apply_filters( 'wfacp_smart_button_legend_title', __( 'Express Checkout', 'woofunnels-aero-checkout' ) );

?>
<div class="wfacp_smart_buttons wfacp-dynamic-checkout-loading" id="wfacp_smart_buttons">

    <div class="wfacp_smart_button_outer_buttons ">
        <div class="wfacp_smart_button_inner wfacp_smart_buttons_placeholder">

            <fieldset>

                <legend><?php echo $legend_title ?></legend>
                <div class="wfacp_smart_button_wrap_st">

                    <div class="dynamic-checkout__skeleton">
                        <div class="placeholder-line placeholder-line--animated"></div>
                    </div>
					<?php

					foreach ( $payment_buttons as $slug => $payment ) {
						?>
                        <div class="wfacp_smart_button_container" id="wfacp_smart_button_<?php echo $slug; ?>">
							<?php
							if ( isset( $payment['iframe'] ) ) {
								do_action( 'wfacp_smart_button_container_' . $slug, $payment );
							} else {
								if ( '' == $payment['image'] ) {
									continue;
								}
								?>
                                <div class="wfacp_smart_button_image_container">
                                    <img src="<?php echo $payment['image'] ?>">
                                </div>
								<?php
							}
							?>
                        </div>
						<?php
					}
					?>
                </div>

            </fieldset>
        </div>
		<?php
		if ( '' !== $or_title ) {
			?>
            <div class="wfacp_smart_button_inner wfacp_smart_button_or_text_placeholder"><label><?php echo $or_title; ?></label></div>
			<?php
		}

		?>
    </div>
</div>

