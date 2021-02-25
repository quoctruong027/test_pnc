jQuery(document).ready(function (){
    if (0 < etcpf_i18n.selected_p_ids.length){
        showEtsyCategory();
        var html = "<div class='categoryListRemoteFrame'>" +
                        "<div class='categoryListRemote'>" +
                            "<h1>Categories <a href='#' onclick='upload_from_product()' class='button button-primary'>Upload</a></h1>" +
                            "<div id='etsy_list'>"+
                            "<img src = '"+etcpf_i18n.loadImg+"' style='margin-left:250px' />";
                            +"</div>" +
                        "</div>" +
                    "</div>";
        jQuery.etcpf_colorbox({
            width:'500px',
            height: '420px',
            html:html,
            title : 'Select Etsy Category'
        });
        jQuery('.categoryListRemoteFrame h1').append(etcpf_i18n.shipping_template);
    }

    jQuery('#shippingTemplate').change(function(e){
        var id = jQuery(this).val();
        jQuery.ajax({
            url : ajaxurl,
            type : 'post',
            data : {
                action : 'exportfeed_etsy',
                feedpath:etcpf_i18n.cmdEtsyProcessings,
                security:etcpf_i18n.nonce_check,
                level:6,
                shipping_id : id
            },
            success : function (res){
                console.log(res);
            }
        });
    });

    function showEtsyCategory(){
        jQuery('#etsy_list').html(etcpf_i18n.loadImg).css('margin-left','200px');
        jQuery.ajax({
            url : ajaxurl,
            type : 'post',
            data : {
                action:'exportfeed_etsy',
                feedpath:etcpf_i18n.cmdEtsyProcessings,
                security:etcpf_i18n.nonce_check,
                level : 'get_etsy_category_tree'
            },
            success : function (res){
                jQuery('#etsy_list').html(res);
            }
        });
    }

    jQuery('a.etcpf_list_on_etsy').click(function (e){
        var thisItem = jQuery(this).attr('data-product');
        jQuery('#cb-select-'+thisItem).attr('checked','checked');
        jQuery('#bulk-action-selector-top').val('export');
        jQuery('form#posts-filter').submit();
    });

});