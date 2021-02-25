<?php if ( isset( $recaptcha_site_key ) && ! empty( $recaptcha_site_key ) ) { ?>
	<div id="<?php echo $id; ?>-pp-grecaptcha" class="pp-grecaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-validate="<?php echo $recaptcha_validate_type; ?>" data-theme="<?php echo $recaptcha_theme; ?>"></div>
<?php } else { ?>
	<div><?php echo pp_get_recaptcha_desc(); ?></div>
<?php } ?>
