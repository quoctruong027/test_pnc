<?php

	// Get our options array.
	$settings = get_option( 'cptpro_settings' );

	// License.
	$license = isset( $settings['licensing'] ) && isset( $settings['licensing']['license'] ) ? $settings['licensing']['license'] : '';
?>
<!-- No License Message -->
<div class="yikes-custom-notice yikes-custom-notice-failure" <?php echo ! empty( $license ) ? 'style="display: none;"' : ''; ?>>
	<span class="yikes-custom-notice-content yikes-custom-notice-content-failure">
		<span class="dashicons dashicons-warning"></span> 
		<span class="yikes-custom-notice-message">
			<?php
			echo sprintf(
				/* translators: the placeholders are HTML link tags. */
				esc_html__(
					'Making a support ticket requires a valid, active license. Please enter your license on the %1$1ssettings page%2$2s. If you have any issues, email us at %3$3s',
					'custom-product-tabs-pro'
				),
				'<a href="' . esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Pro_Settings_Page ), admin_url( 'admin.php' ) ) ) . '">',
				'</a>',
				'<a href="mailto:support@yikesinc.com">support@yikesinc.com</a>'
			);
			?>
		</span>
		<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
	<span>
</div>

<!-- Support Success Message -->
<div class="yikes-custom-notice yikes-custom-notice-success yikes-custom-notice-success" <?php echo isset( $_GET['success'] ) ? '' : 'style="display: none;"'; ?>>
	<span class="yikes-custom-notice-content yikes-custom-notice-content-success">
		<span class="dashicons dashicons-yes"></span> 
		<span class="yikes-custom-notice-message"><?php esc_html_e( 'Your support request has been successfully sent.', 'custom-product-tabs-pro' ); ?></span>
		<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
	<span>
</div>

<!-- Support Failure Message -->
<div class="yikes-custom-notice yikes-custom-notice-failure" style="display: none;">
	<span class="yikes-custom-notice-content yikes-custom-notice-content-failure">
		<span class="dashicons dashicons-warning"></span> 
		<span class="yikes-custom-notice-message"><?php esc_html_e( 'A required field is missing.', 'custom-product-tabs-pro' ); ?></span>
		<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
	<span>
</div>

<p>
	<?php
	echo sprintf(
		/* translators: the placeholder is an HTML mailto link. */
		esc_html__( 'If you have any problems with the form, send an email to %1s and a ticket will be created.', 'custom-product-tabs-pro' ),
		'<a href="mailto:support@yikesinc.com">support@yikesinc.com</a>'
	);
	?>
</p>

<p>
	<?php
	echo sprintf(
		/* translators: the placeholders are HTML link tags */
		esc_html__( 'Before submitting a support request, please visit our %1$1sknowledge base%2$2s where we have step-by-step guides and troubleshooting help.', 'custom-product-tabs-pro' ),
		'<a href="https://yikesplugins.com/support/knowledge-base/product/easy-custom-product-tabs-for-woocommerce/" target="_blank">',
		'</a>'
	);
	?>
</p>				

<!-- Support Form Fields -->
<div class="cptpro-settings cptpro-settings-support-container <?php echo empty( $license ) ? 'cptpro-faded' : ''; ?>">

	<!-- Hidden License field -->
	<input type="hidden" name="cptpro-license" id="cptpro-license" value="<?php echo esc_attr( $license ); ?>"/>

	<!-- Name -->
	<div class="cptpro-settings-field field-name">
		<label for="cptpro-name" class="checkbox-label">
			<span class="checkbox-label-text"><?php esc_html_e( 'Name:  ', 'custom-product-tabs-pro' ); ?></span>
			<input type="input" name="cptpro-name" id="cptpro-name" class="cptpro-input-text" />
			<span class="dashicons dashicons-no cptpro-name-error cptpro-error-icons" style="display: none;"></span>
		</label>
	</div>

	<!-- Email -->
	<div class="cptpro-settings-field field-email">
		<label for="cptpro-email" class="checkbox-label">
			<span class="checkbox-label-text"><?php esc_html_e( 'Email: ', 'custom-product-tabs-pro' ); ?></span>
			<input type="input" name="cptpro-email" id="cptpro-email" class="cptpro-input-text" />
			<span class="dashicons dashicons-no cptpro-email-error cptpro-error-icons" style="display: none;"></span>
		</label>
	</div>

	<!-- Topic -->
	<div class="cptpro-settings-field field-topic">
		<label for="cptpro-topic" class="checkbox-label">
			<span class="checkbox-label-text"><?php esc_html_e( 'Topic: ', 'custom-product-tabs-pro' ); ?></span>
			<input type="input" name="cptpro-topic" id="cptpro-topic" class="cptpro-input-text" />
			<span class="dashicons dashicons-no cptpro-topic-error cptpro-error-icons" style="display: none;"></span>
		</label>
	</div>

	<!-- Issue -->
	<div class="cptpro-settings-field field-issue">
		<label for="cptpro-issue" class="checkbox-label">
			<span class="checkbox-label-text"><?php esc_html_e( 'Enter your issue below, please be as detailed as possible.', 'custom-product-tabs-pro' ); ?></span>
			<span class="dashicons dashicons-no cptpro-issue-error cptpro-error-icons" style="display: none;"></span>
			<?php wp_editor( '', 'cptpro_issue', array( 'textarea_name' => 'cptpro_issue', 'media_buttons' => false ) ); ?>
		</label>
	</div>

	<!-- Priority -->
	<div class="cptpro-settings-field field-priority">
		<span class="checkbox-label-text"><?php esc_html_e( 'Priority: ', 'custom-product-tabs-pro' ); ?></span>

		<label for="cptpro-priority-low" class="checkbox-label cptpro-priority-checkbox-label">
			<input type="radio" value="1" name="cptpro-priority" id="cptpro-priority-low" checked="checked" />
			<?php esc_html_e( 'Low', 'custom-product-tabs-pro' ); ?>
		</label>
		<label for="cptpro-priority-medium" class="checkbox-label cptpro-priority-checkbox-label">
			<input type="radio" value="2" name="cptpro-priority" id="cptpro-priority-medium" />
			<?php esc_html_e( 'Medium', 'custom-product-tabs-pro' ); ?>
		</label>
		<label for="cptpro-priority-high" class="checkbox-label cptpro-priority-checkbox-label">
			<input type="radio" value="3" name="cptpro-priority" id="cptpro-priority-high" />
			<?php esc_html_e( 'High', 'custom-product-tabs-pro' ); ?>
		</label>
		<label for="cptpro-priority-urgent" class="checkbox-label cptpro-priority-checkbox-label">
			<input type="radio" value="4" name="cptpro-priority" id="cptpro-priority-urgent" />
			<?php esc_html_e( 'Urgent', 'custom-product-tabs-pro' ); ?>
		</label>
	</div>

	<div class="cptpro-license-save">
		<button type="button" class="button button-primary cptpro-button-primary" id="cptpro-support-request"><?php esc_html_e( 'Send Support Request', 'custom-product-tabs-pro' ); ?></button>
	</div>
</div>
