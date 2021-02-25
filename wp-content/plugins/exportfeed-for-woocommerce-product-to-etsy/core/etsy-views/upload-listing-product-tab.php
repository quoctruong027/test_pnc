<?php
$n = 1;
foreach ($cpf_products as $prod) {
    $product = wc_get_product($prod->id);
    $valid = true;
    if ($prod->stock <= 0 || $product->get_price() <= 0)
        $valid = false;
    ?>
    <tr class="no-items">
        <th scope="row" class="check-column">
            <label class="screen-reader-text" for="cb-select-"<?= $prod->ID ?>>Check the product for bulk
                actions</label>
            <input id="cb-select-<?= $prod->id ?>" type="checkbox" name="post[]" value="<?= $prod->id ?>"
                   data-remote="<?= $prod->remote_category ?>">
            <input id="valid" type="hidden" name="check_data" value="<?= $valid ?>">
            <div class="locked-indicator"></div>
        </th>
        <td class="colspanchange"><?= $n++; ?></td>
        <td class="colspanchange"><?= $prod->title; ?></td>
        <td class="colspanchange"><?= $prod->stock; ?></td>
        <td class="colspanchange"><?= $product->get_price(); ?></td>
        <td class="colspanchange remote_category"><?= $prod->remote_category; ?></td>
        <?php
        $view_more = "";
        $flag = FALSE;
        if (!isset($prod->upload_details->error)) {
            $flag = TRUE;
            $view_more = ' | <a href="#" onclick="view_etsy_uploaded_listing(' . $prod->id . ',this)">Details</a>';
        }
        ?>
        <?php $word = (TRUE == $flag) ? 'Update' : 'Upload'; ?>
        <td class="colspanchange">
            <a href="#" data-
               onclick="upload_listing(<?= $prod->id . ",'" . $prod->remote_category . "'" ?>)"><?= $word ?></a><?= $view_more ?>
            <?php if ($flag == TRUE) { ?>
                <input type="hidden" class="details_id" value="<?= $prod->upload_details->details_id ?>">
                <input type="hidden" class="shop_id" value="<?= $prod->upload_details->shop_id ?>">
                <input type="hidden" class="shipping_template_id"
                       value="<?= $prod->upload_details->shipping_template_id ?>">
                <input type="hidden" class="listing_id" value="<?= $prod->upload_details->listing_id ?>">
                <input type="hidden" class="who_made" value="<?= $prod->upload_details->who_made ?>">
                <input type="hidden" class="when_made" value="<?= $prod->upload_details->when_made ?>">
                <input type="hidden" class="state" value="<?= $prod->upload_details->state ?>">
                <input type="hidden" class="is_supply" value="<?= $prod->upload_details->is_supply ?>">
            <?php } ?>
        </td>
    </tr>
<?php } ?>
