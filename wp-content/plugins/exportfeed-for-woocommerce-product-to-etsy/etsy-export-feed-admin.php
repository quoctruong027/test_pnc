<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define("PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
/**
 * Required admin files
 *
 */
require_once 'etsy-export-feed-setup.php';
/**
 * Hooks for adding admin specific styles and scripts
 *
 */

add_action('admin_enqueue_scripts', 'gtcpf_styles_and_scripts_register');
function gtcpf_styles_and_scripts_register($hook)
{
    if (!strchr($hook, 'etsy-export-feed')) {
        return;
    }

    wp_enqueue_style('etcpf_bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');

    wp_register_style('etsy-export-feed-style', plugins_url('css/etsy-export-feeds.css', __FILE__));
    wp_enqueue_style('etsy-export-feed-style');
    $protocol = is_ssl() ? 'https://' : 'http://';
    wp_register_style('etcpf-progress-tacker-font', $protocol . 'fonts.googleapis.com/css?family=Source+Sans+Pro:400,700');
    wp_enqueue_style('etcpf-progress-tacker-font');

    wp_register_style('etcpf-progress-tacker-reset', plugins_url('css/progress-reset.css', __FILE__));
    wp_enqueue_style('etcpf-progress-tacker-reset');

    wp_register_style('etcpf-progress-tacker-style', plugins_url('css/progress-style.css', __FILE__));
    wp_enqueue_style('etcpf-progress-tacker-style');

    wp_register_style('etcpf-progress-tacker-modernizer', plugins_url('js/progress-modernizr.js', __FILE__), array('jquery'), ETCPF_PLUGIN_VERSION);
    wp_enqueue_style('etcpf-progress-tacker-modernizer');

    wp_register_style('etsy-export-feed-colorstyle', plugins_url('css/etcpf_colorbox.css', __FILE__), '', ETCPF_PLUGIN_VERSION);
    wp_enqueue_style('etsy-export-feed-colorstyle');

    wp_register_script('etsy-export-feed-script', plugins_url('js/etsy-export-feeds.js', __FILE__), array('jquery'), ETCPF_PLUGIN_VERSION);
    wp_enqueue_script('etsy-export-feed-script');

    wp_register_script('etsy-export-feed-order-script', plugins_url('js/etcpf-etsy-order-fetch.js', __FILE__), array('jquery'), ETCPF_PLUGIN_VERSION);
    wp_enqueue_script('etsy-export-feed-order-script');

    wp_register_script('etsy-export-feed-script-datable', plugins_url('js/DataTables/datatables.js', __FILE__), array('jquery'), ETCPF_PLUGIN_VERSION);
    wp_enqueue_script('etsy-export-feed-script-datable');
//     Datatables attributes
    wp_register_style('etsy-export-feed-script-datatable', plugins_url('js/DataTables/datatables.css', __FILE__), '', ETCPF_PLUGIN_VERSION);
    wp_enqueue_style('etsy-export-feed-script-datatable');

    // Localizing the script
    wp_localize_script('etsy-export-feed-script', 'ETCPF', array(
        'ETCPF_nonce' => wp_create_nonce('exportfeed_etsy_cpf'),
        'cmdFetchCategory' => "core/ajax/wp/fetch-category.php",
        'cmdFetchCategory_custom' => "core/ajax/wp/fetch-category-custom.php",
        'cmdFetchLocalCategories' => "core/ajax/wp/fetch-local-categories.php",
        'cmdFetchLocalCategories_custom' => "core/ajax/wp/fetch-local-categories-custom.php",
        'cmdFetchTemplateDetails' => "core/ajax/wp/fetch-template-details.php",
        'cmdGetFeed' => "core/ajax/wp/get-feed.php",
        'cmdGetCustomFeed' => "core/ajax/wp/get-custom-feed.php",
        'cmdGetcustomfeed_etsy' => "core/ajax/wp/get-custom-feed.php",
        'cmdGetFeedStatus' => "core/ajax/wp/get-feed-status.php",
        'cmdMappingsErase' => "core/ajax/wp/attribute-mappings-erase.php",
        'cmdSearsPostByRestAPI' => "core/ajax/wp/sears_post.php",
        'cmdSelectFeed' => "core/ajax/wp/select-feed.php",
        'cmdSetAttributeOption' => "core/ajax/wp/attribute-mappings-update.php",
        'cmdSetAttributeUserMap' => "core/ajax/wp/attribute-user-map.php",
        'cmdUpdateAllFeeds' => "core/ajax/wp/update-all-feeds.php",
        'cmdUpdateSetting' => "core/ajax/wp/update-setting.php",
        'cmdUploadFeed' => "core/ajax/wp/upload_feed.php",
        'cmdUploadFeedStatus' => "core/ajax/wp/upload_feed_status.php",
        'cmdUpdateFeedConfig' => "core/ajax/wp/update-feed-config.php",
        'cmdFetchProductAjax' => "core/ajax/wp/fetch-product-ajax.php",
        'cmdFetchProductTable' => "core/ajax/wp/fetch-product-ajax.php",
        'cmdEtsyProcessings' => "core/ajax/wp/fetch_etsy_categories.php",
        'cmdEtsyConnect' => 'core/ajax/wp/connect-to-etsy.php',
        'cmdEtsyShop' => admin_url('admin.php?page=etsy-export-feed-configure'),
        'cmdEtsyShipping' => '&tab=settings',
        'cmdEtsyCreateFeed' => '&tab=createfeed',
        'cmdEtsyConfiguration' => '&tab=configuration',
        //'cmdEtsyUploadFeed' => ETCPF_URL . 'core/ajax/wp/etsyupload.php',
        'cmdEtsyUploadData' => ETCPF_URL . 'core/ajax/wp/upload-to-etsy.php',
        'plugins_url' => ETCPF_URL,
        'cmdUploadListing' => "core/ajax/wp/upload_listings.php",
        'cmdFetchLoginURL' => "core/ajax/wp/fetch_login_url_token.php",
        'cmdAuthorizeURL' => "core/ajax/wp/authorize_etsy_token.php",
        'cmdUpdateItem' => "core/ajax/wp/update_listing_item.php",
        'cmdUploadStatus' => "core/ajax/wp/update_and_upload_status.php",
        'cmdEtsyUploadFeed' => 'core/ajax/wp/do_upload_feed.php',
        'cmdResolveFeed' => 'core/ajax/wp/do_resolvefeed.php',
        'cmdResolveProcess' => 'core/ajax/wp/Etcpf_ResolveFeed.php',
        'cmdSearchProduct' => 'core/ajax/wp/ETCPF_Product_Search.php',
        'cmdOrderFectch' => 'core/ajax/wp/etsy-order-fetch.php',
        'cmdsettingsUpdate' => 'core/ajax/wp/etsy-settings.php',
        'cmdUpdateOption' => 'core/ajax/wp/etsy-update-option.php',
        'cmdlanguageUpdate' => 'core/ajax/wp/etsy-language-update.php',
        'cmdsinglevariationpreparation' => 'core/ajax/wp/singlevariatedproductuploadprepare.php',
        'cmd_profiling_ajax_handler'  => 'core/ajax/wp/etcpf_profiling_ajax_handler.php',
        'cmd_get_order_details' => 'core/ajax/wp/get_order_details.php'
    ));

    wp_register_script('google-merchant-etcpf_colorbox', plugins_url('js/jquery.etcpf_colorbox-min.js', __FILE__), array('jquery'));
    wp_enqueue_script('google-merchant-etcpf_colorbox');
}

if (isset($_POST['action']) && $_POST['action'] == 'exportfeed_etsy') {
    add_action('wp_ajax_exportfeed_etsy', 'gtcpf_all_ajax_handle');
}

if (isset($_GET['action']) && $_GET['action'] == 'exportfeed_etsy') {
    add_action('wp_ajax_exportfeed_etsy', 'gtcpf_all_ajax_handle');
}

/*
 * Adding bulk action in product page
 * */
require_once 'etcpf-product-listing.php';

function gtcpf_all_ajax_handle()
{
//    display_error(true);
    $nonce = "";
    $nonce = isset($_GET['security']) ? sanitize_text_field($_GET['security']) : sanitize_text_field($_POST['security']);
    if (!wp_verify_nonce($nonce, 'exportfeed_etsy_cpf')) {
        die('Permission Denied!');
    } else {
        $feedpath = isset($_POST['feedpath']) ? $_POST['feedpath'] : $_GET['feedpath'];
        include_once plugin_dir_path(__FILE__) . $feedpath;
    }
    wp_die();
}

/**
 * Add menu items to the admin
 *
 */
function gtcpf_admin_menu()
{
    /* add new top level */
    add_menu_page(
        __('Etsy Feed', 'etsy-exportfeed-strings'),
        __('Etsy Feed', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-configure',
        'etcpf_etsy_configuration',
        plugins_url('/images/google-merchant.png', __FILE__)
    );

    /* add the submenus */
    add_submenu_page(
        'etsy-export-feed-configure',
        __('Connect to Etsy', 'etsy-exportfeed-strings'),
        __('Connect to Etsy', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-configure',
        'etcpf_etsy_configuration'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Shipping Configuration', 'etsy-exportfeed-strings'),
        __('Shipping Configuration', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-shipping',
        'etcpf_etsy_shipping'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Create New Feed', 'etsy-exportfeed-strings'),
        __('Create New Feed', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-admin',
        'etcpf_admin_page'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Manage Feeds', 'etsy-exportfeed-strings'),
        __('Manage Feeds', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-manage',
        'etcpf_manage_page'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Listing/Upload', 'etsy-exportfeed-strings'),
        __('Listing/Upload', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-upload',
        'etcpf_etsy_upload'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Etsy Orders', 'etsy-exportfeed-strings'),
        __('Etsy Orders', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-order',
        'etsy_export_order'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Etsy Product List', 'etsy-exportfeed-strings'),
        __('Etsy Product List', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-product-list',
        'etsy_product_list'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Variation Profiling', 'etsy-exportfeed-strings'),
        __('Variation Profiling', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-profiling',
        'etcpf_profiling_page'
    );

    add_submenu_page(
        'etsy-export-feed-configure',
        __('Settings', 'etsy-exportfeed-strings'),
        __('Settings', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-setting',
        'etcpf_setting_page'
    );

    add_submenu_page(
        null,
        __('Custom Feed Page', 'etsy-exportfeed-strings'),
        __('Custom Feed Page', 'etsy-exportfeed-strings'),
        'manage_options',
        'etsy-export-feed-custom-feed',
        'etcpf_custom_product_feed'
    );
}

add_action('admin_menu', 'gtcpf_admin_menu');
add_action('etcpf_init_pageview_etsy', 'etcpf_admin_page_action');
add_action('wp_enqueue_scripts', 'wpb_adding_scripts');
add_action('errorreportpage', 'error_report_function');
add_action('etcpf_etsy_report', 'etcpf_listing_report');

/*if(isset($_REQUEST['page']) && $_REQUEST['page']=='errorreportpage'){
do_action('errorreportpage');
}*/
/*add_action('page_request','handle_page_request');
do_action('page_request');*/

/*function handle_page_request(){
if($_GET['page']=='etcpf-listing-variation'){
$id = $_GET['id'];
require_once 'core/etsy-views/varaition-manage-page.php';
$view = new variation();
$view->index($id);
}
}*/

function etcpf_setting_page()
{
    require_once 'core/classes/ETCPF_settings.php';
    update_option('currently_uploading_feed_id', 0);
    $etsy = new ETCPF_settings();
    $etsy->index();
}

function etcpf_admin_page()
{

    require_once 'etsy-export-feed-wpincludes.php';
    include_once 'core/classes/dialogfeedpage.php';
    require_once 'core/feeds/basicfeed.php';
    update_option('currently_uploading_feed_id', 0);
    $etsy = new ETCPF_Etsy();
    if (null == $etsy->api_key) {
        $etsy->get_credentials();
    }

    if ($etsy->mate->count == 0) {
        echo '<script>etcpf_call_out_for_account();</script>';
    }
    global $etcore;
    $etcore->trigger('etcpf_init_feeds');

    do_action('etcpf_init_pageview_etsy');
}

/**
 * Create news feed page
 */
function etcpf_admin_page_action()
{
    echo "<div class='wrap'>";
    echo "<div class='cpf-header'>";
    echo '<h2>Etsy Shopping with ExportFeed';
    $url = admin_url('admin.php?page=etsy-export-feed-manage');
    $contact_url = admin_url('admin.php?page=etsy-export-feed-admin-contact');
    echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location="' . $url . '";" value="' . __('Manage Feeds', 'etsy-exportfeed-strings') . '" />
    </h2>';
    echo '</div>';
//    prints logo/links header info: also version number/check
    etcpf_version();
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'createfeed';
    ?>
    <?php $reg = new ETCPF_EtsyValidation; ?>
    <div class="nav-wrapper" style="margin-left: -7px;">
        <nav class="nav-tab-wrapper">
            <a href="<?php echo admin_url('admin.php?page=etsy-export-feed-admin&tab=createfeed'); ?>"
               class="nav-tab <?php echo $active_tab == 'createfeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Create Feed', 'etsy-exportfeed-strings'); ?></a>
            <a href="<?php echo $url; ?>&tab=managefeed"
               class="nav-tab <?php echo $active_tab == 'managefeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Manage Feed', 'etsy-exportfeed-strings'); ?></a>
            <a href="http://www.exportfeed.com/contact/" target="_blank"
               class="nav-tab <?php echo $active_tab == 'contactus' ? 'nav-tab-active ' : ''; ?>"><?php _e('Contact Us', 'etsy-exportfeed-strings'); ?></a>
            <?php
            $ifpremium = false;
            if ($reg->results['status'] == 'Active') {
                $checklicense = $reg->results;
                $productname = explode(':', $checklicense['productname']);
                if (strpos($productname[0], 'Pro') !== false) {
                    $ifpremium = true;
                }
            }
            if ($ifpremium == false) {
                ?>
                <a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank"
                   class="nav-tab"><?php _e('Go Pro', 'etsy-exportfeed-strings'); ?></a>
            <?php } ?>

            <ul class="subsubsub" style="float: right;">
                <?php if ($ifpremium == false) { ?>
                    <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Go Premium</a> |
                    </li>
                <?php } ?>
                <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Product Site</a> |
                </li>
                <li><a href="http://www.exportfeed.com/faq/" target="_blank">FAQ/Help</a></li>
            </ul>
        </nav>
    </div>
    <div class="clear"></div>
    <?php
    $action = '';
    $source_feed_id = -1;
    $feed_type = -1;
    $progress = false;
//WordPress Header ( May contain a message )

    $message2 = null;
    //check action
    if (isset($_POST['cmd'])) {
        $action = sanitize_key($_POST['cmd']);
    }

    if (isset($_GET['cmd'])) {
        $action = sanitize_key($_GET['cmd']);
    }

    switch ($action) {
        case 'reset_attributes':
            //I don't think this is used -K
            global $wpdb, $woocommerce;
            $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
            $sql = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
            $attributes = $wpdb->get_results($sql);
            foreach ($attributes as $attr) {
                delete_option($attr->attribute_name);
            }

            break;
        case 'edit':
            $action = '';
            $source_feed_id = intval($_GET['id']);
            $feed_type = intval($_GET['feed_type']);
            break;

        case 'upload':
            $source_feed_id = intval($_REQUEST['id']);
            $etsy = new ETCPF_Etsy();
            $etsyUpload = new ETCPF_EtsyUpload();
            $etsyUpload->prepare_the_list_from_feed($source_feed_id, false);
            $progress = true;
            break;

        case 'report':
            do_action('etcpf_etsy_report');
            break;
    }

    /*if (isset($action) && (strlen($action) > 0))
    echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=etsy-export-feed-admin' );</script>";*/
    $reg = new ETCPF_EtsyValidation();
    if (isset($_GET['debug'])) {
        $debug = $_GET['debug'];
        if ($debug == 'phpinfo') {
            phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);
            return;
        } elseif ($debug == 'reg') {
            echo "<pre>";
            print_r($reg);
            echo "</pre>";
            return;
        }
    }
    $chosen_merchant = 'Etsy';
    if (isset($_GET['raw'])) {
        $chosen_merchant = 'Productlistraw';
    }
    # Get Variables from storage ( retrieve from wherever it's stored - DB, file, etc... )

    global $wpdb;
    //Main content
    echo '
    <script type="text/javascript">
    jQuery( document ).ready( function( $ ) {
        jQuery("#ajax-loader-cat-import").show();
        feedajaxhost = "' . plugins_url('/', __FILE__) . '";
        chosen_merchant= "' . $chosen_merchant . '";
        feed_type = 0;
        doGoogleFeed(chosen_merchant);
            doFetchLocalCategories_google();
        feed_type = ' . $feed_type . ';
        window.feed_type = feed_type;
        window.feed_id = ' . $source_feed_id . ';

        if(feed_id > 0 && feed_type == 1){
            googleSaveTocustomTable(feed_id);
            googleShowSelectedProductTables(feed_id);
        }
    } );
    </script>';

    global $message;
    if (strlen($message)) {
        $message .= '<br>';
    }
    //insert break after local message (if present)
    $message .= $reg->getMessage();

    if (!$reg->valid) {
        echo '<div id="setting-error-settings_updated" class="updated settings-error">
              <p>' . $message . '</p>
              </div>';
    }
    if ($progress) {
        $etsy->listing_progress_view();
    } else {
        if ($source_feed_id == -1) {
            // $wpdb->query("TRUNCATE {$wpdb->prefix}etcpf_custom_products");
            //Page Header
            echo ETCPF_PageDialogs::pageHeader();
            //Page Body
            echo ETCPF_PageDialogs::pageBody();
        } else {
            require_once dirname(__FILE__) . '/core/classes/dialogeditfeed.php';
            echo ETCPF_PEditFeedDialog::pageBody($source_feed_id, $feed_type);
        }
    }

}

/**
 * Display the manage feed page
 *
 */

add_action('etcpf_init_pageview_etsy_manage', 'etcpf_manage_page_action');

function etcpf_manage_page()
{
    require_once 'etsy-export-feed-wpincludes.php';
    update_option('currently_uploading_feed_id', 0);
    if (isset($_REQUEST['option'])
        && $_REQUEST['option'] == 'errorreportpage') {
        do_action('errorreportpage');
        exit;
    } else {
        include_once 'core/classes/dialogfeedpage.php';
        global $etcore;
        $etcore->trigger('etcpf_init_feeds');
        do_action('etcpf_init_pageview_etsy_manage');
    }
}

function etcpf_manage_page_action()
{
    $reg = new ETCPF_EtsyValidation();
    require_once 'etsy-export-feed-manage.php';
}

function etcpf_etsy_configuration()
{
    require_once 'core/classes/etsyclient.php';
    require_once 'etsy-export-feeds-information.php';
    include_once 'core/classes/dialogfeedpage.php';
    require_once 'core/feeds/basicfeed.php';
    $etsy = new ETCPF_Etsy();
    update_option('currently_uploading_feed_id', 0);
    if (isset($_REQUEST['stage'])) {
        $token = sanitize_text_field($_REQUEST['oauth_token']);
        $verfier = sanitize_text_field($_REQUEST['oauth_verifier']);
        $etsy->storeOauthVerifier($token, $verfier);
        update_option('etcpf_login_url', '');
        update_option('etcpf_stage', 3);
    }
    if (isset($_GET['tab']) && $_GET['tab'] == 'settings') {
        $etsy->getEtsyShopLang();
    }
    etcpf_info();
    $etsy->loadNavigationTab();
}

function etcpf_etsy_upload()
{
    if (isset($_GET['cmd'])) {
        $id = $_GET['id'];
        require_once 'core/etsy-views/varaition-manage-page.php';
        $view = new variation();
        $view->index($id);
    } else {
        require_once 'core/classes/etsyclient.php';
        require_once 'etcpf-uploaded-product.php';
        /* echo <script type="text/javascript">
                 doupload_listing();
             </script>;*/
        $etsy = new ETCPF_Etsy();
        $etsy->view('upload-listing-page');
    }

}

function etcpf_etsy_shipping()
{
    update_option('currently_uploading_feed_id', 0);
    require_once 'core/classes/etsyclient.php';
    $etsy = new ETCPF_Etsy();
    echo '<h2>Etsy Shipping</h2>';
    $etsy->listShippingTemplate();
}

function error_report_function()
{
    require_once 'etsy-export-feed-wpincludes.php';
    global $etcore;
    require_once 'core/etsy-views/error-report-page.php';
    $object = new Errorreport();
    $object->index();

}

function etcpf_custom_product_feed()
{
    include_once('core/classes/ETCPF_Customfeed.php');
    update_option('currently_uploading_feed_id', 0);
    $OBJECT = new ETCPF_Customfeed(1);
    if (isset($_REQUEST)) {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
            $OBJECT->editFeed($_REQUEST['id']);
            exit;
        } else {
            if ($OBJECT->index() == true) {
                exit;
            } else {
                echo "<pre>";
                print_r("Something went wrong");
                echo "</pre>";
                exit();
            }
        }
    }
}

function etsy_export_order()
{
    include_once('core/classes/ETCPF_Etsyorder.php');
    update_option('currently_uploading_feed_id', 0);
    $orderObject = new Etsyorder();
    $orderObject->index();
}

function etsy_product_list()
{
    include_once PLUGIN_DIR_PATH . "core/etsy-views/etsy-fetch-product.php";
}

function etcpf_profiling_page()
{
    require_once 'core/classes/Profiling.php';
    $action = isset($_GET['action']) ? $_GET['action'] : 'list_profiles';
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $invoker = new Profiling();
    if (! isset($_SESSION))
        session_start();
    switch ($action) {
        case 'add_new':
            $invoker->add_new();
            break;
        case 'list':
            $invoker->list_profiles();
            break;
        case 'edit':
            if ($id) {
                $invoker->edit_profiles($id);
            } else {
                $_SESSION['etcpf_profile_message'] = __('profile id was not provided');
               wp_redirect(admin_url('admin.php?page=etsy-export-feed-profiling'));
            }
            break;
        case 'delete':
            if ($id) {
                $invoker->delete($id);
            } else {
                $_SESSION['etcpf_profile_message'] = __('profile id was not provided');
            }
            break;
        default:
            $invoker->list_profiles();
    }
}

function etcpf_listing_report()
{
    include_once('core/classes/etsy-upload.php');
    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $obj = new ETCPF_EtsyUpload();
        $obj->listing_report($id);
        exit;
    } else {
        echo "<pre>";
        print_r("No Feed Selected");
        echo "</pre>";
        exit();
    }
}
