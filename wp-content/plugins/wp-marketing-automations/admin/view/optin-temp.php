<?php
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_instance = __( 'Hey', 'wp-marketing-automations' );
if ( is_object( $current_user ) ) {
	$user_instance .= ' ' . ucwords( $current_user->display_name ) . ',';
}
$non_sensitive_page_link = esc_url( "https://buildwoofunnels.com/non-sensitive-usage-tracking/?utm_source=autonami&utm_campaign=optin&utm_medium=text-click&utm_term=non-sensitive" );
$accept_link             = esc_url( wp_nonce_url( add_query_arg( array(
	'bwfan-optin-choice' => 'yes',
	'ref'                => filter_input( INPUT_GET, 'page' )
) ), 'bwfan_optin_nonce', '_bwfan_optin_nonce' ) );
$skip_link               = esc_url( wp_nonce_url( add_query_arg( 'bwfan-optin-choice', 'no' ), 'bwfan_optin_nonce', '_bwfan_optin_nonce' ) );
?>
<div id="bwfan_opt-wrap" class="bwfan_opt_wrap">
    <div class="bwfan_opt-logos">
        <img class="bwfan_opt-wrap-logo" width="80" height="80" src="<?php echo esc_url( plugin_dir_url( BWFAN_PLUGIN_FILE ) ) . 'admin/assets/img/wp-woo.jpg'; ?>"/>
        <i class="dashicons dashicons-plus"></i>
        <img class="bwf-wrap-logo" width="80" height="80" src="<?php echo esc_url( plugin_dir_url( BWFAN_PLUGIN_FILE ) ) . 'admin/assets/img/autonami.png'; ?>"/>
    </div>
    <div class="bwfan_opt-content">
        <p><?php echo esc_html( $user_instance ); ?><br></p>
        <h2><?php esc_html_e( 'Thank you for choosing ', 'wp-marketing-automations' );
			echo BWFAN_FULL_NAME . '!'; ?></h2>
        <p><?php esc_html_e( 'We are constantly improving the plugin and building in new features.', 'wp-marketing-automations' ) ?></p>
        <p><?php esc_html_e( 'Never miss an update! Opt in for security, feature updates and non-sensitive diagnostic tracking. Click Allow &amp; Continue!', 'wp-marketing-automations' ) ?></p>
    </div>
    <div class="bwfan_opt-actions" data-source="Autonami">
        <a href="<?php echo esc_url( $skip_link ); ?>" class="button button-secondary" data-status="no"><?php _e( 'Skip', 'wp-marketing-automations' ) ?></a>
        <a href="<?php echo esc_url( $accept_link ); ?>" class="button button-primary" data-status="yes"><?php _e( 'Allow &amp; Continue', 'wp-marketing-automations' ); ?></a>
        <div style="display: none" class="bwfan_opt_loader">&nbsp;</div>
    </div>
    <div class="bwfan_opt-permissions">
        <a class="bwfan_opt-trigger" href="#" tabindex="1"><?php esc_html_e( 'What permissions are being granted?', 'wp-marketing-automations' ) ?></a>
        <ul>
            <li id="bwfan_opt-permission-profile" class="bwfan_opt-permission bwfan_opt-profile">
                <i class="dashicons dashicons-admin-users"></i>
                <div>
                    <span><?php esc_html_e( 'Your Profile Overview', 'wp-marketing-automations' ); ?></span>
                    <p><?php esc_html_e( 'Name and email address', 'wp-marketing-automations' ) ?></p>
                </div>
            </li>
            <li id="bwfan_opt-permission-site" class="bwfan_opt-permission bwfan_opt-site">
                <i class="dashicons dashicons-admin-settings"></i>
                <div>
                    <span><?php esc_html_e( 'Your Site Overview', 'wp-marketing-automations' ) ?></span>
                    <p><?php esc_html_e( 'Site URL, WP version, PHP info, plugins &amp; themes', 'wp-marketing-automations' ) ?></p>
                </div>
            </li>
        </ul>
    </div>
    <div class="bwfan_opt-terms">
        <a href="<?php echo esc_url( $non_sensitive_page_link ); ?>" target="_blank"><?php _e( 'Non-Sensitive Usage Tracking', 'wp-marketing-automations' ) ?></a>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        $('.bwfan_opt-permissions .bwfan_opt-trigger').on('click', function () {
            $('.bwfan_opt-permissions').toggleClass('bwfan_opt-open');

            return false;
        });
        $('.bwfan_opt-actions a').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            var source = $this.parents('.bwfan_opt-actions').data('source');
            var status = $this.data('status');
            $this.parents('.bwfan_opt-actions').find(".bwfan_opt_loader").show();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'bwfan_optin_call',
                    source: source,
                    status: status,
                    _wpnonce: '<?php echo wp_create_nonce( 'bwfan_optin_call' ); ?>',
                },
                success: function (result) {
                    window.location = $this.attr('href');
                }
            });
        })
    })(jQuery);
</script>

