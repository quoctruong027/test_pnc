<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_PEditFeedDialog
{

    public static function pageBody($feed_id, $feed_type)
    {

        require_once dirname(__FILE__) . '/../data/savedfeed.php';
        require_once 'dialogbasefeed.php';

        if ($feed_id == 0) {
            return;
        }

        $feed = new ETCPF_SavedFeed($feed_id);

        //Figure out the dialog for the provider
        $dialog_file = dirname(__FILE__) . '/../feeds/etsy/dialognew.php';
        if (file_exists($dialog_file)) {
            require_once $dialog_file;
        }

        $provider_dialog = new ETCPF_PBaseFeedDialog();
        echo $provider_dialog->mainDialog($feed, $feed_type);
    }
}