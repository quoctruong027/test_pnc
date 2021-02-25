<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wpdb;
$table = $wpdb->prefix."etcpf_etsy_sync";
$results = $wpdb->get_results("SELECT * FROM $table");

$count = $wpdb->get_var("SELECT COUNT(*) FROM $table where mapped_status=0");
if($count > 0){
    echo "<script>";
    echo "jQuery(document).ready(function(){map_products()});";
    echo "</script>";
}
?>
<div style="display: none;" id="ajax-loader-cat-import">
    <div id="gif-message-span-for-more-than-one-feed"></div>
    <span id="gif-message-span"></span>
</div>
<div class="wrap">
    <!-- <button class="button-primary" type="button" onclick="return map_products();"> Start Mapping</button> -->
    <p><a href="<?php echo admin_url('admin.php?page=etsy-export-feed-admin'); ?>">Click Here</a> to create new feed.</p>
    <h2 class="hndle">Etsy Product List</h2>
    <div id="postbox-container-3" class="postbox-container" style="width:100%;">
        <div class="postbox" style="padding-top:5px;">
            <div class="inside">
                <table class="cp-list-table widefat fixed striped accounts">
                    <tr>
                        <th>S.n</th>
                        <th>Title</th>
                        <th>Listing Id</th>
                        <th>Sku</th>
                        <th>State</th>
                        <th>Message</th>
                    </tr>
                    <?php if($results){ ?>
                        <?php $i=1; foreach($results as $result){?>
                        <tr>
                            <td><?php echo $i?></td>
                            <td><?php echo $result->title ?></td>
                            <td><?php echo $result->listing_id ?></td>
                            <td><?php echo $result->sku ?></td>
                            <td><?php echo $result->state ?></td>
                            <td><p id="map_product_<?php echo $i++ ?>"><?php echo $result->message ?><p></td>
                        </tr>
                        <?php } ?>
                    <?php }else{?>
                        <tr>
                            <td>No Products found.</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
