<?php

namespace Wecp\App\Controllers\Admin\Settings;
class General extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = 'general';
        $this->label = __('General', WECP_TEXT_DOMAIN);
    }

    function render()
    {
        $current_status = self::$license->getLicenseStatus();
        $license_key_error = true;
        if ($current_status == "invalid") {
            $license_key_message = __('Please enter valid license key!', WECP_TEXT_DOMAIN);
        } elseif ($current_status == "expired") {
            $license_key_message = __('Your license key was expired!', WECP_TEXT_DOMAIN);
        } else {
            $license_key_error = false;
            $license_key_message = __('Your license key validated successfully!', WECP_TEXT_DOMAIN);
        }
        $options = $this->getOptions();
        $options['license_key'] = self::$license->getLicenseKey();
        $default_language = self::$woocommerce->getSiteDefaultLang();
        $available_languages = self::$woocommerce->getAvailableLanguages();
        if (is_array($available_languages)) {
            //include default language to available languages
            $available_languages = array_merge(array($default_language), $available_languages);
        }
        if (!empty($available_languages)) {
            $available_languages = array_unique($available_languages);
        }
        $params = array(
            'save_key' => $this->key(),
            'options' => $options,
            'available_languages' => $available_languages,
            'license_key_message' => $license_key_message,
            'license_key_error' => $license_key_error,
            'woocommerce_helper' => self::$woocommerce
        );
        return self::$template->setData(WECP_PLUGIN_PATH . 'App/Views/Admin/Settings/' . $this->name() . '.php', $params)->render();
    }

    function getDefaultOptions()
    {
        return array(
            'custom_css' => $this->getDefaultCustomCss(),
            'show_sku' => 1,
            'show_product_image' => 1,
            'product_image_size' => 'thumbnail',
            'product_image_height' => 32,
            'product_image_width' => 32,
            'enable_retainful_integration' => 0,
            'autofix_broken_html' => 0,
        );
    }

    function getDefaultCustomCss()
    {
        $css = '.email-product-list table,.email-download-list table {
                    font-family: verdana, arial, sans-serif;
                    font-size: 14px;
                    border-collapse: collapse;
                }
                
                .email-product-list table thead th,.email-download-list table thead tr {
                    border: 1px solid #e1e1e1;
                    padding: 12px 15px;
                    background-color: #f9f9f9;
                    color: #424242
                }
                
                .email-product-list table tbody th,.email-download-list table tbody tr {
                    border: 1px solid #e1e1e1;
                    padding: 12px 15px;
                    text-align: left
                }
                
                .email-product-list table tbody td, .email-download-list table tbody td,.email-download-list table thead th {
                    border: 1px solid #e1e1e1;
                    padding: 12px 15px
                }
                .email-product-list table td ul{
                    list-style:none;
                    padding-left: 0px
                }
                .email-product-list table td ul li{
                    margin-left: 0px
                }
                .email-product-list table tfoot th {
                    border: 1px solid #e1e1e1;
                    padding: 12px 15px;
                    text-align: left;
                    color: #424242;
                    font-size: 13px
                }
                
                .email-product-list table tfoot td {
                    border: 1px solid #e1e1e1;
                    padding: 12px 15px
                }
                
                .email-product-list table tfoot tr:last-child {
                    background-color: #f9f9f9
                }';
        return apply_filters('woocommerce_email_customizer_plus_default_custom_css', $css);
    }
}