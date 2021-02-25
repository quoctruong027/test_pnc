<style type="text/css">
    .current a {
        cursor: pointer;
    }
</style>
<?php
require_once plugin_dir_path(__FILE__) . '/../../etsy-export-feed-wpincludes.php';

$iconurl = plugins_url('/../../images/exf-sm-logo.png', __FILE__);
$gts_iconurl = plugins_url('/../../images/gts-logo.png', __FILE__);
$class = '';
$onclick = '';
$href = "#";
$login_text = 'Login Requested';
$connect_to_etsy = 'Connected to Etsy';
$authorize_text = 'Authorized';
$shipping_text = 'Shipping';
$stage = get_option('etcpf_stage');
$url = get_option('etcpf_login_url');
$state = 'draft';
if (NULL == $this->api_key) {
    $this->get_credentials();
}
?>
<?php if (property_exists($this,'state') && $this->state == 'active') { ?>
    <div id="cost-information" class="updated settings-error">
        <p style="color:red">Etsy charges standard fees for active listings. <a
                href="https://www.etsy.com/legal/fees/" target="_blank">Learn more about charges</a> . You can
            go to configuration page and change state to Draft in order to prevent charges.</p>
    </div>
<?php } ?>
<?php if(isset($_REQUEST['tab']) || $_REQUEST['page'] == 'etsy-export-feed-configure'){ ?>
    <div class='cpf-header'>
        <h2>Etsy Shopping with ExportFeed</h2>
    </div>
<?php } ?>
<div class="exf-logo-header">
    <?php
    if(!defined('IMAGE_PATH') ) define('IMAGE_PATH',plugins_url( '/', __FILE__ ).'../../images/' );
    $reg = new ETCPF_EtsyValidation();
    if ($reg->valid)
        $lic = '
                <div class="postbox">
                <div class="logo-am" style="vertical-align: middle;">
                    <h4 class="icon-margin">Get standalone plugin for</h4>
                    <div class="upsell-icon-logo">
                        <div class="logoup amazon" style="display:inline-block;">
                            <div class="amazon">

                                <a value="" href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-on-amazon-seller-central/" target="_blank">

                                    <img src="'.IMAGE_PATH.'amazon.png">
                                </a>
                                <span class="plugin-link"><a href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-on-amazon-seller-central/" target="_blank">Get Amazon plugin</a></span>
                                <span class="plugin-desc">Manage bulk products + order & inventory sync</span>
                            </div>
                        </div>
                        <div class="logoup ebay" style="display:inline-block;">
                            <div class="ebay">
                                <a value="" href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/" target="_blank">

                                    <img src="'.IMAGE_PATH.'ebay.png">
                                </a>
                                <span class="plugin-link"><a href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/" target="_blank">Get eBay plugin</a></span>
                                <span class="plugin-desc">Bulk upload products and variations to eBay</span>
                            </div>
                        </div>

                    </div>

                    <div class="clear"></div>

                </div>
                </div>';
    else
        $lic = ETCPF_PLicenseKeyDialog::small_registration_dialog('');
    ?>
    <section>
        <nav>
            <ol class="cd-multi-steps text-bottom count">
                <?php if ($stage == 1) {
                    $class = 'current';
                    $onclick = "etcpf_fetch_login_url(this)";
                    $login_text = 'Request login URL';
                } elseif ($stage > 1) $class = 'visited';
                ?>
                <li class="<?= $class ?>"><a href="#"
                                             onclick="<?= $onclick ?>"><?= $login_text . etcpf_get_gif_loader('login_token_etsy', [
                            'position' => 'absolute',
                            'bottom' => '1px',
                            'left' => '10px'], true, true); ?></a>
                </li>
                <?php
                $class = '';
                if ($stage == 2) {
                    $class = 'current';
                    $connect_to_etsy = 'Connect to Etsy';
                    $href = $url;
                } elseif ($stage > 2) $class = 'visited';
                ?>
                <li class="<?= $class ?>"><a class="redirect_to_login" href="<?= $href ?>"><?= $connect_to_etsy ?></a>
                </li>
                <?php
                $class = '';
                if ($stage == 3) {
                    $class = 'current';
                    $authorize_text = 'Authorize';
                    $onclick = 'etcpf_authorize(this)';
                } elseif ($stage > 3) $class = 'visited';
                ?>
                <li class="<?= $class ?>"><a class="authorize_etsy_token" href="#"
                                             onclick="<?= $onclick ?>"><?= $authorize_text . etcpf_get_gif_loader('authorize_token_etsy', [
                            'position' => 'absolute',
                            'bottom' => '1px',
                            'right' => '-10px'], true, true); ?></a>
                </li>
                <?php $class = '';
                if ($stage == 4) {
                    $class = 'current';
                    $shipping_text = 'Shipping';
                    $href = '?page=etsy-export-feed-configure&tab=settings';
                } elseif ($stage > 4) $class = 'visited'; ?>
                <li class="<?= $class ?>"><a class="etcpf_shipping" href="<?= $href ?>"><?= $shipping_text ?></a></li>
                <?php
                $class = "";
                $text = 'Complete';
                if ($stage == 5) {
                    $class = 'current';
                    $text = "Ready to create Feed";
                    $href = "?page=etsy-export-feed-admin";
                }
                ?>
                <li class="<?= $class ?>"><em><a href="<?= $href ?>"><?= $text ?></em></a></li>
            </ol>
        </nav>
    </section>
    <?php if($_REQUEST['page'] == 'etsy-export-feed-configure' && !isset($_REQUEST['tab'])) echo $lic;?>
    <div class="gts-link">
        <a target="_blank" href="http://www.exportfeed.com"><img class="exf-logo-style" src='<?= $iconurl ?>'
                                                                 alt="shopping cart logo"></a><br>
        Version: <?php echo ETCPF_PLUGIN_VERSION . etcpf_version() ?> <br>
    </div>
</div>
<div style="clear:both"></div>
<?php if(!$reg->php_version) { ?>
    <div class="update-nag" style="color:red">
        <p>Warning: PHP version less than 3.0 detected. Please upgrade to higher version to use this plugin for better performance.</p>
    </div>
<?php } ?>

<?php if(!$reg->cureDetect) { ?>
<div class="update-nag" style="color:red">
    <p>Warning: PHP function curl not detected. You might face problems while creating and uploading feeds. Please upgrade your PHP to 5.3 or greater version.</p>
</div>
<?php } ?>
