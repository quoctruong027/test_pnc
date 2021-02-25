<?php

namespace ElementorPro\Modules\ThemeBuilder\Conditions;

use ElementorPro\Modules\QueryControl\Module as QueryModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WFACP_Elementor_exit_intent extends Post {

	public function get_label() {
		return 'AeroCheckout';
	}

	public function register_sub_conditions() {
	}
}

