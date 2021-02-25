<?php
require_once plugin_dir_path(__FILE__).'etsyclient.php';
class ETCPF_Upload_Cron
{
    function startCron(){
        add_filter('cron_schedules','etcpf_refresh_interval_imgs');
        $next_refresh = wp_next_scheduled('etcpf_mutipl_images_upload');
        if(!$next_refresh){
            wp_schedule_event(time(), 'etcpf_fiv_min', 'etcpf_mutipl_images_upload');
        }
    }
}

function etcpf_refresh_interval_imgs()
{
    $schedules['etcpf_fiv_min'] = array(
        'interval' => 300,
        'display' => __('Every Five Minute uploads Images in Etsy')
    );
    return $schedules;
}
add_action('etcpf_mutipl_images_upload','etcpf_doImageThingy');
function etcpf_doImageThingy(){
    $etsy = new ETCPF_Etsy();
    $etsy->upload_additional_images();
}

$action = new ETCPF_Upload_Cron();
$action->startCron();