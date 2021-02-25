<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
// Create a custom refresh_interval so that scheduled events will be able to display
class ETCPF_Cron
{
    public function __construct()
    {
        add_filter('cron_schedules', array(&$this, 'etsy_cron_add_custom_schedules'));
    }

    public function etsy_cron_add_custom_schedules($schedules)
    {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display' => 'Every Minute'
        );
        $schedules['five_min'] = array(
            'interval' => 60 * 5,
            'display' => 'Once every five minutes'
        );
        $schedules['ten_min'] = array(
            'interval' => 60 * 10,
            'display' => 'Once every ten minutes'
        );
        $schedules['fifteen_min'] = array(
            'interval' => 60 * 15,
            'display' => 'Once every fifteen minutes'
        );
        $schedules['thirty_min'] = array(
            'interval' => 60 * 30,
            'display' => 'Once every thirty minutes'
        );
        $schedules['three_hours'] = array(
            'interval' => 60 * 60 * 3,
            'display' => 'Once every three hours'
        );
        $schedules['six_hours'] = array(
            'interval' => 60 * 60 * 6,
            'display' => 'Once every six hours'
        );
        $schedules['twelve_hours'] = array(
            'interval' => 60 * 60 * 12,
            'display' => 'Once every twelve hours'
        );
        $schedules['daily'] = array(
            'interval' => 60 * 60 * 24,
            'display' => 'Once every twenty four hours'
        );

        /*$schedules['weekly'] = array(
            'interval' => strtotime(604800 . ' seconds'), // 1 week in seconds
            'display' => __('Once Weekly'),
        );*/

        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display' => __('Monthly', 'Etsy'),
        );
        $schedules['etsy_feed'] = array(
            'interval' => strtotime(get_option('et_cp_feed_delay') . ' seconds'),
            'display' => __('Custom', 'Etsy'),
        );
        return $schedules;
    }

    public function etsyFeedUpdateCron()
    {

        /*$current_delay = get_option('et_cp_feed_delay');
        $next_refresh = wp_next_scheduled('update_etsyfeeds_hook');*/

        if (!wp_next_scheduled('update_etsyfeeds_hook')) {
            wp_schedule_event(time(), get_etsy_settings('feed_update_interval'), 'update_etsyfeeds_hook');
        }
    }

    public function scheduleetsyUpload()
    {
        if (!wp_next_scheduled('auto_feed_submission_hook')) {
            wp_schedule_event(time(), get_etsy_settings('feed_submission_interval'), 'auto_feed_submission_hook');
        }
    }

    public function scheduleetsyOrder()
    {
        if (!wp_next_scheduled('auto_etsy_order_hook')) {
            wp_schedule_event(time(), get_etsy_settings('order_fetch_interval'), 'auto_etsy_order_hook');
        }
    }
    public function scheduleetsyFetchProduct()
    {
        if (!wp_next_scheduled('auto_fetch_product_hook')) {
            wp_schedule_event(time(),'every_minute', 'auto_fetch_product_hook');
        }
    }

}
// Class ETCPF_Mapping_Product
// {
//     public static function scheduleetsyMappingProduct()
//     {
//         add_filter('cron_schedules', 'etsy_autouload_refresh_interval');
//         $current_delay = 60; /*get_option('et_cp_feed_delay');*/
//         $autouploadCron = wp_next_scheduled('auto_mapping_product_hook');
//         //wp_unschedule_event($autouploadCron, 'auto_feed_submission_hook');
//         if (!$autouploadCron) {
//             wp_schedule_event(strtotime($current_delay . ' seconds'), 'etcpf_autofeed_refresh_interval', 'auto_mapping_product_hook');
//         }
//     }

// }
// Class ETCPF_Fetch_Product
// {
//     public static function scheduleetsyFetchProduct()
//     {
//         add_filter('cron_schedules', 'etsy_autouload_refresh_interval');
//         $current_delay = 60; /*get_option('et_cp_feed_delay');*/
//         $autouploadCron = wp_next_scheduled('auto_fetch_product_hook');
//         //wp_unschedule_event($autouploadCron, 'auto_feed_submission_hook');
//         if (!$autouploadCron) {
//             wp_schedule_event(strtotime($current_delay . ' seconds'), 'etcpf_autofeed_refresh_interval', 'auto_fetch_product_hook');
//         }
//     }

// }
