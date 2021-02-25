<?php
class ETCPF_EtsyDialog extends ETCPF_PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Etsy';
        $this->service_name_long = 'Etsy Shop';
    }

    function categoryList($initial_remote_category)
    {
        $etsy = new ETCPF_Etsy();


        return '<input type="text" name="etsy_category_display" class="text_big" id="etsy_category_display"  onclick="showEtsyCategories(\'' . $this->service_name . '\',\'default\')" value="' . $initial_remote_category . '" autocomplete="off" readonly="true" placeholder="Click here to select your categories"/>
			<input type="hidden" name="remote_category" id="remote_category" value="' . $initial_remote_category . '" />';
        /*		return $etsy->fetch_category($initial_remote_category).'<input type="hidden" id="service_status" value="'.get_current_user_id().'"/><div id="categoryList" class="categoryList"></div><input type="hidden" id="remote_category" name="remote_category" value="'.$initial_remote_category.'" />';*/
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }
}
