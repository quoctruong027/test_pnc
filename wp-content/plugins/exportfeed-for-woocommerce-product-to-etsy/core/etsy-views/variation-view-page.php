<?php if($status=="success") {?>
<?php print_r("Successfully uploaded");exit; ?>
<?php }else{ ?>
<div id="for_amzon" class="cpf_google_merchant_tutorials postbox report variation">
                    <h3 class="hndle" id="tutorial_title" > Select Variation  Listing</h3>
                    <form id = "variationForm" action="" method="POST">
                    <div class="inside variation">
                        
                        <div id="on_property_div" class="select-variation"> 
                            <span>Select the main variation property.</span>
                               <?php foreach ($data[0] as $key => $value): 
                                     if($key==0) {$checked = "checked";} else{ $checked=''; }
                               ?>

                                    <input class="on-property-attribute" type="radio" <?php echo $checked; ?> name="on_variation" value="<?php echo $value['property_id']; ?>" data-value="<?php echo $value['property_name']; ?>" /><?php echo $value['property_name']; ?>
                               <?php endforeach ?>
                        </div>
                        
                        <div id="select-variation-attributes" class="color-table">
                            <table id="table_color" class="widefat striped variation">
                                    <thead>
                                        <tr>
                                            <th>Variation</th>
                                            <th>Attribute 1</th>
                                            <th>Value</th>
                                            <th>Attribute 2</th>
                                            <th>Value</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <?php foreach($data as $k=>$value) { ?>
                                        <tr>
                                            <td>Variation</td>
                                            <?php foreach ($value as $key => $val) { ?>
                                              <td >
                                                <?php echo $val['property_name']; ?>
                                             </td>
                                              <td id="<?php echo $val['property_name']; ?>-<?php echo $k; ?>" class="<?php echo $val['property_name']; ?> <?php echo $val['values'][0]; ?>"><?php echo $val['values'][0]; ?></td>
                                             <?php } ?>

                                            <td class ="price-td">
                                                <select id="price-<?php echo $k; ?>" class="price-selection-select attribute-value-<?php echo $val['property_name']; ?>" required name="variation[<?php echo $k; ?>][price]">
                                                    <option value="">Price</option>  
                                                     <?php foreach ($value as $key => $val) { ?>
                                                          <option value="<?php echo $val['price']; ?>"><span value=""currency">$</span><?php echo $val['price']; ?></option>            
                                                     <?php } ?>     
                                                </select>
                                            </td>
                                            <td class ="sku-td">
                                                <select id="sku-<?php echo $k; ?>" class="sku-selection-select" required name="variation[<?php echo $k; ?>][sku]">
                                                    <option value="">Sku</option>           
                                                    <?php foreach ($value as $key => $val) { ?>
                                                    <option value="<?php echo $val['sku']; ?>"><?php echo $val['sku']; ?></option>         
                                                     <?php } ?>             
                                                </select>
                                            </td>

                                             <td class ="quantity-td">
                                                <?php if($value[0]['quantity']) {?>
                                                <select id="quantity-<?php echo $k; ?>" class="quantity-selection-select" required name="variation[<?php echo $k; ?>][quantity]">
                                                    <option value="">Quantity</option>           
                                                    <?php foreach ($value as $key => $val) { ?>
                                                    <option value="<?php echo $val['quantity']; ?>"><?php echo $val['quantity']; ?></option>         
                                                     <?php } ?>             
                                                </select>
                                                <?php }else{ ?>
                                                <input id="quantity-<?php echo $k; ?>" type="text" class="quantity-selection-input" required name="variation[<?php echo $k; ?>][quantity]" value="" Placeholder="Please enter the quantity" >
                                                <?php } ?>
                                            </td>
                                        </tr>

                                    <?php } ?>

                            </table>
                        </div>

                        <span><input type="Submit" value="Done" id="done_button" class="button-primary"></span>
                    
            </div>

            </form>
                
</div>
<?php } ?>