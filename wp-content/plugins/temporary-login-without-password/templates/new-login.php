<?php
/**
 * Create New Temporary Login template
 *
 * @package Temporary Login Without Password
 */

?>
<h2> <?php echo esc_html__( 'Create a new Temporary Login', 'temporary-login-without-password' ); ?></h2>
<form method="post">
	<table class="form-table wtlwp-form">
		<tr class="form-field form-required">
			<th scope="row" class="wtlwp-form-row">
				<label for="user_email"><?php echo esc_html__( 'Email*', 'temporary-login-without-password' ); ?> </label>
			</th>
			<td>
				<input name="wtlwp_data[user_email]" type="text" id="user_email" value="" aria-required="true" maxlength="60" class="wtlwp-form-input"/>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row" class="wtlwp-form-row">
				<label for="user_first_name"><?php echo esc_html__( 'First Name', 'temporary-login-without-password' ); ?> </label>
			</th>
			<td>
				<input name="wtlwp_data[user_first_name]" type="text" id="user_first_name" value="" aria-required="true" maxlength="60" class="wtlwp-form-input"/>
			</td>
		</tr>

		<tr class="form-field form-required">
			<th scope="row" class="wtlwp-form-row">
				<label for="user_last_name"><?php echo esc_html__( 'Last Name', 'temporary-login-without-password' ); ?> </label>
			</th>
			<td>
				<input name="wtlwp_data[user_last_name]" type="text" id="user_last_name" value="" aria-required="true" maxlength="60" class="wtlwp-form-input"/>
			</td>
		</tr>

		<?php if ( is_network_admin() ) { ?>
			<tr class="form-field form-required">
				<th scope="row" class="wtlwp-form-row">
					<label for="user_super_admin"><?php echo esc_html__( 'Super Admin', 'temporary-login-without-password' ); ?> </label>
				</th>
				<td>
					<input type="checkbox" id="user_super_admin" name="wtlwp_data[super_admin]"><?php echo esc_html__( 'Grant this user super admin privileges for the Network.', 'temporary-login-without-password' ); ?>
				</td>
			</tr>
		<?php } else { ?>
			<tr class="form-field">
				<th scope="row" class="wtlwp-form-row">
					<label for="adduser-role"><?php echo esc_html__( 'Role', 'temporary-login-without-password' ); ?></label>
				</th>
				<td>
					<select name="wtlwp_data[role]" id="user-role">
						<?php Wp_Temporary_Login_Without_Password_Common::tlwp_dropdown_roles( $visible_roles, $default_role ); ?>
					</select>
				</td>
			</tr>
		<?php } ?>

		<tr class="form-field">
			<th scope="row" class="wtlwp-form-row">
				<label for="adduser-role"><?php echo esc_html__( 'Expiry', 'temporary-login-without-password' ); ?></label>
			</th>
			<td>
				<span id="expiry-date-selection">
						<select name="wtlwp_data[expiry]" id="new-user-expiry-time">
							<?php Wp_Temporary_Login_Without_Password_Common::get_expiry_duration_html( $default_expiry_time ); ?>
						</select>
				</span>

				<span style="display:none;" id="new-custom-date-picker">
					<input type="date" id="datepicker" name="wtlwp_data[custom_date]" value="" class="new-custom-date-picker"/>
				</span>

			</td>
		</tr>

        <tr class="form-field">
            <th scope="row" class="wtlwp-form-row">
                <label for="language"><?php echo esc_html__( 'Language', 'temporary-login-without-password' ); ?></label>
            </th>
            <td scope="row" class="">
                <?php
                    wp_dropdown_languages(array('name' => 'wtlwp_data[locale]', 'selected' => get_locale()));
                ?>
            </td>
        </tr>
		<tr class="form-field">
			<th scope="row" class="wtlwp-form-row"><label for="adduser-role"></label></th>
			<td>
				<p class="submit">
					<input type="submit" class="button button-primary wtlwp-form-submit-button" value="<?php esc_html_e( 'Submit', 'temporary-login-without-password' ); ?>" class="button button-primary" id="generatetemporarylogin" name="generate_temporary_login"> <?php esc_html_e( 'or', 'temporary-login-without-password' ); ?>
					<span class="cancel-new-login-form" id="cancel-new-login-form"><?php esc_html_e( 'Cancel', 'temporary-login-without-password' ); ?></span>
				</p>
			</td>
		</tr>
		<?php wp_nonce_field( 'wtlwp_generate_login_url', 'wtlwp-nonce', true, true ); ?>
	</table>
</form>
