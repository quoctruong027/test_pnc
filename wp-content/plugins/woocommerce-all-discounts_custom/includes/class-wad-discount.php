<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wad-discount
 *
 * @author HL
 */
class WAD_Discount {

    public $id;
    public $settings;
    public $products_list;
    public $products;
    public $title;
    public $rules_verified;
    public $is_applicable;
    public $evaluable_per_product;

    public function __construct($discount_id) {
        if ($discount_id) {
            $this->id = $discount_id;
            $this->settings = get_post_meta($discount_id, "o-discount", true);
            $this->title = get_the_title($discount_id);
            $this->rules_verified=false;
            $this->is_applicable=false;
            $this->evaluable_per_product=false;
            if(empty($this->settings["rules"]))
            {
                $this->rules_verified=true;
                $this->is_applicable=true;
            }


            if (!$this->settings)
                return;

            $list_id = false;
            $products_actions = wad_get_product_based_actions();
            if (in_array($this->settings["action"], $products_actions))
                $list_id = $this->settings["products-list"];
            else if ($this->settings["action"] == "free-gift")
                $list_id = $this->settings["gift-list"];

            if ($list_id) {
                $this->products_list = new WAD_Products_List($list_id);
                //$this->products = $this->products_list->get_products(true);
            }

            $this->init_coupled_rules_if_needed();
        }
    }

