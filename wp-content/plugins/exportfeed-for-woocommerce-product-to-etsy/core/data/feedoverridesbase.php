<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class ETCPF_BaseFeedOverride
{

    protected $advancedCommands = array();
    protected $advancedCustomCommands = array();
    public $overrides = array();
    public $owner;

    function __construct($providerName, $parent, $saved_feed)
    {
        $this->owner = $parent;
    }

    function __destruct()
    {
        unset($this->owner);
    }

    /********************************************************************
     * loadDropDownMappings()
     ********************************************************************/

    protected function loadDropDownMappings($providerName)
    {
        global $etcore;
        $loadDropDownMappings = 'loadDropDownMappings' . $etcore->callSuffix;
        $this->$loadDropDownMappings($providerName);
    }

    private function loadDropDownMappingsW($providerName)
    {

        global $wpdb;
        $sql = "
			SELECT * FROM $wpdb->options
			WHERE $wpdb->options.option_name LIKE 'ETCPF_" . $providerName . "_cp_%'";
        $overrides_from_options = $wpdb->get_results($sql);

        //Attribute Mappings v2.0
        foreach ($overrides_from_options as $this_option) {
            $key = substr($this_option->option_name, strlen('ETCPF_' . $providerName . '_cp_'));
            $this->overrides[$key] = $this_option->option_value;
        }

        //Attribute Mappings v3.0
        foreach ($overrides_from_options as $this_option) {
            $key = substr($this_option->option_name, strlen('ETCPF_' . $providerName . '_cp_'));
            $this->owner->addAttributeMapping($key, $this_option->option_value);
        }

    }

    private function loadDropDownMappingsWe($providerName)
    {
        $this->loadDropDownMappingsW($providerName);
    }

    /********************************************************************
     * Advanced Commands loaded separately from dropdowns
     * since they may be unique (and thus, not loaded)
     ********************************************************************/

    protected function loadAdvancedCommands($providerName)
    {
        global $etcore;
        $providerName = 'Etsy';
        $loadAdvancedCommands = 'loadAdvancedCommands' . $etcore->callSuffix;
        $this->$loadAdvancedCommands($providerName);
    }

    protected function loadCustomAdvancedCommands($providerName)
    {
        global $etcore;
        $providerName = 'Etsy';
        $loadAdvancedCommands = 'loadCustomAdvancedCommands' . $etcore->callSuffix;
        $this->$loadAdvancedCommands($providerName);
    }

    private function loadAdvancedCommandsW($providerName)
    {
        //Advanced options
        $opt = get_option('Etsy-etsy-merchant-settings');
        if (strlen($opt) > 0)
            $this->advancedCommands = explode("\n", $opt);
    }

    private function loadCustomAdvancedCommandsW($providerName)
    {
        $opt = get_option('Etsy_custom-etsy-merchant-settings');
        if (strlen($opt) > 0)
            $this->advancedCommands = explode("\n", $opt);
    }

    private function loadAdvancedCommandsWe($providerName)
    {
        //Advanced options
        $this->advancedCommands = explode("\n", get_option($providerName . '-etsy-merchant-settings'));
    }

    /********************************************************************
     * Split an AdvancedCommand
     * //Old: $items = explode(' ' , $source); (Couldn't account for quotes)
     ********************************************************************/

    public function ptokens($source)
    {
        $items = array();
        $index = 0;
        $used_so_far = 0;
        $this_token = '';
        while ($used_so_far < strlen($source)) {
            switch ($source[$used_so_far]) {
                case ' ':
                case ',':
                    if (strlen($this_token) > 0) {
                        $items[$index] = $this_token;
                        $this_token = '';
                        $index++;
                    }
                    break;
                case '"':
                    $used_so_far++;
                    while (($used_so_far < strlen($source)) && ($source[$used_so_far] != '"')) {
                        $this_token .= $source[$used_so_far];
                        $used_so_far++;
                    }
                    break;
                case '(':
                case ')':
                    if (strlen($this_token) > 0) {
                        $items[$index] = $this_token;
                        $this_token = '';
                        $index++;
                    }
                    $items[$index] = $source[$used_so_far];
                    $this_token = '';
                    $index++;
                    break;
                default:
                    $this_token .= $source[$used_so_far];
            }
            $used_so_far++;
        }
        $items[$index] = $this_token;

        return $items;
    }

    /********************************************************************
     * A validIdentifier is of the form A..Z, a..z, 0..9, _
     ********************************************************************/

    public function validIdentifier($text)
    {
        for ($i = 0; $i < strlen($text); $i++)
            if (($text[$i] < "\x20") || ($text[$i] > "\x7E"))
                return false;
        return true;
    }

}
