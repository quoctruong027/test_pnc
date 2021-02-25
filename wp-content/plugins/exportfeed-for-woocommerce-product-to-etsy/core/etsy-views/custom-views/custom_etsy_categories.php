<div id="etsyCategories">
    <?php
    if (is_array($cpf_categories) > 0) {

        foreach ($cpf_categories as $categories) {?>

            <div id="<?php echo $categories->category_name ?>" style='padding-left:10px;'>
                <?php $taxonomy_path = $categories->full_path_taxonomy_ids;?>
                <input type='radio' name='etsy_category'
                       value="<?php echo $categories->category_path . ':' . $categories->taxonomy_id ?>"
                       onclick='etsy_custom_cat_tree("<?php echo $cpf_localcat; ?>",this,0,"<?php echo $categories->category_path; ?>","<?php echo $taxonomy_path; ?>")'/>
                <span><?php echo $categories->category_name ?></span>

                <?php if (is_array($categories->children) && isset($categories->children[0])) {?>
                    <div class='<?php echo $categories->category_name ?>' style='padding-left:20px; display:none'>
                        <?php foreach ($categories->children as $childs) {?>
                            <div id='<?php echo $childs->category_name ?>'>
                                <?php $taxonomy_path = $childs->full_path_taxonomy_ids;?>
                                <input type='radio' value='<?php echo $childs->category_path . ":" . $childs->taxonomy_id ?>'
                                       name='etsy_category' onclick='etsy_custom_cat_tree("<?php echo $cpf_localcat; ?>",this,1,"<?php echo $childs->category_path; ?>","<?php echo $taxonomy_path; ?>")'/>
                                <span><?php echo $childs->category_name ?></span>

                                <?php if (isset($childs->children) && is_array($childs->children)) {?>
                                    <div class='<?php echo $childs->category_name ?>' style='padding-left:30px; display:none'>
                                        <?php foreach ($childs->children as $subchilds) {?>
                                            <div id='<?php echo $subchilds->category_name ?>'>
                                                <?php $taxonomy_path = $subchilds->full_path_taxonomy_ids;?>
                                                <input type='radio'
                                                       value='<?php echo $subchilds->category_name . ":" . $subchilds->taxonomy_id ?>'
                                                       name='etsy_category' onclick='etsy_custom_cat_tree("<?php echo $cpf_localcat; ?>",this,2,"<?php echo $subchilds->category_path; ?>","<?php echo $taxonomy_path; ?>")'/>
                                                <input id = "<?php echo $subchilds->category_id . '_catpath'; ?>" type="hidden" name="category_path" value="<?php echo $subchilds->category_path; ?>">
                                                <span><?php echo $subchilds->category_name ?></span>
                                            </div>
                                        <?php }?>
                                    </div>
                                <?php }?>

                            </div>
                        <?php }?>
                    </div>
                <?php }?>

            </div>
        <?php }?>
    <?php }?>
</div>