    /**
     * Register the discount custom post type
     */
    public function register_cpt_discount() {

        $labels = array(
            'name' => __('Discount', 'wad'),
            'singular_name' => __('Discount', 'wad'),
            'add_new' => __('New discount', 'wad'),
            'add_new_item' => __('New discount', 'wad'),
            'edit_item' => __('Edit discount', 'wad'),
            'new_item' => __('New discount', 'wad'),
            'view_item' => __('View discount', 'wad'),
            //        'search_items' => __('Search a group', 'wad'),
            'not_found' => __('No discount found', 'wad'),
            'not_found_in_trash' => __('No discount in the trash', 'wad'),
            'menu_name' => __('Discounts', 'wad'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Discounts',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
            'menu_icon' => WAD_URL . 'admin/images/WAD-logo.svg',
        );

        register_post_type('o-discount', $args);
    }

    /**
     * Adds the metabox for the discount CPT
     */
    public function get_discount_metabox() {

        $screens = array('o-discount');

        foreach ($screens as $screen) {

            add_meta_box(
                    'o-discount-settings-box', __('Discount settings', 'wad'), array($this, 'get_discount_settings_page'), $screen
            );
        }
    }

    /**
     * Discount CPT metabox callback
     */
    public function get_discount_settings_page() {
        $raw_wp_language = get_bloginfo("language");
        $formatted_wp_language = substr($raw_wp_language, 0, strpos($raw_wp_language, "-"));
        $url = admin_url( 'post-new.php?post_type=o-list');
        $html = "<a href='".$url."'>here</a>";
        ?>
        <script type="text/javascript">
            var lang_wordpress = '<?PHP echo $formatted_wp_language; ?>';
            jQuery(document).ready(function () {
                if (jQuery('body').hasClass('post-type-o-discount')){
                if (jQuery('tr.product-action-row select#products-list').val() === null ){
                    jQuery("<p><?php _e("You haven't created a products list. You need one in order to apply a discount on multiple products. You can create one $html","wad"); ?></p>").css('color','red').insertAfter( 'tr.product-action-row select#products-list');
                }
        }
            });
        </script>
        <div class='block-form'>
            <?php
            $timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );
            $begin = array(
                'type' => 'sectionbegin',
                'id' => 'wad-datasource-container',
            );
            $start_date = array(
                'title' => __('Start date', 'wad'),
                'name' => 'o-discount[start-date]',
                'type' => 'text',
                'class' => 'o-date',
//                'custom_attributes' => array("required" => ""),
                'desc' => __('Date from which the discount is applied. Not mandatory. </br></br>Local time: ', 'woo-advanced-discounts').'<strong>'.date_i18n( $timezone_format ).'</strong>',
                'default' => '',
            );

            $end_date = array(
                'title' => __('End date', 'wad'),
                'name' => 'o-discount[end-date]',
                'type' => 'text',
                'class' => 'o-date',
//                'custom_attributes' => array("required" => ""),
                'desc' => __('Date when the discount ends.  Not mandatory. </br></br>Local time: ', 'woo-advanced-discounts').'<strong>'.date_i18n( $timezone_format ).'</strong>',
                'default' => '',
            );

            $period = array(
                'title' => "",
                'name' => 'o-discount[period]',
                'type' => 'number',
//                'desc' => __('Repeat every.', 'wad'),
            );
            $period_type = array(
                'title' => "",
                'name' => 'o-discount[period-type]',
                'type' => 'select',
//                'desc' => __('xx', 'wad'),
                'default' => '',
                'options' => array(
                    "d" => __("days", "wad"),
                    "m" => __("months", "wad"),
                    "y" => __("years", "wad"),
                )
            );
            $periodicity = array(
                'title' => __('Repeat every', 'wad'),
                'type' => 'groupedfields',
                'desc' => __('Interval to repeat the discount. Leave empty to disable that feature.', 'wad'),
                'fields' => array($period, $period_type)
            );
            $users_limit = array(
                'title' => __('Users Limit', 'wad'),
                'name' => 'o-discount[users-limit]',
                'desc' => __('Autorized number of customers that can use the discount (leave empty to disable that feature).', 'wad'),
                'type' => 'number',
                'default' => '',
            );
            $action = array(
                'title' => __('Action', 'wad'),
                'name' => 'o-discount[action]',
                'type' => 'select',
                'class' => 'discount-action',
                'desc' => __('Type of discount to apply.', 'wad'),
                'default' => '',
                'options' => $this->get_discounts_actions(),
            );

            $product_lists = new WAD_Products_List(false);

            $free_gift = array(
                'title' => __('Gifts List', 'wad'),
                'name' => 'o-discount[gift-list]',
                'type' => 'select',
                'row_class' => 'free-gift-row',
                'desc' => __('List of products the customer can choose his gift from', 'wad'),
                'default' => '',
                'options' => $product_lists->get_all(),
            );
            $gifts_limit = array(
                'title' => __('Gifts Limit', 'wad'),
                'name' => 'o-discount[nb-gifts-limit]',
                'type' => 'number',
                'row_class' => 'free-gift-row',
                'desc' => __('Maximum number of products the customer can select within the gifts list', 'wad'),
                'default' => 1,
                'options' => $product_lists->get_all(),
                'required' => true
            );
            
            $gift_label = array(
                'title' => __('Gift Label', 'wad'),
                'name' => 'o-discount[gift-label]',
                'row_class' => 'free-gift-row',
                'type' => 'text',
                'desc' => __('This is a short description of the gift product. It will appear at the cart item line level. <br/><strong>Note</strong> that <strong>Free gift</strong> will be display as default value.', 'wad'),
                'default' => 'Free Gift',
            );

            $products_action = array(
                'title' => __('Products list', 'wad'),
                'id' => 'products-list',
                'name' => 'o-discount[products-list]',
                'type' => 'select',
                'row_class' => 'product-action-row',
                'desc' => __('List of products the selected action applies on', 'wad'),
                'default' => '',
                'options' => $product_lists->get_all(),
                'required' => true
            );
            $cheapest_products = array(
                'title' => __('NB. Cheapest products', 'wad'),
                'name' => 'o-discount[nb-cheapest-products]',
                'type' => 'number',
                'row_class' => 'cheapest-product-row',
                'desc' => __('How many products should be considered when applying the discounts on the cheapest products?', 'wad'),
                'default' => 1,
            );

            $shippings_action = array(
                'title' => __('Shipping methods', 'wad'),
                'row_class' => 'shipping-action-row',
                'desc' => __('Shipping methods on which the selected action applies on', 'wad'),
                'type' => 'custom',
                'callback' => array($this, 'get_shipping_method_select'),
            );

            $disable_on_product_pages = array(
                'title' => __('Disable on products and shop pages', 'wad'),
                'id' => 'products-list',
                'name' => 'o-discount[disable-on-product-pages]',
                'type' => 'radio',
                'row_class' => 'product-action-row',
                'desc' => __('Disables the display of discounted prices on all pages except cart and checkout', 'wad'),
                'default' => 'no',
                'options' => array(
                    "yes" => "Yes",
                    "no" => "No",
                )
            );

            $percentage_or_fixed_amount = array(
                'title' => __('Percentage / Fixed amount', 'wad'),
                'name' => 'o-discount[percentage-or-fixed-amount]',
                'type' => 'number',
                'id' => 'percentage-amount',
                'custom_attributes' => array("step" => "any"),
                'row_class' => 'percentage-row',
                'desc' => __('Percentage or fixed amount to apply.', 'wad'),
                'default' => '',
                'required' => true
            );

            $group_by_product = array(
                'title' => __('Evaluate per product', 'wad'),
                'name' => 'o-discount[calculate-per-product]',
                'type' => 'select',
                'row_class' => 'product-action-row',
                'desc' => __('Run the calculations of each product in the list independantly.', 'wad') . "<br><strong style='color: red;'>Beta.</strong>",
                'default' => 'no',
                'options' => array("yes" => "Yes", "no" => "No"),
            );

            $is_cumulable = array(
                'title' => __('Cumulable ?', 'wad'),
                'name' => 'o-discount[is-cumulable]',
                'type' => 'radio',
                'default' => 'no',
                'desc' => __('whether or not the discount can be applied if the condition is fullfilled more than once.', 'wad'),
                'options' => array(
                    "yes" => "Yes",
                    "no" => "No",
                )
            );

            $is_taxable = array(
                'title' => __('Taxable ?', 'wad'),
                'name' => 'o-discount[is-taxable]',
                'type' => 'radio',
                'default' => 'default',
                'row_class' => 'percentage-row wad-taxable',
                'desc' => __('whether or not the discount should be taxable.', 'wad'),
                'options' => array(
                    "yes" => __("Yes",'wad'),
                    "no" => __("No",'wad'),
                    "default" => __("Default",'wad'),
                )
            );

            $relationship = array(
                'title' => __('Rules groups relationship', 'wad'),
                'name' => 'o-discount[relationship]',
                'type' => 'radio',
                'desc' => __('AND: All groups rules must be verified to have the discount action applied.', 'wad') . "<br" . __('OR: AT least one group rules must be verified to have the discount action applied.', 'wad'),
                'default' => 'AND',
                'options' => array(
                    "AND" => "AND",
                    "OR" => "OR",
                )
            );

            $rules = array(
                'title' => __('Rules', 'wad'),
                'desc' => __('Allows you to define which rules should be checked in order to apply the discount. Not mandatory.', 'wad'),
                'name' => 'o-discount[rules]',
                'type' => 'custom',
                'callback' => array($this, "get_discount_rules_callback"),
            );

            $end = array('type' => 'sectionend');
            $settings = array(
                $begin,
                $start_date,
                $end_date,
                $periodicity,
                $users_limit,
//                $is_cumulable,
                $relationship,
                $rules,
                $action,
                $percentage_or_fixed_amount,
                $is_taxable,
                $group_by_product,
                $shippings_action,
                $free_gift,
                $gifts_limit,
                $gift_label,
                $products_action,
                $cheapest_products,
                $disable_on_product_pages,
                $end
            );
            echo o_admin_fields($settings);
            ?>
        </div>

        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
        return;
    }

    /**
     * Discount apply on shipping method select
     */

    public function get_shipping_method_select(){

        $method_title = array();
        $did = get_the_ID();
        $discount_metas = get_post_meta($did, 'o-discount', true);
        $selected_method = isset($discount_metas['shipping-list'])?$discount_metas['shipping-list']:'';
        $shipping_methods = WC()->shipping->get_shipping_methods();
        foreach($shipping_methods as $id => $shipping_method){
            if($id != 'free_shipping'){
                $method_title[$id] = $shipping_method->get_method_title();
            }
        }
        $shipping_html_select = get_wad_html_select('o-discount[shipping-list][]', 'shipping-list', '', $method_title, $selected_method, true, true);

        echo $shipping_html_select;
    }

    private function get_rule_tpl($conditions, $default_condition = false, $default_operator = "", $default_value = "") {
        ob_start();
        $operators = $this->get_operator_fields_match($default_condition, $default_operator);
        $value_field = $this->get_value_fields_match($default_condition, $default_value);
        ?>
        <tr data-id="rule_{rule-group}">
            <td class="param">
                <select class="select wad-pricing-group-param" name="o-discount[rules][{rule-group}][{rule-index}][condition]" data-group="{rule-group}" data-rule="{rule-index}">
                    <?php
                    foreach ($conditions as $condition_key => $condition_val) {
                        if ($condition_key == $default_condition) {
                            ?><option value='<?php echo $condition_key; ?>' selected="selected"><?php echo $condition_val; ?></option><?php
                        } else {
                            ?><option value='<?php echo $condition_key; ?>'><?php echo $condition_val; ?></option><?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="operator">
                <?php echo $operators; ?>
            </td>
            <td class="value">
                <?php echo $value_field; ?>
            </td>
            <td class="add">
                <a class="wad-add-rule button" data-group='{rule-group}'><?php echo __("and", "wad"); ?></a>
            </td>
            <td class="remove">
                <a class="wad-remove-rule acf-button-remove"></a>
            </td>
        </tr>
        <?php
        $rule_tpl = ob_get_contents();
        ob_end_clean();
        return $rule_tpl;
    }

    private function get_value_fields_match($condition = false, $selected_value = "") {
        $selected_value_arr = array();
        $selected_value_str = "";
        if (is_array($selected_value))
            $selected_value_arr = $selected_value;
        else
            $selected_value_str = $selected_value;

        $field_name = "o-discount[rules][{rule-group}][{rule-index}][value]";
        $roles = wad_get_existing_user_roles();
        $roles_select = get_wad_html_select($field_name . "[]", false, "", $roles, $selected_value_arr, true, true);
        $mc_lists = wad_get_mailchimp_lists();
        $affiliate_lists = wad_get_affiliate_lists();
        $sendingblue_lists = wad_get_sendinblue_lists();
        $currencies_lists = array();
        $currencies = get_woocommerce_currencies();
        foreach ($currencies as $currency_name => $currency) {
            $currencies_lists[$currency_name] = $currency_name;
        }

        $mailchimp_select = get_wad_html_select($field_name, false, "", $mc_lists, $selected_value_str, false);
        $affiliate_select = get_wad_html_select($field_name, false, "", $affiliate_lists, $selected_value_str, false);
        $sendinblue_select = get_wad_html_select($field_name, false, "", $sendingblue_lists, $selected_value_str, false);

        $currencies_select = get_wad_html_select($field_name . "[]", false, "", $currencies_lists, $selected_value_arr, true);

        $users = wad_get_existing_users();
        $users_select = get_wad_html_select($field_name . "[]", false, "", $users, $selected_value_arr, true, true);

        $text = '<input type="number" name="' . $field_name . '" value="' . $selected_value_str . '" required>';
        $text_field = '<input type="text" placeholder="'.__('multiple sepated by ,', 'wad').'" name="' . $field_name . '" value="' . $selected_value_str . '" required>';
        $list_obj = new WAD_Products_List(false);
        $lists_arr = $list_obj->get_all();
        $products_list = get_wad_html_select($field_name, false, "", $lists_arr, $selected_value_str, false,true);

        $available_gateways_arr = wad_get_available_payment_gateways();
        $payment_systems_select = get_wad_html_select($field_name . "[]", false, "", $available_gateways_arr, $selected_value_arr, true);

        $countries_obj = new WC_Countries();
        $countries_arr = $countries_obj->get_countries();
        $countries_select = get_wad_html_select($field_name . "[]", false, "", $countries_arr, $selected_value_arr, true);

        $states_arr = wad_get_all_states();
        $states_select = get_wad_html_select($field_name . "[]", false, "", $states_arr, $selected_value_arr, true);

        $shipping_methods_arr = wad_get_all_shipping_methods();
        $shipping_method_select = get_wad_html_select($field_name . "[]", false, "", $shipping_methods_arr, $selected_value_arr, true);

        $groups_arr = wad_get_available_groups();
        $groups_select = get_wad_html_select($field_name . "[]", false, "", $groups_arr, $selected_value_arr, true);


        $values_match = apply_filters("wad_fields_values_match", array(
            "customer-role" => $roles_select,
            "customer" => $users_select,
            "previous-order-count" => $text,
            "total-spent-on-shop" => $text,
            "order-subtotal" => $text,
            "order-subtotal-inc-taxes" => $text,
            "order-item-count" => $text,
            "different-order-item-count" => $text,
            "order-products" => $products_list,
            "customer-reviewed-product" => $products_list,
//            "customer-reviewed-product-only" => $products_list,
            "payment-gateway" => $payment_systems_select,
            "billing-country" => $countries_select,
            "billing-state" => $states_select,
            "shipping-country" => $countries_select,
            "shipping-state" => $states_select,
            "shipping-method" => $shipping_method_select,
            "customer-subscribed-mailchimp" => $mailchimp_select,
            "customer-following-affiliation-link" => $affiliate_select,
            "customer-subscribed-sendinblue" => $sendinblue_select,
            "customer-subscribed-newsletter-plugin" => "",
            "customer-group" => $groups_select,
            "previously-ordered-products-count" => $text,
            "previously-ordered-products-in-list" => $products_list,
            "shop-currency" => $currencies_select,
            "email-domain-name" => $text_field,
                ), $condition, $selected_value);

        if (isset($values_match[$condition]))
            return $values_match[$condition];
        else
            return $values_match;
    }

    private function get_operator_fields_match($condition = false, $selected_value = "") {
        $field_name = "o-discount[rules][{rule-group}][{rule-index}][operator]";
        $arrays_operators = array(
            "IN" => __("IN", "wad"),
            "NOT IN" => __("NOT IN", "wad"),
        );
        $arrays_operators_select = get_wad_html_select($field_name, false, "", $arrays_operators, $selected_value);


        $number_operators = array(
            "<" => __("is less than", "wad"),
            "<=" => __("is less or equal to", "wad"),
            "==" => __("equals", "wad"),
            ">" => __("is more than", "wad"),
            ">=" => __("is more or equal to", "wad"),
            "%" => __("is a multiple of", "wad"),
        );
        $number_operators_select = get_wad_html_select($field_name, false, "", $number_operators, $selected_value);
        $operators_match = apply_filters("wad_operators_fields_match", array(
            "customer-role" => $arrays_operators_select,
            "customer" => $arrays_operators_select,
            "previous-order-count" => $number_operators_select,
            "total-spent-on-shop" => $number_operators_select,
            "order-subtotal" => $number_operators_select,
            "order-subtotal-inc-taxes" => $number_operators_select,
            "order-item-count" => $number_operators_select,
            "different-order-item-count" => $number_operators_select,
            "order-products" => $arrays_operators_select,
            "customer-reviewed-product" => $arrays_operators_select,
//            "customer-reviewed-product-only" => $arrays_operators_select,
            "payment-gateway" => $arrays_operators_select,
            "billing-country" => $arrays_operators_select,
            "billing-state" => $arrays_operators_select,
            "shipping-country" => $arrays_operators_select,
            "shipping-state" => $arrays_operators_select,
            "shipping-method" => $arrays_operators_select,
            "customer-subscribed-mailchimp" => $arrays_operators_select,
            "customer-following-affiliation-link" => $arrays_operators_select,
            "customer-subscribed-sendinblue" => $arrays_operators_select,
            "customer-subscribed-newsletter-plugin" => "",
            "customer-group" => $arrays_operators_select,
            "previously-ordered-products-count" => $number_operators_select,
            "previously-ordered-products-in-list" => $arrays_operators_select,
            "shop-currency" => $arrays_operators_select,
            "email-domain-name" => $arrays_operators_select,
                ), $condition, $selected_value);

        if (isset($operators_match[$condition]))
            return $operators_match[$condition];
        else
            return $operators_match;
    }

    function get_discount_rules_callback() {

        $conditions = $this->get_discounts_conditions();
        $first_rule = $this->get_rule_tpl($conditions, "customer-role");
//        $rule_tpl = $this->get_rule_tpl($conditions);
        $values_match = $this->get_value_fields_match(-1);
        $operators_match = $this->get_operator_fields_match(-1);
        ?>
        <script>
            var wad_values_matches =<?php echo json_encode($values_match); ?>;
            var wad_operators_matches =<?php echo json_encode($operators_match); ?>;
        </script>
        <div class='wad-rules-table-container'>
            <textarea id='wad-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <textarea id='wad-first-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <?php
            $discount_id = get_the_ID();
            $metas = get_post_meta($discount_id, 'o-discount', true);
            $all_rules = array();
            if (isset($metas['rules']))
                $all_rules = $metas['rules'];

            if (is_array($all_rules) && !empty($all_rules)) {
                $rule_group = 0;
                foreach ($all_rules as $rules) {
                    $rule_index = 0;
                    ?>
                    <table class="wad-rules-table widefat wad-rules-table">
                        <tbody>
                            <?php
                            foreach ($rules as $rule_arr) {
                                $rule_arr["condition"] = get_proper_value($rule_arr, "condition");
                                $rule_arr["operator"] = get_proper_value($rule_arr, "operator");
                                $rule_arr["value"] = get_proper_value($rule_arr, "value");
                                $rule_html = $this->get_rule_tpl($conditions, $rule_arr["condition"], $rule_arr["operator"], $rule_arr["value"]);
                                $r1 = str_replace("{rule-group}", $rule_group, $rule_html);
                                $r2 = str_replace("{rule-index}", $rule_index, $r1);
                                echo $r2;
                                $rule_index++;
                            }
                            $rule_group++;
                            ?>
                        </tbody>
                    </table>
                    <?php
                }
            }
            ?>

        </div>
        <a class="button wad-add-group mg-top"><?php _e("Add rules group", "wad"); ?></a>
        <?php
    }

    /**
     * Saves the discount data
     * @param type $post_id
     */
    public function save_discount($post_id) {
        $meta_key = "o-discount";
        if (isset($_POST[$meta_key])) {
            update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
        }
    }

    function get_cart_items($item_id = false) {
        global $woocommerce;
        return $woocommerce->cart->get_cart();
    }

    function get_cart_item_html($price_html, $cart_item, $cart_item_key) {
        //Some plugins like woocommerce request a quote send an empty $cart_item which trigger a lot of issues.
        if (!empty($cart_item)) {
            $product_id = $cart_item["product_id"];
            if ($cart_item["variation_id"])
                $product_id = $cart_item["variation_id"];
            $product_obj = wc_get_product($product_id);
            $original_price = wad_get_product_price($product_obj); //$product_obj->get_price();
            //We check the price used for add to cart
            $used_price = $this->get_regular_price($original_price, $product_obj);
            //A discount is applied
            if ($used_price != $cart_item['data']->get_price()) {
                $old_price_html = wc_price($original_price);
                $price_html = "<span class='wad-discount-price' style='text-decoration: line-through;'>$old_price_html</span>" . " $price_html";
            }
        }
        return $price_html;
    }

    function get_free_gift_quantity_html( $product_quantity,  $cart_item_key ) {
        global $woocommerce;
        $item = $woocommerce->cart->get_cart_item($cart_item_key);
        if( isset( $item['wad_free_gift'] ) ){
            $product_quantity = $item['quantity'];
        }

        return $product_quantity;
    }

    /**
     * Returns the total to widthdraw on cart or taxes
     * @global Array $wad_discounts
     * @global Object $woocommerce
     * @param Bool $on_taxes Whether to return the total on taxes or on the cart
     * @return Float
     */
    private function get_total_cart_discount($on_taxes = false) {
        global $wad_discounts;
        global $wad_settings;
        global $woocommerce;

        //Real price are not get using global so we get subtotal directly within this function which is call on the hook woocommerce_cart_calculate_fees
//        global $wad_cart_total_inc_taxes;
//        global $wad_cart_total_without_taxes;
        $wad_cart_total_inc_taxes = wad_get_cart_total(true);
        $wad_cart_total_without_taxes = wad_get_cart_total();
        $display_individual_discounts = get_proper_value($wad_settings, "individual-cart-discounts", 1);

        $discounts = $wad_discounts;
        $to_widthdraw = 0;
        $to_widthdraw_on_taxes = 0;

        $taxable = wc_tax_enabled();
        $prices_inclusing_taxes = get_option('woocommerce_prices_include_tax') == 'yes' ? true : false;
        if ($taxable && $prices_inclusing_taxes)
            $taxable = false;

        foreach ($discounts["order"] as $discount_id => $discount) {
            $discount_tax=  get_proper_value($discount->settings, 'is-taxable', 'default');
            if($discount_tax=='yes')
                $taxable=true;
            elseif($discount_tax=='no')
                $taxable=false;
            if ($discount->is_applicable()) {
                if ($discount->settings["action"] != "free-gift") {
                    if ($discount->settings["action"] == "percentage-off-osubtotal-inc-taxes" || $discount->settings["action"] == "fixed-amount-off-osubtotal-inc-taxes") {
                        $cart_total = $wad_cart_total_inc_taxes;
                        if ($discount->settings["action"] == "percentage-off-osubtotal-inc-taxes") {
                            $to_widthdraw_on_taxes+=$discount->settings["percentage-or-fixed-amount"];
                        } elseif($cart_total) {
                            //We determine which percentage of the total represents the fixed amount
                            $percentage = $discount->settings["percentage-or-fixed-amount"] * 100 / $cart_total;
                            $to_widthdraw_on_taxes+=$percentage;
                        }
                    } else {
                        $cart_total = $wad_cart_total_without_taxes;
                    }

                    $discount_ttc = $discount->get_discount_amount($cart_total);
                    if ($display_individual_discounts)
                        $woocommerce->cart->add_fee($discount->title, (-1 * $discount_ttc), $taxable, '');
                    $to_widthdraw+=$discount_ttc;
                }

                // We save the discount to use it later when completing the payment.
                global $wad_active_discounts;
                if ( ! in_array( $discount_id, $wad_active_discounts, true ) ) {
                        array_push( $wad_active_discounts, $discount_id );
                }
            }
        }

        if ($on_taxes)
            return $to_widthdraw_on_taxes / 100;
        else
            return $to_widthdraw;
    }

    /**
     * Returns the product price in the cart
     * @global type $woocommerce
     * @param type $product_id
     * @return type
     */
    function get_cart_item_price($product_id) {
        global $woocommerce;
//        $price = 0;
//        foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data) {
//            if ($cart_item_data["product_id"] == $product_id) {
        $product = wc_get_product($product_id);
        //$price = $product->get_price();
        if (WC()->cart->get_tax_price_display_mode() == 'excl') {
            $price = wc_get_price_excluding_tax($product);
        } else {
            $price = wc_get_price_including_tax($product);
        }
//                $price = $cart_item_data["line_total"] / $cart_item_data["quantity"];
//            }
//        }
        return $price;
    }

    function woocommerce_custom_surcharge() {
        global $woocommerce;
        global $wad_settings;
        global $wad_cart_discounts;
        if(WC()->cart){
            foreach(WC()->cart->get_applied_coupons() as $coupon_code){
                $c = new WC_Coupon($coupon_code);
                if($c->discount_type!="smart_coupon"){
                    return;
                }
            }
        }

        $display_individual_discounts = get_proper_value($wad_settings, "individual-cart-discounts", 1);
        $disable_coupons = get_proper_value($wad_settings, "disable-coupons", false);

        if (!defined('WAD_INITIALIZED') || (is_admin() && !is_ajax()))
            return;

        $discount_ttc = $this->get_total_cart_discount() * -1;
        $discount_ht = $discount_ttc / (1 + $this->get_total_cart_discount(true));
        $taxable = wc_tax_enabled();

        if ($discount_ht) {
            if (!$display_individual_discounts)
                $woocommerce->cart->add_fee(__('Reductions on cart', 'wad'), $discount_ht, $taxable, '');
            $wad_cart_discounts = $discount_ttc;
        }
        //remove all applied coupons when cart discount is apply
        if ($wad_cart_discounts && $disable_coupons){
            WC()->cart->remove_coupons();
    }
    }

    function get_discount_amount($amount) {
        $to_widthdraw = 0;
        if (in_array($this->settings["action"], array("percentage-off-pprice", "percentage-off-osubtotal", "percentage-off-osubtotal-inc-taxes", "percentage-off-cprice", "percentage-off-lsubtotal")))
            $to_widthdraw = floatval ($amount) * floatval ($this->settings["percentage-or-fixed-amount"]) / 100;
        //Fixed discount
        else if (in_array($this->settings["action"], array("fixed-amount-off-pprice", "fixed-amount-off-osubtotal", "fixed-amount-off-osubtotal-inc-taxes", "fixed-amount-off-cprice", "fixed-amount-off-lsubtotal"))) {
            $to_widthdraw = $this->settings["percentage-or-fixed-amount"];
        } else if ($this->settings["action"] == "fixed-pprice")
            $to_widthdraw = floatval($amount) - floatval($this->settings["percentage-or-fixed-amount"]);
        //We save the discount in the session to use it later when completing the payment
        $decimals = wc_get_price_decimals();
        return wc_cart_round_discount($to_widthdraw, $decimals);
    }

    function get_free_gifts_subtotals($product_subtotal, $_product, $quantity, $cart) {
        global $wad_discounts;
        foreach ($wad_discounts["order"] as $discount_id => $discount_obj) {
            if ($discount_obj->settings["action"] != "free-gift" || !$discount_obj->is_applicable())
                continue;
            $list_object = $discount_obj->products_list; //new WAD_Products_List($products_list_id);
            $list_products_ids = $list_object->get_products(); //$discount_obj->products;
            $product_id = $_product->get_id();
            if (in_array($product_id, $list_products_ids) && $this->is_free_gift($product_id, $discount_id)) {
                $nb_gifts = 1;
                $quantity-=$nb_gifts;
                $product_subtotal = $this->get_product_subtotal($_product, $quantity, $cart);
            }
        }
        return $product_subtotal;
    }

    function get_free_gifts_item_subtotals( $product_subtotal,  $cart_item,  $cart_item_key){

        global $wad_discounts;
        global $woocommerce;
        foreach ($wad_discounts["order"] as $discount_id => $discount_obj) {
            if ($discount_obj->settings["action"] != "free-gift" || !$discount_obj->is_applicable())
                continue;
            $list_object = $discount_obj->products_list; //new WAD_Products_List($products_list_id);
            $list_products_ids = $list_object->get_products(); //$discount_obj->products;
            $product_id = $cart_item['product_id'];
            $quantity = intval($cart_item['quantity']);
            $_product = wc_get_product($product_id);
            $cart = $woocommerce->cart;
            if (in_array($product_id, $list_products_ids) && isset($cart_item['wad_free_gift'])) {
                $nb_gifts = 1;
                $quantity-=$nb_gifts;
                $product_subtotal = $this->get_product_subtotal($_product, $quantity, $cart);
            }
        }
        return $product_subtotal;
    }

    private function get_product_subtotal($_product, $quantity, $cart) {

        $price = $_product->get_price();
        $taxable = $_product->is_taxable();

        // Taxable
        if ($taxable) {

            if ($cart->tax_display_cart == 'excl') {
                $row_price = wc_get_price_excluding_tax( $_product, array( 'qty' => $quantity ) );
                $product_subtotal = wc_price($row_price);

                if ($cart->prices_include_tax && $cart->tax_total > 0) {
                    $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                }
            } else {
                $row_price = wc_get_price_including_tax( $_product, array( 'qty' => $quantity ) );
                $product_subtotal = wc_price($row_price);

                if (!$cart->prices_include_tax && $cart->tax_total > 0) {
                    $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                }
            }

            // Non-taxable
        } else {

            $row_price = $price * $quantity;
            $product_subtotal = wc_price($row_price);
        }

        return $product_subtotal;
    }

    function show_original_price_on_cart($price, $product){
        global $wad_settings;
        $show_o_price = get_proper_value($wad_settings, 'original-price', '1');

        if($show_o_price == '1' && isset($product['data'])){
            $slashed_price = $product['data']->get_price_html();
            $is_on_sale = $product['data']->is_on_sale();
            if ( $is_on_sale )
                $price = $slashed_price;
        }

        return $price;
    }

    function show_original_total_price_on_checkout($price, $product){
        global $wad_settings;
        $show_o_price = get_proper_value($wad_settings, 'original-price', '1');
        $to_display = $price;
        if(is_checkout() && $show_o_price == '1'){
            $is_on_sale = $product['data']->is_on_sale();
            if ( $is_on_sale ){
                $to_price = $product['data']->get_regular_price() * $product['quantity'];
                $to_display = wc_format_sale_price( $to_price, $price );
            }

        }
        return $to_display;
    }
    
    public function update_cart_items_price_html($items_html, $cart_item){
        global $wad_settings;
        $show_o_price = get_proper_value($wad_settings, 'original-price', '1');
        $product_obj = $cart_item['data'];
        $product_regular_price = $product_obj->get_regular_price();
        $product_sale_price = $product_obj->get_price();
        if( $show_o_price == '1'  ){
            $sale_price = $this->apply_quantity_based_discount_if_needed($product_obj,$product_regular_price);
            if ($product_regular_price != $sale_price){
                $items_html = '<span class="quantity">'.$cart_item['quantity'].' &times; '.wc_format_sale_price($product_regular_price,$sale_price).'</span>';
            }
            if ($product_regular_price > $product_sale_price){
                $sale_price = $this->apply_quantity_based_discount_if_needed($product_obj,$product_sale_price);
                if ($product_sale_price != $sale_price){
                    $items_html = '<span class="quantity">'.$cart_item['quantity'].' &times; '.wc_format_sale_price($product_sale_price,$sale_price).'</span>';
                }
            }
        }
        return $items_html;
    }
    
    public function update_items_prices(){
        foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item){
            $product_data = $cart_item['data'];
            $product_price = 0;
            // if( !empty($product_data->get_sale_price()) )
            //     $product_price = $product_data->get_sale_price();
            // else
            //     $product_price = $product_data->get_price();

            $product_price = $product_data->get_regular_price();
            $product_sale_price = $product_data->get_sale_price();
            if (!$product_sale_price){
                $sale_price = $this->apply_quantity_based_discount_if_needed($product_data,$product_sale_price);
            }else{
                $sale_price = $this->apply_quantity_based_discount_if_needed($product_data,$product_price);
            }
            $product_data->set_sale_price($sale_price);
        }
    }

    function display_total_products_discount(){
        global $woocommerce;
        global $wad_settings;
        $display_discount_products = get_proper_value($wad_settings, 'discount-on-products', '0');
        if ($display_discount_products == '1'){

            $discount_total = 0;

            foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values) {

                $product = $values['data'];
                if(isset($values['wad_free_gift'])){
                    $discount = $product->get_regular_price();
                    $discount_total += $discount;
                }
                elseif ( $product->is_on_sale() ) {
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $discount = ($regular_price - $sale_price) * $values['quantity'];
                    $discount_total += $discount;
                }

            }

            if ( $discount_total > 0 ) {
                echo '<tr class="cart-discount">
                <th>'. __( 'Total Discounts on products', 'wad' ) .'</th>
                <td data-title=" '. __( 'Total Discounts', 'wad' ) .' ">'
                . wc_price( $discount_total ) .'</td>
                </tr>';
            }
        }
    }

    function is_rule_valid($rule, $product_id = false) {
        global $wad_last_products_fetch;
        $is_valid = false;
        $condition = $this->get_evaluable_condition($rule, $product_id);
        $value = get_proper_value($rule, "value");

        //We check if the condition is IN or NOT IN the value
//        $array_operators=array("IN", "NOT IN");
        if ( $rule["condition"] == "customer" || $rule["condition"] == "payment-gateway" || $rule["condition"] == "billing-country" || $rule["condition"] == "shipping-country" || $rule["condition"] == "shipping-state" || $rule["condition"] == "shipping-method" || $rule["condition"] == "billing-state" || $rule["condition"] == "shop-currency") {
//        if(in_array($rule["operator"], $array_operators))
            if (!is_array($value)) {
                $error_msg = __("Discount", "wad") . " #$this->id: " . __("Rule ", "wad") . $rule["condition"] . __(" requires at least one parameter selected in the values", "wad");
                echo $error_msg . "<br>";
                $is_valid = false;
            } else {
                $is_valid = in_array($condition, $value);
                if ($rule["operator"] == "NOT IN") {
                    $is_valid = (!$is_valid);
                }
            }
            //Checks if the a products is in a list
        } else if ($rule["condition"] == "order-products") {
            $list_products_ids = (isset($rule["list"]) && is_array($rule["list"]))?$rule["list"]:array();
            //We check if there is at list one occurence of the items in the condition in the list
            $diff = array_intersect($condition, $list_products_ids);
            $is_valid = count($diff);
            if ($rule["operator"] == "NOT IN") {
                $is_valid = (!$is_valid);
            }
        } else if($rule["condition"] == "customer-role") {
            if(is_array($condition))
                $is_valid = array_intersect($condition, $value);
            else
                $is_valid = in_array($condition, $value);
            if ($rule["operator"] == "NOT IN")
                $is_valid = (!$is_valid);
        } else if ($rule["condition"] == "order-item-count-in-list") {
            $is_valid = $condition;
        }else if ($rule["condition"] == "different-order-item-count-in-list") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "previously-ordered-products-count") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "previously-ordered-products-in-list") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "customer-reviewed-product") {
            $is_valid = $condition;
//        } else if ($rule["condition"] == "customer-reviewed-product-only") {
//            $is_valid = in_array($product_id, $condition);
        } else if ($rule["condition"] == "customer-subscribed-mailchimp") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "customer-following-affiliation-link") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "customer-subscribed-sendinblue") {
            $is_valid = $condition;
        } else if ($rule["condition"] == "customer-subscribed-newsletter-plugin") {
            $is_valid = $condition;
            //customer-group: If the customer belongs to one of the specified groups
        } else if ($rule["condition"] == "customer-group") {
            if (isset($rule["value"])) {
                $selected_groups = $rule["value"];
                $diff = array_intersect($condition, $selected_groups);
                $is_valid = count($diff);
            } else
                $is_valid = false;
        } else if ($rule["condition"] == "previously-ordered-products-count-in-list") {
            $is_valid = $condition;
        } else if($rule["condition"] == "email-domain-name"){
            $is_valid = $condition;
        } else {
            $operator = isset($rule["operator"])?$rule["operator"]:"";
            if ($operator == "%"){//Modulo evaluation
                $expression_to_eval = function($condition,$operator,$value){if($condition > 0 && ($condition % $value==0)) return true; else return false;};
                $is_valid = $expression_to_eval($condition,$operator,$value);
            }
            else{
                $expression_to_eval = wad_evaluate_conditions($condition,$operator,$value);
                $is_valid = $expression_to_eval;
            }
        }
        return apply_filters('wad_is_rule_valid', $is_valid, $rule, $this);
    }

    private function get_evaluable_condition($rule, $product_id) {
        $condition = $rule["condition"];
        $evaluable_condition = false;

        switch ($condition) {
            case "customer-role":
                global $wad_user_role;
                $evaluable_condition = $wad_user_role;
                break;
            case "customer":
                if (is_user_logged_in())
                    $evaluable_condition = get_current_user_id();
                break;
            case "previous-order-count":
                $evaluable_condition = $this->settings["previous-order-count"];
                break;
            case "total-spent-on-shop":
                $evaluable_condition = $this->settings["total-spent-on-shop"];
                break;
            case "order-subtotal":
                global $wad_cart_total_without_taxes;
                $evaluable_condition = $wad_cart_total_without_taxes;
                break;
            case "order-subtotal-in-list":
                global $woocommerce;
                $subtotal_in_list = 0;

                $order_products_in_list = wad_get_order_products_in_list($rule);
                $order_products_in_list_ids = array_keys($order_products_in_list);

                foreach ($woocommerce->cart->cart_contents as $cart_item) {
                    if (in_array($cart_item["variation_id"], $order_products_in_list_ids) || in_array($cart_item["product_id"], $order_products_in_list_ids)) {
                        $subtotal_in_list+=$cart_item["line_subtotal"];
                    }
                }
                $evaluable_condition = $subtotal_in_list;
                break;
            case "order-subtotal-inc-taxes":
                global $wad_cart_total_inc_taxes;
                $evaluable_condition = $wad_cart_total_inc_taxes;
                break;
            case "order-item-count":
                $evaluable_condition = wad_get_cart_products_count(); //$woocommerce->cart->get_cart_contents_count();
                break;
            case "different-order-item-count":
                $evaluable_condition = count(wad_get_cart_products_count(true)); //$woocommerce->cart->get_cart_contents_count();
                break;
            case "order-products":
                $evaluable_condition = wad_get_cart_products();
                break;
            case "customer-reviewed-product":
                $evaluable_condition = false;
                if (is_user_logged_in()) {
                    $current_user_id = get_current_user_id();
                    $evaluable_condition = wad_check_if_customer_reviewed_any_of_these_products($rule["list"], $current_user_id);
                    if($rule["operator"] == 'NOT IN')
                        $evaluable_condition=(!$evaluable_condition);
                }
                break;
//            case "customer-reviewed-product-only":
//                $evaluable_condition = false;
//                if (is_user_logged_in()) {
//                    $current_user_id = get_current_user_id();
//                    $evaluable_condition = wad_get_reviewed_products_by_customer($current_user_id);
//                    if($rule["operator"] == 'NOT IN')
//                        $evaluable_condition=(!$evaluable_condition);
//                }
//                break;
            case "payment-gateway":
                $evaluable_condition = WC()->session->chosen_payment_method;
                break;
            case "billing-country":
                $evaluable_condition = WC()->session->customer["country"];
//                var_dump(WC()->session);
                break;
            case "billing-state":
                //The state condition is stored in form of country-code|state-code
                $country = WC()->session->customer["country"];
                if(isset(WC()->session->customer["billing_state"]))
                    $state = WC()->session->customer["billing_state"];
                else
                    $state = WC()->session->customer["state"];
                $evaluable_condition = "$country|$state";
                break;
            case "shipping-country":
                $evaluable_condition = WC()->session->customer["shipping_country"];
                break;
            case "shipping-state":
                //The state condition is stored in form of country-code|state-code
                $country = WC()->session->customer["shipping_country"];
                if(isset(WC()->session->customer["shipping_state"]))
                    $state = WC()->session->customer["shipping_state"];
                else
                    $state = WC()->session->customer["state"];
                $evaluable_condition = "$country|$state";
                break;
            case "shipping-method":
                $chosen_methods=WC()->session->get( 'chosen_shipping_methods' );
                $evaluable_condition = $chosen_methods[0];
                //Not sure why we have : in that string
                $dot_position=strpos( $evaluable_condition, ":");
                if($dot_position!==FALSE)
                        $evaluable_condition=  substr ($evaluable_condition, 0,$dot_position);
                break;
            case "customer-subscribed-newsletter-plugin":
                $evaluable_condition = false;
                if (is_user_logged_in()) {
                    $current_user_id = wp_get_current_user();
                    $evaluable_condition = wad_is_user_subscribed_to_newsletterplugin($current_user_id->user_email);
                }
                break;
            case "customer-subscribed-mailchimp":
                if (is_user_logged_in()) {
                    $evaluable_condition = wad_is_user_subscribed_to_mailchimp($rule["value"]);
                    if($rule["operator"] == 'NOT IN')
                        $evaluable_condition=(!$evaluable_condition);
                }
                break;
            case "customer-subscribed-sendinblue":
                $evaluable_condition = false;
                if (is_user_logged_in()) {
                    $evaluable_condition = wad_is_user_subscribed_to_sendinblue($rule["value"]);
                    if($rule["operator"] == 'NOT IN')
                        $evaluable_condition=(!$evaluable_condition);
                }
                break;
            case "customer-following-affiliation-link":
                $evaluable_condition = wad_is_user_following_affiliation_link($rule["value"]);
                break;
            case "customer-group":
                $evaluable_condition = wad_get_user_groups();
                break;
            case "order-item-count-in-list":
                $calculate_per_product = get_proper_value($this->settings, "calculate-per-product", "no");
                if ($calculate_per_product == "yes")
                    $order_products_in_list = wad_get_order_products_in_list($rule, $product_id);
                else
                    $order_products_in_list = wad_get_order_products_in_list($rule);
                $count = array_sum($order_products_in_list);
                $operator = $rule["order-item-count"]["operator"];
                $value = $rule["order-item-count"]["value"];
                if($operator =='%'){
                    if($count>0 && ($count % intval($value) == 0))
                        $evaluable_condition = true;
                }
                else{
                    $evaluable_condition = wad_evaluate_conditions($count,$operator,$value);
                }
                break;
            case "different-order-item-count-in-list":
                $calculate_per_product = get_proper_value($this->settings, "calculate-per-product", "no");
                if ($calculate_per_product == "yes")
                    $different_order_products_in_list = wad_get_order_products_in_list($rule, $product_id);
                else
                    $different_order_products_in_list = wad_get_order_products_in_list($rule);
                $count = count($different_order_products_in_list);
                $operator = $rule["different-order-item-count"]["operator"];
                $value = $rule["different-order-item-count"]["value"];
                if($operator =='%'){
                    if($count>0 && ($count % intval($value) == 0))
                        $evaluable_condition = true;
                }
                else{
                    $evaluable_condition = wad_evaluate_conditions($count,$operator,$value);
                }
                break;
            case "previously-ordered-products-count":
                $number_bought_from_list = wad_get_customer_previous_orders_products_count();
                $operator = $rule["operator"];
                $value = $rule["value"];
                if($operator =='%'){
                    if($number_bought_from_list>0 && ($number_bought_from_list % intval($value) == 0))
                        $evaluable_condition = true;
                }
                else{
                $evaluable_condition = wad_evaluate_conditions($number_bought_from_list,$operator,$value);
                }
                break;
            case "previously-ordered-products-in-list":
                $list=  get_proper_value($rule, "list", $rule["value"]);
                $number_bought_from_list = wad_get_customer_previous_orders_products_in_list($list);
                $operator = $rule["operator"];
                $value = $rule["value"];
                $evaluable_condition = $number_bought_from_list;
                if($rule["operator"] == 'NOT IN')
                        $evaluable_condition=(!$evaluable_condition);
                break;
            case "previously-ordered-products-count-in-list":
                //$number_bought_from_list = $rule["calculated-count"];
                $operator = $rule["previously-ordered-products-count"]["operator"];
                $value = $rule["previously-ordered-products-count"]["value"];
                $previously_ordered_products_in_list_rules = $rule["previously-ordered-products-in-list"];
                $list = $previously_ordered_products_in_list_rules["value"];
                $number_bought_from_list = wad_get_customer_previous_orders_products_in_list($list);
                if($operator =='%'){
                    if($number_bought_from_list>0 && ($number_bought_from_list % intval($value) == 0))
                        $evaluable_condition = true;
                }
                else{
                $evaluable_condition = wad_evaluate_conditions($number_bought_from_list,$operator,$value);
                }
                break;
            case "shop-currency":
                $evaluable_condition = get_woocommerce_currency();
                break;
            case "email-domain-name":
                if (is_user_logged_in()) {
                    $current_user_id = wp_get_current_user();
                    $customer_email = $current_user_id->user_email;
                }else{
                    $customer_email = WC()->session->customer["email"];
                    //$customer_email = $_SESSION[ "billing_email" ];
                }
                    $values = explode(',', $rule['value']);
                    if($rule["operator"] == 'IN')
                        $evaluable_condition=false;
                    else if($rule["operator"] == 'NOT IN')
                        $evaluable_condition=true;

                    foreach($values as $value){
                    $is_email_domain=o_endsWith($customer_email, trim($value));
                        if($rule["operator"] == 'IN' && $is_email_domain)
                        {
                            $evaluable_condition=true;
                            break;
                        }
                        else if($rule["operator"] == 'NOT IN' && $is_email_domain)
                        {
                            $evaluable_condition=false;
                            break;
                        }
                    }
                    return $evaluable_condition;
                break;
            default :
                $evaluable_condition = apply_filters("wad_get_evaluable_condition", false, $rule, $product_id); //false;
                break;
        }

        return $evaluable_condition;
    }

    function is_applicable($product_id = false) {
        $is_valid = true;
        if(WC()->cart){
            foreach(WC()->cart->get_applied_coupons() as $coupon_code){
                $c = new WC_Coupon($coupon_code);
                if($c->discount_type!="smart_coupon"){
                    $is_valid=  $this->is_applicable;
                    return apply_filters('wad_is_applicable', $is_valid, $this, $product_id);
                }
            }
        }
        if($this->rules_verified!==true)
        {
            if (!isset($this->settings["rules"]) || !is_array($this->settings["rules"])) {
                $this->settings["rules"] = array();
            }
            foreach ($this->settings["rules"] as $group) {
                foreach ($group as $rule) {
                    $is_valid = $this->is_rule_valid($rule, $product_id);
                    //Group is not valid
                    if (!$is_valid) {
                        break;
                    }
                }
                //If one rule of the group is not valid in a AND case, then the group is not valid
                if ($this->settings["relationship"] == "AND" && !$is_valid) {
                    break;
                }
                //If one group is valid in a OR case, then the discount is valid
                if ($this->settings["relationship"] == "OR" && $is_valid)
                    break;
            }

            if($this->evaluable_per_product==FALSE)
            {
                $this->rules_verified=true;
                $this->is_applicable=$is_valid;
            }

        }
        else
            $is_valid=  $this->is_applicable;
        return apply_filters('wad_is_applicable', $is_valid, $this, $product_id);
    }

    function get_discounts_conditions() {
        return apply_filters('wad_get_discounts_conditions', array(
            "customer-role" => __("If Customer role", "wad"),
            "email-domain-name" => __("If Customer email domain name", "wad"),
            "customer" => __("If Customer", "wad"),
            "previous-order-count" => __("If Previous orders count", "wad"),
            "total-spent-on-shop" => __("If Total spent in shop", "wad"),
            "previously-ordered-products-count" => __("If Previously ordered products count", "wad"),
            "previously-ordered-products-in-list" => __("If Previously ordered products", "wad"),
            "order-subtotal" => __("If Order subtotal", "wad"),
            "order-subtotal-inc-taxes" => __("If Order subtotal (inc. taxes)", "wad"),
            "order-item-count" => __("If Order items count", "wad"),
            "different-order-item-count" => __("If Different Order items count", "wad"),
            "order-products" => __("If Order products", "wad"),
            "customer-reviewed-product" => __("If Customer reviewed any product", "wad"),
//            "customer-reviewed-product-only" => __("If Customer reviewed a product", "wad"),
            "payment-gateway" => __("If Payment gateway", "wad"),
            "billing-country" => __("If Customer billing country", "wad"),
            "billing-state" => __("If Billing state", "wad"),
            "shipping-country" => __("If Shipping country", "wad"),
            "shipping-state" => __("If Shipping state", "wad"),
            "shipping-method" => __("If Shipping method", "wad"),
            "customer-subscribed-mailchimp" => __("If Customer subscribed to Mailchimp list", "wad"),
            "customer-subscribed-sendinblue" => __("If Customer subscribed to a Sendinblue list", "wad"),
            "customer-subscribed-newsletter-plugin" => __("If Customer subscribed to a NewsletterPlugin list", "wad"),
            "customer-following-affiliation-link" => __("If Customer is following an affiliation link", "wad"),
            "customer-group" => __("If Customer belongs to specified groups", "wad"),
            "shop-currency" => __("If shop currency", "wad"),
        ));
    }

    function get_discounts_actions() {
        return apply_filters('wad_get_discount_action', array(
            "percentage-off-pprice" => __("Percentage off product price", "wad"),
            "fixed-amount-off-pprice" => __("Fixed amount off product price", "wad"),
            "fixed-pprice" => __("Fixed product price", "wad"),
            "percentage-off-cprice" => __("Percentage off cheapest product price in cart", "wad"),
            "fixed-amount-off-cprice" => __("Fixed amount off cheapest product price in cart", "wad"),
            "percentage-off-lsubtotal" => __("Percentage off product with lowest subtotal in cart", "wad"),
            "fixed-amount-off-lsubtotal" => __("Fixed amount off product with lowest subtotal in cart", "wad"),
            "percentage-off-osubtotal" => __("Percentage off order subtotal", "wad"),
            "percentage-off-osubtotal-inc-taxes" => __("Percentage off order subtotal (inc. taxes)", "wad"),
            "fixed-amount-off-osubtotal" => __("Fixed amount off order subtotal", "wad"),
            "fixed-amount-off-osubtotal-inc-taxes" => __("Fixed amount off order subtotal (inc. taxes)", "wad"),
            "percentage-off-shipping-fee" => __("Percentage off shipping fees", "wad"),
            "fixed-amount-off-shipping-fee" => __("Fixed amount off shipping fees", "wad"),
            "fixed-shipping-fee" => __("Fixed shipping fees", "wad"),
            "free-gift" => __("Free gift", "wad"),
        ));
    }

    public function get_sale_price($sale_price, $product) {
        //We're still in the init_globals() so we don't need to run yet\
        if(WC()->cart){
            foreach(WC()->cart->get_applied_coupons() as $coupon_code){
                $c = new WC_Coupon($coupon_code);
                if($c->discount_type!="smart_coupon"){
                    return $sale_price;
                }
            }
        }
        //global $woocommerce;

        if (!defined('WAD_INITIALIZED') || !get_option('wad-license-key') )
            return $sale_price;
        global $wad_discounts;
        global $cheapest_products_list;
        global $smalest_subtotal_products;
        if(empty($cheapest_products_list))
            $cheapest_products_list = array();
        if(empty($smalest_subtotal_products))
            $smalest_subtotal_products = array();

        if (isset($product->aelia_cs_conversion_in_progress) && !empty($product->aelia_cs_conversion_in_progress))
            return $sale_price;

        if (is_admin() && !is_ajax() /* || empty($sale_price) */)
            return $sale_price;

        $pid = wad_get_product_id_to_use($product);

        if (empty($sale_price))
        {
            global $wad_ignore_product_prices_calculations;
            $previous_value=$wad_ignore_product_prices_calculations;
            $wad_ignore_product_prices_calculations=TRUE;
            $regular_price = $product->get_regular_price();
            $sale_price= $regular_price;
            $wad_ignore_product_prices_calculations=$previous_value;
        }

        foreach ($wad_discounts["product"] as $discount_id => $discount_obj) {
            $discount_type = $discount_obj->settings['action'];

            $disable_on_products_pages = get_proper_value($discount_obj->settings, "disable-on-product-pages", "no");
            //Even If the discount is disabled on the shop pages, we force it to be enabled in the minicart even if this minicart is on the shop pages
            if($disable_on_products_pages && did_action('woocommerce_before_mini_cart_contents') && !did_action('woocommerce_after_mini_cart'))
                $disable_on_products_pages=false;
//            if ($disable_on_products_pages == "yes" && (is_singular("product") || is_shop() || is_product_category() || is_front_page()))
            if($disable_on_products_pages == "yes" && (!is_cart() && !is_checkout()))
                continue;

            if ($discount_obj->is_applicable($pid)) {
                $list_products = $discount_obj->products_list->get_products();
                if(is_array($list_products) && in_array($pid, $list_products))
                {
                    $cheapest_product_based_actions = wad_get_cheapest_product_based_actions();
                    $smaller_subtotal_based_actions = wad_get_smaller_item_subtotal_based_actions();
                    if( in_array($discount_type, $cheapest_product_based_actions) && !in_array($pid, $cheapest_products_list) )
                            continue;
                    elseif(in_array($discount_type, $smaller_subtotal_based_actions) && !in_array($pid, $smalest_subtotal_products) )
                            continue;

                    $sale_price = floatval($sale_price) - $discount_obj->get_discount_amount(floatval($sale_price));
                    // We save the discount to use it later when completing the payment.
                    global $wad_active_discounts;
                    if ( ! in_array( $discount_id, $wad_active_discounts, true ) && wad_is_checkout() ) {
                            array_push( $wad_active_discounts, $discount_id );
                    }

                }
            }
        }

        //We check if there is a quantity pricing in order to apply that discount in the cart or checkout pages
        if (is_cart() || wad_is_checkout()) {
            $sale_price = $this->apply_quantity_based_discount_if_needed($product, $sale_price);
            foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
                if( isset( $cart_item['wad_free_gift'] ) && ( $cart_item['data']->get_id() == $pid ) && ($cart_item['data'] == $product  ) )  {
                    $sale_price = 0;
                }
            }
            // If product's sale price changed, we must update the product too,
            // so that other parties can access it
            $product->sale_price = $sale_price;
        }

        return $sale_price;
    }

    public function get_regular_price($regular_price, $product) {
        //We're still in the init_globals() so we don't need to run yet
        if (!defined('WAD_INITIALIZED') || !get_option('wad-license-key'))
            return $regular_price;
        global $wad_discounts;
        global $wad_ignore_product_prices_calculations;
        global $cheapest_products_list;
        global $smalest_subtotal_products;

        if(empty($cheapest_products_list))
            $cheapest_products_list = array();
        if(empty($smalest_subtotal_products))
            $smalest_subtotal_products = array();

        if (is_admin() && !is_ajax() || $wad_ignore_product_prices_calculations)
            return $regular_price;

        $pid = wad_get_product_id_to_use($product);

        foreach ($wad_discounts["product"] as $discount_id => $discount_obj) {
            $discount_type = $discount_obj->settings['action'];
            $list_products = $discount_obj->products_list->get_products();

            $disable_on_products_pages = get_proper_value($discount_obj->settings, "disable-on-product-pages", "no");
            if ($disable_on_products_pages == "yes" && (is_singular("product") || is_shop() || is_product_category() || is_front_page()))
                continue;
            if ($discount_obj->is_applicable($pid) && is_array($list_products) && in_array($pid, $list_products)) {
                $cheapest_product_based_actions = wad_get_cheapest_product_based_actions();
                $smaller_subtotal_based_actions = wad_get_smaller_item_subtotal_based_actions();
                if( in_array($discount_type, $cheapest_product_based_actions) && !in_array($pid, $cheapest_products_list) )
                        continue;
                elseif(in_array($discount_type, $smaller_subtotal_based_actions) && !in_array($pid, $smalest_subtotal_products) )
                        continue;

                $regular_price-=$discount_obj->get_discount_amount($regular_price);

                global $wad_active_discounts;
                if ( ! in_array( $discount_id, $wad_active_discounts, true ) ) {
                        array_push( $wad_active_discounts, $discount_id );
                }
            }
        }

        //We check if there is a quantity pricing in order to apply that discount in the cart or checkout pages
        if (is_cart() || wad_is_checkout()) {
            $regular_price = $this->apply_quantity_based_discount_if_needed($product, $regular_price);
        }
//        var_dump($regular_price);
        return $regular_price;
    }

    public function get_cheapest_products(){
        global $woocommerce;
        global $wad_discounts;
        global $cheapest_products_list;
        global $smalest_subtotal_products;
        $cheapest_products_list = array();
        $smalest_subtotal_products = array();

        if(!isset($wad_discounts["product"]) || !is_array($wad_discounts["product"]))
            return;

        foreach ($woocommerce->cart->cart_contents as $cart_item_data){
            $sale_price = floatval($cart_item_data['data']->get_sale_price());
            if(empty($sale_price))
                $sale_price = floatval($cart_item_data['data']->get_price());
            $cart_cheapest_products[$cart_item_data['data']->get_id()] = $sale_price;
            $cart_smaller_sproducts[$cart_item_data['data']->get_id()] = $sale_price*intval($cart_item_data['quantity']);
        }

        foreach ($wad_discounts["product"] as $discount_obj) {
            if(!$discount_obj->is_applicable())
                continue;
            
            $nb_cheapest_products = get_proper_value($discount_obj->settings, 'nb-cheapest-products', 1);
            $discount_type = $discount_obj->settings['action'];
            $cheapest_product_based_actions = wad_get_cheapest_product_based_actions();
            $smaller_subtotal_based_actions = wad_get_smaller_item_subtotal_based_actions();
            
            if(in_array($discount_type, $cheapest_product_based_actions)){
                asort($cart_cheapest_products);
                $cart_cheapest_products = array_keys($cart_cheapest_products);
                $cheapest_products_list = array_slice($cart_cheapest_products, 0, $nb_cheapest_products);
            }
            elseif(in_array($discount_type, $smaller_subtotal_based_actions)){
                asort($cart_smaller_sproducts);
                $cart_smaller_sproducts = array_keys($cart_smaller_sproducts);
                $smalest_subtotal_products = array_slice($cart_smaller_sproducts, 0, $nb_cheapest_products);
            }

        }

    }

    private function apply_quantity_based_discount_if_needed($product, $normal_price) {
        global $wad_cart_total_without_taxes;
        global $wad_cart_total_inc_taxes;
        global $woocommerce;
        global $wad_settings;
        $inc_shipping_in_taxes = get_proper_value($wad_settings, 'inc-shipping-in-taxes', 'Yes');
        //We check if there is a quantity based discount for this product
        $product_type=$product->get_type();
        $id_to_check = $product->get_id();



        if($product_type=="variation")
        {
            $parent_product=$product->get_parent_id();
            $quantity_pricing = get_post_meta($parent_product, "o-discount", true);
        }
        else
        {
            $quantity_pricing = get_post_meta($id_to_check, "o-discount", true);
        }


        $products_qties = $this->get_cart_item_quantities();
        $rules_type = get_proper_value($quantity_pricing, "rules-type", "intervals");
        $use_tiering_price = get_proper_value($quantity_pricing, 'tiered', 'no');

		$original_normal_price = $normal_price;
		$product_qty           = $products_qties[$id_to_check];

        if (!isset($product_qty) || empty($quantity_pricing) || !isset($quantity_pricing["enable"]))
        {
            return $normal_price;
        }

        if (isset($quantity_pricing["rules"]) && $rules_type == "intervals") {
            $prev = array();
            foreach ($quantity_pricing["rules"] as $rule) {
                //if ($rule["min"] <= $products_qties[$id_to_check] && $products_qties[$id_to_check] <= $rule["max"]) {
                if (
                        ($rule["min"] === "" && $product_qty <= $rule["max"]) || ($rule["min"] === "" && $rule["max"] === "") || ($rule["min"] <= $product_qty && $rule["max"] === "") || ($rule["min"] <= $product_qty && $product_qty <= $rule["max"])
                ) {
                    if ($quantity_pricing["type"] == "fixed"){
                        if((!$prev || $use_tiering_price == 'no') || ($use_tiering_price == 'yes' && !$prev))
                            $normal_price-=$rule["discount"];
                        elseif($use_tiering_price == 'yes')
                            $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], $prev, $rule);
                    }
                    else if ($quantity_pricing["type"] == "percentage"){
                        if((!$prev || $use_tiering_price == 'no') || ($use_tiering_price == 'yes' && !$prev))
                            $normal_price-=($normal_price * $rule["discount"]) / 100;
                        elseif($use_tiering_price == 'yes')
                            $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], $prev, $rule);
                    }
                    else if ($quantity_pricing["type"] == "n-free") {
                        $normal_price = wad_get_product_free_gift_price($original_normal_price, $product_qty, $rule["discount"]);
                    }
                    else if ($quantity_pricing["type"] == "fixedPrice") {
                        if((!$prev || $use_tiering_price == 'no') || ($use_tiering_price == 'yes' && !$prev))
                            $normal_price = $rule["discount"];
                        elseif($use_tiering_price == 'yes')
                            $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], $prev, $rule);
                    }

                    break;
                }
                $prev = $rule;
            }
        } else if (isset($quantity_pricing["rules-by-step"]) && $rules_type == "steps") {

            foreach ($quantity_pricing["rules-by-step"] as $rule) {
                if ($product_qty % $rule["every"] == 0) {
                    if ($quantity_pricing["type"] == "fixed")
                        $normal_price-=$rule["discount"];
                    else if ($quantity_pricing["type"] == "percentage")
                        $normal_price-=($normal_price * $rule["discount"]) / 100;
                    else if ($quantity_pricing["type"] == "n-free") {
                        $normal_price = wad_get_product_free_gift_price($original_normal_price, $product_qty, $rule["discount"]);
                    }
                    else if ($quantity_pricing["type"] == "fixedPrice") {
                        $normal_price = $rule["discount"];
                    }
                    
                    break;
                }elseif ( ($product_qty % $rule["every"] != 0) && ($product_qty > $rule["every"]) && ($use_tiering_price == 'yes')) {
                    if ($quantity_pricing["type"] == "fixed")
                        $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], false, $rule);
                    else if ($quantity_pricing["type"] == "percentage")
                        $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], false, $rule);
                    else if ($quantity_pricing["type"] == "fixedPrice") {
                        $normal_price = $this->get_tiering_price($normal_price, $product_qty, $rules_type, $quantity_pricing["type"], false, $rule);
                    }else if($quantity_pricing["type"] == "n-free"){
                        $normal_price = wad_get_product_free_gift_price($original_normal_price, $product_qty, $rule["discount"]);
                    }
                    
                    break;
                }
                
            }
        }
        $wad_cart_total_without_taxes = $woocommerce->cart->subtotal_ex_tax;
        if( version_compare( WC()->version , "3.2.1", "<" ) )
                $taxes=$woocommerce->cart->taxes;
        else
            $taxes=$woocommerce->cart->get_cart_contents_taxes();
        $wad_cart_total_inc_taxes = floatval($woocommerce->cart->subtotal_ex_tax) + array_sum($taxes);
        if(isset($woocommerce->cart->tax_total) && $woocommerce->cart->tax_total>0 && empty($taxes))
        {
            $wad_cart_total_inc_taxes+=$woocommerce->cart->tax_total;
        }
        if ($inc_shipping_in_taxes == 'Yes')
            $wad_cart_total_inc_taxes += $woocommerce->cart->shipping_total;
        return $normal_price;
    }

    private function get_tiering_price($normal_price, $product_qty, $rule_type, $pricing_type, $prev, $current){
        if($rule_type == "intervals"){
            if($pricing_type == 'fixedPrice'){
                $remaining_qty = $product_qty-intval($prev["max"]);
                $prev_discount = floatval($prev["discount"])*floatval($prev["max"]);
                $current_discount = floatval($current["discount"])*floatval($remaining_qty);
                $normal_price = ($prev_discount+$current_discount)/$product_qty;
            }elseif ($pricing_type == 'fixed') {
                $remaining_qty = $product_qty-intval($prev["max"]);
                $prev_discount = floatval($prev["discount"])*floatval($prev["max"]);
                $current_discount = floatval($current["discount"])*floatval($remaining_qty);
                $normal_price -= ($prev_discount+$current_discount)/$product_qty;
            }elseif ($pricing_type == 'percentage') {
                $remaining_qty = $product_qty-intval($prev["max"]);
                $prev_discount = ($normal_price-($normal_price*floatval($prev["discount"])/100))*intval($prev["max"]);
                $current_discount = ($normal_price-($normal_price*floatval($current["discount"])/100))*$remaining_qty;
                $normal_price = ($prev_discount+$current_discount)/$product_qty;
            }
        }elseif($rule_type = "steps"){
            if($pricing_type == 'fixedPrice'){
                $remaining_qty = $product_qty-intval($current["every"]);
                $every = intval($current["every"]);
                while($product_qty > $every){
                    $remaining_qty = $product_qty - $every;
                    $every +=intval($current["every"]);
                }
                $remaining_disc_qty = $product_qty-$remaining_qty;
                $prev_discount = floatval($current["discount"])*$remaining_disc_qty;
                $current_discount = $normal_price*floatval($remaining_qty);
                $normal_price = ($prev_discount+$current_discount)/$product_qty;
            }elseif ($pricing_type == 'fixed') {
                $remaining_qty = $product_qty-intval($current["every"]);
                $every = intval($current["every"]);
                while($product_qty > $every){
                    $remaining_qty = $product_qty - $every;
                    $every +=intval($current["every"]);
                }
                $remaining_disc_qty = $product_qty-$remaining_qty;
                $prev_discount = ($normal_price-floatval($current["discount"]))*$remaining_disc_qty;
                $current_discount = $normal_price*floatval($remaining_qty);
                $normal_price = ($prev_discount+$current_discount)/$product_qty;
            }elseif ($pricing_type == 'percentage') {
                $remaining_qty = $product_qty-intval($current["every"]);
                $every = intval($current["every"]);
                while($product_qty > $every){
                    $remaining_qty = $product_qty - $every;
                    $every +=intval($current["every"]);
                }
                $remaining_disc_qty = $product_qty-$remaining_qty;
                $prev_discount = ($normal_price-($normal_price*floatval($current["discount"])/100))*$remaining_disc_qty;
                $current_discount = $normal_price*floatval($remaining_qty);
                $normal_price = ($prev_discount+$current_discount)/$product_qty;
            }
        }

        return $normal_price;
    }

    function get_free_gifts_table() {
        global $wad_discounts;
        $all_discounts = $wad_discounts;
        foreach ($all_discounts["order"] as $discount_id => $discount_obj) {
            if ($discount_obj->settings["action"] != "free-gift" || !$discount_obj->is_applicable())
                continue;

            $list_products = $discount_obj->products_list->get_products(true); //$discount_obj->products;
            //$products_in_cart = wad_get_cart_products(); //array_keys($woocommerce->cart->get_cart_item_quantities());
            //$gifts_in_cart= array_intersect($products_in_cart, $list_products);
            $gifts_in_cart = $this->get_gift_count_in_cart( $discount_id );
            /*foreach($gifts_in_cart as $index => $gift_id){
                if(!$this->is_free_gift($gift_id, $discount_id))
                    unset($gifts_in_cart[$index]);
            }*/
            $gifts_limit=  get_proper_value( $discount_obj->settings, 'nb-gifts-limit', 1 );
            if ( $gifts_in_cart < intval( $gifts_limit ) ) {
                echo "<h2>" . __("You earned a free gift!", "wad") . "</h2>";
                ?>
                <table class="shop_table cart" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-thumbnail">&nbsp;</th>
                            <th class="product-name"><?php _e("Product", "wad"); ?></th>
                            <th class="product-price" style="width: 50%"><?php _e("Action", "wad"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($list_products as $product_id) {
                            $_product = wc_get_product($product_id);
                            if($_product->get_type()=="variable" /*|| in_array( $product_id, $gifts_in_cart)*/)
                                continue;
                            ?>
                            <tr class="cart_item">

                                <td class="product-thumbnail">
                                    <?php
                                    $thumbnail = $_product->get_image();

                                    if (!$_product->is_visible()) {
                                        echo $thumbnail;
                                    } else {
                                        printf('<a href="%s">%s</a>', esc_url($_product->get_permalink()), $thumbnail);
                                    }
                                    ?>
                                </td>

                                <td class="product-name">
                                    <?php
                                    echo sprintf('<a href="%s">%s </a>', esc_url($_product->get_permalink()), $_product->get_name());
                                    ?>
                                    <dl class="variation">
                                        <?php foreach ( $this->variation_in($_product) as $data ) : ?>
                                            <dt class="<?php echo sanitize_html_class( 'variation-' . $data['key'] ); ?>"><?php echo wp_kses_post( $data['key'] ); ?>:</dt>
                                            <dd class="<?php echo sanitize_html_class( 'variation-' . $data['key'] ); ?>"><?php echo wp_kses_post( wpautop( $data['display'] ) ); ?></dd>
                                        <?php endforeach; ?>
                                    </dl>
                                </td>
                                <td>
                                    <a class="button wad-add-gift-to-cart" href="?add-to-cart=<?php echo $product_id; ?>"><?php _e("Add to cart", "wad"); ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>

                    </tbody>
                </table>
                <?php
            }
        }
    }
    public function variation_in($product){
        $item_data = array();

        // Variation values are shown only if they are not found in the title as of 3.0.
        // This is because variation titles display the attributes.
        if ( $product->is_type('variation') && is_array( $product->get_variation_attributes() ) ) {

            foreach ( $product->get_variation_attributes() as $name => $value ) {
                $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

                if ( taxonomy_exists( $taxonomy ) ) {
                    // If this is a term slug, get the term's nice name.
                    $term = get_term_by( 'slug', $value, $taxonomy );
                    if ( ! is_wp_error( $term ) && $term && $term->name ) {
                        $value = $term->name;
                    }
                    $label = wc_attribute_label( $taxonomy );
                } else {
                    // If this is a custom option slug, get the options name.
                    $value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $product->get_data() );
                    $label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $product->get_data() );
                }

                // Check the nicename against the title.
                if ( '' === $value || wc_is_attribute_in_product_name( $value, $product->get_name() ) ) {
                    continue;
                }

                $item_data[] = array(
                    'key'   => $label,
                    'value' => $value,
                );
            }
        }
        $item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $product );

        // Format item data ready to display.
        foreach ( $item_data as $key => $data ) {
            // Set hidden to true to not display meta on cart.
            if ( ! empty( $data['hidden'] ) ) {
                unset( $item_data[ $key ] );
                continue;
            }
            $item_data[ $key ]['key']     = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
            $item_data[ $key ]['display'] = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
        }
        return $item_data;
    }

    private function is_free_gift($item_id, $discount_id){
        $is_gift = false;
        if(WC()->cart->get_cart_contents_count() > 0){
            foreach(WC()->cart->get_cart_contents() as $item_key => $values){
                if( isset( $values['wad_free_gift'] ) && ( $values['wad_free_gift'] == $discount_id) && ( $values['product_id'] == $item_id || $values['variation_id'] == $item_id ) ) {
                    $is_gift = true;
                    break;
                }
            }
        }
        return $is_gift;
    }

    function update_free_gifts_in_cart() {
        global $wad_discounts;
        global $wad_settings;
        $add_automatically = get_proper_value($wad_settings,'free-gift-auto', '1');

        $all_discounts = $wad_discounts;
        $is_free_gift=0;
        foreach ($all_discounts["order"] as $discount_id => $discount_obj) {

            if ( $discount_obj->settings["action"] != "free-gift" )
                continue;
            $is_free_gift++;
            $list_id = $discount_obj->settings['gift-list'];
            $list = new WAD_Products_List( $list_id );
            $list_products = $list->get_products(true);
            $gifts_limit=  intval( get_proper_value( $discount_obj->settings, 'nb-gifts-limit', 1 ) );
            $find = false;
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                //remove free gift when discount is no longer applicable
                if( !$discount_obj->is_applicable() && isset($values['wad_free_gift'] ) && $values['wad_free_gift'] == $discount_id ){
                    $find = true;
                    unset( $values['wad_free_gift'] );
                    WC()->cart->remove_cart_item( $cart_item_key );
                    continue;
                }
                elseif( $discount_obj->is_applicable() && count($list_products) == 1 && sizeof( WC()->cart->get_cart() ) > 0 ){
                    $product = $values['data'];
                    if( $list_products[0] == $product->get_id() && isset($values['wad_free_gift'] ) ){
                        $find=true;
                        break;
                    }
                }
                
            }
            if( $discount_obj->is_applicable() && $find === false && $add_automatically === '1' && count($list_products) == 1 ) {
                $free_gift_key = WC()->cart->add_to_cart( $list_products[0], $gifts_limit, false, false, array( 'wad_free_gift' => $discount_id ) );
            }
        }
        
        if($is_free_gift==0){
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                //remove free gift when discount is no longer applicable
                if(isset($values['wad_free_gift'] )){
                    unset( $values['wad_free_gift'] );
                    WC()->cart->remove_cart_item( $cart_item_key );
                }

            }
        }
        if ( isset( $_GET['add-to-cart'] ) && !is_admin() ) {
                $referer = wp_get_referer();
                if ( is_cart() || is_checkout() ){
                    wp_safe_redirect( $referer );
                    exit;
                }
        }
    }

    function add_free_gift_meta_data( $cart_item_data, $product_id, $variation_id ) {
        global $wad_discounts;
        $all_discounts = $wad_discounts;
        foreach ( $all_discounts["order"] as $discount_id => $discount_obj ) {
            $gifts_limit=  get_proper_value($discount_obj->settings, 'nb-gifts-limit', 1);
            if ( $discount_obj->settings["action"] == 'free-gift' && $discount_obj->is_applicable() ){
                $list_products = $discount_obj->products_list->get_products(true);
                if( ( in_array( $product_id, $list_products ) || in_array( $variation_id, $list_products ) ) && ! isset( $cart_item_data['wad_free_gift'] ) && $this->get_gift_count_in_cart( $discount_id ) < intval( $gifts_limit ) ) {
                    $cart_item_data['wad_free_gift'] = $discount_id;
                }
            }
        }
        return $cart_item_data;
    }

    private function get_gift_count_in_cart($discount_id){
        $items = WC()->cart->get_cart_contents();
        $gift_count=0;
        foreach($items as $item_key => $values){
            if(isset($values['wad_free_gift']) && $values['wad_free_gift'] == $discount_id)
                $gift_count += $values['quantity'];
        }
        return $gift_count;
    }

    function add_discount_desc_to_products_names($product_name, $cart_item, $cart_item_key) {
        global $wad_discounts;
        foreach ($wad_discounts["order"] as $discount_id => $discount_obj) {
            if ($discount_obj->settings["action"] != "free-gift" || !$discount_obj->is_applicable())
                continue;
//                            $list = $discount_obj->products_list;
            $list_products = $discount_obj->products_list->get_products(); //$discount_obj->products;
            $gift_label = get_proper_value( $discount_obj->settings, 'gift-label', 'Free gift');
            if ((in_array($cart_item["product_id"], $list_products)||in_array($cart_item["variation_id"], $list_products)) && isset($cart_item['wad_free_gift']) && $cart_item['wad_free_gift'] == $discount_id) {
                if ($cart_item["quantity"] == 1)
                    $msg = $gift_label;
                else
                    $msg = $gift_label . " : " . $cart_item["quantity"] . __(" offered", "wad");
                $product_name.="<br> $msg";
            }
        }
        return $product_name;
    }

    function save_used_discounts($order_id) {
        global $wad_active_discounts;
	if ( $wad_active_discounts ) {
            $used_discounts = array_unique( $wad_active_discounts );
            foreach ($used_discounts as $discount_id) {
                add_post_meta($order_id, "wad_used_discount", $discount_id);
                $discout_obj = new WAD_Discount($discount_id);
                add_post_meta($order_id, "wad_used_discount_settings_$discount_id", $discout_obj->settings);
            }
            unset( $wad_active_discounts );
        }
    }

    /**
     * Adds the Custom column to the default products list to help identify which ones are custom
     * @param array $defaults Default columns
     * @return array
     */
    function get_columns($defaults) {
        $defaults['wad_start_date'] = __('Start Date', 'wad');
        $defaults['wad_end_date'] = __('End Date', 'wad');
        $defaults['wad_active'] = __('Active', 'wad');
        return $defaults;
    }

    /**
     * Sets the Custom column value on the products list to help identify which ones are custom
     * @param type $column_name Column name
     * @param type $id Product ID
     */
    function get_columns_values($column_name, $id) {
        //global $wad_discounts;
        //var_dump($wad_discounts);
        $wad_discounts = wad_get_active_discounts();
        if ($column_name === 'wad_active') {
            $class = "";
//            $order_discounts_ids = array_map(create_function('$o', 'return $o->id;'), $wad_discounts["order"]);
//            $products_discounts_ids = array_map(create_function('$o', 'return $o->id;'), $wad_discounts["product"]);
            if (in_array($id, $wad_discounts))
                $class = "active";
            echo "<span class='wad-discount-status $class'></span>";
        }
        else if ($column_name === "wad_start_date") {
            $discount = new WAD_Discount($id);
            if (!$discount->settings) {
                echo "-";
                return;
            }
            $date_formatted = mysql2date(get_option('date_format'), $discount->settings["start-date"], false);
            $time_formatted = mysql2date(get_option('time_format'), $discount->settings["start-date"], false);
            $formatted_date = $date_formatted . ' ' . $time_formatted;
            echo $formatted_date;
        } else if ($column_name === "wad_end_date") {
            $discount = new WAD_Discount($id);
            if (!$discount->settings) {
                echo "-";
                return;
            }
            $date_formatted = mysql2date(get_option('date_format'), $discount->settings["end-date"], false);
            $time_formatted = mysql2date(get_option('time_format'), $discount->settings["end-date"], false);
            $formatted_date = $date_formatted . ' ' . $time_formatted;
            echo $formatted_date;
        }
    }

    /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label($tabs) {
        if(!is_array($tabs))
            return;

        $tabs['wad_quantity_pricing'] = array(
            'label' => __('Quantity Based Pricing', 'wad'),
            'target' => 'wad_quantity_pricing_data',
            'class' => array(),
        );
        return $tabs;
    }

    function get_product_tab_label_old() {
        ?>
        <li class="wad_quantity_pricing"><a href="#wad_quantity_pricing_data"><?php _e('Quantity Based Pricing', 'wad'); ?></a></li>
        <?php
    }

    function get_product_tab_data() {
//        var_dump("yes");
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wad-quantity-pricing-rules',
        );

        $discount_enabled = array(
            'title' => __('Enabled', 'wad'),
            'name' => 'o-discount[enable]',
            'type' => 'checkbox',
            'value' => 1,
            'desc' => __('Enable/Disable this feature', 'wad'),
            'default' => 0
        );

        $discount_type = array(
            'title' => __('Discount type', 'wad'),
            'name' => 'o-discount[type]',
            'type' => 'radio',
            'options' => array(
                "percentage" => __("Percentage off product price", "wad"),
                "fixed" => __("Fixed amount off product price", "wad"),
                "fixedPrice" => __("Fixed product price", "wad"),
                "n-free" => __("Give N for free", "wad"),
            ),
            'default' => 'percentage',
            'desc' => __('Apply a percentage or a fixed amount discount', 'wad'),
        );

        $rules_types = array(
            'title' => __('Rules type', 'wad'),
            'name' => 'o-discount[rules-type]',
            'type' => 'radio',
            'options' => array(
                "intervals" => __("Intervals", "wad"),
                "steps" => __("Steps", "wad"),
            ),
            'default' => 'intervals',
            'desc' => __('If Intervals, the intervals rules will be used.<br>If Steps, the steps rules will be used.', 'wad'),
        );

        $min = array(
            'title' => __('Min', 'wad'),
            'name' => 'min',
            'type' => 'number',
            'default' => '',
        );

        $max = array(
            'title' => __('Max', 'wad'),
            'name' => 'max',
            'type' => 'number',
            'default' => '',
        );

        $discount = array(
            'title' => __('Discount / Nb. Free', 'wad'),
            'name' => 'discount',
            'type' => 'number',
            'custom_attributes' => array("step" => "any"),
            'default' => '',
        );

        $discount_rules = array(
            'title' => __('Intervals rules', 'wad'),
            'desc' => __('If quantity ordered between Min and Max, then the discount specified will be applied. <br>Leave Min or Max empty for any value (joker).', 'wad'),
            'name' => 'o-discount[rules]',
            'type' => 'repeatable-fields',
            'id' => 'intervals_rules',
            'fields' => array($min, $max, $discount),
        );

        $every = array(
            'title' => __('Every X items', 'wad'),
            'name' => 'every',
            'type' => 'number',
            'default' => '',
        );

        $discount_rules_steps = array(
            'title' => __('Steps Rules', 'wad'),
            'desc' => __('If quantity ordered is a multiple of the step, then the discount specified will be applied.', 'wad'),
            'name' => 'o-discount[rules-by-step]',
            'type' => 'repeatable-fields',
            'id' => 'steps_rules',
            'fields' => array($every, $discount),
        );

        $tiered = array(
            'title' => __('Tiered pricing', 'wad'),
            'name' => 'o-discount[tiered]',
            'type' => 'radio',
            'default' => 'no',
            'desc' => __('Can the discounts be accumulated across defined intervals or steps? <br/><strong style="color:red;">Note</strong> : Enabling this will disable the pricing table on the product page. </span>', 'wad'),
            'options' => array(
                "yes" => "Yes",
                "no" => "No",
            )
        );

        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $discount_enabled,
            $discount_type,
            $rules_types,
            $discount_rules,
            $discount_rules_steps,
            $tiered,
            $end
        );
        ?>
        <div id="wad_quantity_pricing_data" class="panel woocommerce_options_panel wpc-sh-triggerable">
            <?php
            echo o_admin_fields($settings);
            ?>
        </div>
        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    function get_quantity_pricing_tables() {
        $product_id = get_the_ID();
        $product_obj = wc_get_product($product_id);
        $quantity_pricing = get_post_meta($product_id, "o-discount", true);
        $rules_type = get_proper_value($quantity_pricing, "rules-type", "intervals");
        $use_tiering_price = get_proper_value($quantity_pricing, 'tiered', 'no');

        ob_start();

        if (isset($quantity_pricing["enable"]) && (isset($quantity_pricing["rules"]) || isset($quantity_pricing["rules-by-step"])) && ($use_tiering_price=="no")) {
            ?>
            <h3><?php _e("Quantity based pricing table", "wad"); ?></h3>

            <?php
            if ($rules_type == "intervals") {
                if ($product_obj->get_type() == "variable") {
                    $available_variations = $product_obj->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $product_price = $variation["display_price"];
                        $this->get_quantity_pricing_table($variation["variation_id"], $quantity_pricing, $product_price);
                    }
                } else {
                    //$product_price = $product_obj->price;
                    //$product_price = wad_get_product_price($product_obj); //$product_obj->get_price();
                    $product_price = $product_obj->get_price();
                    $this->get_quantity_pricing_table($product_id, $quantity_pricing, $product_price, true);
                }
            } else if ($rules_type == "steps") {

                if ($product_obj->get_type() == "variable") {
                    $available_variations = $product_obj->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $product_price = $variation["display_price"];
                        $this->get_steps_quantity_pricing_table($variation["variation_id"], $quantity_pricing, $product_price);
                    }
                } else {
                    //$product_price = $product_obj->price;
                    //$product_price = wad_get_product_price($product_obj); //$product_obj->get_price();
                    $product_price = $product_obj->get_price();
                    $this->get_steps_quantity_pricing_table($product_id, $quantity_pricing, $product_price, true);
                }
            }
        }
        $table=ob_get_clean();
        echo apply_filters('wad_get_quantity_pricing_tables', $table, $product_id, $product_obj);
    }

    private function get_steps_quantity_pricing_table($product_id, $quantity_pricing, $product_price, $display = false) {
        $style = "";
        if (!$display)
            $style = "display: none;";
        ?>
        <table class="wad-qty-pricing-table" data-id="<?php echo $product_id; ?>" style="<?php echo $style; ?>">
            <thead>
                <tr>
                    <th><?php _e("Every multiple of", "wad"); ?></th>
                    <th><?php _e("Unit Price", "wad"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($quantity_pricing["rules-by-step"] as $rule) {
                    if ($quantity_pricing["type"] == "fixed") {
                        $price = $product_price - $rule["discount"];
                    } else if ($quantity_pricing["type"] == "percentage") {
                        $price = $product_price - ($product_price * $rule["discount"]) / 100;
                    }else if ($quantity_pricing["type"] == "fixedPrice") {
                        $price = $rule["discount"];
                    }
                    ?>
                    <tr>
                        <td><?php echo $rule["every"]; ?></td>
                        <td><?php echo wc_price($price); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }

    private function get_quantity_pricing_table($product_id, $quantity_pricing, $product_price, $display = false) {
        $style = "";
        if (!$display)
            $style = "display: none;";
        ?>
        <table class="wad-qty-pricing-table" data-id="<?php echo $product_id; ?>" style="<?php echo $style; ?>">
            <thead>
                <tr>
                    <th><?php _e("Min", "wad"); ?></th>
                    <th><?php _e("Max", "wad"); ?></th>
                    <th><?php _e("Unit Price", "wad"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($quantity_pricing["rules"] as $rule) {
                    if ($quantity_pricing["type"] == "fixed") {
                        $price = $product_price - $rule["discount"];
                    } else if ($quantity_pricing["type"] == "percentage") {
                        $price = $product_price - ($product_price * $rule["discount"]) / 100;
                    } else if ($quantity_pricing["type"] == "n-free") {
                        if ($rule["min"])
                            $quantity_to_check = $rule["min"];
                        else
                            $quantity_to_check = $rule["max"];

                        $price = $normal_price = wad_get_product_free_gift_price($product_price, $quantity_to_check, $rule["discount"]);
                    }else if ($quantity_pricing["type"] == "fixedPrice") {
                        $price = $rule["discount"];
                    }
                    ?>
                    <tr>
                        <td><?php echo $rule["min"]; ?></td>
                        <td><?php if(empty($rule["max"])) _e('And more.', 'wad'); else echo $rule["max"]; ?></td>
                        <td><?php echo wc_price($price); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }

    function get_cart_item_quantities() {
        global $woocommerce;
        $item_qties = array();
        if(isset($woocommerce->cart->cart_contents) && is_array( $woocommerce->cart->cart_contents))
        {
            foreach ($woocommerce->cart->cart_contents as $cart_item) {
                if (!empty($cart_item["variation_id"]))
                    $item_qties[$cart_item["variation_id"]] = $cart_item["quantity"];
                else
                    $item_qties[$cart_item["product_id"]] = $cart_item["quantity"];
            }
        }
        return $item_qties;
    }

    function initialize_used_discounts_array() {
        //We hide the notices that this triggers when the debug mode is on.
        global $wad_active_discounts;
        $default_error_level = error_reporting();
        error_reporting(0);
        if (!is_admin() && !wad_is_checkout())
            $wad_active_discounts = array();
        error_reporting($default_error_level);
    }

    function get_variations_prices($prices, $product) {
        foreach ($prices["regular_price"] as $variation_id => $variation_price) {
            $variation = wc_get_product($variation_id);

            $variation_sale_price = $prices["sale_price"][$variation_id];
            $prices["sale_price"][$variation_id] = $this->get_sale_price($variation_sale_price, $variation);

            $variation_price = $prices["price"][$variation_id];
            $prices["price"][$variation_id] = $this->get_sale_price($variation_price, $variation);
        }
        asort($prices["price"]);
        asort($prices["regular_price"]);
        asort($prices["sale_price"]);

        return $prices;
    }

    /**
     * Build the new discounts if some couples are found in the rules
     */
    private function init_coupled_rules_if_needed() {
        global $wad_settings;
        global $wad_last_products_fetch;
        if(!$wad_last_products_fetch)
            $wad_last_products_fetch = wad_get_cart_products ();
        if (isset($this->settings["rules"])) {
            //Some rules can be coupled to produce new ones
            foreach ($this->settings["rules"] as $i => $group) {
                //order-products + order-item-count
                $order_item_count_rules = array_filter($group, "wad_filter_order_item_count");
                $order_products_rules = array_filter($group, "wad_filter_order_products");
                $order_subtotal_rules = array_filter($group, "wad_filter_order_subtotal");

                //order-products + different-order-item-count
                $different_order_item_count_rules = array_filter($group, "wad_filter_different_order_item_count");

                //previously-ordered-products-count + previously-ordered-products-in-list
                $previously_ordered_products_count_rules = array_filter($group, "wad_filter_previously_ordered_products_count");
                $previously_ordered_products_in_list_rules = array_filter($group, "wad_filter_previously_ordered_products_in_list");

                $previous_order_count_rules = array_filter($group, "wad_filter_previous_order_count");
                $total_spent_on_shop_rules = array_filter($group, "wad_filter_total_spent_on_shop");

                $product_review_rules = array_filter($group, "wad_filter_product_review");
                $product_review_only_rules = array_filter($group, "wad_filter_product_review_only");
                $order_sharing_rules = array_filter($group, "wad_filter_product_shares");
                if (!empty($order_item_count_rules) && !empty($order_products_rules)) {
                    $k1 = array_keys($order_item_count_rules);
                    $k2 = array_keys($order_products_rules);
                    $k = array_merge($k1, $k2);

                    $order_item_count_rules = current($order_item_count_rules);
                    $order_products_rules = current($order_products_rules);


                    $new_rule = array(
                        "condition" => "order-item-count-in-list",
                        "operator" => "",
                        "value" => "",
                        "order-product" => $order_products_rules,
                        "order-item-count" => $order_item_count_rules,
                    );

                    $products_list = new WAD_Products_List($new_rule["order-product"]["value"]);
                    //Coupled discounts don't get the lists because the woocommerce constants
                    //haven't been initialized yet so we save the list object in order to set the ids later

                    $products_ids=$products_list->get_products();
                    if(empty($products_ids))
                        $new_rule["order-product"]["list"] = $products_list;//->get_products();
                    else
                        $new_rule["order-product"]["list"] = $products_ids;

                    $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                    $calculate_per_product = get_proper_value($this->settings, "calculate-per-product", "no");
                    if ($calculate_per_product == "yes")
                        $this->evaluable_per_product=true;
                    array_push($this->settings["rules"][$i], $new_rule);
                } elseif (!empty($previously_ordered_products_count_rules) && !empty($previously_ordered_products_in_list_rules)) {
                    $k1 = array_keys($previously_ordered_products_count_rules);
                    $k2 = array_keys($previously_ordered_products_in_list_rules);
                    $k = array_merge($k1, $k2);

                    $previously_ordered_products_count_rules = current($previously_ordered_products_count_rules);
                    $previously_ordered_products_in_list_rules = current($previously_ordered_products_in_list_rules);

                    //$products_list_id = $previously_ordered_products_in_list_rules["value"];
                    //next line don't return true value
                    //$number_bought_from_list = wad_get_customer_previous_orders_products_in_list($products_list_id);

                    $new_rule = array(
                        "condition" => "previously-ordered-products-count-in-list",
                        "operator" => "",
                        "value" => "",
                        "previously-ordered-products-in-list" => $previously_ordered_products_in_list_rules,
                        "previously-ordered-products-count" => $previously_ordered_products_count_rules,
                        //"calculated-count" => $number_bought_from_list
                    );
                    $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                    array_push($this->settings["rules"][$i], $new_rule);
                }elseif (!empty($different_order_item_count_rules) && !empty($order_products_rules)) {
                    $k1 = array_keys($different_order_item_count_rules);
                    $k2 = array_keys($order_products_rules);
                    $k = array_merge($k1, $k2);

                    $different_order_item_count_rules = current($different_order_item_count_rules);
                    $order_products_rules = current($order_products_rules);


                    $new_rule = array(
                        "condition" => "different-order-item-count-in-list",
                        "operator" => "",
                        "value" => "",
                        "order-product" => $order_products_rules,
                        "different-order-item-count" => $different_order_item_count_rules,
                    );

                    $products_list = new WAD_Products_List($new_rule["order-product"]["value"]);
                    //Coupled discounts don't get the lists because the woocommerce constants
                    //haven't been initialized yet so we save the list object in order to set the ids later

                    $products_ids=$products_list->get_products();
                    if(empty($products_ids))
                        $new_rule["order-product"]["list"] = $products_list;//->get_products();
                    else
                        $new_rule["order-product"]["list"] = $products_ids;

                    $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                    $calculate_per_product = get_proper_value($this->settings, "calculate-per-product", "no");
                    if ($calculate_per_product == "yes")
                        $this->evaluable_per_product=true;
                    array_push($this->settings["rules"][$i], $new_rule);
                } elseif (!empty($previous_order_count_rules)) {
                    $this->settings["previous-order-count"] = count(wad_get_customer_orders());
                } elseif (!empty($total_spent_on_shop_rules)) {
                    $customer_orders = wad_get_customer_orders();
                    $total_spent = 0;
                    foreach ($customer_orders as $order) {
                        $order = wc_get_order($order->ID);
                        $total_spent+=$order->get_total();
                    }
                    $this->settings["total-spent-on-shop"] = $total_spent;
                } elseif (!empty($order_subtotal_rules) && !empty($order_products_rules)) {
                    $k1 = array_keys($order_subtotal_rules);
//                    var_dump($order_products);
                    $k2 = array_keys($order_products_rules);
//                    var_dump($k2);
                    $k = array_merge($k1, $k2);

                    $order_subtotal_rules = current($order_subtotal_rules);
                    $order_products_rules = current($order_products_rules);


                    $new_rule = array(
                        "condition" => "order-subtotal-in-list",
                        "operator" => $order_subtotal_rules["operator"],
                        "value" => $order_subtotal_rules["value"],
                        "order-product" => $order_products_rules,
                        "order-subtotal" => $order_subtotal_rules,
                    );
                    $products_list = new WAD_Products_List($new_rule["order-product"]["value"]);
                    $new_rule["order-product"]["list"] = $products_list->get_products();
                    $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                    array_push($this->settings["rules"][$i], $new_rule);
                } elseif (!empty($product_review_rules)) {
                    $k = array_keys($product_review_rules);

                    foreach ($product_review_rules as $rule_index => $product_review_rule) {
                        $products_list = new WAD_Products_List($product_review_rule["value"]);
                        $product_review_rule["list"] = $products_list->get_products();

                        $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                        array_push($this->settings["rules"][$i], $product_review_rule);
                    }
                } elseif (!empty($order_products_rules)) {
                    $k = array_keys($order_products_rules);

                    foreach ($order_products_rules as $rule_index => $order_products_rule) {
                        $products_list = new WAD_Products_List($order_products_rule["value"]);
                        $order_products_rule["list"] = $products_list->get_products();
                        //var_dump($products_list->get_products());

                        $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                        array_push($this->settings["rules"][$i], $order_products_rule);
                    }
                }
                elseif(!empty ($product_review_only_rules) || !empty ($order_sharing_rules))
                {
                    $this->evaluable_per_product=true;
                }
                elseif (!empty($previously_ordered_products_in_list_rules)) {
                    $k = array_keys($previously_ordered_products_in_list_rules);

                    foreach ($previously_ordered_products_in_list_rules as $rule_index => $rule) {
                        $products_list = new WAD_Products_List($rule["value"]);
                        $rule["list"] = $products_list->get_products();

                        $this->settings["rules"][$i] = array_diff_key($group, array_flip($k));
                        array_push($this->settings["rules"][$i], $rule);
                    }
                }
            }
        }
        $this->settings = apply_filters("wad_init_coupled_rules", $this->settings, $this);
    }

    function identify_free_gifts_in_cart($cart) {
        global $wad_discounts;
        global $wad_free_gifts;
        //We're still in the init_globals() so we don't need to run yet
        if (!defined('WAD_INITIALIZED'))
            return;

        $discounts = $wad_discounts;

        foreach ($discounts["order"] as $discount_id => $discount) {
            if ($discount->is_applicable()) {
                if ($discount->settings["action"] == "free-gift") {
                    $list_products_ids = $discount->products_list->get_products();
                    if(!is_array( $list_products_ids))
                        continue;
                    $products_in_cart = wad_get_cart_products();
                    $gifts_ids = array_intersect($products_in_cart, $list_products_ids);
                    $cart_content = WC()->cart->get_cart_contents();
                    if(!empty($gifts_ids)){
                        foreach ($cart_content as $item=>$values) {
                            $item_id = $values['product_id'];
                            if($values['variation_id'] > 0)
                                $item_id = $values['variation_id'];
                            if(!isset($values['wad_free_gift']) || !in_array($item_id, $gifts_ids))
                                continue;
                            else{
                                //We use the product price in the cart
                                    $price = $this->get_cart_item_price($item_id);
                                //We don't remove it anymore here but globally in the remove_free_gifts_from_totals function
        //                        $to_widthdraw+=$price;
                                //We save the free gifts list in a global variable to access it anytime
                                    $wad_free_gifts[$item_id] = $price;
                            }
                        }
                    }
                }
            }
        }
    }

    function get_cart_subtotal($subtotal){
        $n_subtotal = 0;
        $items = WC()->cart->get_cart_contents();
        global $tax_values;
        if (!is_cart() && !is_checkout()){
            foreach($items as $item => $values) {
                $_product_obj = $values['data'];
                if( isset($values['wad_free_gift']) && (!empty($values['wad_free_gift'])))
                    continue;

                $product_id = $values['product_id'];
                if ("variation" == $_product_obj->get_type())
                    $product_id = $values['variation_id'];
                
                $tax_values     = array_values( WC_Tax::get_rates( $_product_obj->get_tax_class() ) );
                $price = $this->get_cart_item_price($product_id);
                $price = $this->apply_quantity_based_discount_if_needed($values['data'], $price);
                $quantity = $values['quantity'];
                $n_subtotal += $price * $quantity;
            }
            $subtotal = wc_price($n_subtotal);
        }

        return $subtotal;
    }

    function increase_free_gifts_quantities_in_cart($cart) {
        //We're still in the init_globals() so we don't need to run yet
        if (!defined('WAD_INITIALIZED'))
            return;
        //We adjust the product quantities in the cart to avoid calculations on the free gifts
        $this->adjust_free_gifts_quantities($cart, "+");
    }

    function decrease_free_gifts_quantities_in_cart($cart) {
        //We're still in the init_globals() so we don't need to run yet
        if (!defined('WAD_INITIALIZED'))
            return;
        //We adjust the product quantities in the cart to avoid calculations on the free gifts
        $this->adjust_free_gifts_quantities($cart, "-");
    }

    private function adjust_free_gifts_quantities($cart, $operation) {
        //We're still in the init_globals() so we don't need to run yet
        if (!defined('WAD_INITIALIZED'))
            return;
        global $wad_free_gifts;
        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {
            if ((array_key_exists($cart_item["product_id"], $wad_free_gifts) || array_key_exists($cart_item["variation_id"], $wad_free_gifts)) && isset($cart_item['wad_free_gift']) ) {
                if ($operation == "+")
                    $cart->cart_contents[$cart_item_key]["quantity"]+=1;
                else
                    $cart->cart_contents[$cart_item_key]["quantity"]-=1;
            }
        }
    }

    public function get_coupons_status($enabled) {
        global $wad_settings;
        global $wad_cart_discounts;
        $disable_coupons = get_proper_value($wad_settings, "disable-coupons", false);
        if ($wad_cart_discounts && $disable_coupons){
            WC()->cart->remove_coupons();
            $enabled = false;
        }
        return $enabled;
    }

    public function get_loop_data($wp_query) {
        global $wad_last_products_fetch;

        if(empty($wp_query))
            global $wp_query;
        //var_dump($wp_query);
        if (is_cart() || is_checkout()) {
            $cart_products = wad_get_cart_products();
            if ($cart_products)
                $wad_last_products_fetch = $cart_products;
        }
        else {
            if (isset($wp_query->query["post_type"]))
                $query_post_types = $wp_query->query["post_type"];
            else
                $query_post_types = array("product");
            if (
                    !empty($query_post_types) && (
                    (is_array($query_post_types) && in_array("product", $query_post_types))
                    ||(!is_array($query_post_types) && strpos($query_post_types, "product") !== false)
                    ||(is_array($query_post_types) && (
                            is_product_category()
                            || is_product_tag())
                            || is_product_taxonomy()
                    ))
            ) {
                $wad_last_products_fetch = array_map(function($o){ return $o->ID;}, $wp_query->posts);
            }
        }
    }

    public function get_mini_cart_loop_data()
    {
        global $wad_last_products_fetch;
        $wad_last_products_fetch = wad_get_cart_products();
    }

    function prepare_related_products_loop_data($template_name, $template_path, $located, $args)
    {
        global $wad_last_products_fetch;
        if($template_name=="single-product/related.php") // related products
        {
            extract( $args );
            $wad_last_products_fetch = array_map(function($o){ return $o->get_id();}, $related_products);
        } elseif ($template_name=="single-product/up-sells.php") { // up-sells products
            extract( $args );
            $wad_last_products_fetch = array_map(function($o){ return $o->get_id();}, $upsells);
        } elseif ($template_name=="cart/cross-sells.php") { // cross-sells products
            extract( $args );
            $wad_last_products_fetch = array_map(function($o){ return $o->get_id();}, $cross_sells);
        }

    }
    
    function get_shortcode_products_query($args){
        global $wad_discounts;
        global $wad_last_products_fetch;
        if( isset($args['post__in'] ) ) {
            $on_sale_products = $args['post__in'];
        } else {
            $on_sale_products = array();
        }
        foreach ( $wad_discounts["product"] as $discount_obj ) {
            $wad_on_sale_products = $discount_obj->products_list->get_products(true);
            $on_sale_products = array_merge( $on_sale_products, $wad_on_sale_products );
        }
        $wad_last_products_fetch = $on_sale_products;
        $args['post__in']        = $on_sale_products;

        return $args;
    }


    function get_shipping_amount($shipping_amount, $package){
        if (!defined('WAD_INITIALIZED'))
                return $shipping_amount;

        //Update cart total
        global $wad_discounts,$wad_cart_total_inc_taxes,$wad_cart_total_without_taxes;
        $wad_cart_total_inc_taxes = wad_get_cart_total(true);
        $wad_cart_total_without_taxes = wad_get_cart_total();

        $shipping_discounts = $wad_discounts['shipping'];
        foreach($shipping_discounts as $discount){
            $shipping_actions = wad_get_shipping_based_actions();
            if($discount->is_applicable()){
                $settings = $discount->settings;
                $shipping_values =  get_proper_value($settings, "shipping-list", "");
                $to_apply = $settings['action'];
                $wad_amount = floatval($settings['percentage-or-fixed-amount']);
                $package_id       = $package->get_method_id();    
                if(in_array($package_id, $shipping_values)){
                    if(in_array($to_apply, $shipping_actions)){
                        $wad_amount     = floatval( $settings['percentage-or-fixed-amount'] );
                        $shipping_taxes = $package->get_shipping_tax();

                        if ( 'percentage-off-shipping-fee' === $to_apply ) {
                            $wad_shipping_amount = $wad_amount * floatval( $shipping_amount ) / 100;
                        } elseif ( 'fixed-amount-off-shipping-fee' === $to_apply ) {
                            $wad_shipping_amount = $shipping_amount - $wad_amount;
                        } elseif ( 'fixed-shipping-fee' === $to_apply ) {
                            $wad_shipping_amount = $wad_amount;
                        }

                        global $wad_shipping_taxes;
                        if ( ! $wad_shipping_taxes ) {
                            $wad_shipping_taxes = $shipping_taxes / $shipping_amount;
                        }
                        $taxes_rate = $wad_shipping_taxes;
                        $taxes      = $package->get_taxes();
                        foreach ( $taxes as $key => $value ) {
                            $first_key = $key; // we need the first key to set the taxes.
                            break;
                        }
                        if ( isset( $first_key ) ) {
                            $package->set_taxes( array( $first_key => $wad_shipping_amount * $taxes_rate ) );
                        }
                        $shipping_amount = $wad_shipping_amount;

                        if($shipping_amount<=0){
                            $shipping_amount = 0;
                            $package->taxes = 0;
                        }
                    }
                }
            }
        }
        return $shipping_amount;
    }

}
