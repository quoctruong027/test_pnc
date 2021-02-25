<?php echo '<script type="text/javascript" src="'.wcuf_PLUGIN_PATH.'/js/wcuf-frontend-global-error-catcher.js"></script>'; ?>
<div id="wcuf_deleting_message">
	<h4><?php echo $button_texts['deleting_files_message']; ?></h4>
	<div class="wcuf_spacer"></div> 
</div>

<div id="wcuf_file_uploads_container">
<input type="hidden" value="yes" name="wcuf-uploading-data"></input>
<div id="wcuf-files-box"></div>
<?php 
$exists_one_required_field = false;
$render_upload_button = false;
$post_max_size = WCUF_File::return_bytes(ini_get('post_max_size'));
$max_chunk_size = WCUF_File::return_bytes($wcuf_option_model->get_php_settings('size_that_can_be_posted'));
//$upload_max_filesize = ini_get('upload_max_filesize');
$bad_chars = array('"', "'");
$num_total_uploaded_files = 0;
$total_costs = 0;

if(is_array($file_fields_groups))
foreach($file_fields_groups as $file_fields): 
			
	
		$enable_for = isset($file_fields['enable_for']) ? $file_fields['enable_for']:'always';
		$hide_upload_after_upload = isset($file_fields['hide_upload_after_upload']) ? $file_fields['hide_upload_after_upload']:false;
		$upload_fields_editable_for_completed_orders = isset($file_fields['upload_fields_editable_for_completed_orders']) ? $file_fields['upload_fields_editable_for_completed_orders']:false;
		$display_text_field = isset($file_fields['text_field_on_order_details_page']) ? (bool)$file_fields['text_field_on_order_details_page']:false;
		$text_field_max_input_chars = !isset($file_fields['text_field_max_input_chars']) ?  0:$file_fields['text_field_max_input_chars'];
		$is_text_field_required = isset($file_fields['is_text_field_on_order_details_page_required']) ? (bool)$file_fields['is_text_field_on_order_details_page_required']:false;
		$display_on_order_detail = isset($file_fields['display_on_order_detail']) ? $file_fields['display_on_order_detail']:false;
		$display_on_thank_you = isset($file_fields['display_on_thank_you']) ? $file_fields['display_on_thank_you']:false;
		$required_on_checkout = isset($file_fields['required_on_checkout']) ? $file_fields['required_on_checkout']:false;
		$disable_stacking = isset($file_fields['disable_stacking']) ? (bool)$file_fields['disable_stacking']:false;
		$multiple_uploads_max_files_depends_on_quantity = isset($file_fields['multiple_uploads_max_files_depends_on_quantity']) ? $file_fields['multiple_uploads_max_files_depends_on_quantity']:false;
		$multiple_uploads_min_files_depends_on_quantity = isset($file_fields['multiple_uploads_min_files_depends_on_quantity']) ? $file_fields['multiple_uploads_min_files_depends_on_quantity']:false;
		$multiple_uploads_minimum_required_files = isset($file_fields['multiple_uploads_minimum_required_files']) ? $file_fields['multiple_uploads_minimum_required_files']:0;
		$display_disclaimer_checkbox = isset($file_fields['disclaimer_checkbox']) ? (bool)$file_fields['disclaimer_checkbox']:false;
		$disclaimer_text = isset($file_fields['disclaimer_text']) ? $file_fields['disclaimer_text']:"";
		$enable_multiple_uploads_per_field = isset($file_fields['enable_multiple_uploads_per_field']) ? (bool)$file_fields['enable_multiple_uploads_per_field'] : false;
		$display_on_product_before_adding_to_cart = isset($file_fields['display_on_product_before_adding_to_cart']) ? $file_fields['display_on_product_before_adding_to_cart']:false;
		$disable_stacking_for_variation = isset($file_fields['disable_stacking_for_variation']) /*  &&  !$display_on_product_before_adding_to_cart */ ? (bool)$file_fields['disable_stacking_for_variation']:false;
		$multiple_files_min_size_sum = isset($file_fields['multiple_files_min_size_sum']) ? $file_fields['multiple_files_min_size_sum']*1048576:0;
		$multiple_files_max_size_sum = isset($file_fields['multiple_files_max_size_sum']) ? $file_fields['multiple_files_max_size_sum']*1048576:0;
		$min_size = isset($file_fields['min_size']) ? $file_fields['min_size']*1048576:0;
		$selected_categories = isset($file_fields['category_ids']) ? $file_fields['category_ids']:array();
		$selected_attributes = !isset($file_fields['attributes_ids']) ? array(): $file_fields['attributes_ids'];
		$display_product_fullname = isset($file_fields['full_name_display']) ? $file_fields['full_name_display']:true; //Usefull only for variable products
		$display_simple_product_name_with_attributes = isset($file_fields['display_simple_product_name_with_attributes']) ? $file_fields['display_simple_product_name_with_attributes']:false; //Usefull only for simple products		
		$all_products_cats_ids = array();
		$products_for_which_stacking_is_disabled = array();
		$can_render = $enable_for == 'always' ? true:false;
		$dimensions_logical_operator = isset($file_fields['dimensions_logical_operator']) ? $file_fields['dimensions_logical_operator'] : 'and';
		$max_width = isset($file_fields['width_limit']) ? $file_fields['width_limit'] : 0;
		$max_height = isset($file_fields['height_limit']) ? $file_fields['height_limit'] : 0;
		$min_width_limit = isset($file_fields['min_width_limit']) ? $file_fields['min_width_limit'] : 0;
		$min_height_limit = isset($file_fields['min_height_limit']) ? $file_fields['min_height_limit'] : 0;		
		$min_dpi_limit = isset($file_fields['min_dpi_limit']) ? $file_fields['min_dpi_limit'] : 0;		
		$max_dpi_limit = isset($file_fields['max_dpi_limit']) ? $file_fields['max_dpi_limit'] : 0;	
		$ratio_x = isset($file_fields['ratio_x']) ? $file_fields['ratio_x'] : 0;		
		$ratio_y = isset($file_fields['ratio_y']) ? $file_fields['ratio_y'] : 0;	
		$min_seconds_length = isset($file_fields['min_seconds_length']) ? $file_fields['min_seconds_length'] : 0;		
		$max_seconds_length = isset($file_fields['max_seconds_length']) ? $file_fields['max_seconds_length'] : 0;		
		$consider_sum_of_media_seconds = isset($file_fields['consider_sum_of_media_seconds']) ? $file_fields['consider_sum_of_media_seconds'] : false;		
		$enable_crop_editor = isset($file_fields['enable_crop_editor']) ?  $file_fields['enable_crop_editor']:false;
		$crop_mandatory_for_multiple_files_upload = isset($file_fields['crop_mandatory_for_multiple_files_upload']) ?  $file_fields['crop_mandatory_for_multiple_files_upload']:false;
		$crop_area_type = isset($file_fields['crop_area_type']) ?  $file_fields['crop_area_type']:'square';
		$cropped_image_width = isset($file_fields['cropped_image_width']) ?  $file_fields['cropped_image_width']:200;
		$cropped_image_height = isset($file_fields['cropped_image_height']) ?  $file_fields['cropped_image_height']:200;
		$file_fields['user_can_download_his_files'] = isset($file_fields['user_can_download_his_files']) ? $file_fields['user_can_download_his_files'] : false;
		$hide_extra_info = isset($file_fields['hide_extra_info']) ? $file_fields['hide_extra_info'] : false;
		$exists_one_required_field = !$exists_one_required_field && $required_on_checkout ? true:$exists_one_required_field;
		$text_field_label = isset($file_fields['text_field_label']) ? $file_fields['text_field_label'] : "";
		$text_field_description = isset($file_fields['text_field_description']) ? $file_fields['text_field_description'] : "";
		$selected_products = isset($file_fields['products_ids']) ? $file_fields['products_ids']:array();
		$roles = !isset($file_fields['roles']) ?  array():$file_fields['roles'];
		$order_status_to_hide = !isset($file_fields['order_status']) ?  array():$file_fields['order_status'];
		$roles_policy = !isset($file_fields['roles_policy']) ?  "allow":$file_fields['roles_policy'];
		$visibility_gateways = !isset($file_fields['visibility_gateways']) ?  array():$file_fields['visibility_gateways'];
		$visibility_payment_gateway_policy = !isset($file_fields['visibility_payment_gateway_policy']) ?  "allow":$file_fields['visibility_payment_gateway_policy'];
		$preview_images_before_upload_disabled = !isset($file_fields['preview_images_before_upload_disabled']) ?  false:$file_fields['preview_images_before_upload_disabled'];
	
		$enable_upload_per_file = false;
		
		//Role check
		if(!empty($roles) && !$wcuf_customer_model->belongs_to_allowed_roles($roles,$roles_policy))
			continue;
		
		if(!$disable_stacking_for_variation)
			foreach($order_items as $product_index => $product)
			{
				$product->set_variation_id(0);
			}
				
		//Visibility per gateway
		if(!empty($visibility_gateways) && !$wcuf_order_model->is_selected_payment_method_allowed($order, $visibility_gateways,$visibility_payment_gateway_policy))
			continue;
		
		if( $current_page == 'order_details' && /* isset($file_order_metadata) && !empty($file_order_metadata) && */ array_key_exists($current_order_status, $order_status_to_hide))
			$can_render = false;
		
		if(($display_on_order_detail && $current_page == 'order_details') || ($display_on_thank_you && $current_page == 'thank_you') || (($display_on_order_detail || $display_on_thank_you) && $current_page == 'shortcode'))
		{
			//1: which restriction applies (if any)
			if( !array_key_exists($current_order_status, $order_status_to_hide) && (($enable_for === 'always' && $disable_stacking) || $enable_for !== 'always' && (count($selected_categories) > 0 || count($selected_products) > 0 || count($selected_attributes) > 0)))
			{
				foreach($order_items as $product)
				{
									
					//$product->get_meta('bundled_by'): to avoid that upload field is shown for "buldles" -> WooCommerceProduct Bundles
					if( $product->get_meta('bundled_by'))
						continue;
					
					$sold_as_individual_id = $wcuf_order_model->read_order_item_meta($product,'_wcuf_sold_as_individual_unique_key');
					$disable_stacking_for_variation = $sold_as_individual_id ? $sold_as_individual_id : true;
					$product->add_meta_data(WCUF_Cart::$sold_as_individual_item_cart_key_name, $sold_as_individual_id ? $sold_as_individual_id : 0, true);
					
					//WPML
					if($wcuf_wpml_helper->wpml_is_active())
					{
						$product->set_product_id($wcuf_wpml_helper->get_main_language_id($product->get_product_id()));
						if($product->get_variation_id() != 0)
							$product->set_variation_id($wcuf_wpml_helper->get_main_language_id($product->get_variation_id(), 'product_variation'));
					}
					
					//2. Check restriction: product, categories or attributes
					//attributes 
					if(!empty($selected_attributes))
					{
						foreach($selected_attributes as $attribute_value_id)
						{
							$tmp_product_id = $product['variation_id'] != 0 ? $product['variation_id'] : $product['product_id'];
							$selected_products[] = $wcuf_product_model->has_attribute_value($tmp_product_id, $attribute_value_id) ? $tmp_product_id : -1;
						}
					}
					//end attributes 
					
					//products
					$discard_field = false;
					if(!empty($selected_products) )
					{
						foreach($selected_products as $product_id)
						{	
							$variation_id = $is_variation = 0;
							//if(!$display_on_product_before_adding_to_cart)
							{
								$is_variation = $wcuf_product_model->is_variation($product_id);
								$variation_id = $is_variation > 0 ? $product_id : 0 ;
								$product_id = $is_variation > 0 ? $is_variation : $product_id ;
							}
							$discard_field = false;
							//wcuf_var_dump("current cart item: ".$product->get_product_id()." selected: ".$product_id.", enabled: ".$enable_for); 
							if( ($product_id == $product->get_product_id() && ($variation_id == 0 || $variation_id == $product->get_variation_id()) && ($enable_for === 'categories' || $enable_for === 'categories_children'))
								|| ( !in_array($product->get_product_id(), $selected_products) && !in_array($product->get_variation_id(), $selected_products) && ($enable_for === 'disable_categories' || $enable_for === 'disable_categories_children')) 
							   )
								{
									if($disable_stacking)
										$enable_upload_per_file = true;
									$can_render = true;
									
									$force_disable_stacking_for_variation =  $disable_stacking_for_variation;
									$product->add_meta_data('force_disable_stacking_for_variation', $force_disable_stacking_for_variation, true);
									
									//In case of variable
									if(!wcuf_product_is_in_array($product, $products_for_which_stacking_is_disabled, $force_disable_stacking_for_variation, $disable_stacking, true))
									{
										$products_for_which_stacking_is_disabled[] = $product;
									}
								}
								elseif( $enable_for !== 'always') 
									$discard_field = true;
							
						}
					}
					else if($enable_for === 'always' && $disable_stacking)
					{
						$enable_upload_per_file = true;
						$can_render = true;
						//In case of variable
						if(!wcuf_product_is_in_array($product, $products_for_which_stacking_is_disabled, $disable_stacking_for_variation,$disable_stacking, true))
							$products_for_which_stacking_is_disabled[] = $product;
					}
					//end products 	
			
					//product categories
					$product_cats = wp_get_post_terms( $product->get_product_id(), 'product_cat' );
					$current_product_categories_ids = array();
					foreach($product_cats as $category)
					{
						$category_id = $wcuf_wpml_helper->get_main_language_id($category->term_id, 'product_cat');
						
						if(!$disable_stacking)
							array_push($all_products_cats_ids, (string)$category_id);
						else
							array_push($current_product_categories_ids, (string)$category_id);
						
						//parent categories
						if($enable_for == "categories_children" || $enable_for == "disable_categories_children")
						{
							//$parents =  get_ancestors( $category->term_id, 'product_cat' ); 
							$parents =  get_ancestors( $category_id, 'product_cat' ); 
							foreach($parents as $parent_id)
							{
								$temp_category =$wcuf_wpml_helper->get_main_language_id($parent_id, 'product_cat');
								if(!$disable_stacking)
									array_push($all_products_cats_ids, (string)$temp_category);
								else
									array_push($current_product_categories_ids, (string)$temp_category);//$category_id
							}
						}
					}
					//Can enable upload for this product? (if stacking uploads are disabled)
					if($disable_stacking && count($selected_categories) > 0)
					{
						if($enable_for === 'categories' || $enable_for === 'categories_children')
						{
							if(array_intersect($selected_categories, $current_product_categories_ids))
							{
								//if(!in_array($product, $products_for_which_stacking_is_disabled))
								if(!wcuf_product_is_in_array($product, $products_for_which_stacking_is_disabled, $disable_stacking_for_variation,$disable_stacking, true))
									array_push($products_for_which_stacking_is_disabled, $product);
								$can_render = true;
							}
						}
						elseif(!$discard_field)
						{
							if(!array_intersect($selected_categories, $current_product_categories_ids))
							{
								//if(!in_array($product, $products_for_which_stacking_is_disabled))
								if(!wcuf_product_is_in_array($product, $products_for_which_stacking_is_disabled, $disable_stacking_for_variation,$disable_stacking, true))
									array_push($products_for_which_stacking_is_disabled, $product);
								$can_render = true;
							}
							else $can_render = false;
						}	
					}
					//end categories 
					
				} //ends product foreach
				//WCUF_UploadFieldsConfiguratorPage::WCUF_restore_current_lang();	
				
				//Cumulative ORDER catagories. If exists at least one product with an "enabled"/"disabled" category, upload field can be rendered
				if(!$disable_stacking && count($selected_categories) > 0)
					if($enable_for === 'categories' || $enable_for === 'categories_children')
					{  
						if(array_intersect($selected_categories, $all_products_cats_ids))
							$can_render = true;
					}
					elseif(!$discard_field)
					{ 
						if(!array_intersect($selected_categories, $all_products_cats_ids))
						//if( $selected_categories !== $all_products_cats_ids)
							$can_render = true;
						else $can_render = false;
					}						
			}
			
			//End computation -> fields rendering
				
			if($can_render /* && !$is_thank_you_page */): ?>
				<div class="wcuf_single_upload_field_container" id="wcuf_upload_field_container_<?php echo $file_fields['id']; ?><?php //if(isset($file_fields['field_css_id'])) echo $file_fields['field_css_id'];?>">
			<?php if(!$disable_stacking && !$enable_upload_per_file): //?? $enable_upload_per_file == $disable_stacking  always?
				
				if(!isset($product))
					$product = null;
				
				if($file_order_metadata && isset($file_order_metadata[$file_fields['id']]['url']))
					$file_order_metadata[$file_fields['id']]['url']  = $wcuf_upload_field_model->get_secure_urls($order_id, $file_fields['id'], $file_order_metadata);
				
				$uploaded_file_data = !isset($file_order_metadata[$file_fields['id']]) ? null : $file_order_metadata[$file_fields['id']];
				$num_of_uploaded_files = $wcuf_upload_field_model->get_num_uploaded_files($order_id, $file_fields['id'], $all_options['max_uploaded_files_number_considered_as_sum_of_quantities']);
				
				$upload_has_been_performed = isset($uploaded_file_data) ? true : false;
				$is_multiple_file_upload = !isset($file_order_metadata[$file_fields['id']]['is_multiple_file_upload']) ? false : $file_order_metadata[$file_fields['id']]['is_multiple_file_upload'];
				$multiple_uploads_max_files = $upload_has_been_performed ? 0 : 1;
				$multiple_uploads_min_files = 1;
				$unlimited_uploads = $file_fields['multiple_uploads_max_files'] == 0 ? true : false;
				$feedback_can_be_peformed = $upload_has_been_performed ? false : true;
				
				//Min/max uploadable files
				if($enable_multiple_uploads_per_field)
				{
					if($required_on_checkout)
						$multiple_uploads_min_files = $multiple_uploads_min_files == 0 ? 1 : $multiple_uploads_min_files;
					$multiple_uploads_max_files  =  $file_fields['multiple_uploads_max_files'] != 0 && $file_fields['multiple_uploads_max_files'] - $num_of_uploaded_files >= 0 ? $file_fields['multiple_uploads_max_files'] - $num_of_uploaded_files : 0;
					$multiple_uploads_min_files = $num_of_uploaded_files > $multiple_uploads_minimum_required_files ? 0 : $multiple_uploads_minimum_required_files - $num_of_uploaded_files;
				
					$feedback_can_be_peformed =  $unlimited_uploads ||  $multiple_uploads_max_files > 0 ? true : false;
				}
				?>
			<div class="wcuf_upload_fields_row_element">
				<<?php echo $all_options['upload_field_title_style']; ?> style="margin-bottom:5px;  margin-top:15px;" class="wcuf_upload_field_title <?php if($required_on_checkout ) echo 'wcuf_required_label'; ?>"><?php  echo $file_fields['title'] ?></<?php echo $all_options['upload_field_title_style']; ?>>
				<?php if(!$hide_upload_after_upload || ($hide_upload_after_upload && !$upload_has_been_performed)):?>
					<p class="wcuf_field_description"><?php echo do_shortcode($file_fields['description']); ?></p>
				<?php endif; ?>
				<?php if($display_text_field): ?>
					<?php if($text_field_label != ""):?>
						<h5><?php echo $text_field_label; ?></h5>
					<?php endif; ?>
					<?php if ($text_field_description != ""): ?>
						<div class="wpuef_text_field_description"><?php echo $text_field_description; ?></div>
					<?php endif; ?>
					<textarea class="wcuf_feedback_textarea" data-id="<?php echo $file_fields['id']; ?>" id="wcuf_feedback_textarea_<?php echo $file_fields['id']; ?>" name="wcuf[<?php echo $file_fields['id']; ?>][user_feedback]" <?php if($is_text_field_required) echo 'required="required"'; if(!$feedback_can_be_peformed) echo "disabled";?> <?php if($text_field_max_input_chars != 0) echo 'maxlength="'.$text_field_max_input_chars.'"';?>><?php if(isset($uploaded_file_data)) echo $uploaded_file_data['user_feedback'];?></textarea>
				<?php endif;?>
				<?php 
						//if(!isset($uploaded_file_data)): 
						if(($enable_multiple_uploads_per_field && ($unlimited_uploads || $multiple_uploads_max_files > 0)) || !$upload_has_been_performed ):
							$render_upload_button = true; 
						?>						
						<input type="hidden" name="wcuf[<?php echo $file_fields['id']; ?>][title]" value="<?php echo $file_fields['title']; ?>"></input>
						<input type="hidden" name="wcuf[<?php echo $file_fields['id']; ?>][id]" value="<?php echo $file_fields['id']; ?>"></input>
						<input type="hidden" id="wcuf-filename-<?php echo $file_fields['id']; ?>" name="wcuf[<?php echo $file_fields['id']; ?>][file_name]" value=""></input>
						
						<?php if($display_disclaimer_checkbox): ?>
							<label class="wcuf_disclaimer_label" id="wcuf_disclaimer_label_<?php echo $file_fields['id']; ?>"><input type="checkbox" class="wcuf_disclaimer_checkbox" id="wcuf_disclaimer_checkbox_<?php echo $file_fields['id']; ?>"></input><span class="wcuf_discaimer_text"><?php echo $disclaimer_text;?></span></label>
						<?php endif; ?>
						<?php if(/* !$enable_multiple_uploads_per_field || */ $all_options['drag_and_drop_disable']):?>
							<button id="wcuf_upload_field_button_<?php echo $file_fields['id']; ?>"  style="margin-right:<?php echo $css_options['css_distance_between_upload_buttons']; ?>px;" class="button wcuf_upload_field_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']; ?>"><?php if(!$enable_multiple_uploads_per_field) echo $button_texts['drag_and_drop_area_single_file_instruction']; else echo $button_texts['drag_and_drop_area_instruction']; ?></button>
						<?php else: ?>
							<div id="wcuf_upload_field_button_<?php echo $file_fields['id']; ?>" class="wcuf_upload_field_button wcuf_upload_drag_and_drop_area" data-id="<?php echo $file_fields['id']; ?>">
								<svg class="wcuf_drag_and_drop_area_icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43"><path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/></svg>
								<span class="wcuf_drag_and_drop_area_description"><?php echo !$enable_multiple_uploads_per_field ? $button_texts['drag_and_drop_area_single_file_instruction'] : $button_texts['drag_and_drop_area_instruction'];?></span>
							</div>
						<?php endif; ?>
						<button id="wcuf_upload_multiple_files_button_<?php echo $file_fields['id']; ?>" class="button wcuf_upload_multiple_files_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']; ?>"><?php echo $button_texts['upload_selected_files_button']; ?></button>
							
						<input type="file"  <?php //if($required_on_checkout ) echo 'required="required"'; ?> 
											data-disclaimer="<?php echo $display_disclaimer_checkbox;?>" 
											data-title="<?php echo $file_fields['title']; ?>" 
											id="wcuf_upload_field_<?php echo $file_fields['id']; ?>" 
											data-required="<?php if($required_on_checkout) echo 'true'; else echo 'false' ?>" 
											data-id="<?php echo $file_fields['id']; ?>" 
											data-min-files="<?php echo $multiple_uploads_min_files ?>" 
											data-max-files="<?php echo $multiple_uploads_max_files; ?>" 
											data-dimensions-logical-operator="<?php echo $dimensions_logical_operator; ?>" 
											data-max-width="<?php echo $max_width; ?>" 
											data-max-height="<?php echo $max_height; ?>" 
											data-min-height="<?php echo $min_height_limit; ?>" 
											data-min-width="<?php echo $min_width_limit; ?>"
											data-min-dpi="<?php echo $min_dpi_limit; ?>" 
											data-max-dpi="<?php echo $max_dpi_limit; ?>"
											data-ratio-x="<?php echo $ratio_x; ?>" 
											data-ratio-y="<?php echo $ratio_y; ?>"
											data-min-length="<?php echo $min_seconds_length; ?>" 
											data-max-length="<?php echo $max_seconds_length; ?>" 
											data-length-already-uploaded="<?php echo $wcuf_media_model->get_sum_of_media_length($uploaded_file_data); ?>"
											data-consider-sum-length="<?php echo $consider_sum_of_media_seconds; ?>" 
											data-images-preview-disabled= "<?php echo $preview_images_before_upload_disabled ? 'true' : 'false';?>" 
											data-detect-pdf = "false" 
											data-enable-crop-editor="<?php echo $enable_crop_editor; ?>"
											data-crop-mandatory-for-multiple-uploads="<?php echo $crop_mandatory_for_multiple_files_upload; ?>" 
											data-crop-area-shape="<?php echo $crop_area_type; ?>" 
											data-cropped-width="<?php echo $cropped_image_width; ?>" 
											data-cropped-height="<?php echo $cropped_image_height; ?>" 
											data-is-multiple-files="<?php if($enable_multiple_uploads_per_field) echo 'true'; else echo 'false'; ?>" 
											data-multiple-files-max-sum-size="<?php echo $multiple_files_max_size_sum; ?>" 
											data-multiple-files-min-sum-size="<?php echo $multiple_files_min_size_sum; ?>" 
											data-is-multiple-files="<?php if($enable_multiple_uploads_per_field) echo 'true'; else echo 'false'; ?>" 
											class="wcuf_file_input <?php if($enable_multiple_uploads_per_field) echo 'wcuf_file_input_multiple'; ?>" <?php if($enable_multiple_uploads_per_field)  echo 'multiple="multiple"'; ?> 
											name="wcufuploadedfile_<?php echo $file_fields['id']?>"  <?php if($file_fields['types'] != '') echo 'accept="'.$file_fields['types'].'"';?> 
											data-size="<?php echo $file_fields['size']*1048576; ?>" value="<?php echo $file_fields['size']*1048576; ?>" 
											data-min-size="<?php echo $min_size; ?>" ></input>
						<?php if(!$hide_extra_info): ?>			
						<strong class="wcuf_max_size_notice" id="wcuf_max_size_notice_<?php echo $file_fields['id'];?>">
									<?php if($min_size !=0) echo sprintf(__('Min size: %s MB', 'woocommerce-files-upload'), $min_size/1048576)."<br/>"; 
										  if($file_fields['size'] !=0) echo sprintf(__('Max size: %s MB', 'woocommerce-files-upload'),$file_fields['size'])."<br/>"; 
										  if($enable_multiple_uploads_per_field && $multiple_uploads_min_files) __('Min files: ', 'woocommerce-files-upload').$multiple_uploads_min_files."<br/>"; 
									      if($enable_multiple_uploads_per_field && $multiple_uploads_max_files && !$unlimited_uploads) __('Max files: ', 'woocommerce-files-upload').$multiple_uploads_max_files."<br/>";
										  if($min_width_limit) echo __('Min width: ', 'woocommerce-files-upload').$min_width_limit."px"."<br/>"; 
										  if($max_width) echo __('Max width: ', 'woocommerce-files-upload').$max_width."px"."<br/>"; 
										  if($min_height_limit) echo __('Min height: ', 'woocommerce-files-upload').$min_height_limit."px"."<br/>"; 
										  if($max_height) echo __('Max height: ', 'woocommerce-files-upload').$max_height."px"."<br/>";  
										  if($min_dpi_limit) echo __('Min DPI: ', 'woocommerce-files-upload').$min_dpi_limit."px"."<br/>";   
										  if($max_dpi_limit) echo __('Max DPI: ', 'woocommerce-files-upload').$max_dpi_limit."px"."<br/>"; 
										  if($ratio_x && $ratio_y) echo __('Ratio: ', 'woocommerce-files-upload').$ratio_x.":".$ratio_y."<br/>"; 
										  if($min_seconds_length) echo __('Min length: ', 'woocommerce-files-upload').wcuf_format_seconds_to_readable_length($min_seconds_length)."<br/>"; 
										  if($max_seconds_length) echo __('Max length: ', 'woocommerce-files-upload').wcuf_format_seconds_to_readable_length($max_seconds_length);
										  ?>
						</strong>
						
						<?php endif;
							  if($enable_crop_editor): ?>
								<?php 
										$cropper_product_id = $file_fields['id'];
										if(!$enable_multiple_uploads_per_field)
											include WCUF_PLUGIN_ABS_PATH.'/template/cropper.php'; 
										else
											include WCUF_PLUGIN_ABS_PATH.'/template/croppper_for_multiple.php';
										?>
							<?php endif; ?>
						
						<div class="wcuf_upload_status_box" id="wcuf_upload_status_box_<?php echo $file_fields['id']; ?>">
							<div class="wcuf_multiple_file_progress_container" id="wcuf_multiple_file_progress_container_<?php echo $file_fields['id']; ?>">
								<span class="wcuf_total_files_progress_bar_title"><?php _e('Total: ', 'woocommerce-files-upload'); ?></span>
								<div class="wcuf_bar" id="wcuf_multiple_file_bar_<?php echo $file_fields['id']; ?>"></div>
								<div id="wcuf_multiple_file_upload_percent_<?php echo $file_fields['id']; ?>"></div>
								<span class="wcuf_current_file_progress_bar_title" ><?php _e('Current: ', 'woocommerce-files-upload'); ?></span>
							</div>
							<div class="wcuf_bar" id="wcuf_bar_<?php echo $file_fields['id']; ?>"></div>
							<div class="wcuf_percent" id="wcuf_percent_<?php echo $file_fields['id']; ?>">0%</div>
							<div class="wcuf_status" id="wcuf_status_<?php echo $file_fields['id']; ?>"></div>
						</div>
						<div class="wcuf_deleting_box" id="wcuf_deleting_box_<?php echo $file_fields['id']; ?>">
							<?php _e('Deleting, please wait...', 'woocommerce-files-upload');  ?>
						</div>
						<div id="wcuf_file_name_<?php echo $file_fields['id']; ?>" class="wcuf_file_name"></div>
						<div class="wcuf_multiple_files_actions_button_container" id="wcuf_multiple_files_actions_button_container_<?php echo $file_fields['id'] ?>">
							<button class="button wcuf_just_selected_multiple_files_delete_button" id="wcuf_just_selected_multiple_files_delete_button_<?php echo $file_fields['id']; ?>" data-id="<?php echo $file_fields['id']."-".$product_id; ?>"><?php  _e('Delete selected files', 'woocommerce-files-upload'); ?></button>
							<button id="wcuf_upload_multiple_files_mirror_button_<?php echo $file_fields['id'] ?>" class="button wcuf_upload_multiple_files_mirror_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']; ?>"><?php echo $button_texts['upload_selected_files_button']; ?></button>
						</div>
						<div id="wcuf_delete_button_box_<?php echo $file_fields['id']; ?>">
						</div>
			      <?php //else: //not uplaoded data -> $upload_has_been_performed   !isset($file_order_metadata[$file_fields['id']])
						endif;
						if($upload_has_been_performed): ?>
						<div class="wcuf_already_uploaded_data_container"><?php 
							if(!isset($file_fields['message_already_uploaded']))
							{
								//_e('File already uploaded.', 'woocommerce-files-upload'); 
							}
							else
								{
									
									$already_uploaded_message = $file_fields['message_already_uploaded'];
									//[file_name] & [file_name_no_cost]
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name]', $already_uploaded_message, $file_fields, $uploaded_file_data,  false, $order_id, $file_fields['user_can_delete']);
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_no_cost]', $already_uploaded_message, $file_fields, $uploaded_file_data,  false, $order_id, $file_fields['user_can_delete']);
									//[file_name_with_image_preview] & [file_name_with_image_preview_no_cost]
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_image_preview]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);//old
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_media_preview]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_image_preview_no_cost]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);//old
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_media_preview_no_cost]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									//[image_preview_list] 
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[image_preview_list]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									//[uploaded_files_num]
									$already_uploaded_message = $wcuf_shortcodes->uploaded_files_num($already_uploaded_message, $file_fields, $uploaded_file_data);
									//[additional_costs]
									$already_uploaded_message = $wcuf_shortcodes->additional_costs($already_uploaded_message, $file_fields_groups, $uploaded_file_data, $file_fields,$product);
									
									$num_total_uploaded_files += isset($uploaded_file_data["quantity"]) ? count($uploaded_file_data["quantity"]) : 0; //each quantity element contains the selected quantity for each file
									//$total_costs += $wcuf_shortcodes->total_costs();
									
									echo do_shortcode($already_uploaded_message);
								}
							?></div>
					 <?php if($file_fields['user_can_delete']):?>
							<button class="button delete_button wcuf_delete_button" data-temp="no" data-id="<?php echo $file_fields['id'];?>"><?php  echo $button_texts['delete_file_button']; ?></button>
					<?php endif; ?>	
					<?php if($file_fields['user_can_download_his_files'] && isset($file_order_metadata[$file_fields['id']]) && !$is_multiple_file_upload):
									if(isset($file_order_metadata[$file_fields['id']][0]) || isset($file_order_metadata[$file_fields['id']]['url'])):
									$file_url = isset($file_order_metadata[$file_fields['id']]['url'][0]) ? $file_order_metadata[$file_fields['id']]['url'][0] : $file_order_metadata[$file_fields['id']]['url']; 
									?>
										<a class="button download_button" href="<?php echo $file_url; ?>" target="_blank" download><?php  _e('Download / View file(s)', 'woocommerce-files-upload'); ?></a>
							<?php endif; 
							endif; ?>		
			<?php endif; ?>
			</div> <!-- wcuf_upload_fields_row_element -->
			<!-- <div class="wcuf_spacer2"></div> 
			<div class="wcuf_upload_fields_row_element">-->
			
			<?php  else: //Disable stacking: Upload per product & variant
					foreach($products_for_which_stacking_is_disabled as $product): ?>
					<div class="wcuf_upload_fields_row_element">
					<?php 
					    $product_id = version_compare( WC_VERSION, '2.7', '<' )  ? $product["item_meta"]['_product_id'][0] :  $product->get_product_id();
						$product_name_backend = $product_name = $product['name'];
						$product_var_id = version_compare( WC_VERSION, '2.7', '<' ) ? $product["item_meta"]['_variation_id'][0] : $product->get_variation_id() ; 
						$product_var_id = $product_var_id == "" || $product_var_id == 0 ? false: $product_var_id;
						$product_variation = null;
						$product->add_meta_data('force_disable_stacking_for_variation', isset($product['force_disable_stacking_for_variation']) && $product['force_disable_stacking_for_variation'] ? $product['force_disable_stacking_for_variation'] : false, true);
						$variation_exists = true;
						$show_upload_field_for_each_variation = false;
						try{
								$variation = wc_get_product($product_var_id);
							}catch(Exception $e){$variation_exists = false;}
							
						if($variation_exists && $product_var_id && ($disable_stacking_for_variation || $product->get_meta('force_disable_stacking_for_variation')))	
						{
							$show_upload_field_for_each_variation = true;
							$product_in_order =  wc_get_product($product->get_variation_id() ? $product->get_variation_id()  : $product->get_product_id() ); //$order->get_product_from_item( $product );
							$product_id .= "-".$product_var_id;
							
							if($display_product_fullname)
								$product_name = $variation->get_title()." - ";	
							$product_name_backend = $variation->get_title()." - ";
							$attributes_counter = 0;
							foreach($variation->get_attributes( ) as $attribute_name => $value){
								
								$product_name .= $attributes_counter > 0 && $display_product_fullname ? ", " : "";
								$product_name_backend .=  $attributes_counter > 0 ? ", " : "";
								$meta_key = urldecode( str_replace( 'attribute_', '', $attribute_name ) ); 
								
								if(isset($product['item_meta']) && !empty($product['item_meta']))
									foreach($product['item_meta'] as $attribute_name => $attribute_value)
										if($attribute_name == $meta_key && is_array($attribute_value) && isset($attribute_value[0]))
												$value = urldecode($attribute_value[0]);
								
								//wcuf_var_dump($product);
								
								if($display_product_fullname)
									$product_name .= " ".wc_attribute_label( $meta_key, $product_in_order ).": ".$value;
								$product_name_backend .= " ".wc_attribute_label( $meta_key, $product_in_order ).": ".$value;
								$attributes_counter++;
							} 
							
							$wc_price_calculator_is_active = $wcuf_product_model->wc_price_calculator_is_active_on_product( $variation );
						}
						else
						{
							$_product = wc_get_product( $product_id );
							if($_product)
							{
								$attributes = $_product->get_attributes( );
								$product_name = $current_page != 'product' ? $_product->get_title() : "";
								$product_in_order = apply_filters( 'woocommerce_get_product_from_item', $_product, $product, null );
								$product_name .= !empty($attributes) && $display_simple_product_name_with_attributes ? " - " : "";
								$attributes_counter = 0;							
								foreach($attributes as $attribute)
								{
									$product_name .= $attributes_counter > 0  && $display_simple_product_name_with_attributes ? ", " : "";
									$product_name_backend .=  $attributes_counter > 0 ? ", " : "";
									$meta_key = urldecode( str_replace( 'attribute_', '', $attribute->get_name() ) ); 
									if($display_simple_product_name_with_attributes)
										$product_name .= " ".wc_attribute_label( $meta_key, $product_in_order ).": ".$_product->get_attribute($attribute->get_name() );
										
									$product_name_backend .= " ".wc_attribute_label( $meta_key, $product_in_order ).": ".$_product->get_attribute($attribute->get_name() );
									
									$attributes_counter++;
								}
								$wc_price_calculator_is_active = $wcuf_product_model->wc_price_calculator_is_active_on_product( wc_get_product($product_id) );
							}
						}
					
					$upload_field_unique_title = $file_fields['title'].' ('.$product_name_backend.')';
					$file_field_title = $file_fields['title'];
					 
					//Wc price calclator managment (if active)
					$unique_product_name_hash = $addtional_id_on_title = "";	
					if($wc_price_calculator_is_active && ($disable_stacking_for_variation || $product->get_meta('force_disable_stacking_for_variation')))
					{
						$measures_string = $wcuf_product_model->wc_price_calulator_get_order_item_name($product);
						//wcuf_var_dump($measures_string);
						$product_name .= $measures_string;
						$product_name_backend .= $measures_string;
						$upload_field_unique_title = $file_fields['title'].' ('.$product_name.')';
						/* $unique_product_name_hash = $wcuf_product_model->wc_price_calulator_get_unique_product_name_hash($upload_field_unique_title);
						$product_id .= "-".$unique_product_name_hash; */
						$product_id .= !$disable_stacking_for_variation || !$product_var_id || $product_var_id == "" ? "-0"."-idsai".$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name) : "-idsai".$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name);
					}
					//individual product managment
					else if($wcuf_product_model->sold_as_individual_product($product->get_product_id(), $product->get_variation_id()) && 
								$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name) != "") 
					{	
						$product_id .= !$disable_stacking_for_variation || !$product_var_id || $product_var_id == "" ? "-0"."-idsai".$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name) : "-idsai".$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name);
						$addtional_id_on_title = /* !$wcuf_product_model->is_variable($product->get_product_id()) || $show_upload_field_for_each_variation ?  */" ".$button_texts['cart_individual_item_identifier'].$product->get_meta(WCUF_Cart::$sold_as_individual_item_cart_key_name) /* : "" */;
						$product_name .= $addtional_id_on_title;
						$upload_field_unique_title = $file_fields['title'].' ('.$product_name_backend.$addtional_id_on_title.') ';
					}
						
					//sanatize 
					$file_fields['title'] = str_replace($bad_chars, "",$file_fields['title']);
					$product_name = str_replace($bad_chars, "",$product_name);
					$addtional_id_on_title = str_replace($bad_chars, "",$addtional_id_on_title);
					
				
					
					if($file_order_metadata && isset($file_order_metadata[$file_fields['id']."-".$product_id]['url']))
						$file_order_metadata[$file_fields['id']."-".$product_id]['url'] = $wcuf_upload_field_model->get_secure_urls($order_id, $file_fields['id']."-".$product_id, $file_order_metadata);
					
					$uploaded_file_data = !isset($file_order_metadata[$file_fields['id']."-".$product_id]) ? null : $file_order_metadata[$file_fields['id']."-".$product_id];
					
					$num_of_uploaded_files = $wcuf_upload_field_model->get_num_uploaded_files($order_id, $file_fields['id']."-".$product_id, $all_options['max_uploaded_files_number_considered_as_sum_of_quantities']);
					//wcuf_var_dump($num_of_uploaded_files);
					$upload_has_been_performed = isset($uploaded_file_data) ? true : false;
					$is_multiple_file_upload = !isset($file_order_metadata[$file_fields['id']."-".$product_id]['is_multiple_file_upload']) ? false : $file_order_metadata[$file_fields['id']."-".$product_id]['is_multiple_file_upload'];
					$multiple_uploads_max_files = $upload_has_been_performed ? 0 : 1;
					$multiple_uploads_min_files = 1;
					$unlimited_uploads = !$multiple_uploads_max_files_depends_on_quantity && $file_fields['multiple_uploads_max_files'] == 0 ? true : false;
					$feedback_can_be_peformed = $upload_has_been_performed ? false : true;
					
					//Min/max uploadable files
					if($enable_multiple_uploads_per_field)
					{
						$multiple_uploads_max_files = $multiple_uploads_max_files_depends_on_quantity ? $product['qty'] : $file_fields['multiple_uploads_max_files'];
						$multiple_uploads_min_files = $multiple_uploads_min_files_depends_on_quantity  ? $product['qty'] : $multiple_uploads_minimum_required_files;
						
						//Incremental upload
						if($required_on_checkout)
							$multiple_uploads_min_files = $multiple_uploads_min_files == 0 ? 1 : $multiple_uploads_min_files;						
						$multiple_uploads_max_files =  $multiple_uploads_max_files != 0 && $multiple_uploads_max_files - $num_of_uploaded_files >= 0 ? $multiple_uploads_max_files - $num_of_uploaded_files : 0;
						$multiple_uploads_min_files = $num_of_uploaded_files > $multiple_uploads_min_files ? 0 : $multiple_uploads_min_files - $num_of_uploaded_files;
					
						$feedback_can_be_peformed =  $unlimited_uploads ||  $multiple_uploads_max_files > 0 ? true : false;
					}
					?>
					  <<?php echo $all_options['upload_field_title_style']; ?> style="margin-bottom:5px;  margin-top:15px;" class="wcuf_upload_field_title <?php if($required_on_checkout ) echo 'wcuf_required_label'; ?>"><?php  echo $file_field_title; ?></<?php echo $all_options['upload_field_title_style']; ?>>
					  <?php if(!empty($product_name)) echo '<'.$all_options['product_title_style'].' class="wcuf_product_title_under_upload_field_name">'.$product_name.'</'.$all_options['product_title_style'].'>'; ?>
					  <?php if(!$hide_upload_after_upload || ($hide_upload_after_upload && !$upload_has_been_performed)):?>
							<p class="wcuf_field_description"><?php echo do_shortcode($file_fields['description']); ?></p>
					   <?php endif; ?>
						<?php if($display_text_field): ?>
							<?php if($text_field_label != ""):?>
								<h5><?php echo $text_field_label; ?></h5>
							<?php endif; ?>
							<?php if ($text_field_description != ""): ?>
								<div class="wpuef_text_field_description"><?php echo $text_field_description; ?></div>
							<?php endif; ?>
							<textarea data-id="<?php echo $file_fields['id']."-".$product_id; ?>" class="wcuf_feedback_textarea" id="wcuf_feedback_textarea_<?php echo $file_fields['id']."-".$product_id; ?>" name="wcuf[<?php echo $file_fields['id']; ?>][user_feedback]" <?php if($is_text_field_required) echo 'required="required"'; if(!$feedback_can_be_peformed) echo "disabled";?> <?php if($text_field_max_input_chars != 0) echo 'maxlength="'.$text_field_max_input_chars.'"';?>><?php if(isset($uploaded_file_data) ) echo $uploaded_file_data['user_feedback'];?></textarea>
						<?php endif;?>
						<?php 
								//if(!$upload_has_been_performed /* !isset($uploaded_file_data ) */):
								 if(($enable_multiple_uploads_per_field && ($unlimited_uploads || $multiple_uploads_max_files > 0)) || !$upload_has_been_performed): 
									$render_upload_button = true;
									
								?>							
									<input type="hidden" name="wcuf[<?php echo $file_fields['id']."-".$product_id; ?>][title]" value="<?php echo $upload_field_unique_title; ?>"></input>
									<input type="hidden" name="wcuf[<?php echo $file_fields['id']."-".$product_id; ?>][id]" value="<?php echo $file_fields['id']."-".$product_id; ?>"></input>
									<input type="hidden" id="wcuf-filename-<?php echo $file_fields['id']."-".$product_id; ?>" name="wcuf[<?php echo $file_fields['id']."-".$product_id; ?>][file_name]" value=""></input>
									
									<?php if($display_disclaimer_checkbox): ?>
										<label class="wcuf_disclaimer_label" id="wcuf_disclaimer_label_<?php echo $file_fields['id']."-".$product_id; ?>"><input type="checkbox" class="wcuf_disclaimer_checkbox" id="wcuf_disclaimer_checkbox_<?php echo $file_fields['id']."-".$product_id; ?>"></input><?php echo $disclaimer_text;?></label>
									<?php endif; ?>
									<?php if(/* !$enable_multiple_uploads_per_field || */ $all_options['drag_and_drop_disable']):?>
										<button id="wcuf_upload_field_button_<?php echo $file_fields['id']."-".$product_id; ?>"  style="margin-right:<?php echo $css_options['css_distance_between_upload_buttons']; ?>px;" class="button wcuf_upload_field_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']."-".$product_id; ?>"><?php if(!$enable_multiple_uploads_per_field) echo $button_texts['drag_and_drop_area_single_file_instruction']; else echo $button_texts['drag_and_drop_area_instruction'];?></button>
									<?php else: ?>
										<div id="wcuf_upload_field_button_<?php echo $file_fields['id']."-".$product_id; ?>" class="wcuf_upload_field_button wcuf_upload_drag_and_drop_area" data-id="<?php echo $file_fields['id']."-".$product_id; ?>">
											<svg class="wcuf_drag_and_drop_area_icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43"><path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/></svg>
											<span class="wcuf_drag_and_drop_area_description"><?php echo !$enable_multiple_uploads_per_field ? $button_texts['drag_and_drop_area_single_file_instruction'] : $button_texts['drag_and_drop_area_instruction'];?></span>
										</div>
									<?php endif; ?>
									<button id="wcuf_upload_multiple_files_button_<?php echo $file_fields['id']."-".$product_id; ?>" class="button wcuf_upload_multiple_files_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']."-".$product_id; ?>"><?php echo $button_texts['upload_selected_files_button']; ?></button>
							
									<input type="file"  <?php //if($required_on_checkout ) echo 'required="required"'; ?> 
											data-title="<?php echo $upload_field_unique_title; ?>" 
											id="wcuf_upload_field_<?php echo $file_fields['id']."-".$product_id; ?>" 
											data-disclaimer="<?php echo $display_disclaimer_checkbox;?>" 
											data-required="<?php if($required_on_checkout) echo 'true'; else echo 'false' ?>" 
											data-id="<?php echo $file_fields['id']."-".$product_id; ?>" 
											data-min-files="<?php echo $multiple_uploads_min_files ?>" 
											data-max-files="<?php echo $multiple_uploads_max_files; ?>" 
											data-dimensions-logical-operator="<?php echo $dimensions_logical_operator; ?>" 
											data-max-width="<?php echo $max_width; ?>" 
											data-max-height="<?php echo $max_height; ?>"
											data-min-height="<?php echo $min_height_limit; ?>" 
											data-min-width="<?php echo $min_width_limit; ?>" 
											data-min-dpi="<?php echo $min_dpi_limit; ?>" 
											data-max-dpi="<?php echo $max_dpi_limit; ?>"
											data-ratio-x="<?php echo $ratio_x; ?>" 
											data-ratio-y="<?php echo $ratio_y; ?>"
											data-min-length="<?php echo $min_seconds_length; ?>" 
											data-max-length="<?php echo $max_seconds_length; ?>" 
											data-length-already-uploaded="<?php echo $wcuf_media_model->get_sum_of_media_length($uploaded_file_data); ?>" 
											data-consider-sum-length="<?php echo $consider_sum_of_media_seconds; ?>" 
											data-already-uploaded-length="<?php echo $consider_sum_of_media_seconds; ?>" 
											data-images-preview-disabled= "<?php echo $preview_images_before_upload_disabled ? 'true' : 'false';?>" 
											data-detect-pdf = "false" 
											data-enable-crop-editor="<?php echo $enable_crop_editor; ?>" 
											data-crop-mandatory-for-multiple-uploads="<?php echo $crop_mandatory_for_multiple_files_upload; ?>" 
											data-crop-area-shape="<?php echo $crop_area_type; ?>" 
											data-cropped-width="<?php echo $cropped_image_width; ?>" 
											data-cropped-height="<?php echo $cropped_image_height; ?>" 
											data-multiple-files-max-sum-size="<?php echo $multiple_files_max_size_sum; ?>"
											data-multiple-files-min-sum-size="<?php echo $multiple_files_min_size_sum; ?>"
											data-is-multiple-files="<?php if($enable_multiple_uploads_per_field) echo 'true'; else echo 'false'; ?>" 
											class="wcuf_file_input <?php if($enable_multiple_uploads_per_field) echo 'wcuf_file_input_multiple'; ?>" <?php if($enable_multiple_uploads_per_field)  echo 'multiple="multiple"'; ?> 
											name="wcufuploadedfile_<?php echo $file_fields['id']."-".$product_id; ?>" <?php if($file_fields['types'] != '') echo 'accept="'.$file_fields['types'].'"';?> 
											data-min-size="<?php echo $min_size; ?>"
											data-size="<?php echo $file_fields['size']*1048576; ?>" value="<?php echo $file_fields['size']*1048576; ?>" ></input>
									<?php if(!$hide_extra_info): ?>			
									<strong class="wcuf_max_size_notice" id="wcuf_max_size_notice_<?php echo $file_fields['id']."-".$product_id; ?>" >
										<?php if($min_size !=0) echo sprintf(__('Min size: %s MB', 'woocommerce-files-upload'), $min_size/1048576)."<br/>"; 
											  if($file_fields['size'] !=0) echo sprintf(__('Max size: %s MB', 'woocommerce-files-upload'),$file_fields['size'])."<br/>";  
											  if($enable_multiple_uploads_per_field && $multiple_uploads_min_files) echo __('Min files: ', 'woocommerce-files-upload').$multiple_uploads_min_files."<br/>"; 
											  if($enable_multiple_uploads_per_field && $multiple_uploads_max_files && !$unlimited_uploads) echo __('Max files: ', 'woocommerce-files-upload').$multiple_uploads_max_files."<br/>"; 
											  if($min_width_limit) echo __('Min width: ', 'woocommerce-files-upload').$min_width_limit."px"."<br/>"; 
											  if($max_width) echo __('Max width: ', 'woocommerce-files-upload').$max_width."px"."<br/>"; 
											  if($min_height_limit) echo __('Min height: ', 'woocommerce-files-upload').$min_height_limit."px"."<br/>"; 
											  if($max_height) echo __('Max height: ', 'woocommerce-files-upload').$max_height."px"."<br/>";   
											  if($min_dpi_limit) echo __('Min DPI: ', 'woocommerce-files-upload').$min_dpi_limit."px"."<br/>";   
											  if($max_dpi_limit) echo __('Max DPI: ', 'woocommerce-files-upload').$max_dpi_limit."px"."<br/>"; 
											  if($ratio_x && $ratio_y) echo __('Ratio: ', 'woocommerce-files-upload').$ratio_x.":".$ratio_y."<br/>"; 
											  if($min_seconds_length) echo __('Min length: ', 'woocommerce-files-upload').wcuf_format_seconds_to_readable_length($min_seconds_length)."<br/>"; 
											  if($max_seconds_length) echo __('Max length: ', 'woocommerce-files-upload').wcuf_format_seconds_to_readable_length($max_seconds_length);											  
										?>
										</strong>
										
									<?php endif;
										  if($enable_crop_editor): ?>
										<?php 
												$cropper_product_id = $file_fields['id']."-".$product_id;
												if(!$enable_multiple_uploads_per_field)
													include WCUF_PLUGIN_ABS_PATH.'/template/cropper.php'; 
												else
													include WCUF_PLUGIN_ABS_PATH.'/template/croppper_for_multiple.php';
										?>
									<?php endif; ?>
									
									<div class="wcuf_upload_status_box" id="wcuf_upload_status_box_<?php echo $file_fields['id']."-".$product_id; ?>">
										<div class="wcuf_multiple_file_progress_container" id="wcuf_multiple_file_progress_container_<?php echo $file_fields['id']."-".$product_id; ?>">
											<span class="wcuf_total_files_progress_bar_title"><?php _e('Total: ', 'woocommerce-files-upload'); ?></span>
											<div class="wcuf_bar" id="wcuf_multiple_file_bar_<?php echo $file_fields['id']."-".$product_id; ?>"></div>
											<div id="wcuf_multiple_file_upload_percent_<?php echo $file_fields['id']."-".$product_id; ?>"></div>
											<span class="wcuf_current_file_progress_bar_title" ><?php _e('Current: ', 'woocommerce-files-upload'); ?></span>
										</div>
										<div class="wcuf_bar" id="wcuf_bar_<?php echo $file_fields['id']."-".$product_id; ?>"></div >
										<div class="wcuf_percent" id="wcuf_percent_<?php echo $file_fields['id']."-".$product_id; ?>">0%</div>
										<div class="wcuf_status"  id="wcuf_status_<?php echo $file_fields['id']."-".$product_id; ?>"></div>
									</div>
									<div id="wcuf_file_name_<?php echo $file_fields['id']."-".$product_id; ?>" class="wcuf_file_name"></div>
									<div class="wcuf_multiple_files_actions_button_container" id="wcuf_multiple_files_actions_button_container_<?php echo $file_fields['id']."-".$product_id ?>">
										<button class="button wcuf_just_selected_multiple_files_delete_button" id="wcuf_just_selected_multiple_files_delete_button_<?php echo $file_fields['id']."-".$product_id; ?>" data-id="<?php echo $file_fields['id']."-".$product_id; ?>"><?php  _e('Delete selected files', 'woocommerce-files-upload'); ?></button>
										<button id="wcuf_upload_multiple_files_mirror_button_<?php echo $file_fields['id']."-".$product_id; ?>" class="button wcuf_upload_multiple_files_mirror_button <?php echo $additional_button_class;?>" data-id="<?php echo $file_fields['id']."-".$product_id; ?>"><?php echo $button_texts['upload_selected_files_button']; ?></button>
									</div>
									<div class="wcuf_deleting_box" id="wcuf_deleting_box_<?php echo $file_fields['id']."-".$product_id; ?>">
										<?php _e('Deleting, please wait...', 'woocommerce-files-upload');  ?>
									</div>
									<div id="wcuf_delete_button_box_<?php echo $file_fields['id']."-".$product_id; ?>" >
									</div>
						<?php //else: //$upload_has_been_performed : data has not been uploaded
								endif;
								if($upload_has_been_performed): ?>
								<div class="wcuf_already_uploaded_data_container"><?php 
								if(!isset($file_fields['message_already_uploaded']))
								{
									//_e('File already uploaded.', 'woocommerce-files-upload'); 
								}
								else
								{
									$already_uploaded_message = $file_fields['message_already_uploaded'];
									
									//[file_name] & [file_name_no_cost]
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name]', $already_uploaded_message, $file_fields, $uploaded_file_data,  false, $order_id, $file_fields['user_can_delete']);
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_no_cost]', $already_uploaded_message, $file_fields, $uploaded_file_data,  false, $order_id, $file_fields['user_can_delete']);
									//[file_name_with_image_preview] & [file_name_with_image_preview_no_cost]
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_image_preview]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);//old
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_media_preview]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_image_preview_no_cost]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);//old
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[file_name_with_media_preview_no_cost]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									//[image_preview_list] 
									$already_uploaded_message = $wcuf_shortcodes->get_file_names('[image_preview_list]',$already_uploaded_message, $file_fields, $uploaded_file_data, true, $order_id, $file_fields['user_can_delete']);
									//[uploaded_files_num]
									$already_uploaded_message = $wcuf_shortcodes->uploaded_files_num($already_uploaded_message, $file_fields, $uploaded_file_data);
									//[additional_costs]
									$already_uploaded_message = $wcuf_shortcodes->additional_costs($already_uploaded_message, $file_fields_groups, $uploaded_file_data, $file_fields,$product);
									
									$num_total_uploaded_files += isset($uploaded_file_data["quantity"]) ? count($uploaded_file_data["quantity"]) : 0; //each quantity element contains the selected quantity for each file
									//$total_costs += $wcuf_shortcodes->total_costs();
									
									echo do_shortcode($already_uploaded_message);
								}
								?></div>
							 <?php if($file_fields['user_can_delete']):?>
									<button class="button delete_button wcuf_delete_button" data-temp="no" data-id="<?php echo $file_fields['id']."-".$product_id;?>"><?php  echo $button_texts['delete_file_button']; ?></button>
							<?php endif; ?>
							<?php if($file_fields['user_can_download_his_files'] && isset($file_order_metadata[$file_fields['id']."-".$product_id]) && !$is_multiple_file_upload): 
									if(isset($file_order_metadata[$file_fields['id']."-".$product_id]['url'][0]) || isset($file_order_metadata[$file_fields['id']."-".$product_id]['url'])):
										$file_url = isset($file_order_metadata[$file_fields['id']."-".$product_id]['url'][0]) ? $file_order_metadata[$file_fields['id']."-".$product_id]['url'][0] : $file_order_metadata[$file_fields['id']."-".$product_id]['url'];?>
										<a class="button download_button" href="<?php echo $file_url; ?>" target="_blank" download><?php  _e('Download / View file(s)', 'woocommerce-files-upload'); ?></a>
							<?php endif; endif; ?>	
					<?php endif; ?>
						<!-- <div class="wcuf_spacer2"></div> -->
						</div> <!-- <div class="wcuf_upload_fields_row_element"> -->
					<?php endforeach; //products_for_which_stacking_is_disabled
				endif;//disable stacking ?>
			</div> <!--wcuf_single_upload_field_container-->
			<?php endif;//can render
		}
	endforeach; //upload field 
	
	//Totals area
	if($current_page == 'product' && $all_options['display_totals_in_product_page']): ?>
		<div class="wcuf_totals_container">
		<<?php echo $all_options['upload_field_title_style']; ?> class="wcuf_totals_area_title"><?php echo $button_texts['totals_area_title'] ?></<?php echo $all_options['upload_field_title_style']; ?>>
		<?php
			if(in_array('num_files',$all_options['totals_info_to_display'])) //num_files, costs
				echo "<div class='wcuf_total_single_line_container'><span class='wcuf_extra_costs_label'>".$button_texts['totals_num_files_label']."</span> <span class='wcuf_extra_costs_value'>".$num_total_uploaded_files."</span></div>";
			/*if(in_array('costs',$all_options['totals_info_to_display'])) 
				echo "<div class='wcuf_total_single_line_container'><span class='wcuf_extra_costs_label'>".$button_texts['totals_extra_costs_label']."</span> <span class='wcuf_extra_costs_value'>".wc_price($total_costs)."</span></div>";
			*/
		?>
		</div>
	<?php endif; ?>
	<?php
		if($render_upload_button && false): //never rendered anymore ?> 
			<div id="wcuf_save_uploaded_files_button_area">
				<h4><?php echo $button_texts['order_page_save_uploaded_files_title']; ?></h4>
				<button name="upload_button" id="wcuf_upload_button" class="button" ><?php echo $button_texts['save_uploads_button']; ?></button>
			</div>
			<div class="wcuf_spacer"></div>
		<?php endif; ?>
