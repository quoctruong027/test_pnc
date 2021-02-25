<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
global $cp_feed_order, $cp_feed_order_reverse;
require_once 'core/classes/dialogfeedsettings.php';
require_once 'core/data/savedfeed.php';
$etsy = new ETCPF_Etsy();
$ifpremium = false;
?>
    <div style="display: none;" id="ajax-loader-cat-import">
        <div id="gif-message-span-for-more-than-one-feed"></div>
        <div id="gif-message-span"></div>
        </span></div>

    <div class="wrap">
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : "";
        if ($tab !== 'etsymanagefeed') {
            ?>
            <h2>
                <?php
                _e('Manage Cart Product Feeds', 'etsy-exportfeed-strings');
                $url = admin_url('admin.php?page=etsy-export-feed-admin');
                echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location=\'' . $url . '\';" value="' . __('Generate New Feed', 'etsy-exportfeed-strings') . '" />';
                ?>
            </h2>
            <?php etcpf_info();
            $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'managefeed';
            ?>
            <div class="nav-wrapper" style="margin-bottom: -30px; margin-top: 60px;">
                <nav class="nav-tab-wrapper">
                    <a href="<?php echo admin_url('admin.php?page=etsy-export-feed-admin&tab=createfeed'); ?>"
                       class="nav-tab <?php echo $active_tab == 'createfeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Create Feed', 'etsy-exportfeed-strings'); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=etsy-export-feed-manage&tab=managefeed'); ?>"
                       class="nav-tab <?php echo $active_tab == 'managefeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Manage Feed', 'etsy-exportfeed-strings'); ?></a>
                    <a href="http://www.exportfeed.com/contact/"
                       target="_blank"
                       class="nav-tab <?php echo $active_tab == 'contactus' ? 'nav-tab-active ' : ''; ?>"><?php _e('Contact Us', 'etsy-exportfeed-strings'); ?></a>
                    <?php
                    if (!class_exists('ETCPF_EtsyValidation')) {
                        $reg = new ETCPF_EtsyValidation;
                    }

                    $ifpremium = false;
                    if ($reg->results['status'] == 'Active') {
                        $checklicense = $reg->results;
                        $productname = explode(':', $checklicense['productname']);
                        if (strpos($productname[0], 'Pro') !== false) {
                            $ifpremium = true;
                        }
                    }
                    if ($ifpremium == false) {
                        ?>
                        <a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank"
                           class="nav-tab"><?php _e('Go Pro', 'etsy-exportfeed-strings'); ?></a>
                    <?php } ?>

                    <ul class="subsubsub" style="float: right;">
                        <?php if ($ifpremium == false) { ?>
                            <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Go
                                    Premium</a> |
                            </li>
                        <?php } ?>
                        </li>
                        <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Product
                                Site</a> |
                        </li>
                        <li><a href="http://www.exportfeed.com/faq/" target="_blank">FAQ/Help</a></li>
                    </ul>
                </nav>
            </div>
            <div class="clear"></div>
        <?php }

        global $message;

        // check for delete ID
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action == "delete") {
                $id = intval($_GET['id']);
                if (isset($id)) {
                    $delete_id = $id;
                    $message = etcpf_delete_feed($delete_id);
                }
            }
        }
        if (defined('DISABLE_WP_CRON') && (DISABLE_WP_CRON == true)) {
            $message = '<p>';
            $message .= '<span style="color:green;font-weight: bold">WordPress Cron is disabled. Set your Cron on server to update feeds.</span>';
            $message .= '<ol>';
            $message .= '<li>Log in to your hosting cpanel using your username and password.</li>';
            $message .= '<li>When you log into your cpanel you will see an option for cron jobs or scheduled tasks.</li>';
            $message .= '<li>Under the Common Settings, select <strong>Twice Per Hour</strong> to run cron every 30 minutes.</li>';
            $message .= '<li>Add Cron Command to Run as: <strong>wget -O /dev/null ' . site_url('wp-cron.php') . '</strong></li>';
            $message .= '<li>Click on <strong>Add New Cron Job</strong>, and then you are all ready.</li>';
            $message .= '</ol>';
            $message .= '<span style="color:green;font-weight: bold">If you have any confusion you can check our documentation. <a href="http://www.exportfeed.com/documentation/install-shoppingcartproductfeed-wordpress-plugin/" target="_blank">Click here</a> </span>';
            $message .= '</p>';
        }

        //WordPress Header ( May contain a message )
        $reg = new ETCPF_EtsyValidation;
        if (strlen($message)) {
            $message .= '<br>'; //insert break after local message (if present)
        }

        $message .= $reg->getMessage();
        if ($message && $ifpremium == false && !$reg->valid) {
            echo '<div id="setting-error-settings_updated" class="updated settings-error manage-feed-block">
                <span class="close-img" onclick="closemanagefeedblock()">Close</span>
               <p>' . $message . '</p></div>';
        }
        //"New Feed" button
        $url = admin_url('admin.php?page=etsy-export-feed-admin');
        //echo '<input style="margin-top:12px;" type="button" class="button-primary" onclick="document.location=\'' . $url . '\';" value="' . __( 'Generate New Feed', 'etsy-exportfeed-strings' ) . '" />';
        ?>

        <br/>
        <?php
        echo '
        <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {
           feedajaxhost = "' . plugins_url('/', __FILE__) . '";
        } );
        </script>';

        echo ETCPF_SettingDialogs::refreshTimeOutDialog();
        //        echo ETCPF_SettingDialogs::filterProductDialog();

        // The table of existing feeds
        etcpf_manage_page_main_table();
        ?>
        <br/>
    </div>
