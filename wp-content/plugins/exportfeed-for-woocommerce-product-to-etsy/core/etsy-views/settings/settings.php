<div style="display: none;" id="ajax-loader-cat-import"></div>
<div id="poststuff">

    <form id="settings-form">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-3" class="postbox-container" style="width: 100%;">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox" id="Product-management-box">
                        <h3 class="hndle"><span>Etsy Listing configuration</span></h3>
                        <div class="inside">


                            <table style="width:100%">

                                <tbody>

                                <?php
                                if ($etsy_configuration) {

                                    foreach ($etsy_configuration as $key => $value) { ?>

                                        <?php if (is_array($value) && count($value) > 0) { ?>
                                            <tr>
                                                <th style="text-align: left">
                                                    <?php
                                                    if($key=='is_supply'){
                                                        echo "Is a Supply or Finished Product ?";
                                                    }elseif($key=='who_made_it'){
                                                        echo ucfirst(str_replace('_', ' ', $key)) .' ?';
                                                    }elseif($key=='when_made'){
                                                        echo 'When was it made ?';
                                                    }elseif($key=='etsy_api_limit'){
                                                        echo 'Etsy API Limit <p style="font-size: 10px;">Note: For larger product quantities, <br> you may need to request Etsy to increase API limit for your account</p>';
                                                    }
                                                    else{
                                                        echo ucfirst(str_replace('_', ' ', $key));
                                                    }
                                                    ?>
                                                </th>

                                                <td>

                                                    <select style="min-width: 30%;" name="<?php echo $key ?>"
                                                            id="etcpf_<?php echo $key; ?>">
                                                        <option value=""> Choose One</option>
                                                        <?php foreach ($value as $k => $item) { ?>
                                                            <option <?php if( get_etsy_settings($key) == $item ) echo 'selected'; ?> value="<?php echo $item ?>"><?php echo $k; ?></option>
                                                        <?php } ?>

                                                </td>

                                                    <?php if($key=='shop_language'){ ?>
                                                        <td><input type="button" name="etsy-language-sync-button" class="button-primary" value="sync current language"></td>

                                                <?php } ?>

                                            </tr>

                                        <?php } ?>

                                    <?php }
                                } ?>

                                </tbody>

                            </table>


                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-3" class="postbox-container" style="width: 100%;">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox" id="Product-management-box">
                        <h3 class="hndle">
                            <span>Etsy Sync Settings (This settings allow which product attributes should be udated when inventory sync is performed)</span>
                        </h3>
                        <div class="inside">


                            <table style="width:100%">
                                <tbody>
                                <?php foreach ($exception_setting as $key => $value) { ?>
                                    <tr>
                                        <th style="text-align: left">
                                            <?php echo ucfirst($value); ?>
                                        </th>

                                        <td>
                                            <input type="hidden" name="<?php echo $value.'_sync' ?>" value="no">
                                            <input type="checkbox"
                                                   name="<?php echo $value ?>_sync"
                                                <?php if(get_etsy_settings($value.'_sync')=='yes') echo 'checked'; ?>
                                                   id="etcpf-sync-<?php echo $value ?>"
                                                   class="checkbox_cap" value="yes">
                                            <label for="<?php echo $value; ?>">
                                                Check if you want to sync WooCommerce product
                                                <b><?php echo $value; ?></b> with etsy item <b><?php echo $value; ?></b></label>
                                        </td>

                                    </tr>
                                <?php } ?>

                                </tbody>
                            </table>


                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-3" class="postbox-container" style="width: 100%;">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox" id="Product-management-box">
                        <h3 class="hndle">
                            <span>Etsy Cron Intervals Settings</span>
                        </h3>
                        <div class="inside">
                            <table style="width:100%">
                                <tbody>
                                <?php foreach ($cron_settings as $key => $value) { ?>
                                    <tr>
                                        <th style="text-align: left">
                                            <?php echo ucfirst(str_replace('_',' ',$key)); ?>
                                        </th>

                                        <td>

                                            <select style="min-width: 30%;" name="<?php echo $key ?>"
                                                    id="etcpf_<?php echo $key; ?>">
                                                <option value=""> Choose One</option>
                                                <option value="">--------------------------------------</option>
                                                <?php foreach ($value as $k => $item) { ?>
                                                    <option <?php if( get_etsy_settings($key) == $k ) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $item; ?></option>
                                                <?php } ?>

                                        </td>

                                    </tr>
                                <?php } ?>

                                </tbody>
                            </table>


                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-3" class="postbox-container" style="width: 100%;">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox" id="Product-management-box">
                        <h3 class="hndle"><span>Product Management</span></h3>
                        <div class="inside">


                            <table style="width:100%">
                                <tbody>
                                <tr>
                                    <th style="text-align: left">
                                        Stock Management
                                    </th>

                                    <td>
                                        <input type="hidden" name="stock_managed" value="no">
                                        <input type="checkbox" name="stock_managed"
                                               id="wpla_permissions_administrator_manage_amazon_listings"
                                               class="checkbox_cap" <?php if (get_etsy_settings('stock_managed') == 'yes') echo 'checked'; ?> value="yes" >
                                        <label for="wpla_permissions_administrator_manage_amazon_listings">Is stock managed
                                            ? </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th style="text-align: left">
                                        Default stock quantity
                                        <p style="font-size: 10px;">Note: For Unmanaged Stock products.</p>
                                    </th>

                                    <td>
                                        <input type="number" name="default_stock_quantity"
                                               id="etcpf_default_stock_quantity" class="checkbox_cap"
                                               value="<?php echo get_etsy_settings('default_stock_quantity'); ?>" placeholder="more than 0 & less than 999" min="1">
                                    </td>
                                </tr>

                                </tbody>
                            </table>


                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div>
            <input class="button-primary" type="button" name="submit" value="Save Settings">
        </div>
    </form>

</div>
<script>
    jQuery('[name="submit"]').click(function (e) {
        e.preventDefault();
        let formData = jQuery('#settings-form').serializeArray();
        console.log(formData)
        let payload = {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdsettingsUpdate,
            security: ETCPF.ETCPF_nonce,
            perform: 'update_etsy_settings',
            formData: formData,
        };
        jQuery('#ajax-loader-cat-import').show();
        etcpfglobalAjax(this, payload, function (error, data) {
            if (error) {
                console.log(error);
            } else {
                console.log(data)
            }
            jQuery('#ajax-loader-cat-import').hide();
        });
    });

    jQuery('[name="etsy-language-sync-button"]').on('click', function (event) {
        let payload = {
            action: 'exportfeed_etsy',
            feedpath: ETCPF.cmdlanguageUpdate,
            security: ETCPF.ETCPF_nonce,
            perform: 'update_etsy_language',
        };
        jQuery('#ajax-loader-cat-import').show();
        etcpfglobalAjax(this, payload, function (error, data) {
            if (error) {
                console.log(error);
            } else {
                location.reload();
                console.log(data)
            }
            jQuery('#ajax-loader-cat-import').hide();
        });
    });
</script>
