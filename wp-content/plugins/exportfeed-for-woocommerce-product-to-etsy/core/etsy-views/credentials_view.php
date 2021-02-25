<?php
if (!$cpf_api) { ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            /*var html;

            html = '<div class="etcpf_app_main">';
            html = '<div class="overlaydiv"><?php /* etcpf_get_biggif_loader("credential_gif",["position"=> "absolute","left"=>"158px"]) */ ?></div>';
            html += '<div class="etcpf_app_header"><span>Connect to Etsy App</span></div>';

            html += '<div class="etcpf_app_body">';
            html += '<ul>';
            html += '<li><strong>Step 1:</strong> <span> Create <a href="https://www.etsy.com/developers/register" target="_blank">Etsy Developer</a> account</span></li>';
            html += '<li><strong>Step 2:</strong> <span> Once logged in, <a href="https://www.etsy.com/developers/register" target="_blank"> Create a New App</a></li></span>';
            html += '<li><strong>Step 3:</strong> <span> On the Apps you\'ve made, click on SEE API KEY DETAILS</li></span>';
            html += '<li><strong>Step 4:</strong> <span> Copy the <strong>KEYSTRING</strong> and <strong>SHARED SECRET</strong>, paste them below</li></span>';
            html += '</ul>';
            html += '</div>';

            html += '<div class="etcpf_app_footer">';

            html += '<table border="0" class="etcpf_credential_box">';
            html += '<tr><td><label for="keystring">Keystring</label></td><td><input type="text" id="keystring" value name="keystring" placeholder="Enter Keystring here" /></td></tr>';
            html += '<tr><td><label for="shared_key">Shared Key</label></td><td><input type="text" id="shared_key" value name="shared_key" placeholder="Enter Shared Key here" /></td></tr>';
            html += '</table>';

            html += '<div id="credential-submit-area">';
            html += '<div id="etcpf_message"></div>';
            html += '<div id="btn_box"><a href="#" id="etcpf_credential_save_btn" class="button button-primary button-hero">Save & Continue</a></div>';
            html += '</div><div class="clear"></div>';

            html += '</div>';

            html += '</div>';

            jQuery.etcpf_colorbox({
                html: html,
                width: '520px',
                className: 'bordercurl',
                close : 'X'
            });
            jQuery('#etcpf_cboxLoadingOverlay').remove();
            jQuery('#etcpf_cboxLoadingGraphic').remove();*/
            
            jQuery('a.etsy-connector').click(function (e) {
                e.preventDefault();
                var key = 'jevniwtcoh47dy8cg1q6oci3';
                var secret = 'rnij99jfu7';
                if (!key) {
                    alert('Keystring is missing');
                    return;
                }
                if (!secret) {
                    alert('Secret Key is missing');
                    return;
                }
                jQuery('.overlaydiv').show();
                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        keystring: key,
                        shared_secret: secret,
                        security: ETCPF.ETCPF_nonce,
                        feedpath: ETCPF.cmdEtsyProcessings,
                        action: 'exportfeed_etsy',
                        level: 'save_credentials'
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function (res) {
                        etcpf_fetch_login_url(this);
                        /*jQuery('.overlaydiv').hide();
                        if (!res.status)
                            jQuery('#etcpf_message').html('<span style="color:red">KEYSRTING is not valid.</span>');
                        else {
                            jQuery('#btn_box').html('<a href="' + ETCPF.cmdEtsyShop + '" class="button button-primary button-hero">Connect with Etsy</a>');
                            jQuery('.etcpf_credential_box').hide();
                        }*/
                    }
                });
            });
        });
    </script>
<?php } ?>
