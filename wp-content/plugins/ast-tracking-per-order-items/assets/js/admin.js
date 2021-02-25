jQuery(document).on("submit", "#wc_ast_addons_form", function( ){	
	var form = jQuery(this);
	form.find(".spinner").addClass("active");
	jQuery('#ast_tpi_license_message').hide();
	var action = jQuery('#ast-license-action').val();			
	jQuery.ajax({
		url: tpi_ajax_object.ajax_url,		
		data: form.serialize(),
		type: 'POST',
		success: function(data) {
			form.find(".spinner").removeClass("active");
			jQuery('#ast_tpi_license_message').show();
			//console.log(data.success);
			var btn_value = 'Activate';
			if(data.success == true){
				if(action == 'tracking_per_item_license_activate'){
					var btn_value = 'Deactivate';
					jQuery('#ast-license-action').val('esre_license_deactivate');
					jQuery('#ast_tpi_license_message').html('<span style="color:green;">Congratulation, your license successful activated</span>');
					jQuery('.activated').show();
					window.location.reload();
				} else {
					jQuery('#ast-license-action').val('esre_license_activate');
					jQuery('#ast_product_license_key').val('');
					jQuery('#ast_product_license_email').val('');
					jQuery('#ast_tpi_license_message').html('<span style="color:green;">Congratulation, your license successful deactivated</span>');
					jQuery('.activated').hide();
					window.location.reload();				
				}
			} else {
				jQuery('#ast_tpi_license_message').html('<span style="color:red;">'+data.error+'</span>');
			}
			
			jQuery('#saveS').prop('disabled', false).val(btn_value);
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR.status);			
		}
	});
	return false;
});

jQuery(document).on("submit", "#tpi_settings_form", function( ){	
	var form = jQuery(this);
	form.find(".spinner").addClass("active");
	jQuery.ajax({
		url: tpi_ajax_object.ajax_url,		
		data: form.serialize(),
		type: 'POST',
		success: function(response) {
			form.find(".spinner").removeClass("active");
			jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
			jQuery("#ast_settings_snackbar").text(tpi_ajax_object.i18n.data_saved);			
			setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR.status);			
		}
	});
	return false;
});