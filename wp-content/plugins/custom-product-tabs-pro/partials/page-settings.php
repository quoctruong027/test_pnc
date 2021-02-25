<?php

// Get our options array.
$settings = get_option( 'cptpro_settings' );

// Seperate Setting From Free Plugin.
$disable_the_content = get_option( 'yikes_cpt_use_the_content' );

// Get our License.
$license = isset( $settings['licensing'] ) && isset( $settings['licensing']['license'] ) ? $settings['licensing']['license'] : '';

// Unpack our settings.
$hide_tab_title                 = isset( $settings['hide_tab_title'] ) && $settings['hide_tab_title'] === true;
$search_wordpress               = isset( $settings['search_wordpress'] ) && $settings['search_wordpress'] === true;
$search_woo                     = isset( $settings['search_woo'] ) && $settings['search_woo'] === true;
$enable_ordering                = isset( $settings['enable_ordering'] ) && $settings['enable_ordering'] === true;
$description_order              = isset( $settings['description_order'] ) ? $settings['description_order'] : 'before';
$additional_information_order   = isset( $settings['additional_information_order'] ) ? $settings['additional_information_order'] : 'before';
$reviews_order                  = isset( $settings['reviews_order'] ) ? $settings['reviews_order'] : 'last';
$disable_description            = isset( $settings['disable_description'] ) && $settings['disable_description'] === true;
$disable_additional_information = isset( $settings['disable_additional_information'] ) && $settings['disable_additional_information'] === true;
$disable_reviews                = isset( $settings['disable_reviews'] ) && $settings['disable_reviews'] === true;
$disable_sslverify              = isset( $settings['disable_sslverify'] ) && $settings['disable_sslverify'] === true;

?>

