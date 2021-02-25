<?php
/**
 * Temporary Login settings template
 *
 * @package Temporary Login Without Password
 */

?>
<h2> <?php echo esc_html__( 'Temporary Login Settings', 'temporary-login-without-password' ); ?></h2>
<form method="post">
	<table class="form-table wtlwp-form">
		<tr class="form-field">
			<th scope="row" class="wtlwp-form-row">
				<label for="visible_roles"><?php echo esc_html__( 'Visible Roles', 'temporary-login-without-password' ); ?></label>
				<p class="description"><?php echo esc_html__( 'select roles from which you want to create a temporary login', 'temporary-login-without-password' ); ?></p>

			</th>
			<td>
				<select multiple name="tlwp_settings_data[visible_roles][]" id="visible-roles" class="visible-roles-dropdown">
					<?php Wp_Temporary_Login_Without_Password_Common::tlwp_multi_select_dropdown_roles( $visible_roles ); ?>
				</select>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" class="wtlwp-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Default Role', 'temporary-login-without-password' ); ?></label>
			</th>
			<td>
				<select name="tlwp_settings_data[default_role]" id="default-role" class="default-role-dropdown">
					<?php wp_dropdown_roles( $default_role ); ?>
				</select>
			</td>
		</tr>
        <tr class="form-field">
			<th scope="row" class="wtlwp-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Default Expiry Time', 'temporary-login-without-password' ); ?></label>
			</th>
			<td>
                <select name="tlwp_settings_data[default_expiry_time]" id="default-expiry-time">
					<?php Wp_Temporary_Login_Without_Password_Common::get_expiry_duration_html( $default_expiry_time, array('custom_date') ); ?>
                </select>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" class="wtlwp-form-row"><label for="temporary-login-settings"></label></th>
			<td>
				<p class="submit">
					<input type="submit" class="button button-primary wtlwp-form-submit-button" value="<?php esc_html_e( 'Submit', 'temporary-login-without-password' ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login">
				</p>
			</td>
		</tr>

		<?php wp_nonce_field( 'wtlwp_generate_login_url', 'wtlwp-nonce', true, true ); ?>
	</table>
</form>
