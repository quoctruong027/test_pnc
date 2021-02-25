<?php
if (!defined('ABSPATH')) exit;
if (!is_admin()) die("Permission Denied");
define('XMLRPC_REQUEST', true);
if (!wp_verify_nonce(sanitize_text_field($_POST['security']), 'exportfeed_etsy_cpf')) die("Security Denied");
ob_start(null);
require_once dirname(__FILE__) . '/../../../etsy-export-feed-wpincludes.php';
include_once dirname(__FILE__) . '/../../classes/etsyclient.php';
if (defined('ENV')) {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

Class ETCPF_Customfeed
{
    public function __construct($method)
    {
        global $etcore;
        $this->etcore = $etcore;
        $this->etcore->trigger('etcpf_init_feeds');
        add_action('etcpf_custom_main_feed_hook_' . $method, array($this, $method));
        do_action('etcpf_custom_main_feed_hook_' . $method);
    }

    public function index()
    {
        if (!$_POST) {
            $this->set_output(array('status' => 'error', 'msg' => 'No post parameters supplied'));
        } else {
            $requestCode = 'Etsy';
            $filename = sanitize_text_field($this->post('filename'));
            $feedIdentifier = sanitize_text_field($this->post('feed_identifier'));
            $saved_feed_id = intval($this->post('feed_id'));
            $feed_list = sanitize_text_field($this->post('feed_ids')); //For Aggregate Feed Provider
            $feedLimit = sanitize_text_field($this->post('feedLimit'));
            $feed_type = intval($this->post('feed_type'));
            $data = $this->post('products');
            $selectedProductArray = json_decode(stripslashes($data), true);

            if(empty($selectedProductArray)){
                $this->set_output(array(
                    'status' => 'error',
                    'type' => 'info',
                    'msg' => 'Items not selected. Please select at least an item to create feed.',
                    'response code' => 200,
                )); exit();
            }

            $categories = json_decode(stripslashes($this->post('categories')), true);
            $this->etcore->feedType = 1;
            if (empty($filename)) {
                $this->set_output(array('status' => 'error', 'msg' => 'Error: Please mention file name for the feed'));
            }
            /* Check if directory is writable */
            $dir = ETCPF_FeedFolder::uploadRoot();
            if (!is_writable($dir)) {
                $this->set_output(array('status' => 'error', 'msg' => "Error: Folder {$dir} must be writable"));
            }
            $dir = ETCPF_FeedFolder::uploadFolder();

            if (!is_dir($dir)) {
                mkdir($dir);
                mkdir($dir.'Etsy/');
            }
            if (!is_writable($dir)) {
                $this->set_output(array('status' => 'error', 'msg' => "Error: Folder {$dir} must be writable "));
            }
            $providerFile = 'feeds/etsy/feed.php';

            if (!file_exists(dirname(__FILE__) . '/../../' . $providerFile)) {
                if (!class_exists('ETCPF_EtsyFeed')) {
                    $this->set_output(array('status' => 'error', 'msg' => "Error: Provider file not found."));
                }
            }

            $providerFileFull = dirname(__FILE__) . '/../../' . $providerFile;
            if (file_exists($providerFileFull)) {
                include_once realpath($providerFileFull);
            }

            $filename = sanitize_title_with_dashes($filename);
            if ($filename == '') {
                $filename = 'feed' . rand(10, 1000);
            }
            $params = array(
                'filename' => $filename,
                'categories' => $categories,
                'products' => $selectedProductArray,
                'feedtype' => 1
            );
            $customFeedObj = new ETCPF_EtsyFeed();
            $check = $customFeedObj->checkIfFeedFileAlreadyExists($filename);
            if (isset($check) && $check != false) {
                $result = $customFeedObj->updateCustomFeed($check->id, $params, $check);
                /*$this->set_output(array(
                    'status' => 'failed',
                    'msg' => 'Feed File with the name' . $filename . ' already exists',
                    'data' => json_encode($check)
                ));*/
            } else {
                $result = $customFeedObj->createCustomFeed($params);
            }
            if ($result && isset($result['status'])) {
                /* @Todo: Handle success here */
                $errorOutput = $customFeedObj->getErrorReportList($filename, $result['id']);
                /*wp_redirect(admin_url() . 'admin.php?page=etsy-export-feed-manage&option=errorreportpage&feed_id=' . $result['id']);
                exit;*/
                $outputdata = array(
                    'status' => 'success',
                    'response code' => 200,
                    'file_url' => $result['file_url'],
                    'pagelink' => admin_url() . 'admin.php?page=etsy-export-feed-manage&option=errorreportpage&feed_id=' . $result['id']
                );
                $this->set_output($outputdata);
            } else {
                /* Handle error case here */
                $this->set_output(array(
                    'status' => 'failed',
                    'response code' => 502,
                ));
            }
        }
    }

    public function edit_feed()
    {
        $id = $this->post('feed_id');
    }

    public function set_output($args)
    {
        if ($args) {
            wp_send_json_success($args);
            exit();
        }
        wp_send_json_error(array("status" => 'error', 'msg' => 'No return arguments supplied'));
        exit;
    }

    private function post($index)
    {
        if (isset($_POST[$index])) {
            return $_POST[$index];
        }
        return null;
    }

    public function getEtsyCategories()
    {
        $localcategory = $this->post('localcat');
        $etsy = new ETCPF_Etsy();
        $data = $etsy->getEtsyCategoriesForCustom($localcategory);
        exit;
    }

    public function _Initiate()
    {
        $method = array_key_exists('perform', $_POST) ? $_POST['perform'] : null;
        $arguments = array_key_exists('params', $_POST) ? $_POST['params'] : $_POST;
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }
        if (is_null($method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods was null"));
        } elseif (!method_exists($this, $method)) {
            echo json_encode(array('success' => false, 'msg' => "Methods {$method} does not exists."));
        } else {
            call_user_func_array(array($this, $method), array($arguments));
        }
    }
}

if (isset($_POST['perform'])) {
    $OBJECT = new ETCPF_Customfeed($_POST['perform']);
} else {
    wp_send_json_error(array("status" => 'error', 'msg' => 'Method Cannot be empty.'));
}
//$OBJECT->_Initiate();


