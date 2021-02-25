<?php
/* error_reporting(0);
ini_set('display_errors', 0);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' ); */

global $wcuf_option_model, $wcuf_text_model, $sitepress;
$all_options = $wcuf_option_model->get_all_options();
$button_texts  = $wcuf_text_model->get_button_texts(true);
$post_max_size = WCUF_File::return_bytes(ini_get('post_max_size'));
$max_chunk_size = WCUF_File::return_bytes($wcuf_option_model->get_php_settings('size_that_can_be_posted'));
$bad_chars = array('"', "'");

/*** set the content type header ***/
/* header("Content-type: application/javascript"); */
?>
var wcuf_is_force_reloading = false;
var wcuf_max_uploaded_files_number_considered_as_sum_of_quantities = <?php echo $all_options['max_uploaded_files_number_considered_as_sum_of_quantities'] ? 'true':'false'; ?>;
var wcuf_wpml_language = "<?php if(isset($sitepress)) echo $sitepress->get_current_language(); else echo "none"; ?>";
var wcuf_enable_select_quantity_per_file = <?php echo $all_options['enable_quantity_selection'] ? 'true':'false'; ?> ;
var wcuf_show_required_upload_add_to_cart_warning_message = <?php echo $all_options['show_required_upload_add_to_cart_warning_message'] ? 'true':'false'; ?> ;
var wcuf_quantity_per_file_label = "<?php echo $button_texts['select_quantity_label']; ?>";
var wcuf_single_crop_button_label = "<?php  _e('Crop', 'woocommerce-files-upload'); ?>";
var wcuf_force_require_check_before_adding_item_to_cart = <?php if($all_options['force_require_check_befor_adding_item_to_car']=='yes') echo 'true'; else echo 'false'; ?>;
var wcuf_allow_user_to_leave_page_in_case_of_required_field = '<?php echo $all_options['allow_user_to_leave_page_in_case_of_required_field']; // see wcuf_leave_the_page_after_warning_popup_prompt() method comments. ?>';
var wcuf_progressbar_color = "<?php echo $all_options['bar_color'] ?>";
var wcuf_crop_disable_zoom_controller = <?php echo $all_options['crop_disable_zoom_controller'] ? 'true' : 'false' ?>;
var wcuf_ajax_reloading_fields_text = "<?php echo addslashes($button_texts['loading_upload_field_message']); ?>";
var wcuf_current_prduct_id = wcuf_options.current_page == 'product'  ? wcuf_options.current_product_id : 0;
var wcuf_is_order_detail_page = false;
var wcuf_order_id = "0";
var wcuf_ajax_action = "upload_file_during_checkout_or_product_page";
var wcuf_ajax_delete_action = "delete_file_during_checkout_or_product_page";
var wcuf_ajax_delete_single_file_action = "delete_single_file_during_checkout_or_product_page";
var wcuf_is_deleting = false;
var wcuf_current_page = wcuf_options.current_page;
var wcuf_minimum_required_files_message = "<?php echo str_replace($bad_chars, "", __('You have to upload at least: ', 'woocommerce-files-upload')); ?>";
var wcuf_checkout_required_message = "<?php echo str_replace($bad_chars, "", __('Please upload all the required files before proceding to checkout', 'woocommerce-files-upload')); ?>";
var wcuf_user_feedback_required_message = "<?php echo str_replace($bad_chars, "", __('Please fill all required text fields before uploading file(s).', 'woocommerce-files-upload')); ?>";
var wcuf_multiple_uploads_error_message = "<?php echo $button_texts['incomplete_files_upload_message']; ?>";
var wcuf_unload_confirm_message = "<?php echo str_replace($bad_chars, "", __('Please upload all the required files before leaving the page.', 'woocommerce-files-upload')); ?>";
var wcuf_disclaimer_must_be_accepted_message = "<?php echo str_replace($bad_chars, "", __('You must accept the disclaimer', 'woocommerce-files-upload')); ?>";
var wcuf_unload_check = wcuf_options.current_page == 'product' || wcuf_options.current_page == 'cart' || wcuf_options.current_page == 'shortcode';
var wcuf_exist_a_field_before_add_to_cart = wcuf_options.current_page == 'product' && wcuf_options.exists_a_field_to_show_before_adding_item_to_cart == 'true'; 
var wcuf_item_has_been_added_to_cart = wcuf_options.current_page == 'product' && wcuf_options.has_already_added_to_cart == 'true';
var wcuf_file_size_type_header_error = "<?php echo str_replace($bad_chars, "", __(' size is incorrect or its type is not allowed.  ', 'woocommerce-files-upload')); ?>";
var wcuf_mandatory_crop_error = "<?php echo str_replace($bad_chars, "", __('Before uploading, you need to crop all the images.', 'woocommerce-files-upload')); ?>";
var wcuf_file_size_error = "<?php echo str_replace($bad_chars, "", __(' Max allowed size: ', 'woocommerce-files-upload')); ?>";
var wcuf_file_min_size_error = "<?php echo str_replace($bad_chars, "", __(' Min file size: ', 'woocommerce-files-upload')); ?>";
var wcuf_image_size_error = "<?php echo str_replace($bad_chars, "", __('One (or more) file is not an image or it has wrong sizes/DPI. Sizes/DPI allowed:', 'woocommerce-files-upload')); ?>";
var wcuf_media_length_error = "<?php echo str_replace($bad_chars, "", __('One (or more) media file length is not valid. ', 'woocommerce-files-upload')); ?>";
var wcuf_image_exact_size_error = "<?php echo str_replace($bad_chars, "", __(' file is not an image or size to big. Size must be: ', 'woocommerce-files-upload')); ?>";
var wcuf_media_file_type_error = "<?php echo str_replace($bad_chars, "", __('One (or more) is not a valid media. ', 'woocommerce-files-upload')); ?>";
var wcuf_image_height_text = "<?php echo str_replace($bad_chars, "", __('max height', 'woocommerce-files-upload')); ?>";
var wcuf_image_width_text = "<?php echo str_replace($bad_chars, "", __('max width', 'woocommerce-files-upload')); ?>";
var wcuf_image_min_height_text = "<?php echo str_replace($bad_chars, "", __('min height', 'woocommerce-files-upload')); ?>";
var wcuf_image_min_width_text = "<?php echo str_replace($bad_chars, "", __('min width', 'woocommerce-files-upload')); ?>";
var wcuf_image_min_dip_text = "<?php echo str_replace($bad_chars, "", __('min DPI', 'woocommerce-files-upload')); ?>";
var wcuf_image_max_dip_text = "<?php echo str_replace($bad_chars, "", __('max DPI', 'woocommerce-files-upload')); ?>";
var wcuf_image_aspect_ratio_text = "<?php echo str_replace($bad_chars, "", __('Aspect ratio', 'woocommerce-files-upload')); ?>";
var wcuf_media_min_length_text = "<?php echo str_replace($bad_chars, "", __('Min allowed length: ', 'woocommerce-files-upload')); ?>";
var wcuf_media_max_length_text = "<?php echo str_replace($bad_chars, "", __('Max allowed length: ', 'woocommerce-files-upload')); ?>";
var wcuf_type_allowed_error = "<?php  echo str_replace($bad_chars, "", __('Allowed file types: ', 'woocommerce-files-upload')); ?>";
var wcuf_image_file_error = "<?php  echo str_replace($bad_chars, "", __('Input file must be an image', 'woocommerce-files-upload')); ?>";
var wcuf_file_num_error = "<?php  echo str_replace($bad_chars, "", __('Maximum of file upload error. You can upload max : ', 'woocommerce-files-upload')); ?>";
var wcuf_removed_files_text = "<?php  echo str_replace($bad_chars, "", __('Following files have been removed: ', 'woocommerce-files-upload')); ?>";
var wcuf_ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var wcuf_success_msg = '<?php  echo str_replace($bad_chars, "", __('Done! ', 'woocommerce-files-upload')); ?>';
var wcuf_loading_msg = '<?php  echo addslashes($button_texts['save_in_progress_message']); ?>';
var wcuf_delete_msg = '<?php  echo str_replace($bad_chars, "", __('Deleting, pelase wait... ', 'woocommerce-files-upload')); ?>';
var wcuf_failure_msg = '<?php  echo str_replace($bad_chars, "", __('An error has occurred.', 'woocommerce-files-upload')); ?>';
var wcuf_delete_file_msg = '<?php  echo $button_texts['delete_file_button']; ?>';
var wcuf_html5_error = "<?php echo str_replace($bad_chars, "", __('The HTML5 standards are not fully supported in this browser, please upgrade it or use a more moder browser like Google Chrome or FireFox.', 'woocommerce-files-upload')); ?>";
//var wcuf_file_sizes_error = "<?php echo str_replace($bad_chars, "",__("The sum of file sizes cannot be greater than {$post_max_size}MB!", "woocommerce-files-upload")); ?>";
var wcuf_file_sizes_error = "<?php echo str_replace($bad_chars, "",__("The sum of file sizes cannot be greater than %s MB!", "woocommerce-files-upload")); ?>";
var wcuf_file_sizes_min_error = "<?php echo str_replace($bad_chars, "",__("The sum of file sizes cannot be minor than %s MB!", "woocommerce-files-upload")); ?>";
var wcuf_max_file_sizes = <?php echo $post_max_size*1024*1024;?>;
var wcuf_max_chunk_size = <?php echo $max_chunk_size*1024*1024;?>;
var wcuf_exists_at_least_one_upload_field_bounded_to_variations = wcuf_options.exists_at_least_one_upload_field_bounded_to_variations == 'true';
var wcuf_exists_at_least_one_upload_field_bounded_to_gateway = wcuf_options.exists_at_least_one_upload_field_bounded_to_gateway == 'true';
var wcuf_required_upload_add_to_cart_warning_message = '<div class="wcuf_required_upload_add_to_cart_warning_message" style="display:none;"><?php echo $button_texts['required_upload_add_to_cart_warning_message']; ?></div>';
var wcuf_delete_single_file_warning_msg = "<?php echo str_replace($bad_chars, "",__("Are sure you want to delete the file?", "woocommerce-files-upload")); ?>";
var wcuf_multiple_file_list_tile = "<?php echo str_replace($bad_chars, "",__("To upload:", "woocommerce-files-upload")); ?>";
var wcuf_auto_upload_for_multiple_files_upload_field = <?php echo $all_options['auto_upload_for_multiple_files_upload_field'] ? "true" : "false"; ?>;
/*
 Workflow:
	-- upload: wcuf_backgroud_file_upload -> wcuf_append_file_delete() -> wcuf_ajax_reload_upload_fields_container()
	-- delete: wcuf_delete_file -> wcuf_ajax_reload_upload_fields_container()
*/
jQuery(document).ready(function()
{
	jQuery('.wcuf_file_input').val('');
	
	//Shows upload field area after page load
	wcuf_show_upload_field_area();
	
	if(typeof wcuf_unload_check === 'undefined')
	{
		console.log("[WCUF] javascript libraries have not properly loaded due to 3rd party plugin interference.");
		return;
	}
	
	if(wcuf_unload_check) //not performed in checkout page. For that one is used the '#place_order' click event.
		wcuf_before_unload();
	
	jQuery(document).on('click','.delete_button', wcuf_delete_file);
	jQuery(document).on('click', '.wcuf_delete_single_file_stored_on_server', wcuf_delete_single_file_on_server);
	jQuery(document).on('click','.wcuf_upload_field_button', wcuf_browse_file);
	jQuery(document).on('click','button.single_add_to_cart_button', wcuf_check_multiple_upload_status); //Product page: deny add to cart if selected files have not been uploaded
	jQuery(document).on('click', '#place_order', wcuf_checkout_check_required_fields); //only during checkout
	
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
	jQuery('#wcuf_close_popup_alert').on('click',function(event){ event.preventDefault(); event.stopImmediatePropagation(); jQuery.magnificPopup.close(); return false});
	jQuery('#wcuf_leave_page').on('click', wcuf_leave_the_page_after_warning_popup_prompt); //see method comment
		
	if (window.File && window.FileReader && window.FileList && window.Blob) 
	{
		//Old "string encoding" method
		//jQuery('.wcuf_file_input').on('change' ,wcuf_encode_file);
		
		//jQuery('form').on('change', '.wcuf_file_input.wcuf_file_input_multiple', wcuf_check_multiple_file_uploads_limit_and_crop);
		
		jQuery(document).on('change','.wcuf_file_input', wcuf_file_input_check);
		jQuery(document).on('drop','.wcuf_upload_drag_and_drop_area', wcuf_on_files_dragged);
		jQuery(document).on('dragover','.wcuf_upload_drag_and_drop_area', wcuf_on_drag_over_event);
		jQuery(document).on('dragleave','.wcuf_upload_drag_and_drop_area', wcuf_on_drag_leave_event);
		
		//jQuery('.wcuf_file_input').change(wcuf_file_input_check);
		
		jQuery(document).on('click','.wcuf_upload_multiple_files_button', wcuf_start_checks_on_files_info);
	} 
	else 
	{
		jQuery('#wcuf_file_uploads_container').hide();
		wcuf_show_popup_alert(wcuf_html5_error);
	}
	
	//Required uploads warning message
	if(wcuf_show_required_upload_add_to_cart_warning_message)
		jQuery(".product_meta").prepend(wcuf_required_upload_add_to_cart_warning_message);
	
	//Hide add to cart in case of required field 
	wcuf_hide_add_to_cart_button_in_case_of_required_upload(0, 0);
	
});

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
	wcuf_ui_delete_file();
	event.preventDefault();
	event.stopImmediatePropagation();
	
	//wcuf_show_popup_alert(wcuf_delete_single_file_warning_msg);
	//console.log(id);
	
	//
	jQuery.post( wcuf_ajaxurl , { action: wcuf_ajax_delete_single_file_action, id: id, field_id:field_id } ).done(wcuf_ui_after_delete);
}

