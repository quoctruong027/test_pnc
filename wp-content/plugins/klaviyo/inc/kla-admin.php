<?PHP
class WPKlaviyoAdmin {

    function __construct() {
        if (is_admin()) {
            $klaviyo_settings = get_option('klaviyo_settings');

            add_action('admin_menu', array(&$this, 'add_options_subpanel'));
            add_filter('plugin_action_links_' . KLAVIYO_BASENAME, array(&$this, 'plugin_settings_link'));
        }
    }

    function add_options_subpanel() {
        if (function_exists('add_menu_page') && current_user_can('manage_options')) {
            global $submenu, $klaviyowp;

            add_menu_page('Klaviyo', 'Klaviyo', 'manage_options', 'klaviyo_settings', array($this, 'settings'), KLAVIYO_URL . 'img/klaviyo-logo.png');
            add_submenu_page('klaviyo_settings', 'Help', 'Help', 'manage_options', 'klaviyo_help', array($this, 'help'));

            $submenu['klaviyo_settings'][0][0] = 'Settings';
        }
    }

    function help() {
        $content = '';
        $content = '<ol>
                      <li><a href="#help-1">Where do I find my Klaviyo API keys?</a></li>
                      <li><a href="#help-4">How do I add a Klaviyo email sign up into my sidebar?</a></li>
                    </ol>
                    <p><a name="help-1"></a><h2>1) Where do I find my Klaviyo API keys?</h2></p>
                    <p>
                      You can find your Klaviyo API keys by going to the
                      <a href="https://www.klaviyo.com/account#api-keys-tab">account page</a> in Klaviyo.
                      Your <strong>public</strong> API key will be 6-7 characters long.
                      Your <strong>private</strong> API key will be 7 characters, a hyphen and then 16 more.<br /><br />

