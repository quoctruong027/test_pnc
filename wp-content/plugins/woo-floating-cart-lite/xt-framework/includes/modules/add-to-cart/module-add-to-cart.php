<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Module_Add_To_Cart' ) ) {

    class XT_Module_Add_To_Cart extends XT_Framework_Module
    {

        /**
         * Get module name
         *
         * @return    string    name.
         * @since     1.0.0
         */
        public function name() {

            return 'XT Woo '.$this->name;
        }

        /**
         * Get module menu name
         *
         * @return    string    menu name.
         * @since     1.0.0
         */
        public function menu_name() {

            return 'Woo '.$this->name;
        }

        protected function add_hooks()
        {

            // Init customizer options
            $this->loader()->add_filter($this->prefix('customizer_sections'), $this, 'customizer_sections', 1, 1);
            $this->loader()->add_filter($this->prefix('customizer_fields'), $this, 'customizer_fields', 1, 2);

            // Add WC Ajax Events
            $this->core->plugin_loader()->add_filter($this->core->plugin_prefix('wc_ajax_add_events'), $this, 'ajax_add_events', 1, 1);

            // Filter woocommerce enable ajax add to cart option
            $this->loader()->add_filter( 'option_woocommerce_enable_ajax_add_to_cart', $this, 'enable_ajax_add_to_cart');

            // Filter woocommerce cart redirect option
            $this->loader()->add_filter( 'option_woocommerce_cart_redirect_after_add', $this, 'cart_redirect_after_add');

            // Filter add to cart cart redirect path
            $this->loader()->add_filter( 'woocommerce_add_to_cart_redirect', $this, 'woocommerce_add_to_cart_redirect');

            // Maybe force displaying add to cart button on shop page
            $this->loader()->add_action('init', $this, 'maybe_enable_loop_add_to_cart');

            // Enqueue assets
            $this->loader()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets', 1 );

            // Enqueue customizer controls assets
            $this->loader()->add_action($this->customizer()->prefix( 'customizer_controls_assets') , $this, 'customizer_controls_assets');

            // Set body classes
            $this->loader()->add_action( 'body_class', $this, 'body_class', 1 );
        }

        /**
         * Add ajax events
         */
        public function ajax_add_events($ajax_events) {

            $ajax_events[] = array(
                'function' => 'xt_atc_single',
                'callback' => array( $this, 'single_add_to_cart' ),
                'nopriv'   => true
            );

            return $ajax_events;
        }

        public function get_script_vars() {

            $customizer = $this->customizer();

            $xt_woofc = $this->core->get_instance('xt-woo-floating-cart');

            $overrideSpinner = $customizer->get_option_bool('override_spinner', false);
            $spinnerIcon = $customizer->get_option('spinner_icon', 'xt_icon-spinner');
            $checkmarkIcon = $customizer->get_option('checkmark_icon', 'xt_icon-checkmark');
            $ajaxAddToCart = $customizer->get_option_bool('ajax_add_to_cart', true);
            $ajaxSinglePageAddToCart = $customizer->get_option_bool('single_ajax_add_to_cart', true);
            $singleRefreshFragments = $customizer->get_option_bool('single_refresh_fragments', true);
            $singleScrollToNotice = $customizer->get_option_bool('single_added_scroll_to_notice', true);
            $singleScrollToNoticeTimeout = (!empty($xt_woofc) && $xt_woofc->customizer()->get_option_bool('flytocart_animation') ? $xt_woofc->customizer()->get_option('flytocart_animation_duration') : false);
            $redirectionEnabled = $customizer->get_option_bool('redirection_enabled', false);
            $redirectionTo = apply_filters('woocommerce_add_to_cart_redirect', wc_get_cart_url(), null);

            return array(
                'customizerConfigId' => $this->customizer()->config_id(),
                'ajaxUrl' => urldecode(add_query_arg('wc-ajax', '%%endpoint%%', home_url('/'))),
                'ajaxAddToCart' => $ajaxAddToCart,
                'ajaxSinglePageAddToCart' => $ajaxSinglePageAddToCart,
                'singleRefreshFragments' => $singleRefreshFragments,
                'singleScrollToNotice' => $singleScrollToNotice,
                'singleScrollToNoticeTimeout' => $singleScrollToNoticeTimeout,
                'isProductPage' => is_product(),
                'overrideSpinner' => $overrideSpinner,
                'spinnerIcon' => $spinnerIcon,
                'checkmarkIcon' => $checkmarkIcon,
                'redirectionEnabled' => $redirectionEnabled,
                'redirectionTo' => $redirectionTo
            );
        }

        public function enqueue_assets()
        {

            $deps = array(
                'jquery',
                'wc-cart-fragments',
                'xt-jquery-ajaxqueue',
                'xt-observers-polyfill'
            );


            if(get_site_option('enable_ajax_add_to_cart') === 'yes' || $this->customizer()->get_option_bool('single_ajax_add_to_cart', true)) {
                $deps[] = 'wc-add-to-cart';
            }

            wp_enqueue_style(
                $this->prefix(),
                $this->url('assets/css', 'add-to-cart.css'),
                array('xt-icons'),
                XTFW_VERSION
            );

            wp_enqueue_script(
                $this->prefix(),
                $this->url('assets/js', 'add-to-cart'.XTFW_SCRIPT_SUFFIX . '.js'),
                $deps,
                XTFW_VERSION
            );

            wp_localize_script($this->prefix(), 'XT_ATC', $this->get_script_vars());

            if(is_customize_preview()) {

                wp_add_inline_script($this->prefix(), '

                    var disableClickSelectors = [
                        ".add_to_cart_button"
                    ];
                    
                    disableClickSelectors = disableClickSelectors.join(",");

                    jQuery(document).on("mouseenter", disableClickSelectors, function() {

                        jQuery(this).attr("data-href", jQuery(this).attr("href")).attr("href", "#");
    
                    }).on("mouseleave", disableClickSelectors, function() {

                        jQuery(this).attr("href", jQuery(this).attr("data-href"));
                    });
                ');
            }
        }

        public function customizer_controls_assets() {

            wp_enqueue_script(
                $this->prefix('customizer-assets'),
                $this->url('assets/js', 'customizer-controls'.XTFW_SCRIPT_SUFFIX . '.js'),
                array(),
                XTFW_VERSION,
                true
            );

            wp_localize_script($this->prefix('customizer-assets'), 'XT_ATC', $this->get_script_vars());
        }

        public function body_class($classes) {

            $overrideSpinner = $this->customizer()->get_option_bool( 'override_spinner', false );
            $hideViewCartButton = $this->customizer()->get_option_bool( 'hide_view_cart_button', false );

            if( $overrideSpinner ) {
                $classes[] = 'xt_atc_override_spinner';
            }

            if ( $hideViewCartButton ) {
                $classes[] = 'xt_atc_hide_view_cart';
            }

            return $classes;
        }

        public function customizer_sections($sections)
        {

            $sections[] = array(
                'id' => 'default',
                'title' => $this->menu_name(),
                'icon' => 'dashicons-button',
                'priority' => 0
            );

            return $sections;
        }

        public function customizer_fields($fields, $customizer)
        {

            require $this->path('customizer', 'fields.php');

            return $fields;
        }

        public function cart_fragments( $fragments ) {

            $notices = wc_print_notices( true );
            $fragments[".woocommerce-notices-wrapper"] = '<div class="' . esc_attr( "woocommerce-notices-wrapper" ) . '">' . $notices . '</div>';

            wc_clear_notices();

            return $fragments;
        }

        public function single_add_to_cart() {

            $this->loader()->add_filter( 'woocommerce_add_to_cart_fragments', $this, 'cart_fragments', 10 );

            WC_Ajax::get_refreshed_fragments();
        }

        public function enable_ajax_add_to_cart() {

            $ajax_add_to_cart = $this->customizer()->get_option_bool('ajax_add_to_cart', true);

	        return $ajax_add_to_cart ? 'yes' : 'no';
        }

        public function cart_redirect_after_add() {

            $redirection_enabled = $this->customizer()->get_option_bool('redirection_enabled', false);

            return $redirection_enabled ? 'yes' : 'no';
        }

        public function woocommerce_add_to_cart_redirect($redirect_url) {

            $redirection_enabled = $this->customizer()->get_option_bool('redirection_enabled', false);
            if($redirection_enabled) {

                $redirection_to = $this->customizer()->get_option('redirection_to', 'cart');

                if($redirection_to === 'cart') {

                    $redirect_url = wc_get_cart_url();

                }else if($redirection_to === 'checkout') {

                    $redirect_url = wc_get_checkout_url();

                }else if($redirection_to === 'custom') {

                    $redirection_to_custom = $this->customizer()->get_option('redirection_to_custom', '');

                    if(!empty($redirection_to_custom)) {

                        $redirect_url = get_permalink($redirection_to_custom);
                    }
                }
            }

            return $redirect_url;
        }

        public function maybe_enable_loop_add_to_cart() {

            $enable = false;

            $enable_for_theme = $this->core->is_theme(array('Divi'));
            $disable_for_theme = $this->core->is_theme(array('Avada'));

            $xt_wooqv = $this->core->get_instance('xt-woo-quick-view');

            if(!empty($xt_wooqv)) {

                $enable = $enable_for_theme;

                $position = $xt_wooqv->customizer()->get_option('trigger_position', 'before');
                $enable_for_positions = array('before', 'after', 'above', 'below');
                $enable_for_wooqv = in_array($position, $enable_for_positions);

                $enable = $enable && $enable_for_wooqv ? true : $enable;
            }

            $xt_woovs = $this->core->get_instance('xt-woo-variation-swatches');

            if(!empty($xt_woovs)) {

                $enable = $enable_for_theme;

                $enable_for_woovs = $xt_woovs->frontend()->enabled('archives');

                $enable = $enable && $enable_for_woovs ? true : $enable;
            }

            if(($enable || $this->customizer()->get_option_bool('show_archive_add_to_cart_button', false)) && (!has_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart') && !$disable_for_theme)) {
                add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20);
            }
        }
    }
}