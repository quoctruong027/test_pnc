<?php
$has_default_shipping = (NULL != $this->mate->fields->shipping_template_id) ? false : true;
if ($has_default_shipping)
    echo '<script>guide_to_etsy_shipping();</script>';
?>
<table class="cp-list-table widefat fixed striped accounts">
    <thead>
    <tr>
        <th scope="col" id="details" class="manage-column column-details column-primary">Shop ID</th>
        <th scope="col" id="user_name" class="manage-column column-user_name">Shop Name</th>
        <th scope="col" id="site" class="manage-column column-site">Site</th>
        <th scope="col" id="site" class="manage-column column-site">Etsy User</th>
        <th scope="col" id="site" class="manage-column column-site">Action</th>
    </tr>
    </thead>
    <tbody id="the-list" data-cpf-lists="list:etsy_account">
    <?php

    if (!isset($cpf_shops['error'])) {

        foreach ($cpf_shops as $shop) {
            echo "	<tr>
			            		<td>{$shop->shop_id}</td>
			            		<td>{$shop->shop_name}</td>
			            		<td>{$shop->url}</td>
			            		<td>{$shop->login_name}</td>
			            		<td><a href='#' onclick='deleteEtsyShop(" . $this->mate->fields->id . ")' title='Delete'>Delete</a></td>
							</tr>
			            		";
        }
    } else echo "<tr><td>" . $cpf_shops['error'] . "</td></tr>";
    ?>

    </tbody>

    <tfoot>
    <tr>
        <th scope="col" id="details" class="manage-column column-details column-primary">Shop ID</th>
        <th scope="col" id="shop_name" class="manage-column column-user_name">Shop Name</th>
        <th scope="col" id="shop_site" class="manage-column column-site">Site</th>
        <th scope="col" id="shop_user" class="manage-column column-site">Etsy User</th>
        <th scope="col" id="site" class="manage-column column-site">Action</th>
    </tr>
    </tfoot>
</table>