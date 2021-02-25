
<style type="text/css">
    th {
        text-align: left;
    }
</style>
<?php 
    // include_once dirname(__FILE__) . '/../classes/etsyclient.php';
    // $etsyclient = new ETCPF_Etsy();
    $data = unserialize($cpf_item->data);
    $datas = json_decode($cpf_item->prepared_data);
?>
<div class="wrap">
    <h1>
        Edit Item : <?= $data->title ?>
        <?php etcpf_get_gif_loader('etcpf_edit_loader'); ?>
    </h1>
    <div id="edit_etsy_msg_box" class="updated settings-error">
        <p>Edit your product and submit to etsy store. </p>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-3" class="postbox-container">
                <input type="hidden" name="listing_id" value="<?php echo $cpf_item->listing_id; ?>"/>
                <input type="hidden" name="item_id" value="<?php echo $cpf_item->id; ?>"/>
                <table id="edit_listing_etsy">
                    <tr>
                        <th>Title</th>
                        <td>
<<<<<<< HEAD
                            <input type="text" name="item_title" readonly value="<?= $data->title ?>"/>
=======
                            <input type="text" name="item_title" readonly value="<?php echo $data->title; ?>"/>
>>>>>>> 3cebd42ec2c7f42fbbec2f898358c572f48c730f
                        </td>
                        <td id="title_msg_box">

                        </td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
<<<<<<< HEAD
                        <td><input type="text" name="item_quantity" readonly value="<?= $data->quantity ?>"/></td>
=======
                        <td><input type="text" name="item_quantity" readonly value="<?php echo $data->quantity; ?>"/></td>
>>>>>>> 3cebd42ec2c7f42fbbec2f898358c572f48c730f
                        <td>

                        </td>
                    </tr>
                    <tr>
                        <th>Price</th>
<<<<<<< HEAD
                        <td><input type="text" name="item_price" readonly value="<?= $data->price ?>"/></td>
=======
                        <td><input type="text" name="item_price" readonly value="<?php echo $data->price; ?>"/></td>
>>>>>>> 3cebd42ec2c7f42fbbec2f898358c572f48c730f
                        <td></td>
                    </tr>
                    <tr>
                        <th>Who Made It?</th>
                        <td>
                            <?php
                            // echo '<pre>';
                            // print_r('abc');
                            // echo '</pre>';die;
                            $who_made = $this->get_config('who_made');
                            $this->parseSettingOptions($who_made);
                            ?>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>When It Was Made?</th>
                        <td>
<<<<<<< HEAD
                            <input type="text" value="<?= $datas->when_made ?>" name="when_made">
=======
                            <input type="text" value="<?php echo $data->when_made; ?>" name="when_made">
>>>>>>> 3cebd42ec2c7f42fbbec2f898358c572f48c730f
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>State</th>
                        <td>
                            <?php
                            $state = $this->get_config('state');
                            $this->parseSettingOptions($state);
                            ?>
                        </td>
                        <td id="state_msg_box"></td>
                    </tr>
                    <tr>
                        <th>Shipping Template</th>
                        <td>
<<<<<<< HEAD
                            <?php
                            echo $this->get_shipping_listing($datas->shipping_template_id);
                            ?>
=======
                            <?php echo $this->get_shipping_listing($data->shipping_template_id); ?>
>>>>>>> 3cebd42ec2c7f42fbbec2f898358c572f48c730f
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <button class="button button-primary" id="update_btn" onclick="updateListing()">Update in
                                Etsy
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var html = '<p style="">';
        html += '<strong>Notice:</strong> Product is active. Etsy charges standard fees for active listings. <a href="https://www.etsy.com/legal/fees/" target="_blank">Learn more</a>';
        html += '</p>';

        var state = jQuery('#state').val();
        if (state == 'active')
            jQuery('#edit_etsy_msg_box').html(html);
        jQuery('#state').change(function () {
            var state = jQuery(this).val();
            if (state == 'active')
                jQuery('#state_msg_box').html(html);
        });
    });
</script>
<?php exit(); ?>