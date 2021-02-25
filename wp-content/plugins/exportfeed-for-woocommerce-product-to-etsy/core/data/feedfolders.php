<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_FeedFolder
{

    /********************************************************************
     * feedURL is where the client should be sent to generate the new feed
     * It's unclear if it's still used
     ********************************************************************/

    public static function feedURL()
    {
        global $etcore;
        $feedURL = 'feedURL' . $etcore->callSuffix;
        return ETCPF_FeedFolder::$feedURL();
    }

    private static function feedURLW()
    {
        global $etcore;
        return $etcore->siteHost;
    }

    private static function feedURLWe()
    {
        global $etcore;
        return $etcore->siteHost;
    }

    /********************************************************************
     * uploadFolder is where the plugin should make the file
     ********************************************************************/
    public static function uploadFolder()
    {
        global $etcore;
        $uploadFolder = 'uploadFolder' . $etcore->callSuffix;
        return ETCPF_FeedFolder::$uploadFolder();
    }

    private static function uploadFolderW()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/etsy_merchant_feeds/';
    }

    private static function uploadFolderWe()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/etsy_merchant_feeds/';
    }

    /********************************************************************
     * uploadRoot is where the plugin should make the file (same as uploadFolder)
     * but no "google_merchant". Useful for ensuring folder exists
     ********************************************************************/

    public static function uploadRoot()
    {
        global $etcore;
        $uploadRoot = 'uploadRoot' . $etcore->callSuffix;
        return ETCPF_FeedFolder::$uploadRoot();
    }

    private static function uploadRootW()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'];
    }

    private static function uploadRootWe()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'];
    }

    /********************************************************************
     * URL we redirect the client to in order for the user to see the feed
     ********************************************************************/

    public static function uploadURL()
    {
        global $etcore;
        $uploadURL = 'uploadURL' . $etcore->callSuffix;
        return ETCPF_FeedFolder::$uploadURL();
    }

    private static function uploadURLW()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/etsy_merchant_feeds/';
    }

    private static function uploadURLWe()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/etsy_merchant_feeds/';
    }

}