<?php

// The feeds table flat
/**
 *
 */
function etcpf_manage_page_main_table()
{

    global $wpdb;

    $feed_table = $wpdb->prefix . 'etcpf_feeds';
    $providerList = new ETCPF_ProviderList();
    $categoryMappingTable = $wpdb->prefix . 'etcpf_category_mappings';

    // Read the feeds
    $sql_feeds = ("SELECT f.*,description, GROUP_CONCAT(CM.local_category_slug) as mappedlocalcategory,GROUP_CONCAT(CM.showValue) as mappedremotecategory FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy on ( f.category=term_id and taxonomy='product_cat'  ) LEFT JOIN $categoryMappingTable CM on f.id=CM.feed_id  GROUP BY f.id");
//    echo $sql_feeds;die;
    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);

    // Find the ordering method
    $reverse = false;
    if (isset($_GET['order_by'])) {
        $order = $_GET['order_by'];
    } else {
        $order = '';
    }

    if ($order == '') {
        $order = get_option('et_cp_feed_order');
        $reverse = get_option('et_cpf_feed_order_reverse');
    } else {
        $old_order = get_option('et_cp_feed_order');
        $reverse = get_option('et_cpf_feed_order_reverse');
        if ($old_order == $order) {
            $reverse = !$reverse;
        } else {
            $reverse = FALSE;
        }
        update_option('et_cp_feed_order', $order);
        if ($reverse) {
            update_option('et_cpf_feed_order_reverse', TRUE);
        } else {
            update_option('et_cpf_feed_order_reverse', false);
        }
    }
    if (!empty($list_of_feeds)) {
        // Setup the sequence array
        $seq = false;
        $num = false;
        foreach ($list_of_feeds as $this_feed) {
            $this_feed_ex = new ETCPF_SavedFeed($this_feed['id']);
            switch ($order) {
                case 'name':
                    $seq[] = strtolower(stripslashes($this_feed['filename']));
                    break;
                case 'description':
                    $seq[] = strtolower(stripslashes($this_feed_ex->local_category));
                    break;
                case 'url':
                    $seq[] = strtolower($this_feed['url']);
                    break;
                case 'category':
                    $seq[] = $this_feed['category'];
                    $num = true;
                    break;
                case 'google_category':
                    $seq[] = $this_feed['remote_category'];
                    break;
                case 'type':
                    $seq[] = $this_feed['type'];
                    break;
                default:
                    $seq[] = $this_feed['id'];
                    $num = true;
                    break;
            }
        }

        // Sort the seq array
        if ($num) {
            asort($seq, SORT_NUMERIC);
        } else {
            asort($seq, SORT_REGULAR);
        }
        // Reverse ?
        if ($reverse) {
            $t = $seq;
            $c = count($t);
            $tmp = array_keys($t);
            $seq = false;
            for ($i = $c - 1; $i >= 0; $i--) {
                $seq[$tmp[$i]] = '0';
            }
        }
        $image['down_arrow'] = '<img src="' . ETCPF_URL . 'images/down.png" alt="down" style=" height:12px; position:relative; top:2px; " />';
        $image['up_arrow'] = '<img src="' . ETCPF_URL . 'images/down.png" alt="up" style=" height:12px; position:relative; top:2px; " />';
        ?>
        <!--	<div class="table_wrapper">	-->

        <table class="widefat" style="margin:12px 0px;" id="etcpf_manage_table_originals">
            <thead>
            <tr>
                <?php $url = get_admin_url() . 'admin.php?page=etsy-export-feed-manage&amp;order_by='; ?>
                <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="etcpf_select_all_feed">Select All</label><input id="etcpf_select_all_feed" type="checkbox"></td>
                <th scope="col" style="min-width: 50px;">
                    <a href="<?php echo $url . "id" ?>">
                        <?php
                        _e('ID', 'etsy-exportfeed-strings');
                        if ($order == 'id') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 120px;">
                    <a href="<?php echo $url . "name" ?>">
                        <?php
                        _e('Name', 'etsy-exportfeed-strings');
                        if ($order == 'name') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <a href="<?php echo $url . "category" ?>">
                        <?php
                        _e('Local category', 'etsy-exportfeed-strings');
                        if ($order == 'category') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="<?php echo $url . "google_category" ?>">
                        <?php
                        _e('Export category', 'etsy-exportfeed-strings');
                        if ($order == 'google_category') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>

                <th scope="col" style="width: 120px;">
                    <a href="<?php echo $url . "url" ?>">
                        <?php
                        _e('URL', 'etsy-exportfeed-strings');
                        if ($order == 'url') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 80px;"><?php _e('Last Updated', 'etsy-exportfeed-strings'); ?></th>
                <th scope="col"><?php _e('Products', 'etsy-exportfeed-strings'); ?></th>
                <!--<th scope="col"><?php /*_e('Reports', 'etsy-exportfeed-strings'); */ ?></th>-->
            </tr>
            </thead>
            <tbody>
            <?php $alt = ' class="alternate" '; ?>

            <?php
            $idx = '0';
            foreach (array_keys($seq) as $s) {
                $this_feed = $list_of_feeds[$s];
                $this_feed_ex = new ETCPF_SavedFeed($this_feed['id']);
                $pendcount = FALSE;
                ?>
                <tr <?php
                echo($alt);
                if ($pendcount) {
                    echo 'style="background-color:#ffdddd"';
                }
                ?> >
                    <th scope="row" class="check-column"><input type="checkbox" class="etcpf_select_feed"/></th>
                    <td><?php echo $this_feed['id']; ?></td>
                    <td><?php echo $this_feed['filename']; ?>
                        <input type="hidden" class="cpf_hidden_feed_id" value="<?php echo $this_feed['id']; ?>"/>
                        <div class="row-actions"><span class="id">ID: <?php echo $this_feed['id']; ?> | </span>
                            <span class="purple_xmlsedit"><a href="<?php echo $this_feed['url'] ?>" target="_blank"
                                                             title="View this Feed" rel="permalink">View</a>|</span>
                            <?php
                            if ($this_feed['feed_type'] == 1) {
                                $url_edit = get_admin_url() . 'admin.php?page=etsy-export-feed-custom-feed&action=edit&id=' . $this_feed['id'] . '&feed_type=' . $this_feed['feed_type'];
                            } else {
                                $url_edit = get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=edit&id=' . $this_feed['id'] . '&feed_type=' . $this_feed['feed_type'];
                            }
                            ?>
                            <span class="purple_xmlsedit"><a href="<?php echo($url_edit) ?>" target="_blank"
                                                             title="Edit this Feed" rel="permalink">Edit</a>|</span>
                            <?php
                            $url = wp_nonce_url(get_admin_url() . 'admin.php?page=etsy-export-feed-manage&action=delete&id=' . $this_feed['id'], 'delete_feed', 'ETCPF_security');
                            ?>
                            <span class="delete"><a href="<?php echo($url) ?>"
                                                    title="Delete this Feed">Delete</a>|</span>
                            <?php if ($this_feed['type'] !== 'Productlistraw') {
                                $table = $wpdb->prefix . 'etcpf_listings';
                                $checkfailed = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE feed_id = %d AND (uploaded = %d)", array($this_feed['id'], 3)));
                                $checkunuploaded = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE feed_id = %d AND (uploaded = %d)", array($this_feed['id'], 0)));
                                ?>
                                <span class="update">
                                <?php if ($this_feed['feed_title'] == 'uploaded') { ?>
                                    <a title="Uploads from the beginning."
                                       href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id'] . '&resubmit=1'; ?>">Re-Upload</a>
                                    </span>|
                                    <?php if ($checkfailed) { ?>
                                        <span><a title="Uploads only if failed listing exists."
                                                 href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id'] . '&uploadfailed=1'; ?>">Upload Failed</a></span>
                                    <?php }
                                    if ($checkunuploaded) { ?>
                                        |<span><a title="Uploads only if failed listing exists."
                                                  href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id']; ?>">Upload unuploaded</a></span>
                                    <?php }
                                } else {
                                    if ($this_feed['feed_title'] == 'uploading') { ?>
                                        <a title="Uploads from the beginning."
                                           href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id'] . '&resubmit=1'; ?>">
                                            Re-Upload</a>
                                        </span>|

                                        <a title="Resumes upload process."
                                           href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id']; ?>">Resume</a></span>
                                    <?php } else { ?>
                                        <a title="Upload"
                                           href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $this_feed['id']; ?>">Upload</a></span>
                                    <?php }
                                }
                            } ?>
                        </div>
                    </td>
                    <td>
                        <small><?php
                            if ($this_feed['mappedlocalcategory']) {
                                echo $this_feed['mappedlocalcategory'];
                            } else {
                                echo esc_attr(stripslashes($this_feed_ex->local_category));
                            }
                            ?></small>
                    </td>
                    <td>
                        <?php
                        if ($this_feed['mappedremotecategory']) {
                            echo $this_feed['mappedremotecategory'];
                        } else {
                            echo substr(str_replace(".and.", " & ", str_replace(".in.", " > ", esc_attr(stripslashes($this_feed['remote_category'])))), 0, 100) . "...";
                        }
                        ?>
                    </td>

                    <td><?php echo $this_feed['url'] ?></td>
                    <td><?php
                        $ext = '.' . $providerList->getExtensionByType($this_feed['type']);
                        $feed_file = ETCPF_FeedFolder::uploadFolder() . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;
                        if (file_exists($feed_file)) {
                            echo date("d-m-Y H:i:s", filemtime($feed_file));
                        } else {
                            echo 'DNE';
                        }
                        ?></td>
                    <td><?php echo $this_feed['product_count'] ?></td>
                    <!--<td><?php /*echo admin_url('') */ ?></td>-->
                </tr>
                <?php
                if ($alt == '') {
                    $alt = ' class="alternate" ';
                } else {
                    $alt = '';
                }

                $idx++;
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <?php
                $url = get_admin_url() . 'admin.php?page=google-merchant-manage-page&amp;order_by=';
                $order = '';
                ?>
                <th></th>
                <th scope="col" style="min-width: 50px;">
                    <a href="<?php echo $url . "id" ?>">
                        <?php
                        _e('ID', 'etsy-exportfeed-strings');
                        if ($order == 'id') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 120px;">
                    <a href="<?php echo $url . "name" ?>">
                        <?php
                        _e('Name', 'etsy-exportfeed-strings');
                        if ($order == 'name') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <a href="<?php echo $url . "category" ?>">
                        <?php
                        _e('Local Category', 'etsy-exportfeed-strings');
                        if ($order == 'category') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="<?php echo $url . "google_category" ?>">
                        <?php
                        _e('Export category', 'etsy-exportfeed-strings');
                        if ($order == 'google_category') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 120px;">
                    <a href="<?php echo $url . "url" ?>">
                        <?php
                        _e('URL', 'etsy-exportfeed-strings');
                        if ($order == 'url') {
                            if ($reverse) {
                                echo $image['up_arrow'];
                            } else {
                                echo $image['down_arrow'];
                            }
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 80px;"><?php _e('Last Updated', 'etsy-exportfeed-strings'); ?></th>
                <!--  <th scope="col"><?php //_e( 'View', 'etsy-exportfeed-strings' );
                ?></th> -->
                <!-- <th scope="col"><?php _e('Options', 'etsy-exportfeed-strings'); ?></th> -->
                <!-- <th scope="col"><?php //_e( 'Delete', 'etsy-exportfeed-strings' );
                ?></th> -->
                <th scope="col"><?php _e('Products', 'etsy-exportfeed-strings'); ?></th>
            </tr>
            </tfoot>

        </table>

        <input class="button button-primary" type="submit" value="Update Now" id="submit" name="submit"
               onclick="googleDoUpdateAllFeeds()">
        <div id="update-message">&nbsp;</div>
        <!--	</div> -->
        <?php
    } else {
        ?>
        <p><?php _e('No feeds yet!', 'etsy-exportfeed-strings'); ?></p>
        <?php
    }
}

function etcpf_delete_feed($delete_id = NULL)
{

    if (!isset($_GET['ETCPF_security']) && !wp_verify_nonce($_GET['ETCPF_security'], 'delete_feed')) {
        if (!current_user_can('editor') || !current_user_can('administrator')) {
            return false;
        }

    }
    // Delete a Feed
    global $wpdb;

    $feed_table = $wpdb->prefix . 'etcpf_feeds';
    $sql_feeds = $wpdb->prepare("SELECT * FROM $feed_table where id= %s", $delete_id);
    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);

    if (isset($list_of_feeds[0])) {
        $this_feed = $list_of_feeds[0];
        $ext = '.xml';
        if (strpos(strtolower($this_feed['url']), '.csv') > 0) {
            $ext = '.csv';
        }
        $upload_dir = wp_upload_dir();
        $feed_file = $upload_dir['basedir'] . '/etsy_merchant_feeds/' . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;

        if (file_exists($feed_file)) {
            unlink($feed_file);
        }
        $wpdb->query($wpdb->prepare("DELETE FROM $feed_table where id=%s", $delete_id));
        return "Feed deleted successfully!";
    }
    return false;
}
