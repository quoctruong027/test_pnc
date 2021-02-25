<?php
?>
<fieldset class="bwfan-tab-content bwfan-activeTab" setting-id="tab-abandonment">
    <div class="form-group field-input">
        <label><?php esc_html_e( 'Enable Cart Tracking', 'wp-marketing-automations' ); ?></label>
        <div class="field-wrap">
            <div class="wrapper">
                <input class="bwfan_ab_enable" name="bwfan_ab_enable" type="checkbox" value="1" <?php echo empty( $global_settings['bwfan_ab_enable'] ) ? '' : 'checked'; ?> />
                <span class=""><?php esc_html_e( 'Enable it to live capture buyer\'s email and cart details.', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
    </div>
    <div class="bwfan_ab_enable_html <?php echo empty( $global_settings['bwfan_ab_enable'] ) ? 'bwfan_hide' : ''; ?>">
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Wait Period (minutes)', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input type="number" name="bwfan_ab_init_wait_time" placeholder="xx minutes" value="<?php esc_attr_e( $global_settings['bwfan_ab_init_wait_time'] ); ?>" onclick="select()"/>
                </div>
                <span class="hint"><?php esc_html_e( 'Wait for given time before cart is marked as \'Abandoned\' .', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Cart Tracking Notice', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input class="bwfan_ab_email_consent" name="bwfan_ab_email_consent" type="checkbox" value="1" <?php echo empty( $global_settings['bwfan_ab_email_consent'] ) ? '' : 'checked'; ?> />
                    <span class=""><?php esc_attr_e( 'Inform customers when entering their email address that their email and cart data are saved to send abandonment reminders.', 'wp-marketing-automations' ); ?></span>
                </div>
            </div>
        </div>
        <div class="form-group field-input bwfan_ab_email_consent_message <?php echo empty( $global_settings['bwfan_ab_email_consent'] ) ? 'bwfan_hide' : ''; ?>">
            <label><?php esc_html_e( 'Cart Tracking Notice Text', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <textarea cols="40" rows="4" name="bwfan_ab_email_consent_message"><?php echo $global_settings['bwfan_ab_email_consent_message']; //phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
                </div>
                <span class="hint"><?php esc_html_e( 'Use merge tag {{no_thanks label="No Thanks"}} to let users opt out of cart tracking.', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Track on Add to Cart', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input class="bwfan_ab_track_on_add_to_cart" name="bwfan_ab_track_on_add_to_cart" type="checkbox" value="1" <?php echo empty( $global_settings['bwfan_ab_track_on_add_to_cart'] ) ? '' : 'checked'; ?> />
                    <span class=""><?php esc_attr_e( 'Track abandoned cart actions when a product is added to the cart for logged in users (instead of at checkout).', 'wp-marketing-automations' ); ?></span>
                </div>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Exclude Users from Cart Tracking', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input class="bwfan_ab_exclude_users_cart_tracking" name="bwfan_ab_exclude_users_cart_tracking" type="checkbox" value="1" <?php echo empty( $global_settings['bwfan_ab_exclude_users_cart_tracking'] ) ? '' : 'checked'; ?> />
                    <span class=""><?php esc_attr_e( 'Exclude particular users from cart tracking.', 'wp-marketing-automations' ); ?></span>
                </div>
            </div>
        </div>
        <div class="form-group field-input bwfan_ab_exclude_roles_cart_tracking <?php echo empty( $global_settings['bwfan_ab_exclude_users_cart_tracking'] ) ? 'bwfan_hide' : ''; ?>">
            <label><?php esc_html_e( 'Exclude User Roles', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <select class="bwfan_ab_exclude_roles" name="bwfan_ab_exclude_roles[]" multiple>
						<?php
						foreach ( $user_roles as $role => $role_name ) {
							$selected = in_array( $role, $global_settings['bwfan_ab_exclude_roles'], true ) ? 'selected' : '';
							echo '<option value="' . esc_attr__( $role ) . '" ' . esc_attr__( $selected ) . '>' . esc_html__( $role_name ) . '</option>';
						}
						?>
                    </select>
                </div>
                <span class="hint"><?php esc_html_e( 'Select user roles for whom the abandoned carts will not be tracked.', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
        <div class="form-group field-input bwfan_ab_exclude_emails_cart_tracking <?php echo empty( $global_settings['bwfan_ab_exclude_users_cart_tracking'] ) ? 'bwfan_hide' : ''; ?>">
            <label><?php esc_html_e( 'Exclude Specific Emails', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <textarea cols="40" rows="4" name="bwfan_ab_exclude_emails"><?php echo $global_settings['bwfan_ab_exclude_emails']; //phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
                </div>
                <span class="hint">
                    <?php esc_html_e( 'Enter emails, domains or partials to exclude from tracking abandonment separated by comma(,).', 'wp-marketing-automations' ); ?>
                    <br/><?php esc_html_e( 'You can add in full emails (i.e. foo@example.com) or domains (i.e. domain.com), and partials (i.e. john).', 'wp-marketing-automations' ); ?>
                </span>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Mark Cart As Lost Cart After (days)', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input type="number" placeholder="xx days" name="bwfan_ab_mark_lost_cart" value="<?php esc_attr_e( $global_settings['bwfan_ab_mark_lost_cart'] ); ?>" onclick="select()"/>
                </div>
                <span class="hint"><?php esc_attr_e( 'Considers abandoned carts as lost carts after xx days.', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Notice text when cart is restored successfully', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input type="text" name="bwfan_ab_restore_cart_message_success" value="<?php esc_attr_e( $global_settings['bwfan_ab_restore_cart_message_success'] ); ?>"/>
                </div>
                <span class="hint"><?php esc_attr_e( 'Notice displayed when cart is successfully restored. Leave blank in case don’t want to show notice', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>
        <div class="form-group field-input">
            <label><?php esc_html_e( 'Notice text when cart is failed to restore', 'wp-marketing-automations' ); ?></label>
            <div class="field-wrap">
                <div class="wrapper">
                    <input type="text" name="bwfan_ab_restore_cart_message_failure" value="<?php esc_attr_e( $global_settings['bwfan_ab_restore_cart_message_failure'] ); ?>"/>
                </div>
                <span class="hint"><?php esc_attr_e( 'Notice displayed when cart is not restored. Leave blank in case don’t want to show notice', 'wp-marketing-automations' ); ?></span>
            </div>
        </div>

		<?php
		if ( true === apply_filters( 'bwfan_ab_delete_inactive_carts', false ) ) {
			?>
            <div class="form-group field-input">
                <label><?php esc_html_e( 'Remove Inactive Carts Time', 'wp-marketing-automations' ); ?></label>
                <div class="field-wrap">
                    <div class="wrapper">
                        <input type="number" placeholder="xx days" name="bwfan_ab_remove_inactive_cart_time" value="<?php esc_attr_e( $global_settings['bwfan_ab_remove_inactive_cart_time'] ); ?>" onclick="select()"/>
                    </div>
                    <span class="hint"><?php esc_attr_e( 'Remove inactive carts after xx days.', 'wp-marketing-automations' ); ?></span>
                </div>
            </div>
			<?php
		}
		?>

    </div>
</fieldset>