function wcuf_delete_file(event)
{
	wcuf_is_deleting = true;
	wcuf_ui_delete_file();
	event.preventDefault();
	event.stopImmediatePropagation();
	
	//UI 
	wcuf_show_hide_add_to_cart_area(false, false, 100,100);
	
	jQuery.post( wcuf_ajaxurl , { action: wcuf_ajax_delete_action, id: jQuery(event.target).data('id')/* , order_id:wcuf_order_id  */} ).done(wcuf_ui_after_delete);
	return false;
}

function wcuf_reset_data()
{
	wcuf_multiple_files_queues = new Array();
	wcuf_multiple_files_mandatory_crop = new Array();
}
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
		jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("<h4>"+unescape(wcuf_ajax_reloading_fields_text)+"</h4>");
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
			dataType : "html",
			contentType: "application/json; charset=utf-8",
			async: true,
			success: function (data) 
			{
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container_loading_container').html("");  
				jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').html(data);
				wcuf_show_upload_field_area()
				//jQuery('#wcuf_'+wcuf_current_page+'_ajax_container').animate({ opacity: 1 }, 500);	
							
				//Hide add to cart in case of required field 
				wcuf_hide_add_to_cart_button_in_case_of_required_upload(600, 400);
				
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
function wcuf_append_file_delete(id) //after file upload
{
	 if(wcuf_current_page == "cart"  ) //|| wcuf_current_page == "shortcode"
	{
		wcuf_reload_page(500);
		return false;
	} 
	else //if(wcuf_current_page == "product") 
	{
		wcuf_ajax_reload_upload_fields_container();
		//setTimeout(function(){wcuf_ajax_reload_upload_fields_container() }, 1500);
		return false;
	} 
	
	
	//Not used any more ------------------------------------------------------ 
	var delete_id = wcuf_is_order_detail_page ? id : 'wcufuploadedfile_'+id;
	jQuery('.wcuf_file_name').delay(320).fadeIn(300);
	jQuery('#wcuf_upload_status_box_'+id).delay(300).hide(500);
	
	wcuf_show_control_buttons(id);

	jQuery('#wcuf_upload_field_button_'+id+', #wcuf_upload_multiple_files_button_'+id+', #wcuf_file_name_'+id+', #wcuf_max_size_notice_'+id+', #wcuf_feedback_textarea_'+id).remove();
	
	jQuery('#wcuf_delete_button_box_'+id).append('<button class="button delete_button" data-id="'+delete_id+'">'+wcuf_delete_file_msg+'</button>');
	jQuery(document).on('click', '#wcuf_delete_button_box_'+id, wcuf_delete_file);	
}
function wcuf_set_bar_background()
{
	jQuery('.wcuf_bar').css('background-color',wcuf_progressbar_color);
}
function wcuf_hide_control_buttons()
{
	jQuery('.wcuf_crop_container, .wcuf_multiple_files_actions_button_container, .wcuf_disclaimer_label, .wcuf_upload_field_button, .wcuf_feedback_textarea, .wcuf_max_size_notice, .wcuf_upload_multiple_files_button, .delete_button').fadeOut(0);
	
}
function wcuf_show_control_buttons(id)
{
	var current_elem = jQuery('#wcuf_upload_field_'+id);
	var is_multiple = jQuery(current_elem).data('is-multiple-files');
	
	jQuery('.wcuf_upload_field_button,  .wcuf_disclaimer_label, .wcuf_max_size_notice, .wcuf_feedback_textarea, .delete_button').fadeIn(200);
	if(is_multiple)
		jQuery('.wcuf_multiple_files_actions_button_container').fadeIn(200);
	
	jQuery('.wcuf_upload_multiple_files_button').each(function(index,elem)
	{
		var id = jQuery(this).data('id');
		if(typeof wcuf_multiple_files_queues !== 'undefined' && typeof wcuf_multiple_files_queues[id] !=='undefined' && wcuf_multiple_files_queues[id].length > 0)
			jQuery(this).fadeIn(500);
	});
}
function wcuf_show_multiple_files_progress_area(id)
{
	jQuery('#wcuf_multiple_file_progress_container_'+id).fadeIn();
}
function wcuf_reset_loading_ui(id)
{
	wcuf_set_bar_background();
	jQuery('#wcuf_file_name_'+id).html("");
	jQuery('.wcuf_file_name, wcuf_multiple_file_progress_container_'+id).fadeOut(0);	
	jQuery('#wcuf_bar_'+id+", #wcuf_multiple_file_bar_"+id).css('width', "0%");
	
	//jQuery('input.wcuf_file_input, .wcuf_max_size_notice, .delete_button').fadeOut(500);
	wcuf_hide_control_buttons();
	
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
	//jQuery('#wcuf_status_'+id).html("");
	jQuery('#wcuf_status_'+id).html(unescape(wcuf_loading_msg));
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
	{
		evt.dot_not_invoke_the_callback = true;	//this triggers the autoupload feature (autoupload automatic upload)
	}
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
	//For not image files, error is not rised if min/max width/wight are not set.
	wcuf_check_image_file_width_and_height(files, evt, current_elem, wcuf_result_on_files_info, max_image_width, max_image_height, min_image_width, min_image_height, min_image_dpi ,max_image_dpi, dimensions_logical_operator);
	return false;
}
//this method is eventually invoked by the "wcuf_check_image_file_width_and_height" method according the "autoupload" option
function wcuf_result_on_files_info(evt, error, img, data)
{
	if(!error)
	{
		//Check if audio/video file respects length limit (if any)
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
		/* if(!data.exact_image_size) */
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
	
	if(!is_multiple && enable_crop)
	{
		new wcuf_image_crop(evt, id, wcuf_backgroud_file_upload);
	}			
	else
		wcuf_backgroud_file_upload(evt);
}
function wcuf_backgroud_file_upload(evt)
{
	evt.preventDefault();
	evt.stopImmediatePropagation();
	var id =  jQuery(evt.currentTarget).data('id');
	var current_elem = jQuery('#wcuf_upload_field_'+id); //jQuery(evt.currentTarget)
	var file_wcuf_user_feedback = jQuery('#wcuf_feedback_textarea_'+id).val();
	var size = current_elem.data('size');
	var min_size = current_elem.data('min-size');
	var file_wcuf_name = current_elem.attr('name');
	var file_wcuf_title = current_elem.data('title');
	var detect_pdf = current_elem.data('detect-pdf');
	var check_disclaimer = current_elem.data('disclaimer');
	var extension =  current_elem.val().replace(/^.*\./, '');
	var extension_accepted = current_elem.attr('accept');
	var max_image_width = current_elem.data("max-width");
	var max_image_height = current_elem.data("max-height");
	var is_crop_editor_enabled = current_elem.data("enable-crop-editor");
	
	var files;
    var file;
	//var is_multiple = jQuery(evt.currentTarget).hasClass('wcuf_upload_multiple_files_button');
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
		else  //in case the file (image) has been cropped
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
		 if(!is_multiple && !is_crop_editor_enabled) //On Chrome if the input field is not cleared, it doesn't allow the file selection 
		 {
			 jQuery("#wcuf_upload_field_"+id).val(""); 
		 }
		return false;
	}
	//if(is_multiple)
	{
		//size and filetypecheck
		if(!wcuf_check_multiple_file_uploads_limit_and_crop(id, files, file))
		{
			return false;
		}
	}
	if(jQuery('#wcuf_feedback_textarea_'+id).val() == "" && jQuery('#wcuf_feedback_textarea_'+id).prop('required'))
	{
		wcuf_show_popup_alert(wcuf_user_feedback_required_message);
		/* if(!is_multiple) //On Chrome if the input field is not cleared, it doesn't allow the file selection   *** NO NEED, IT HAS BEEN FIXED
			jQuery("#wcuf_upload_field_"+id).val(""); */
		return;
	}	
	jQuery('#wcuf_feedback_textarea_'+id).prop('disabled', true);

	
	if (xhr.upload /* && 
		(extension_accepted == undefined || extension_accepted.indexOf(extension) > -1) &&
		((size == 0 || file.size <= size) && (min_size == 0 || file.size >= min_size) )  */ )
		{
			//UI			
			wcuf_reset_loading_ui(id);
			wcuf_show_hide_add_to_cart_area(false, false, 100,100);
	
			var formData = {'action': wcuf_ajax_action,
							'title': file_wcuf_title,
							'detect_pdf': detect_pdf,
							'user_feedback': file_wcuf_user_feedback,
							'order_id': wcuf_order_id,
							'wcuf_wpml_language': wcuf_wpml_language
						};
			
			var multiple_file_uploader = new WCUFMultipleFileUploader({/* xhr: xhr, */  upload_field_id:id, form_data: formData, files: files, file: file, file_name:file_wcuf_name});
			document.addEventListener('onWCUFMultipleFileUploaderComplete', function(){wcuf_append_file_delete(id);});
			
			if(typeof wcuf_multiple_files_queues !== 'undefined' && typeof wcuf_multiple_files_queues[id] !== 'undefined')
				wcuf_multiple_files_queues[id] = new Array();
			
			multiple_file_uploader.continueUploading();
		}	
		else
		{
			console.log("here");
			wcuf_display_file_size_or_ext_error(file, size, extension_accepted, min_size, size);
		}
			
	return false;
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
	if (max_num != 0 && /* (files.length+all_files_quantity_sum) */ all_files_quantity_sum > max_num)
	{
		wcuf_show_popup_alert(wcuf_file_num_error+max_num);
		error = true;
	}
	else if(min_num != 0 && /* (files.length+all_files_quantity_sum)  */ all_files_quantity_sum < min_num)
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
				
				//msg += "<br/>"; 
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
function wcuf_check_multiple_upload_status(evt)
{
	 if(typeof wcuf_multiple_files_queues !== 'undefined')
		for (var key in wcuf_multiple_files_queues) 
		//jQuery.each(wcuf_multiple_files_queues, function(key, value)
		{
		  if ((!isNaN(key) || key.indexOf('-') > -1) && wcuf_multiple_files_queues[key].length != 0) //In some instalaltion keys contains some other window/dom elements
		  {
			  if(evt != null)
			  {
				  evt.preventDefault();
				  evt.stopImmediatePropagation();
				  wcuf_show_popup_alert(wcuf_multiple_uploads_error_message);
			  }
			 return false;
		  }
		}
	return true;
}
function wcuf_all_required_uploads_have_been_performed(bypass_page_leave_check)
{
	var ok = true;
	if(!bypass_page_leave_check && wcuf_allow_user_to_leave_page_in_case_of_required_field == 'always')
		return true;
	jQuery('.wcuf_file_input').each(function(index,value)
	{
		var min_files = parseInt(jQuery(this).data('min-files'));
		var data_is_required = jQuery(this).data('required');
		
		//if(jQuery(this).prop('required') && jQuery(this).val() == '') //before incremental upload 
		if(data_is_required && min_files != 0)
			ok =  false;
	});
	return ok;
}
function wcuf_checkout_check_required_fields(event)
{
	/* jQuery('.wcuf_file_input').each(function(index, object)
	{
		if(jQuery(this).val() == "" && jQuery(this).prop('required'))
		{
			event.preventDefault();
			event.stopImmediatePropagation();
			wcuf_show_popup_alert(wcuf_checkout_required_message);
			return false;
		}
	}); */
	if(!wcuf_all_required_uploads_have_been_performed(false))
	{
		event.preventDefault();
		event.stopImmediatePropagation();
		wcuf_show_popup_alert(wcuf_checkout_required_message); 
		return false;
	}
	
	wcuf_check_multiple_upload_status(event);
}
function wcuf_is_edge()
{
	return /Edge\/\d+/i.test(navigator.userAgent);
}
function wcuf_is_IE()
{
	var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))  // If Internet Explorer, return version number
    {
		 return true;
    }
}

