<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
        integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
        crossorigin="anonymous">
</script>
<div class="etcpf-upload-block">
    <form id="regForm" action="" method="post">
        <header>
            <h2>Etsy Variation Profiling</h2>
        </header>
        <div class="draggable_section">
            <div class="divider personal_info col-md-8">
                <div class="form-group col-md-12">
                    <label class="col-md-3" for="profile_name">Profile Name</label>
                    <input class="col-md-9" type="text" name="profile_name" value="" required
                           placeholder="Enter identifiable name for this variation profile.">
                </div>
                <div class="form-group col-md-12">
                    <label class="col-md-3" for="profile_description">Attributes Separator: </label>
                    <input type="text" id="select-separator" name="attribute-seperator" value=""
                           placeholder="Enter the attribute seperator for eg: - or | or : etc">
                </div>
                <div class="container-table col-md-12">
                    <label class="col-md-3" for="name">Variations Attributes: </label>
                    <form class="col-md-9" method="post">
                        <select id="select-display">
                            <option value="0">Select attributes for profiling</option>
                            <?php
                            foreach ($product_attributes as $key => $value) { ?>
                                <option id="attribute-selection-option-<?php echo $value ?>"
                                        value="<?php echo $value; ?>"><?php echo $value; ?></option>
                            <?php } ?>
                        </select>

                    </form>
                    <table class="table wp-list-table widefat fixed striped ui-sortable col-md-offset-3" id="tablelist"
                           style="display: none; width: 75%; float: right;">
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

                        </tbody>
                    </table>
                </div>
                <input id="sorted-attribute" type="hidden" name="sorted_attributes"
                       value="">
            </div>
            <?php if (isset($product_attributes) && count($product_attributes) > 0) {
                $attributes = array_values($product_attributes);
                $seperator = get_etsy_settings('variation_seperation');
                ?>
                <div class="variation_right hidden col-md-4">
                    <div class="variation-selection-info-div">
                        <!--Attribute are added on the table based on the attributes you select
                        You can remove unnecessary attributes if you added them or don’t want to create single products based on those variations.
                        Move the attribute rows up and down to enable attributes sorting for profile listing-->
                        Attributes are added on the table based on your selection. You can remove unnecessary attributes
                        from the table and also move the attribute rows up and down to sort the attributes.
                    </div>

                    <table class="tbl_variation_right ">
                        <thead id="variation-head">
                        <tr>
                            <th>
                                Variation attribute Order
                            </th>
                        </tr>
                        </thead>
                        <tbody id="variation-body"></tbody>
                        <thead id="result-head">
                        <tr>
                            <th>Result</th>
                        </tr>
                        </thead>
                        <tbody id="result-body">
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
    (function (jQuery) {
        let attributeseperator = jQuery('#select-separator').val(),
            seperator = attributeseperator ? attributeseperator : '-',
            sorted = [],
            $sortable = jQuery("#tablelist > tbody"),
            sortableIn,
            ajaxLoader = jQuery('#ajax-loader-cat-import');
        $sortable.sortable({
            /*over: function (e, ui) {
                console.log("over");
                sortableIn = 1;
            },
            out: function (e, ui) {
                console.log("out");
                sortableIn = 0;
            },*/
            /*beforeStop: function (e, ui) {
                console.log(sortableIn);
                if (sortableIn === 0) {
                    if (confirm('Are you sure want to remove this photo?')) {
                        ui.item.remove();
                    } else {
                        ui.item.show();
                    }
                }
            },*/
            stop: function (event, ui) {
                let parameters = $sortable.sortable("toArray");
                sorted = parameters;
                jQuery('#sorted-attribute').val(sorted);
                if (parameters.length >= 2) formExample(parameters);
            }
        }).disableSelection();

        jQuery(document).on('input', '.variation-attribute-prefix , .variation-attribute-suffix', function (event) {
            event.preventDefault();
            updateSortable();
        });

        jQuery("#select-display").change(function () {
            let nodatatd = jQuery('#no-data-td');
            if (nodatatd.length) {
                nodatatd.closest('tr').remove();
            }
            jQuery("#tablelist").show();
            let attribute = jQuery(this).val();
            if (attribute === '0') {
                return false;
            }

            if (typeof jQuery('#' + attribute.replace(/ /g, '_space_')).html() !== 'undefined') {
                alert("This attibute " + attribute + " is already on list");
                jQuery("#select-display").prop("selectedIndex", 0);
                return false;
            }

            jQuery('#attribute-selection-option-' + attribute).attr('disabled', 'disabled');

            let html = '<tr id="' + attribute.replace(/ /g, '_space_') + '" class="ui-sortable-handle">' +
                '<td><img src="' + ETCPF.plugins_url + "images/move.png" + '" id="drag" /></td>' +
                '<td><input class="variation-attribute-prefix" type="text" placeholder="eg: sup" name="prefix_' + attribute.replace(/ /g, '_space_') + '" value=""></td>' +
                '<td>' + attribute + '</td>' +
                '<td><input class="variation-attribute-suffix" type="text" placeholder="eg: dup" name="suffix_' + attribute.replace(/ /g, '_space_') + '" value=""></td>' +
                '<td><button type="button" data-attribute="' + attribute.replace(/ /g, '_space_') + '" data-id="0" class="button deletebtn">Remove</button></td>' +
                '</tr>';
            jQuery("#tablelist tbody").append(html);
            updateSortable();
            if (jQuery('#tablelist > tbody > tr').length > 0) {
                jQuery(".variation_right").removeClass("hidden");
            } else {
                jQuery(".variation_right").addClass("hidden");
            }

        });

        jQuery(document).on('click', '.deletebtn', function (event) {
            event.preventDefault();

            let r = confirm("Are you sure you want to remove this attribute ?");
            if (r === false) {
                return false;
            }


            ajaxLoader.show();

            let attribute = jQuery(this).attr('data-attribute');
            jQuery('#attribute-selection-option-' + attribute).removeAttr('disabled');
            jQuery("#select-display").prop("selectedIndex", 0);
            jQuery(this).closest('tr').remove();

            updateSortable();

            if (jQuery('#tablelist > tbody > tr').length > 0) {
                jQuery(".variation_right").removeClass("hidden");
            } else {
                jQuery(".variation_right").addClass("hidden");
            }

            ajaxLoader.hide();
        });

        jQuery(document).on('input', '#select-separator', function () {
            seperator = jQuery('#select-separator').val();
            updateSortable();
        });

        var updateSortable = function () {
            $sortable = jQuery("#tablelist > tbody");
            $sortable.trigger('sortupdate');
            var parameters = $sortable.sortable("toArray");
            jQuery('#sorted-attribute').val(parameters);
            if (parameters.length > 0) formExample(parameters);
        };

        var formExample = function (parameters) {
            let attributeFormed = '',
                valueFormed = '';

            for (var i in parameters) {
                let prefix = jQuery("[name=prefix_" + parameters[i] + "]").val(),
                    suffix = jQuery("[name=suffix_" + parameters[i] + "]").val();
                attributeFormed += parameters[i].replace(/_space_/g, ' ');
                valueFormed += prefix + parameters[i].replace(/_space_/g, ' ') + ' value' + suffix;
                if (parameters.length - 1 > i) {
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
            jQuery('.variation_right').show();
        };

        jQuery('#profile-submitter').on('click', function (event) {
            let Attributes = jQuery('#sorted-attribute').val();
            if (Attributes.length <= 0) {
                alert("At least one variation attribute must be selected.");
                return false;
            } else {
                return true;
            }
        });

        jQuery("[name=profile_name]").on("input", function(event){
            event.preventDefault();
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
                        jQuery("[name=profile_name").after('<div class="validation-error col-md-4">'+data.data.message+'</div>');
                        jQuery(".validation-error").css({'color':'red'})
                    }else{
                        jQuery('#profile-submitter').attr('disabled', false);
                        jQuery(".validation-error").remove();
                    }
                }
            });
        });

    })(jQuery)

</script>
