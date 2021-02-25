<?php

/**
 * @params: $key
 * Inserts or update the settings table value for etsy
 * @return string on that key
 */

function get_etsy_settings($key)
{
    global $wpdb;
    $table = $wpdb->prefix . 'etcpf_settings';
    $value = $wpdb->get_var($wpdb->prepare("SELECT _settings_mvalue FROM $table WHERE _settings_mkey=%s", array($key)));
    return $value;
}


/**
 * @params: $key and $value
 * Inserts or update the settings table value for etsy
 * @return boolean
 */

function update_etsy_settings($key, $value)
{

    global $wpdb;
    $table = $wpdb->prefix . 'etcpf_settings';
    $data = array(
        '_settings_mkey' => $key,
        '_settings_mvalue' => $value
    );
    $value = $wpdb->get_var($wpdb->prepare("SELECT _settings_mvalue FROM $table WHERE _settings_mkey=%s", array($key)));
    if ($value) {
        $wpdb->update($table, $data, array('_settings_mkey' => $key));
        if (empty($wpdb->last_error)) {
            return true;
        }
    } else {
        if ($wpdb->insert($table, $data)) return true;
    }
}

function etcpf_random_string($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getsafePostData($index)
{
    if (is_array($_POST[$index])) {
        return wp_unslash($_POST[$index]);
    } else {
        return sanitize_text_field($_POST[$index]);
    }
}
function getsafeajaxPostData($index)
{
    if (isset($_POST['data'][$index]) && is_array($_POST['data'][$index])) {
        return wp_unslash($_POST['data'][$index]);
    } else {
        return sanitize_text_field($_POST['data'][$index]);
    }
}
