<?php
/**
 * WPKlaviyo Helper Class
 */

class WPKlaviyo {

    public static function is_connected($public_api_key='') {
        if (trim($public_api_key) != '') {
            return true;
        } else {
            $klaviyo_settings = get_option('klaviyo_settings');
            if (trim($klaviyo_settings['public_api_key']) != '') {
                return true;
            } else {
                return false;
            }
        }
    }

    function __construct() {
        global $klaviyowp_admin, $klaviyowp_notice, $klaviyowp_analytics, $klaviyowp_tracking;
        global $post;

        $klaviyo_settings = get_option('klaviyo_settings');
        $klaviyo_public_key = $klaviyo_settings['public_api_key'];

        $klaviyowp_admin = new WPKlaviyoAdmin();

        if ( !is_admin() ) {
            $klaviyowp_analytics = new WPKlaviyoAnalytics($klaviyo_public_key);
        }

        // Display config message.
        $klaviyowp_message = new WPKlaviyoNotification();
        add_action('admin_notices', array(&$klaviyowp_message, 'config_warning'));

        add_action('widgets_init', function() use (&$klaviyo_settings) {
            register_widget("Klaviyo_EmailSignUp_Widget");
            // Only display Built-in Signup Form widget if klaviyo.js is checked in settings
            if ( isset( $klaviyo_settings['klaviyo_popup'] ) ) {
                if ( $klaviyo_settings['klaviyo_popup'] == 'true' ) {
                    register_widget("Klaviyo_EmbedEmailSignUp_Widget");
                }
            }
        });
    }

    function add_defaults() {
        $klaviyo_settings = get_option('klaviyo_settings');

        if (($klaviyo_settings['installed'] != 'true') || !is_array($klaviyo_settings)) {
            $klaviyo_settings = array(
                'installed' => 'true',
                'public_api_key' => '',
                'klaviyo_newsletter_list_id' => '',
                'admin_settings_message' => '',
                'klaviyo_newsletter_text' => '',
                'klaviyo_popup' => ''
            );
            update_option('klaviyo_settings', $klaviyo_settings);
        }
    }

    function format_text($content, $br=true) {
        return $content;
    }
}