<?php

if (!defined('ABSPATH')) die("Piece of shit, not allowed");
include_once ETCPF_PATH . '/EB_Controller.php';

Class ETCPF_settings extends EB_Controller
{

    public $db;

    private $table;

    function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'etcpf_settings';
    }

    public function index()
    {
        $this->data['etsy_configuration'] = $this->settingValues();
        $this->data['exception_setting'] = $this->exceptionSettings();
        $this->data['cron_settings'] = $this->cronSettings();
        $this->view('settings/settings', $this->data);
    }

    public function settingValues()
    {
        $values = new stdClass();

        $values->shop_language = $this->shopDetails();

        $values->variation_seperation = array(
            'pipe' => '|',
            'hypon' => '-',
            'colon' => ':',
            'comma' => ',',
        );

        $values->who_made_it = array(
            'I did' => 'i_did',
            'Collectively' => 'collective',
            'Someone else made it' => 'someone_else'
        );

        $values->is_supply = array(
            'It is supply' => '1',
            'Its is Finished product' => '0'
        );

        $values->when_made = array(
            'Made to order' => 'made_to_order',
            'Between 2010 to 2019' => '2010_2019',
            'Between 2000 to 2009' => '2000_2009',
            'Before 2000' => 'before_2000',
            "90s Product" => '1990s',
            '80s Product' => '1980s',
            '70s Product' => '1970s',
            '60s Product' => '1960s',
            '50s product' => '1950s',
            '40s Product' => '1940s',
            '30s Product' => '1930s',
            '20s Product' => '1920s',
            '1910s product' => '1910s',
            '1900s Product' => '1900s',
            '1800s Product' => '1800s',
            '1700s Product' => '1700s',
            'Before 1700' => 'before_1700'
        );

        $values->state = array(
            'Draft' => 'draft',
            'Active' => 'active',
            'Inactive' => 'inactive'
        );

        $values->recipient = array();

        $values->occassion = array();

        $values->etsy_api_limit = array(
            '5000' => '5000',
            '10000' => '10000',
            '15000' => '15000',
            '50000' => '50000',
            '75000' => '75000',
            '100000' => '100000',
            '200000' => '200000',
            '500000' => '500000'
        );

        $values->order_fetching_time_interval = array(
            'Daily' => '1',
            'Two Days' => '2',
            'Three Days' => '3',
            'Five Days' => '5',
            'Ten Days' => '10',
            '15 Days' => '15',
            'Twenty Days' => '20',
            'Monthly' => '30',
            'Three Months' => '90',
            'Six Months' => '180'
        );

        return $values;
    }

    public function exceptionSettings()
    {
        return array(
            'title',
            'description',
            'tags',
            'images',
        );
    }

    public function cronSettings()
    {
        $values = new stdClass();
        $values->order_fetch_interval = $this->cronTime();
        $values->feed_submission_interval = $this->cronTime();
        $values->feed_update_interval = $this->cronTime();

        return $values;
    }

    public function cronTime()
    {
        return array(
            'every_minute' => 'Every Minute',
            'five_min' => 'Five Minute',
            'ten_min' => 'Ten Minutes',
            'fifteen_min' => 'Fifteen Minutes',
            'thirty_min' => 'Thirty Minutes',
            'three_hours' => 'Three Hours',
            'six_hours' => 'Six Hours',
            'twelve_hours' => 'Twelve Hours',
            'daily' => 'Daily',
            //'weekly' => 'Weekly', /doesn't exists in wp-cron
            'monthly' => 'Monthly'
        );
    }

    public function shopDetails()
    {
        $languages = array();
        $data = get_option('etcpf_etsy_shops');
        if ($data) {
            foreach ($data as $key => $value) {
                foreach ($value->languages as $k => $val) {
                    $languages[$this->getfullLang($val)] = $val;
                }
            }
        }
        return $languages;
    }

    public function getfullLang($code)
    {
        switch ($code) {
            case 'en-US':
                $country = 'English(United states)';
                return $country;
            case 'nl':
                $country = 'Nederlands';
                return $country;
            case 'fr':
                $country = 'Français';
                return $country;
            case 'de':
                $country = 'Deutsch';
                return $country;
            case 'it':
                $country = 'Italy';
                return $country;
            case 'ja':
                $country = 'Japanese';
                return $country;
            case 'pl':
                $country = 'Polski';
                return $country;
            case 'pt':
                $country = 'Portuguese';
                return $country;
            case 'ru':
                $country = 'Russian';
                return $country;
            case 'es':
                $country = 'Spanish';
                return $country;
            default:
                $country = 'English(United states)';
                return $country;
        }
    }


}
