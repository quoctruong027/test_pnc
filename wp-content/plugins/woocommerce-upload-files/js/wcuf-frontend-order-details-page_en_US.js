var wcuf_max_uploaded_files_number_considered_as_sum_of_quantities = true;
var wcuf_wpml_language = "none";
var wcuf_enable_select_quantity_per_file = false ;
var wcuf_quantity_per_file_label = "Select quantity:";
var wcuf_single_crop_button_label = "Crop";
var wcuf_progressbar_color = "#808080";
var wcuf_crop_disable_zoom_controller = false;
var wcuf_is_order_detail_page = true;
var wcuf_order_id = wcuf_options.order_id;
var wcuf_ajax_action = "upload_file_on_order_detail_page";
var wcuf_ajax_delete_action = "delete_file_on_order_detail_page";
var wcuf_ajax_delete_single_file_action = "delete_single_file_on_order_detail_page";
var wcuf_is_deleting = false;
var wcuf_current_page = wcuf_options.current_page;
var wcuf_checkout_required_message = "Please upload all the required files before saving";
var wcuf_unload_confirm_message = "Please upload all the required files before leaving the page.";
var wcuf_minimum_required_files_message = "You have to upload at least: ";
var wcuf_user_feedback_required_message = "Please fill all required text fields before uploading file(s).";
var wcuf_upload_required_message = "Please upload all required files.";
var wcuf_multiple_uploads_error_message = "Your file upload is incomplete – click on the Upload selected files button or remove the file(s)";
var wcuf_disclaimer_must_be_accepted_message = "You must accept the disclaimer";
var wcuf_image_size_error = "One (or more) file is not an image or it has wrong sizes/DPI. Sizes/DPI allowed: ";
var wcuf_media_length_error = "One (or more) media file length is not valid. ";
var wcuf_media_file_type_error = "One (or more) is not a valid media. ";
var wcuf_image_exact_size_error = " file is not an image or size to big. Size must be: ";
var wcuf_mandatory_crop_error = "Before uploading, you need to crop all the images.";
var wcuf_image_height_text = "max height";
var wcuf_image_width_text = "max width";
var wcuf_image_min_height_text = "min height";
var wcuf_image_min_width_text = "min width";
var wcuf_image_min_dip_text = "min DPI";
var wcuf_image_max_dip_text = "max DPI";
var wcuf_image_aspect_ratio_text = "Aspect ratio";
var wcuf_media_min_length_text = "Min allowed length: ";
var wcuf_media_max_length_text = "Max allowed length: ";
var wcuf_unload_check = false;
var wcuf_file_size_type_header_error = " size is incorrect or its type is not allowed.  ";
var wcuf_file_size_error = " Max allowed size: ";
var wcuf_file_min_size_error = " Min file size: ";
var wcuf_file_num_error = "Maximum of file upload error. You can upload max : ";
var wcuf_image_file_error = "Input file must be an image";
var wcuf_type_allowed_error = "Allowed file types: ";
var wcuf_removed_files_text = "Following files have been removed: ";
var wcuf_ajaxurl = 'https://woocommerce-525238-1675102.cloudwaysapps.com/wp-admin/admin-ajax.php';
var wcuf_success_msg = 'Done! ';
var wcuf_loading_msg = 'Save in progress, please wait...';
var wcuf_delete_msg = 'Deleting, pelase wait... ';
var wcuf_failure_msg = 'An error has occurred.';
var wcuf_delete_file_msg = 'Delete uploaded file(s)';
var wcuf_html5_error = "The HTML5 standards are not fully supported in this browser, please upgrade it or use a more moder browser like Google Chrome or FireFox.";
//var wcuf_file_sizes_error = "The sum of file sizes cannot be greater than 1024MB!";
var wcuf_file_sizes_error = "The sum of file sizes cannot be greater than %s MB!";
var wcuf_file_sizes_min_error = "The sum of file sizes cannot be minor than %s MB!";
var wcuf_max_file_sizes = 1073741824;
var wcuf_max_chunk_size = 1073741824;
var wcuf_delete_single_file_warning_msg = "Are sure you want to delete the file?";
var wcuf_multiple_file_list_tile = "To upload:";
var wcuf_auto_upload_for_multiple_files_upload_field = false;

