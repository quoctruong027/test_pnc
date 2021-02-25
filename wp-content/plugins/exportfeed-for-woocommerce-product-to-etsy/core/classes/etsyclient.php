<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once 'etsy-export-feed.php';
require_once "OAuth.php";
if (!class_exists('ETCPF_Etsy')) {
    class ETCPF_Etsy
    {
        public $hmac_method; // Signature Method
        public $consumer; // user with API key and SECRET key
        public $token; // user with authorized token key and secret token key
        public $mate;
        public $config;
        public $message;
        public $api_key = NULL;
        public $secret_key = NULL;
        public $connected = false;
        public $product_ids = 0;
        
        function __construct($id = null)
        {
            $data = $this->_old_client($id);
            $this->_configurations();
            $this->mate = $data;
            $this->api_key = get_option('etcpf_api_key');
            $this->secret_key = get_option('etcpf_secret_key');
            $this->connected = get_option('etcpf_connected_to_shop');
        }
        
        function get_credentials()
        {
            $api = get_option('etcpf_api_key');
            $secret = get_option('etcpf_secret_key');
            
            $this->view('credentials_view', array(
                'api' => $api,
                'secret' => $secret,
            ));
        }
        
        function saveCredential()
        {
            $result = array('status' => 0);
            $data = array(
                'shared_secret' => sanitize_text_field($_POST['shared_secret']),
                'keystring' => sanitize_text_field($_POST['keystring']),
            );
            $check_keys = wp_remote_get('https://openapi.etsy.com/v2/listings/active?api_key=' . $data['keystring']);
            if (wp_remote_retrieve_response_code($check_keys) === 200 || wp_remote_retrieve_response_code($check_keys) === 201) {
                $body = json_decode(wp_remote_retrieve_body($check_keys));
                if ($body) {
                    $result['status'] = 1;
                    update_option('etcpf_api_key', $data['keystring']);
                    update_option('etcpf_secret_key', $data['shared_secret']);
                }
                
            } else {
                $result['status'] = 0;
            }
            echo json_encode($result);
            wp_die();
        }
        
        function _configurations()
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_etsy_configuration";
            $sql = "SELECT id,configuration_title,configuration_value,configuration_description,options FROM $table";
            $results = $wpdb->get_results($sql, OBJECT);
            
            foreach ($results as $key => $config) {
                if ($config->configuration_title == 'state') {
                    $this->state = $config->configuration_value;
                    continue;
                }
                
            }
            $this->config = $results;
        }
        
        function getConfig($title)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_etsy_configuration";
            
            $sql = $wpdb->prepare("SELECT configuration_value FROM $table WHERE configuration_title = %s", $title);
            
            $configuration_value = $wpdb->get_row($sql, OBJECT);
            
            return $configuration_value;
        }
        
        function shipping_info($shipping_id = false)
        {
            global $wpdb;
            
            if (!$shipping_id) {
                $id = $this->mate->fields->shipping_template_id;
            } else {
                $id = $shipping_id;
            }
            
            $table = $wpdb->prefix . "etcpf_shipping_template";
            $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE shipping_template_id = %d", array($id));
            return $wpdb->get_row($sql);
        }
        
        public function display()
        {
            $shop = $this->get_shop();
            $this->view('shop-main-page', array('shop' => $shop));
        }
        
        function listing_progress_view($no_of_progress = 0)
        {
            if (!empty($_REQUEST)) {
                if (isset($_REQUEST['uploadfailed'])) {
                    $requestType = 3;
                } elseif (isset($_REQUEST['resubmit'])) {
                    $requestType = 7;
                } else {
                    $requestType = 0;
                }
            } else {
                $requestType = 0;
            }
            $msg = '';
            if ($no_of_progress > 0) {
                $msg = $no_of_progress . ' products are ready to be uploaded';
            }
            $data = $this->getPreparedListing($requestType);
            $feedDetail = $this->getFeedDetail();
            $this->view('listing_progress_view', array('msg' => $msg, 'data' => $data, 'feedDetail' => $feedDetail));
        }
        
        public function get_createfeeds_page()
        {
            require_once plugin_dir_path(__FILE__) . '../../etsy-export-feed-wpincludes.php';
            $source_feed_id = -1;
            $feed_type = -1;
            $reg = new ETCPF_EtsyValidation();
            
            if (NULL == $this->api_key) {
                $this->get_credentials();
            }
            if ($this->mate->count == 0) {
                echo '<script>etcpf_call_out_for_account();</script>';
            }
            
            global $wpdb;
            $chosen_merchant = 'Etsy';
            if (isset($_GET['raw'])) {
                $chosen_merchant = 'Productlistraw';
            }
            
            //Main content
            echo '<script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                feedajaxhost = "' . plugins_url('/', __FILE__) . '";
                chosen_merchant= "' . $chosen_merchant . '";

                doGoogleFeed();
                doFetchLocalCategories_google();
                feed_type = ' . $feed_type . ';
                window.feed_type = feed_type;
                feed_id = ' . $source_feed_id . ';

                if(feed_id > 0 && feed_type == 1){
                    googleSaveTocustomTable(feed_id);
                    googleShowSelectedProductTables(feed_id);
                }
            } );
            </script>';
            
            if (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'etsymanagefeed') {
                //WordPress Header ( May contain a message )
                $reg = new ETCPF_EtsyValidation;
                if (strlen($this->message)) {
                    $this->message .= '<br>';
                }
                //insert break after local message (if present)
                $this->message .= $reg->getMessage();
                
                if (!$reg->isValid()) {
                    echo '<div id="setting-error-settings_updated" class="updated settings-error">
                  <p>' . $this->message . '</p>
                  </div>';
                }
            }
            //$wpdb->query("TRUNCATE {$wpdb->prefix}etcpf_custom_products");
            //Page Header
            echo ETCPF_PageDialogs::pageHeader();
            //Page Body
            echo ETCPF_PageDialogs::pageBody();
        }
        
        public function get_managefeed_page()
        {
            require_once plugin_dir_path(__FILE__) . '../../etsy-export-feed-wpincludes.php';
            require_once plugin_dir_path(__FILE__) . '../../etsy-export-feed-manage.php';
        }
        
        function fetchLoginToken()
        {
            global $wpdb;
            $url = "https://openapi.etsy.com/v2/oauth/request_token";
            //$callback = wp_nonce_url(get_admin_url() . 'admin.php?page=etsy-export-feed-configure', 'get_etsy_token_request', 'etcpf_token_authorized');
            $callback = get_admin_url() . 'admin.php?page=etsy-export-feed-configure&stage=2';
            $params = array(
                'scopes' => 'email_r%20listings_r', // not necessary for now
                'oauth_callback' => $callback, // if none set the value to 'oob'
            );
            $this->_initiate();
            //preparing request data
            $req_req = OAuthRequest::from_consumer_and_token($this->consumer, NULL, "GET", $url, $params);
            $req_req->sign_request($this->hmac_method, $this->consumer, NULL);
            $resp = "";
            // checking if all required parameters are validated or not
            if (!empty($req_req->base_string)) {
                $resp = $this->_curlRequest($req_req);
                if ($resp === false) {
                    $this->deleteAccount();
                    ob_clean();
                    $resp = false;
                }
            }
            return urldecode($resp);
        }
        
        function storeOauthVerifier($token, $verfier)
        {
            if ($token) {
                update_option('etcpf_oauth_token', $token);
            }
            
            if ($verfier) {
                update_option('etcpf_oauth_verfier', $verfier);
            }
        }
        
        function checkInformation()
        {
        
        }
        
        /*  Step 2 : Requesting a login access with etsy through oauth
                    *   Requesting a Token for access with ETSY
                    *   Redirects users to etsy allow access page
        */
        public function requestToken()
        {
            global $wpdb;
            
            // url for requesting a token
            $url = "https://openapi.etsy.com/v2/oauth/request_token";
            
            // setting the paramenter
            // $callback = 'https://localhost/wordpress/wp-admin/admin.php?page=etsy-configure';
            //  $callback = admin_url('admin.php?page=etsy-export-feed-configure');
            // $callback = plugin_dir_url(__FILE__) . '../ajax/wp/etsy-fetch-token.php';
            $callback = wp_nonce_url(get_admin_url() . 'admin.php?page=etsy-export-feed-configure', 'get_etsy_token_request', 'etcpf_token_authorized');
            
            $params = array(
                'scopes' => 'email_r%20listings_r', // not necessary for now
                'oauth_callback' => $callback, // if none set the value to 'oob'
            );
            $this->_initiate();
            //preparing request data
            $req_req = OAuthRequest::from_consumer_and_token($this->consumer, NULL, "GET", $url, $params);
            
            $req_req->sign_request($this->hmac_method, $this->consumer, NULL);
            
            // checking if all required parameters are validated or not
            if (!empty($req_req->base_string)) {
                
                // making a request with Etsy Server
                $resp = $this->_curlRequest($req_req);
                
                $lurl = explode("login_url=", $resp);
                $login_url = $lurl[1];
                
                // saving secret token key
                $oauth_data = explode("&", urldecode($login_url));
                
                $table_name = $wpdb->prefix . "etcpf_etsy_token";
                
                $sql = $wpdb->prepare("SELECT user_id FROM {$table_name} WHERE user_id = %d", array(get_current_user_id()));
                
                $check = $wpdb->get_row($sql);
                
                $flag = false;
                if (count($check) > 0) {
                    $token = explode("=", $oauth_data[4]);
                    $insert_array = array('token' => $token[1]);
                    $wpdb->update($table_name, $insert_array, array('user_id' => $check->user_id));
                    $flag = true;
                    
                } else {
                    $token = explode("=", $oauth_data[4]);
                    $insert_array = array(
                        'user_id' => get_current_user_id(),
                        'token' => $token[1],
                    );
                    $wpdb->insert($table_name, $insert_array);
                    $flag = true;
                }
                if ($flag) {
                    echo "Redirecting to Etsy. Please Wait ...";
                    echo "<script> window.location.href = '" . urldecode($login_url) . "';</script>";
                }
            }
        }
        
        function doTheAuthorizingThingy()
        {
            $url = "https://openapi.etsy.com/v2/oauth/access_token";
            $oauth_token = get_option('etcpf_oauth_token');
            $oauth_verifier = get_option('etcpf_oauth_verfier');
            
            $params = array(
                'oauth_token' => $oauth_token,
                'oauth_verifier' => $oauth_verifier,
            );
            
            $token_secret = get_option('etcpf_oauth_token_secret');
            $user_token = new OAuthConsumer($oauth_token, $token_secret);
            
            $this->_initiate();
            $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $user_token, "GET", $url, $params);
            $acc_req->sign_request($this->hmac_method, $this->consumer, $user_token);
            $token_req = $this->_curlRequest($acc_req);
            return $token_req;
        }
        
        /*  Step 4 : Accessing Token from Oauth
                    *   Steps: Request Etsy for access with oauth token key and verifier
                    *          And Stores all the required information for future references
        */
        public function authorize($data)
        {
            global $wpdb;
            
            // url for accessing token
            $url = "https://openapi.etsy.com/v2/oauth/access_token";
            $params = array();
            
            $params = array(
                'oauth_token' => $data['oauth_token'],
                'oauth_verifier' => $data['oauth_verifier'],
            );
            
            $token = $data['oauth_token'];
            $token_secret = $data['oauth_secret_token'];
            
            $user_token = NULL;
            if ($token) {
                $user_token = new OAuthConsumer($token, $token_secret);
            }
            
            $init = $this->_initiate();
            if ($init) {
                
                // preparing a request data for accessing token
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $user_token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $user_token);
            }
            // sending a request for access token
            $token_req = $this->_curlRequest($acc_req);
            // exploding the token and public token key from the request
            // reminder : data comes in string format
            $t = explode("&", $token_req);
            
            $table_name = $wpdb->prefix . "etcpf_etsy_token";
            $insert_array = array(
                'oauth_token' => explode("=", $t[0])[1],
                'oauth_token_secret' => explode("=", $t[1])[1],
                'oauth_verifier' => $data['oauth_verifier'],
                'is_default' => 1,
            );
            
            // saving oauth_token and oauth_token_secret in database
            
            if (!$wpdb->update($table_name, $insert_array, array('user_id' => get_current_user_id()))) {
                echo $wpdb->last_query;
            }
        }
        
        // initializing the setting for connection
        public function _initiate()
        {
            //$call_back = "https://localhost/wordpress/wp-admin/admin.php?page=etsy-configure";
            $call_back = get_admin_url('?page=etsy-export-feed-configure');
            $api_key = get_option('etcpf_api_key');
            $secret_key = get_option('etcpf_secret_key');
            
            $token_key = "";
            $token_secret = "";
            
            $this->hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
            $this->consumer = new OAuthConsumer($api_key, $secret_key, $call_back);
            
            return true;
        }
        
        // executing every request needed to make with server through cURL php
        protected function _curlRequest($request)
        {
            $response = wp_remote_get($request,
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true
                )
            );
            
            if (wp_remote_retrieve_response_code($response) == 200 || wp_remote_retrieve_response_code($response) == 201) {
                $data = wp_remote_retrieve_body($response);
            } else {
                $data = false;
            }
            return $data;
            
            /** $ch = curl_init();
             *
             * $options = array(
             * CURLOPT_URL => $request,
             * CURLOPT_RETURNTRANSFER => true,
             * // CURLOPT_HEADER      => true,
             * CURLOPT_FOLLOWLOCATION => true,
             * CURLOPT_ENCODING => "",
             * CURLOPT_AUTOREFERER => true,
             * CURLOPT_CONNECTTIMEOUT => 120,
             * CURLOPT_TIMEOUT => 120,
             * CURLOPT_MAXREDIRS => 10,
             * );
             * curl_setopt_array($ch, $options);
             * $response = curl_exec($ch);
             * $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
             *
             * if ($httpCode != 200) {
             * echo "Return code is {$httpCode} \n"
             * . curl_error($ch);
             * }
             *
             * curl_close($ch);*/
            
        }
        
        private function _old_client($_id = null)
        {
            $connect = get_option('etcpf_connected_to_shop');
            $data = new stdClass();
            $data->count = 0;
            $data->fields = new stdClass();
            if ($connect) {
                $data->count = 1;
                $data->fields->user_id = get_current_user_id();
                $data->fields->token = get_option('etcpf_oauth_token_secret');
                $data->fields->oauth_token = get_option('etcpf_oauth_token');
                $data->fields->oauth_verfier = get_option('etcpf_oauth_verfier');
                $data->fields->oauth_token_secret = get_option('etcpf_oauth_token_secret');
                $data->fields->shipping_template_id = get_option('etcpf_shipping_template_id');
            }
            return $data;
        }
        
        function upload_listing($upload_data = array())
        {
            global $wpdb;
            global $message;
            $id = $upload_data['product_id'];
            $listing_id = 0;
            // $category_id = explode(":",$upload_data['category'])[1];
            $detail_id = $this->prepareListing($id, 2, $upload_data['category']);
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            //items
            $sql = $wpdb->prepare("SELECT image,description,quantity,title,listing_id,price,who_made,when_made,state,is_supply,category_id,shipping_template_id FROM {$table} WHERE id = (%d)", array($detail_id));
            $item = $wpdb->get_row($sql, ARRAY_A);
            
            $image = $item['image'];
            unset($item['image']);
            $response = $this->doUploading($item);
            $res = json_decode($response);
            // print_r($res);
            $listing_id = $res->results[0]->listing_id;
            $user_id = $res->results[0]->user_id;
            if ($listing_id > 0) {
                $wpdb->update($table, ['listing_id' => $listing_id, 'uploaded' => 1, 'shop_id' => $user_id], ['id' => $detail_id]);
                $imgRequest = $this->uploadImage($listing_id, $image);
                
            }
            echo '<p>' . $res->results[0]->title . " has been uploaded. You can check you Etsy shop.</p>";
            sleep($this->getConfig('request_per_minute')->configuration_value);
            
        }
        
        function get_shipping_listing($default)
        {
            
            global $wpdb;
            
            if (isset($this->mate->fields->shipping_template_id) && !$this->mate->fields->shipping_template_id) {
                $this->get_shipping_info();
            }
            
            $ships = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}etcpf_shipping_template");
            $html = "<select name='shippingTemplate' id='shippingTemplate' value='{$this->mate->fields->shipping_template_id}'>";
            $html .= "<option value=''>Shipping Template</option>";
            foreach ($ships as $ship) {
                $select = "";
                if ($default == $ship->shipping_template_id) {
                    $select = ' selected="selected" ';
                }
                $html .= '<option value="' . $ship->shipping_template_id . '"' . $select . '>' . $ship->title . '</option>';
            }
            $html .= "</select>";
            return $html;
        }
        
        function doUploading($data)
        {
            $listing_id = $data['listing_id'];
            unset($data['listing_id']);
            $path = "";
            if ($listing_id > 0 && $listing_id != NULL) {
                $path = "/" . $listing_id;
            }
            
            $url = "https://openapi.etsy.com/v2/listings" . $path . '?scopes=email_r%20listings_r%20listings_w';
            
            $init = $this->_initiate();
            if ($init) {
                
                $params = array(
                    'shipping_template_id' => $data['shipping_template_id'],
                    'method' => $listing_id > 0 ? "PUT" : "POST",
                );
                
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "POST", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
            }
            unset($data['shipping_template_id']);
            
            return $this->_curlPost($acc_req, $data);
        }
        
        private function _curlPost($res, $items = array())
        {
            update_option('etcpf_update_status', 'Making Changes in Etsy...');
            //open connection
            /*$ch = curl_init();
            $data = json_encode($items);

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $res);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            // curl_errno($ch)
            $result = curl_exec($ch);*/
            $response = wp_remote_post($res,
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Expect' => '',
                    ),
                    'body' => json_encode($items)
                )
            );
            
            $resposeCode = wp_remote_retrieve_response_code($response);
            $data = wp_remote_retrieve_body($response);
            if ($resposeCode !== 200 && $resposeCode !== 201) {
                echo 'Curl error: ' . $resposeCode . wp_remote_retrieve_response_message($response);
                $resp = 0;
            }
            return $data;
        }
        
        function loadNavigationTab()
        {
            $this->view('navigation');
        }
        
        public function getShopSection($return = true)
        {
            $init = $this->_initiate();
            if ($init) {
                $url = "https://openapi.etsy.com/v2/shops/" . get_option('etcpf_shop_id') . "/sections";
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $response = wp_remote_get($acc_req);
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                if ($response_code == 200 && $return == true) {
                    return json_decode($body);
                }
                return false;
            }
        }
        
        function get_shop()
        {
            $result['error'] = null;
            $result = array();
            if ($this->mate->count == 0) {
                $result['error'] = 'Connect with your Etsy shop first.';
                return $result;
            }
            $url = "https://openapi.etsy.com/v2/users/__SELF__/shops";
            $init = $this->_initiate();
            if ($init) {
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $data = json_decode($this->_curlRequest($acc_req));
                if (isset($data->count)) {
                    $result = $data->results;
                    $shop = $result[0];
                    $shop_id = $shop->shop_id;
                    update_option('etcpf_shop_id', $shop_id);
                    $shopSectionDetails = $this->getShopSection(true);
                    if ($shopSectionDetails !== true) {
                        if (empty(get_option('etcpf_shop_sections_' . $shop_id))
                            || get_option('etcpf_shop_sections_count_' . $shop_id) < $shopSectionDetails->count) {
                            update_option('etcpf_shop_sections_' . $shop_id, $shopSectionDetails);
                            update_option('etcpf_shop_sections_count_' . $shop_id, $shopSectionDetails->count);
                        }
                    }
                } else {
                    $result['error'] = $data;
                }
            }
            return $result;
        }
        
        function listShippingTemplate()
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_shipping_template";
            $sql = "SELECT * FROM {$table}";
            $shippings = $wpdb->get_results($sql);
            $msg = "";
            
            /**========================================================================================================
             * if (!isset($this->mate->fields->shipping_template_id)) {
             *$msg = "<p style='color:#0073AA'>Please Make one of the listed Shipping Template as default.</p>";
             * =========================================================================================================*/
            
            $this->view('shipping_index', array(
                    'msg' => $msg,
                    'shippings' => $shippings,
                    'country' => $this->shippingTemplate(),
                )
            );
        }
        
        function get_shipping_info($id = "")
        {
            global $wpdb;
            $url = "https://openapi.etsy.com/v2/users/__SELF__/shipping/templates";
            
            if ($id > 0) {
                $url = "https://openapi.etsy.com/v2/shipping/templates/{$id}";
            }
            $init = $this->_initiate();
            if ($init) {
                // $url = "https://openapi.etsy.com/v2/taxonomy/categories";
                $params = array('scopes' => 'listings_r');
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $data = json_decode($this->_curlRequest($acc_req));
                if (isset($_POST['level']) && $_POST['level'] == 11) {
                    $table = $wpdb->prefix . "etcpf_shipping_template";
                    $insert = array();
                    if(empty($data->results))
                        return "No data were fetched";
                    $details = $data->results;
                    $wpdb->query("TRUNCATE TABLE {$table}");
                    if (is_array($details))
                        foreach ($details as $key => $shiping) {
                            $insert = array(
                                'shipping_template_id' => $shiping->shipping_template_id,
                                'title' => $shiping->title,
                                'processing_days_display_label' => $shiping->processing_days_display_label,
                                'country' => $this->countryByID($shiping->origin_country_id),
                            );
                            if ($wpdb->insert($table, $insert)) {
                                continue;
                            } else {
                                wp_die('Something went wrong!');
                            }
                        }
                }
            }
            echo 'Shipping Template imported successfully. Refresh the page to see the list.';
        }
        
        public function getEtsyShopLang()
        {
            global $wpdb;
            $url = "https://openapi.etsy.com/v2/users/__SELF__/shops";
            $init = $this->_initiate();
            if ($init) {
                $params = array('scopes' => 'listings_r');
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $data = json_decode($this->_curlRequest($acc_req));
                if ($data) {
                    $shopdata = $data->results;
                    update_etsy_settings('shop_language', $shopdata[0]->languages[0]);
                    $shopIdsArray = array();
                    $etsyShops = array();
                    foreach ($shopdata as $key => $value) {
                        $etsyShops[$value->shop_id] = $value;
                        $shopIdsArray[] = $value->shop_id;
                    }
                    update_option('etcpf_etsy_shop_ids', $shopIdsArray);
                    update_option('etcpf_etsy_shops', $etsyShops);
                    return true;
                } else {
                    $shopdata = new stdClass();
                    $shopdata->count = 0;
                    $shopdata->result = array();
                    return false;
                }
            }
        }
        
        function shippingTemplate($code = false, $flag = false)
        {
            
            $url = "https://openapi.etsy.com/v2/countries";
            if ($code) {
                $url = "https://openapi.etsy.com/v2/countries/iso/" . $code;
            }
            $init = $this->_initiate();
            if ($init && property_exists($this->mate->fields, 'oauth_token')) {
                // $url = "https://openapi.etsy.com/v2/taxonomy/categories";
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $countries = json_decode($this->_curlRequest($acc_req)); // getting country codes as per Etsy mentioned
                if ($flag) {
                    return $countries;
                }
                $cntry = "<select name='origin_country_id' value >";
                if ($countries) {
                    foreach ($countries->results as $country) {
                        $cntry .= "<option value=" . $country->country_id . ">" . $country->name . "</option>";
                    }
                }
                $cntry .= "</select>";
                return $cntry;
            } else {
                return false;
            }
            /*$this->view('shipping_form', ['country' => $cntry*/
        }
        
        /**
         * @param $shipping
         */
        function createShippingTemplate($shipping)
        {
            $url = "https://openapi.etsy.com/v2/shipping/templates?scopes=listings_w";
            $init = $this->_initiate();
            if ($init) {
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "POST", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
            }
            $data = $this->_curlPost($acc_req, $shipping);
            return $data;
        }
        
        function configuration_tab()
        {
            $this->view('settings');
            
        }
        
        function changeConfigurations()
        {
            $result = array();
            $result['errors'] = false;
            $result['status'] = true;
            $result['error_msg'] = '';
            $shop = $this->mate->fields->user_id;
            global $wpdb;
            $data = $_POST;
            $table = $wpdb->prefix . "etcpf_etsy_configuration";
            unset($data['level']);
            unset($data['unq']);
            unset($data['action']);
            unset($data['feedpath']);
            unset($data['security']);
            foreach ($data as $key => $value) {
                $wpdb->update($table, array('configuration_value' => $value), array('configuration_title' => $key));
                if (!$wpdb->update($table, array('configuration_value' => $value), array('configuration_title' => $key)) && $wpdb->last_error) {
                    $result['status'] = false;
                    $result['errors'] = true;
                    $result['error_msg'] .= $wpdb->last_error . '\n';
                }
            }
            echo json_encode($result);
        }
        
        function countryByID($id)
        {
            $url = "https://openapi.etsy.com/v2/countries/" . $id;
            $init = $this->_initiate();
            if ($init) {
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                
                $data = $this->_curlRequest($acc_req);
            }
            
            $country = json_decode($data);
           if(empty($country->results))
               return false;
            return $country->results[0]->name;
        }
        
        function deleteShipping()
        {
            $url = "https://openapi.etsy.com/v2/shipping/templates/" . $_POST['id'];
            $init = $this->_initiate();
            if ($init) {
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "DELETE", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $data = $this->_curlDELETE($acc_req);
                echo "<pre>";
                print_r(json_decode($data));
                echo "</pre>";
            }
        }
        
        function deleteFromEtsy($id)
        {
            $url = "https://openapi.etsy.com/v2/listings/" . $id;
            $init = $this->_initiate();
            if ($init) {
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "DELETE", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                $data = $this->_curlDELETE($acc_req);
                return $data;
            }
            return null;
        }
        
        private function _curlDELETE($res)
        {
            /*======================================================================================================
               $ch = curl_init();
               curl_setopt($ch, CURLOPT_URL, $res);
               curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
               $result = curl_exec($ch);
               $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
               curl_close($ch);
               ======================================================================================================*/
            $response = wp_remote_request($res,
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'headers' => array(
                        'Expect' => '',
                    ),
                    'method' => 'DELETE'
                )
            );
            return $response;
        }
        
        function makeDefaultShipping($id)
        {
            if (update_option('etcpf_shipping_template_id', $id)) {
                update_option('etcpf_stage', 5);
            }
        }
        
        function fetchEtsyCategories()
        {
            $results = $this->curlForCategories();
            $results = json_decode($results);
            if ($results->status == 401) {
                $this->fetchEtsyCategories();
            } else {
                if ($results->status == 200) {
                    $this->view('etsy_categories', [
                        'categories' => $results->data,
                    ]);
                    die;
                } else {
                    echo "<pre>";
                    echo "Response Code: " . $results->status;
                    echo '<br>';
                    print_r($results->message);
                    echo "</pre>";
                    exit;
                }
            }
            
        }
        
        public function getEtsyCategoriesForCustom($localcategory = null)
        {
            $results = $this->curlForCategories();
            $results = json_decode($results);
            if ($results->status == 401) {
                $this->fetchEtsyCategories();
            } else {
                if ($results->status == 200) {
                    return $this->view('custom-views/custom_etsy_categories', [
                        'categories' => $results->data,
                        'localcat' => $localcategory
                    ]);
                    die;
                } else {
                    echo "<pre>";
                    echo "Response Code: " . $results->status;
                    echo '<br>';
                    print_r($results->message);
                    echo "</pre>";
                    exit;
                }
            }
        }
        
        public function curlForCategories()
        {
            $postfields = array(
                'level' => 0,
                'parent_id' => 0,
            );
            $header = array();
            $url = 'http://apis.exportfeed.com/etsycategory';
            $result = $this->_CURL($url, $postfields, $header);
            return $result;
        }
        
        public function curlForAccessToken($user, $pass)
        {
            $postfields = array(
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => 'rnGzFotxOZyoLli8076mJAY0jcYCOIWeXag3Nt1b',
                'username' => $user,
                'password' => $pass,
            );
            $url = 'http://apis.exportfeed.com/accessToken';
            $result = $this->_CURL($url, $postfields, false);
            return $result;
        }
        
        public function _CURL($url, $postfields, $header = false)
        {
            $data = wp_remote_post($url,
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $postfields
                )
            );
            if (!empty($data['errors'])) {
                echo "<p>" . $data['errors'] . "</p>";
            } elseif (isset($data['response']['code']) && $data['response']['code'] == 200) {
                return $data['body'];
            } else {
                echo "<pre>";
                print_r($data['response']['message']);
                echo "</pre>";
                exit();
            }
            return null;
        }
        
        function manuals()
        {
            $this->view('manuals');
        }
        
        function view($insView, $inaData = array(), $echo = true)
        {
            /*$sFile = dirname(__FILE__).DS.self::ViewDir.DS.$insView.self::ViewExt;*/
            $sFile = dirname(__FILE__) . '/../etsy-views/' . $insView . '.php';
            if (!is_file($sFile)) {
                echo("View not found: " . $sFile);
                wp_die();
                return false;
            }
            if (count($inaData) > 0) {
                extract($inaData, EXTR_PREFIX_ALL, 'cpf');
            }
            ob_start();
            include $sFile;
            $sContents = ob_get_contents();
            ob_end_clean();
            if ($echo) {
                echo $sContents;
                return true;
            } else {
                return $sContents;
            }
        }
        
        function deleteAccount()
        {
            /*global $wpdb;
            $table = $wpdb->prefix . "etcpf_etsy_token";*/
            delete_option('etcpf_oauth_token');
            update_option('etcpf_connected_to_shop', 0);
            delete_option('etcpf_oauth_token_secret');
            delete_option('etcpf_oauth_token_secret');
            delete_option('etcpf_oauth_verfier');
            delete_option('etcpf_login_url');
            delete_option('etcpf_shipping_template_id');
            update_option('etcpf_stage', '1');
            update_option('etcpf_api_key', '');
            update_option('etcpf_secret', '');
            $result = array('result' => true, 'msg' => 'Account Has been Deleted!');
            /*--------------------------------------------------------------------------------------------------------*/
            // This code doesn't make any sense for now, so is commented. Can add later on with the appropriate use case
            /** $id = 1;
             * if ($id > 0) {
             * $wpdb->delete($table, array('id' => $id));
             *
             * } else {
             * $result = array('result' => false, 'msg' => 'Account deletion process failed.Please try again.');
             * }*/
            /*--------------------------------------------------------------------------------------------------------*/
            echo json_encode($result);
        }
        
        function parseSettingOptions($config)
        {
            $options = $config->options;
            if ($options == '0') {
                $html = '<input type="text" id="' . $config->configuration_title . '" value="' . $config->configuration_value . '" name ="' . $config->configuration_title . '" />';
            } else {
                $opts = explode(',', $options);
                $html = '<select id="' . $config->configuration_title . '" value="' . $config->configuration_value . '">';
                foreach ($opts as $key => $value) {
                    $default = '';
                    $value_name = $opts;
                    if ($config->configuration_value == $value) {
                        $default = 'selected="selected"';
                    }
                    if ($config->configuration_title == 'who_made') {
                        $value_name = array('I did', 'My shop member', 'Another company or person');
                    }
                    $html .= '<option value="' . $value . '" ' . $default . '>' . $value_name[$key] . '</option>';
                }
                $html .= '<select>';
            }
            echo $html;
        }
        
        function get_uploaded_details($product_id, $feed_id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $sql = $wpdb->prepare("SELECT id as details_id,shop_id,shipping_template_id,shop_id,listing_id,uploaded,who_made,when_made,state,is_supply,image FROM {$table} WHERE item_id = %d", array($product_id));
            $item = $wpdb->get_row($sql);
            
            if (sizeof($item) > 0) {
                return $item;
            } else {
                $item = new stdClass();
                $item->error = 'Not Uploaded Yet!';
                return $item;
            }
        }
        
        function deleteListing($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_listings";
            $wpdb->delete($table, array('id' => $id));
            if (empty($wpdb->last_error)) {
                return array('status' => 'SUCCESS', 'message' => "Listing Deleted Successfully");
            }
            return array('status' => 'FAILED', 'message' => 'Empty Listing Id');
        }
        
        function updateUploadData($data, $id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $wpdb->update($table, $data, array('id' => $id));
        }
        
        function get_list_to_upload()
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            /*$lists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE uploaded = %d",[0]));*/
            $lists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE uploaded = %d", array(0)), ARRAY_A);
            return $lists;
        }
        
        function getPreparedListing($type)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_listings";
            if ($type == 0) {
                $lists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE feed_id = %d ", array($_REQUEST['id'])));
            } else {
                $lists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE feed_id = %d AND uploaded = %d ORDER BY id ASC", array($_REQUEST['id'], $type)));
            }
            /*echo "<pre>";
            print_r($wpdb);
            echo "</pre>";
            exit();*/
            update_option('etsy_current_uploading_' . $_REQUEST['id'], count($lists));
            return $lists;
        }
        
        public function getFeedDetail()
        {
            if ($_GET) {
                global $wpdb;
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    $tbl = $wpdb->prefix . "etcpf_feeds";
                    $feedData = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tbl WHERE id = %d", array($id)));
                    return $feedData;
                }
            }
            
        }
        
        function startUpload($item)
        {
            $url = "https://openapi.etsy.com/v2/listings?scopes=email_r%20listings_r%20listings_w";
            $item['shipping_template_id'] = $this->mate->fields->shipping_template_id;
            
            $listing_id = '';
            if ($item['listing_id'] > 0) {
                $listing_id = $item['listing_id'];
                $url = "https://openapi.etsy.com/v2/listings/" . $listing_id . "?scopes=email_r%20listings_r%20listings_w";
            } else {
                unset($item['listing_id']);
            }
            
            unset($item['shop_id']);
            unset($item['id']);
            unset($item['item_id']);
            unset($item['uploaded']);
            unset($item['feed_id']);
            unset($item['uploaded_date']);
            unset($item['image']);
            unset($item['additional_images']);
            
            if (strlen($item['tags']) < 1) {
                unset($item['tags']);
            }
            
            if (strlen($item['materials']) < 1) {
                unset($item['materials']);
            }
            
            $init = $this->_initiate();
            
            if ($init) {
                
                $params = array(
                    'shipping_template_id' => $item['shipping_template_id'],
                    'method' => $listing_id > 0 ? "PUT" : "POST",
                );
                
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "POST", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
                update_option('etcpf_update_status', 'Configuring token access... ');
                unset($item['shipping_template_id']);
                
                return $this->_curlPost($acc_req, $item);
            }
            return false;
            
        }
        
        function purifyCategoryID($id)
        {
            if (strlen($id) <= 0) {
                return false;
            }
            
            $id = explode(":", $id);
            return end($id);
            
        }
        
        function formartImageLink($image)
        {
            $name = basename($image);
            /*'@/var/www/html/amazonfeed/wp-content/uploads/2017/01/unnamed-4e0ef91fda.jpg;type=image/jpeg'*/
            $mime_type = wp_check_filetype($image);
            $format = '@';
            $url = wp_upload_dir();
            $pos = strpos($image, 'uploads');
            $img = substr($image, $pos + 7);
            $format .= $url['basedir'] . $img;
            $format .= ';type=' . $mime_type['type'];
            return $format;
        }
        
        function saveItem($item)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE item_id = %d", array($item['item_id'])));
            $res = false;
            if ($id) {
                $update = $wpdb->update($table, $item, array('id' => $id));
                if ($update) {
                    $res = $id;
                }
                
            } else {
                $insert = $wpdb->insert($table, $item);
                if ($insert) {
                    $res = $wpdb->insert_id;
                }
                
            }
            $count = $wpdb->get_var("SELECT id FROM $table ORDER BY id DESC LIMIT 1");
            $this->product_ids = $count;
            return $res;
        }
        
        function get_configuration_value()
        {
            foreach ($this->config as $key => $value) {
                switch ($value->configuration_title) {
                    case 'who_made':
                    case 'when_made':
                    case 'state':
                        $data[$value->configuration_title] = $value->configuration_value;
                        break;
                    case 'default':
                        break;
                }
            }
            return $data;
        }
        
        function checkItem($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $item_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE item_id = %d AND listing_id = %s", array($id, '')));
            if ($item_id) {
                return true;
            } else {
                return false;
            }
        }
        
        function get_category_details($id)
        {
            $url = "https://openapi.etsy.com/v2/taxonomy/seller/" . $id . "/properties";
            
            $init = $this->_initiate();
            if ($init) {
                
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
            }
            $data = $this->_curlRequest($acc_req);
            return $data;
        }
        
        function get_remote_category()
        {
            $url = "https://openapi.etsy.com/v2/taxonomy/seller/get";
            //$url = "https://openapi.etsy.com/v2/taxonomy/categories/accessories";
            $init = $this->_initiate();
            if ($init) {
                
                $params = array();
                $token = new OAuthConsumer($this->mate->fields->oauth_token, $this->mate->fields->oauth_token_secret);
                $acc_req = OAuthRequest::from_consumer_and_token($this->consumer, $token, "GET", $url, $params);
                $acc_req->sign_request($this->hmac_method, $this->consumer, $token);
            }
            $data = json_decode($this->_curlRequest($acc_req));
            $this->view('etsy_cat_tree', array('categories' => $data->results));
        }
        
        function prepare_from_product()
        {
            global $wpdb;
            $category_id = explode(":", $_POST['category']);
            $category_id = $category_id[1];
            $selected = $_POST['selected_products'];
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $sql = $wpdb->prepare("UPDATE {$table} SET category_id = %d WHERE item_id IN ($selected)", array($category_id));
            $wpdb->query($sql);
            echo 'Product with id ' . $selected . ' are ready to upload!';
        }
        
        function upload_from_product()
        {
            global $wpdb;
            if (isset($_POST['selected_products'])) {
                $selected_ids = $_POST['selected_products'];
            }
            
            $table = $wpdb->prefix . "etcpf_tmp_etsy_listing";
            $sql = "SELECT id,image,description,listing_id,quantity,title,price,who_made,when_made,state,is_supply,category_id,shipping_template_id FROM {$table} WHERE item_id IN ($selected_ids)";
            $data = $wpdb->get_results($sql, ARRAY_A);
            
            $count = 0;
            foreach ($data as $key => $item) {
                $detail_id = $item['id'];
                $image = $item['image'];
                unset($item['id']);
                unset($item['image']);
                $response = $this->doUploading($item);
                $res = json_decode($response);
                
                $listing_id = $res->results[0]->listing_id;
                $user_id = $res->results[0]->user_id;
                if ($listing_id > 0) {
                    $wpdb->update($table, array('listing_id' => $listing_id, 'uploaded' => 1, 'description' => 0, 'shop_id' => $user_id), array('id' => $detail_id));
                    $imgRequest = $this->uploadImage($listing_id, $image);
                }
                $count = $count + $res->count;
            }
            echo $count;
        }
        
        function get_config($name)
        {
            global $wpdb;
            $table = $wpdb->prefix . "etcpf_etsy_configuration";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE configuration_title = %s", array($name));
            $config = $wpdb->get_row($sql);
            return $config;
        }
    }
}
