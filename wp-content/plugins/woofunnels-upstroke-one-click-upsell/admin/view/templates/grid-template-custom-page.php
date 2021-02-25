<?php
$custom_page       = array(
	'name'      => __( 'Custom Page', 'woofunnels-upstroke-one-click-upsell' ),
	'thumbnail' => WFOCU_PLUGIN_URL . '/admin/assets/img/thumbnail-link-to-custom.jpg',
	'type'      => 'link',
);
$custom_active_img = WFOCU_PLUGIN_URL . '/admin/assets/img/thumbnail-custom-page.jpg';

?>
<div class="wfocu_template_box" v-if="template_group==`custom`" v-bind:class="current_template==`custom-page`?` wfocu_temp_box_custom wfocu_template_box_single  wfocu_selected_template`:`wfocu_empty_template wfocu_template_box wfocu_temp_box_custom wfocu_template_box_single`  " data-slug="custom-page">
	<div class="wfocu_template_box_inner">
		<div class="wfocu_template_img_cover">
			<div class="wfocu_template_thumbnail">
				<div class="wfocu_img_thumbnail ">
					<div class="wfocu_overlay"></div>
					<div class="wfocu_vertical_mid">
						<div class="wfocu_add_tmp_se">
							<a href="javascript:void(0)" class="wfacp_steps_btn_add" data-izimodal-open="#modal-prev-template_custom-page" data-izimodal-transitionin="fadeInDown">
								<span>+</span>
							</a>
						</div>
						<div class="wfocu_clear_20"></div>
						<div class="wfocu_clear_20"></div>
						<div data-izimodal-open="#modal-prev-template_custom-page" data-izimodal-transitionin="fadeInDown" class="wfocu_p"><?php esc_html_e( 'Link to custom page', 'woofunnels-upstroke-one-click-upsell' ); ?></div>

					</div>

				</div>
			</div>
		</div>
	</div>
</div>
<?php include_once plugin_dir_path( WFOCU_PLUGIN_FILE ) . 'admin/view/modal-search-page.php'; ?>