<div class="wrap woo-ct-admin-page-wrap">
	<h1>
		<span class="dashicons dashicons-exerpt-view"></span> 
		<?php esc_html_e( 'Custom Product Tabs Pro | Settings', 'custom-product-tabs-pro' ); ?>
	</h1>

	<!-- License -->
	<h2><?php esc_html_e( 'License', 'custom-product-tabs-pro' ); ?></h2>

	<!-- License Success Message -->
	<div class="yikes-custom-notice yikes-custom-notice-license-success" style="display: none;">
		<span class="yikes-custom-notice-content yikes-custom-notice-content-license-success">
			<span class="dashicons dashicons-yes"></span> 
			<span class="yikes-custom-notice-message"><?php esc_html_e( 'Your license has been successfully activated.', 'custom-product-tabs-pro' ); ?></span>
			<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
		<span>
	</div>

	<!-- License Failure Message -->
	<div class="yikes-custom-notice yikes-custom-notice-license-failure" style="display: none;">
		<span class="yikes-custom-notice-content yikes-custom-notice-content-license-failure">
			<span class="dashicons dashicons-no-alt"></span> 
			<span class="yikes-custom-notice-message"><?php esc_html_e( 'Your license is not valid. Please try again in a few minutes. If the issue persists, please email us at support@yikesinc.com.', 'custom-product-tabs-pro' ); ?></span>
			<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
		<span>
	</div>

	<!-- License Container -->
	<div class="cptpro-settings cptopro-settings-license-container">

		<div class="cptpro-settings-field field-hide-tab-title">
			<p class="cptpro-field-description">
				<?php esc_html_e( 'To receive updates and premium support, please enter your license key.', 'custom-product-tabs-pro' ); ?>		
			</p>
			<label for="cptpro-license" class="checkbox-label">
				<input type="input" value="<?php echo esc_html( $license ); ?>" name="cptpro-license" id="cptpro-license" class="cptpro-input-text" />
				<!-- <span class="checkbox-label-text"><?php esc_html_e( 'Enter your license key.', 'custom-product-tabs-pro' ); ?></span> -->
				<span style="display: none;" class="dashicons dashicons-thumbs-up license-active" title="<?php esc_attr_e( 'Your license is active', 'custom-product-tabs-pro' ); ?>"></span>
				<span class="dashicons dashicons-thumbs-down license-inactive" title="<?php esc_attr_e( 'Your license is not yet active', 'custom-product-tabs-pro' ); ?>"></span>
				<span style="display: none;" class="license-spinner-gif"><img src="<?php echo esc_url( admin_url( 'images/loading.gif' ) ); ?>" alt="License Details Loading"/></span>
			</label>

			<div class="cptpro-license-details cptpro-settings" style="display: none;">
				<div>
					<div class="cptpro-license-customer-label cptpro-license-field-label"><strong>Customer:</strong></div>
					<div class="cptpro-license-customer-value cptpro-license-field-value"></div>
				</div>
				<div>
					<div class="cptpro-license-limit-label cptpro-license-field-label">License Limit: </div>
					<div class="cptpro-license-limit-value cptpro-license-field-value"></div>
				</div>
				<!-- <span class="cptpro-license-site-count-label">Active Site Count: </span>
				<span class="cptpro-license-site-count-value"></span> -->
				<div>
					<div class="cptpro-license-expires-label cptpro-license-field-label">License Expires: </div>
					<div class="cptpro-license-expires-value cptpro-license-field-value"></div>
				</div>
			</div>
		</div>

		<div class="cptpro-license-save">
			<!-- Activate License -->
			<button type="button" class="button button-primary cptpro-button-primary" id="cptpro-license-activate">
				<?php esc_html_e( 'Activate License', 'custom-product-tabs-pro' ); ?>
			</button>

			<!-- Deactivate License -->
			<button style="display: none;" type="button" class="button button-primary cptpro-button-primary" id="cptpro-license-deactivate">
				<?php esc_html_e( 'Deactivate License', 'custom-product-tabs-pro' ); ?>		
			</button>
		</div>
	</div>

	<!-- Settings -->
	<h2><?php esc_html_e( 'Tab Settings', 'custom-product-tabs-pro' ); ?></h2>

	<!-- Settings Success Message -->
	<div class="yikes-custom-notice yikes-custom-notice-success" style="display: none;">
		<span class="yikes-custom-notice-content yikes-custom-notice-content-success">
			<span class="dashicons dashicons-yes"></span> 
			<span class="yikes-custom-notice-message"><?php esc_html_e( 'Your settings have been successfully saved.', 'custom-product-tabs-pro' ); ?></span>
			<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
		<span>
	</div>

	<!-- Settings Failure Message -->
	<div class="yikes-custom-notice yikes-custom-notice-failure" style="display: none;">
		<span class="yikes-custom-notice-content yikes-custom-notice-content-failure">
			<span class="dashicons dashicons-no-alt"></span> 
			<span class="yikes-custom-notice-message"><?php esc_html_e( 'Something went wrong...', 'custom-product-tabs-pro' ); ?></span>
			<span class="dashicons dashicons-dismiss yikes-custom-dismiss" title="<?php esc_attr_e( 'Dismiss', 'custom-product-tabs-pro' ); ?>"></span><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'custom-product-tabs-pro' ); ?></span>
		<span>
	</div>

	<!-- Settings Container -->
	<div class="cptpro-settings cptpro-settings-settings-container">

		<span style="display: none;" class="settings-spinner-gif"><img src="<?php echo esc_url( admin_url( 'images/loading.gif' ) ); ?>" alt="Settings Saving"/></span>

		<h3><?php esc_html_e( 'Tab Titles', 'custom-product-tabs-pro' ); ?></h3>

		<div class="cptpro-settings-field field-hide-tab-title">
			<label for="hide-tab-title" class="checkbox-label">
				<input type="checkbox" value="1" name="hide-tab-title" id="hide-tab-title" <?php echo $hide_tab_title ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Remove tab title from tab content.', 'custom-product-tabs-pro' ); ?></span>
			</label>
			<p>
				<?php esc_html_e( 'Remove the tab title from being repeated in the tab content area.', 'custom-product-tabs-pro' ); ?>
			</p>
			<p>
				<img src="<?php echo esc_url( YIKES_Custom_Product_Tabs_Pro_URI . 'images/hide-title.png' ); ?>" />
			</p>
		</div>

		<hr />

		<h3><?php esc_html_e( 'Tab Order', 'custom-product-tabs-pro' ); ?></h3>

		<div class="cptpro-settings-field field-enable-ordering">
			<label for="enable-ordering" class="checkbox-label">
				<input type="checkbox" value="1" name="enable-ordering" id="enable-ordering" <?php echo $enable_ordering ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Apply ordering to custom product tabs.', 'custom-product-tabs-pro' ); ?></span>
			</label>

			<p>
				<?php esc_html_e( 'Enable drag-and-drop ordering of saved tabs and conditional ordering of default tabs.', 'custom-product-tabs-pro' ); ?>
			</p>
		</div>

		<div id="cptpro-ordering-subfields">
			<div class="cptpro-settings-field field-description-order">
				<label for="description-order">
					<span class="checkbox-label-text ordering-select-text"><?php esc_html_e( 'Description tab should appear:', 'custom-product-tabs-pro' ); ?></span>
					<select id="description-order" name="description-order">
						<?php echo YIKES_Custom_Product_Tabs_Pro_Settings::default_tab_order_dropdown( $description_order ); ?>
					</select>
				</label>
			</div>

			<div class="cptpro-settings-field field-additional-information-order">
				<label for="additional-information-order">
					<span class="checkbox-label-text ordering-select-text"><?php esc_html_e( 'Additional information tab should appear:', 'custom-product-tabs-pro' ); ?></span>
					<select id="additional-information-order" name="additional-information-order">
						<?php echo YIKES_Custom_Product_Tabs_Pro_Settings::default_tab_order_dropdown( $additional_information_order ); ?>
					</select>
				</label>
			</div>

			<div class="cptpro-settings-field field-reviews-order">
				<label for="reviews-order">
					<span class="checkbox-label-text ordering-select-text"><?php esc_html_e( 'Reviews tab should appear:', 'custom-product-tabs-pro' ); ?></span>
					<select id="reviews-order" name="reviews-order">
						<?php echo YIKES_Custom_Product_Tabs_Pro_Settings::default_tab_order_dropdown( $reviews_order ); ?>
					</select>
				</label>
			</div>
		</div>

		<hr />

		<h3><?php esc_html_e( 'Disable Default Tabs', 'custom-product-tabs-pro' ); ?></h3>

		<div class="cptpro-settings-field field-disable-description">
			<label for="disable-description" class="checkbox-label">
				<input type="checkbox" value="1" name="disable-description" id="disable-description" data-tab="description" <?php echo $disable_description ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Disable the description tab on all products.', 'custom-product-tabs-pro' ); ?></span>
			</label>
		</div>

		<div class="cptpro-settings-field field-disable-additional-information">
			<label for="disable-additional-information" class="checkbox-label">
				<input type="checkbox" value="1" name="disable-additional-information" id="disable-additional-information" data-tab="additional-information" <?php echo $disable_additional_information ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Disable the additional information tab on all products.', 'custom-product-tabs-pro' ); ?></span>
			</label>
		</div>

		<div class="cptpro-settings-field field-disable-reviews">
			<label for="disable-reviews" class="checkbox-label">
				<input type="checkbox" value="1" name="disable-reviews" id="disable-reviews" data-tab="reviews" <?php echo $disable_reviews ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Disable the reviews tab on all products.', 'custom-product-tabs-pro' ); ?></span>
			</label>
		</div>

		<hr />

		<h3><?php esc_html_e( 'Search', 'custom-product-tabs-pro' ); ?></h3>

		<p>
			<?php esc_html_e( 'By default, custom tab content is not included in WordPress or WooCommerce search, adjust those settings below.', 'custom-product-tabs-pro' ); ?>
		</p>

		<div class="cptpro-settings-field field-search-wordpress">
			<label for="search-wordpress" class="checkbox-label">
				<input type="checkbox" value="1" name="search-wordpress" id="search-wordpress" <?php echo $search_wordpress ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Include custom tab content in the WordPress search.', 'custom-product-tabs-pro' ); ?></span>
			</label>
		</div>

		<div class="cptpro-settings-field field-search-woo">
			<label for="search-woo" class="checkbox-label">
				<input type="checkbox" value="1" name="search-woo" id="search-woo" <?php echo $search_woo ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Include custom tab content in the WooCommerce search widget.', 'custom-product-tabs-pro' ); ?></span>
			</label>
		</div>

		<hr/>

		<h3><?php esc_html_e( 'Advanced', 'custom-product-tabs-pro' ); ?></h3>

		<div class="cptpro-settings-field field-disable-sslverify">
			<label for="disable-sslverify" class="checkbox-label">
				<input type="checkbox" value="1" name="disable-sslverify" id="disable-sslverify" <?php echo $disable_sslverify ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Turn SSL verification off.', 'custom-product-tabs-pro' ); ?></span>
			</label>

			<p>
				<?php esc_html_e( 'Check this if experiencing SSL issues updating or verifying your license.', 'custom-product-tabs-pro' ); ?>
			</p>
		</div>

		<div class="cptpro-settings-field field-disable-the-content">
			<label for="disable-the-content" class="checkbox-label">
				<input type="checkbox" value="1" name="disable-the-content" id="disable-the-content" <?php echo $disable_the_content === 'true' ? 'checked="checked"' : ''; ?> />
				<span class="checkbox-label-text"><?php esc_html_e( 'Page Builder Compatibility.', 'custom-product-tabs-pro' ); ?></span>
			</label>

			<p>
				<?php esc_html_e( 'If you\'re using a page builder like Elementor turn on this setting if you experience issues.', 'custom-product-tabs-pro' ); ?>
			</p>
		</div>

		<div class="cptpro-settings-save">
			<button type="button" class="button button-primary cptpro-button-primary" id="cptpro-settings-save"><?php esc_html_e( 'Save Settings', 'custom-product-tabs-pro' ); ?></button>
		</div>
	</div>
</div>
