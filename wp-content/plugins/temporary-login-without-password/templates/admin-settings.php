<?php
/**
 * Admin Settings Template
 *
 * @package Temporary Login Without Password
 */

?>
<h2 class="nav-tab-wrapper">
    <?php if(! $is_temporary_login)  { ?>
        <a href="<?php echo esc_url( admin_url( 'users.php?page=wp-temporary-login-without-password&tab=home' ) ); ?>" class="nav-tab <?php echo 'home' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Temporary Logins', 'temporary-login-without-password' ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'users.php?page=wp-temporary-login-without-password&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Settings', 'temporary-login-without-password' ); ?></a>
	<?php } ?>
    <a href="<?php echo esc_url( admin_url( 'users.php?page=wp-temporary-login-without-password&tab=system-info' ) ); ?>" class="nav-tab <?php echo 'system-info' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'System Info', 'temporary-login-without-password' ); ?></a>
</h2>

<?php if ( 'home' === $active_tab && !$is_temporary_login ) { ?>
    <div class="wrap wtlwp-settings-wrap" id="temporary-logins">
        <h2>
			<?php echo esc_html__( 'Temporary Logins', 'temporary-login-without-password' ); ?>
            <span class="page-title-action" id="add-new-wtlwp-form-button"><?php esc_html_e( 'Create New', 'temporary-login-without-password' ); ?></span>
        </h2>
        <div class="wtlwp-settings">
            <!-- Add New Form Start -->

            <div class="wrap new-wtlwp-form" id="new-wtlwp-form">
				<?php include WTLWP_PLUGIN_DIR . '/templates/new-login.php'; ?>
            </div>

			<?php if ( $do_update ) { ?>

                <div class="wrap update-wtlwp-form" id="update-wtlwp-form">
					<?php include WTLWP_PLUGIN_DIR . '/templates/update-login.php'; ?>
                </div>

			<?php } ?>

			<?php $wtlwp_generated_url = esc_url( $wtlwp_generated_url );
			if ( ! empty( $wtlwp_generated_url ) ) { ?>

                <div class="wrap generated-wtlwp-login-link" id="generated-wtlwp-login-link">
                    <p>
						<?php esc_attr_e( "Here's a temporary login link", 'temporary-login-without-password' ); ?>
                    </p>
                    <input id="wtlwp-click-to-copy-btn" type="text" class="wtlwp-wide-input" value="<?php echo esc_url( $wtlwp_generated_url ); ?>">
                    <button class="wtlwp-click-to-copy-btn" data-clipboard-action="copy" data-clipboard-target="#wtlwp-click-to-copy-btn"><?php echo esc_html__( 'Click To Copy', 'temporary-login-without-password' ); ?></button>
                    <span id="copied-text-message-wtlwp-click-to-copy-btn"></span>
                    <p>
						<?php
						esc_attr_e( 'User can directly login to WordPress admin panel without username and password by opening this link.', 'temporary-login-without-password' );
						if ( ! empty( $user_email ) ) {
							/* translators: %s: mailto link */
							echo " " . __( sprintf( '<a href="%s">Email</a> temporary login link to user', $mailto_link ), 'temporary-login-without-password' ); //phpcs:ignore
						}
						?>
                    </p>

                </div>
			<?php } ?>
            <!-- Add New Form End -->

            <!-- List All Generated Logins Start -->
            <div class="wrap list-wtlwp-logins" id="list-wtlwp-logins">
				<?php load_template( WTLWP_PLUGIN_DIR . '/templates/list-temporary-logins.php' ); ?>
            </div>
            <!-- List All Generated Logins End -->
        </div>
    </div>
<?php } elseif ( 'settings' === $active_tab && !$is_temporary_login) { ?>
    <div class="wrap list-wtlwp-logins" id="wtlwp-logins-settings">
		<?php include WTLWP_PLUGIN_DIR . '/templates/temporary-logins-settings.php'; ?>
    </div>

<?php } else {  ?>
    <div class="wrap tlwp-sytem-info" id="tlwp-system-info">
		<?php include WTLWP_PLUGIN_DIR . '/templates/system-info.php'; ?>
    </div>
<?php }  ?>
