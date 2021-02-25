<?php
/** Registering Settings in top bar */
if ( class_exists( 'BWF_Admin_Breadcrumbs' ) ) {
	BWF_Admin_Breadcrumbs::register_node( [ 'text' => __('One Click Upsells', 'woofunnels-upstroke-one-click-upsell') ] );
}
BWF_Admin_Breadcrumbs::render_sticky_bar();
?>
<div class="wrap wfocu_funnels_listing wfocu_global">
    <h1 class="wp-heading-inline"><?php _e('One Click Upsells', 'woofunnels-upstroke-one-click-upsell'); ?></h1>
        <a href="javascript:void(0)" class="page-title-action" data-izimodal-open="#modal-add-funnel" data-iziModal-title="Create New Offer" data-izimodal-transitionin="fadeInDown"><?php echo esc_attr__( "Add New", 'woofunnels-upstroke-one-click-upsell' ); ?></a>
    <a href="<?php echo admin_url( 'admin.php?page=upstroke&tab=import' ); ?>" class="page-title-action"> <?php esc_html_e( 'Import', 'woofunnels-flex-funnels' ); ?> </a>
    <a href="<?php echo admin_url( 'admin.php?page=upstroke&tab=export' ); ?>" class="page-title-action"> <?php esc_html_e( 'Export', 'woofunnels-flex-funnels' ); ?> </a>
    <hr class="wp-header-end">
    <div class="wfocu_clear_10"></div>
    <div id="poststuff">
        <div class="inside">
            <div class="wfocu_page_col2_wrap wfocu_clearfix">
                <div class="wfocu_page_left_wrap">
                    <form method="GET">
                        <input type="hidden" name="page" value="upstroke"/>
                        <input type="hidden" name="status" value="<?php echo esc_attr( isset( $_GET['status'] ) ? wc_clean( $_GET['status'] ) : '' );  // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"/>
						<?php
						$table = new WFOCU_Post_Table();
						$table->render_trigger_nav();
						$table->search_box( 'Search' );
						$table->data = WFOCU_Common::get_post_table_data();
						$table->prepare_items();
						$table->display();
						?>
                    </form>
					<?php $table->order_preview_template() ?>
                </div>
                <div class="wfocu_page_right_wrap" style="display: none;">
					<?php do_action( 'wfocu_page_right_content' ); ?>
                </div>
            </div>
        </div>
        <div style="display: none" class="wfocu_success_modal" id="modal-wfocu-state-change-success" data-iziModal-icon="icon-home">


        </div>
    </div>
</div>

<div class="wfocu_izimodal_default" style="display: none" id="modal-add-funnel">
    <div class="sections">
        <form class="wfocu_add_funnel" data-wfoaction="add_new_funnel">
            <div class="wfocu_vue_forms" id="part-add-funnel">
                <vue-form-generator :schema="schema" :model="model" :options="formOptions"></vue-form-generator>
            </div>
            <fieldset>
                <div class="wfocu_form_submit">
                    <input hidden name="_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wfocu_add_new_funnel' ) ); ?>"/>
                    <button type="submit" class="wfocu_btn wfocu_btn_primary" value="add_new"><?php echo esc_attr_e( "Create", 'woofunnels-upstroke-one-click-upsell' ); ?></button>
                </div>
                <div class="wfocu_form_response">
                </div>
            </fieldset>
        </form>
        <div class="wfocu-funnel-create-success-wrap">
            <div class="wfocu-funnel-create-success-logo">
                <div class="swal2-icon swal2-success swal2-animate-success-icon">
                    <div class="swal2-success-circular-line-left"></div>
                    <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
                    <div class="swal2-success-ring"></div>
                    <div class="swal2-success-fix"></div>
                    <div class="swal2-success-circular-line-right"></div>
                </div>
            </div>
            <div class="wfocu-funnel-create-message"><?php esc_attr_e( 'Upsell Funnel Created Successfully. Launching Funnel Editor...', 'woofunnels-upstroke-one-click-upsell' ) ?></div>
        </div>
    </div>
</div>

<div class="wfocu_izimodal_default" style="display: none" id="modal-duplicate-funnel">
    <div class="wfocu_duplicate_modal" id="modal_settings_duplicate" data-iziModal-icon="icon-home"></div>
</div>




