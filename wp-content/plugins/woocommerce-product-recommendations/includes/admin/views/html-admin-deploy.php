<?php
/**
 * Admin View: Deploy engine
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce">

	<?php if ( ! $deployment ) { ?>

		<h1 class="wp-heading-inline"><?php echo sprintf( __( 'Deploy &quot;%s&quot;</span>', 'woocommerce-product-recommendations' ), $engine->get_name() ? $engine->get_name() : __( '(untitled)', 'woocommerce-product-recommendations' ) ); ?></h1>
		<a href="<?php echo ! $is_quick ? admin_url( sprintf( 'post.php?post=%d&action=edit', $engine_id ) ) : admin_url( 'admin.php?page=prl_locations' ) ?>" class="page-title-action"><?php esc_html_e( 'Back', 'woocommerce-product-recommendations' ); ?></a>

	<?php } else { ?>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Edit deployment', 'woocommerce-product-recommendations' ); ?></h1>
		<a href="<?php echo admin_url( 'admin.php?page=prl_locations' ) ?>" class="page-title-action"><?php esc_html_e( 'All deployments', 'woocommerce-product-recommendations' ); ?></a>
	<?php } ?>

	<hr class="wp-header-end">

	<div class="postbox wc-prl-deploy" id="wc-prl-deploy-data">

		<form method="post" id="mainform" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'woocommerce-prl-deploy' ); ?>
			<input type="hidden" name="prl_deploy[engine_id]" value="<?php echo $engine->get_id() ?>">
			<input type="hidden" name="prl_deploy[engine_type]" id="prl_engine_type" value="<?php echo $engine->get_type() ?>">

			<?php if ( ! $deployment ) { ?>
				<div class="sw-form sw-form--stepper wc-prl-deploy_form">
			<?php } else { ?>
				<div class="wc-prl-deploy_form">
			<?php } ?>

				<?php if ( $deployment ) { ?>

					<div class="sw-form-header">
						<?php echo esc_html( __( 'Engine', 'woocommerce-product-recommendations' ) ); ?>
					</div>

					<div class="sw-form-field">
						<div class="sw-form-content">
							<span class="text">
								<span class="text--editable"><?php echo $engine->get_name() ? $engine->get_name() : __( '(untitled)', 'woocommerce-product-recommendations' ); ?></span>
								<a href="<?php echo admin_url( sprintf( 'post.php?post=%d&action=edit', $engine_id ) ) ?>"><?php _e( 'Edit', 'woocommerce-product-recommendations' ); ?></a>
							</span>
						</div>
					</div>
				<?php } ?>

				<div class="sw-form-header">
					<?php echo esc_html( empty( $deployment ) ? __( 'Configure Display Settings', 'woocommerce-product-recommendations' ) : __( 'Display Settings', 'woocommerce-product-recommendations' ) ); ?>
				</div>

				<?php if ( $deployment ) { ?>
					<div class="sw-form-field sw-form-field--checkbox">
						<label for="prl_deploy_active">
							<?php esc_html_e( 'Active', 'woocommerce-product-recommendations' ); ?>
						</label>
						<div class="sw-form-content">
							<input type="checkbox" name="prl_deploy[active]" id="prl_deploy_active"<?php echo $deployment->is_active() ? ' checked' : '' ?>>
						</div>
					</div>
				<?php } ?>

				<div class="sw-form-field">
					<label for="prl_deploy_title">
						<?php esc_html_e( 'Title', 'woocommerce-product-recommendations' ); ?>
					</label>
					<div class="sw-form-content">
						<input type="text" name="prl_deploy[title]" id="prl_deploy_title" placeholder="<?php _e( 'e.g. &quot;You may also like&hellip;&quot;', 'woocommerce-product-recommendations' ); ?>" value="<?php echo $deployment ? esc_attr( $deployment->get_title() ) : ''; ?>"/>
					</div>
				</div>

				<div class="sw-form-field">
					<label for="prl_deploy_description">
						<?php esc_html_e( 'Description', 'woocommerce-product-recommendations' ); ?>
					</label>
					<div class="sw-form-content">
						<textarea type="text" name="prl_deploy[description]" id="prl_deploy_description"><?php echo $deployment ? esc_textarea( $deployment->get_description() ) : ''; ?></textarea>
					</div>
				</div>

				<div class="sw-form-field sw-form-field--small">
					<label for="prl_deploy_columns">
						<?php esc_html_e( 'Product columns', 'woocommerce-product-recommendations' ); ?>
					</label>
					<div class="sw-form-content">
						<input type="number" name="prl_deploy[columns]" id="prl_deploy_columns" value="<?php echo $deployment ? $deployment->get_columns() : ''; ?>" placeholder="4" />
					</div>
				</div>

				<div class="sw-form-field sw-form-field--small">
					<label for="prl_deploy_limit">
						<?php esc_html_e( 'Product rows', 'woocommerce-product-recommendations' ); ?>
					</label>
					<div class="sw-form-content">
						<input type="number" name="prl_deploy[rows]" id="prl_deploy_rows" value="<?php echo $deployment ? absint( $deployment->get_limit() / $deployment->get_columns() ) : ''; ?>" placeholder="1" />
					</div>
				</div>

				<div class="sw-form-header">
						<?php echo esc_html( empty( $deployment ) ? __( 'Choose Location', 'woocommerce-product-recommendations' ) : __( 'Location', 'woocommerce-product-recommendations' ) ); ?>
				</div>

				<div class="sw-form-radio">
					<?php foreach( $locations as $location_info ) { ?>
						<?php
						if ( empty( $location_info[ 'hooks' ] ) ) {
							continue;
						}
						?>
						<div class="sw-form-radio_group">
							<div class="sw-form-radio_group_name">
								<?php echo $location_info[ 'title' ]; ?>
							</div>
							<div class="sw-form-radio_group_list">
								<?php foreach ( $location_info[ 'hooks' ] as $hook => $label ) { ?>
									<p>
										<input name="prl_deploy[hook]" type="radio" id="wc_prl_hook-<?php echo $hook ?>" value="<?php echo $hook ?>" <?php echo $deployment && $deployment->get_hook() == $hook ? 'checked="checked"' : '' ?>/>
										<label for="wc_prl_hook-<?php echo $hook ?>"><?php echo $label ?></label>
									</p>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>

				<div class="sw-form-header">
						<?php echo esc_html( empty( $deployment ) ? __( 'Add Visibility Conditions', 'woocommerce-product-recommendations' ) : __( 'Visibility Conditions', 'woocommerce-product-recommendations' ) ); ?>
						<span><?php esc_html_e( '(optional)', 'woocommerce-product-recommendations' ); ?></span>
				</div>

				<div class="sw-form-os">
					<?php
					$options                  = array();
					$options[ 'post_name' ]   = 'prl_deploy';
					$options[ 'hide_header' ] = true;

					if ( $deployment ) {
						$options[ 'conditions' ] = $deployment->get_conditions_data();
					}

					WC_PRL()->conditions->get_admin_conditions_html( $engine->get_type(), $options );
					?>
				</div>

				<?php if ( empty( $GLOBALS[ 'hide_save_button' ] ) ) : ?>
					<div class="sw-form-submit">
						<?php if ( ! $deployment ) { ?>
							<button name="save" class="button button-primary sw-button-primary" id="sw-button-primary" type="submit" value="<?php esc_attr_e( 'Deploy', 'woocommerce-product-recommendations' ); ?>"><?php esc_html_e( 'Deploy', 'woocommerce-product-recommendations' ); ?></button>
							<a href="<?php echo ! $is_quick ? admin_url( sprintf( 'post.php?post=%d&action=edit', $engine_id ) ) : admin_url( 'admin.php?page=prl_locations' ) ?>" class="sw-form-cancel"><?php esc_html_e( 'Cancel', 'woocommerce-product-recommendations' ); ?></a>
						<?php } else { ?>
							<input type="hidden" name="prl_deploy[id]" value="<?php echo $deployment->get_id() ?>">
							<button name="save" class="button button-primary sw-button-primary" id="sw-button-primary" type="submit" value="<?php esc_attr_e( 'Save changes', 'woocommerce-product-recommendations' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce-product-recommendations' ); ?></button>
						<?php } ?>
					</div>
				<?php endif; ?>

			</div>

		</form>
	</div>
</div>
