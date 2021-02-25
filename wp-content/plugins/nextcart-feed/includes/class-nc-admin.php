<?php
/** 
 * @package Next-Cart
 * 
 */

class NCF_Admin {
    
    const FEED_URL = 'https://feed.next-cart.com/service/';
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_insert_post', array($this, 'update_product_trigger'), 10, 3);
        add_action('deleted_post', array($this, 'delete_product_trigger'));
		add_filter('woocommerce_product_data_tabs', array($this,'nc_add_shopify_product_data_tab') , 10 , 1);
		add_action('woocommerce_product_data_panels', array($this,'nc_add_shopify_product_data_fields'));
    }
    
    /**
     * Add menu items.
     */
    public function admin_menu() {
        if (empty($GLOBALS['admin_page_hooks']['nextcart-extras'])) {
            add_menu_page('Next-Cart Extras', 'Next-Cart Extras', 'manage_options', 'nextcart-extras', null, NCF_PLUGIN_URL . '/assets/images/logo-red-16.png', '57');
        }
        add_submenu_page('nextcart-extras', 'Product Data Feed', 'Product Data Feed', 'edit_posts', 'nc-feed', array($this, 'feed_page'));
        remove_submenu_page('nextcart-extras', 'nextcart-extras');
    }
    
    public function feed_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(
                    '<h1>You need a higher level of permission.</h1>' .
                    '<p>Sorry, you are not allowed to view URL redirects.</p>', 403
            );
        }
        if (isset($_GET['delete']) && $_GET['delete'] == 'logs') {
            $this->delete_log();
            wp_safe_redirect(esc_url_raw(admin_url('admin.php?page=nc-feed')));
            exit;
        }
        $feed_id = get_option('nextcart_feed_id');
        $feed_license = get_option('nextcart_feed_license');
        $feed_custom_id = get_option('nextcart_feed_custom_id');
        include NCF_PLUGIN_PATH . 'templates/settings.php';
    }
    
    private function delete_log() {
        $removed = false;
        if (file_exists(NC_LOG_DIR . 'product_data_feed.log')) {
            $removed = unlink(NC_LOG_DIR . 'product_data_feed.log');
        }
        return $removed;
    }
    
    public function register_settings() {
        register_setting('nextcart_feed_setting', 'nextcart_feed_id');
        register_setting('nextcart_feed_setting', 'nextcart_feed_license');
        register_setting('nextcart_feed_setting', 'nextcart_feed_custom_id');
    }
    
    public function nc_add_log($entry) {
        $handle = @fopen(NC_LOG_DIR . 'product_data_feed.log', 'a');
        if ($handle) {
            fwrite($handle, $entry . PHP_EOL);
        }
    }

    public function update_product_trigger($post_ID, $post, $update) {
        $feed_id = get_option('nextcart_feed_id');
        $feed_license = get_option('nextcart_feed_license');
        $post_status = get_post_status($post_ID);
        if(!$feed_id ||!$feed_license || get_post_type($post_ID) != 'product' || in_array($post_status, array('inherit','auto-draft','draft'))) {
            return;
        }
        if ($post_status == 'trash') {
            return $this->delete_product_trigger($post_ID);
        }
        $custom_id = false;
        $feed_custom_id = get_option('nextcart_feed_custom_id');
        if ($feed_custom_id) {
            $custom_id = get_post_meta($post_ID, $feed_custom_id, true);
        }
        $request = wp_remote_post(self::FEED_URL . $feed_id .'/update.php', array(
            'method' => 'POST',
            'timeout' => 30,
            'body' => array(
                'product_id' => $post_ID,
                'product_custom_id' => $custom_id,
                'license_key' => $feed_license,
                'action' => $update ? 'update' : 'add'
                ),
            )
        );
        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            $this->nc_add_log('ERROR: Failed to update product ID: ' . $post_ID);
            return false;
        }
        $response = @json_decode(wp_remote_retrieve_body($request), 1);
        if (!$response || $response['result'] == 'error') {
            if (!empty($response['msg'])) {
                $this->nc_add_log('ERROR: Failed to update product ID: ' . $post_ID . '. Reason: ' . $response['msg']);
            } else {
                $this->nc_add_log('ERROR: Failed to update product ID: ' . $post_ID . '. Reason: Unknown');
            }
        } else {
            $this->nc_add_log('SUCCESS: Product ID: ' . $post_ID . ' is updated');
        }
    }
    
    public function delete_product_trigger($post_id) {
        $feed_id = get_option('nextcart_feed_id');
        $feed_license = get_option('nextcart_feed_license');
        if(!$feed_id ||!$feed_license || get_post_type( $post_id ) != 'product' || in_array(get_post_status($post_id), array('inherit','auto-draft','draft'))) {
            return;
        }
        $custom_id = false;
        $feed_custom_id = get_option('nextcart_feed_custom_id');
        if ($feed_custom_id) {
            $custom_id = get_post_meta($post_id, $feed_custom_id, true);
        }
        $request = wp_remote_post(self::FEED_URL . $feed_id .'/update.php', array(
            'method' => 'POST',
            'timeout' => 30,
            'body' => array(
                'product_id' => $post_id,
                'product_custom_id' => $custom_id,
                'license_key' => $feed_license,
                'action' => 'delete'
                ),
            )
        );
        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            $this->nc_add_log('ERROR: Failed to delete product ID: ' . $post_id);
            return false;
        }
        $response = @json_decode(wp_remote_retrieve_body($request), 1);
        if (!$response || $response['result'] == 'error') {
            if (!empty($response['msg'])) {
                $this->nc_add_log('ERROR: Failed to delete product ID: ' . $post_id . '. Reason: ' . $response['msg']);
            } else {
                $this->nc_add_log('ERROR: Failed to delete product ID: ' . $post_id . '. Reason: Unknown');
            }
        } else {
            $this->nc_add_log('SUCCESS: Product ID: ' . $post_id . ' is deleted');
        }
    }
	
	public function nc_add_shopify_product_data_tab( $product_data_tabs ) {
		$product_data_tabs['shopify'] = array(
			'label' => 'Shopify',
			'target' => 'shopify_product_data',
			'priority' => 80,
		);
		return $product_data_tabs;
	}
	
	public function nc_add_shopify_product_data_fields() {
		?>
		<div id="shopify_product_data" class="panel woocommerce_options_panel">
			<?php
			woocommerce_wp_text_input(array(
				'id'            => '_shopify_id',
				'label'         => 'Shopify ID',
				'description'   => 'The original ID from Shopify store',
			));
			?>
		</div>
		<?php
	}

}

return new NCF_Admin();
