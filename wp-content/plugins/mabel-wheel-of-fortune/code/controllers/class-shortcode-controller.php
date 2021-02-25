<?php

namespace MABEL_WOF\Code\Controllers
{

	use MABEL_WOF\Code\Models\Wheel_Shortcode_VM;
	use MABEL_WOF\Code\Services\Wheel_service;
	use MABEL_WOF\Core\Common\Managers\Config_Manager;
	use MABEL_WOF\Core\Common\Managers\Script_Style_Manager;
	use MABEL_WOF\Core\Common\Shortcode;

	if(!defined('ABSPATH')){die;}

	class Shortcode_Controller
	{
		private $slug;

		public function __construct()
		{
			$this->slug = Config_Manager::$slug;
			$this->init_shortcode();
		}

		private function init_shortcode()
		{
			new Shortcode(
				'wof_wheel',
				'wheel-shortcode',
				array($this,'create_wheel_shortcode')
			);
		}

		public function create_wheel_shortcode($attributes) {

			$model = new Wheel_Shortcode_VM();

			if (!isset($attributes['id']))
				return $model;

			$model->wheel = Wheel_service::get_wheel($attributes['id']);

			Script_Style_Manager::publish_script(Config_Manager::$slug);
			Script_Style_Manager::add_script_vars();

			$model->wheel->standalone = true;

			return $model;
		}

	}
}