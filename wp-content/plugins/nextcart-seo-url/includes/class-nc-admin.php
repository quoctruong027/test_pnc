<?php
/** 
 * @package Next-Cart
 * 
 */

class NCSU_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
    }
    
    /**
     * Add menu items.
     */
    public function admin_menu() {
        if (empty($GLOBALS['admin_page_hooks']['nextcart-extras'])) {
            add_menu_page('Next-Cart Extras', 'Next-Cart Extras', 'manage_options', 'nextcart-extras', null, NCSU_PLUGIN_URL . '/assets/images/logo-red-16.png', '57');
        }
        add_submenu_page('nextcart-extras', 'URL Redirects', 'URL Redirects', 'edit_posts', 'nc-seo-url', array($this, 'seo_url_page'));
        remove_submenu_page('nextcart-extras', 'nextcart-extras');
    }
    
    public function seo_url_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(
                    '<h1>You need a higher level of permission.</h1>' .
                    '<p>Sorry, you are not allowed to view URL redirects.</p>', 403
            );
        }
        $messages = $errors = array();
        if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            if (!current_user_can('edit_posts')) {
                wp_die(
                        '<h1>You need a higher level of permission.</h1>' .
                        '<p>Sorry, you are not allowed to delete URL redirects.</p>', 403
                );
            }
            $result = $this->process_delete_redirect();
            if (is_wp_error($result)) {
                foreach ($result->get_error_messages() as $error) {
                    $errors[] = $error;
                }
                //echo '<div class="error"><p>' . wp_kses_post($result->get_error_message()) . '</p></div>';
            } elseif ($result !== false) {
                $messages[] = 'License updated.';
            }
        } else {
            if (isset($_GET['update']) && $_GET['update'] == 'del') {
                $delete_count = isset($_GET['deleted_count']) ? (int) $_GET['deleted_count'] : 0;
                $failed_count = isset($_GET['failed_count']) ? (int) $_GET['failed_count'] : 0;
                $fail = $success = '';
                if ($delete_count == 1) {
                    $success = 'URL redirect deleted.';
                } elseif ($delete_count > 1) {
                    $success = sprintf('%s URL redirects deleted.', $delete_count);
                } else {
                    $success = '0 URL redirect deleted.';
                }
                if ($failed_count > 0) {
                    $fail = sprintf(' %s URL redirects could not be deleted.', $failed_count);
                }
                $messages[] = $success . $fail;
            }
        }
        require_once NCSU_PLUGIN_PATH . 'includes/class-nc-seo-url-list-table.php';
        $list_table = new NC_Seo_Url_List_Table();
        include NCSU_PLUGIN_PATH . 'templates/list.php';
    }
    
    private function process_delete_redirect() {
        if (empty($_REQUEST['urlredirects']) && empty($_REQUEST['urlredirect'])) {
            $redirect = sprintf('?page=%s', $_REQUEST['page']);
            wp_redirect($redirect);
            exit();
        }
        if (empty($_REQUEST['urlredirects'])) {
            $urlredirect_ids = array(intval($_REQUEST['urlredirect']));
        } else {
            $urlredirect_ids = array_map('intval', (array) $_REQUEST['urlredirects']);
        }
        
        $update = 'del';
	$deleted_count = $failed_count = 0;
        
        foreach ($urlredirect_ids as $id) {
            $result = $this->delete_redirect($id);
            if ($result === true) {
                ++$deleted_count;
            } else {
                ++$failed_count;
            }
        }
        
        $redirect = sprintf('?page=%s&update=%s&deleted_count=%s&failed_count=%s', $_REQUEST['page'], $update, $deleted_count, $failed_count);
        wp_redirect($redirect);
        exit();
    }
    
    private function delete_redirect($id) {
        global $wpdb;
        $result = $wpdb->delete($wpdb->prefix . 'nextcart_seo_url', array('redirect_id' => $id));
        if (false === $result) {
            return new WP_Error('cannot_delete_redirect', __('<strong>ERROR</strong>: Could not delete URL Redirect ID: ' . $id));
        }
        return true;
    }

}

return new NCSU_Admin();