jQuery(document).ready(function()
{
	jQuery('.wcuf_file_input').val('');
	
	wcuf_show_upload_field_area(); // wcuf-frontend-ui-manager
	wcuf_smooth_scroll_to_upload_area();

	jQuery(document).on('click', '.delete_button', wcuf_delete_file);
	jQuery(document).on('click', '.wcuf_delete_single_file_stored_on_server', wcuf_delete_single_file_on_server);
	jQuery(document).on('click','#wcuf_upload_button', wcuf_save_all_uploads);
	jQuery(document).on('click', '.wcuf_upload_field_button', wcuf_browse_file);
		
	jQuery('#wcuf_show_popup_button').magnificPopup({
          type: 'inline',
		  showCloseBtn:false,
          preloader: false,
            callbacks: {
            /*
			
			beforeOpen: function() {
              console.log("here");
            }*/
			 /* close: function(event) {
				  wcuf_test(event)
				} */
          } 
        });	
	jQuery(document).on('click', '#wcuf_close_popup_alert, #wcuf_leave_page', function(event){ event.preventDefault(); event.stopImmediatePropagation(); jQuery.magnificPopup.close(); return false});
	
	if (window.File && window.FileReader && window.FileList && window.Blob) 
	{
		//Old "string encoding" method
		//jQuery('.wcuf_file_input').on('change' ,wcuf_encode_file);
		
		//jQuery('.wcuf_file_input.wcuf_file_input_multiple').on('change', wcuf_check_multiple_file_uploads_limit_and_crop);
		jQuery(document).on('change','.wcuf_file_input', wcuf_file_input_check);
		jQuery(document).on('drop','.wcuf_upload_drag_and_drop_area', wcuf_on_files_dragged);
		jQuery(document).on('dragover','.wcuf_upload_drag_and_drop_area', wcuf_on_drag_over_event);
		jQuery(document).on('dragleave','.wcuf_upload_drag_and_drop_area', wcuf_on_drag_leave_event);
		jQuery(document).on('click','.wcuf_upload_multiple_files_button', wcuf_start_checks_on_files_info);
	} 
	else 
	{
		jQuery('#wcuf_file_uploads_container').hide();
		wcuf_show_popup_alert(wcuf_html5_error);
	}
	
});
function wcuf_ajax_reload_upload_fields_container()
{
	wcuf_reset_data();
	//var action = wcuf_current_page == 'checkout' ? 'reload_upload_fields_on_checkout' : 'reload_upload_fields'; 
	var action = 'reload_upload_fields_on_checkout';
	if(wcuf_current_page == 'product')
		action = 'reload_upload_fields';
	else if(wcuf_current_page == 'cart')
		action = 'reload_upload_fields_on_cart';
	else if(wcuf_current_page == 'shortcode')
		action = 'reload_shortcode_upload_fields';
	
	if(wcuf_current_page ==  'checkout')
		jQuery( document.body ).trigger( 'update_checkout' ); 
	
	var random = Math.floor((Math.random() * 1000000) + 999);
	
	//ajax_reload_upload_fields
	jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({ opacity: 0 }, 500, function()
	{	
		jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("<h4>"+wcuf_ajax_reloading_fields_text+"</h4>");
		var variation_id = jQuery('input[name=variation_id]').val();
		var formData = new FormData();	
		formData.append('action', action);	
		if( wcuf_current_page == 'checkout' && typeof wcuf_current_paymenth_method !== 'unedfined')
			formData.append('payment_method', wcuf_current_paymenth_method);
		formData.append('product_id', wcuf_current_prduct_id);		
		formData.append('variation_id', jQuery.isNumeric(variation_id) ? variation_id : 0);		
		formData.append('wcuf_wpml_language', wcuf_wpml_language);
		formData.append('current_item_cart_id', wcuf_options.current_item_cart_id);
		jQuery.ajax({
			url: wcuf_ajaxurl+"?nocache="+random,
			type: 'POST',
			data: formData,
			async: true,
			contentType: "application/json; charset=utf-8", 
			dataType: "json", 
			success: function (data) 
			{
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("");  
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').html(data);
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({ opacity: 1 }, 500);	
							
				//Hide add to cart in case of required field 
				//wcuf_hide_add_to_cart_button_if_product_page_and_before_add();
				
			},
			error: function (data) {
				//console.log(data);
				//wcuf_show_popup_alert("Error: "+data);
			},
			cache: false,
			contentType: false,
			processData: false
		});
	});
}
function wcuf_browse_file(event)
{
	event.preventDefault();
	event.stopImmediatePropagation();
	var id = jQuery(event.currentTarget).data('id');
	jQuery("#wcuf_upload_field_"+id).trigger('click');
	return false;
}
function wcuf_delete_single_file_on_server(event)
{
	var id = jQuery(event.currentTarget).data('id');
	var field_id = jQuery(event.currentTarget).data('field-id');
	wcuf_ui_delete_file_on_order_details_page();
	event.preventDefault();
	event.stopImmediatePropagation();
	//wcuf_show_popup_alert(wcuf_delete_single_file_warning_msg);
	//console.log(id);
	
	jQuery.post( wcuf_ajaxurl , { action: wcuf_ajax_delete_single_file_action, id: id, order_id:wcuf_order_id, field_id:field_id } ).done( wcuf_ui_after_delete );
	return false;
}
function wcuf_delete_file(event)
{
	wcuf_is_deleting = true;
	wcuf_ui_delete_file_on_order_details_page();
	event.preventDefault();
	event.stopImmediatePropagation();
	var is_temp = jQuery(event.target).data('temp');
	
	if(is_temp == "yes")
		return;
	
	jQuery.post( wcuf_ajaxurl , { action: wcuf_ajax_delete_action, id: jQuery(event.target).data('id'), order_id:wcuf_order_id, is_temp:is_temp } ).done( wcuf_ui_after_delete /* function(){  window.location.reload(true);    } */);
	return false;
}
function wcuf_delete_temp_file(event)
{
	event.preventDefault();
	event.stopImmediatePropagation();
	//jQuery("#wcuf_deleting_message").animate({'opacity':'1'}, 200); 
	
	var id = jQuery(event.target).data('id');
	var upload_id = jQuery(event.target).data('upload-id');
	var is_temp = jQuery(event.target).data('temp');
	jQuery('#wcuf_delete_button_box_'+upload_id).fadeOut();
	jQuery('#wcuf_deleting_box_'+upload_id).fadeIn(400);
	jQuery('#wcuf_file_name_'+upload_id).fadeOut(400);
	jQuery('#wcuf_upload_button').fadeOut(400);
	
	jQuery.post( wcuf_ajaxurl , { action: wcuf_ajax_delete_action, id: id, order_id:wcuf_order_id, is_temp:is_temp, wcuf_wpml_language:'wcuf_wpml_language' } ).done( function()
				{  
					jQuery('#wcuf_feedback_textarea_'+upload_id).prop('disabled', false);
					jQuery("#wcuf_max_size_notice_"+upload_id).removeClass("wcuf_already_uploaded");
					jQuery("#wcuf_disclaimer_label_"+upload_id).removeClass("wcuf_already_uploaded");
					//jQuery("#wcuf_feedback_textarea_"+upload_id).removeClass("wcuf_already_uploaded");
					
					jQuery("#wcuf_upload_field_button_"+upload_id).removeClass("wcuf_already_uploaded");
					jQuery("#wcuf_upload_multiple_files_button_"+upload_id).removeClass("wcuf_already_uploaded");
					jQuery("#wcuf_file_name"+upload_id).removeClass("wcuf_already_uploaded");
					jQuery('#wcuf_upload_field_button_'+upload_id+', #wcuf_max_size_notice_'+upload_id+', #wcuf_feedback_textarea_'+upload_id+', #wcuf_upload_multiple_files_button_'+id).fadeIn(200); 
					check_which_multiple_files_upload_button_show();
					
					jQuery('#wcuf_file_name_'+upload_id).html("");
					jQuery('#wcuf_delete_button_box_'+upload_id).empty(); 
					jQuery('#wcuf_delete_button_box_'+upload_id).fadeIn(); 					
					jQuery('#wcuf_disclaimer_label_'+upload_id).fadeIn(); 					
					
					jQuery('#wcuf_deleting_box_'+upload_id).fadeOut(400);
					jQuery('#wcuf_upload_button').fadeIn(400);
				});
	return false;
}
function check_which_multiple_files_upload_button_show()
{
	jQuery('.wcuf_upload_multiple_files_button:not(".wcuf_already_uploaded")').each(function(index,elem)
	{
		var id = jQuery(this).data('id');
		if(typeof wcuf_multiple_files_queues !== 'undefined' && typeof wcuf_multiple_files_queues[id] !=='undefined' && wcuf_multiple_files_queues[id].length > 0)
			jQuery(this).fadeIn(500);
	});
}
function wcuf_upload_complete(id)//wcuf_append_file_delete
{
	
	//new method
	wcuf_save_all_uploads(null);
	return;
	
	//old method
	var delete_id = 'wcufuploadedfile_'+id;
	jQuery('#wcuf_file_name_'+id).delay(320).fadeIn(300,function()
	{
		//Smooth scroll
		try{
			jQuery('html, body').animate({
				  scrollTop: jQuery('#wcuf_file_name_'+id).offset().top - 200 //#wcmca_address_form_container ?
				}, 500);
		}catch(error){}
	});
	jQuery('#wcuf_upload_status_box_'+id).delay(300).hide(500);
	
	wcuf_show_control_buttons(id);
	jQuery('#wcuf_delete_button_box_'+id).empty(); 
	jQuery('#wcuf_delete_button_box_'+id).append('<button data-temp="yes" class="button delete_button" data-id="'+delete_id+'" data-upload-id="'+id+'">'+wcuf_delete_file_msg+'</button>');
	jQuery('#wcuf_delete_button_box_'+id).on('click', wcuf_delete_temp_file);	
}
function wcuf_set_bar_background()
{
	jQuery('.wcuf_bar').css('background-color',wcuf_progressbar_color);
}
function wcuf_hide_control_buttons()
{
	
	jQuery('#wcuf_upload_button').fadeOut(0)
	jQuery('.wcuf_crop_container, .wcuf_multiple_files_actions_button_container, .wcuf_disclaimer_label, .wcuf_upload_field_button, .wcuf_upload_multiple_files_button, .wcuf_max_size_notice, .delete_button, .wcuf_feedback_textarea').fadeOut(300);
	
}
function wcuf_show_control_buttons(id)
{
	var current_elem = jQuery('#wcuf_upload_field_'+id);
	var is_multiple = jQuery(current_elem).data('is-multiple-files');
	
	if(is_multiple)
		jQuery('.wcuf_multiple_files_actions_button_container').fadeIn(200);
	
	jQuery('#wcuf_upload_button').fadeIn(200);
	jQuery('.wcuf_multiple_files_actions_button_container, .wcuf_crop_container:not(".wcuf_already_uploaded"):not(".wcuf_not_to_be_showed"), .wcuf_disclaimer_label:not(".wcuf_already_uploaded"), .wcuf_upload_field_button:not(".wcuf_already_uploaded"), .wcuf_max_size_notice:not(".wcuf_already_uploaded"), .wcuf_feedback_textarea:not(".wcuf_already_uploaded"), .delete_button').fadeIn(500);
	jQuery('.wcuf_file_name:not(".wcuf_already_uploaded")').each(function(index, obj)
	{
		if(jQuery(obj).children().length > 0)
			jQuery(obj).fadeIn(500);
	});
	check_which_multiple_files_upload_button_show();
}
function wcuf_show_multiple_files_progress_area(id)
{
	jQuery('#wcuf_multiple_file_progress_container_'+id).fadeIn();
}
function wcuf_reset_loading_ui(id)
{
	wcuf_set_bar_background();;
	jQuery('#wcuf_file_name_'+id).html("");
	jQuery('.wcuf_file_name, wcuf_multiple_file_progress_container_'+id).fadeOut(0);	
	jQuery('#wcuf_bar_'+id+"#wcuf_multiple_file_bar_"+id).css('width', "0%");
	
	wcuf_hide_control_buttons();	
	jQuery("#wcuf_crop_container_"+id).addClass("wcuf_already_uploaded");
	jQuery("#wcuf_upload_field_button_"+id).addClass("wcuf_already_uploaded");
	jQuery("#wcuf_upload_multiple_files_button_"+id).addClass("wcuf_already_uploaded");
	jQuery("#wcuf_file_name"+id).addClass("wcuf_already_uploaded");
	jQuery("#wcuf_disclaimer_label_"+id).addClass("wcuf_already_uploaded");
	
	jQuery("#wcuf_max_size_notice_"+id).addClass("wcuf_already_uploaded");
	jQuery('#wcuf_upload_status_box_'+id).show(400,function()
	{
		//Smooth scroll
		try{
			jQuery('html, body').animate({
				  scrollTop: jQuery('#wcuf_upload_status_box_'+id).offset().top - 200 //#wcmca_address_form_container ?
				}, 500);
		}catch(error){}
	});
	jQuery('#wcuf_delete_button_box_'+id).empty();
	jQuery('#wcuf_status_'+id).html(unescape(wcuf_loading_msg));
	
}
function wcuf_save_all_uploads(evt)
{
	if(evt != null)
	{
		evt.preventDefault();
		evt.stopImmediatePropagation();
	}
	//validation
	 var can_send = true;
	
	/*if(!wcuf_all_required_uploads_have_been_performed())
	{
		wcuf_show_popup_alert(wcuf_checkout_required_message); 
		return false;
	}*/
	
	if(typeof wcuf_multiple_files_queues !== 'undefined')
		for (var key in wcuf_multiple_files_queues) {
		  if (wcuf_multiple_files_queues[key].length != 0) {
			  wcuf_show_popup_alert(wcuf_multiple_uploads_error_message);
			  return false;
		  }
		}
	jQuery('.wcuf_file_input').each(function(index,elem)
	{
		var my_id = jQuery(this).data('id');
		if(jQuery(this).prop('required') && jQuery(this).val() == "")
		{
			can_send = false;
		}
	});
	if(!can_send)
	{
		wcuf_show_popup_alert(wcuf_upload_required_message)
		return;
	} 
	
	//UI
	jQuery('#wcuf_upload_button').fadeOut(200);	
	jQuery('#wcuf_file_uploads_container').fadeOut(200);
	jQuery('#wcuf_progress').delay(250).fadeIn();
	try{
			jQuery('html, body').animate({
				  scrollTop: jQuery('#wcuf_file_uploads_container').offset().top - 200 
				}, 500);
		}catch(error){}
	
	var formData = new FormData();
	formData.append('action', 'save_uploaded_files_on_order_detail_page');
	formData.append('order_id', wcuf_order_id);
	var random = Math.floor((Math.random() * 1000000) + 999);
	
	jQuery.ajax({
		url: wcuf_ajaxurl+"?nocache="+random,
		type: 'POST',
		data: formData,
		async: true,
		dataType : "html",
		contentType: "application/json; charset=utf-8", 
		success: function (data) {
			//window.location.reload(true);	
			wcuf_reload_page_with_anchor();			
		},
		error: function (data) 
		{
			//console.log(data);
			//window.location.reload(true);	
			wcuf_reload_page_with_anchor();
			//wcuf_show_popup_alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	});
	return false;
}
function wcuf_on_drag_over_event(event)
{
	event.preventDefault();  
    event.stopPropagation();
	
	jQuery(event.currentTarget).addClass('wcuf_dragover');
}
function wcuf_on_drag_leave_event(event)
{
	event.preventDefault();  
    event.stopPropagation();
	
	jQuery(event.currentTarget).removeClass('wcuf_dragover');
}
function wcuf_on_files_dragged(event)
{
	event.preventDefault();
	event.stopPropagation();	
	wcuf_on_drag_leave_event(event);
	
	var id =  jQuery(event.currentTarget).data('id');
	var current_elem = jQuery('#wcuf_upload_field_'+id);
	var is_multiple = jQuery(current_elem).data('is-multiple-files');
	/* let object_to_return = {target:{files:new Array()}, currentTarget:current_elem}; */
	
	event.target.files = new Array();
	event.currentTarget = current_elem;
	
	if (event.originalEvent.dataTransfer.items) 
	{
		const end_loop = is_multiple ? event.originalEvent.dataTransfer.items.length : 1;
		// Use DataTransferItemList interface to access the file(s)
		for (var i = 0; i < end_loop; i++) 
		{
		  // If dropped items aren't files, reject them
		  if (event.originalEvent.dataTransfer.items[i].kind === 'file') {
			var file = event.originalEvent.dataTransfer.items[i].getAsFile();
			event.target.files.push(file);
		  }
		}
	  } 
	  else 
	  {
		// Use DataTransfer interface to access the file(s)
		event.target.files = event.originalEvent.dataTransfer.files;
		/* for (var i = 0; i < event.dataTransfer.files.length; i++) 
		{
		  console.log('... file[' + i + '].name = ' + event.dataTransfer.files[i].name);
		  
		} */
	  }
	  
 /*  console.log(event);
  console.log(event.target.files); */
  wcuf_file_input_check(event);
}
function wcuf_file_input_check(evt)
{
	evt.preventDefault();
	evt.stopImmediatePropagation();
	var id = jQuery(evt.target).data('id');
	var is_multiple = jQuery(evt.currentTarget).data('is-multiple-files');
	if(is_multiple && !wcuf_auto_upload_for_multiple_files_upload_field)
		evt.dot_not_invoke_the_callback = true;	//this triggers the autoupload feature (autoupload automatic upload)
	
	//if(jQuery(evt.target).prop('multiple'))
	if(is_multiple)
	{
		wcuf_manage_multiple_file_browse(evt);
		//if(wcuf_auto_upload_for_multiple_files_upload_field)
		{
			wcuf_start_checks_on_files_info(evt);
		}
	}
	else
		wcuf_start_checks_on_files_info(evt);
	
	return false;
}
function wcuf_start_checks_on_files_info(evt)
{
	evt.preventDefault();
	evt.stopImmediatePropagation();
	var id =  jQuery(evt.currentTarget).data('id');
	var current_elem = jQuery('#wcuf_upload_field_'+id);
	var dimensions_logical_operator = current_elem.data("dimensions-logical-operator");
	var max_image_width = current_elem.data("max-width");
	var max_image_height = current_elem.data("max-height");
	var min_image_width = current_elem.data("min-width");
	var min_image_height = current_elem.data("min-height");
	var min_image_dpi = current_elem.data("min-dpi");
	var max_image_dpi = current_elem.data("max-dpi");
	var enable_crop = current_elem.data('enable-crop-editor');
	/* var exact_image_size = current_elem.data("exact-image-size"); */
	
	//var is_multiple = jQuery(evt.currentTarget).hasClass('wcuf_upload_multiple_files_button');
	var is_multiple = current_elem.data('is-multiple-files');
	if(is_multiple)
	{
		if(typeof wcuf_multiple_files_queues === 'undefined' || typeof wcuf_multiple_files_queues[id] === 'undefined')
			return false;
		
		files = wcuf_multiple_files_queues[id];
	}
	else
	{
		files = evt.target.files;
	}
	
	//this triggers the order details specific automatic upload mechanism (on order details there is no need to click the "upload button")
	if(!enable_crop && max_image_width == 0 &&  max_image_height  == 0 &&  min_image_width  == 0 &&  min_image_height  == 0 && min_image_dpi == 0 && max_image_dpi == 0)
		//wcuf_backgroud_file_upload(evt);
		wcuf_check_if_show_cropping_area(evt)
	else
		wcuf_check_image_file_width_and_height(files, evt, current_elem, wcuf_result_on_files_info, max_image_width, max_image_height, min_image_width, min_image_height, min_image_dpi ,max_image_dpi, dimensions_logical_operator);
	
	return false;
}
function wcuf_result_on_files_info(evt, error, img, data)
{
	if(!error)
	{
		// wcuf_check_if_show_cropping_area(evt);
		new WCUFAudioAndVideoLenghtChecker( evt, wcuf_check_if_show_cropping_area);
	}
	else
	{
		var size_string = "<br/>";
		size_string += typeof data.min_image_width !== 'undefined' && data.min_image_width != 0 ? data.min_image_width+"px "+wcuf_image_min_width_text+"<br/>" : ""; 
		size_string += typeof data.max_image_width !== 'undefined' && data.max_image_width != 0 ? data.max_image_width+"px "+wcuf_image_width_text+"<br/>" : ""; 
		size_string += typeof data.min_image_height !== 'undefined' && data.min_image_height != 0 ? data.min_image_height+"px "+wcuf_image_min_height_text+"<br/>": "";
		size_string += typeof data.max_image_height !== 'undefined' && data.max_image_height != 0 ? data.max_image_height+"px "+wcuf_image_height_text+"<br/>" : "";
		size_string += typeof data.min_dpi !== 'undefined' && data.min_dpi != 0 ? data.min_dpi+" "+wcuf_image_min_dip_text+"<br/>" : "";
		size_string += typeof data.max_dpi !== 'undefined' && data.max_dpi != 0 ? data.max_dpi+" "+wcuf_image_max_dip_text+"<br/>" : "";
		size_string += typeof data.ratio_x !== 'undefined' && data.ratio_x != 0 && typeof data.ratio_y !== 'undefined' && data.ratio_y != 0 ? wcuf_image_aspect_ratio_text+" "+data.ratio_x+":"+data.ratio_y+"<br/>" : "";
		
		if(data.images_to_remove.length != 0)
		{
			size_string += "<br/>"+wcuf_removed_files_text+"<ol>";
			jQuery.each(data.images_to_remove, function(index, file_data)
			{
				jQuery("#wcuf_delete_single_file_in_multiple_list_"+file_data.unique_id).trigger('click');
				size_string += "<li>"+file_data.name+"</li>";
			});
			size_string += "</ol>";
		}
		
		//if(!data.exact_image_size)
			wcuf_show_popup_alert(wcuf_image_size_error+" "+size_string);
		/* else
			wcuf_show_popup_alert(img.name+wcuf_image_exact_size_error+size_string); */
		return false;
	}
		
}
function wcuf_check_if_show_cropping_area(evt)
{
	
	var id = jQuery(evt.currentTarget).data('id');
	var enable_crop = jQuery("#wcuf_upload_field_"+id).data('enable-crop-editor');
	//var is_multiple = jQuery(evt.currentTarget).hasClass('wcuf_upload_multiple_files_button');
	var is_multiple = jQuery("#wcuf_upload_field_"+id).data('is-multiple-files');
	/* console.log(is_multiple);
	console.log(enable_crop); */
	if(!is_multiple && enable_crop)
	{
		new wcuf_image_crop(evt, id,wcuf_backgroud_file_upload);
	}			
	else
		wcuf_backgroud_file_upload(evt);
}
function wcuf_all_required_uploads_have_been_performed() //not used
{
	var ok = true;
	
	jQuery('.wcuf_file_input').each(function(index,value)
	{
		var min_files = parseInt(jQuery(this).data('min-files'));
		var data_is_required = jQuery(this).data('required');
		
		//if(jQuery(this).prop('required') && jQuery(this).val() == '') //before incremental upload 
		if(data_is_required && min_files != 0 && jQuery(this).is(":visible"))
			ok =  false;
	});
	return ok;
}
function wcuf_backgroud_file_upload(evt)
{
	evt.preventDefault();
	evt.stopImmediatePropagation();
	var id =  jQuery(evt.currentTarget).data('id');
	var current_elem = jQuery('#wcuf_upload_field_'+id); //jQuery(evt.currentTarget)
	
	var size = current_elem.data('size');
	var min_size = current_elem.data('min-size');
	var file_wcuf_name = current_elem.attr('name');
	var file_wcuf_title = current_elem.data('title');
	var check_disclaimer = current_elem.data('disclaimer');
	var extension =  current_elem.val().replace(/^.*\./, '');
	var extension_accepted = current_elem.attr('accept');
	var file_wcuf_user_feedback = jQuery('#wcuf_feedback_textarea_'+id).val();
	var detect_pdf = current_elem.data('detect-pdf');
	
	var files;
    var file;
	
	//The event if autotamically triggered, is triggered on the "input" element. Otherwise is triggered by the button (upload files button). Both element have different classes associated for "multiple files" upload type
	//var is_multiple = !jQuery( evt.currentTarget ).is("input") ? jQuery(evt.currentTarget).hasClass('wcuf_upload_multiple_files_button') : jQuery(evt.currentTarget).hasClass('wcuf_file_input_multiple');
	var is_multiple = current_elem.data('is-multiple-files');
	
	if(is_multiple)
	{
		if(typeof wcuf_multiple_files_queues === 'undefined' || typeof wcuf_multiple_files_queues[id] === 'undefined')
			return false;
		
		files = wcuf_multiple_files_queues[id];
		file = wcuf_multiple_files_queues[id][0];
	}
	else
	{
		files = evt.target.files;
		if(typeof evt.blob === 'undefined') 
			file = files[0]; 
		else //in case the file (image) has been cropped
			file = evt.blob;
	}
	
	extension =  extension.toLowerCase();
	if(typeof extension_accepted !== 'undefined')
		extension_accepted =  extension_accepted.toLowerCase();
	
	if (location.host.indexOf("sitepointstatic") >= 0) return;
	
	var xhr = new XMLHttpRequest();
	
	
	
	//Checkes
	if(check_disclaimer && !jQuery('#wcuf_disclaimer_checkbox_'+id).prop('checked'))
	{
		wcuf_show_popup_alert(wcuf_disclaimer_must_be_accepted_message);
		/* if(!is_multiple) //On Chrome if the input field is not cleared, it doesn't allow the file selection *** NO NEED, IT HAS BEEN FIXED
			jQuery("#wcuf_upload_field_"+id).val(""); */
		return false;
	}
	if(is_multiple)
	{
		if(!wcuf_check_multiple_file_uploads_limit_and_crop(id, files, file))
		{
			return false;
		}
	}
	if(jQuery('#wcuf_feedback_textarea_'+id).val() == "" && jQuery('#wcuf_feedback_textarea_'+id).prop('required'))
	{
		wcuf_show_popup_alert(wcuf_user_feedback_required_message);
		/* if(!is_multiple) //On Chrome if the input field is not cleared, it doesn't allow the file selection  *** NO NEED, IT HAS BEEN FIXED
			jQuery("#wcuf_upload_field_"+id).val("");  */
		return;
	}
	jQuery('#wcuf_feedback_textarea_'+id).prop('disabled', true);
	
		if (xhr.upload /*&& 
			 (extension_accepted == undefined || extension_accepted.indexOf(extension) > -1) &&
			((size == 0 || file.size <= size) && (min_size == 0 || file.size >= min_size) ) */ ) 
			{
				//UI			
				wcuf_reset_loading_ui(id);
				
				var formData = {'action': wcuf_ajax_action,
							    'title': file_wcuf_title,
								'detect_pdf': detect_pdf,
								'user_feedback': file_wcuf_user_feedback,
								'order_id': wcuf_order_id,
								'wcuf_wpml_language': wcuf_wpml_language
							};
				
				var multiple_file_uploader = new WCUFMultipleFileUploader({/* xhr: xhr, */ upload_field_id:id, form_data: formData, files: files, file: file, file_name:file_wcuf_name});
				document.addEventListener('onWCUFMultipleFileUploaderComplete', function(){wcuf_upload_complete(id);});
				
				if(files.length == 1)
				{
					var tempfile_name  = wcuf_replace_bad_char(file.name);
					jQuery('#wcuf_file_name_'+id).html("<ol><li>"+tempfile_name+"</li><ol>");
				}
				else
				{
					var file_list = "<ol>";
					jQuery('#wcuf_file_name_'+id).html("");
					for(var i = 0; i < files.length; i++)
					{
						var tempfile_name  = wcuf_replace_bad_char(files[i].name);
						file_list += "<li>"+tempfile_name+"</li>";
					}
					file_list += "</ol>";
					jQuery('#wcuf_file_name_'+id).html(file_list)
				}
				if(typeof wcuf_multiple_files_queues !== 'undefined' && typeof wcuf_multiple_files_queues[id] !== 'undefined')
					wcuf_multiple_files_queues[id] = new Array();
				
				multiple_file_uploader.continueUploading();

			}	
			else
			{
				wcuf_display_file_size_or_ext_error(file, size, extension_accepted, min_size, size);
			}
}
function wcuf_replace_bad_char(text)
{
	text = text.replace(/'/g,"");
	text = text.replace(/"/g,"");
	text = text.replace(/ /g,"_");
	text = text.replace(/\+/g,"_");
	text = text.replace(/%/g,"_");
	text = text.replace(/#/g,"_");
	return text;
}
function wcuf_display_file_size_or_ext_error(file, size, extension_accepted, min_size, max_size)
{
	var msg = "";
	
	if(min_size != 0)
		msg += file.name+wcuf_file_min_size_error+(min_size/(1024*1024))+" MB<br/>";
	if(max_size != 0)
		msg += file.name+wcuf_file_size_error+(size/(1024*1024))+" MB<br/>";
	
	if(typeof extension_accepted !== 'undefined')
		msg += wcuf_type_allowed_error+" "+extension_accepted;
	wcuf_show_popup_alert(msg);
}

function wcuf_check_multiple_file_uploads_limit_and_crop(id, files, file)
{
	var fileUpload = jQuery('#wcuf_upload_field_'+id);
	var max_size = fileUpload.data('size');
	var min_size = fileUpload.data('min-size');
	var multiple_files_sum_max_size = fileUpload.data('multiple-files-max-sum-size');
	var multiple_files_sum_min_size = fileUpload.data('multiple-files-min-sum-size');
	var is_multiple_files_field = fileUpload.data('is-multiple-files');
	var max_num = fileUpload.data('max-files');
	var min_num = fileUpload.data('min-files');
	var extension_accepted = fileUpload.attr('accept');
	var error = false;
	var files = is_multiple_files_field ? wcuf_multiple_files_queues[id] : [file];
	var all_files_quantity_sum = 0;
	var sum_all_file_sizes = 0;
	
	if(typeof extension_accepted !== 'undefined')
		extension_accepted =  extension_accepted.toLowerCase();
	
	//Computing number of files and their quantity
	all_files_quantity_sum = files.length;
	if(wcuf_max_uploaded_files_number_considered_as_sum_of_quantities)
	{
		for (var i=0; i<files.length; i++)
		{
			all_files_quantity_sum += typeof files[i].quantity !== 'undefined' && parseInt(files[i].quantity) > 1 ? parseInt(files[i].quantity) - 1 : 0;
		}
		
	}
	
	//if (parseInt($fileUpload.get(0).files.length) > max_num)
	if (max_num != 0 && /* files.length */ all_files_quantity_sum > max_num)
	{
		wcuf_show_popup_alert(wcuf_file_num_error+max_num);
		error = true;
	}
	else if(min_num != 0 && /* files.length */ all_files_quantity_sum < min_num)
	{
		wcuf_show_popup_alert(wcuf_minimum_required_files_message+min_num);
		error = true;
	}
	else 
	{
		var msg="";
		for(var i = 0; i < files.length; i++)
		{
			var name = files[i].name;
			var extension =  name.replace(/^.*\./, '');
			extension =  extension.toLowerCase();
			sum_all_file_sizes += files[i].size;
			if((min_size != 0 && files[i].size < min_size) || (max_size != 0 && files[i].size > max_size) || (extension_accepted != undefined && extension_accepted.indexOf(extension) == -1))
			{
				
				msg += name+wcuf_file_size_type_header_error;
				if(max_size != 0)
					msg += wcuf_file_size_error+(max_size/(1024*1024))+" MB<br/>";
				if(min_size != 0)
					msg += wcuf_file_min_size_error+(min_size/(1024*1024))+" MB<br/>";
				
				if(typeof extension_accepted !== 'undefined')
					msg += wcuf_type_allowed_error+" "+extension_accepted+"<br/>";
				
				msg += "<br/>"; 
			}
		}
		if(msg =="" && is_multiple_files_field && multiple_files_sum_max_size != 0 && sum_all_file_sizes > multiple_files_sum_max_size)
		{
			var size_error_message = wcuf_file_sizes_error.replace("%s", (multiple_files_sum_max_size/(1024*1024)));
			msg = size_error_message; 
		}
		if(msg =="" && is_multiple_files_field && multiple_files_sum_min_size != 0 && sum_all_file_sizes < multiple_files_sum_min_size)
		{
			var size_error_message = wcuf_file_sizes_min_error.replace("%s", (multiple_files_sum_min_size/(1024*1024)));
			msg = size_error_message; 
		}
		
		if(msg != "")
		{
			wcuf_show_popup_alert(msg);
			error = true;
		}
	}
	
	//Crop 
	for (var element in wcuf_multiple_files_mandatory_crop)
	{
		for (var element2 in wcuf_multiple_files_mandatory_crop[element])
		{
			if(!error && wcuf_multiple_files_mandatory_crop[element][element2])
			{
				wcuf_show_popup_alert(wcuf_mandatory_crop_error);
				error = true;
			}
		}
	}
			
	if(error)
	{
		/* event.stopImmediatePropagation();
		event.preventDefault(); */
		return false;
	}
	return true;
}