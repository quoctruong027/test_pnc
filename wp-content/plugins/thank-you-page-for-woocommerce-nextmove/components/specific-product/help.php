<?php
defined( 'ABSPATH' ) || exit;

ob_start();
?>
    <div style="display:none;" class="xlwcty_tb_content" id="xlwcty_component_settings<?php echo $config['slug']; ?>_help">
        <h3><?php echo $config['title'] . ' ' . __( 'Component Design & Settings', 'thank-you-page-for-woocommerce-nextmove' ); ?></h3>
        <p class="xlwcty_center"><img src="//storage.googleapis.com/xl-nextmove/specific-products.jpg"/></p>
        <table align="center" width="650" class="xlwcty_modal_table">
            <tr>
                <td width="50">1.</td>
                <td><strong>Heading:</strong> Enter any heading. Customize font size and text alignment too.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td><strong>Description:</strong> Enter any text here. Alignment option available here.</td>
            </tr>
            <tr>
                <td>3.</td>
                <td><strong>Choose Products:</strong> Here you can choose specific products which you want to display. Choose any number of products, all will gets displayed.</td>
            </tr>
            <tr>
                <td>4.</td>
                <td><strong>Layout:</strong> Plugin has 3 layouts `Grid (advanced)`, `Grid (theme)` & `List`.<br/>Grid (advanced) - It has a nice design with option to choose grid column size.<br/>Grid
                    (theme) - This is theme native grid design.<br/>List - This is again a nice & simple design. Select to view the final output.
                </td>
            </tr>
            <tr>
                <td>5.</td>
                <td><strong>Display Rating:</strong> Here if you want to display rating, choose option 'yes'</td>
            </tr>
            <tr>
                <td>6.</td>
                <td><strong>Border:</strong> You can add any border style, manage width or color. Or if you want to disable the border, choose border style option 'none'.</td>
            </tr>
        </table>
    </div>
<?php
return ob_get_clean();
