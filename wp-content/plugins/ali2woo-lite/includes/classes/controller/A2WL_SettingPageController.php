<?php

/**
 * Description of A2WL_SettingPage
 *
 * @author Andrey
 * 
 * @autoload: a2wl_init 
 */
if (!class_exists('A2WL_SettingPageController')) {


    class A2WL_SettingPageController extends A2WL_AbstractAdminPage {

        private $product_import_model;
        private $woocommerce_model;
        private $localizator;

        public function __construct() {
            parent::__construct(__('Settings', 'ali2woo-lite'), __('Settings', 'ali2woo-lite'), 'import', 'a2wl_setting', 30);

            $this->product_import_model = new A2WL_ProductImport();
            $this->woocommerce_model = new A2WL_Woocommerce();
            $this->localizator = A2WL_AliexpressLocalizator::getInstance();

            add_action('wp_ajax_a2wl_update_price_rules', array($this, 'ajax_update_price_rules'));

            add_action('wp_ajax_a2wl_apply_pricing_rules', array($this, 'ajax_apply_pricing_rules'));

            add_action('wp_ajax_a2wl_update_phrase_rules', array($this, 'ajax_update_phrase_rules'));

            add_action('wp_ajax_a2wl_apply_phrase_rules', array($this, 'ajax_apply_phrase_rules'));

            add_action('wp_ajax_a2wl_get_status_apply_phrase_rules', array($this, 'ajax_get_status_apply_phrase_rules'));

            add_action('wp_ajax_a2wl_reset_shipping_meta', array($this, 'ajax_reset_shipping_meta'));

            add_action('wp_ajax_a2wl_calc_external_images_count', array($this, 'ajax_calc_external_images_count'));
            add_action('wp_ajax_a2wl_calc_external_images', array($this, 'ajax_calc_external_images'));
            add_action('wp_ajax_a2wl_load_external_image', array($this, 'ajax_load_external_image'));

            add_filter('a2wl_setting_view', array($this, 'setting_view'));

            add_filter('a2wl_configure_lang_data', array($this, 'configure_lang_data'));

        }

        function configure_lang_data($lang_data) {
            if ($this->is_current_page()) {
                $lang_data = array(
                    'process_loading_d_of_d_erros_d' => _x('Process loading %d of %d. Errors: %d.', 'Status', 'ali2woo-lite'),
                    'load_button_text' => _x('Load %d images', 'Status', 'ali2woo-lite'),
                    'all_images_loaded_text' => _x('All images loaded', 'Status', 'ali2woo-lite'),
                );
            }
            return $lang_data;
        }

        public function render($params = array()) {
            $current_module = isset($_REQUEST['subpage']) ? $_REQUEST['subpage'] : 'common';

            $this->model_put("modules", $this->getModules());
            $this->model_put("current_module", $current_module);

            $this->include_view(array("settings/settings_head.php", apply_filters('a2wl_setting_view', $current_module), "settings/settings_footer.php"));
        }

        public function getModules() {
            return apply_filters('a2wl_setting_modules', array(
                array('id' => 'common', 'name' => __('Common settings', 'ali2woo-lite')),
                array('id' => 'account', 'name' => __('Account settings', 'ali2woo-lite')),
                array('id' => 'price_formula', 'name' => __('Pricing Rules', 'ali2woo-lite')),
                array('id' => 'reviews', 'name' => __('Reviews settings', 'ali2woo-lite')),
                array('id' => 'shipping', 'name' => __('Shipping settings', 'ali2woo-lite')),
                array('id' => 'phrase_filter', 'name' => __('Phrase Filtering', 'ali2woo-lite')),
                array('id' => 'chrome_api', 'name' => __('API Keys', 'ali2woo-lite')),
                array('id' => 'system_info', 'name' => __('System Info', 'ali2woo-lite')),
            ));
        }

        public function setting_view($current_module) {
            $view = "";
            switch ($current_module) {
                case 'common':
                    $view = $this->common_handle();
                    break;
                case 'account':
                    $view = $this->account_handle();
                    break;
                case 'price_formula':
                    $view = $this->price_formula();
                    break;
                case 'reviews':
                    $view = $this->reviews();
                    break;
                case 'shipping':
                    $view = $this->shipping();
                    break;
                case 'phrase_filter':
                    $view = $this->phrase_filter();
                    break;
                case 'chrome_api':
                    $view = $this->chrome_api();
                    break;
                case 'system_info':
                    $view = $this->system_info();
                    break;
            }
            return $view;
        }

        private function common_handle() {
            global $a2wl_settings;
            if (isset($_POST['setting_form'])) {
                a2wl_settings()->auto_commit(false);
                a2wl_set_setting('item_purchase_code', isset($_POST['a2wl_item_purchase_code']) ? wp_unslash($_POST['a2wl_item_purchase_code']) : '');

                a2wl_set_setting('import_language', isset($_POST['a2w_import_language']) ? wp_unslash($_POST['a2w_import_language']) : 'en');
                a2wl_set_setting('local_currency', isset($_POST['a2w_local_currency']) ? wp_unslash($_POST['a2w_local_currency']) : 'USD');
                a2wl_set_setting('default_product_type', isset($_POST['a2wl_default_product_type']) ? wp_unslash($_POST['a2wl_default_product_type']) : 'simple');
                a2wl_set_setting('default_product_status', isset($_POST['a2wl_default_product_status']) ? wp_unslash($_POST['a2wl_default_product_status']) : 'publish');
                a2wl_set_setting('tracking_code_order_status', isset($_POST['a2wl_tracking_code_order_status']) ? wp_unslash($_POST['a2wl_tracking_code_order_status']) : '');

                a2wl_set_setting('placed_order_status', isset($_POST['a2wl_placed_order_status']) ? wp_unslash($_POST['a2wl_placed_order_status']) : '');

                a2wl_set_setting('currency_conversion_factor', isset($_POST['a2wl_currency_conversion_factor']) ? wp_unslash($_POST['a2wl_currency_conversion_factor']) : '1');
                a2wl_set_setting('import_product_images_limit', isset($_POST['a2wl_import_product_images_limit']) && intval($_POST['a2wl_import_product_images_limit']) ? intval($_POST['a2wl_import_product_images_limit']) : '');
                a2wl_set_setting('import_extended_attribute', isset($_POST['a2wl_import_extended_attribute']) ? 1 : 0);

                a2wl_set_setting('background_import', isset($_POST['a2wl_background_import']) ? 1 : 0);

                a2wl_set_setting('use_external_image_urls', isset($_POST['a2wl_use_external_image_urls']));
                a2wl_set_setting('use_cdn', isset($_POST['a2wl_use_cdn']));
                a2wl_set_setting('not_import_attributes', isset($_POST['a2wl_not_import_attributes']));
                a2wl_set_setting('not_import_description', isset($_POST['a2wl_not_import_description']));
                a2wl_set_setting('not_import_description_images', isset($_POST['a2wl_not_import_description_images']));

                a2wl_set_setting('use_random_stock', isset($_POST['a2wl_use_random_stock']));
                if (isset($_POST['a2wl_use_random_stock'])) {
                    $min_stock = (!empty($_POST['a2wl_use_random_stock_min']) && intval($_POST['a2wl_use_random_stock_min']) > 0) ? intval($_POST['a2wl_use_random_stock_min']) : 1;
                    $max_stock = (!empty($_POST['a2wl_use_random_stock_max']) && intval($_POST['a2wl_use_random_stock_max']) > 0) ? intval($_POST['a2wl_use_random_stock_max']) : 1;

                    if ($min_stock > $max_stock) {
                        $min_stock = $min_stock + $max_stock;
                        $max_stock = $min_stock - $max_stock;
                        $min_stock = $min_stock - $max_stock;
                    }
                    a2wl_set_setting('use_random_stock_min', $min_stock);
                    a2wl_set_setting('use_random_stock_max', $max_stock);
                }

                a2wl_set_setting('auto_update', isset($_POST['a2wl_auto_update']));
                a2wl_set_setting('on_not_available_product', isset($_POST['a2wl_on_not_available_product']) ? wp_unslash($_POST['a2wl_on_not_available_product']) : 'trash');
                a2wl_set_setting('on_not_available_variation', isset($_POST['a2wl_on_not_available_variation']) ? wp_unslash($_POST['a2wl_on_not_available_variation']) : 'trash');
                a2wl_set_setting('on_new_variation_appearance', isset($_POST['a2wl_on_new_variation_appearance']) ? wp_unslash($_POST['a2wl_on_new_variation_appearance']) : 'add');
                a2wl_set_setting('on_price_changes', isset($_POST['a2wl_on_price_changes']) ? wp_unslash($_POST['a2wl_on_price_changes']) : 'update');
                a2wl_set_setting('on_stock_changes', isset($_POST['a2wl_on_stock_changes']) ? wp_unslash($_POST['a2wl_on_stock_changes']) : 'update');
                a2wl_set_setting('email_alerts', isset($_POST['a2wl_email_alerts']));
                a2wl_set_setting('email_alerts_email', isset($_POST['a2wl_email_alerts_email']) ? wp_unslash($_POST['a2wl_email_alerts_email']) : '');
                
                
                a2wl_set_setting('fulfillment_prefship', isset($_POST['a2w_fulfillment_prefship']) ? wp_unslash($_POST['a2w_fulfillment_prefship']) : 'ePacket');
                a2wl_set_setting('fulfillment_phone_code', isset($_POST['a2wl_fulfillment_phone_code']) ? wp_unslash($_POST['a2wl_fulfillment_phone_code']) : '');
                a2wl_set_setting('fulfillment_phone_number', isset($_POST['a2wl_fulfillment_phone_number']) ? wp_unslash($_POST['a2wl_fulfillment_phone_number']) : '');
                a2wl_set_setting('fulfillment_custom_note', isset($_POST['a2wl_fulfillment_custom_note']) ? wp_unslash($_POST['a2wl_fulfillment_custom_note']) : '');

                a2wl_set_setting('order_translitirate', isset($_POST['a2wl_order_translitirate']));
                a2wl_set_setting('order_third_name', isset($_POST['a2wl_order_third_name']));
                a2wl_set_setting('order_autopay', $_POST['a2wl_order_awaiting_payment'] === "no");
                a2wl_set_setting('order_awaiting_payment', $_POST['a2wl_order_awaiting_payment'] === "yes");

                a2wl_settings()->commit();
                a2wl_settings()->auto_commit(true);
            }

            $this->model_put("currencies", $this->localizator->getCurrencies(false));
            $this->model_put("custom_currencies", $this->localizator->getCurrencies(true));
            $this->model_put("order_statuses", function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : array());

            return "settings/common.php";
        }

        private function account_handle() {
            $account = A2WL_Account::getInstance();

            if (isset($_POST['setting_form'])) {
                $account->set_account_type(isset($_POST['a2wl_account_type']) && in_array($_POST['a2wl_account_type'], array('aliexpress', 'admitad', 'epn')) ? $_POST['a2wl_account_type'] : 'aliexpress');
                $account->use_custom_account(isset($_POST['a2wl_use_custom_account']));
                if ($account->custom_account && isset($_POST['a2wl_account_type'])) {
                    if ($_POST['a2wl_account_type'] == 'aliexpress') {
                        $account->save_aliexpress_account(isset($_POST['a2wl_appkey']) ? $_POST['a2wl_appkey'] : '', isset($_POST['a2wl_secretkey']) ? $_POST['a2wl_secretkey'] : '', isset($_POST['a2wl_trackingid']) ? $_POST['a2wl_trackingid'] : '');
                    } else if ($_POST['a2wl_account_type'] == 'admitad') {
                        $account->save_admitad_account(isset($_POST['a2wl_admitad_cashback_url']) ? $_POST['a2wl_admitad_cashback_url'] : '');
                    } else if ($_POST['a2wl_account_type'] == 'epn') {
                        $account->save_epn_account(isset($_POST['a2wl_epn_cashback_url']) ? $_POST['a2wl_epn_cashback_url'] : '');
                    }
                }
            }

            $this->model_put("account", $account);

            return "settings/account.php";
        }

        private function price_formula() {
            $formulas = A2WL_PriceFormula::load_formulas();

            if ($formulas) {
                $add_formula = new A2WL_PriceFormula();
                $add_formula->min_price = floatval($formulas[count($formulas) - 1]->max_price) + 0.01;
                $formulas[] = $add_formula;
                $this->model_put("formulas", $formulas);
            } else {
                $this->model_put("formulas", A2WL_PriceFormula::get_default_formulas());
            }

            $this->model_put("pricing_rules_types", A2WL_PriceFormula::pricing_rules_types());

            $this->model_put("default_formula", A2WL_PriceFormula::get_default_formula());

            $this->model_put('cents', a2wl_get_setting('price_cents'));
            $this->model_put('compared_cents', a2wl_get_setting('price_compared_cents'));

            return "settings/price_formula.php";
        }

        private function reviews() {
            if (isset($_POST['setting_form'])) {
                a2wl_settings()->auto_commit(false);
                a2wl_set_setting('load_review', isset($_POST['a2wl_load_review']));
                a2wl_set_setting('review_status', isset($_POST['a2wl_review_status']));
                a2wl_set_setting('review_translated', isset($_POST['a2wl_review_translated']));
                a2wl_set_setting('review_avatar_import', isset($_POST['a2wl_review_avatar_import']));

                a2wl_set_setting('review_schedule_load_period', 'a2wl_15_mins');

                a2wl_set_setting('review_max_per_product', isset($_POST['a2wl_review_max_per_product']) ? wp_unslash($_POST['a2wl_review_max_per_product']) : '');

                //todo:
                if (isset($_POST['a2wl_review_allow_country'])) {
                    $value = trim($_POST['a2wl_review_allow_country']);
                    if (!empty($value)) {
                        $value = str_replace(" ", "", $_POST['a2wl_review_allow_country']);
                        $value = strtoupper($value);
                    }

                    a2wl_set_setting('review_allow_country', $value);
                }

                //raiting fields
                $raiting_from = 1;
                $raiting_to = 5;
                if (isset($_POST['a2wl_review_raiting_from']))
                    $raiting_from = intval($_POST['a2wl_review_raiting_from']);

                if (isset($_POST['a2wl_review_raiting_to']))
                    $raiting_to = intval($_POST['a2wl_review_raiting_to']);

                if ($raiting_from >= 5)
                    $raiting_from = 5;
                if ($raiting_from < 1 || $raiting_from > $raiting_to)
                    $raiting_from = 1;

                if ($raiting_to >= 5)
                    $raiting_to = 5;
                if ($raiting_to < 1)
                    $raiting_to = 1;

                a2wl_set_setting('review_raiting_from', $raiting_from);
                a2wl_set_setting('review_raiting_to', $raiting_to);


                //update more field
                a2wl_set_setting('review_load_attributes', isset($_POST['a2wl_review_load_attributes']));
                a2wl_set_setting('review_show_image_list', isset($_POST['a2wl_review_show_image_list']));
                a2wl_set_setting('moderation_reviews', isset($_POST['a2wl_moderation_reviews']));

                if (isset($_FILES) && isset($_FILES['a2wl_review_noavatar_photo']) && 0 === $_FILES['a2wl_review_noavatar_photo']['error']) {

                    if (!function_exists('wp_handle_upload'))
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );

                    $uploadedfile = $_FILES['a2wl_review_noavatar_photo'];
                    $upload_overrides = array('test_form' => false);
                    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                    if ($movefile) {
                        a2wl_set_setting('review_noavatar_photo', $movefile['url']);
                    } else {
                        echo "Possible file upload attack!\n";
                    }
                } else {
                    a2wl_del_setting('review_noavatar_photo');
                }

                a2wl_settings()->commit();
                a2wl_settings()->auto_commit(true);
            }
            return "settings/reviews.php";
        }

        private function shipping() {
            if (isset($_POST['setting_form'])) {

                a2wl_set_setting('aliship_shipto', isset($_POST['a2w_aliship_shipto']) ? wp_unslash($_POST['a2w_aliship_shipto']) : 'US');
                a2wl_set_setting('aliship_frontend', isset($_POST['a2wl_aliship_frontend']));
                a2wl_set_setting('default_shipping_class', !empty($_POST['a2wl_default_shipping_class']) ? $_POST['a2wl_default_shipping_class'] : false);

                if (isset($_POST['a2wl_aliship_frontend']) && isset($_POST['default_rule'])) {
                    A2WL_ShippingPriceFormula::set_default_formula(new A2WL_ShippingPriceFormula($_POST['default_rule']));
                }
            }

            $countryModel = new A2WL_Country();

            $this->model_put("shipping_countries", $countryModel->get_countries());

            $this->model_put("default_formula", A2WL_ShippingPriceFormula::get_default_formula());

            $shipping_class = get_terms(array('taxonomy' => 'product_shipping_class', 'hide_empty' => false));
            $this->model_put("shipping_class", $shipping_class ? $shipping_class : array());

            return "settings/shipping.php";
        }

        private function phrase_filter() {
            $phrases = A2WL_PhraseFilter::load_phrases();

            if ($phrases) {
                $this->model_put("phrases", $phrases);
            } else {
                $this->model_put("phrases", array());
            }

            return "settings/phrase_filter.php";
        }

        private function chrome_api() {
            $api_keys = a2wl_get_setting('api_keys', array());
            
            if (!empty($_REQUEST['delete-key'])) {
                foreach ($api_keys as $k => $key) {
                    if ($key['id'] === $_REQUEST['delete-key']) {
                        unset($api_keys[$k]);
                        a2wl_set_setting('api_keys', $api_keys);
                        break;
                    }
                }
                wp_redirect(admin_url('admin.php?page=a2wl_setting&subpage=chrome_api'));
            } else if (!empty($_POST['a2wl_api_key'])) {
                $key_id = $_POST['a2wl_api_key'];
                $key_name = !empty($_POST['a2wl_api_key_name']) ? $_POST['a2wl_api_key_name'] : "New key";

                $is_new = true;
                foreach ($api_keys as &$key) {
                    if ($key['id'] === $key_id) {
                        $key['name'] = $key_name;
                        $is_new = false;
                        break;
                    }
                }

                if ($is_new) {
                    $api_keys[] = array('id' => $key_id, 'name' => $key_name);
                }

                a2wl_set_setting('api_keys', $api_keys);

                wp_redirect(admin_url('admin.php?page=a2wl_setting&subpage=chrome_api&edit-key=' . $key_id));
            } else if (isset($_REQUEST['edit-key'])) {
                $api_key = array('id' => md5("a2wkey" . rand() . microtime()), 'name' => "New key");
                $is_new = true;
                if (empty($_REQUEST['edit-key'])) {
                    $api_keys[] = $api_key;
                    a2wl_set_setting('api_keys', $api_keys);
                    
                    wp_redirect(admin_url('admin.php?page=a2wl_setting&subpage=chrome_api&edit-key=' . $api_key['id']));
                } else if (!empty($_REQUEST['edit-key']) && $api_keys && is_array($api_keys)) {
                    foreach ($api_keys as $key) {
                        if ($key['id'] === $_REQUEST['edit-key']) {
                            $api_key = $key;
                            $is_new = false;
                        }
                    }
                }
                $this->model_put("api_key", $api_key);
                $this->model_put("is_new_api_key", $is_new);
            }

            $this->model_put("api_keys", $api_keys);

            return "settings/chrome.php";
        }

        private function system_info() {
            if (isset($_POST['setting_form'])) {
                a2wl_set_setting('write_info_log', isset($_POST['a2wl_write_info_log']));
            }

            $server_ip = '-';
            if (array_key_exists('SERVER_ADDR', $_SERVER))
                $server_ip = $_SERVER['SERVER_ADDR'];
            elseif (array_key_exists('LOCAL_ADDR', $_SERVER))
                $server_ip = $_SERVER['LOCAL_ADDR'];
            elseif (array_key_exists('SERVER_NAME', $_SERVER))
                $server_ip = gethostbyname($_SERVER['SERVER_NAME']);
            else {
                // Running CLI
                if (stristr(PHP_OS, 'WIN')) {
                    $server_ip = gethostbyname(php_uname("n"));
                } else {
                    $ifconfig = shell_exec('/sbin/ifconfig eth0');
                    preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
                    $server_ip = $match[1];
                }
            }

            $this->model_put("server_ip", $server_ip);

            return "settings/system_info.php";
        }

        public function ajax_update_phrase_rules() {
            a2wl_init_error_handler();

            $result = A2WL_ResultBuilder::buildOk();
            try {

                A2WL_PhraseFilter::deleteAll();

                if (isset($_POST['phrases'])) {
                    foreach ($_POST['phrases'] as $phrase) {
                        $filter = new A2WL_PhraseFilter($phrase);
                        $filter->save();
                    }
                }

                $result = A2WL_ResultBuilder::buildOk(array('phrases' => A2WL_PhraseFilter::load_phrases()));

                restore_error_handler();
            } catch (Throwable $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            } catch (Exception $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            }

            echo json_encode($result);

            wp_die();
        }

        public function ajax_apply_phrase_rules() {
            a2wl_init_error_handler();

            $result = A2WL_ResultBuilder::buildOk();
            try {

                $type = isset($_POST['type']) ? $_POST['type'] : false;
                $scope = isset($_POST['scope']) ? $_POST['scope'] : false;

                if ($type === 'products' || $type === 'all_types') {
                    if ($scope === 'all' || $scope === 'import') {
                        $products = $this->product_import_model->get_product_list(false);

                        foreach ($products as $product) {

                            $product = A2WL_PhraseFilter::apply_filter_to_product($product);
                            $this->product_import_model->upd_product($product);
                        }
                    }

                    if ($scope === 'all' || $scope === 'shop') {
                        //todo: update attributes as well
                        A2WL_PhraseFilter::apply_filter_to_products();
                    }
                }

                if ($type === 'all_types' || $type === 'reviews') {

                    A2WL_PhraseFilter::apply_filter_to_reviews();
                }

                if ($type === 'all_types' || $type === 'shippings') {
                    
                }
                restore_error_handler();
            } catch (Throwable $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            } catch (Exception $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            }

            echo json_encode($result);

            wp_die();
        }

        public function ajax_update_price_rules() {
            a2wl_init_error_handler();

            $result = A2WL_ResultBuilder::buildOk();
            try {
                a2wl_settings()->auto_commit(false);

                $pricing_rules_types = array_keys(A2WL_PriceFormula::pricing_rules_types());
                a2wl_set_setting('pricing_rules_type', $_POST['pricing_rules_type'] && in_array($_POST['pricing_rules_type'],$pricing_rules_types)?$_POST['pricing_rules_type']:$pricing_rules_types[0]);

                $use_extended_price_markup = isset($_POST['use_extended_price_markup']) ? filter_var($_POST['use_extended_price_markup'], FILTER_VALIDATE_BOOLEAN) : false;
                $use_compared_price_markup = isset($_POST['use_compared_price_markup']) ? filter_var($_POST['use_compared_price_markup'], FILTER_VALIDATE_BOOLEAN) : false;

                a2wl_set_setting('price_cents', isset($_POST['cents']) && intval($_POST['cents']) > -1 && intval($_POST['cents']) <= 99 ? intval(wp_unslash($_POST['cents'])) : -1);
                if ($use_compared_price_markup)
                    a2wl_set_setting('price_compared_cents', isset($_POST['compared_cents']) && intval($_POST['compared_cents']) > -1 && intval($_POST['compared_cents']) <= 99 ? intval(wp_unslash($_POST['compared_cents'])) : -1);
                else
                    a2wl_set_setting('price_compared_cents', -1);

                a2wl_set_setting('use_extended_price_markup', $use_extended_price_markup);
                a2wl_set_setting('use_compared_price_markup', $use_compared_price_markup);

                a2wl_settings()->commit();
                a2wl_settings()->auto_commit(true);

                if (isset($_POST['rules'])) {
                    A2WL_PriceFormula::deleteAll();
                    foreach ($_POST['rules'] as $rule) {
                        $formula = new A2WL_PriceFormula($rule);
                        $formula->save();
                    }
                }

                if (isset($_POST['default_rule'])) {
                    A2WL_PriceFormula::set_default_formula(new A2WL_PriceFormula($_POST['default_rule']));
                }

                $result = A2WL_ResultBuilder::buildOk(array('rules' => A2WL_PriceFormula::load_formulas(), 'default_rule' => A2WL_PriceFormula::get_default_formula(), 'use_extended_price_markup' => $use_extended_price_markup, 'use_compared_price_markup' => $use_compared_price_markup));

                restore_error_handler();
            } catch (Throwable $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            } catch (Exception $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            }

            echo json_encode($result);

            wp_die();
        }

        public function ajax_apply_pricing_rules() {
            a2wl_init_error_handler();

            $result = A2WL_ResultBuilder::buildOk(array('done' => 1));
            try {

                $type = isset($_POST['type']) ? $_POST['type'] : false;
                $scope = isset($_POST['scope']) ? $_POST['scope'] : false;
                $page = isset($_POST['page']) ? $_POST['page'] : 0;
                $import_page = isset($_POST['import_page']) ? $_POST['import_page'] : 0;

                if ($page == 0 && ($scope === 'all' || $scope === 'import')) {
                    $products_count = $this->product_import_model->get_products_count();

                    $update_per_request = a2wl_check_defined('A2WL_UPDATE_PRODUCT_IN_IMPORTLIST_PER_REQUEST');
                    $update_per_request = $update_per_request ? A2WL_UPDATE_PRODUCT_IN_IMPORTLIST_PER_REQUEST : 50;

                    $products_id_list = $this->product_import_model->get_product_id_list($update_per_request, $update_per_request * $import_page);
                    foreach ($products_id_list as $product_id) {
                        $product = $this->product_import_model->get_product($product_id);
                        if (!isset($product['disable_var_price_change']) || !$product['disable_var_price_change']) {
                            $product = A2WL_PriceFormula::apply_formula($product, 2, $type);
                            $this->product_import_model->upd_product($product);
                        }
                        unset($product);
                    }
                    unset($products_id_list);

                    if (($import_page * $update_per_request + $update_per_request) >= $products_count) {
                        $result = A2WL_ResultBuilder::buildOk(array('done' => 1, 'info' => 'Import: 100%'));
                    } else {
                        $result = A2WL_ResultBuilder::buildOk(array('done' => 0, 'info' => 'Import: ' . round(100 * ($import_page * $update_per_request + $update_per_request) / $products_count, 2) . '%'));
                    }
                }
                if ($result['done'] == 1 && ($scope === 'all' || $scope === 'shop')) {

                    $update_per_request = a2wl_check_defined('A2WL_UPDATE_PRODUCT_PER_REQUEST');
                    $update_per_request = $update_per_request ? A2WL_UPDATE_PRODUCT_PER_REQUEST : 30;

                    $products_count = $this->woocommerce_model->get_products_count();
                    if (($page * $update_per_request + $update_per_request) >= $products_count) {
                        $result = A2WL_ResultBuilder::buildOk(array('done' => 1, 'info' => 'Shop: 100%'));
                    } else {
                        $result = A2WL_ResultBuilder::buildOk(array('done' => 0, 'info' => 'Shop: ' . round(100 * ($page * $update_per_request + $update_per_request) / $products_count, 2) . '%'));
                    }

                    $product_ids = $this->woocommerce_model->get_products_ids($page, $update_per_request);
                    foreach ($product_ids as $product_id) {
                        $product = $this->woocommerce_model->get_product_by_post_id($product_id);
                        if (!isset($product['disable_var_price_change']) || !$product['disable_var_price_change']) {
                            $product = A2WL_PriceFormula::apply_formula($product, 2, $type);
                            if (isset($product['sku_products']['variations']) && count($product['sku_products']['variations']) > 0) {
                                $this->woocommerce_model->update_price($product_id, $product['sku_products']['variations'][0]);
                                foreach ($product['sku_products']['variations'] as $var) {
                                    $variation_id = get_posts(array('post_type' => 'product_variation', 'fields' => 'ids', 'numberposts' => 100, 'post_parent' => $product_id, 'meta_query' => array(array('key' => 'external_variation_id', 'value' => $var['id']))));
                                    $variation_id = $variation_id ? $variation_id[0] : false;
                                    if ($variation_id) {
                                        $this->woocommerce_model->update_price($variation_id, $var);
                                    }
                                }
                                wc_delete_product_transients($product_id);
                            }
                        }
                        unset($product);
                    }
                    unset($product_ids);
                }

                restore_error_handler();
            } catch (Throwable $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            } catch (Exception $e) {
                a2wl_print_throwable($e);
                $result = A2WL_ResultBuilder::buildError($e->getMessage());
            }

            echo json_encode($result);

            wp_die();
        }

        public function ajax_calc_external_images_count() {
            echo json_encode(A2WL_ResultBuilder::buildOk(array('total_images' => A2WL_Attachment::calc_total_external_images())));
            wp_die();
        }

        public function ajax_calc_external_images() {
            $page_size = isset($_POST['page_size']) && intval($_POST['page_size']) > 0 ? intval($_POST['page_size']) : 1000;
            $result = A2WL_ResultBuilder::buildOk(array('ids' => A2WL_Attachment::find_external_images($page_size)));
            echo json_encode($result);
            wp_die();
        }

        public function ajax_load_external_image() {
            global $wpdb;

            a2wl_init_error_handler();

            $attachment_model = new A2WL_Attachment('local');

            $image_id = isset($_POST['id']) && intval($_POST['id']) > 0 ? intval($_POST['id']) : 0;

            if ($image_id) {
                try {
                    $attachment_model->load_external_image($image_id);

                    $result = A2WL_ResultBuilder::buildOk();
                } catch (Throwable $e) {
                    a2wl_print_throwable($e);
                    $result = A2WL_ResultBuilder::buildError($e->getMessage());
                } catch (Exception $e) {
                    a2wl_print_throwable($e);
                    $result = A2WL_ResultBuilder::buildError($e->getMessage());
                }
            } else {
                $result = A2WL_ResultBuilder::buildError("load_external_image: waiting for ID...");
            }


            echo json_encode($result);
            wp_die();
        }

        public function ajax_reset_shipping_meta() {
            $result = A2WL_ResultBuilder::buildOk();
            //remove saved shipping meta
            A2WL_ShippingMeta::clear_in_all_product();
            echo json_encode($result);
            wp_die();
        }

    }

}
