<?php
// Uploaded files preview rendering
$non_images_uploads = array();
$title_html = "<h4>".__('Uploaded images:', 'woocommerce-files-upload')."</h4>";
$title_html_rendered = false;
$exists_at_least_one_non_image_to_list = false;
foreach($current_product_uploads as $upload_id => $upload)
{
	//Image preview
	$non_images_uploads[$upload_id] = array();
	$upload['url'] = $wcuf_upload_field_model->get_secure_urls($order_id, $upload_id, $current_product_uploads);
	foreach((array)$upload['url'] as $index => $upload_url)
	{
		$feedback = wcuf_get_value_if_set($upload, 'user_feedback', "");
		$file_abs_path = $upload['absolute_path'][$index];
		if(!$disable_image_preview && $wcuf_media_model->is_image($file_abs_path))
		{
			if(!$title_html_rendered)
			{
				$title_html_rendered = true;
				echo $title_html;
			}			
			echo "<div class='wcuf_image_preview_container'>
						<img class='wcuf_image_preview' src='{$upload_url}'  width='120' />
						<span class='wcuf_file_name_text'>".$upload['original_filename'][$index]."</span>
						<span class='wcuf_file_quantity_text'>".__('Quantity: ','woocommerce-files-upload').$upload['quantity'][$index]."</span>";
				if($feedback != "")
						echo "<span class='wcuf_feedback_text'>".__('Feedback: ','woocommerce-files-upload').$feedback."</span>";
			echo	 "</div>";
		}
		else 
		{
			$exists_at_least_one_non_image_to_list = true;
			$non_images_uploads[$upload_id][$index] =  array('name'=> $upload['original_filename'][$index], 'quantity' => $upload['quantity'][$index], 'feedback' => $feedback);
		}
	}
}

//Non images preview
//if(!empty($non_images_uploads))
if($exists_at_least_one_non_image_to_list)
{
	echo "<h4>".__('Uploaded files list:', 'woocommerce-files-upload')."</h4>";
	echo "<ol class='wcuf_non_image_list'>";
	foreach($non_images_uploads as $file_current_data)
		foreach($file_current_data as $file_data)
		{
			echo "<li class='wcuf_non_image_element'>
						<span class='wcuf_file_name_text'>".$file_data['name']."</span>
						<span class='wcuf_file_quantity_text'>".__('Quantity: ', 'woocommerce-files-upload').$file_data['quantity']."</span>";	
				if($file_data['feedback'] != "")
						echo "<span class='wcuf_feedback_text'>".__('Feedback: ','woocommerce-files-upload').$file_data['feedback']."</span>";
			echo  "</li>";
		}
	echo "</ol>";
}
	
?>