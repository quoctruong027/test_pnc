<?php

namespace ElementorPro\Modules\ThemeBuilder\Conditions;

use ElementorPro\Modules\QueryControl\Module as QueryModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WooFunnels_Offers extends Post {

	public function get_label() {
		return 'Upstroke Offers';
	}


	public function register_sub_conditions() {
	}


}