</div><!-- wcuf_file_uploads_container -->

<div id="wcuf_progress">
	<h4 id="wcuf_upload_message"><?php echo $button_texts['save_in_progress_message']; ?></h4>
	<div id="wcuf_infinite_bar" style="background-image: url('<?php echo wcuf_PLUGIN_PATH;?>/img/loader.gif');"></div >
   <div id="wcuf_status"></div>
</div>

<?php 
//Summary data
$summary_box_data = array();
$all_uploaded_data = $file_order_metadata;
$num_total_uploaded_files = 0;
if($display_summary_box != 'no' && isset($all_uploaded_data) && !empty($all_uploaded_data))
{
	
	foreach($all_uploaded_data as $completed_upload)
	{
		foreach($file_fields_groups as $file_fields_group)
		{
			/* if(!isset($completed_upload['id']))
				continue; */
			$field_id = explode("-",$completed_upload['id']); //It may happen that upload fileds are deleted. It make sure that are displayed files for active upload fields
			if($file_fields_group['id'] == $field_id[0])
			{
				$num_total_uploaded_files += count($completed_upload["quantity"]);
				if(!isset( $summary_box_data[$completed_upload['title']]))
					$summary_box_data[$completed_upload['title']] = array();
				
					$summary_box_data[$completed_upload['title']] = $wcuf_shortcodes->get_file_names('[file_name_with_image_preview]', '[file_name_with_image_preview]',$file_fields, $completed_upload, in_array('preview_image', $summary_box_info_to_display), $order_id, false, $summary_box_info_to_display);
			}
		}
	}
}

