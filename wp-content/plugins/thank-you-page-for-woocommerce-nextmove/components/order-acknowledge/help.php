<?php
defined( 'ABSPATH' ) || exit;

ob_start();
?>
    <div style="display:none;" class="xlwcty_tb_content" id="xlwcty_component_settings<?php echo $config['slug']; ?>_help">
        <h3><?php echo $config['title'] . ' ' . __( 'Component Design & Settings', 'thank-you-page-for-woocommerce-nextmove' ); ?></h3>
        <p class="xlwcty_center"><img src="//storage.googleapis.com/xl-nextmove/order-confirmation.jpg"/></p>
        <table align="center" width="650" class="xlwcty_modal_table">
            <tr>
                <td width="50">1.</td>
                <td><strong>Icon:</strong> Select 'Built-in' option to choose from available icons with color. 'Custom' option to upload your own icon. And 'none' option for no icon.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td><strong>Heading:</strong> Enter any Heading. Customize font size and text alignment too.</td>
            </tr>
            <tr>
                <td>3.</td>
                <td><strong>Sub Heading</strong> Enter any Sub-Heading. Customize font size and text alignment too.</td>
            </tr>

        </table>
    </div>
<?php
return ob_get_clean();
