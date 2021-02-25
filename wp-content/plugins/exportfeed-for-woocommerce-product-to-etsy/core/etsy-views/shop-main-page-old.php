<?php
$login_url = get_option('etcpf_login_url');
$token_secret = get_option('etcpf_oauth_token_secret');
$stage = get_option('etcpf_stage');
$url = '#';
$display = 'display:block';
$token = false;


if ($login_url) {
//    $display = 'display:block';
    $url = $login_url;
}

if (strlen($token_secret) > 0) {
    $token = true;
//    $display = 'display:block';
}

?>
<div class="wrap">
    <?php if (isset($cpf_shop['error'])): ?>
        <div id="etcpf_etsy_shop" class="updated settings-errors">
            <p><?= $cpf_shop['error']; ?></p>
        </div>
    <?php endif; ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-3" class="postbox-container">
                <div class="postbox">
                    <h3 class="hndle">Shop Details</h3>
                    <div class="inside">
                        <table>
                            <?php if (is_array($cpf_shop) && isset($cpf_shop[0])) { ?>
                                <?php $shop = $cpf_shop[0]; ?>
                                <table class="cp-list-table widefat fixed striped accounts">
                                    <tr>
                                        <th>Shop Name</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                    <tr>
                                        <td><?= $shop->shop_name ?></td>
                                        <td><?= $shop->name ?></td>
                                        <td><a href="#" onclick="deleteEtsyShop()">Remove</a></td>
                                    </tr>
                                </table>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>

            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox" id="etcpf">
                    <h3 class="hndle">Installing a Shop?</h3>
                    <div class="inside">
                        <?php #echo '<pre>';print_r($this);echo '</pre>';?>
                        <p>If you have already added your newly created Etsy App's KEYSTRNG AND SHARED SECRET, follow
                            these steps to install you shop.</p>
                        <ul>
                            <li id="fetch_login_token">
                                <span class="dashicons dashicons-arrow-right"></span>
                                <?php if ($stage < 2) { ?>
                                    <a href="#" class="button button-primary" onclick="etcpf_fetch_login_url(this)">Fetch
                                        Login URL for Etsy.</a>
                                    <?php etcpf_get_gif_loader('login_token'); ?>
                                <?php } else { ?>
                                    <span>Login URL fetched</span>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php } ?>
                            </li>

                            <?php
                            if ($stage < 2)
                                $display = 'display:none';
                            ?>
                            <li id="redirect_to_login" style="<?= $display ?>">
                                <span class="dashicons dashicons-arrow-right"></span>
                                <?php if ($stage > 2) { ?>
                                    <span>Login Authenticated</span>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php } else { ?>
                                    <a href="<?= $url ?>" class="button button-primary">Connect To Authenticate</a>
                                <?php } ?>
                            </li>

                            <?php
                            if ($stage < 3)
                                $display = 'display:none';
                            ?>
                            <li id="authorize_token" style="<?= $display ?>">
                                <span class="dashicons dashicons-arrow-right"></span>
                                <?php if ($stage > 3) { ?>
                                    <span>Token Authorized</span>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php } else { ?>
                                    <a href="#" class="button button-primary"
                                       onclick="etcpf_authorize(this)">Authorize</a>
                                    <?php etcpf_get_gif_loader('authorize_token'); ?>
                                <?php } ?>
                            </li>
                            <li id="show_myshop" style="display:none">
                                <span class="dashicons dashicons-arrow-right"></span>
                                <a href="?page=etsy-export-feed-configure" class="button button-primary">Show My
                                    Shop</a>
                            </li>
                            <?php
                            if ($stage < 4)
                                $display = 'display:none';
                            ?>
                            <li style="<?= $display ?>">
                                <span class="dashicons dashicons-arrow-right"></span>
                                <?php if ($stage > 4) { ?>
                                    <span>Shipping Address Added</span>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php } else { ?>
                                    <a class="button button-primary"
                                       href="?page=etsy-export-feed-configure&tab=settings">Add Shipping Details</a>
                                <?php } ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>