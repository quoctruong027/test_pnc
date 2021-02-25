<script
        src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
        integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
        crossorigin="anonymous">
</script>

<style>
    .variation-upload-div {
        padding: 20px;
    }

    .variation-upload-div select {
        width: 222px;
    }
</style>

<div style="display: none;" id="ajax-loader-cat-import">
    <div id="gif-message-span-for-more-than-one-feed"></div>
    <div id="gif-message-span"></div>
</div>

<?php
if (strlen($cpf_msg) > 0):?>
    <div class="update-nag" id="report_msg" style="display:block; "><p><?= $cpf_msg ?></p></div>
<?php endif;
define('IMAGE_PATH', plugins_url('/', __FILE__) . '../../images/');

/**
 * @INFO : This will be implemented later in accordance with the etsy api criteria
 *
 * $occassion_list = array(
 * 'anniversary',
 * 'anniversary',
 * 'baptism',
 * 'bar_or_bat_mitzvah',
 * 'birthday',
 * 'canada_day',
 * 'chinese_new_year',
 * 'cinco_de_mayo',
 * 'confirmation',
 * 'christmas',
 * 'day_of_the_dead',
 * 'easter',
 * 'eid',
 * 'engagement',
 * 'fathers_day',
 * 'get_well',
 * 'graduation',
 * 'halloween',
 * 'hanukkah',
 * 'housewarming',
 * 'kwanzaa',
 * 'prom',
 * 'july_4th',
 * 'mothers_day',
 * 'new_baby',
 * 'new_years',
 * 'quinceanera',
 * 'retirement',
 * 'st_patricks_day',
 * 'sweet_16',
 * 'sympathy',
 * 'thanksgiving',
 * 'valentines',
 * 'wedding');
 */


global $wpdb;
$table = $wpdb->prefix . 'etcpf_profiles';
$profiles = $wpdb->get_results("SELECT * FROM $table");
if ($cpf_feedDetail->variation_upload_profile && intval($cpf_feedDetail->variation_upload_profile) > 0) {
    $currentProfile = $wpdb->get_row("SELECT * FROM $table WHERE  id={intval($cpf_feedDetail->variation_upload_profile)}");
    $preptable = $wpdb->prefix . 'etcpf_variationupload_preparation';
    $variationData = $wpdb->get_results("SELECT * FROM $preptable WHERE profile_id={$cpf_feedDetail->variation_upload_profile}", ARRAY_A);
    if ($variationData && count($variationData) === 0) {
        $variationDeleted = true;
    } else {
        $variationDeleted = false;
    }
    $onlyVariationAttributes = array_column($variationData, 'variation_attribute');
    $prefix = array_column($variationData, 'prefix');
    $suffix = array_column($variationData, 'suffix');
} else {
    $variationData = false;
}
?>

<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script> -->

