<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
require_once 'core/classes/etsyclient.php';
require_once 'core/classes/etsy-upload.php';

class ETCPF_Product_Uploaded extends WP_List_Table
{
    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'product',     //singular name of the listed records
            'plural' => 'products',    //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name)
    {
        $data = maybe_unserialize($item['upload_result']);
        $preparedData = json_decode($item['prepared_data']);
        if (isset($preparedData)) {
            if ($item['variation_upload_result'] && $item['variation_upload_result'] !== null) {
                if (json_decode($item['variation_upload_result'])) {
                    $count = isset(json_decode($item['variation_upload_result'])->results->products) ? count(json_decode($item['variation_upload_result'])->results->products) : '';
                    $variation = isset($count) ? $count . " variations" : $item['variation_upload_result'];
                } else {
                    $variation = $item['variation_upload_result'];
                }

            } else {
                $variation = "No variation on this product item";
            }
            switch ($column_name) {
                case 'title':
                    return $preparedData->title;
                    break;
                case 'quantity':
                    return $preparedData->quantity;
                    break;
                case 'state':
                    return $preparedData->state;
                    break;
                case 'last_updated':
                    return $item['uploaded_at'];
                    break;
                case 'price':
                    $price_html = isset($data->currency_code) ? $preparedData->price.' '.$data->currency_code : $preparedData->price;
                    return $price_html;
                    break;
                case 'variation':
                    if ($item['variation_upload_result'] == null) {
                        return $variation;
                    }
                    return $this->showDetails($variation, $item['id']);
                    break;

                    case 'upload_result':
                        if(isset($item['upload_result']) && $item['uploaded'] !=2){
                            if($item['uploaded'] == 3 || $item['uploaded'] ==6 ){
                                $html = str_replace('Relist Item ?','',$item['upload_result']);
                                $html = str_replace('Delete','',$html);
                                return $html;
                            }else{
                                return 'Not uploaded or updated yet.';
                            }
                        }else{
                            if($item['uploaded'] == 2){
                                return 'Uploaded Successfully';
                            }else{
                                return 'Uploading..';
                            }
                        }

                case 'shipping_template_id':
                    $etsy = new ETCPF_Etsy();
                    $shipping = $etsy->shipping_info($preparedData->shipping_template_id);
                    $country = isset($shipping->country) ? $shipping->country: '';
                    return isset($shipping->title)? $shipping->title : '' . '(' . $country . ')';
                    break;
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        } else {
            return "No Data Found.";
        }
    }


    function showDetails($message, $id)
    {
        $action['view details'] = "<a href='" . admin_url() . "admin.php?page=etsy-export-feed-upload&cmd=variationdetail&id=" . $id . "'>Update<a>";
        $always_visible = true;
        return sprintf('%1$s %2$s',
            /*$1%s*/
            $message,
            /*$2%s*/
            $this->row_actions($action, $always_visible)
        );
        // return sprintf('<a href="%s" target="_blank">View details</a>', '?page=amwscpf-feed-orders&action=createorder&post='. $id);

    }

    function column_title($item)
    {
        // $shop_id = get_option('etcpf_shop_id');
        // $shop = get_option('etcpf_etsy_shops');
        // $shop_name = $shop[$shop_id]->shop_name;
        $shop = get_option('etcpf_etsy_shops');
        if ($shop) {
            foreach ($shop as $shop_detail) {
                $shop_name = $shop_detail->shop_name;
            }
        }

        $data = json_decode($item['prepared_data']);
        //Build row actions
        if(isset($data)){
            if($data->state == 'active'){
                $actions['view'] = sprintf('<a target="_blank" href="https://www.etsy.com/listing/%d">View Listing</a>', $item['listing_id']);
            }else{
                $actions[' view'] = sprintf('<a target="_blank" href="https://www.etsy.com/your/shops/%s/tools/listings/state:%s,sort:update_date,stats:true/%d">View Listing</a>',$shop_name,$data->state,$item['listing_id']);
            }
        }
        $actions['delete'] = sprintf('<a href="?page=%s&action=%s&id=%d">Delete From Etsy</a>', $_REQUEST['page'], 'delete_from_etsy', $item['id']);
        /*$actions['edit'] = sprintf('<a target="" href="?page=%s&action=%s&id=%d">Edit</a>', $_REQUEST['page'], 'edit', $item['id']);*/


        if (isset($data)) {
            if ($data->state == 'draft' || $data->state == 'inactive')
                $actions['state'] = sprintf('<a class="activate_in_etsy" href="?page=%s&action=%s&id=%d">Make Active in Etsy</a>', $_REQUEST['page'], 'state_change', $item['id']);
            /**
             * if ($data->state == 'active')
             * $actions['state'] = sprintf('<a class="inactive_etsy" href="?page=%s&action=%s&id=%d">Make Inactive in Etsy</a>', $_REQUEST['page'], 'state_inactive', $item['id']);
             */
            if (NULL == $item['listing_id']) {
                $actions['msg'] = sprintf('<br><span style="color:red">%s</span>', $item['upload_result']);
            }
            $title = $data->title;
        } else {
            $title = "<p style='color:red;'>It seems that product didn't uploaded on etsy.Please select all and delete and then reupload.</p>";
            $actions[] = null;
        }

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/
            $title,
            /*$2%s*/
            $item['id'],
            /*$3%s*/
            $this->row_actions($actions, true)
        );
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'title' => 'Title',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'variation' => 'Variations',
            'upload_result' => 'Upload Result',
            'shipping_template_id' => 'Shipping Title',
            'state' => 'State',
            'last_updated' => 'Last Update',
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            /*'state' => array('state', false),     //true means it's already sorted
            'title' => array('title', false),
            'price' => array('price', false),
            'quantity' => array('quantity', false),*/
            'shipping_template_id' => array('shipping_template_id', false),
            'last_updated' => array('last_updated', false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'delete from database',
            'delete_from_etsy' => 'delete from etsy',
            //'make_all_active' => 'Make All Active In Etsy'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $etsy = new ETCPF_Etsy();
        $etsyUploader = new ETCPF_EtsyUpload();
        $tbl = $wpdb->prefix . "etcpf_listings";

        $action = $this->current_action();
        $allowed_actions = array('delete', 'edit', 'state_change', 'delete_from_etsy', 'state_inactive', 'make_all_active');
        if (!in_array($action, $allowed_actions)) {
            return;
        }
        //security check
        if (isset($_REQUEST['_wpnonce']))
            check_admin_referer('bulk-products');

        if ($this->current_action()) {
            if (isset($_REQUEST['id']))
                $ids = [$_REQUEST['id']];
            else
                $ids = isset($_REQUEST['product']) ? $_REQUEST['product'] : null;
            $action = $this->current_action();
            switch ($action) {
                case 'delete':
                case 'delete_from_etsy':
                    if(!empty($ids)){
                        foreach ($ids as $key => $id) {
                            $listing_id = $wpdb->get_var($wpdb->prepare("SELECT listing_id FROM $tbl WHERE id = %d", [$id]));
                            $msg = 'd';
                            if ($action == 'delete_from_etsy' && $listing_id) {
                                $etsy->deleteFromEtsy($listing_id);
                                $msg = 'de';
                            }
                            $delete = $wpdb->delete($tbl, ['id' => $id]);

                        }
                    }else{
                        $msg = 'Items Not Selected';
                    }
                    $url = admin_url('admin.php?page=etsy-export-feed-upload&msg=' . $msg);
                    echo '<script type="text/javascript">window.location.href = "' . $url . '"</script>';
                    exit();
                    break;

                case 'edit':
                    $id = $_REQUEST['id'];
                    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tbl WHERE id = %d", [$id]));
                    echo $etsy->view('listing_edit_page', [
                        'item' => $item
                    ]);
                    exit();
                    break;

                case 'state_change':
                    $msg = "Failed";
                    $id = $_REQUEST['id'];
                    $params = ['state' => 'active'];
                    $data = $etsyUploader->prepare_the_list_by_id($id, $params);
                    $result = $etsyUploader->EtsyUploadById((array) $data,true);
                    if(isset($result['status']) && $result['status']=='CONTINUE'){
                        $etsyUploader->logUploadResult($data,$result['listing_id']);
                        $title = $result['data']['title'];
                        $msg = 'e';
                    }else{
                        $title = 'Failed';
                        $msg = 'b';
                    }
                    /*$listing = $etsyUploader->get_submitted_listing_by_id($id);
                    if ($listing) {
                        $data = maybe_unserialize($listing->upload_result);

                    } else {
                        $msg = 'b';
                        $data = new stdClass();
                        $data->title = 'n/a';
                    }*/
                    $url = admin_url('admin.php?page=etsy-export-feed-upload&msg=' . $msg . '&title=' . $title);
                    echo '<script type="text/javascript">window.location.href = "' . $url . '"</script>';
                    exit();
                    break;
                case 'state_inactive':
                    $id = $_REQUEST['id'];
                    $params = ['state' => 'inactive'];
                    $etsyUploader->prepare_the_list_by_id($id, $params);
                    $etsyUploader->submit_listing_to_etsy(null,null,null);
                    $listing = $etsyUploader->get_submitted_listing_by_id($id);

                    if ($listing) {
                        $data = maybe_unserialize($listing->upload_result);

                        $msg = 'f';
                    } else {
                        $msg = 'b';
                        $data = new stdClass();
                        $data->title = 'n/a';
                    }
                    $url = admin_url('admin.php?page=etsy-export-feed-upload&msg=' . $msg . '&title=' . $data->title);
                    echo '<script type="text/javascript">window.location.href = "' . $url . '"</script>';
                    exit();
                    break;
                case 'make_all_active':
                    $data = new stdClass();
                    if (is_array($ids) && count($ids) > 0) {
                        foreach ($ids as $id) {
                            $params = ['state' => 'active'];

                            /**==========================================================================
                             * $etsyUploader->prepare_the_list_by_id($id, $params);
                             * $listing = $etsyUploader->get_submitted_listing_by_id($id);
                             * if ($listing) {
                             * $data = maybe_unserialize($listing->upload_result);
                             * $msg = 'g';
                             * } else {
                             * $msg = 'b';
                             * $data->title = 'n/a';
                             * }
                             * ===========================================================================*/

                            $transaction = $etsyUploader->update_listing_by_id($id, $params);
                            $msg = $transaction['message'];
                            $data->title = $transaction['listing_id'];
                            sleep(1);
                        }
                    } else {
                        $msg = 'np';
                        $data->title = 'N/A';
                    }
                    $url = admin_url('admin.php?page=etsy-export-feed-upload&msg=' . $msg . '&title=' . $data->title);
                    wp_redirect($url);
                    exit;
                    //echo '<script type="text/javascript">window.location.href = "' . $url . '"</script>';
                    break;
            }
        }
    }

    function perform_export($post_id)
    {
        return true;
    }

    function prepare_items()
    {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 25;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();


        $data = $this->get_items();
        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }
       if(isset($_REQUEST['orderby'])){
           usort($data, 'usort_reorder');
       }

        $current_page = $this->get_pagenum();

        $total_items = count($data);


        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

    function get_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . "etcpf_listings";
        /* Perform search when search is present */
        if(isset($_REQUEST['s'])){
            $search = $_REQUEST['s'];
            $where = "WHERE feed_id='{$search}' OR item_id='{$search}' OR listing_id='{$search}' OR title like '%{$search}%'";
        }else{
            $where= '';
        }
        $query = "SELECT * FROM {$table} {$where} ORDER BY id ASC";
        $data = $wpdb->get_results($query, ARRAY_A);
        return $data;
    }
}
