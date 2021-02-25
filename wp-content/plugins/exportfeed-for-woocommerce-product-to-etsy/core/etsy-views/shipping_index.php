<div class="wrap cpf-page">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container containerfix">
                <div id="side-sortables" class="meta-box">
                    <div class="postbox" id="submitdiv">
                        <h3><a href="https://www.exportfeed.com/contact/" target="_blank">HELP</a></h3>
                        <div class="inside">
                            <div id="update-button">
                                <?php if (strlen($cpf_msg) > 0) {
                                    echo $cpf_msg;
                                } else {
                                    echo "<p style='margin: 0em 10px;'> --> Click on Refresh Shipping.</p>";
                                    echo "<p style='margin: 0em 10px;'> --> Reload the page.</p>";
                                    echo "<p style='margin: 0em 10px;'> --> Choose any one from list to make it default.</p>";
                                    echo "<p style='margin: 0em 10px;'> --> If you cannot find any shipping template in the right table, create one from below.</p>";
                                } ?>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <div class="postbox discriptionDiv">
                    <div id="update-button">
                        <p style="margin:0">This section allows you to manage your shipping templates based on their respective template ID. You can create new shipping templates, set a default template and get a general overview of overall shipping section.</p>
                    </div>
                </div>
                <div class="meta-box-sortables ui-sortable">
                    <div><?php echo $cpf_msg; ?></div>
                    <input type="hidden" name="page" value="eBay_settings_tabs">
                    <table class="cp-list-table widefat fixed striped accounts">
                        <thead>
                        <tr>
                            <th scope="col" id="site" style="width:48px;" class="manage-column column-site">Default</th>
                            <th scope="col" id="details" class="manage-column column-details column-primary">Template
                                ID
                            </th>
                            <th scope="col" id="user_name" class="manage-column column-user_name">Title</th>
                            <th scope="col" id="site" class="manage-column column-site">Origin Country ID</th>
                            <th scope="col" id="site" class="manage-column column-site">Processing Time</th>

                        </tr>
                        </thead>
                        <tbody id="the-list" data-cpf-lists="list:etsy_account">

                        <?php
                        if ($this->mate->count > 0) {

                            foreach ($cpf_shippings as $shipping) {

                                $default_shipping_template = ($this->mate->fields->shipping_template_id == $shipping->shipping_template_id) ? "checked='checked'" : "";
                                echo "	<tr>
	                            <td>
									<input type='radio' id='default_etsy_shipping' name='default_etsy_shipping' " . $default_shipping_template . " title='Default' value='" . $shipping->shipping_template_id . "' onclick='makeDefaultEtsyShipping(this)' />
										<div class='spinner1'></div>
								</td>
								<td>{$shipping->shipping_template_id}</td>
								<td>{$shipping->title}</td>
								<td>{$shipping->country}</td>
								<td>{$shipping->processing_days_display_label}</td>

							</tr>";
                            }
                        } else
                            echo "<tr><td colspan='5'>Goto Accounts tab and connect your Etsy shop first!</td></tr>";
                        ?>

                        </tbody>
                        <tfoot>
                        <tr>
                            <th scope="col" id="site" class="manage-column column-site">Default</th>
                            <th scope="col" id="details" class="manage-column column-details column-primary">Template
                                ID
                            </th>
                            <th scope="col" id="user_name" class="manage-column column-user_name">Title</th>
                            <th scope="col" id="site" class="manage-column column-site">Origin Country ID</th>
                            <th scope="col" id="site" class="manage-column column-site">Processing Time</th>

                        </tr>
                        <tr>
                            <th colspan="5">
                                <a href="#" onclick="updateShippingTemplate()" class="button button-primary">
                                    Refresh Shipping
                                </a>
                                <span class="msg_box_ship">

                                </span>
                                <?php etcpf_get_gif_loader('etcpf_gif_loader') ?>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="postbox shippingfix" id="submitdiv">
                    <h3>Create New Etsy Shipping Template</h3>
                    <div class="inside">
                        <div id="update-button">
                            <table style="margin: 10px 15px;text-align:left;width:80%" class="shippingfix">
                                <tr>
                                    <th>Title</th>
                                    <th><input type="text" name="title" style="width:100%" value placeholder="Title"/></th>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td><?= $cpf_country; ?></td>
                                </tr>
                                <tr>
                                    <th>Processing Days</th>
                                    <td><input type="number" name="min_processing_days" style="width:40%" placeholder="Min. Days"/> -
                                        <input type="number" name="max_processing_days" style="width:40%" placeholder="Max. Days"/></td>
                                </tr>
                                <tr>
                                    <th>Cost</th>
                                    <td><input type="text" name="primary_cost" style="width:40%" placeholder="One Item"/> /
                                        <input type="text"
                                               name="secondary_cost"
                                               style="width:40%"
                                               placeholder="additional cost per item"/></td>
                                </tr>
                                <tr style="line-height: 29px;">
                                    <th>Default</th>
                                    <td style="float:left"><input type="checkbox" id="make_default" /> </td>
                                </tr>
                                <tr class="msg_box_new_ship">
                                    <th></th>
                                </tr>
                                <tr>
                                    <th id="shipsubmit"><button class="button button-primary" onclick="addShippingTemplate()">Create Shipping for Etsy</button></th>
                                    <td><?php etcpf_get_gif_loader('shipping_loader') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
