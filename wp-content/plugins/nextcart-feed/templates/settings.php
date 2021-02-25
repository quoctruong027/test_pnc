<?php
/** 
 * @package Next-Cart
 * 
 */
?>
<div class="wrap">
    <h2>Product Data Feed</h2>
    <form action="options.php" method="POST">
        <?php settings_fields('nextcart_feed_setting'); ?>
        <p>*Note: Changing these settings will affect the synchronization of the Product Data Feed.</p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="nextcart_feed_id">Feed ID</label></th>
                    <td>
                        <input style="min-width:800px;" name="nextcart_feed_id" type="text" id="nextcart_feed_id" value="<?php echo $feed_id; ?>" />
                        <p class="description"><?php _e('Enter the ID of your Product Data Feed tool.'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="nextcart_feed_license">Feed License</label></th>
                    <td>
                        <input style="min-width:800px;" name="nextcart_feed_license" type="text" id="nextcart_feed_license" value="<?php echo $feed_license; ?>" />
                        <p class="description"><?php _e('Enter the license key of your Product Data Feed tool.'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="nextcart_feed_custom_id">Product Custom ID</label></th>
                    <td>
                        <input style="min-width:800px;" name="nextcart_feed_custom_id" type="text" id="nextcart_feed_custom_id" value="<?php echo $feed_custom_id; ?>" />
                        <p class="description"><?php _e('Enter the meta key used for Product Custom ID (if any).'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<div class="wrap">
    <h2>
        Data Feed Logs
        <a id="delete-log" class="page-title-action" href="<?php echo esc_url(admin_url( 'admin.php?page=nc-feed&delete=logs' )); ?>">Clear log</a>
    </h2>
    <div style="background: #fff; border: 1px solid #e5e5e5; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 5px 20px;">
        <pre style="white-space: pre-wrap;"><?php echo file_exists(NC_LOG_DIR . 'product_data_feed.log') ? file_get_contents(NC_LOG_DIR . 'product_data_feed.log') : 'There are currently no logs to view.' ?></pre>
    </div>
</div>
