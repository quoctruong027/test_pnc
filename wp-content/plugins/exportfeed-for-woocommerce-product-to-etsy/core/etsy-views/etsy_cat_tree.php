<?php
function etcpf_get_categories($data, $parent = 0)
{
    foreach ($data as $key => $cat) {

        $wrap_start = "";
        $wrap_close = "";
        $style1 = 'style=padding-left:10px;';
        $data_toggle = "data-parent";

        if ($parent > 0) {

            $spacing = ($cat->level * 2) * 10;

            $style1 = "";
            $style2 = "style='padding-left:" . $spacing . "px; display:block'";

            $wrap_start = "<div class='" . $parent . "' " . $style2 . " data-child>";
            $wrap_close = "</div>";

        }
        echo $wrap_start;
        ?>
        <div id="<?php echo $cat->id ?>" <?= $style1 ?>>

            <input
                type='radio'
                name='etsy_category'
                value="<?php echo $cat->path . ':' . $cat->category_id ?>"
                onclick='etcpf_give_me_tree_line(this,"<?= $cat->level ?>")'
            />

            <span>
                <?php echo $cat->name ?>
            </span>

        </div>

        <?php
        echo $wrap_close;
        if (!empty($cat->children_ids)) {
            etcpf_get_categories($cat->children, $cat->id);
        }
    }
}

etcpf_get_categories($cpf_categories);
?>
<script>
    function etcpf_give_me_tree_line(selector, level) {

        jQuery.ajax({
            url: ajaxurl,
            data: {
                action: 'exportfeed_etsy',
                feedpath: etcpf_i18n.cmdEtsyProcessings,
                security: etcpf_i18n.nonce_check,
                level: 'prepare_from_product',
                category: jQuery(selector).val(),
                selected_products: etcpf_i18n.selected_p_ids
            },
            type: "post",
            success: function (res) {
                jQuery('#etcpf_cboxClose').html('Save and Close');
                console.log(res);
            }
        });
    }

    function upload_from_product() {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'exportfeed_etsy',
                feedpath: etcpf_i18n.cmdEtsyProcessings,
                security: etcpf_i18n.nonce_check,
                level: 'upload_from_product',
                selected_products: etcpf_i18n.selected_p_ids
            },
            success: function (res) {
                if (res) {
                    jQuery('#etsy_list').html('<h4>' + res + ' Product has been uploaded!</h4>');
                }
            }
        });
    }
</script>
