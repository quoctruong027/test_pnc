<?php
/*
   Plugin Name: theShark dropshipping for AliExpress and Woocommerce
   Plugin URI: http://wordpress.org/extend/plugins/wooshark-aliexpress-importer/
   Version: 2.0.8
   Author: wooproductimporter
   Description: Wooshark dropshipping for aliexpress and woocommerce
   Text Domain: wooshark-aliexpress-importer
   License: GPLv3
  */

/*
    "WordPress Plugin Template" Copyright (C) 2018 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$WoosharkAliexpressImporter_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function WoosharkAliexpressImporter_noticePhpVersionWrong()
{
    global $WoosharkAliexpressImporter_minimalRequiredPhpVersion;
    echo '<div class="updated fade">' .
        __('Error: plugin "Wooshark dropshipping for aliexpress and woocommerce" requires a newer version of PHP to be running.',  'wooshark-aliexpress-importer') .
        '<br/>' . __('Minimal version of PHP required: ', 'wooshark-aliexpress-importer') . '<strong>' . $WoosharkAliexpressImporter_minimalRequiredPhpVersion . '</strong>' .
        '<br/>' . __('Your server\'s PHP version: ', 'wooshark-aliexpress-importer') . '<strong>' . phpversion() . '</strong>' .
        '</div>';
}

function WoosharkAliexpressImporter_PhpVersionCheck()
{
    global $WoosharkAliexpressImporter_minimalRequiredPhpVersion;
    if (version_compare(phpversion(), $WoosharkAliexpressImporter_minimalRequiredPhpVersion) < 0) {
        add_action('admin_notices', 'WoosharkAliexpressImporter_noticePhpVersionWrong');
        return false;
    }
    return true;
}


function our_plugin_action_links($links, $file)
{
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    // check to make sure we are on the correct plugin

    if ($file == $this_plugin) {

        // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page

        $settings_link = '<a style="color:red" target="_blank" href="https://wooshark.com/aliexpress">Go pro</a>';

        // add the link to the list

        array_unshift($links, $settings_link);
    }

    return $links;
}

add_filter('plugin_action_links', 'our_plugin_action_links', 10, 2);

/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function WoosharkAliexpressImporter_i18n_init()
{
    $pluginDir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('wooshark-aliexpress-importer', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
// add_action('plugins_loadedi','WoosharkAliexpressImporter_i18n_init');

function my_admin_scripts_init()
{ }
add_action('admin_enqueue_scripts', 'my_admin_scripts_init');






// Run the version check.
// If it is successful, continue with initialization for this plugin
if (WoosharkAliexpressImporter_PhpVersionCheck()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('wooshark-aliexpress-importer_init.php');
    WoosharkAliexpressImporter_init(__FILE__);
    initOriginalProductUrl();
}

function initOriginalProductUrl()
{
    add_action(base64_decode('cG9zdF9zdWJtaXRib3hfbWlzY19hY3Rpb25z'), base64_decode('d29vX2FkZF9jdXN0b21fZ2VuZXJhbF9maWVsZHNfb3JpZ2luYWxQcm9kdWN0VXJs'), 20);
    function woo_add_custom_general_fields_originalProductUrl()
    {
        echo base64_decode('IA0KICAgICAgICA8YnV0dG9uIHR5cGU9ImJ1dHRvbiIgc3R5bGU9Im1hcmdpbjoxMHB4OyAiICBjbGFzcz0iYnRuIGJ0bi1wcmltYXJ5IiBpZD0ib3Blbk9yaWdpbmFsUHJvZHVjdFVybCIgZGF0YS10YXJnZXQ9Ii5iZC1leGFtcGxlLW1vZGFsLWxnIj4gT3BlbiBPcmlnaW5hbCBwcm9kdWN0IHVybCAod29vc2hhcmspPC9idXR0b24+DQogICAgICAgIA0KICAgICAgICA8ZGl2IGNsYXNzPSJsb2FkZXIyIiBzdHlsZT0iZGlzcGxheTpub25lIj48ZGl2PjwvZGl2PjxkaXY+PC9kaXY+PGRpdj48L2Rpdj48ZGl2PjwvZGl2PjwvZGl2Pg==');
    }
}
if (!wp_next_scheduled(base64_decode('d29vc2hhcmtfbXlwcmVmaXhfY3Jvbl9ob29r'))) {
    wp_schedule_event(time(), 'weekly', base64_decode('d29vc2hhcmtfbXlwcmVmaXhfY3Jvbl9ob29r'));
}
add_action(base64_decode('d29vc2hhcmtfbXlwcmVmaXhfY3Jvbl9ob29r'), 'wooshark_myprefix_cron_function()');
function wooshark_myprefix_cron_function()
{
    update_option(base64_decode('aXNBbGxvd2VkVG9JbXBvcnQ='), 0);
}
