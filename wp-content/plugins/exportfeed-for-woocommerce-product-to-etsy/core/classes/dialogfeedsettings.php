<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_SettingDialogs
{

    static function formatIntervalOption($value, $descriptor, $current_delay)
    {
        $selected = '';
        if ($value == $current_delay) {
            $selected = ' selected="selected"';
        }

        return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
    }

    static function fetchRefreshIntervalSelect()
    {
        $current_delay = get_option('et_cp_feed_delay');

        return '
					<select name="delay" class="select_medium" id="selectDelay" style="width: 81%">' . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" .
            ETCPF_SettingDialogs::formatIntervalOption(300, '5 Minutes', $current_delay) . "\r\n" . '
					</select>';
    }

    public static function refreshTimeOutDialog()
    {
        // made width restriction from form table th more responsive
        global $wpdb;
        $html = '';
        $html .= '<div id="poststuff">
			        <div id="post-body" class="metabox-holder columns-2">
			  	      <div id="postbox-container-1" class="postbox-container">
			  		     <div class="postbox">
			  		        <h3 class="hndle"></h3>
						 <div class="inside export-target">
							<span class="dashicons dashicons-arrow-right"></span><b>Feeds will be refreshed automatically at the set interval duration.</b>
							<br/><br/>';
        if (get_etsy_settings("feed_update_interval")) {
            $html .= '<span class="dashicons dashicons-arrow-right"></span><b>Your automatic feed update is set to';
            $html .= '<span> ' . get_etsy_settings("feed_update_interval") . '</span>.</b>';
        } else {
            $html .= '<span class="dashicons dashicons-arrow-right"></span><b>Automatic Feed Update is not set, please go to settings page and set the cron interval.';
        }
        $html .= '</div>
			    </div>
			  </div>
			</div></div> <div class="clear"></div>';
        return $html;

    }

    public static function filterProductDialog()
    {

        global $wpdb;

        return '
		  <div id="cpf_filter_poststuff">
			<div class="postbox">
			  <h3 class="hndle" style="font-size: 14px;padding-left: 13px;">Select Feed type you want to display.</h3>
			  <div class="inside export-target" style="padding-left: 100px;">
			  <select name="cpf_filter_product_feed" id="cpf_filter_product_feed" style="width: 26%">
							<option value="0">Select Feed Type</option>
							<option value="1">Custom product feed</option>
							<option value="2">Feed by Category</option>
					</select>
					<span class="spinner" style="float: none;"></span>
			  </div>
			 </div>
		  </div>';

    }
}

?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#cpf_filter_product_feed").change(function () {
            jQuery('#cpf_filter_poststuff').find('.spinner').css('visibility', 'visible');
            var feed_type = jQuery("#cpf_filter_product_feed").val();
            jQuery.ajax({
                type: "POST",
                url: "<?php echo ETCPF_URL . "core/ajax/wp/fetch_feed_table.php" ?>",
                data: {feed_type: feed_type},
                success: function (res) {
                    jQuery('#cpf_filter_poststuff').find('.spinner').css('visibility', 'hidden');
                    jQuery("#cpf_manage_table_originals").html(res);
                }
            });
        });

        jQuery("#set_interval_time").html(jQuery("#selectDelay option:selected").html());

    });

</script>
