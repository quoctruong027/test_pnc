<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_EtsyValidation
{

    public $debugmode = false;
    public $error_message = '';
    public $results = array();
    public $valid = false;
    public $cureDetect = false;
    public $php_version = false;

    protected $strErrorMsgNolicense = '  No license Key (limited to 100 items per feed). Get the pro version or a free trial key at: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
    protected $strErrorMsgMain = '  You are using the 7 day trial version. Get the pro version or a free trial key at: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
    protected $strErrorMsgInactive = 'Your License has been expired. Get the pro version or a free trial key at: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
    protected $strErrorMsgExpired = 'Your License has been expired. Get the pro version or a free trial key at: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';

    protected $strLicenseKey = 'etcpf_licensekey';
    protected $strLocalKey = 'etcpf_localkey';
    protected $strLicenseKeyOld = 'purplexml_licensekey';
    protected $strLocalKeyOld = 'purplexml_localkey';
    protected $strRapidcartToken = 'cp_rapidcarttoken';


    function __construct($debug = false)
    {

        global $etcore;
        $rapidToken = $etcore->settingGet($this->strRapidcartToken);
        //When loading license key, we must be careful to check for valid licenses from prior versions
        $licensekey = $etcore->settingGet($this->strLicenseKey);
        if (strlen($licensekey) == 0) {
            //Look for old version of key
            $licensekey = $etcore->settingGet($this->strLicenseKeyOld);
            if (strlen($licensekey) > 0)
                $etcore->settingSet($this->strLicenseKey, $licensekey);
        }

        $localkey = $etcore->settingGet($this->strLocalKey);
        if (strlen($localkey) == 0) {
            //Look for old version of key
            $localkey = $etcore->settingGet($this->strLocalKeyOld);
            if (strlen($localkey) > 0)
                $etcore->settingSet($this->strLocalKey, $localkey);
        }

        $this->debugmode = $debug;
        if ($this->debugmode) {
            echo "License Key: $licensekey \r\n";
            echo "Local Key: $localkey \r\n";
            echo "RapidCart Token: $rapidToken \r\n";
        }

        $this->results['status'] = 'Invalid';
        $this->error_message = '';

        //If there are keys set, remember this fact
        $hasKeys = false;
        if (strlen($localkey) > 0 || strlen($licensekey) > 0)
            $hasKeys = true;

        $this->checkLicense($licensekey, $localkey);
        $this->results['checkmd5x'] = new ETCPF_md5x($licensekey . $localkey, $this->results);
        if ($this->results['status'] == 'Active'){
            $this->valid = true;
        }
        elseif ($hasKeys)
            $this->error_message = 'License Key Invalid: ' . $this->error_message;

        $this->cureDetect = $this->checkCurl();
        $this->php_version = $this->checkphp_version();

        return true;
    }

    function checkphp_version()
    {
        if (PHP_MAJOR_VERSION < 3.2)
            return false;
        else
            return true;
    }

    function checkCurl()
    {
        if (function_exists('curl_exec'))
            return true;
        else
            return false;
    }

    function checkLicense($licensekey, $localkey = '')
    {
        global $wpdb;
        #echo '<pre>';print_r($_SERVER);die;
        //initial values
        //$whmcsurl = 'https://shop.shoppingcartproductfeed.com/'; Old urlor
        $protocol = is_ssl() ? 'https://' : 'http://';
        $whmcsurl = $protocol . 'shop.exportfeed.com/';
        $licensing_secret_key = '437682532'; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
        $check_token = time() . md5(mt_rand(1000000000, mt_getrandmax()) . $licensekey);
        $checkdate = date('Ymd'); # Current date
        if (!isset($_SERVER['SERVER_ADDR']) && !isset($_SERVER['LOCAL_ADDR'])) {
            $sql = "SELECT option_value FROM `" . $wpdb->options . "` where option_name like '%_transient_external_ip_address%'";
            $usersip = $wpdb->get_var($sql);
        } else
            $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];

        $localkeydays = 1; # How long the local key is valid for in between remote checks
        $allowcheckfaildays = 0; # How many days to allow after local key expiry before blocking access if connection cannot be made
        $localkeyvalid = false;
        $originalcheckdate = '';
        $localexpiry = 0;
        $status = '';

        $Results = $this->validateLocalKey($localkey, $localkeydays, $licensing_secret_key, $localexpiry, $usersip);
        if ($Results) {
            if ($this->debugmode) echo "Return After validation. \r\n";
            foreach ($Results as $k => $result)
                $this->results[$k] = $result;
            return;
        }

        $domain = get_option('siteurl'); //or home
        $domain = str_replace($protocol, '', $domain);
        $domain = str_replace('www', '', $domain); //add the . after the www if you don't want it
        $domain = strstr($domain, '/', true); //PHP5 only, this is in case WP is not root
        if (isset($_SERVER['SERVER_NAME'])) {
            $domain = $_SERVER['SERVER_NAME'];
        }
        $postfields['licensekey'] = $licensekey;
        // $postfields['domain'] = $_SERVER['SERVER_NAME'];
        $postfields['domain'] = $domain;
        $postfields['ip'] = $usersip;
        $postfields['dir'] = dirname(__FILE__);

        // if($postfields){
        //     if($serverValue = get_option('ETCPF_LICENING_SERVER_DETAILS')){
        //         $postfields = maybe_unserialize($serverValue);
        //         if($postfields['licensekey'] !== $licensekey){
        //             $postfields['licensekey'] = $licensekey;
        //             update_option('ETCPF_LICENING_SERVER_DETAILS',maybe_serialize($postfields));
        //         }
        //     }else{
        //         update_option('ETCPF_LICENING_SERVER_DETAILS',maybe_serialize($postfields));
        //     }
        // } TODO in near future

        if ($check_token)
            $postfields['check_token'] = $check_token;

        if (function_exists('curl_exec')) {
            if ($this->debugmode) echo "curl_init(). \r\n";
            $response = wp_remote_post($whmcsurl . 'modules/servers/licensing/verify.php',
                array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'headers' => array(
                        'Expect' => '',
                    ),
                    'body' => $postfields
                )
            );
            $code = wp_remote_retrieve_response_code($response);
            if ($code!==200){
                $this->error_message = 'Curl error: ' . $code . wp_remote_retrieve_response_message($response);
            }
            $data = wp_remote_retrieve_body($response);
            /*==========================================================================================================
                  $ch = curl_init();
                   curl_setopt($ch, CURLOPT_URL, $whmcsurl . 'modules/servers/licensing/verify.php');
                   curl_setopt($ch, CURLOPT_POST, 1);
                   curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                   curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                   //added for users who experience 'Remote Check Failed' license issue.
                   //it is not enough to disable CURLOPT_SSL_VERIFYPEER
                   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                   //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                   $data = curl_exec($ch);
                   //echo curl_error($ch); //debug
                   if (curl_errno($ch))
                       $this->error_message = 'Curl error: ' . curl_error($ch);
                   curl_close($ch);
            ===========================================================================================================*/
        } else {
            if ($this->debugmode) echo "fsockopen(). \r\n";
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);

            if ($fp) {
                $querystring = '';
                foreach ($postfields as $k => $v)
                    $querystring .= '$k=' . urlencode($v) . '&';

                $header = "POST " . $whmcsurl . "modules/servers/licensing/verify.php HTTP/1.0\r\n";
                $header .= "Host: " . $whmcsurl . "\r\n";
                $header .= "Content-type: application/x-www-form-urlencoded\r\n";
                $header .= "Content-length: " . @strlen($querystring) . "\r\n";
                $header .= "Connection: close\r\n\r\n";
                $header .= $querystring;

                $data = '';
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);

                while (!@feof($fp) && $status) {
                    $data .= @fgets($fp, 1024);
                    $status = @socket_get_status($fp);
                }

                @fclose($fp);
            }
        }

        if (!$data) {
            if ($this->debugmode) echo "Remote check failed. \r\n";
            $this->error_message = 'Remote Check Failed. Please enable cURl to activate your license key.For more details please contact your hosting service.<br/>Check our FAQ for technical requirements: <a target=\'_blank\' href = \'http://www.exportfeed.com/faq/technical-requirement-use-plugin-wordpress-site/\'> Click Here</a>';
            return;
        }

        preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
        $inputdata = array();
        foreach ($matches[1] as $k => $v) {
            $inputdata[$v] = $matches[2][$k];
            $this->results[$v] = $matches[2][$k];
        }

        if (isset($inputdata['md5hash']) && $inputdata['md5hash']) {
            if ($inputdata['md5hash'] != md5($licensing_secret_key . $check_token)) {
                $this->error_message = 'It seems that the licensekey you have entered does not exists in our records. Please contact our <a target="_blank" href="https://www.exportfeed.com/contact">Support</a> for further assistance.';
                $this->results['status'] = 'Inactive';
                if ($this->debugmode) echo "MD5 Checksum Verification Failed. \r\n";
                return;
            }
        }
        // $inputdata["status"]="Inactive";
        if ($inputdata["status"] == "Active") {
            if ($inputdata["productid"] == 88) {
                $this->error_message .= $this->strErrorMsgMain;
                if ($this->debugmode) echo "FreeLicenseKey. \r\n";
            }
            if ($this->debugmode) echo "Status Active. \r\n";
            $inputdata["checkdate"] = $checkdate;
            $data_encoded = serialize($inputdata);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $inputdata["localkey"] = $data_encoded;
            update_option('etcpf_localkey',$data_encoded);
        } elseif ($inputdata["status"] == "Expired") {
            $this->error_message .= $this->strErrorMsgExpired;
            if ($this->debugmode) echo "Expired. \r\n";
        } else {
            $this->error_message .= $this->strErrorMsgNolicense;
            if ($this->debugmode) echo "Inactive. \r\n";
        }

        $this->inputdata["remotecheck"] = true;
        unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
    }

    function setLicenseKey($licenseKey, $localKey)
    {
        global $etcore;
        $etcore->settingSet($this->strLicenseKey, $licenseKey);
        $etcore->settingSet($this->strLocalKey, $localKey);
    }

    function unregister()
    {
        global $etcore;
        //This will remove the license key (which is likely an undesirable course of action)
        $etcore->settingSet($this->strLicenseKey, '');
        $etcore->settingSet($this->strLocalKey, '');
    }

    function unregisterAll()
    {
        global $etcore;
        //Remove all stored license keys for all known products
        $etcore->settingDelete('etcpf_licensekey');
        $etcore->settingDelete('etcpf_localkey');
        $etcore->settingDelete('cp_rapidcarttoken');
        $etcore->settingDelete('purplexml_licensekey');
        $etcore->settingDelete('purplexml_localkey');
        $etcore->settingDelete('gts_licensekey');
        $etcore->settingDelete('gts_localkey');
        $etcore->settingDelete('fv_licensekey');
        $etcore->settingDelete('fv_localkey');
    }

    function validateLocalKey($localkey, $localkeydays, $licensing_secret_key, $localexpiry, $usersip)
    {
        if (!$localkey)
            return false;

        $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
        $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
        $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash

        if ($md5hash != md5($localdata . $licensing_secret_key))
            return false;

        $localdata = strrev($localdata); # Reverse the string
        $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
        $localdata = substr($localdata, 32); # Extract License Data
        $localdata = base64_decode($localdata);
        $localkeyresults = unserialize($localdata);
        $originalcheckdate = $localkeyresults['checkdate'];

        if ($md5hash != md5($originalcheckdate . $licensing_secret_key))
            return false;

        $locheck_licensecalexpiry = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - $localkeydays, date('Y')));

        if ($originalcheckdate < $locheck_licensecalexpiry)
            return false;

        $validdomains = explode(',', $localkeyresults['validdomain']);
        if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
            $this->error_message = 'Valid domain incorrect. ';
            return false;
        }

        $validips = explode(',', $localkeyresults['validip']);
        if (!in_array($usersip, $validips)) {
            $this->error_message = 'IP incorrect. ';
            return false;
        }

        if ($localkeyresults['validdirectory'] != dirname(__FILE__)) {
            $this->error_message = 'Valid directory mismatch. ';
            return false;
        }
        return $localkeyresults;
    }

    function getMessage()
    {
        return $this->strErrorMsgMain;
    }
}

class ETCPF_PLicenseGTS extends ETCPF_EtsyValidation
{

    function __construct()
    {
        $this->strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
        $this->strLicenseKey = 'gts_licensekey';
        $this->strLocalKey = 'gts_localkey';
        parent::__construct();
    }
}

class ETCPF_PLicenseFV extends ETCPF_EtsyValidation
{

    function __construct()
    {
        $this->strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
        $this->strLicenseKey = 'fv_licensekey';
        $this->strLocalKey = 'fv_localkey';
        $this->strLocalKeyOld = '';
        $this->strLocalKeyOld = '';
        parent::__construct();
    }
}