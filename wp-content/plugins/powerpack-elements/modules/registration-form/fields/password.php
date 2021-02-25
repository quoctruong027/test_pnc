<div class="pp-rf-field-inner">
	<input
		type="password" 
		class="pp-rf-control elementor-field elementor-size-sm form-field-password elementor-field-textual" 
		name="<?php echo $field_name; ?>" 
		id="<?php echo $field_id; ?>" 
		value="" 
		placeholder="<?php echo $field['placeholder']; ?>" 
		autocomplete="off" 
		autocorrect="off" 
		autocapitalize="off" 
		spellcheck="false" 
		aria-required="true" 
		aria-describedby="login_error" 
	/>
	<?php if ( 'yes' === $field['password_toggle'] ) { ?>
	<button type="button" class="pp-rf-toggle-pw hide-if-no-js" aria-label="<?php _e( 'Show password', 'powerpack' ); ?>">
		<span class="fa far fa-eye" aria-hidden="true"></span>
	</button>
	<?php } ?>
</div>
<div class="pp-rf-pws-status"></div>