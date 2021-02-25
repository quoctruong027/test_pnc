jQuery(document).on("click", "#wc_ast_status_delivered", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});

jQuery(document).on("click", "#wc_ast_status_shipped_active", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});

jQuery(document).on("click", "#wc_ast_status_partial_shipped", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});
jQuery(document).on("click", "#wc_ast_status_updated_tracking", function(){
	if(jQuery(this).prop("checked") == true){
        jQuery(this).closest('tr').removeClass('disable_row');				
    } else{
		jQuery(this).closest('tr').addClass('disable_row');
	}	
});

jQuery( document ).ready(function() {	
	jQuery(".woocommerce-help-tip").tipTip();
	
	if(jQuery('#wc_ast_status_delivered').prop("checked") == true){
		jQuery('.status_label_color_th').show();		
	} else{
		jQuery('.status_label_color_th').hide();		
	}

	if(jQuery('#wc_ast_status_partial_shipped').prop("checked") == true){
		jQuery('.partial_shipped_status_label_color_th').show();		
	} else{
		jQuery('.partial_shipped_status_label_color_th').hide();			
	}	
		
	jQuery('.color_field input').wpColorPicker();		
});
jQuery(document).on("change", "#wc_ast_status_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-delivered').css('color',font_color);
});
jQuery(document).on("change", "#wc_ast_shipped_status_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-shipped').css('color',font_color);
});
jQuery(document).on("change", "#wc_ast_status_partial_shipped_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-partially-shipped').css('color',font_color);
});
jQuery(document).on("change", "#wc_ast_status_updated_tracking_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-updated-tracking').css('color',font_color);
});