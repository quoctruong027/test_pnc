<div id="poststuff">
    <div class="inside">
        <div class="bwfan_highlight_center">
            <form method="post" enctype="multipart/form-data" action="" id="bwfan-autonami-import">
                <div class="bwfan_heading"><?php esc_html_e( 'Import Automations from a JSON file', 'wp-marketing-automations' ); ?></div>
                <div class="bwfan_clear_20"></div>
                <div class="bwfan_content">
                    <p><?php esc_html_e( 'This tool allows you to import the automations from the JSON file.', 'wp-marketing-automations' ); ?></p>
                </div>
                <div class="bwfan_clear_20"></div>
                <input type="file" name="bwfan-import-automation" id="bwfan-import-automations" required/>
                <div class="bwfan_clear_30"></div>
                <div class="bwfan-import-button">
                    <button type="submit" class="bwfan_btn_blue_big import-automations" id="bwfan-imp-aut"><?php esc_html_e( 'Import', 'wp-marketing-automations' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