                      Once you have connected your Klaviyo account, tracking will be enabled for visitors.
                    </p>
                    <p><a name="help-2"></a><h2>2) How do I add a Klaviyo email sign up into my sidebar?</h2></p>
                    <p>
                      Make sure you have connected your Klaviyo account on the Klaviyo settings page.<br />
                      Then you can find the widget under Appearance &raquo; Widgets titled &quot;Klaviyo: Email Sign Up&quot;.
                    </p>';

        $content = $this->postbox('klaviyo-help', 'FAQ', $content);
        $this->admin_wrap('Klaviyo Plugin Help', $content);
    }

    function settings() {
        $klaviyo_settings = $this->process_settings();

        $content = '<p>Insert your Klaviyo API key below to connect. You can find them on your Klaviyo <a href="https://www.klaviyo.com/account#api-keys-tab">account page</a>.</p>
            <p>Insert your Klaviyo List ID to add a newsletter checkbox on the checkout page. <a href="https://help.klaviyo.com/hc/en-us/articles/115005078647-Find-a-List-ID">How to find list ID</a>.</p>
            <table class="form-table">';

        if (function_exists('wp_nonce_field')) {
          $content .= wp_nonce_field('klaviyo-update-settings', '_wpnonce', true, false);
        }
        $content .= '<tr><th scope="row"><label for="klaviyo_public_api_key">Public API Key</label></th><td><input type="text" class="regular-text" name="klaviyo_public_api_key" value="' . $klaviyo_settings['public_api_key'] . '" /></td></tr>';
        $content .= '<tr><th scope="row"><label for="klaviyo_newsletter_list_id">Add a subscribe to newsletter checkbox on the checkout page</label></th><td><input type="text" class="regular-text" name="klaviyo_newsletter_list_id" placeholder="Klaviyo list ID" value="' . $klaviyo_settings['klaviyo_newsletter_list_id'] . '" /></td></tr>';
        $content .= '<tr><th scope="row"><label for="klaviyo_newsletter_text">Subscribe to newsletter text</label></th><td><input type="text" class="regular-text" name="klaviyo_newsletter_text" placeholder="Eg. I do not mind if you send me relevant updates." value="' . $klaviyo_settings['klaviyo_newsletter_text'] . '" /></td></tr>';
        $content .= '<tr><th scope="row"><label for="klaviyo_configuration_warning">Disable Configuration Warning</label></th><td><input type="checkbox" name="admin_settings_message" value="true" ' . checked($klaviyo_settings['admin_settings_message'], 'true', false) . ' /></td></tr>';
        $content .= '<tr><th scope="row"><label for="klaviyo_popup">Enable Klaviyo signup forms</label></th><td><input type="checkbox" name="klaviyo_popup" value="true" ' . checked($klaviyo_settings['klaviyo_popup'], 'true', false) . ' /></td></tr>';
        $content .= '</table>';
        $content .= '<p>This will automatically install the Klaviyo script needed for signup forms. This script is required for the built-in signup form widget. Learn more about Klaviyo <a href="https://help.klaviyo.com/hc/en-us/articles/360002035871-Install-Klaviyo-Signup-Forms#verify-your-installation">Signup forms.</a></p>';
        
        $wrapped_content = $this->postbox('klaviyo-settings', 'Connect to Klaviyo', $content);
        
        $this->admin_wrap('Klaviyo Settings', $wrapped_content);
    }

    function process_settings() {
        $klaviyo_notification = new WPKlaviyoNotification('settings_update');

        if (!empty($_POST['klaviyo_option_submitted'])) {
             
            $klaviyo_settings = get_option('klaviyo_settings');

            if ($_GET['page'] == 'klaviyo_settings' && check_admin_referer('klaviyo-update-settings')) {
                if (isset($_POST['klaviyo_public_api_key']) && strlen($_POST['klaviyo_public_api_key']) < 8) {
                    $klaviyo_settings['public_api_key'] = $_POST['klaviyo_public_api_key'];
                }

                $klaviyo_setting_keys = ['klaviyo_public_api_key', 'admin_settings_message', 'klaviyo_subscribe_checkbox', 'klaviyo_newsletter_list_id', 'klaviyo_newsletter_text', 'klaviyo_popup'];
                $klaviyo_updated_settings = array_fill_keys($klaviyo_setting_keys, '');
                
                foreach($_POST as $key => $value) {
                    if ( 'klaviyo_newsletter_text' === $key ) {
                        $value = stripslashes($value);
                    }

                    $klaviyo_updated_settings[$key] = $value;
                }
                
                $klaviyo_settings = array_merge($klaviyo_settings, $klaviyo_updated_settings);
                                
                $klaviyo_notification->display_message(3);
                update_option('klaviyo_settings', $klaviyo_settings);
            }
        }
        
        return get_option('klaviyo_settings');
    }

    function plugin_settings_link($links) {
        $settings_link = '<a href="' . KLAVIYO_ADMIN . 'admin.php?page=klaviyo_settings">Settings</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    function show_plugin_support() {
        $content = '<p>First, check the <a href="' . KLAVIYO_ADMIN . 'admin.php?page=klaviyo_help">Help Section</a>. If you still have questions or want to give feedback, send an email to <a href="http://docs.klaviyo.com/customer/portal/emails/new">Klaviyo support.</a></p>';
        return $this->postbox('klaviyo-support', 'Help / Feedback', $content);
    }

    function postbox($id, $title, $content) {
        $wrapper = '';
        $wrapper .= '<div id="' . $id . '" class="postbox">';
        $wrapper .=   '<div class="handlediv" title="Click to toggle"><br /></div>';
        $wrapper .=   '<h3 class="hndle"><span>' . $title . '</span></h3>';
        $wrapper .=   '<div class="inside">' . $content . '</div>';
        $wrapper .= '</div>';
        return $wrapper;
    }

    function admin_wrap($title, $content) {

    $showpluginsupport = $this->show_plugin_support();

      echo <<<EOT
        <div class="wrap">
          <div class="dashboard-widgets-wrap">
            <h2>{$title}</h2>
            <form method="post" action="">
              <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container" style="width:60%;">
                  <div class="meta-box-sortables ui-sortable">
                     {$content}
                    <p class="submit">
                      <input type="submit" name="klaviyo_option_submitted" class="button-primary" value="Save Settings" />
                    </p>
                  </div>
                </div>
                <div class="postbox-container" style="width:40%;">
                  <div class="meta-box-sortables ui-sortable">
                    {$showpluginsupport}
                  </div>
                </div>
                </div>
            </form>
          </div>
        </div>
EOT;

    }
  }
?>
