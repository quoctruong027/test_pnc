<?php
defined( 'ABSPATH' ) || exit;

ob_start();
?>
    <div style="display:none;" class="xlwcty_tb_content" id="xlwcty_component_settings<?php echo $config['slug']; ?>_help">
        <h3><?php echo $config['title'] . ' ' . __( 'Component Design & Settings', 'thank-you-page-for-woocommerce-nextmove' ); ?></h3>
        <p class="xlwcty_center"><img src="//storage.googleapis.com/xl-nextmove/dynamic-coupons.jpg"/></p>
        <table align="center" width="650" class="xlwcty_modal_table">
            <tr>
                <td width="50">1.</td>
                <td><strong>Select Coupon:</strong> User have to select a coupon to inherit all settings of it if choose personalize option or display selected coupon code.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td><strong>Heading:</strong> Enter any heading. Customize font size and text alignment too.</td>
            </tr>
            <tr>
                <td>3.</td>
                <td><strong>Description:</strong> Enter any text here. Alignment option available here.</td>
            </tr>
            <tr>
                <td>4.</td>
                <td><strong>Coupon Code:</strong> Here you can modify the font size and text/ background color of coupon code.</td>
            </tr>
            <tr>
                <td>5.</td>
                <td><strong>Button:</strong> Here you can enter button text, link and some css properties like font size, text color and background color.</td>
            </tr>
            <tr>
                <td>6.</td>
                <td><strong>Personalize Coupon:</strong> Here you can Personalize the coupon. Append user or order related value in coupon like first name or order id etc. You can also set expiry of
                    coupon if want.
                </td>
            </tr>
            <tr>
                <td>7.</td>
                <td><strong>Display Coupon:</strong> Plugin has two options 'Immediate' or 'Click on Button'.<br/>Immediate - Here coupon will gets display immediately.<br/>Click on Button - Here
                    coupon gets displayed after user action i.e. button click. Choose this option to see further settings.
                </td>
            </tr>
            <tr>
                <td>8.</td>
                <td><strong>Border:</strong> You can add any border style, manage width or color. Or if you want to disable the border, choose border style option 'none'.</td>
            </tr>
        </table>
    </div>
<?php
return ob_get_clean();