//In case of 'allow_user_to_leave_page_in_case_of_required_field', popup ok button (alert_popup.php) has id "wcuf_leave_page". So the click
//is detected via this handler and user can leave page on next leave attempt
function wcuf_leave_the_page_after_warning_popup_prompt(event)
{
	jQuery.magnificPopup.close();
	wcuf_is_force_reloading = true;
	return false;
}
function wcuf_before_unload() 
{
	jQuery(window).bind("beforeunload", function (e) 
	{
		if(wcuf_is_force_reloading)
			return; 
        var can_exit = true;
		var multiple_uploads_status = wcuf_check_multiple_upload_status(null);
		var confirmationMessage = wcuf_unload_confirm_message;
		var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
		
		if(!multiple_uploads_status)
		{
			can_exit = multiple_uploads_status;
			confirmationMessage = wcuf_multiple_uploads_error_message;
		}
		
		if( wcuf_current_page != 'product' || 
			(wcuf_force_require_check_before_adding_item_to_cart && wcuf_exist_a_field_before_add_to_cart) || 
			(wcuf_current_page == 'product' && wcuf_item_has_been_added_to_cart))
				/* jQuery('.wcuf_file_input').each(function(index,value)
				{
					if(jQuery(this).prop('required') && jQuery(this).val() == '')
						can_exit = false;
				}); */
				can_exit = wcuf_all_required_uploads_have_been_performed(false);
		if(can_exit || wcuf_is_deleting)
			return undefined;
		
		/* wcuf_show_popup_alert(confirmationMessage);
				window.setTimeout(function() {
					window.stop();
				}, 1); */
		
		 //if(/Firefox[\/\s](\d+)/.test(navigator.userAgent) && new Number(RegExp.$1) >= 4) 
		 {
			 if(isChrome)
			 {
				 wcuf_show_popup_alert(confirmationMessage);
				 return confirmationMessage;
			 }
			 else if(!wcuf_is_IE() && confirm(confirmationMessage)) 
				history.go();
			else  
				{
					if(!wcuf_is_edge() && !wcuf_is_IE())
						wcuf_show_popup_alert(confirmationMessage);
					window.setTimeout(function() {
						window.stop();
					}, 1);
					if(wcuf_is_edge() || wcuf_is_IE())
					{
						e.preventDefault();
						e.stopImmediatePropagation();
						(e || window.event).returnValue = confirmationMessage; //Gecko + IE
						return confirmationMessage;
					}
				}
		}
		/* else //old method
		{
			wcuf_show_popup_alert(confirmationMessage);
			(e || window.event).returnValue = confirmationMessage; //Gecko + IE
			e.preventDefault();
			e.stopImmediatePropagation();
			return confirmationMessage;
		}  */
    });
};