<tr>
    <form id="etsy_shipping">
        <td><input type="text" name="title" style="width:100%" value placeholder="Title"/></td>
        <td><?= $cpf_country; ?></td>
        <td>
            <input type="text" name="min_processing_days" style="width:80px" placeholder="Min. Days"/> - <input
                type="text" name="max_processing_days" style="width:80px" placeholder="Max. Days"/>
        </td>
        <td>
            <input type="text" name="primary_cost" style="width:80px" placeholder="Min. Cost"/> - <input type="text"
                                                                                                         name="secondary_cost"
                                                                                                         style="width:80px"
                                                                                                         placeholder="Max. Cost"/>
        </td>
        <td>

            <div class="dashicons dashicons-plus" style="cursor:pointer" id="addShippingIcon"
                 onclick="addShippingTemplate(this)"></div>
            <div class="" id="loadImg1"></div>
        </td>
    </form>
</tr>