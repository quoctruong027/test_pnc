<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!defined('IMAGE_PATH')) {
    define('IMAGE_PATH', plugins_url('/', __FILE__) . '../../images/');
}
$feed_id = $_REQUEST['feed_id'];
$feed_data = $this->regenerateFeed($feed_id);
if ($feed_data !== null) {
    $feed_name = $feed_data->filename;
    $feed_remote_category = $feed_data->remote_category;
    $feed_local_category = $feed_data->category;
    $feed_provide = $feed_data->type;
    $feed_url = $feed_data->url;
    if(isset($feed_data->mappedlocalcategory)){
        $feed_category_names = $feed_data->mappedlocalcategory;
    }else{
        $feed_category_names = $feed_data->category;
    }

    if(isset($feed_data->mappedremotecategory)){
        $feed_etsy_category = $feed_data->mappedremotecategory;
    }else{
        $feed_etsy_category = $feed_data->category_path;
    }
    $feed_etsy_taxonomy = $feed_data->texonomy_path;
    $feed_id = $feed_data->id;
} /*else {
    Do sth that says no sufficient data to regenerate feed
}*/
?>
<div style="display: none;" id="ajax-loader-cat-import"><span id="gif-message-span"></span></div>
<div id="poststuff">
    <div class="postbox" id="error-page">
        <h3 class="hndle">Feed Details</h3>
        <div class="inside">
            <div class="inside-left">
            <p>Feed Name: <?php echo $feed_name; ?></p>
            <p>Etsy Category: <?php echo $feed_etsy_category; ?></p>
            <p >
                <?php if($feed_data->feed_type==1) {?>
                <a href="<?php echo admin_url() . 'admin.php?page=etsy-export-feed-custom-feed&action=edit&id=' . $feed_id . '&feed_type=' . $feed_data->feed_type ?>">Edit
                    Feed</a>
                <?php }else{?>
                    <a href="<?php echo admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=edit&id=' . $feed_id . '&feed_type=' . $feed_data->feed_type ?>">Edit
                        Feed</a>
                <?php } ?>
                <!-- | -->
                <a href="<?php echo wp_nonce_url(get_admin_url() . 'admin.php?page=etsy-export-feed-manage&action=delete&id=' . $feed_id, 'delete_feed', 'ETCPF_security'); ?>">Delete</a>
                <a target="_blank" href="<?php echo $feed_data->url; ?>">View</a>
            </p>
        </div>
        <div class="inside-right">
            <div class="inside-right-1">
            <p class="upload">
            <?php if ($feed_data->type != 'Productlistraw') { ?>
                    <a href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-admin&cmd=upload&id=' . $feed_id ?>">Upload to Etsy</a>
                <?php } ?>
            </p>
        </div>
        <!-- <div class="inside-right-2">
            <p>
            <span class="manage-feed">
                <a href="<?php echo get_admin_url() . 'admin.php?page=etsy-export-feed-manage' ?>">Goto Manage Feed</a>
                </span>
            </p>
        </div> -->

        </div>
        </div>
    </div>
    <?php if ($feed_data->type == 'Productlistraw')
        exit; ?>
    <?php if ($data['error_product_count'] > 0) { ?>
        <div class="postbox" id="error-page">
            <h3 class="hndle">Note</h3>
            <div class="inside">
                <p>The following products doesn't have the attributes requires for feed. Please enter the suitable
                    values
                    for the attributes. Once errors are resolved, you will be provided with regenerate option.</p>
                <p>If you ignore the warning errors feed may be to etsy sometimes, but if you ifgnore the error you won't be able to upload the error items.</p>
            </div>
        </div>
    <?php } ?>
    <div class="postbox" id="progress-details" style="display:none;">
        <h3 class="hndle">Error Feed Details</h3>
        <div class="inside listing-progress">
            <label>Your products are listing on Etsy</label>
            <label>Upload process may take some time depending upon the volume of products on your feed.</label>
            <div id="progress-bar" class="progress-body">
                <label id="shop_sync_percentage">20%</label>
                <div class="cssProgress">
                    <div class="progress2 ">
                        <div class="cssProgress-bar cssProgress-success-dupe cssProgress-active-dupe" data-percent="20%"
                             style="width: 20%">
                            <span class="cssProgress-label"></span>
                        </div>
                    </div>
                </div>
                <label class="progress-remaining"><span id="time_required_to_complete">***</span>
                    minutes remaining (approximately)</label>
            </div>
        </div>
    </div>
    <div class="error-tab-container">
        <a href="#" class="view-all"
           onclick="view_all('<?php echo $feed_id; ?>');"> <?php _e('All Products', 'etcpf_langstring'); ?> ( <span
                    id="total-product-count"><?php echo $data['total_products']; ?></span>)</a>
        <!--<a href="#" class="selected-products" onclick="showResolvedProducts();">Number of Resolved Errors (<span
                    id="resolved-products-rpc"><?php /*echo $data['success_products']; */ ?></span>)</a>-->
        <?php if ($data['error_product_count'] > 0) { ?>
            <a href="#" class="error-products active" onclick="error_products();">Resolve error in Bulk(<span
                        id="initial-error-product"><?php echo $data['error_product_count']; ?></span>)</a>
        <?php } ?>
    </div>
    <div id="all-product-div" <?php if ($data['error_product_count'] > 0) {
        echo 'style="display: none;"';
    } ?> ">
    <table id="error-free-product-table" style="display: none;"
           class="cp-list-table widefat suzandontable striped main-table all-product-table">
        <thead>
        <tr>
            <th class="checkbox-cell"><input type="checkbox"></th>
            <th><?php _e('Product Name'); ?></th>
            <th><?php _e('SKU', 'etcpf_langstring'); ?></th>
            <th><?php _e('Categories', 'etcpf_langstring'); ?></th>
            <th><?php _e('Action', 'etcpf_langstring'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php if (!empty($data['all_products']) && count($data['all_products']) > 0) {
            foreach ($data['all_products'] as $key => $ap) { ?>
                <tr id="row_all_product_<?php echo $ap['data'][0]->p_id; ?>">
                    <td><input type="checkbox"></td>
                    <td><?php echo $ap['data'][0]->p_name ?></td>
                    <td><?php echo $ap['data'][0]->sku ?></td>
                    <td><?php echo $ap['data'][0]->prod_categories ?></td>
                    <td id="aetsmtd_<?php echo $ap['data'][0]->p_id; ?>"
                        <?php if ($ap['contains_errors']) {
                            echo 'style="color:green;"';
                            $action = "<span style='color:red;'>[ Contains Errors ]</span> <a class='edit-error all-products-tab-edit-error' data-feed_id='".$feed_id."' data-target='" . $ap['data'][0]->p_id . "' >Resolve</a>";
                        } else {
                            echo 'style="color:green;"';
                            $action = "Resolved";
                        } ?> >
                        <?php echo $action; ?>
                        <div style="display: none;" class="error-resolve-div-pep"
                             id="allproduct_<?php echo $ap['data'][0]->p_id; ?>" data-feed_id='<?php echo $feed_id; ?>'
                             data-id="<?php echo $ap['data'][0]->p_id; ?>">
                            <ul>
                                <?php if (is_array($ap['data'])) {
                                    foreach ($ap['data'] as $k => $val) {
                                        if ($val->error_status != '2' && $val->error_status != '1') {
                                            ?>
                                            <li id="li_<?php echo $val->p_id . '_' . $val->error_code; ?>">
                                                <a class='edit-error insideerror-each-type'><?php echo ucfirst($this->getAttributeNameByCode($val->error_code)); ?>
                                                    Missing</a>
                                                <div class="div-for-input" style="display: none;">
                                                    <input type="text" class=""
                                                           id="product_value_<?php echo $val->p_id . '_' . $val->error_code; ?>"
                                                           placeholder=" Enter <?php $this->getAttributeNameByCode($val->error_code); ?> value">
                                                    <p class="actions">
                                                        <button type="submit" id="fuckuptest" value="submit"
                                                                onclick="return AMWSCP_AssignProductValueinWooFromAllProductTab(this,'<?php echo $val->p_id; ?>','<?php echo $val->error_code; ?>','<?php echo $feed_id; ?>');">
                                                            ✓
                                                        </button>
                                                        <button onclick="return closeProductEditBox(this);"
                                                                type="submit" class="edit-cancel-button-xttpy"
                                                                value="cancel">✗
                                                        </button>
                                                    </p>
                                                    <br>
                                                    <?php $products = $this->getproductbyErrorCode($feed_id, $val->error_code);
                                                    if (is_array($products) && count($products) > 0) { ?>
                                                        <span class="resolve-error-in-bluk-"<?php echo $val->error_code; ?>> <?php echo count($products); ?>
                                                            has same error <a
                                                                    onclick="return SelectBulkResolutionofecode('<?php echo $val->error_code; ?>')"
                                                                    style="cursor: pointer;"
                                                                    class="<?php echo $val->error_code; ?>">Resolve in bulk</a></span>
                                                    <?php } ?>
                                                </div>
                                            </li>
                                        <?php }
                                    }
                                } ?>

                            </ul>
                        </div>
                    </td>
                </tr>
            <?php }
        } ?>

        </tbody>

        <tfoot>
        <tr>
            <th class="checkbox-cell"><input type="checkbox"></th>
            <th><?php _e('Product Name'); ?></th>
            <th><?php _e('SKU', 'etcpf_langstring'); ?></th>
            <th><?php _e('Categories', 'etcpf_langstring'); ?></th>
            <th><?php _e('Message', 'etcpf_langstring'); ?></th>
        </tr>
        </tfoot>
    </table>
</div>

<!--<table id="error-resolved-product-table" style="display: none;" class="cp-list-table widefat striped main-table">
        <thead>
        <tr>
            <th><?php /*_e('Product Name'); */ ?></th>
            <th><?php /*_e('SKU', 'etcpf_langstring'); */ ?></th>
            <th><?php /*_e('Categories', 'etcpf_langstring'); */ ?></th>
            <th><?php /*_e('Message', 'etcpf_langstring'); */ ?></th>
        </tr>
        </thead>

        <tbody>

        <?php /*if (!empty($data['successproducts']) && count($data['successproducts']) > 0) {
            foreach ($data['successproducts'] as $key => $sp) { */ ?>
                <tr>
                    <td><?php /*echo $sp->p_name */ ?></td>
                    <td><?php /*echo $sp->sku */ ?></td>
                    <td><?php /*echo $sp->prod_categories */ ?></td>
                    <td <?php /*if ($sp->error_status == '0') {
                        echo 'style="color:orange;"';
                    } elseif ($sp->error_status == '-1') {
                        echo 'style="color:red;"';
                    } else {
                        echo 'style="color:green;"';
                    } */ ?> ><?php /*echo $sp->message */ ?></td>
                </tr>
            <?php /*}
        } */ ?>

        </tbody>

    </table>-->

<?php if ($data['error_product_count'] > 0) {
    ?>

    <table id="error-product-table" class="cp-list-table widefat striped main-table">
        <thead>
        <tr>
            <th><?php _e('Type', 'etcpf_langstring'); ?></th>
            <th><?php _e('Message', 'etcpf_langstring'); ?></th>
            <th><?php _e('Error Code | Details', 'etcpf_langstring'); ?></th>
            <th><?php _e('Action', 'etcpf_langstring'); ?></th>
            <!--<th><?php /*_e('Option', 'etcpf_langstring'); */ ?></th>-->
        </tr>
        </thead>

        <tbody>
        <tr>
            <?php
            if (is_array($data)
            && count($data['errortypes']) > 0
            ) {
            foreach ($data['errortypes'] as $key => $datum) {
            ?>
            <td <?php if ($datum->error_status == -1) {
                echo "style='color:red';";
            } else {
                echo "style='color:orange';";
            }
            ?> ><?php if ($datum->error_status == -1) {
                    echo "Error";
                } elseif ($datum->error_status == 0) {
                    echo "Warning";
                } else {
                    echo "All Good";
                }
                ?></td>
            <td <?php if ($datum->error_status == -1) {
                echo "style='color:red';";
            } else {
                echo "style='color:orange';";
            }
            ?> ><?php echo $datum->message; ?></td>
            <td><a target="_blank"
                   href="<?php echo "http://docs.exportfeed.com/error_doc.php?id=" . $datum->error_code; ?>"><?php echo $datum->error_code; ?>
                    | </a><a target="_blank"
                             href="<?php echo "http://docs.exportfeed.com/error_doc.php?id=" . $datum->error_code; ?>">See
                    details and how to resolve</a></td>
            <td>
                <a id="<?php echo $datum->error_code; ?>"
                   onclick="return showproductresolutionfield('product-attributes-add_<?php echo $datum->error_code; ?>');"
                   class="edit-error"
                   href="javascript:void(0)">Resolve</a>
            </td>
            <!-- <td><a onclick="return showfield('advance-command-section-<?php /*echo $datum->error_code; */ ?>');" class="edit-error" target="_blank"
                       href="javascript:void(0);">Use customization option</a></td>-->
        </tr>
        <!--<tr id="advance-command-section-<?php /*echo $datum->error_code; */ ?>" class="advance_command_class" style="display: none;">
                <td colspan="5">
                    <div class="inside advance-sec-block">
                        <div id="cpf_advance_command_default">
                            <div class="feed-advanced" id="feed-advanced-default" style="display: inline;">
                                <textarea class="feed-advanced-text" id="feed-advanced-text-default"
                                          autofocus></textarea>
                                <input class="button button-primary" type="button" id="bUpdateSettingDefault"
                                       name="bUpdateSettingDefault"
                                       title="Update Setting will update your feed data according to the Feed Customization enterd in Customization section."
                                       value="Update Settings"
                                       onclick="googleDoUpdateSetting('feed-advanced-text-default', 'cp_advancedFeedSetting-Etsy'); return false;">
                                <div id="updateSettingMsg">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>-->
        <tr id="product-attributes-add_<?php echo $datum->error_code; ?>"
            style="display: none; background-color: #dcdcdc8a;"
            class="product_attribute_table resolvable">
            <td class="inside-product-table" colspan="5">
                <div class="">
                    <div class="inside">
                        <div>
                            <label class="error-label" style="display: block;"> It is always best to resolve
                                attributes value in product level. Use the bulk action to resolve the
                                error
                                at once.From here you can change the missing attribute at once by providing the
                                missing
                                value or you can select the desired categories you want to change from the
                                dropdown. </label>

                            <!-- <div class="input-value" style="display: inline-block;">

                        </div> -->
                        </div>
                        <div class="all-products-table" style="width: 100%; margin: 0 auto;">
                            <!--<div class="show-products">Show
                                            <select>
                                                <option>5</option>
                                                <option>10</option>
                                                <option>15</option>
                                                <option>20</option>
                                                <option>30</option>
                                            </select>
                            </div> -->
                            <table id="table_<?php echo $datum->error_code; ?>"
                                   class="cp-list-table striped widefat suzandontable error-resolve-table">
                                <!--style="width: 100%;margin: 10px auto; table-layout: fixed;"-->
                                <thead>
                                <tr>
                                    <th class="checkbox-cell"><input
                                                onclick="return select_all_productschk('<?php echo $datum->error_code ?>',this);"
                                                type="checkbox"></th>
                                    <th><?php _e('Products', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Error Type', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Action', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Category', 'etcpf_langstring'); ?></th>
                                </tr>
                                <!-- <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr> -->
                                </thead>
                                <tbody>
                                <?php
                                $products = $this->getproductbyErrorCode($feed_id, $datum->error_code);
                                if (array($products) && count($products) > 0) {
                                    foreach ($products as $key => $product) {
                                        // $this->getProductEditUrl($product->p_id);
                                        ?>
                                        <tr id="row_<?php echo $product->p_id . '_' . $product->error_code; ?>">
                                            <td class="checkbox-cell"><input type="checkbox" name="select[]"
                                                                             value="<?php echo $product->p_id; ?>">
                                            </td>
                                            <td><?php echo $product->p_name; ?>
                                            </td>
                                            <td><?php echo $product->message; ?></td>
                                            <?php $ID = ($product->parent_id ? $product->parent_id : $product->p_id); ?>
                                            <td><a class='edit-error'
                                                   onclick="return showEditBox(this,'<?php echo $product->p_id; ?>','<?php echo $datum->error_code; ?>');"
                                                    <?php /*onclick="return etcpf_ClearDataFromDb(this,'<?php echo $ID; ?>','<?php echo $datum->error_code; ?>')"
                                                       href='<?php echo admin_url() . 'post.php?post=' . $ID . '&action=edit'; ?>'*/ ?>
                                                >Edit in Product</a>
                                                <div style="display:none;" class="error-resolve-div-pep"
                                                     id="edit_product_<?php echo $product->p_id . '_' . $datum->error_code; ?>">
                                                    <input type="text" class="" value=""
                                                           placeholder=" Enter <?php echo $this->getAttributeNameByCode($datum->error_code); ?> value"/>
                                                    <p class="actions">
                                                        <button type="submit" id="fuckuptest" value="submit"
                                                                onclick="return AssignProductValueinWoo(this,'<?php echo $product->p_id; ?>','<?php echo $datum->error_code; ?>','<?php echo $feed_id; ?>');">
                                                            ✓
                                                        </button>
                                                        <button type="submit" class="edit-cancel-button-xttpy"
                                                                value="cancel">✗
                                                        </button>
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                echo $product->prod_categories;
                                                ?>
                                            </td>
                                        </tr>
                                    <?php }
                                } ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th class="checkbox-cell"><input
                                                onclick="return select_all_productschk('<?php echo $datum->error_code ?>',this);"
                                                type="checkbox"></th>
                                    <th><?php _e('Products', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Error Type', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Action', 'etcpf_langstring'); ?></th>
                                    <th><?php _e('Category', 'etcpf_langstring'); ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="bulk-section">
                            <!--<span>Filter By:</span><select id="select_inner_table"
                                                           onchange="return show_inner_table(this.value);">
                                <option value="category">Categories</option>
                                <option value="products">categories1</option>
                                <option value="products1">categories3</option>
                                <option value="products2">categories4</option>
                            </select>-->

                            <!--<input type="checkbox" id="select_all_products_<?php /*echo $datum->error_code */ ?>"
                                       class="checkbox-iitpf"
                                       style="vertical-align: middle;"
                                       onclick="select_all_productschk('<?php /*echo $datum->error_code */ ?>')" >-->

                            <span id="table_<?php echo $datum->error_code; ?>-count-span"
                                  style="vertical-align: baseline;"></span>
                            <!--<label for="attribute" style="vertical-align: baseline;">Mapping</label>--><?php /*echo $this->getAttributes($datum->error_code); */ ?>
                            <input type="text" id="resolvation_value_<?php echo $datum->error_code ?>"
                                   name="attribute_value>" data-error_code="<?php echo $datum->error_code; ?>"
                                   placeholder="Enter value" style="vertical-align: middle;">
                            <input class="button action" id="submit-tek-button-<?php echo $datum->error_code; ?>"
                                   onclick="return ResolesubmitbuttonCall('<?php echo $datum->error_code; ?>')"
                                   type="submit" value="Apply" id="submit" name="submit"
                                   style="vertical-align: middle;">
                            <!-- Bulk action: <select>
                            <option value="category">attributes 1</option>
                            <option value="products">attribute 2</option>
                        </select> -->
                        </div>
                    </div>
            </td>
        </tr>
        <?php }
        } ?>
        </tbody>

    </table>

<?php } ?>
<input type="hidden" name="local_category_display" class="text_big" id="local_category_display"
       onclick="showGLocalCategories('Etsy')" value="" autocomplete="off" readonly="true"
       placeholder="Click here to select your categories">
<input type="hidden" name="local_category" id="local_category" value="<?php echo $feed_local_category; ?>">

<input type="hidden" name="etsy_category_display" class="text_big" id="etsy_category_display"
       onclick="showEtsyCategories('Etsy','default')" value="" autocomplete="off" readonly="true"
       placeholder="Click here to select your categories">
<input type="hidden" name="remote_category" id="remote_category" value="<?php echo $feed_remote_category; ?>">
<input type="hidden" id="etsy-category-path" name="category_path" value="<?php echo $feed_category_names; ?>">
<input type="hidden" id="etsy-taxonomy-path" name="taxonomy_path" value="<?php echo $feed_etsy_taxonomy; ?>">
<input type="hidden" name="feed_filename" id="feed_filename_default" class="text_big"
       value="<?php echo $feed_name; ?>">
<input id="feedidinput" type="hidden" name="feed_id" value="<?php echo $_REQUEST['feed_id']; ?>">
</div>

<!--<div class="error-page-gf" style="margin-top: 20px;">
    <input class="button button-primary" type="button" onclick="googledoGetFeed('Etsy' , this,true)"
           value="Create Feed">
</div>-->

<?php if ($data['error_product_count'] > 0) { ?>
    <script>
        jQuery(window).load(function () {
            jQuery("#error-free-product-table_wrapper").hide();
        });
    </script>
<?php } else { ?>
    <script>
        jQuery(window).load(function () {
            jQuery(".view-all").trigger("click");
        });
    </script>
<?php } ?>

<?php
if (get_option('ETCPF_RESOLVED') == 'yes') {
    ?>
    <script>
        jQuery(window).load(function () {
            jQuery('#no-error-popup-modal').show();
        });
    </script>
<?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('table.suzandontable').DataTable({
            drawCallback: function () {
                jQuery('.paginate_button:not(.disabled)', this.api().table().container())
                    .on('click', function () {
                        jQuery("#" + jQuery(this).attr('aria-controls')).find("input[type=checkbox]").attr('checked', false);
                        jQuery("#" + jQuery(this).attr('aria-controls') + '-count-span').html('');
                    });
            },
            "order": [[0, null]],
            /*stateSave: true*/
            /*"columns": [
                {"orderable": false},
                {"orderable": false},
                {"orderable": false},
                {"orderable": false}
            ],*/
            /*initComplete: function () {
                this.api().columns([4]).every(function () {
                    var column = this;
                    var select = jQuery('<select><option value="">Filter By category</option></select>')
                        .appendTo(jQuery(column.header()).empty())
                        .on('change', function () {
                            var val = jQuery.fn.dataTable.util.escapeRegex(
                                jQuery(this).val()
                            );
                            column
                                .search(val ? '^' + val + '$' : '', true, false)
                                .draw();
                        });
                    column.data().unique().sort().each(function (d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>')
                    });
                });
            }*/
        });
        jQuery('.categoryfilter').children('select').css({'margin-left': '220%'});
    });

    jQuery(window).load(function () {
        jQuery("#myBtn").click(function () {
            jQuery('#no-error-popup-modal').show();
        });
        /*jQuery('#no-error-popup-modal').click(function(){
            jQuery('#no-error-popup-modal').hide();
        });*/
        jQuery('.close').click(function () {
            jQuery('#no-error-popup-modal').hide();
        });
        //jQuery("#error-free-product-table_wrapper").hide();
    });

    jQuery(".edit-cancel-button-xttpy").on('click', function () {
        jQuery(this).closest('tr').find("input[type=checkbox]").attr('checked', false);
        jQuery(".error-resolve-div-pep").hide();
    });

    jQuery("#error-free-product-table").on("click", '.all-products-tab-edit-error', function (e) {
        t =this;
        let target = jQuery(this).attr('data-target');
        let pid = jQuery(this).attr('data-target');
        let feed_id = jQuery(this).attr('data-feed_id');
        console.log(pid);
        console.log(feed_id);
        fetchResolveSection(feed_id,pid,target);
        return;
    });

    jQuery(document).on("click", '.insideerror-each-type', function () {
        jQuery('.div-for-input').hide();
        jQuery(this).next('div').toggle();
        console.log(jQuery(this).next('div'));
    });

    function closeProductEditBox(selector) {
        jQuery('.error-resolve-div-pep').hide();
    }

</script>
<div id="no-error-popup-modal" class="modal postbox">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img src="<?php echo IMAGE_PATH; ?>/checked.png">
        <label>All products error are resolved. Click to regenerate feed to create error free feed.</label>
        <label><a href="javascript:void(0);"
                  onclick="return googledoGetFeed('Etsy' , this,true);">Regenerate-feed</a></label>
    </div>
</div>

<!--float: left;
padding: 2px 10px;
text-decoration: none;
transition: background-color .3s;

background-color: #2f4ea5e6;
color: white;
border: 1px solid #2f4ea5e6;-->