<style>
    #bwfan_opt-wrap {
        width: 480px;
        -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        margin: 30px 0;
        max-width: 100%
    }

    #bwfan_opt-wrap .bwfan_opt-content {
        background: #fff;
        padding: 15px 20px 5px
    }

    #bwfan_opt-wrap .bwfan_opt-content p {
        margin: 0 0 1em;
        padding: 0;
        font-size: 1.1em
    }

    #bwfan_opt-wrap .bwfan_opt-actions {
        padding: 10px 20px;
        background: #C0C7CA;
        position: relative
    }

    #bwfan_opt-wrap .bwfan_opt-actions .bwfan_opt_loader {
        background: url("<?php echo esc_url(admin_url('images/spinner.gif')); ?>") no-repeat rgba(238, 238, 238, 0.5);
        background-position: center;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0
    }

    #bwfan_opt-wrap .bwfan_opt-actions .button {
        padding: 0 10px 1px;
        line-height: 35px;
        height: 37px;
        font-size: 16px;
        margin-bottom: 0
    }

    #bwfan_opt-wrap .bwfan_opt-actions .button .dashicons {
        font-size: 37px;
        margin-left: -8px;
        margin-right: 12px
    }

    #bwfan_opt-wrap .bwfan_opt-actions .button.button-primary {
        padding-right: 15px;
        padding-left: 15px
    }

    #bwfan_opt-wrap .bwfan_opt-actions .button.button-primary:after {
        content: ' \279C'
    }

    #bwfan_opt-wrap .bwfan_opt-actions .button.button-primary {
        float: right
    }

    #bwfan_opt-wrap.bwfan_opt-anonymous-disabled .bwfan_opt-actions .button.button-primary {
        width: 100%
    }

    #bwfan_opt-wrap .bwfan_opt-permissions {
        padding: 10px 20px;
        background: #FEFEFE;
        -moz-transition: background .5s ease;
        -o-transition: background .5s ease;
        -ms-transition: background .5s ease;
        -webkit-transition: background .5s ease;
        transition: background .5s ease
    }

    #bwfan_opt-wrap .bwfan_opt-permissions .bwfan_opt-trigger {
        font-size: .9em;
        text-decoration: none;
        text-align: center;
        display: block
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul {
        height: 0;
        overflow: hidden;
        margin: 0
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li {
        margin-bottom: 12px
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li:last-child {
        margin-bottom: 0
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li i.dashicons {
        float: left;
        font-size: 40px;
        width: 40px;
        height: 40px
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li div {
        margin-left: 55px
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li div span {
        font-weight: 700;
        text-transform: uppercase;
        color: #23282d
    }

    #bwfan_opt-wrap .bwfan_opt-permissions ul li div p {
        margin: 2px 0 0
    }

    #bwfan_opt-wrap .bwfan_opt-permissions.bwfan_opt-open {
        background: #fff
    }

    #bwfan_opt-wrap .bwfan_opt-permissions.bwfan_opt-open ul {
        height: auto;
        margin: 20px 20px 10px
    }

    #bwfan_opt-wrap .bwfan_opt-logos {
        padding: 20px;
        line-height: 0;
        background: #fafafa;
        height: 84px;
        position: relative
    }

    #bwfan_opt-wrap .bwfan_opt-logos .bwf-wrap-logo {
        position: absolute;
        left: calc(50% + 50px);
        left: -moz-calc(50% + 50px);
        left: -webkit-calc(50% + 50px);
        top: 20px
    }

    #bwfan_opt-wrap .bwfan_opt-logos img, #bwfan_opt-wrap .bwfan_opt-logos object {
        width: 80px;
        height: 80px
    }

    #bwfan_opt-wrap .bwfan_opt-logos .dashicons-plus {
        position: absolute;
        top: 50%;
        font-size: 30px;
        margin-top: -15px;
        color: #bbb;
        width: 30px;
        height: 30px;
    }

    #bwfan_opt-wrap i.dashicons.dashicons-plus {
        position: absolute;
        left: 50%;
        margin-left: -15px;
    }

    #bwfan_opt-wrap .bwfan_opt-logos .bwfan_opt-wrap-logo {
        left: calc(50% - 130px);
        left: -moz-calc(50% - 130px);
        left: -webkit-calc(50% - 130px);
        position: absolute;
    }

    #bwfan_opt-wrap .bwfan_opt-terms {
        text-align: center;
        font-size: .85em;
        padding: 5px;
        background: rgba(0, 0, 0, 0.05)
    }

    #bwfan_opt-wrap .bwfan_opt-terms, #bwfan_opt-wrap .bwfan_opt-terms a {
        color: #999
    }

    #bwfan_opt-wrap .bwfan_opt-terms a {
        text-decoration: none
    }

    #bwfan_opt-theme_connect_wrapper #bwfan_opt-wrap {
        top: 0;
        text-align: left;
        display: inline-block;
        vertical-align: middle;
        margin-top: 52px;
        margin-bottom: 20px
    }

    #bwfan_opt-theme_connect_wrapper #bwfan_opt-wrap .bwfan_opt-terms {
        background: rgba(140, 140, 140, 0.64)
    }

    #bwfan_opt-theme_connect_wrapper #bwfan_opt-wrap .bwfan_opt-terms, #bwfan_opt-theme_connect_wrapper #bwfan_opt-wrap .bwfan_opt-terms a {
        color: #c5c5c5
    }
</style>
