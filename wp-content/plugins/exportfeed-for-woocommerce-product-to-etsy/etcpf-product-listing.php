<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('ETCPF_Product_Listing')) {

    class ETCPF_Product_Listing
    {
        var $selected_ids = '';
        var $prepared = false;

        function __construct()
        {
            /*
            if(is_admin()) {


                add_filter('post_row_actions', array(&$this,'etcpf_action_link'), 10, 2);
                // admin actions/filters
                add_action('admin_footer-edit.php', array(&$this, 'custom_bulk_admin_footer'));
                add_action('admin_print_styles', array(&$this, 'printProductsPageStyles'));
                add_action('load-edit.php',         array(&$this, 'custom_bulk_action'));
                add_action('admin_notices',         array(&$this, 'custom_bulk_admin_notices'));

                if (isset($_GET['ids']) && isset($_GET['etcpf_etsy_list']) ){
                    $this->selected_ids = sanitize_text_field($_GET['ids']);
                    $this->prepared = true;
                }
            }
            */
        }

        function etcpf_action_link($actions, $item)
        {
            global $post_type;
            if ($post_type == 'product') {
                $actions['list_on_etsy'] = "<a class='etcpf_list_on_etsy' data-product = '" . $item->ID . "' href='#'>" . __('List on Etsy', 'etcpf') . "</a>";
            }
            return $actions;
        }

        function custom_bulk_admin_footer()
        {
            global $post_type;

            if ($post_type == 'product') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery('<option>').val('export').text('<?php _e('List on Etsy')?>').appendTo("select[name='action']");
                        jQuery('<option>').val('export').text('<?php _e('List on Etsy')?>').appendTo("select[name='action2']");
                    });
                </script>
                <?php
            }
        }

        function printProductsPageStyles()
        {
            global $wp_scripts;

            $etsy = new ETCPF_Etsy;
            $ship = $etsy->get_shipping_listing();

            wp_register_script('google-merchant-etcpf_colorbox', plugins_url('js/jquery.etcpf_colorbox-min.js', __FILE__), array('jquery'));
            wp_enqueue_script('google-merchant-etcpf_colorbox');

            wp_register_style('etsy-export-feed-colorstyle', plugins_url('css/etcpf_colorbox.css', __FILE__));
            wp_enqueue_style('etsy-export-feed-colorstyle');

            // ProfileSelector
            wp_register_script('etcpf_profile_selector', ETCPF_URL . '/js/category_selector.js?ver=' . time(), array('jquery'));
            wp_enqueue_script('etcpf_profile_selector');


            wp_localize_script('etcpf_profile_selector', 'etcpf_i18n', array(
                    'ETCPF_URL' => ETCPF_URL,
                    'selected_p_ids' => $this->selected_ids,
                    'prepared' => $this->prepared,
                    'nonce_check' => wp_create_nonce('exportfeed_etsy_cpf'),
                    'cmdEtsyProcessings' => "core/ajax/wp/fetch_etsy_categories.php",
                    'loadImg' => ETCPF_URL . 'images/spinner-2x.gif',
                    'shipping_template' => $ship
                )
            );
        }

        function custom_bulk_action()
        {
            global $typenow;
            $post_type = $typenow;

            if ($post_type == 'product') {

                // get the action
                $wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
                $action = $wp_list_table->current_action();

                $allowed_actions = array("export");
                if (!in_array($action, $allowed_actions)) return;

                // security check
                check_admin_referer('bulk-posts');

                // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
                if (isset($_REQUEST['post'])) {
                    $post_ids = array_map('intval', $_REQUEST['post']);
                }

                if (empty($post_ids)) return;

                // this is based on wp-admin/edit.php
                $sendback = remove_query_arg(array('etcpf_etsy_list', 'untrashed', 'deleted', 'ids'), wp_get_referer());
                if (!$sendback)
                    $sendback = admin_url("edit.php?post_type=$post_type");

                $pagenum = $wp_list_table->get_pagenum();
                $sendback = add_query_arg('paged', $pagenum, $sendback);

                switch ($action) {
                    case 'export':
                        $exported = 0;
                        $prepared_ids = [];
                        foreach ($post_ids as $post_id) {
                            $prepare = $this->perform_export($post_id);
                            if (!$prepare) {
                                wp_die(__('Error exporting post.'));
                            }
                            $prepared_ids[$exported] = $prepare;
                            $exported++;
                        }
                        $sendback = add_query_arg(array('etcpf_etsy_list' => $exported, 'ids' => join(',', $post_ids)), $sendback);
                        $this->selected_ids = implode(",", $prepared_ids);
                        break;

                    default:
                        return;
                }

                $sendback = remove_query_arg(array('action', 'action2'), $sendback);

                echo '<script type="text/javascript">window.location.href = "'.$sendback.'"</script>';
                exit();
            }
        }

        function custom_bulk_admin_notices()
        {
            global $post_type, $pagenow;

            if ($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['etcpf_etsy_list']) && (int)$_REQUEST['etcpf_etsy_list']) {
                $message = sprintf(_n('Product exported.', '%s products exported.', $_REQUEST['etcpf_etsy_list']), number_format_i18n($_REQUEST['etcpf_etsy_list']));
                echo "<div class='updated'><p>{$message}</p></div>";
            }
        }

        function perform_export($post_id)
        {
            $etsy = new ETCPF_Etsy();
            $detail_id = $etsy->prepareListing($post_id);

            if ($detail_id > 0)
                return $detail_id;
            return false;
        }
    }
}

new ETCPF_Product_Listing();