if(!empty($summary_box_data) && in_array($current_page, $display_summary_box)): ?>
	<div id="wcuf_summary_uploaded_files">
		<h2 class="wcuf_upload_summary_title"><?php _e('Uploads Summary', 'woocommerce-files-upload');?></h2>
		<?php foreach($summary_box_data as $title => $file_list): ?>
			<div class="wcuf_summary_file_list_block">
				<h4 class="wcuf_upload_field_title wcuf_summary_uploaded_files_title"><?php echo $title; ?></h4>
				<?php echo $file_list; ?>
			</div>
			<!--<div class="wcuf_summary_uploaded_files_list_spacer"></div>-->
		<?php endforeach; ?>
		<?php //Totals area
		if($all_options['display_totals_in_summary_boxes']): ?>
			<h3 class="wcuf_upload_field_title wcuf_summary_uploaded_files_title wcuf_summary_box_totals_area_title"><?php echo $button_texts['totals_area_title'] ?></h3>
			<div class="wcuf_summany_box_totals_container">
			<?php
				if(in_array('num_files',$all_options['totals_info_to_display'])) //num_files, costs
					echo "<div class='wcuf_total_single_line_container'><span class='wcuf_extra_costs_label'>".$button_texts['totals_num_files_label']."</span> <span class='wcuf_extra_costs_value'>".$num_total_uploaded_files."</span></div>";
				/* if(in_array('costs',$all_options['totals_info_to_display'])) 
					echo "<div class='wcuf_total_single_line_container'><span class='wcuf_extra_costs_label'>".$button_texts['totals_extra_costs_label']."</span> <span class='wcuf_extra_costs_value'>".wc_price($total_costs)."</span></div>";
				*/
			?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; 
//End //Summary data ?>