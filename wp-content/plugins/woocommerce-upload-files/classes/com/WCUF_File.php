<?php
class WCUF_File
{
	var $current_order;
	var $email_sender;
	var $file_zip_name = 'multiple_files.zip';
	var $dropbox;
	var $to_remove_from_file_name = array(".php", "../", ".jsp", ".vbs", ".exe", ".bat", ".php5", ".pht", ".phtml", 
										  ".shtml", ".asa", ".cer", ".asax", ".swf", ".xap", ";", ".asp", ".aspx",
										  "*", "<", ">", "::");
	var $saving_on_session = false;
	public function __construct()
	{
		add_action( 'before_delete_post', array( &$this, 'delete_all_order_uploads' ), 10 );
		//Ajax
		add_action( 'wp_ajax_upload_file_during_checkout_or_product_page', array( &$this, 'ajax_save_file_on_session' ));
		add_action( 'wp_ajax_nopriv_upload_file_during_checkout_or_product_page', array( &$this, 'ajax_save_file_on_session' ));
		
		add_action( 'wp_ajax_save_uploaded_files_on_order_detail_page', array( &$this, 'ajax_save_file_uploaded_from_order_detail_page' ));
		add_action( 'wp_ajax_nopriv_save_uploaded_files_on_order_detail_page', array( &$this, 'ajax_save_file_uploaded_from_order_detail_page' ));
		
		add_action( 'wp_ajax_upload_file_on_order_detail_page', array( &$this, 'ajax_upload_file_on_order_detail_page' ));
		add_action( 'wp_ajax_nopriv_upload_file_on_order_detail_page', array( &$this, 'ajax_upload_file_on_order_detail_page' ));
		
		add_action( 'wp_ajax_wcuf_file_chunk_upload', array( &$this, 'ajax_manage_file_chunk_upload' ));
		add_action( 'wp_ajax_nopriv_wcuf_file_chunk_upload', array( &$this, 'ajax_manage_file_chunk_upload' ));
		
		add_action( 'wp_ajax_delete_file_on_order_detail_page', array( &$this, 'ajax_delete_file_on_order_detail_page' ));
		add_action( 'wp_ajax_nopriv_delete_file_on_order_detail_page', array( &$this, 'ajax_delete_file_on_order_detail_page' ));
		
		add_action( 'wp_ajax_delete_file_during_checkout_or_product_page', array( &$this, 'ajax_delete_file_from_session' ));
		add_action( 'wp_ajax_nopriv_delete_file_during_checkout_or_product_page', array( &$this, 'ajax_delete_file_from_session' ));
		
		add_action( 'wp_ajax_delete_single_file_on_order_detail_page', array( &$this, 'ajax_delete_single_file_from_order' ));
		add_action( 'wp_ajax_nopriv_delete_single_file_on_order_detail_page', array( &$this, 'ajax_delete_single_file_from_order' ));
		
		add_action( 'wp_ajax_delete_single_file_during_checkout_or_product_page', array( &$this, 'ajax_delete_single_file_from_session' ));
		add_action( 'wp_ajax_nopriv_delete_single_file_during_checkout_or_product_page', array( &$this, 'ajax_delete_single_file_from_session' ));
		
		add_action('init', array( &$this, 'get_file_in_zip' ));
		add_action('init', array( &$this, 'process_drobpox_temp_link_request' )); 
		add_action('init', array( &$this, 'zip_upload_field_files_and_download' )); 
		add_action('init', array( &$this, 'process_secure_link_request' )); 

	}
	public static function return_bytes($val) 
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$val = intval (substr($val, 0, -1));
		
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				//$val *= 1024;
				$val *= 1024;
			case 'm':
				$val *= 1;
				break;
			case 'k':
				$val = 1;
		}
		
		return $val;
	}
	function process_secure_link_request()
	{
		if(!isset($_GET['wcuf_order_id']) || !isset($_GET['wcuf_upload_id']) || !isset($_GET['wcuf_index']))
			return;
		
		global $wcuf_upload_field_model, $wcuf_option_model;
		$order_id = $_GET['wcuf_order_id'];
		$upload_id = $_GET['wcuf_upload_id'];
		$index = $_GET['wcuf_index'];
		
		$wc_order = wc_get_order($order_id );
		$secure_links = $wcuf_option_model->get_all_options('secure_links', false);
		if($secure_links && $wc_order && $wc_order->get_customer_id() && $wc_order->get_customer_id() != get_current_user_id() && !current_user_can( 'manage_woocommerce' ))
			exit;
		
		$uploaded_files_metadata = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order_id);
		$file_to_serve = $uploaded_files_metadata[$upload_id]['absolute_path'][$index];
		
		if($uploaded_files_metadata[$upload_id]['source'][$index] == 'local')
			$this->output_file($uploaded_files_metadata[$upload_id]['absolute_path'][$index]);
		else wp_redirect($uploaded_files_metadata[$upload_id]['url'][$index]);
		
		
		exit;
	}
	function process_drobpox_temp_link_request()
	{
		if(!isset($_GET['dropbox_get_item_link']))
			return;
		
		$file_path = $_GET['dropbox_get_item_link'];
		$dropbox = new WCUF_DropBox();
		wp_redirect( $dropbox->getTemporaryLink($file_path) );
		exit;
	}
	function zip_upload_field_files_and_download() 
	{
		$create_single_zip_file_for_order = false;
		if(isset($_GET['wcuf_create_single_zip_for_order']))
		{
			$create_single_zip_file_for_order = true;
		}
		else if(!isset($_GET['wcuf_create_zip_for_field']) || !isset($_GET['wcuf_order_id']) || !class_exists('ZipArchive'))
			return;
		
		$user = wp_get_current_user();
		$allowed_roles = array('shop_manager', 'administrator');
		
		global $wcuf_upload_field_model;
		
		$order_id = !$create_single_zip_file_for_order ? $_GET['wcuf_order_id'] : $_GET['wcuf_create_single_zip_for_order'];
		$file_meta = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order_id);
			
		if(!$create_single_zip_file_for_order)
		{
			if(!isset($file_meta[$_GET['wcuf_create_zip_for_field']]))
				return;
		
			$files_to_zip = $wcuf_upload_field_model->can_be_zip_file_created_upload_field_content($file_meta[$_GET['wcuf_create_zip_for_field']]);
		}
		else
		{
			$files_to_zip = array();
			foreach($file_meta as $index => $data)
			{
				$result = $wcuf_upload_field_model->can_be_zip_file_created_upload_field_content($file_meta[$index]);
				//wcuf_var_dump($result);
				$files_to_zip = !empty($result) ? array_merge($files_to_zip, $result) : $files_to_zip;
			}
		}
		
		$zip = new ZipArchive();
		$filename = @tempnam("tmp", "zip");
		if (empty($files_to_zip) || $zip->open($filename, ZipArchive::OVERWRITE)!==TRUE) {
			return;
		}
		foreach($files_to_zip as $file_data)
		{
			
			$zip->addFile($file_data['path'], $file_data['name']);
		}
		
		$zip->close();			
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($filename));
		header('Content-Disposition: attachment; filename="'.$order_id.'_wcuf_files.zip"');
		
		//File read
		$handle = fopen($filename, 'rb'); 
		$buffer = ''; 
		while (!feof($handle)) 
		{ 
			$buffer = fread($handle, 4096); 
			echo $buffer; 
			@ob_flush(); 
			flush(); 
		} 
		fclose($handle); 
		  
		unlink($filename); 
		die();
	}
	//OLD METHOND: no longer used after 18.6
	//Used ONLY in Admin order details page, when are generated links to uploaded files. In case of dropbox ZIP this method is prevented to be used.
	public function get_file_in_zip() 
	{
		if(!isset($_GET['wcuf_zip_name']) || !isset($_GET['wcuf_single_file_name']) || !isset($_GET['wcuf_order_id']))
			return;
		
		$user = wp_get_current_user();
		$allowed_roles = array('shop_manager', 'administrator');
		//if(!current_user_can( 'manage_options' ) || !current_user_can( 'manage_woocommerce' ))
		//if($user && !array_intersect($allowed_roles, $user->roles ))
		if(!is_user_logged_in() || (!array_intersect($allowed_roles, $user->roles ) && !current_user_can( 'manage_woocommerce' )))
		{
			_e('You are not authorized', 'woocommerce-files-upload');
			return;
		}
		
		$path = $_GET['wcuf_zip_name'];
		$single_file_name = $_GET['wcuf_single_file_name'];
		$temp_dir = $this->get_temp_dir_path($_GET['wcuf_order_id']);
		
		$z = new ZipArchive();
		if ($z->open($temp_dir.$path)) {
			$file_string = $z->getFromName($single_file_name);			
			$z->close();	
			header("Content-length: ".strlen($file_string));
			//header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename=".$single_file_name.";" );
			header('Content-Transfer-Encoding: chunked');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			echo $file_string;
		}
		else
		{
			_e('Error opening the file', 'woocommerce-files-upload');
			return;
		}
		wp_die();
	}
	private function output_file($path)
	{
		/* $path = $attachments_meta[$file_id]['absolute_path'];*/
		$size = filesize($path); 
		$fileName = basename($path);
		
		$preview_method = 'standard_method';
		
		header("Content-length: ".$size);
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=".$fileName.";" );
		
		//header('Content-Transfer-Encoding: binary');
		header('Content-Transfer-Encoding: chunked');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header("Content-Type: application/download");
		header('Content-Description: File Transfer');
		header('Content-Type: application/force-download');
		//echo $content;
		//ob_clean();
		
		if($preview_method == 'standard_method')
			readfile($path);
		
		else
		{
			if ($fd = fopen ($path, "r")) 
			{

				set_time_limit(0);
				ini_set('memory_limit', '1024M');
				ob_clean();
				flush();
				
				while(!feof($fd)) 
				{
					echo fread($fd, 1024);
					flush();
				}   
				ob_end_flush();
			 fclose($fd);
			} 
		}
		exit(); 
	}
	private function create_empty_file($path)
	{
		$file = fopen($path, 'w'); 
		fclose($file); 
	}
	private function create_folder($folder_name)
	{
		$upload_dir = wp_upload_dir();
		$base_path = $upload_dir['basedir']."/wcuf/";
		 
		if (!file_exists($base_path)) 
			mkdir($base_path, 0775, true);
		
		if( !file_exists ($base_path.'/index.html'))
			$this->create_empty_file ($base_path.'/index.html');
		
		if (!file_exists($base_path.$folder_name)) 
			mkdir($base_path.$folder_name, 0775, true);
		
		if( !file_exists ($base_path.$folder_name.'/index.html'))
			$this->create_empty_file  ($base_path.$folder_name.'/index.html');
		
		return $base_path.$folder_name;
	}
	function ajax_delete_file_on_order_detail_page()
	{
		global $wcuf_upload_field_model;
		if(!isset($_POST) || !isset($_POST['is_temp']) || !isset($_POST['order_id']) || $_POST['order_id'] == 0)
			return;
		
		if($_POST['is_temp'] == 'no')
		{
			global $wcuf_option_model;
			$file_order_metadata = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($_POST['order_id']);
			$this->delete_file($_POST['id'], $file_order_metadata, $_POST['order_id']);
		}
		else
		{
			$this->ajax_delete_file_from_session(true);
		}
		wp_die();
	}
	function ajax_delete_file_from_session($is_order_detail_page = false)
	{
		global $wcuf_session_model,$wcuf_cart_model ;
		$wcuf_upload_unique_name = isset($_POST['id']) ? $_POST['id']:null;
		if(isset($wcuf_upload_unique_name))
		{
			$wcuf_session_model->remove_item_data($wcuf_upload_unique_name, $is_order_detail_page);
		}
		wp_die();
	}
	function ajax_delete_single_file_from_order()
	{
		global $wcuf_order_model;
		$single_file_id = isset($_POST['id']) ? $_POST['id'] : null;
		$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : null;
		$field_id = isset($_POST['field_id']) && $_POST['field_id'] >= 0 ? $_POST['field_id'] : null;
		if($single_file_id != null && $order_id != null && $field_id != null)
		{
			$wcuf_order_model->remove_single_file_form_order_uploaded_data($order_id, $field_id, $single_file_id);
		}
		wp_die();
	}
	function ajax_delete_single_file_from_session()
	{
		global $wcuf_session_model;
		$single_file_id = isset($_POST['id']) ? $_POST['id'] : null;
		$field_id = isset($_POST['field_id']) && $_POST['field_id'] >= 0 ? $_POST['field_id'] : null;
		if($single_file_id != null && $field_id != null)
		{
			$wcuf_session_model->remove_upload_field_subitem($field_id, $single_file_id);
		}
		wp_die();
	}
	//Called when on Order details / Thank you page upload are saved
	function ajax_save_file_uploaded_from_order_detail_page()
	{
		global $wcuf_option_model, $wcuf_session_model,$wcuf_upload_field_model, $wcuf_option_model;
		$temp_uploads = $wcuf_session_model->get_item_data(null,null,true);
		if(!isset($_POST) || $_POST['order_id'] == 0)
			return;
		
		$order_id = $_POST['order_id'];
		
		if(!empty($temp_uploads))
		{
			$order = wc_get_order($order_id);
			$status_change_options = $wcuf_option_model->get_order_stratus_change_options();
			$file_fields_groups =  $wcuf_option_model->get_fields_meta_data();
			$file_order_metadata = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order_id); //$wcuf_option_model->get_order_uploaded_files_meta_data($order_id);
			//Merge session arrays with order_meta arrays
			$file_order_metadata = $this->upload_files($order, $file_order_metadata, $file_fields_groups, $temp_uploads);
			
					
			if($status_change_options["order_details_page_change_order_status"] != false && 
			   $status_change_options["status_to_assign"] != false && 
			   (empty($status_change_options["current_status_to_consider"]) || in_array($order->get_status(),  $status_change_options["current_status_to_consider"]))
			  )
			{
				$order->set_status($status_change_options["status_to_assign"]);
				$order->save();
			}
		}
		$wcuf_session_model->remove_item_data(null, true);
		wp_die();
	}
	function is_saving_on_session()
	{
		if($this->saving_on_session)
			return true;
		
		//wcuf_var_dump($_POST['action']);
		if(isset($_POST['action']) && ($_POST['action'] == 'wcuf_file_chunk_upload' || 
									   $_POST['action'] == 'upload_file_during_checkout_or_product_page' || 
									   $_POST['action'] == 'save_uploaded_files_on_order_detail_page' || 
									   $_POST['action'] == 'reload_upload_fields' || 
									   $_POST['action'] == 'upload_file_on_order_detail_page'))
									   {
										   //wcuf_var_dump("saving");
										 return true;
									   }
		
		return false;
	}
	function ajax_upload_file_on_order_detail_page()
	{
		$this->saving_on_session = true;
		$this->ajax_save_file_on_session(true);
	}
	private function any_upload_error_due_to_bad_server_paramenter()
	{
		if(empty($_FILES) /* && empty($_POST) */ && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{ 
			return true;
		}
		return false;
	}
	function check_if_there_was_uploading_errors()
	{
		//wcuf_var_dump($_FILES);
		if(empty($_FILES) && isset($_POST['action']) && $_POST['action'] == 'upload_file_on_order_detail_page')
		{
			include WCUF_PLUGIN_ABS_PATH.'/template/upload_error_due_to_bad_server_php_settings.php';
			wp_die();
		}
	}
	function ajax_manage_file_chunk_upload()
	{
		global $wcuf_session_model ;
		
		if(!isset($_POST['wcuf_upload_field_name']))
			wp_die();
		
		$this->saving_on_session = true;
		$buffer = 5242880; //1048576; //1mb
		$target_path = $this->get_temp_dir_path();
		$tmp_name = $_FILES['wcuf_file_chunk']['tmp_name'];
		$size = $_FILES['wcuf_file_chunk']['size'];
		$current_chunk_num = $_POST['wcuf_current_chunk_num'];
		$file_name = str_replace($this->to_remove_from_file_name, "",$_POST['wcuf_file_name']);
		$tmp_file_name = $_POST['wcuf_current_upload_session_id']."_".$file_name;
		$upload_field_name = str_replace($this->to_remove_from_file_name, "", $_POST['wcuf_upload_field_name']);
		$wcuf_is_last_chunk = $_POST['wcuf_is_last_chunk'] == 'true' ? true : false;
	
		$com = fopen($target_path.$tmp_file_name, "ab");
		$in = fopen($tmp_name, "rb");
			if ( $in ) 
				while ( $buff = fread( $in, $buffer ) ) 
				   fwrite($com, $buff);
				 
			fclose($in);
		fclose($com);
		
		wp_die();
	}
	function ajax_save_file_on_session($is_order_detail_page = false)
	{
		if(!isset($_POST))
		{
			wp_die();
		}
		
		global $wcuf_session_model,$wcuf_cart_model, $wcuf_media_model;
		
		/*Format 
		//$_POST
		array(1) {
			  ["action"]=>
			  string(27) "upload_file_during_checkout"
			   ["title"]=>
			  string(16) "test (Product 1)"
			  ["detect_pdf"]=>
				string(5) "false"
			  ["user_feedback"]=>
			  string(9) "undefined"
			  ["order_id"]=>
			  string(1) "0"
			  ["wcuf_wpml_language"]=>
			  string(4) "none"
			  ["multiple"]=>
			  string(3) "yes"
			  ["quantity_0"]=>
			  string(1) "1"
			  ["quantity_1"]=>
			  string(1) "1"
			}
		//$_FILES
		array(1) {
		  ["wcufuploadedfile_58"]=>
		  array(5) {
			["name"]=>
			string(15) "Snake_River.jpg"
			["type"]=>
			string(10) "image/jpeg"
			["tmp_name"]=>
			string(26) "/var/zpanel/temp/php7XJBgQ"
			["error"]=>
			int(0)
			["size"]=>
			int(5245329)
		  }
			}
		*/
		$unique_key = "";
		$num_files = 0;
		$upload_field_name = $_POST['title'];
		$getID3 = new getID3();
		$ID3_info = array();
		//if(count($_FILES) > 1 /* && class_exists('ZipArchive') */ ) //Multiple files
		{
			$filename = array();
			$file_names = array();
			$file_quantity = array();
			
			$wcuf_upload_unique_name = $_POST['upload_field_name'];
			$chunked_file_path = $this->get_temp_dir_path().$_POST['file_session_id']."_".$_POST['file_name'];
			$data = array('tmp_name' => $chunked_file_path, 'name' =>$_POST['file_name'], 'type' => $this->mime_content_type($chunked_file_path));
			
			//foreach($_FILES as $wcuf_upload_unique_name => $data)
			{
				//new zip file managment
				$filename[] = $data['tmp_name'];
				//end new
				if($unique_key == "")
					$unique_key = $wcuf_upload_unique_name;
				//$zip->addFile($data['tmp_name'], $data['name']);
				$file_names[$num_files] =  $data['name'];
				$curr_quantity = isset($_POST['quantity_'.$num_files]) ? $_POST['quantity_'.$num_files] : 1;
				$detect_pdf = isset($_POST['detect_pdf']) && $data['type'] == 'application/pdf' ? $_POST['detect_pdf'] : 'false';
				if($detect_pdf == 'true')
					$curr_quantity = $wcuf_media_model->pdf_count_pages($data['tmp_name']);
				//wcuf_var_dump($curr_quantity);
				$file_quantity[$num_files] =  $curr_quantity;
				//ID3 Info
				try{
					$file_id3 = $getID3->analyze($data['tmp_name']);
					//playtime_seconds
					//playtime_string
					
					if( (isset($file_id3['video']) || isset($file_id3['audio'])) && isset($file_id3['playtime_string']) )
						$ID3_info[$num_files] = array( 'file_name' => $data['name'],
											//'file_name_unique' => "", //lately filled in upload_file_method
											'index' => $num_files,
											'quantity' => $file_quantity[$num_files] ,
											'type' => isset($file_id3['video']) ? 'video' : 'audio',
											'playtime_seconds' => isset($file_id3['playtime_seconds']) ? $file_id3['playtime_seconds'] : 'none',
											'playtime_string' => isset($file_id3['playtime_string']) ? $file_id3['playtime_string'] : 'none');		
				}catch(Exception $e){}
				$num_files++;
			}
			$data = array('upload_field_id'=> $unique_key, 'tmp_name' => $filename, 'name'=>$file_names, 'quantity' => $file_quantity);
			$wcuf_session_model->set_item_data($unique_key, $data, false, $is_order_detail_page, $num_files, $ID3_info);
		}
		wp_die();
	}
	public function get_product_ids_and_field_id_by_file_id($temp_upload_id)
	{
		global $wcuf_upload_field_model;
		list($fieldname, $field_id_and_product_id) = explode("_", $temp_upload_id );
		$ids = explode("-", $field_id_and_product_id ); //0 => $field_id, 1 => $product_id, 2 => $variation_id, 3 => file title hash (only if 2 exists)
													    //													    3 can be the wcuf_unique id (it will have a prefix "idsai"). If so 2 always exists (it will be 0 for no variable products)
		 
		$variant_id = isset($ids[3]) || (isset($ids[2]) && is_numeric($ids[2])) ? $ids[2] : 0;
		$unique_product_name_hash = isset($ids[3]) ? $ids[3] : "";
		if(isset($ids[2]) && !is_numeric($ids[2]))
			$unique_product_name_hash = $ids[2];
		$is_sold_individually =  $wcuf_upload_field_model->is_individual_id_string($unique_product_name_hash);
		$unique_product_name_hash = $is_sold_individually ?  $wcuf_upload_field_model->get_individual_id_from_string($unique_product_name_hash) : $is_sold_individually;
																														//This can be the hash for WC Product measure products or the Unique id in case products are sold as "individual" (in this case it will have "idsai" prefix)
		return array('field_id' => $ids[0], 'product_id' => isset($ids[1]) ? $ids[1] : null, 'variant_id'=>$variant_id, 'unique_product_id'=>$unique_product_name_hash, 'fieldname'=>$fieldname, 'is_sold_individually' => $is_sold_individually);
	}
	private function mime_content_type($filename) 
	{
		$type = wp_check_filetype($filename);
		
		return $type['type'];
	}
	public function get_temp_url()
	{
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl']."/wcuf/tmp/";
	}
	public function get_temp_dir_path($order_id = null, $baseurl = false)
	{
		$upload_dir = wp_upload_dir();
		$temp_dir = !$baseurl ? $upload_dir['basedir']. '/wcuf/' : $upload_dir['baseurl']. '/wcuf/';
		$temp_dir .= isset($order_id) && $order_id !=0 ? $order_id.'/': 'tmp/';
		
		if(!$baseurl)
		{
			if (!file_exists($temp_dir)) 
					mkdir($temp_dir, 0775, true);
			
			if( !file_exists ($temp_dir.'index.html'))
				//touch ($temp_dir.'index.html');
				$this->create_empty_file  ($temp_dir.'index.html');
		}
		return $temp_dir;
	}
	public function wcuf_override_upload_directory( $dir ) 
	{ 
		global $wcuf_order_model;
		return array(
			'path'   => $dir['basedir'] . '/wcuf/'.$wcuf_order_model->get_order_id($this->current_order),//$this->current_order->id
			'url'    => $dir['baseurl'] . '/wcuf/'.$wcuf_order_model->get_order_id($this->current_order),//g$this->current_order->id
			'subdir' => '/wcuf/'.$wcuf_order_model->get_order_id($this->current_order),//$this->current_order->id
		) + $dir;
	}
	public function generate_unique_file_name($dir, $name)
	{
		global $wcuf_option_model;
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		$file_name = pathinfo($name, PATHINFO_FILENAME);
		//return  $wcuf_option_model->remove_file_name_prefix() == 'no' || $name == $this->file_zip_name ? rand(0,100000)."_".$name.$ext : $name.$ext;
		
		$random_name = $file_name."_".rand(0,100000).".".$ext;
		return  $wcuf_option_model->remove_file_name_prefix() == 'no' || $name == $this->file_zip_name ? $random_name : $name;
	}
	public function get_random_chunk_name()
	{
		$file_name = rand(0,100000);
		$dir = $this->get_chuck_upload_directory();
		
		while(!file_exists ($dir.$file_name))
			$file_name = rand(0,100000);
		
		return $file_name;
	}
	public function normalizeStringForFolderName ($str = '')
	{
		$str = strip_tags($str); 
		$str = preg_replace('/[\r\n\t ]+/', ' ', $str);
		$str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
		$str = strtolower($str);
		$str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
		$str = htmlentities($str, ENT_QUOTES, "utf-8");
		$str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
		$str = str_replace(' ', '-', $str);
		$str = rawurlencode($str);
		$str = str_replace('%', '-', $str);
		return $str;
	}
	public function manage_access_to_order_folder($order_id, $deny_access = true)
	{
		$upload_dir = wp_upload_dir();
		$upload_complete_dir = $upload_dir['basedir']. '/wcuf/'.$order_id.'/';
		if (!file_exists($upload_complete_dir)) //It means that is used a cloud service
				return;
			
		$htaccess = $upload_complete_dir.".htaccess";
		if($deny_access)
		{
			if(!file_exists($htaccess));
			{
				$f = fopen($htaccess, "a+");
				fwrite($f, "Deny from all");
				fclose($f);
			}
		}
		else 
		{
			if(file_exists($htaccess));
				@unlink ( $htaccess);
		}
	}
	public function upload_files($order,$file_order_metadata, $options, $temp_uploaded = null)
	{
		global $wcuf_option_model, $wcuf_upload_field_model, $wcuf_session_model, $wcuf_order_model, $wcuf_ftp_model;
		$order_id = $wcuf_order_model->get_order_id($order) ;	
		
		if(isset($_FILES) && isset($temp_uploaded)) //???????????????????
			$files_array = array_merge($_FILES, $temp_uploaded );
		else
			$files_array = isset($temp_uploaded) ? $temp_uploaded : $_FILES; //$temp_uploaded is the only used
	  
		 $upload_dir = wp_upload_dir();
		if (!file_exists($upload_dir['basedir']."/wcuf")) 
				mkdir($upload_dir['basedir']."/wcuf", 0775, true);
			
		 $links_to_notify_via_mail = array();
		 $links_to_attach_to_mail = array();
		 foreach($files_array as $fieldname_id => $file_data)
		 {
			list($fieldname, $id) = explode("_", $fieldname_id );
			$upload_field_ids = $this->get_product_ids_and_field_id_by_file_id($fieldname_id);
			$product_id_folder_name = isset($upload_field_ids['product_id']) ? apply_filters('wcuf_order_sub_folder_name', 
																							  $upload_field_ids['product_id']."-".$upload_field_ids['variant_id'], 
																							  $upload_field_ids['product_id'], 
																							  $upload_field_ids['variant_id'], 
																							  $upload_field_ids['is_sold_individually'] ? $upload_field_ids['unique_product_id'] : false,
																							  $order) : "";
			
			if($upload_field_ids['is_sold_individually'])
				  $product_id_folder_name .= "-".$upload_field_ids['unique_product_id'];
			  
			//multiple file managment 
			$is_multiple_file_upload = isset($file_data['is_multiple_file_upload']) ? $file_data['is_multiple_file_upload'] : false; 
			$files_name = is_array($file_data["name"]) ? $file_data["name"] : array($file_data["name"]); //Double check, it would be no necessary
			$files_path = is_array($file_data["tmp_name"]) ? $file_data["tmp_name"] : array($file_data["tmp_name"]); //Double check, it would be no necessary
			 
			$movefiles = array();
			foreach($files_name as $file_name_counter => $file_name)
			{
			   if($file_name != '' && file_exists($files_path[$file_name_counter]))
				{
					$this->current_order = $order;
					$file_name = $this->generate_unique_file_name('none', $file_name);
					$folder_path_new = $product_id_folder_name != "" ? '/wcuf/'.$order_id.'/'. $product_id_folder_name : '/wcuf/'.$order_id;
					$file_path_new = $folder_path_new.'/'.$file_name;
					
					if (!file_exists($upload_dir['basedir']."/wcuf/".$order_id)) 
						mkdir($upload_dir['basedir']."/wcuf/".$order_id, 0775, true);
					
					if ($product_id_folder_name != "" && !file_exists($upload_dir['basedir'].$folder_path_new)) 
						mkdir($upload_dir['basedir'].$folder_path_new, 0775, true);
					
					if( !file_exists ($upload_dir['basedir'].$folder_path_new))
						//touch ($upload_dir['basedir'].$folder_path_new.'/index.html');
						$this->create_empty_file  ($upload_dir['basedir'].$folder_path_new.'/index.html');
					
					@rename($files_path[$file_name_counter], $upload_dir['basedir'] . $file_path_new );
					$movefiles[$file_name_counter] = array('file'=> $upload_dir['basedir'] . $file_path_new ,
										'url' => $upload_dir['baseurl'] . $file_path_new ,
										'name' => $file_name,
										'product_id_folder_name'=> $product_id_folder_name );
										
					foreach((array)$file_data['ID3_info'] as $id3_key => $id3_info)
						if(isset($id3_info['index']) && $id3_info['index'] == $file_name_counter)
							$file_data['ID3_info'][$id3_key]['file_name_unique'] = $file_name;
					
					
					if( !file_exists ($upload_dir['basedir'].'/wcuf/index.html'))
						//touch ($upload_dir['basedir'].'/wcuf/index.html');
						$this->create_empty_file  ($upload_dir['basedir'].'/wcuf/index.html');
					
					
				} 
			}
			$cloud_settings = $wcuf_option_model->get_cloud_settings();	
			
			foreach($movefiles as $key => $movefile)
				if ( $movefile && !isset( $movefile['error'] ) ) 
				{
					//FTP
					if($cloud_settings['cloud_storage_service'] == 'ftp')
					{
						$folder_ids_path = $movefile['product_id_folder_name'] == "" ? '/'.$order_id."/" : '/'.$order_id."/".$movefile['product_id_folder_name']."/";
						$ftp_upload_result = $wcuf_ftp_model->upload_file($movefile['file'], $folder_ids_path. $movefile['name']);
						if($ftp_upload_result === false)
						{
							$movefiles[$key]['cloud_storage_service'] = 'local';
							$notification_email = new WCUF_Email();
							$notification_email->send_error_email_to_admin(sprintf(__("During the file(s) upload process on FTP, your server was unable to connect to remote server for file: %s<br><br><strong>DON'T WORRY!</strong> files have been stored in the local <i>wp-content/wcuf</i> folder :)<br>You can normally manage the uploaded file(s) via the admin <a href='%s'>order edit page</a>.", 'woocommerce-files-upload'), $movefile['file'], get_edit_post_link($order_id) )); 
						}
						else
						{
							$movefiles[$key]['file'] = $ftp_upload_result['path'];
							$movefiles[$key]['url'] = $ftp_upload_result['path'];
							$movefiles[$key]['cloud_storage_service'] = 'ftp';
						}
						
					}
					else if(false)
					{
						try
						{
							wcuf_write_log("Google test: start"); 
							
							if(!isset($gDrive))
								$gDrive = new Gdrive();
							
							wcuf_write_log("Google test: created"); 
							@set_time_limit(3000);
							$result = $gDrive->upload_file($movefile['file']);
							$gDrive = empty($result) ? true : false;
							wcuf_write_log($result); 
						}
						catch(Error $e)
						{
							wcuf_write_log("Google drive: Error"); 
							wcuf_write_log($e->getMessage()); 
						}
						finally
						{
							wcuf_write_log("Google drive: Finally"); 
							$notification_email = new WCUF_Email();
							$notification_email->send_error_email_to_admin(sprintf(__("During the connection to the gDrive service.<br><br>Please check the <strong>Api key</strong>.<br><br>Error for file:%s<br><br><strong>DON'T WORRY!</strong> files have been stored in the local <i>wp-content/wcuf</i> folder :)<br>You can normally manage the uploaded file(s) via the admin <a href='%s'>order edit page</a>.", 'woocommerce-files-upload'), $movefile['file'], get_edit_post_link($order_id) )); 
							$gDrive_upload_error = true;
						}
						//Todo
						/* if(!$gDrive_upload_error)
						{
							$this->delete_local_file($movefile['file']);
							$movefiles[$key]['file'] = WCUF_Gdrive::$gdrive_filepath_prefix.$result['Key']; //used for image preview and delete the file
							$movefiles[$key]['url'] = $result['ObjectURL'];
							$movefiles[$key]['cloud_storage_service'] = 'gdrive';
						}
						else 
							$movefiles[$key]['cloud_storage_service'] = 'local'; */
					}
					//Amazon S3
					else if($cloud_settings['cloud_storage_service'] == 's3')
					{
						try
						{
							if(!isset($s3))
								$s3 = new WCUF_S3();
							
							@set_time_limit(3000);
							$folder_ids_path = $movefile['product_id_folder_name'] == "" ? '/'.$order_id."/" : '/'.$order_id."/".$movefile['product_id_folder_name']."/";
							$result = $s3->upload_file($movefile['file'], ['key' => $folder_ids_path]);
							$s3_upload_error = empty($result) ? true : false;
						}
						catch(Error $e)
						{
							wcuf_write_log("Amazon S3: Exception"); 
							wcuf_write_log($e->getMessage()); 
							$s3_upload_error = true;
							
							
							$notification_email = new WCUF_Email();
							$notification_email->send_error_email_to_admin(sprintf(__("During the connection to the S3 service.<br><br>Please check the <strong>Access key id</strong>, the <strong>Secret access key id</strong> and the <strong>Bucket region</strong><br><br>Error for file:%s<br><br><strong>DON'T WORRY!</strong> files have been stored in the local <i>wp-content/wcuf</i> folder :)<br>You can normally manage the uploaded file(s) via the admin <a href='%s'>order edit page</a>.", 'woocommerce-files-upload'), $movefile['file'], get_edit_post_link($order_id) )); 
							$s3_upload_error = true;
						}
						finally 
						{
							//wcuf_write_log("Amazon S3: Finally"); 
						}
						
						if(!$s3_upload_error)
						{
							$this->delete_local_file($movefile['file']);
							$movefiles[$key]['file'] = WCUF_S3::$s3_filepath_prefix.$result['Key']; //used for image preview and delete the file
							$movefiles[$key]['url'] = $result['ObjectURL'];
							$movefiles[$key]['cloud_storage_service'] = 's3';
						}
						else 
							$movefiles[$key]['cloud_storage_service'] = 'local';
						
					}
					//DropBox
					else if($cloud_settings['cloud_storage_service'] == 'dropbox') //locally || dropbox
					{
						$dropbox_upload_error = false;
						try
						{
							if(!isset($dropbox))
								$dropbox = new WCUF_DropBox();
							//old file managment
							//$dropbox_file_name = is_array($movefile['name']) ? $this->file_zip_name : $movefile['name'];
							//end old
							@set_time_limit(3000);
							$dropbox_file_name = $movefile['name'];
							$folder_ids_path = $movefile['product_id_folder_name'] == "" ? '/'.$order_id."/" : '/'.$order_id."/".$movefile['product_id_folder_name']."/";
							$file_metadata = $dropbox->upload_file($movefile['file'], $folder_ids_path.$dropbox_file_name);
							$dropbox_upload_error = empty($file_metadata) ? true : false;
						}
						catch(Exception $e)
						{
							$notification_email = new WCUF_Email();
							$notification_email->send_error_email_to_admin(sprintf(__("During the file(s) upload process on Dropbox, the plugin got this error:<br><br>%s<br><br>For file:%s<br><br><strong>DON'T WORRY!</strong> files have been stored in the local <i>wp-content/wcuf</i> folder :)<br>You can normally manage the uploaded file(s) via the admin <a href='%s'>order edit page</a>.", 'woocommerce-files-upload'), $e->getMessage(), $movefile['file'], get_edit_post_link($order_id) )); 
							$dropbox_upload_error = true;
						}
						if(!$dropbox_upload_error)
						{
							$this->delete_local_file($movefile['file']);
							$movefiles[$key]['file'] = WCUF_DropBox::$dropbox_filepath_prefix.$file_metadata['path_lower']; //used for image preview and delete the file
							$movefiles[$key]['url'] = get_site_url()."?dropbox_get_item_link=".urlencode($file_metadata['path_lower']);
							$movefiles[$key]['cloud_storage_service'] = 'dropbox';
						}
						else 
							$movefiles[$key]['cloud_storage_service'] = 'local';
					}
					//End DropBox
				}
						
				if( !file_exists ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html'))
					//touch ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html');
					$this->create_empty_file  ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html');
				
				$posted_user_feedback = isset($_POST['wcuf'][$id]['user_feedback']) ? $_POST['wcuf'][$id]['user_feedback'] : "";
				
				//file ref
				foreach($movefiles as $file_index => $movefile)
				{
					//new method 
					$file_data['absolute_path'][$file_index] = $movefile['file'];
					$file_data['url'][$file_index] = $movefile['url']; 
					$file_data['original_filename'][$file_index] = $movefile['name'];
					$file_data['source'][$file_index] = isset($movefile['cloud_storage_service']) ? $movefile['cloud_storage_service'] : 'local';
				}
				
				$file_order_metadata[$id]['id'] = $id;
				$file_order_metadata[$id]['title'] = !isset($_POST['wcuf'][$id]['title']) ? $file_data['title'] : $_POST['wcuf'][$id]['title'];
				$file_order_metadata[$id] = $wcuf_session_model->merge_item_data_arrays($file_order_metadata[$id], $file_data, true);
				
				$original_option_id = $id;
				$needle = strpos($original_option_id , "-");
				if($needle !== false)
					$original_option_id = substr($original_option_id, 0, $needle);
				foreach($options as $option)
				{
					if(/* !$mail_sent &&  */$option['id'] == $original_option_id && $option['notify_admin'] )
					{
						$recipients = $option['notifications_recipients'] != "" ? $option['notifications_recipients'] : get_option( 'admin_email' );
						if(!isset($links_to_notify_via_mail[$recipients]))
							$links_to_notify_via_mail[$recipients] = array('file_info' => array(), 'order_meta'=>$file_order_metadata[$id]);
						
						$file_urls = $wcuf_upload_field_model->get_secure_urls($order_id, $id, $file_order_metadata);
						array_push($links_to_notify_via_mail[$recipients]['file_info'], array('title' => $file_order_metadata[$id]['title'], 
																				 'file_name' => $file_order_metadata[$id]['original_filename'], 
																				 'url'=> $file_urls, 
																				 'source' => $file_order_metadata[$id]['source'], 
																				 'feedback' => $file_order_metadata[$id]['user_feedback'], 
																				 'quantity' => $file_order_metadata[$id]['quantity']));
					
						if($option['notify_attach_to_admin_email'])
						{
							if(!isset($links_to_attach_to_mail[$recipients]))
								$links_to_attach_to_mail[$recipients] = array();
							
							array_push($links_to_attach_to_mail[$recipients], array('paths' => $file_order_metadata[$id]['absolute_path'], 
																					'sources' => $file_order_metadata[$id]['source']));
						}						
					}
				}
		 }
		 //Notification via mail
		if(count($links_to_notify_via_mail) > 0)
		{
			global $wcuf_wpml_helper;
			$wcuf_wpml_helper->switch_to_admin_default_lang();
			$this->email_sender = new WCUF_Email();
			$this->email_sender->trigger($links_to_notify_via_mail, $order, $links_to_attach_to_mail );	
			$wcuf_wpml_helper->restore_from_admin_default_lang();
		}
		//Save upload fields data
		$wcuf_upload_field_model->save_uploaded_files_meta_data_to_order($order_id, $file_order_metadata);
		do_action( 'wcuf_upload_process_completed' , $order_id);
		return $file_order_metadata;
	}
	//NO LONGER USED
	public function upload_and_decode_files($order,$file_order_metadata, $options)
	{
		global $wcuf_upload_field_model;
		$order_id = $order->id ;	
		 $links_to_notify_via_mail = array();
		 $links_to_attach_to_mail = array();
		 foreach($_POST['wcuf-encoded-file'] as $id => $file_data)
		 {
			$this->current_order = $order;
			
			//decode data
			$upload_dir = wp_upload_dir();
			$upload_complete_dir = $upload_dir['basedir']. '/wcuf/'.$order->id.'/';
			if (!file_exists($upload_complete_dir)) 
					mkdir($upload_complete_dir, 0775, true);
				
			$unique_file_name = $this->generate_unique_file_name(null, $_POST['wcuf'][$id]['file_name']);
			$ifp = fopen($upload_complete_dir.$unique_file_name, "w"); 
			fwrite($ifp, base64_decode($file_data)); 
			fclose($ifp); 
		
			if( !file_exists ($upload_dir['basedir'].'/wcuf/index.html'))
				//touch ($upload_dir['basedir'].'/wcuf/index.html');
				$this->create_empty_file  ($upload_dir['basedir'].'/wcuf/index.html');
				
			
			if( !file_exists ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html'))
				//touch ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html');
				$this->create_empty_file  ($upload_dir['basedir'].'/wcuf/'.$order_id.'/index.html');
			
			$file_order_metadata[$id]['absolute_path'] = $upload_complete_dir.$unique_file_name;
			$file_order_metadata[$id]['url'] = $upload_dir['baseurl'].'/wcuf/'.$order->id.'/'.$unique_file_name;
			$file_order_metadata[$id]['title'] = $_POST['wcuf'][$id]['title'];
			$file_order_metadata[$id]['id'] = $id;
			$original_option_id = $id;
			$needle = strpos($original_option_id , "-");
			if($needle !== false)
				$original_option_id = substr($original_option_id, 0, $needle);
			foreach($options as $option)
			{
				if(/* !$mail_sent &&  */$option['id'] == $original_option_id && $option['notify_admin'] )
				{
					$recipients = $option['notifications_recipients'] != "" ? $option['notifications_recipients'] : get_option( 'admin_email' );
					if(!isset($links_to_notify_via_mail[$recipients]))
						$links_to_notify_via_mail[$recipients] = array('file_info' => array(), 'order_meta'=>$file_order_metadata[$id]);
					
					array_push($links_to_notify_via_mail[$recipients]['file_info'], array('title' => $file_order_metadata[$id]['title'], 
																			  'url'=> $file_order_metadata[$id]['url'], 
																			  'quantity' => $file_order_metadata[$id]['quantity'],
																			  'id' => $id,
																			  'all_meta' =>$file_order_metadata[$id]));
				
					if($option['notify_attach_to_admin_email'])
					{
						if(!isset($links_to_attach_to_mail[$recipients]))
							$links_to_attach_to_mail[$recipients] = array();
						array_push($links_to_attach_to_mail[$recipients], $file_order_metadata[$id]['absolute_path'] );
					}
				}
			}
				 
			
		 }
		 //Notification via mail
		if(count($links_to_notify_via_mail) > 0)
		{
			$this->email_sender = new WCUF_Email();
			$this->email_sender->trigger($links_to_notify_via_mail, $order, $links_to_attach_to_mail );	
		}
		//update_post_meta( $order_id, '_wcst_uploaded_files_meta', $file_order_metadata);
		$wcuf_upload_field_model->save_uploaded_files_meta_data_to_order($order_id, $file_order_metadata);
		return $file_order_metadata;
	}
	public function create_tmp_file_data_from_order($order_meta)
	{
		$new_meta = array();
		foreach($order_meta as $key => $upload_field_meta)
		{
			$tmp_files = array();
			foreach($upload_field_meta['original_filename'] as $index => $original_filename)
			{
				$target_path = $this->get_temp_dir_path();
				$tmp_file_path = $target_path.$original_filename;
				copy($upload_field_meta['url'][$index], $tmp_file_path);
				$tmp_files[$index] = $tmp_file_path;
			}
			
			$results = $this->move_temp_file($tmp_files);
			
			$order_meta[$key]['tmp_name'] = array();
			$order_meta[$key]['file_temp_name'] = array();
			$order_meta[$key]['name'] = array();
			foreach($results as $index => $result)
			{
				$order_meta[$key]['tmp_name'][$index] = $result['absolute_path'];
				$order_meta[$key]['file_temp_name'][$index] = $result['file_temp_name'];
				$order_meta[$key]['name'][$index] = basename($result['absolute_path']);
			}
			
			//unuseful
			unset($order_meta[$key]['url']);
			unset($order_meta[$key]['original_filename']);
			unset($order_meta[$key]['absolute_path']);
			unset($order_meta[$key]['source']);
			unset($order_meta[$key]['id']);
			
			$new_meta[$order_meta[$key]['upload_field_id']] = $order_meta[$key];
		}
		
		return $new_meta;
	}
	public function move_temp_file($file_tmp_name)
	{
		$absolute_path = array();
		$file_tmp_name = !is_array($file_tmp_name) ? array($file_tmp_name) : $file_tmp_name;
		
		foreach($file_tmp_name as $index => $tmp_file)
		{
			$tmp_file = str_replace($this->to_remove_from_file_name, "", $tmp_file);
			$file_info = pathinfo($tmp_file);
			$ext = isset($file_info['extension']) && $file_info['extension'] != "" ? ".".$file_info['extension'] : "";
			$absolute_path_tmp = $this->create_temp_file_name($ext); 	
			$absolute_path[$index] = $absolute_path_tmp;
			if(move_uploaded_file($tmp_file, $absolute_path_tmp['absolute_path']) == false) //old method: file was moved from tmp server dir to wcuf tmp dir
			{
				rename($tmp_file, $absolute_path_tmp['absolute_path']); //new method valid for chunked file upload
			}
			
		}
		//return count($absolute_path) > 1 ? $absolute_path : $absolute_path[0];
		return $absolute_path;
	}
	public function create_temp_file_name($ext = "")
	{
		$upload_dir = wp_upload_dir();
		$temp_dir = $upload_dir['basedir']. '/wcuf/tmp/';
		if (!file_exists($temp_dir)) 
				mkdir($temp_dir, 0775, true);
		if( !file_exists ($temp_dir.'index.html'))
			//touch ($temp_dir.'index.html');
			$this->create_empty_file  ($temp_dir.'index.html');
		
		$file_temp_name = rand(0,9999999).$ext;
		$absolute_path = $temp_dir.$file_temp_name;	
		return array('absolute_path'=>$absolute_path, 'file_temp_name'=>$file_temp_name);
	}
	public function delete_temp_file($path)
	{
		$path = is_array($path) ? $path : array($path);
		$dropbox = $s3 = null;
		
		foreach($path as $temp_path)
		{
			$remote_type = wcuf_get_remote_type($temp_path);
			if($remote_type == 'dropbox')
			{
				try{
					if(!isset($dropbox))
						$dropbox = new WCUF_DropBox();
					
					@$dropbox->delete_file($temp_path, true);
				}catch(Exception $e){};	
				
			}
			else if($remote_type == 's3')
			{
				try{
					if(!isset($s3))
						$s3 = new WCUF_S3();
					
					$s3->delete_file($temp_path, true);
				}catch(Error $e){wcuf_write_log($e->getMessage());};	
			}
			else
			{
				try{
					@unlink($temp_path);
				}catch(Exception $e){};		
			}
		}
	}
	public function delete_file($id, $file_order_metadata, $order_id)
	{
		global $wcuf_upload_field_model;
		$dropbox = $s3 = null;
		try
		{
			//multiple file managing
			$absolute_paths = is_array($file_order_metadata[$id]['absolute_path']) ? $file_order_metadata[$id]['absolute_path'] : array($file_order_metadata[$id]['absolute_path']);
			
			//DropBox and other remote services managment
			foreach($absolute_paths as $absolute_path)
			{
				$remote_type = wcuf_get_remote_type($absolute_path);
				if($remote_type == 'dropbox')
				{
					if(!isset($dropbox))
						$dropbox = new WCUF_DropBox();
					try 
					{
						@$dropbox->delete_file($absolute_path, true);
					}catch(Exception $e){};
				}
				else if($remote_type == 's3')
				{
					try{
						if(!isset($s3))
							$s3 = new WCUF_S3();
						
						$s3->delete_file($absolute_path, true);
					}catch(Error $e){wcuf_write_log($e->getMessage()); };	
				}
				else //local
					@unlink($absolute_path);
			}
		}catch(Exception $e){};
		unset($file_order_metadata[$id]);
		//update_post_meta( $order_id, '_wcst_uploaded_files_meta', $file_order_metadata);
		$wcuf_upload_field_model->save_uploaded_files_meta_data_to_order($order_id, $file_order_metadata);
		return $file_order_metadata; 
	}	
	//This method is used ONLY to delete local files when moving them to DropBox
	public function delete_local_file($absolute_path)
	{
		try{
			@unlink($absolute_path);
		}catch(Exception $e){};
	}
	public function delete_expired_sessions_files($max_time)
	{
		$upload_dir = wp_upload_dir();
		$temp_dir = $upload_dir['basedir']. '/wcuf/tmp/';

		//glob($temp_dir."*.jpg")
		//glob($temp_dir."*.txt")
		//glob($temp_dir."*.{jpg,JPG,jpeg,JPEG,png,PNG}")
		$files = glob($temp_dir."*");
		//$total_files = 0;
		if(is_array($files) && count($files) > 0)
		{
			foreach ($files as $file) //glob($temp_dir."*") 
			{
				if (basename($file) != "index.html" && @filemtime($file) < time() - ($max_time/* *60 */)) //No need to multiply for 60, it is already done in the Options model. 86400:24h, 14400:4h, 10800:3h, 1800: 30 min 
				{
					
					try{
						//$total_files--;
						@unlink($file);
					}catch(Exception $e){};
				}
			}
		}
	}
	public function start_delete_empty_order_directories()
	{
		$upload_dir = wp_upload_dir();
		$temp_dir = $upload_dir['basedir']. '/wcuf';
		$this->delete_empty_order_directory($temp_dir, true);
	}
	private function delete_empty_order_directory($temp_dir, $is_root = false)
	{
		$files = glob($temp_dir."/*");
		if(is_array($files))
		{
			$total_files = 0;
			foreach ($files as $file) //glob($temp_dir."*") 
			{
				if(is_dir($file) && basename($file) == 'tmp')
					continue;
				
				if(is_dir( $file))
				{
					$total_files += $this->delete_empty_order_directory($file);
				}
				else
				{
					$total_files = basename($file) != "index.html" ? $total_files+1 : $total_files;
					
				} 
			}
			
			if($total_files == 0 && !$is_root)
			{
				$this->deleteDirectory($temp_dir);
				//wcuf_var_dump("To delete: ".$temp_dir);
				return 0;
			}
		}
		return $total_files;
		
	}
	private function deleteDirectory($dir) 
	{
		if (!file_exists($dir)) 
		{
			return true;
		}

		if (!is_dir($dir)) 
		{
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) 
		{
			if ($item == '.' || $item == '..') 
			{
				continue;
			}

			if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) 
			{
				return false;
			}

		}

		return rmdir($dir);
	}
	
	public function delete_all_order_uploads($order_id)
	{
		global $wcuf_upload_field_model;
		$order = wc_get_order($order_id);
		$dropbox = $s3 = null;
		if (is_object($order))
		{
			$file_order_metadata = $wcuf_upload_field_model->get_uploaded_files_meta_data_by_order_id($order_id);//get_post_meta($order_id, '_wcst_uploaded_files_meta');
			foreach($file_order_metadata as $file_to_delete)
			{
				try
				{
					//multiple file managing
					$absolute_paths = is_array($file_to_delete['absolute_path']) ? $file_to_delete['absolute_path'] : array($file_to_delete['absolute_path']);
					//DropBox managment
					foreach($absolute_paths as $absolute_path)
					{
						$remote_type = wcuf_get_remote_type($absolute_path);				
						if($remote_type == 'dropbox')
						{
							if(!isset($dropbox))
								$dropbox = new WCUF_DropBox();
							try{
								$dropbox->delete_file($absolute_path, true);
							}catch(Exception $e){};
						}
						else if($remote_type == 's3')
						{
							try{
								if(!isset($s3))
									$s3 = new WCUF_S3();
								
								$s3->delete_file($absolute_path, true);
							}catch(Error $e){wcuf_write_log($e->getMessage());};	
						}
						else //local
						{
							//wcuf_var_dump($absolute_path);
							@unlink($absolute_path);
						}
					}
				}catch(Exception $e){};
			}
			$wcuf_upload_field_model->delete_uploaded_files_meta_data_by_order_id($order_id);
		}
	}
}	
?>