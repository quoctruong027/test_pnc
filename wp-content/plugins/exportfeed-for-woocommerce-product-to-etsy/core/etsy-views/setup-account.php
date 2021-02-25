<div id="wrap_account_details">

    <p style="padding-left:0.2em;">
        In order to add a new Etsy account, you need to:
    </p>
    <ol>
        <li>
            <?php
            if (isset($this_feed))
                $id = $this_feed['id'];
            else
                $id = '';
            $url = wp_nonce_url(get_admin_url() . 'admin.php?page=etsy-export-feed-configure&id=' . $id, 'setup_etsy_account', 'ETCPF_security');
            // $url = ETCPF_URL.'core/ajax/wp/connect-to-etsy.php';
            ?>
            <a href="<?= $url ?>" style="float: right" target="_blank" class="button button-primary button-hero"
               type="button" id="etsy_connect" name="connect" value="Connect To Etsy"/>Connect To Etsy</a><BR>
            Click "Connect To Etsy" to sign in to eBay and grant access for Etsy Feed. <br>
            <small>This will open the Etsy Sign In page in a new window.</small>
            <br>
            <small>Please sign in, grant access for Etsy Feed Plugin and close the new window to come back here.</small>
        </li>
        <li>
            <h3>After linking Etsy Feed with your Etsy account, Your Shop will be listed here. If not, please Reload it
                once.</h3>
        </li>
    </ol>


    <p></p>
</div>