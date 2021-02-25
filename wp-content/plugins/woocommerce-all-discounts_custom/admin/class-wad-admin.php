<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.orionorigin.com/
 * @since      0.1
 *
 * @package    Wad
 * @subpackage Wad/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wad
 * @subpackage Wad/admin
 * @author     ORION <support@orionorigin.com>
 */
class Wad_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wad_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wad_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wad-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( "acd-flexgrid", plugin_dir_url( __FILE__ ) . 'css/flexiblegs.css', array(), $this->version, 'all' );
//                wp_enqueue_style( "acd-tooltip", plugin_dir_url( __FILE__ ) . 'css/tooltip.css', array(), $this->version, 'all' );
        wp_enqueue_style( "o-ui", plugin_dir_url( __FILE__ ) . 'css/UI.css', array(), $this->version, 'all' );
        wp_enqueue_style( "o-datepciker", plugin_dir_url( __FILE__ ) . 'js/datepicker/css/datepicker.css', array(), $this->version, 'all' );
        wp_enqueue_style( "wad-datetimepicker", plugin_dir_url( __FILE__ ) . 'js/datetimepicker/jquery.datetimepicker.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wad-admin.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( "o-admin", plugin_dir_url( __FILE__ ) . 'js/o-admin.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( "wad-tabs", plugin_dir_url( __FILE__ ) . 'js/SpryAssets/SpryTabbedPanels.js', array( 'jquery' ), $this->version, false );
//        wp_enqueue_script("acd-accordion", plugin_dir_url(__FILE__) . 'js/SpryAssets/SpryAccordion.js', array('jquery'), $this->version, false);
        wp_enqueue_script( "wad-serializejson", plugin_dir_url( __FILE__ ) . 'js/jquery.serializejson.min.js', array( 'jquery' ), $this->version, false );
//        wp_enqueue_script("o-datepicker", plugin_dir_url(__FILE__) . 'js/datepicker/js/datepicker.js', array('jquery'), $this->version, false);
//        wp_enqueue_script("o-datepicker-eye", plugin_dir_url(__FILE__) . 'js/datepicker/js/eye.js', array('jquery'), $this->version, false);
//        wp_enqueue_script("o-datepicker-util", plugin_dir_url(__FILE__) . 'js/datepicker/js/util.js', array('jquery'), $this->version, false);
//        wp_enqueue_script("o-datepicker-layout", plugin_dir_url(__FILE__) . 'js/datepicker/js/layout.js', array('jquery'), $this->version, false);
        wp_enqueue_script( "wad-datetimepicker", plugin_dir_url( __FILE__ ) . 'js/datetimepicker/build/jquery.datetimepicker.full.min.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'license_message', array( 'purchase_code_used_on_different_site' => __("This purchase code has been used on another website. If you want to use it anyway, please go to the your account page on https://discountsuiteforwp.com/my-account to cancel your previous activation.") ) );
    }

    /*
     * disable acf timepicker script as needed
     */
    function acf_pro_dequeue_script(){
        if(class_exists('acf') && is_admin())
            if(strpos($_SERVER['REQUEST_URI'], '?post_type=o-discount') || (isset($_GET['post']) && get_post_type($_GET['post']) =='o-discount'))
                    wp_dequeue_script( 'acf-timepicker' );
    }

    public function get_max_input_vars_php_ini()
    {
        $total_max_normal = ini_get('max_input_vars');
        $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", "vpc");
        ?>
        <script type="text/javascript">
            var o_max_input_vars = <?php echo $total_max_normal; ?>;
            var o_max_input_msg = "<?php echo $msg; ?>";
        </script>
        <?php
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function add_wad_menu() {
        $parent_slug = "edit.php?post_type=o-discount";
        add_submenu_page( $parent_slug, __( 'Products Lists', 'wad' ), __( 'Products Lists', 'wad' ), 'manage_product_terms', 'edit.php?post_type=o-list', false );
        add_submenu_page( $parent_slug, __( 'Settings', 'wad' ), __( 'Settings', 'wad' ), 'manage_product_terms', 'wad-manage-settings', array( $this, 'get_wad_settings_page' ) );
        add_submenu_page($parent_slug, __('Get Started', 'wad'), __('Get Started', 'wad'), 'manage_product_terms', 'wad-about', array($this, "get_about_page"));
        add_submenu_page($parent_slug, __('User Manual', 'wad' ), __( 'User Manual', 'wad' ), 'manage_product_terms', 'wad-user-manual', array($this, "redirect_to_user_manual"));
        add_submenu_page($parent_slug, __('Submit a ticket', 'wad' ), __( 'Submit a ticket', 'wad' ), 'manage_product_terms', 'wad-submit-a-ticket', array($this, "redirect_to_support"));
    }


    /**
     * Redirect to the documentation of the plugin.
     *
     * @return void
     */
    public function redirect_to_user_manual(){
        wp_redirect( 'https://discountsuiteforwp.com/tutorials/how-to-write-your-first-discount/?utm_source=WAD%20free&utm_medium=user%20manual%20submenu&utm_campaign=wordpress.org' );
        exit();
    }

    /**
     * Redirect to the ticket support for send a issue.
     *
     * @return void
     */
    public function redirect_to_support(){
        wp_redirect( 'https://discountsuiteforwp.com/contact-us/?utm_source=WAD%20free&utm_medium=get%support%20submenu&utm_campaign=wordpress.org');
        exit();
    }


    public function get_wad_settings_page() {
        if ((isset( $_POST[ "wad-options" ] ) && !empty( $_POST[ "wad-options" ] ))) {
            //$_POST[ "wad-options" ][ "social-desc" ] = stripslashes( wp_filter_post_kses( addslashes( $_POST[ "wad-options" ][ "social-desc" ] ) ) );
            update_option( "wad-options", $_POST[ "wad-options" ] );
            //echo stripslashes(wp_filter_post_kses(addslashes($_POST["wad-options"]["social-desc"])));
        }
        ?>
        <div class="o-wrap cf">
            <h1 style="font-size: 23px; text-transform: uppercase; margin: 1em 0;"><?php _e( "Woocommerce All Discounts Settings", "wad" ); ?></h1>
            <form method="POST" action="" class="mg-top">
                <div class="postbox" id="wad-options-container">
                    <?php
                    $begin = array(
                        'type' => 'sectionbegin',
                        'id' => 'wad-datasource-container',
                        'table' => 'options',
                    );

                    $mailchimp_api_key_admin = array(
                        'title' => __( 'Mailchimp API KEY', 'wad' ),
                        'name' => 'wad-options[mailchimp-api-key]',
                        'type' => 'text',
                        'desc' => __( 'Used when a MailChimp based discount need to be set. <a href="http://kb.mailchimp.com/accounts/management/about-api-keys" target="blank">How to find my API Key?</a>', 'wad' ),
                        'default' => '',
                    );
                    $sendinblue_api_key_admin = array(
                        'title' => __( 'SendinBlue API KEY', 'wad' ),
                        'name' => 'wad-options[sendinblue-api-key]',
                        'type' => 'text',
                        'desc' => __( 'Used when a SendinBlue based discount need to be set.<a href="https://my.sendinblue.com/advanced/apikey" target="blank">How to find my API Key?</a>', 'wad' ),
                        'default' => '',
                    );

                    $license_key = array(
                        'title' => __( 'Licence key', 'wad' ),
                        'name' => 'wad-options[purchase-code]',
                        'type' => 'text',
                        'default' => '',
                        'desc' => 'Licence key received after your purchase. <a href="https://discountsuiteforwp.com/my-account/orders/" target="blank">Where is my licence key</a>?'
                    );

                    $include_taxes = array(
                        'title' => __( 'Include shipping in taxes', 'wad' ),
                        'name' => 'wad-options[inc-shipping-in-taxes]',
                        'type' => 'select',
                        'options' => array( 'No' => "No", 'Yes' => "Yes" ),
                        'desc' => __( 'Wether or not to consider shipping as part of taxes', 'wad' ),
                        'default' => 'Yes',
                    );

                    $disable_coupons = array(
                        'title' => __( 'Disable coupons', 'wad' ),
                        'name' => 'wad-options[disable-coupons]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'whether or not to disable the coupons usage when a cart discount is active.', 'wad' ),
                        'default' => '',
                    );
                    $display_cart_discounts_individually = array(
                        'title' => __( 'Display cart discounts individually', 'wad' ),
                        'name' => 'wad-options[individual-cart-discounts]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'whether or not to display each cart discount individually on cart pages.', 'wad' ),
                        'default' => 1,
                    );
                    $display_original_pprice = array(
                        'title' => __( 'Display each discounted product original price crossed on the cart page', 'wad' ),
                        'name' => 'wad-options[original-price]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'whether or not to display each discounted product original price crossed on the cart page', 'wad' ),
                        'default' => 1,
                    );
                    $display_products_discounts = array(
                        'title' => __( 'Display the total products discounts', 'wad' ),
                        'name' => 'wad-options[discount-on-products]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'whether or not to display total discounts on products below the cart subtotal.', 'wad' ),
                        'default' => 0,
                    );
                    $add_gift_automatically = array(
                        'title' => __( 'Automatically add free gifts to the cart', 'wad' ),
                        'name' => 'wad-options[free-gift-auto]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'Wether or not to automatically add the free gift to the cart if there is only one product in the gifts list.', 'wad' ),
                        'default' => 1,
                    );
                    $completed_order_statuses = array(
                        'title' => __( 'Completed Orders Statuses', 'wad' ),
                        'name' => 'wad-options[completed-order-statuses]',
                        'type' => 'multiselect',
                        'options' => wc_get_order_statuses(),
                        'desc' => __( 'List of order statuses considered as completed (used when manipulating previous orders in the discounts conditions).', 'wad' ),
                        'default' => '',
                    );
                    /*
                    $fast_extraction_algorithm = array(
                        'title' => __( 'Use new extraction algorithm', 'wad' ),
                        'name' => 'wad-options[new-extraction-algorithm]',
                        'type' => 'select',
                        'options' => array( 0 => "No", 1 => "Yes" ),
                        'desc' => __( 'Use new extraction algorithm recommanded for slow websites.', 'wad' ),
                        'default' => 1,
                    );
                    */
                    $end = array( 'type' => 'sectionend' );
                    $settings = apply_filters('wad_get_settings_fields',array(
                        $begin,
                        $license_key,
                        $mailchimp_api_key_admin,
                        $sendinblue_api_key_admin,
                        $disable_coupons,
                        $display_cart_discounts_individually,
                        $display_original_pprice,
                        $display_products_discounts,
                        $add_gift_automatically,
                        $include_taxes,
                        $completed_order_statuses,
                        //$fast_extraction_algorithm,
                        $end
                    ));
                    echo o_admin_fields( $settings );
                    ?>
                </div>
                <input type="submit" class="button button-primary button-large" value="<?php _e( "Save", "wad" ); ?>">
            </form>
        </div>
        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode( $o_row_templates ); ?>;
        </script>
        <?php
    }

    /**
     * Builds the about page
     */
    function get_about_page() {
        ?>
        <div id='wad-about-page'>
            <div class="wrap">
                <div id="features-wrap">
                    <h2 class="feature-h2"><?php _e('Getting Started', 'wpd'); ?></h2>
                    <div class="list-posts-content">
                      <div class="o-wrap o-features xl-gutter-8">
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-26.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A PRODUCTS LIST?', 'wad'); ?></h3>
                              <p><?php _e('A product list is a subset of your shop’s products that you can target in the discount conditions or the products on which they apply while setting up a dynamic...', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/create-products-list-using-woocommerce-discounts/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20product%20list%20using%20woocommerce%20all%20discounts" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-01.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE DISCOUNT BASED ON THE CUSTOMER EMAIL DOMAIN?', 'wad'); ?></h3>
                              <p><?php _e('Email addresses from a company are grouped under the same email domain and can be easily used to target a group of customers for multiple purposes.', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/create-woocommerce-discount-based-customer-email-domain/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20discount%20on%20customer%20email%20domain" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-02.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE DISCOUNT ON A PRODUCT VARIATION?', 'wad'); ?></h3>
                              <p><?php _e('Variable products are a product type in WooCommerce that lets you offer a set of variations on a product, with control over prices, stock, image and more for ...', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/woocommerce-variable-pricing-product-variations/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20discount%20on%20a%20product%20variation" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-07.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE DISCOUNT PER PAYMENT METHOD?', 'wad'); ?></h3>
                              <p><?php _e('A payment gateway is a service that allows a customer to pay for his order using credit cards or direct payments methods. While there are hundreds of payment methods...', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/create-woocommerce-discount-per-payment-method/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20discount%20per%20payment%20method" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-08.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE DISCOUNT ON THE FIRST ORDER OR THE NTH ORDER?', 'wad'); ?></h3>
                              <p><?php _e('A discount on the first order is the most effective way to convert a first time visitor into a paying customer. That conversion is the first and hardest step into...', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/create-woocommerce-discount-first-order-nth-order/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20discount%20on%20the%20nth%20order" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-24.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE BULK DISCOUNT PER CUSTOMER ROLE OR GROUP?', 'wad'); ?></h3>
                              <p><?php _e('Bulk discounts per customer are particularly useful if you have different types of customers or different pricing strategies to apply.', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/create-woocommerce-bulk-discount-per-customer-role-group/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20bulk%20discount%20per%20customer%20role%20or%20group" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>
                          <div class="o-col col xl-1-2">
                              <img class="vc_single_image-img " src="<?php echo WAD_URL."admin/images/features/"?>WAD-Icons-25.svg">
                              <h3 class="wad-title"><?php _e('HOW TO CREATE A WOOCOMMERCE BULK DISCOUNT PER PRODUCT CATEGORY?', 'wad'); ?></h3>
                              <p><?php _e('Bulk discounts are one of the most popular deals mainly used to increase the average order size on online stores. Despite WooCommerce being one of the most...', 'wpd'); ?></p>
                              <a href="https://discountsuiteforwp.com/tutorials/woocommerce-bulk-discount-per-category/?utm_source=WAD%20Pro&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_term=how%20to%20create%20a%20bulk%20discount%20per%20product%20category" class="button" target="_blank"><?php _e('Learn More', 'wad'); ?></a>
                          </div>

                      </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    /*
     *
     * Newsletter
     */
    function wad_subscribe(){
        $email = $_POST['email'];

        if (preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $email)) {
            $url = "https://discountsuiteforwp.com/service/osubscribe/v1/subscribe/?email=" . $email;
            $args=array('timeout' => 120);
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
                die();
            }
            if (isset($response["body"])) {
                $answer = $response["body"];
                if($answer == "true" ){
                    update_option('o-wad-subscribe', "subscribed");
                    echo $answer;
                }else{
                    echo $answer;
                }

                die();
            }
        } else {
            echo 'Please enter a valid email address';
            die();
        }
    }

      function wad_get_subscription_notice() {
        if ( !get_option('o-wad-subscribe') && get_transient("wad-hide-notice") != "hide" ) {
            ?>
            <div id="subscription-notice" class="notice notice-info">

                <div id="plug-logo-text" >
                    <img id="plug-logo" style="height:50px; width: 50px"src="<?php echo WAD_URL; ?>/admin/images/WAD-logo.svg">
                    <p>
                        <?php _e('<strong>Woocommerce All Discounts</strong>: Sign up now to receive new releases notices and important bugs fixes directly<br> into your inbox! ', 'wad'); ?>
                    </p>

                </div>

                <div id="plug-sucribe-form">
                    <input type="email" id="o_user_email" name="usermail" placeholder="Your email here">
                    <img id="wad-subscribe-loader" style="display:none;" src="<?php echo WAD_URL; ?>/admin/images/loader.gif" >
                    <button id="wad-subscribe" class="button button-primary"><?php _e("Subscribe", "wad"); ?></button>
                    <a id="wad-dismiss"><?php _e("Not now", "wad"); ?></a>
                </div>
            </div>
            <?php
        }

        ?>
        <div id="subscription-success-notice" class="notice notice-info is-dismissible" style="display:none;">
                <img src="<?php echo WAD_URL; ?>/admin/images/WAD-logo.svg">
                <div> <?php _e('<strong>Woocommerce All Discounts</strong>: Thank you for subscribing! ', 'wad'); ?></div>
        </div>
        <?php
    }

    function wad_hide_notice(){
        set_transient('wad-hide-notice', "hide", 2*WEEK_IN_SECONDS);
        echo 'ok';
            die();
    }



    /**
     * Runs the new version check and upgrade process
     * @return \WAD_Updater
     */
    function get_updater() {
//        do_action('wad_before_init_updater');
        require_once( WAD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'updaters' . DIRECTORY_SEPARATOR . 'class-wad-updater.php' );
        $updater = new WAD_Updater();
        $updater->init();
        require_once( WAD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'updaters' . DIRECTORY_SEPARATOR . 'class-wad-updating-manager.php' );
        $updater->setUpdateManager( new WAD_Updating_Manager( WAD_VERSION, $updater->versionUrl(), WAD_MAIN_FILE ) );
//        do_action('wad_after_init_updater');
        return $updater;
    }

    function get_license_activation_notice() {
        $wad_settings = get_option('wad-options');
        //if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
        if(empty($wad_settings["purchase-code"])){
            ?>
            <div class="notice notice-error">
                <p><b>Woocommerce All Discounts: </b><?php _e( "No licence key found in the settings. Please click <a href='edit.php?post_type=o-discount&page=wad-manage-settings'>here</a> to define one.", 'wad' ); ?></p>
                <p></p>
            </div>
            <?php
            return;
        }

        if (!get_option( 'wad-license-key' )) {
            ?>
            <div class="notice notice-error">
                <p><b>Woocommerce All Discounts: </b><?php _e( 'You have not activated your license yet. Please activate it in order to get the plugin working.', 'wad' ); ?></p>
                    <a class="button" id="wad-activate"><?php _e( "Activate", "wad" ); ?></a><img style="display:none; margin-top: 3px; width: 22px; height: 22px;" id="spinner" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/spinner.gif'; ?> ">
                <p></p>
                <div id="license-message"></div>
            </div>
            <?php
        }
        //}
    }

    function activate_license() {
        global $wad_settings;
        if (isset( $wad_settings[ 'purchase-code' ] )  && $wad_settings['purchase-code'] != "" ) {
            $purchase_code = $wad_settings[ 'purchase-code' ];
            $site_url = get_site_url();
            $code = $_POST[ 'code' ];
            $plugin_name = WAD_PLUGIN_NAME;
            $url = "https://discountsuiteforwp.com/service/olicenses/v1/license/?purchase-code=" . $purchase_code . "&siteurl=" . urlencode( $site_url ) . "&subscription=1";
            $args=array('timeout' => 60,);
            $response = wp_remote_get( $url, $args);
            if (is_wp_error( $response )) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
                die();
            }
            if (isset( $response[ "body" ] )) {
                $answer = $response[ "body" ];
            }

            if (is_array( json_decode( $answer, true ) )) {
                $data = json_decode( $answer, true );
                $key = $data[ 'key' ];
                update_option("wad-license-key", $key);
                set_transient('wad-license-checking', 'valid', 1*WEEK_IN_SECONDS);
                echo "200";
            } else {
                echo $answer;
            }
        } else {
            echo ("Purchase code not found. Please, set your purchase code in the plugin's settings. ");
        }


        die();
    }

    function o_verify_validity() {
        $wad_settings = get_option('wad-options');
        if(get_transient("wad-license-checking") !== 'valid'){
            if ( isset($wad_settings[ 'purchase-code' ]) && $wad_settings['purchase-code'] != "" ) {
                $purchase_code = $wad_settings[ 'purchase-code' ];
                $site_url = get_site_url();
                $plugin_name = WAD_PLUGIN_NAME;
                $url = "https://discountsuiteforwp.com/service/olicenses/v1/checking/?license-key=" . $purchase_code . "&siteurl=" . urlencode( $site_url ) . "&subscription=1";
                $args=array('timeout' => 60);
                $response = wp_remote_get( $url, $args);
                if (!is_wp_error( $response )) {
                    if (isset( $response[ "body" ] ) && intval($response[ "body" ] ) == 403) {
                        delete_option( "wad-license-key" );
                    }
                }
            } else {
                if (get_option( "wad-license-key" )) {
                    delete_option( "wad-license-key" );
                }
            }
            set_transient('wad-license-checking', 'valid', 1*WEEK_IN_SECONDS);
        }
    }

    /**
     * Redirects the plugin to the getting page after the activation
     */
    function wad_redirect() {
        if (get_option('wad_do_activation_redirect', false)) {
            delete_option('wad_do_activation_redirect');
            wp_redirect(admin_url('edit.php?post_type=o-discount&page=wad-about'));
        }
    }

    /**
     * Checking if product list is define.
     */
    function check_product_list(){
        $product_lists = new WAD_Products_List(false);
        $product_lists_counts = $product_lists->get_all();
        global $post_type,$pagenow;
        if ('o-discount' == $post_type || 'o-list' == $post_type || ( 'edit.php' == $pagenow || ( isset($_GET["page"] ) && $_GET["page"]!="wad-pro-features"))){
             if (isset($product_lists_counts) && empty($product_lists_counts)){
                 $url = admin_url( 'post-new.php?post_type=o-list');
                 $html = "<a href='".$url."'>here</a>";
                 ?>
                     <div class="wad notice notice-error">
                         <p>
                     <?php
                       _e( "You haven't created a products list. You need one in order to apply a discount on multiple products. You can create one $html .", 'wad' );
                     ?>
                         </p>
                     </div>
                 <?php
}
         }

    }
}