<div class="etcpf-upload-block">

    <div class="etcpf-step" style="text-align:center;">
        <ol>
            <li class="active current">
                Ready
                <span class="step active">1</span>
            </li>
            <li>
                Uploading
                <span class="step">2</span>
            </li>
            <li>
                Finish
                <span class="step">3</span>
            </li>
        </ol>
    </div>
    <form id="regForm" action="javascript:void(0);">
        <header>
            <h1>Etsy Feed Upload</h1>
        </header>
        <section class="etcpf-upload-body">
            <!-- One "tab" for each step in the form: -->
            <div class="container-etsy-listing">

                <div class="etcpf-feed-details">
                    <p class="tab">Feed Name: <?php echo $cpf_feedDetail->filename; ?> </p>
                    <p class="tab">Feed Path: <?php echo $cpf_feedDetail->url; ?> </p>
                    <p class="tab">Total product in feed with
                        variation: <?php echo $cpf_feedDetail->product_count; ?></p>
                    <p class="tab">Prepared
                        items: <?php echo get_option('etsy_current_uploading_' . $_REQUEST['id']); ?></p>
                    <!-- <div class="tab" style="color:#31708f;display: none;"> Note: Please Click show more to see the
                         upload
                         process in
                         detail.
                     </div>-->
                </div>

            </div>


        </section>

        <?php /*<div class="etcpf-footer-action">
            <h3>Choose Preferences</h3>
            <label for="etsy-primary-color">Primary Color:</label>
            <select id="primary-color-attribute-etsy" name="etsy-primary-color" label="Primary color options">
                <optgroup label="Primary color options">
                    <option value="1213">Beige</option>
                    <option value="1">Black</option>
                    <option value="2">Blue</option>
                    <option value="1216">Bronze</option>
                    <option value="3">Brown</option>
                    <option value="1219">Clear</option>
                    <option value="1218">Copper</option>
                    <option value="1214">Gold</option>
                    <option value="5">Gray</option>
                    <option value="4">Green</option>
                    <option value="6">Orange</option>
                    <option value="7">Pink</option>
                    <option value="8">Purple</option>
                    <option value="1220">Rainbow</option>
                    <option value="9">Red</option>
                    <option value="1217">Rose gold</option>
                    <option value="1215">Silver</option>
                    <option value="10">White</option>
                    <option value="11">Yellow</option>
                </optgroup>
                <optgroup label="Offer multiple options?">
                    <option value="__variation">I offer more than one</option>
                </optgroup>
            </select>
            <label for="etsy-primary-color">Secondary Color:</label>
            <select class="select select-custom" id="etsy-attribute-271" name="Secondary color">
                <option value="">Choose secondary color</option>
                <optgroup label="Secondary color options">
                    <option value="1213">Beige</option>
                    <option value="1">Black</option>
                    <option value="2">Blue</option>
                    <option value="1216">Bronze</option>
                    <option value="3">Brown</option>
                    <option value="1219">Clear</option>
                    <option value="1218">Copper</option>
                    <option value="1214">Gold</option>
                    <option value="5">Gray</option>
                    <option value="4">Green</option>
                    <option value="6">Orange</option>
                    <option value="7">Pink</option>
                    <option value="8">Purple</option>
                    <option value="1220">Rainbow</option>
                    <option value="9">Red</option>
                    <option value="1217">Rose gold</option>
                    <option value="1215">Silver</option>
                    <option value="10">White</option>
                    <option value="11">Yellow</option>
                </optgroup>
                <optgroup label="Offer multiple options?">
                    <option value="__variation">I offer more than one</option>
                </optgroup>
            </select>
            <label for="etsy-primary-color">Occasion:</label>
            <select class="select select-custom" id="etsy-attribute-445" name="Secondary color">
                <option value="">Choose Occasion</option>
                <optgroup label="Occasion options">
                    <?php if(is_array($occassion_list)) foreach ($occassion_list as $key=>$values){?>
                    <option value="<?php echo $values; ?>"><?php echo $values ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>*/ ?>
        <div class="selector show_draggable_section" style="padding: 20px"> Select Variation Upload Type &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" <?php if (isset($cpf_feedDetail->variation_upload_type) && $cpf_feedDetail->variation_upload_type === 'single') echo 'checked'; ?>
                   style="margin-top: 0" name="choose" value="single"> Upload variations combined to one main variation
            <a href="#"
               title="If you have more than two variation attribute select this option. for eg: if your product has color, size & material attribute, variation formed will be (red-xl-cotton) as one variation."
               class="tooltip-section"><span class="dashicons dashicons-info"></span></a>
            <input type="radio" <?php if (isset($cpf_feedDetail->variation_upload_type) && $cpf_feedDetail->variation_upload_type === 'multiple') echo 'checked'; ?>
                   style="margin-top: 0" name="choose" value="multiple"> Upload multiple variation type separately <a
                    href="#"
                    title="If you've exactly one or two variation attribute select this option. In this option, if you've variation attribute color & size, there will be option to choose both color and size separately on etsy."
                    class="tooltip-section"><span class="dashicons dashicons-info"></span></a>
        </div>

        <?php if (isset($profiles) && is_array($profiles) && count($profiles) > 0) { ?>
            <div class="draggable_section variation-upload-div col-md-12" <?php if ($cpf_feedDetail->variation_upload_type !== 'single') {
                echo 'style="display: none;"';
            } ?> >
                <label for="variation_upload_type">Select the Profile for variation upload</label>
                <select name="variation_upload_profile" id="variation-upload-type">
                    <option readonly value="0">Select Variation Profile</option>
                    <?php foreach ($profiles as $key => $value) { ?>
                        <option <?php if (isset($cpf_feedDetail->variation_upload_profile) && intval($cpf_feedDetail->variation_upload_profile) == $value->id) echo 'selected'; ?>
                                value="<?php echo $value->id ?>"><?php echo $value->profile_name; ?></option>
                    <?php } ?>
                </select>
                <?php if (isset($cpf_feedDetail->variation_upload_profile) && intval($cpf_feedDetail->variation_upload_profile) > 0) { ?>
                    <p class="profile-variartion-notice notice is-dismissible notice-info"
                       style="display: inline-block; font-weight: 600; padding: 10px 38px 10px 10px;">
                        <?php if ($variationDeleted === true) { ?>
                            It seems the variation you selected earlier has been deleted, please either create new or slect another one.
                            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        <?php } else { ?>
                            This variation profile contains following variation attributes.
                            <br> <b>Variation order: [<?php echo implode(',', $onlyVariationAttributes) ?>] </b><br>
                            Value format:
                            <?php
                            $values = '<br>';
                            foreach ($onlyVariationAttributes as $k => $val) {
                                $values .= $prefix[$k] . $val . $suffix[$k];
                                if (count($onlyVariationAttributes) - 1 > $k) {
                                    $values .= $currentProfile->attribute_seperator;
                                }
                            }
                            echo '<b>' . $values . '</b>';
                            ?>
                            <br>Attributes that are not included in this variation profile will be discarded while uploading to etsy
                            <a target='_blank'
                               href='<?php echo admin_url("admin.php?page=etsy-export-feed-profiling&action=edit&id=" . $cpf_feedDetail->variation_upload_profile) ?>'>View
                                Detail</a>
                            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        <?php } ?>
                    </p>
                <?php } else { ?>
                    <p class="profile-variartion-notice notice is-dismissible notice-info"
                       style="display: inline-block; font-weight: 600; padding: 10px 38px 10px 10px;">Please Select
                        variation profile.</p>
                <?php } ?>
            </div>
        <?php } else {
            echo '<p id="no-profile-message" style="padding:20px">You have not created any variation profiles for etsy, please click <a href="' . admin_url("admin.php?page=etsy-export-feed-profiling&action=add_new") . '">add new</a> to create one. Thanks.</p>';
        } ?>

        <div class="etsy-variation-selection-div variation-upload-div" <?php if ($cpf_feedDetail->variation_upload_type !== 'multiple') echo 'style="display: none;"'; ?> >
            <p style="display: inline-block;">Select the variation attributes you want to make a on property value
                of:</p>
            <input id="property-value-etsy-input" type="text"
                   name="etsy_variation_on_property_<?php echo $_REQUEST['id'] ?>"
                   value="<?php echo get_option('etsy_variation_on_property_' . $_REQUEST['id']) ?>"
                   placeholder="eg:size (this is optional)">
            <input class="button" style="vertical-align: middle;" type="submit" name="submit"
                   id="property-value-etsy">
            <div class="property-value-etsy_spinner"></div>
            <!--<p class="profile-variartion-notice notice is-dismissible notice-info" style="display: inline-block; padding: 10px 38px 10px 10px;">We have attributes for instance: <b>length|ring-size</b></p>-->
        </div>

        <section>
            <div class="etcpf-footer-action">
                <span>Products that will be uploaded</span>
                <button class="button-primary" type="button" id="showspin"
                        onclick="return getCurrentListing( <?php echo $cpf_feedDetail->id; ?> );">Start Uploading
                </button>
                <div class="spinner"></div>
            </div>
        </section>

        <div class="etcpf-products-table">
            <table class="striped widefat" id="uploaded-table">
                <thead>
                <tr>
                    <th>Products</th>
                    <th>State</th>
                    <th>Listing ID</th>
                    <th> Result</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php
                if (is_array($cpf_data) && count($cpf_data) > 0) {
                    foreach ($cpf_data as $key => $value) {
                        $variationUploadResult = json_decode($value->variation_upload_result);
                        $imageID = isset($value->listing_image_id) ? ('Image Id: ' . $value->listing_image_id) : 'Feature image was not found';
                        if ($variationUploadResult) {
                            if (isset($variationUploadResult->results) && is_array($variationUploadResult->results->products)) {
                                $v_message = count($variationUploadResult->results->products) . ' variation uploaded.';
                            } else {
                                $v_message = 'No child product of this item';
                            }
                        } else {
                            $v_message = 'No child product of this item';
                        }
                        $productData = maybe_unserialize($value->data);

                        $echo = isset($value->listing_id) ? $value->listing_id : 'Will be fetched';
                        ?>

                        <tr id="table-tr-id-<?php echo $value->item_id; ?>">
                            <td class="prod_name"><?php echo $productData->title; ?>
                                <span class="error-img" id="error_image_<?php echo $value->item_id; ?>"
                                      style="position: relative;">
                                   <img src="<?php echo IMAGE_PATH; ?>tick.png">
                                </span>

                            </td>
                            <td><p id="product_state_id_<?php echo $value->item_id; ?>">
                                    <?php if (!empty($value->listing_id)) {
                                        echo $value->etsy_state;
                                    } else {
                                        echo 'Not uploaded';
                                    } ?>
                                </p>
                            </td>
                            <td><p id="product_item_id_<?php echo $value->item_id; ?>"
                                   class="<?php if (empty($value->listing_id)) {
                                       echo 'item_id';
                                   } ?>"><?php echo $echo; ?></p></td>
                            <td>
                                <?php if ($value->etsy_status == 'removed'){
                                    echo "<span id='upload_status_message_" . $value->item_id . "' class='error-resolve-msg'>Item Deleted from etsy<br><a class='relist-to-etsy' data-id='" . $value->id . "' href='javascript:void(0);'>Relist Item ?</a> <br>  <a class='delete-from-etsy' data-id='" . $value->id . "' onclick='delete_listing(" . $value->id . ",this);' href='javascript:void(0)'>Delete</a></span>";
                                } elseif ($value->listing_id !== NULL && empty($_REQUEST['resubmit'])) { ?>
                                    <p><span id="upload_status_message_<?php echo $value->item_id ?>"
                                             class="error-resolve-msg"><?php echo($value->listing_id !== NULL ? 'Uploaded' : 'Ready to upload'); ?></span>
                                    </p><?php }
                                else{ ?>
                                <p><span id="upload_status_message_<?php echo $value->item_id ?>"
                                         class="error-resolve-msg message_span"><?php echo($value->listing_id !== NULL ? 'Uploaded' : 'Ready to upload'); ?></span>
                                    <?php } ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>

                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <!-- Circles which indicates the steps of the form: -->
        <?php if (isset($_REQUEST['resubmit']) && $_REQUEST['resubmit'] == 1) { ?>
            <input type="hidden" id="etsy-feed-resubmit" name="resubmit-feed" value="1">
        <?php } else { ?>
            <input type="hidden" id="etsy-feed-resubmit" name="resubmit-feed" value="0">
        <?php } ?>

        <?php if (isset($_REQUEST['uploadfailed']) && $_REQUEST['uploadfailed'] == 1) { ?>
            <input type="hidden" id="etsy-feed-uploadfailed" name="uploadfailed-feed" value="1">
        <?php } else { ?>
            <input type="hidden" id="etsy-feed-uploadfailed" name="uploadfailed-feed" value="0">
        <?php } ?>
        <input type="hidden" id="currently-uploading-feed" name="feed_id" value="<?php echo $_REQUEST['id']; ?>">
    </form>
</div>

<script>
    let variationAttributes;
    jQuery(document).ready(function () {

        jQuery(document).on('click', '.notice-dismiss', function () {
            jQuery(this).parent().hide();
        });

        jQuery(".toggle-more-options").click(function () {
            jQuery(this).text(jQuery(this).text() == 'Show More' ? 'Hide' : 'Show More');
            jQuery("#uploaded-table").toggle();
        });

        jQuery(".error-img").click(function () {
            var attrid = this.id;
            var id = attrid.replace("error_image_", '');
            id = parseInt(id);
        });

        jQuery(document).on('click', '#property-value-etsy', function (event) {
            jQuery(".property-value-etsy_spinner").show();
            let payload = {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdUpdateOption,
                security: ETCPF.ETCPF_nonce,
                perform: 'fetch_etsy_orders',
                optionkey: jQuery('#property-value-etsy-input').attr('name'),
                propertykey: jQuery('#property-value-etsy-input').val()
            };

            etcpfglobalAjax('property-value-etsy-input', payload, function (err, data) {
                jQuery(".property-value-etsy_spinner").hide();
                if (data === false) {
                    console.log(data.message);
                }
            })
        });

        jQuery(document).on('change', '#variation-upload-type', function (event) {
            event.preventDefault();
            if (jQuery(this).val() === '0') {
                jQuery('.profile-variartion-notice').html("Please Select Appropriate variation Profile");
                return false;
            }
            submit_this_form();
        });

        jQuery(document).on('change', "[name='choose']", function (event) {
            let noprofileSelector = jQuery('#no-profile-message');
            if (jQuery(this).val() === 'single') {
                jQuery('.draggable_section.variation-upload-div').show();
                jQuery('#property-value-etsy-input').parent().hide();
                if (noprofileSelector.text()) {
                    noprofileSelector.show();
                    jQuery('#showspin').hide();
                }
            } else {
                jQuery('.draggable_section.variation-upload-div').hide();
                jQuery('#property-value-etsy-input').parent().show();
                if (noprofileSelector.text()) {
                    noprofileSelector.hide();
                    jQuery('#showspin').show();
                }
            }
            submit_this_form();
        });

        let submit_this_form = function () {
            let payload = {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmdsinglevariationpreparation,
                security: ETCPF.ETCPF_nonce,
                data: jQuery("form").serialize(),
                perform: 'updateProfile',
            };
            etcpfglobalAjax(this, payload, function (error, data) {
                if (error) {
                    console.log(error);
                } else {
                    if (data.variation_type === 'single') {
                        jQuery(".profile-variartion-notice").css({'display': 'inline-block'});
                        jQuery(".profile-variartion-notice").html(
                            "This variation profile contains following variation attributes.<br> Variation Order: [" + data.variation_data + "] <br>" +
                            "Value Format:<br>" + "<b>" + data.variation_result + "</b><br>" +
                            " Variation attributes of products that are not included in variation profiles will also be uploaded to etsy <a target='_blank' href='" + window.origin + "/wp-admin/admin.php?page=etsy-export-feed-profiling&action=edit&id=" + data.id + "'>View Detail</a><button type=\"button\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Dismiss this notice.</span></button>"
                        );
                        jQuery('.profile-variartion-notice button').addClass('notice-dismiss');
                    }
                    if (jQuery('#showspin').html() === 'Finished') {
                        jQuery('#ajax-loader-cat-import').show();
                        location.reload();
                    }
                }
            });
        }
    });


</script>
