<?php
$picons_order = WFOCU_Common::get_option( 'wfocu_other_picons_order' );
$custom_icon  = WFOCU_Common::get_option( 'wfocu_other_picons_custom' );
$picons_color = WFOCU_Common::get_option( 'wfocu_other_picons_color' );


if ( empty( $picons_order ) && empty( $custom_icon ) ) {
	return;
}

$template_ins = $this->get_template_ins();

$icon_color_class = $picons_color ? $picons_color : '';

?>
<div class="wfocu-product-pay-card <?php echo $icon_color_class; ?>">
    <ul>
		<?php
		if ( is_array( $picons_order ) && count( $picons_order ) > 0 ) {
			foreach ( $picons_order as $picons ) {
				$img_src_path = $template_ins->img_public_path . 'payment_cards/' . $picons . '.png';
				if ( $picons !== '' ) {
					?>
                    <li><img class="wfocu-cardIcon" src="<?php echo $img_src_path; ?>" alt="<?php get_bloginfo( 'title' ); ?>"></li>
					<?php
				}
			}
		}
		if ( ! empty( $custom_icon ) ) {
			?>
            <li><img class="wfocu-cardIcon" src="<?php echo $custom_icon; ?>" alt="<?php get_bloginfo( 'title' ); ?>"></li>
			<?php
		}
		?>
    </ul>
</div>
