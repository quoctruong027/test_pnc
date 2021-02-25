<?php
/**
 * Coupon Email HTML to be sent in woocommerce email.
 */
?>
<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
    <tr>
        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
<div style="margin:0px auto;max-width:600px;background:#fff;margin-bottom: 30px;">
    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#fff;" align="center" border="0">
        <tbody>
        <tr>
            <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:0px;">
                <!--[if mso | IE]>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width:600px;">
                <![endif]-->
                <div style="margin:0px auto;max-width:600px;background:{{{data.section_color_lock}}};">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:{{{data.section_color_lock}}};" align="center" border="0">
                        <tbody>
                        <tr>
                            <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;padding-bottom:20px;padding-left:20px;padding-right:20px;padding-top:20px;">
                                <!--[if mso | IE]>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:top;width:600px;">
                                <![endif]-->
                                <div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="vertical-align:top;" width="100%" border="0">
                                        <tbody>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:15px;padding-right:0px;padding-left:0px;" align="center">
                                                <div style="cursor:auto;color:#d26e4b;font-family:\'Raleway\', Arial, sans-serif;font-size:20px;line-height:22px;text-align:center;">
                                                    <p style="margin: 0; padding: 0;">
                                                        <span style="font-size: 20px;line-height: 30px;color: {{{data.heading_color_lock}}};">{{{data.heading_lock}}}</span>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:15px;padding-right:0px;padding-left:0px;" align="center">
                                                <div style="cursor:auto;color:#777;font-family:\'Raleway\', Arial, sans-serif;font-size:15px;line-height:22px;text-align:center;">
                                                    <p style="margin: 0; padding: 0 0 10px; "><span style="font-size: 15px;line-height: 24px;color: {{{data.content_color_lock}}};">{{{data.content_lock}}}</span>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:15px;padding-right:0px;padding-left:0px;" align="center">
                                                <div style="cursor:auto;color:#d26e4b;font-family:\'Raleway\', Arial, sans-serif;font-size:25px;line-height:22px;text-align:center;">
                                                    <p style="background-color:{{{data.coupon_bg_color_lock}}}; margin: 0; padding: 10px; border: 2px dashed {{{data.coupon_border_color_lock}}};">
                                                        <span style="font-size: 22px;line-height: 30px;color: {{{data.coupon_text_color_lock}}}; letter-spacing: 5px; font-weight: 700;">{{{data.coupon_code_lock}}}</span>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:0px;" align="center">
                                                <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:100%;" align="center" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="border:none;border-radius:6px;color:#FFFFFF;cursor:auto;padding:10px 25px;" align="center" valign="middle" bgcolor="{{{data.button_bg_color_lock}}}">
                                                            <a href="{{{data.btn_link_lock}}}" style="display:block;text-decoration:none;color:{{{data.button_text_color_lock}}};font-family: Arial, sans-serif;font-size:16px;font-weight:600;text-transform:none;margin:0px;" target="_blank">{{{data.btn_txt_lock}}}</a>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>
                                </td></tr></table>
                                <![endif]-->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!--[if mso | IE]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
        </tbody>
    </table>
</div>
<!--[if mso | IE]>
</td></tr></table>
<![endif]-->
