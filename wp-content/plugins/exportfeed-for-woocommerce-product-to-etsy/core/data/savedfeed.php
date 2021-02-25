<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_SavedFeed
{

    function __construct($id)
    {
        global $etcore;
        $feedLoader = 'feedLoader' . $etcore->callSuffix;
//		$feedLoader = 'feedLoaderW';
        $this->$feedLoader($id);
    }

    private function feedLoaderW($id)
    {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'etcpf_feeds';

        //Go load the feed in question
        $sql = "SELECT f.*,description as local_category,category_path,texonomy_path FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy t on ( f.category=term_id and t.taxonomy='product_cat'  ) WHERE f.id=$id";
        $feed = $wpdb->get_results($sql);
        if(empty($feed)){
            return null;
        }
        $feed_details = $feed[0];
        $this->id = $id;
        $this->provider = $feed_details->type;
        //$this->local_category = $feed_details->local_category;
        $this->category_id = $feed_details->category;
        $this->remote_category = $feed_details->remote_category;
        $this->filename = $feed_details->filename;
        $this->url = $feed_details->url;
        $this->own_overrides = $feed_details->own_overrides;
        $this->feed_overrides = $feed_details->feed_overrides;
        $this->texonomy_path = $feed_details->texonomy_path;
        $this->category_path = $feed_details->category_path;
        $this->feed_type = $feed_details->feed_type;

        //Load the categories
        $this->local_category = '';
        $my_categories = explode(",", $this->category_id);
        $sql = "
			SELECT tdesc.term_id, description, tname.name
			FROM $wpdb->term_taxonomy tdesc
			LEFT JOIN $wpdb->terms tname ON (tdesc.term_id = tname.term_id)
			WHERE tdesc.taxonomy='product_cat'";
        $wp_categories = $wpdb->get_results($sql);
        foreach ($wp_categories as $this_category)
            if (in_array($this_category->term_id, $my_categories))
                $this->local_category .= $this_category->name . ', ';

        //Strip trailing comma
        $this->local_category = substr($this->local_category, 0, -2);

    }

    private function feedLoaderWe($id)
    {
        $this->feedLoaderW($id);
    }

    public function fullFilename()
    {
        //return "file.ext" since that takes a bit of computation
        $ext = '.xml';
        if (strpos(strtolower($this->url), '.csv') > 0)
            $ext = '.csv';
        return $this->filename . $ext;
    }

    public function save_ownoverrides($value)
    {
        global $etcore;
        $feedOverrideSaver = 'feedOverrideSaver' . $etcore->callSuffix;
        $this->$feedOverrideSaver($value);
    }

    private function feedOverrideSaverW($value)
    {
        //WordPress doesn't need due to AJAX differences
    }

    private function feedOverrideSaverWe($value)
    {
        //WordPress doesn't need due to AJAX differences
    }

}
