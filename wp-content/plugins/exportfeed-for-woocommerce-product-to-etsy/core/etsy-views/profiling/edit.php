<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
        integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
        crossorigin="anonymous">
</script>
<div style="display: none;" id="ajax-loader-cat-import"><span id="gif-message-span"></span></div>
<div class="etcpf-upload-block">
    <form id="regForm" action="" method="post">
        <header>
            <h1>Etsy Variation Profiling</h1>
        </header>
        <div class="draggable_section">
            <div class="divider col-md-8">
                <div class="form-group col-md-12">
                    <label class="col-md-3" for="profile_name">Profile Name</label>
                    <input class="col-md-9" type="text" name="profile_name"
                           value="<?php echo $profile[0]->profile_name; ?>" required>

                    <input type="hidden" name="current_profile_name" value="<?php echo $profile[0]->profile_name; ?>">
                </div>
                <div class="form-group col-md-12">
                    <label class="col-md-3" for="attribute-seperator">Separtor</label>
                <input type="text" id="select-separator" name="attribute-seperator" value="<?php echo $profile[0]->attribute_seperator; ?>">
                </div>

                <input id="sorted-attribute" type="hidden" name="sorted_attributes"
                       value="<?php echo implode(',', $product_attributes) ?>">
                <div class="container-table col-md-12">
                    <label class="col-md-3" for="name">Variations</label>
                    <form class="col-md-9" method="post">
                        <select id="select-display">
                            <option value="0">Select a Variation</option>
                            <?php
                            foreach ($all_product_attributes as $key => $value) { ?>
                                <option id="attribute-selection-option-<?php echo $value ?>" value="<?php echo $value; ?>"><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                    </form>
                    <table class="table wp-list-table widefat fixed striped ui-sortable col-md-offset-3" id="tablelist"
                           style="width: 75%; float: right;">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Prefix</th>
                            <th>Attribute</th>
                            <th>Suffix</th>
                            <th>Remove</th>
                        </tr>
                        </thead>
                        <tbody style="overflow: paged-y;">
                        <?php
                        foreach ($profile as $key => $value) {
                            if (isset($value->variation_attribute)) {
                                ?>
                                <tr id="<?php echo str_replace(' ', '_space_', $value->variation_attribute); ?>" class="ui-sortable-handle">
                                    <td>
                                        <img src="<?php echo ETCPF_URL ?>images/move.png"
                                             id="drag" alt=""></td>
                                    <td><input class="variation-attribute-prefix" type="text" placeholder="eg: sup"
                                               name="prefix_<?php echo str_replace(' ', '_space_', $value->variation_attribute);
                                               ?>"
                                               value="<?php echo $value->prefix; ?>"></td>
                                    <td><?php echo $value->variation_attribute; ?></td>
                                    <td><input class="variation-attribute-suffix" type="text" placeholder="eg: dup"
                                               name="suffix_<?php echo str_replace(' ', '_space_', $value->variation_attribute); ?>"
                                               value="<?php echo $value->suffix; ?>"></td>
                                    <td>
                                        <button type="button" data-id="<?php echo $value->id; ?>"
                                                class="button deletebtn">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4" id="no-data-td" style="text-align: center;">No Data Found</td>
                                </tr>
                            <?php }
                        } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        $seperator = get_etsy_settings('variation_seperation');
        if (isset($product_attributes) && count($product_attributes) > 0) {
            ?>
            <div class="variation_right col-md-4">

                <div class="variation-selection-info-div">
                    <!--Attribute are added on the table based on the attributes you select
                    You can remove unnecessary attributes if you added them or don’t want to create single products based on those variations.
                    Move the attribute rows up and down to enable attributes sorting for profile listing-->
                    Attributes are added on the table based on your selection. You can remove unnecessary attributes from the table and also move the attribute rows up and down to sort the attributes.
                </div>

                <table class="tbl_variation_right">
                    <thead id="variation-head">
                    <tr>
                        <th>
                            Variation attribute Order
                        </th>
                    </tr>
                    </thead>
                    <tbody id="variation-body">

                    <?php
                    $valueFormed = '';
                    $attributeFormed = '';
                    foreach ($profile as $key => $item) {
                        $attributeFormed .= $item->variation_attribute;
                        $valueFormed .= $item->prefix . $item->variation_attribute . ' value' . $item->suffix;
                        if($key < count($profile)-1){
                            $attributeFormed .= $item->attribute_seperator;
                            $valueFormed .= $item->attribute_seperator;
                        }
                    }
                    ?>

                    <tr>
                        <td><?php echo $attributeFormed; ?></td>
                    </tr>
                    </tbody>
                    <thead id="result-head">
                        <tr>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody id="result-body">
                    <tr>
                        <td><?php echo $valueFormed ?></td>
                    </tr>
                    </tbody>
                </table>

            </div>
        <?php } ?>

            <div class="form-group col-md-10">
                <input id="profile-submitter" class="col-md-2" type="submit" name="Submit" value="submit">
            </div>

        </div>
    </form>
</div>

<script>
    (function(jQuery){
        let ajaxLoader = jQuery('#ajax-loader-cat-import'),
            seperator = jQuery('#select-separator').val(),
            sorted = [];

        var $sortable = jQuery("#tablelist > tbody");
        $sortable.sortable({
            stop: function (event, ui) {
                var parameters = $sortable.sortable("toArray");
                console.log(parameters);
                sorted = parameters;
                jQuery('#sorted-attribute').val(sorted);
                /*if(parameters.length >= 2)*/
                formExample(parameters);
            }
        });

        jQuery("#select-display").change(function () {
            if (jQuery('#no-data-td').length) {
                jQuery('#no-data-td').closest('tr').remove();
            }
            jQuery("#tablelist").show();
            let attribute = jQuery(this).val();

            if(attribute === '0'){
                return false;
            }
            if (typeof jQuery('#' + attribute.replace(/ /g, '_space_')).html() !== 'undefined') {
                alert("This attibute "+attribute+" is already on list");
                jQuery("#select-display").prop("selectedIndex", 0);
                return false;
            }

            jQuery('#attribute-selection-option-'+attribute).attr('disabled','disabled');

            let html = '<tr id="' + attribute.replace(/ /g, '_space_') + '" class="ui-sortable-handle">' +
                '<td><img src="' + ETCPF.plugins_url + "images/move.png" + '" id="drag" /></td>' +
                '<td><input class="variation-attribute-prefix" type="text" placeholder="eg: sup" name="prefix_' + attribute.replace(/ /g, '_space_') + '" value=""></td>' +
                '<td>' + attribute + '</td>' +
                '<td><input class="variation-attribute-suffix" type="text" placeholder="eg: dup" name="suffix_' + attribute.replace(/ /g, '_space_') + '" value=""></td>' +
                '<td><button type="button" data-attribute="' + attribute.replace(/ /g, '_space_') + '" data-id="0" class="button deletebtn">Remove</button></td>' +
                '</tr>';
            jQuery("#tablelist tbody").append(html);
            updateSortable();
        });

        jQuery(document).on('click', '.deletebtn', function (event) {
            event.preventDefault();

            let r=confirm("Are you sure you want to remove this attribute ?");
            if(r===false){
                return false;
            }

            ajaxLoader.show();

            let attribute = jQuery(this).attr('data-attribute');
            jQuery('#attribute-selection-option-'+attribute).removeAttr('disabled');
            jQuery("#select-display").prop("selectedIndex", 0);

            selector = this;
            let id = jQuery(this).attr('data-id');
            if (id > 0) {
                payload = {
                    action: 'exportfeed_etsy',
                    feedpath: ETCPF.cmd_profiling_ajax_handler,
                    security: ETCPF.ETCPF_nonce,
                    perform: 'deleteattributes',
                    id: id
                };

                etcpfglobalAjax(this, payload, function (err, res) {
                    console.log(res);
                    if (!res.success) {
                        alert("There was some problem please try again later");
                    } else {
                        jQuery(selector).closest('tr').remove();
                        updateSortable();
                    }
                    jQuery('#ajax-loader-cat-import').hide();
                });
            } else {
                jQuery(this).closest('tr').remove();
                jQuery('#ajax-loader-cat-import').hide();
                updateSortable();
            }

        });


        jQuery(document).on('input', '.variation-attribute-prefix , .variation-attribute-suffix', function (event) {
            event.preventDefault();
            updateSortable();
        });

        var updateSortable = function () {
            $sortable = jQuery("#tablelist > tbody");
            $sortable.trigger('sortupdate');
            var parameters = $sortable.sortable("toArray");
            console.log(parameters)
            jQuery('#sorted-attribute').val(parameters);
            if(parameters.length >= 2) formExample(parameters);
        };

        jQuery(document).on('input', '#select-separator', function(){
            seperator = jQuery('#select-separator').val();
            updateSortable();
        });

        var formExample = function (parameters) {
            let attributeFormed = '',
                valueFormed = '';

            for( var i in parameters){
                let prefix = jQuery("[name=prefix_"+parameters[i]+"]").val(),
                    suffix = jQuery("[name=suffix_"+parameters[i]+"]").val();
                attributeFormed += parameters[i].replace(/_space_/g, ' ');
                valueFormed += prefix + parameters[i].replace(/_space_/g, ' ') + ' value' + suffix;
                if(parameters.length-1 > i){
                    attributeFormed += seperator;
                    valueFormed += seperator;
                }
            }

            let variations = '<tr><td>';
            variations += attributeFormed;
            variations += '</td></tr>';
            let result = '<tr><td>';
            result += valueFormed;
            result += '</td></tr>';

            jQuery('#variation-body').html(variations);
            jQuery('#result-body').html(result);
        };

        jQuery("[name=profile_name]").on("input", function(event){
            event.preventDefault();
            if(jQuery(this).val() === jQuery("[name=current_profile_name").val()){
                jQuery(".validation-error").remove();
                return true;
            }

            let payload = {
                action: 'exportfeed_etsy',
                feedpath: ETCPF.cmd_profiling_ajax_handler,
                security: ETCPF.ETCPF_nonce,
                data: {'profile_name':jQuery(this).val()},
                perform: 'validateprofile',
            };

            etcpfglobalAjax(this, payload, function(err, data){
                if(err){
                    alert(err)
                }else{
                    if(data.success===false){
                        jQuery('#profile-submitter').attr('disabled', true);
                        jQuery("[name=profile_name").focus();
                        console.log(jQuery('.validation-error').html())
                        if(jQuery('.validation-error').html()){
                            jQuery(".validation-error").html(data.data.message);
                        }else{
                            jQuery("[name=profile_name").after('<div class="validation-error col-md-4">'+data.data.message+'</div>');
                        }
                        jQuery(".validation-error").css({'color':'red'})
                    }else{
                        jQuery('#profile-submitter').attr('disabled', false);
                        jQuery(".validation-error").remove();
                    }
                }
            });
        });

        jQuery('#profile-submitter').on('click', function(event){
            let Attributes = jQuery('#sorted-attribute').val();
            if(Attributes.length <= 0){
                alert("At least one variation attribute must be selected.");
                return false;
            }else{
                return true;
            }
        })

    })(jQuery)

</script>
