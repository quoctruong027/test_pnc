<?php
if (!defined('ABSPATH')) {
	exit;
}
// Exit if accessed directly
class ETCPF_PageDialogs {

	/**
	 * @return string
	 */
	public static function pageHeader() {

		global $etcore;
		$gap = '
			<div style="float:left; width: 50px;">
			</div>';

		if ($etcore->cmsName == 'WordPress') {

		} else {
			$lic = '';
		}

		$providers = new ETCPF_ProviderList();
		$style = 'display : block';
		$output = '<div class="clear"></div>';
		return $output;
	}

	public static function pageBody() {
		$output = '

	  <div id="feedPageBody" class="wrap">
	    <div class="inside export-target">
	      <h4>Selecting Feed Type, please wait...</h4>
		  <hr />
		</div>
	  </div>
	  ';
		return $output;
	}
}
