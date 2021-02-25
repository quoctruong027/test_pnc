<?php

namespace MABEL_WOF\Code\Controllers
{

	use MABEL_WOF\Code\Models\Wheel_Model;
	use MABEL_WOF\Code\Models\Wheel_Shortcode_VM;
	use MABEL_WOF\Code\Services\AC_Service;
	use MABEL_WOF\Code\Services\CK_Service;
	use MABEL_WOF\Code\Services\CM_service;
    use MABEL_WOF\Code\Services\Drip_Service;
    use MABEL_WOF\Code\Services\GR_Service;
	use MABEL_WOF\Code\Services\Helper_Service;
	use MABEL_WOF\Code\Services\Integrations_Service;
	use MABEL_WOF\Code\Services\KV_Service;
	use MABEL_WOF\Code\Services\Log_Service;
	use MABEL_WOF\Code\Services\MailChimp_Service;
	use MABEL_WOF\Code\Services\Mailster_Service;
	use MABEL_WOF\Code\Services\ML_Service;
	use MABEL_WOF\Code\Services\Nl2Go_Service;
	use MABEL_WOF\Code\Services\RM_Service;
	use MABEL_WOF\Code\Services\SIB_Service;
	use MABEL_WOF\Code\Services\Theming_Service;
	use MABEL_WOF\Code\Services\WC_Service;
	use MABEL_WOF\Code\Services\Wheel_service;
	use MABEL_WOF\Core\Common\Admin;
	use MABEL_WOF\Core\Common\Html;
	use MABEL_WOF\Core\Common\Linq\Enumerable;
	use MABEL_WOF\Core\Common\Managers\Config_Manager;
	use MABEL_WOF\Core\Common\Managers\License_Manager;
	use MABEL_WOF\Core\Common\Managers\Options_Manager;
	use MABEL_WOF\Core\Common\Managers\Script_Style_Manager;
	use MABEL_WOF\Core\Common\Managers\Settings_Manager;
	use MABEL_WOF\Core\Models\Autocomplete_Option;
	use MABEL_WOF\Core\Models\Checkbox_Option;
	use MABEL_WOF\Core\Models\Choicepicker_Option;
	use MABEL_WOF\Core\Models\ColorPicker_Option;
	use MABEL_WOF\Core\Models\Container_Option;
	use MABEL_WOF\Core\Models\Custom_Option;
	use MABEL_WOF\Core\Models\Dropdown_Option;
	use MABEL_WOF\Core\Models\Editor_Option;
	use MABEL_WOF\Core\Models\MediaSelector_Option;
	use MABEL_WOF\Core\Models\Number_And_Choice_option;
	use MABEL_WOF\Core\Models\Number_Option;
	use MABEL_WOF\Core\Models\Option;
	use MABEL_WOF\Core\Models\Option_Dependency;
	use MABEL_WOF\Core\Models\Text_Option;

	if(!defined('ABSPATH')){die;}

	class Admin_Controller extends Admin
	{
		private $slug;

		public function __construct() {

			parent::__construct(new Options_Manager());
			$this->slug = Config_Manager::$slug;

			$this->add_mediamanager_scripts = true;

			Script_Style_Manager::add_script(Config_Manager::$slug.'-frontend-js','public/js/public.min.js',array('jquery','wp-color-picker'));
			Script_Style_Manager::add_style('wp-color-picker',null);
			Script_Style_Manager::add_style(Config_Manager::$slug.'-frontend-css', 'public/css/public.min.css');

			$this->add_ajax_function('mb-wof-get-wheels', $this,'get_wheels',false,true);
			$this->add_ajax_function('mb-wof-get-wheel', $this,'get_wheel',false,true);
			$this->add_ajax_function('mb-wof-add-wheel', $this,'add_wheel',false,true);
			$this->add_ajax_function('mb-wof-update-wheel', $this,'update_wheel',false,true);
			$this->add_ajax_function('mb-wof-delete-wheel', $this, 'delete_wheel', false, true);
			$this->add_ajax_function('mb-wof-toggle-activation', $this,'toggle_wheel_activation',false,true);
			$this->add_ajax_function('mb-wof-get-statistics', $this, 'get_all_statistics', false, true);

			$this->add_ajax_function('mb-wof-get-provider-lists', $this, 'get_provider_lists', false, true);
			$this->add_ajax_function('mb-wof-get-active-provider-lists', $this, 'get_active_providers_lists', false, true);
			$this->add_ajax_function('mb-wof-get-list-fields', $this, 'get_list_fields', false, true);
			$this->add_ajax_function('mb-wof-get-wc-product', $this, 'get_woo_products_by_name',false, true);
			$this->add_ajax_function('mb-wof-get-wc-categories', $this, 'get_woo_categories_by_name',false,true);
			$this->add_ajax_function('mb-wof-get-product-names-by-ids', $this, 'get_product_names_by_ids',false,true);
			$this->add_ajax_function('mb-wof-get-product-categories-by-ids', $this, 'get_category_names_by_ids',false,true);
			$this->add_ajax_function('mb-wof-get-mailchimp-groups', $this, 'get_mailchimp_groups_of_list', false, true);
			$this->add_ajax_function('mb-wof-get-last-optins', $this, 'get_last_optins', false, true);
			$this->add_ajax_function('mb-wof-delete-logs', $this, 'delete_logs',false, true);

			add_action('wp_loaded',array($this,'parse_request'));

			add_action('admin_init',array($this,'add_suggested_privacy_content'),30);
			add_action('admin_init',array($this,'deactivate_free_version'));

		}

		public function deactivate_free_version(){
			if(function_exists('run_MABEL_WOF_LITE') && current_user_can('activate_plugins'))
				deactivate_plugins('wp-optin-wheel/wp-optin-wheel.php');
		}

		public function add_suggested_privacy_content() {
			if(function_exists('wp_add_privacy_policy_content')) {
				$content = Html::view( 'admin/views/privacy-policy-suggestions', null );
				wp_add_privacy_policy_content( Config_Manager::$name, $content );
			}
		}

		public function get_list_fields() {
			if(empty($_GET['provider']))
				wp_send_json(array());

			$fields = array();
			$list = empty($_GET['list'])? '' : $_GET['list'];

			switch($_GET['provider']) {
				case 'mailchimp': $fields = MailChimp_Service::get_fields_from_list($list); break;
				case 'ac': $fields = AC_Service::get_fields_from_list($list); break;
				case 'cm': $fields = CM_service::get_fields_from_list($list); break;
				case 'gr': $fields = GR_Service::get_fields_from_list(); break;
				case 'ml': $fields = ML_Service::get_fields_from_list(); break;
				case 'kv': $fields = KV_Service::get_fields_from_list(); break;
				case 'mailster': $fields = Mailster_Service::get_fields_from_list(); break;
                case 'rm': $fields = RM_Service::get_fields_from_list(); break;
				case 'ck': $fields = CK_Service::get_fields_from_list(); break;
				case 'newsletter2go': $fields = Nl2Go_Service::get_fields_from_list($list); break;
				case 'sib': $fields = SIB_Service::action('get fields'); break;
				case 'drip': $fields = Drip_Service::get_fields_from_list(); break;
			}

			$fields = apply_filters('wof-list-fields', $fields);

			wp_send_json($fields);
		}

		public function get_active_providers_lists() {

			$lists = array();

			if(Settings_Manager::has_setting('mailchimp_api'))
				array_push($lists,array(
					'id' => 'mailchimp',
					'lists' => $this->get_provider_lists('mailchimp')
				));
			if(Settings_Manager::has_setting('cm_api') && Settings_Manager::has_setting('cm_client'))
				array_push($lists,array(
					'id' => 'cm',
					'lists' => $this->get_provider_lists('cm')
				));
			if(Settings_Manager::has_setting('ac_api') && Settings_Manager::has_setting('ac_url'))
				array_push($lists,array(
					'id' => 'ac',
					'lists' => $this->get_provider_lists('ac')
				));
			if(Settings_Manager::has_setting('gr_api'))
				array_push($lists,array(
					'id' => 'gr',
					'lists' => $this->get_provider_lists('gr')
				));
			if(Settings_Manager::has_setting('ml_api'))
				array_push($lists,array(
					'id' => 'ml',
					'lists' => $this->get_provider_lists('ml')
				));
			if(Settings_Manager::has_setting('kv_api'))
				array_push($lists,array(
					'id' => 'kv',
					'lists' => $this->get_provider_lists('kv')
				));
			if(function_exists('mailster'))
				array_push($lists,array(
					'id' => 'mailster',
					'lists' => $this->get_provider_lists('mailster')
				));
			if(Settings_Manager::has_setting('rm_key') )
				array_push($lists,array(
					'id' => 'rm',
					'lists' => $this->get_provider_lists('rm')
				));

			if(Settings_Manager::has_setting('ck_key') && Settings_Manager::has_setting('ck_secret'))
				array_push($lists,array(
					'id' => 'ck',
					'lists' => $this->get_provider_lists('ck')
				));

			if(Settings_Manager::has_setting('nl2go_u') && Settings_Manager::has_setting('nl2go_pw') && Settings_Manager::has_setting('nl2go_authkey'))
				array_push($lists,array(
					'id' => 'newsletter2go',
					'lists' => $this->get_provider_lists('newsletter2go')
				));

			if(Settings_Manager::has_setting('sib_api') )
				array_push($lists,array(
					'id' => 'sib',
					'lists' => $this->get_provider_lists('sib')
				));

			if(Settings_Manager::has_setting('sib_apiv3') )
				array_push($lists,array(
					'id' => 'sib',
					'lists' => $this->get_provider_lists('sib')
				));

            if(Settings_Manager::has_setting('drip_api') && Settings_Manager::has_setting('drip_account') )
                array_push($lists,array(
                    'id' => 'drip',
                    'lists' => $this->get_provider_lists('drip')
                ));

			$lists = apply_filters('wof-get-provider-lists', $lists);

			wp_send_json($lists);
		}

		public function get_provider_lists($provider_id = null) {

			if(empty($provider_id) && empty($_GET['provider']))
				wp_send_json(array());

			$provider = empty($provider_id) ? $_GET['provider'] : $provider_id;

			$lists = array();

			switch($provider) {
				case 'mailchimp': $lists = MailChimp_Service::get_email_lists(); break;
				case 'ac': $lists = AC_Service::get_email_lists(); break;
				case 'cm': $lists = CM_service::get_email_lists(); break;
				case 'gr': $lists = GR_Service::get_email_lists(); break;
				case 'ml': $lists = ML_Service::get_email_lists(); break;
				case 'kv': $lists = KV_Service::get_email_lists(); break;
				case 'mailster': $lists = Mailster_Service::get_email_lists(); break;
				case 'rm': $lists = RM_Service::get_email_lists();break;
				case 'ck': $lists = CK_Service::get_email_lists(); break;
				case 'newsletter2go' : $lists = Nl2Go_Service::get_email_lists(); break;
				case 'sib' : $lists = SIB_Service::action('get lists'); break;
				case 'drip' : $lists = Drip_Service::get_email_lists(); break;
			}
			if(is_wp_error($lists))
				wp_send_json_error();

			$lists = apply_filters('wof-lists', $lists);

			if(empty($provider_id))
				wp_send_json($lists);
			else return $lists;

		}

		public function parse_request() {

			if(isset($_GET['wof-optins-csv']) || isset($_GET['wof-plays-csv'])) {
				if (strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-type: application-download");
					header("Content-Disposition: attachment; filename=\"wheel-".$_GET['wheel_id']."-log.csv\"");
					header("Content-Transfer-Encoding: binary");
				} else {
					header("Content-type: application-download");
					header("Content-Disposition: attachment; filename=\"wheel-".$_GET['wheel_id']."-log.csv\"");
				}

				$separator = ';';

				$wheel = Wheel_service::get_wheel($_GET['wheel_id']);

				if(isset($_GET['wof-optins-csv'])) {

					$headings = array();
					foreach($wheel->fields as $field) {
						if($field->id !== 'primary_email') {
							array_push($headings,(object)array('id' => $field->id, 'title' => Helper_Service::truncate_text($field->placeholder)));
						}
					}

					echo 'Opt-in date'.$separator.'E-mail'.$separator;
					foreach ($headings as $heading) {
						echo (empty($heading->title) ? $heading->id : $heading->title) . $separator;
					}
					echo PHP_EOL;

					$lines = Log_Service::get_all_optins($_GET['wheel_id']);
					foreach ($lines as $line) {
						$parsed = json_decode($line->fields);

						echo get_date_from_gmt($line->created_date).$separator.(empty($line->email)?'N/A' : $line->email).$separator;

						foreach($headings as $heading) {
							if($parsed === null) {
								echo $separator;
								continue;
							}

							$parsed_field = Enumerable::from($parsed)->firstOrDefault(function($x) use($heading) {
								return $heading->id === $x->id;
							});

							if($parsed_field === null){
								echo $separator;
								continue;
							}

							else {
								$value = $parsed_field->value;
								if ( strpos( $parsed_field->id, 'consent_checkbox' ) !== false ) {
									$value = $parsed_field->value === true ? 'true' : 'false';
								}
								echo $value . $separator;
							}
						}
						echo PHP_EOL;
					}
				} else{
					echo 'Date'.$separator.'E-mail'.$separator.'Winning'.$separator.'Segment'.$separator.'Segment text'.$separator.'Prize';
					echo PHP_EOL;
					$lines = Log_Service::get_all_plays($_GET['wheel_id']);
					foreach($lines as $line) {
						echo get_date_from_gmt($line->created_date).$separator.(empty($line->email) ?  'N/A' : $line->email).$separator.
						     ($line->winning == 1 ? 'true' : 'false').$separator.$line->segment.$separator.$line->segment_text.
					         $separator.$line->prize.PHP_EOL;
					}
				}

				die();
			}
		}

		public function get_last_optins() {
			$logs = Log_Service::get_last_logs($_GET['id']);

			wp_send_json(Enumerable::from($logs)->select(function($x){
				return array(
					'date' => get_date_from_gmt($x->created_date),
					'type' => $x->type_description,
					'log' => $this->log_message($x)
				);
			})->toArray());

		}

		private function log_message($log){
			if($log->type_description === 'play')
				return sprintf('%s landed on segment %d %s %s',
					empty($log->email) ? 'Someone' : $log->email,
					$log->segment,
					$log->winning ? 'and won' : 'and lost',
					$log->winning ? '"' . $log->segment_text . '", with value '.$log->prize : ''
				);
			else
				return sprintf('%s opted in to the list', empty($log->email) ? 'Someone' : $log->email);
		}

		public function get_mailchimp_groups_of_list() {
			wp_send_json(MailChimp_Service::get_list_groups($_GET['id']));
		}

		public function get_woo_categories_by_name(){
			wp_send_json(WC_Service::get_categories_by_name(sanitize_title_for_query($_GET['q'])));
		}

		public function get_category_names_by_ids(){
			wp_send_json(WC_Service::get_product_categories_by_ids(explode(',',$_GET['ids'])));
		}

		public function get_product_names_by_ids(){
			wp_send_json(WC_Service::get_product_names_by_ids(explode(',',$_GET['ids'])));
		}

		public function get_woo_products_by_name() {
			$q = urldecode($_GET['q']);

			$products = WC_Service::get_products_by_name(($q));
			wp_send_json($products);
		}

		public function get_all_statistics() {
			wp_send_json(Wheel_service::get_all_statistics());
		}

		public function toggle_wheel_activation()
		{
			Wheel_service::toggle_activation($_REQUEST['id'], $_REQUEST['toggle']);
			wp_die();
		}

		public function delete_wheel() {
			$id = $_REQUEST['id'];
			if(empty($id))
				wp_die();

			Wheel_service::delete_wheel($id);
			Log_Service::delete_all_logs_from_db($id);
			wp_die();
		}

		public function delete_logs() {
			$id = $_REQUEST['id'];
			if(empty($id))
				wp_die();

			Log_Service::delete_all_logs_from_db($id);
			wp_die();
		}

		public function get_wheel()
		{
			if(!isset($_GET['id'])) wp_die();
			$notification = Wheel_service::get_wheel($_GET['id']);
			wp_send_json($notification);
		}

		public function get_wheels()
		{
			$notifications = Wheel_service::get_all_wheels();
			wp_send_json($notifications);
		}

		public function update_wheel()
		{
			Wheel_service::edit_wheel($_POST['id'],$_POST['options']);
			wp_die();
		}

		public function add_wheel()
		{
			$id = Wheel_service::add_wheel($_POST['options']);
			wp_die($id);
		}

		public function init_admin_page() {

			$this->options_manager->add_section('settings', __('General settings','mabel-wheel-of-fortune'), 'admin-settings', true);
			$this->options_manager->add_section('apis', __('Integrations', 'mabel-wheel-of-fortune'), 'email-alt');
			$this->options_manager->add_section('addwheel', __('Add Wheel','mabel-wheel-of-fortune'), 'plus');
			$this->options_manager->add_section('wheels', __('Wheels','mabel-wheel-of-fortune'), 'dashboard');

			$this->add_integrations_to_options($this->options_manager);

			$this->options_manager->add_option('settings', new Checkbox_Option(
				'woo_coupons',
				__('Use WooCommerce coupons', 'mabel-wheel-of-fortune'),
				__('Generate unique coupons via Woocommerce', 'mabel-wheel-of-fortune'),
				Settings_Manager::get_setting('woo_coupons'),
				__("If the visitor lands on a winning coupon-slice, you can have WooCommerce auto generate a coupon code. Selecting this means each coupon code is unique to 1 user and can expire when you want it to. Changing this setting means you'll have to <b>update the slices on existing</b> wheels as well.", 'mabel-wheel-of-fortune')
			));

			$this->options_manager->add_option('settings', new Number_And_Choice_option(
				__('Coupon duration', 'mabel-wheel-of-fortune'),
				new Number_Option('woo_coupon_duration',null,Settings_Manager::get_setting('woo_coupon_duration')),
				new Dropdown_Option('woo_coupon_timeperiod',null,array(
					'minutes' => __('Minutes', 'mabel-wheel-of-fortune'),
					'hours' => __('Hours', 'mabel-wheel-of-fortune'),
					'days' => __('Days', 'mabel-wheel-of-fortune'),
				), Settings_Manager::get_setting('woo_coupon_timeperiod')),
				__('How long before the coupon expires? Tip: you can inform the user of this via the content settings of each wheel.', 'mabel-wheel-of-fortune'),
				array(new Option_Dependency('woo_coupons', 'true'))
			));

			$this->options_manager->add_option('addwheel',
				new Custom_Option(null,'add_wheel',$this->create_addwheel_model())
			);

			$this->options_manager->add_option('wheels',
				new Custom_Option(null,'all_wheels',array('base_url' => Config_Manager::$url))
			);

			do_action($this->slug.'-options',$this->options_manager);

		}

		private function add_integrations_to_options(Options_Manager $manager) {

			$manager->add_option('apis', new Custom_Option(null,'integrations',array(
				'integrations' => Integrations_Service::get_integrations(),
				'integrations_without_card' => Enumerable::from(Integrations_Service::get_integrations())->select(function($x){
					unset($x->card);
					return $x;
				})->toArray()
			)));
		}

		private function create_addwheel_model() {

			$themes_data = Theming_Service::get_themes();
			$themes_data = apply_filters('wof-add-themes-to-list', $themes_data);

			$bgs = Theming_Service::get_backgrounds();
			$bgs = apply_filters('wof-add-backgrounds-to-list',$bgs);

			$bgs_dropdown_data = array();

			foreach($bgs as $bg) {
				$bgs_dropdown_data[$bg['id']] = $bg['title'];
			}

			$theme_setting = new Container_Option(null,__('1. Pick a theme', 'mabel-wheel-of-fortune'));
			$theme_setting->options = array(
				new Custom_Option(
					null,
					'themes',
					array('themes' => $themes_data,'backgrounds' => $bgs))
			);

			$design_slices_setting = new Container_Option(null,__('2. Edit slices', 'mabel-wheel-of-fortune'));
			$design_slices_setting->options = array(
				new Custom_Option(
					null,
					'slices-design-settings',array(
						'options' => array($this->add_data_attribute_for_data_bind(new Dropdown_Option(
							'amount_of_slices',
							__('Number of slices', 'mabel-wheel-of-fortune'),
							array(4 => 4,6 => 6,8 => 8,10 => 10,12 => 12,14=>14,15=>15,16=>16,18=>18,20=>20,22=>22,24=>24),
							12
						)))
					)
				)
			);

			$slices = array(
				array(
					'label' => __('5% Discount', 'mabel-wheel-of-fortune'),
					'value' => '',
					'chance' => 30,
					'type' => 1
				),
				array(
					'label' => __('No prize', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('Next time', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('Almost!', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('10% Discount', 'mabel-wheel-of-fortune'),
					'value' => '',
					'chance' => 30,
					'type' => 1
				),
				array(
					'label' => __('Free Ebook', 'mabel-wheel-of-fortune'),
					'value' => 'https://google.com/',
					'chance' => 30,
					'type' => 2
				),
				array(
					'label' => __('No Prize', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('No luck today', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('Almost!', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('50% Discount', 'mabel-wheel-of-fortune'),
					'value' => '',
					'chance' => 10,
					'type' => 1
				),
				array(
						'label' => __('No prize', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
				array(
					'label' => __('Unlucky', 'mabel-wheel-of-fortune'),
					'type' => 0
				),
			);

			$content_settings = new Container_Option(null, __('Content settings','mabel-wheel-of-fortune'));

			$content_settings->options = array(
				$this->add_data_attribute_for_data_bind(
					new Text_Option(
						'title',
						__('Title', 'mabel-wheel-of-fortune'),
						null,
						__('Get your chance to <em>win a price</em>!', 'mabel-wheel-of-fortune'),
						__('Use &lt;em&gt;&lt;/em&gt; to emphasise text (it will have a different color).', 'mabel-wheel-of-fortune')
					)
				),
				$this->add_data_attribute_for_data_bind(
					new Editor_Option(
						'explainer',
						__('Explainer text', 'mabel-wheel-of-fortune'),
						null,
						array(
							'tinymce' => array(
								'toolbar1' => 'bold,italic,underline',
								'toolbar2' => false
							),
							'quicktags' => false
						),
						__('A short paragraph explaining how it works.', 'mabel-wheel-of-fortune')
					)
				),
				$this->add_data_attribute_for_data_bind(
					new Editor_Option(
						'disclaimer',
						__('Disclaimer text', 'mabel-wheel-of-fortune'),
						null,
						array(
							'tinymce' => array(
								'toolbar1' =>
								'bold,italic,underline,bullist,justifyleft,justifycenter' .
								',justifyright,link,unlink',
								'toolbar2' => false
							),
							'quicktags' => false
						),
						__('Add a short paragraph explaining the rules & regulations.', 'mabel-wheel-of-fortune')
					)
				),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'button_text',
					__('Spin-button text', 'mabel-wheel-of-fortune'),
					null,
					__('Try your luck', 'mabel-wheel-of-fortune'),
					__('This text will appear on the button the visitor has to click to spin the wheel.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'close_text',
					__('Close popup text', 'mabel-wheel-of-fortune'),
					null,
					__("I don't feel lucky", 'mabel-wheel-of-fortune'),
					__('Optional link in the lower right corner. The upper right corner already has an X-button by default.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'losing_title',
					__('Losing title', 'mabel-wheel-of-fortune'),
					null,
					__("Uh oh! Looks like you lost", 'mabel-wheel-of-fortune'),
					__('This title will appear after a player hits a losing segment.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'losing_text',
					__("Losing text", 'mabel-wheel-of-fortune'),
					null,
					__("We're sorry, the wheel of fortune has let you down. Better luck next time!", 'mabel-wheel-of-fortune'),
					__('This text will appear below the losing title after a player hits a losing segment.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'winning_title',
					__('Winning title', 'mabel-wheel-of-fortune'),
					null,
					__("Hurray! You've hit {x}. Lucky you!", 'mabel-wheel-of-fortune'),
					__("This title will appear after a player hits a winning segment. Use {x} to denote the segment's label.",'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'winning_text_coupon',
					__("Winning text for coupons", 'mabel-wheel-of-fortune'),
					null,
					__("Nicely done! You can use the coupon code below to claim your prize:", 'mabel-wheel-of-fortune'),
					__('This text will appear below the winning title after a player hits a winning coupon-segment.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'winning_text_link',
					__("Winning text for links/redirects", 'mabel-wheel-of-fortune'),
					null,
					__("Nicely done! here's the link to your free product:", 'mabel-wheel-of-fortune'),
					__('This text will appear below the winning title after a player hits a winning link- or redirect-segment.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'winning_text_texthtml',
					__("Winning text for text/html", 'mabel-wheel-of-fortune'),
					null,
					__("Nicely done! here are your instructions:", 'mabel-wheel-of-fortune'),
					__('This text will appear below the winning title after a player hits a winning text/html segment.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'button_done',
					__("'Done' button text", 'mabel-wheel-of-fortune'),
					null,
					__("I'm done playing", 'mabel-wheel-of-fortune'),
					__('When the player has done playing, this button will appear to allow to close the popup.', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'button_again',
					__("'Try again' button text", 'mabel-wheel-of-fortune'),
					null,
					__("Try again", 'mabel-wheel-of-fortune'),
					__('If the player lost, and you allow him to play again, this button will appear to start another game.', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'games_left_text',
					__("'Games left' text", 'mabel-wheel-of-fortune'),
					null,
					__("You have {x} spins left", 'mabel-wheel-of-fortune'),
					__('If the player can play again, this message indicates how many times they can try again. Use {x} to denote the number of tries.', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_already_used',
					__("'Email already used' error", 'mabel-wheel-of-fortune'),
					null,
					__("This email address is already used", 'mabel-wheel-of-fortune')
				)),
			);

			$design_settings = new Container_Option(null, __('3. Other design settings','mabel-wheel-of-fortune'));
			$design_settings->options = array(
				$this->add_data_attribute_for_data_bind(new Dropdown_Option(
					'bgpattern',__('Background pattern', 'mabel-wheel-of-fortune'),$bgs_dropdown_data)
				),
				$this->add_data_attribute_for_data_bind(new MediaSelector_Option(
					'custom_bg',
					Settings_Manager::get_setting('custom_bg'),
					__('Or upload your own','mabel-wheel-of-fortune'),
					__('Select background','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'wheel_color',
					null,
					__('Wheel color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'dots_color',
					null,
					__('Wheel dots color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'bgcolor',
					null,
					__('Background color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'fgcolor',
					null,
					__('Primary text color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'secondary_color',
					null,
					__('Secondary text color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'button_bgcolor',
					null,
					__('Button background', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'button_fgcolor',
					null,
					__('Button text color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'pointer_color',
					null,
					__('Pointer color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'error_color',
					null,
					__('Error text color', 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new MediaSelector_Option(
					'logo',
					Settings_Manager::get_setting('logo'),
					__('Center logo','mabel-wheel-of-fortune'),
					__('Select image','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(New Checkbox_Option(
					'shadows',
					__('Shadows','mabel-wheel-of-fortune'),
					__('Enable shadows on the wheel','mabel-wheel-of-fortune'),
					true
				)),
				$this->add_data_attribute_for_data_bind(New Checkbox_Option(
					'handles',
					__('Handles','mabel-wheel-of-fortune'),
					__('Show the handles on the wheel', 'mabel-wheel-of-fortune'),
					true
				))
			);

			$security_settings = new Container_Option(null, __('Security & logging', 'mabel-wheel-of-fortune'));
			$security_settings->options = array(
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'check_mail_domains',
					__('Anti-cheat', 'mabel-wheel-of-fortune'),
					__('Validate email domains', 'mabel-wheel-of-fortune'),
					false,
					__("This setting will use our external service to test emails for validity. We test against known fake domains and the server's MX record. Drawback: slows things down a little due to an external API call.<div style=\"padding:5px;margin-top:8px;color:#000;background:#ffeaea;\"><b>This service requires an active license key. When your license key becomes inactive, this service will no longer work.</b></div>", 'mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'invalid_mail_error',
					__('Invalid email error', 'mabel-wheel-of-fortune'),
					null,
					__('This email appears to be invalid.', 'mabel-wheel-of-fortune'),
					__('Which error should appear when the email address is invalid/fake?','mabel-wheel-of-fortune'),
					array(new Option_Dependency('check_mail_domains', 'true'))
				)),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'log_ips',
					__('Check IPs', 'mabel-wheel-of-fortune'),
					__('Check IP addresses', 'mabel-wheel-of-fortune'),
					false,
					__("If checked, each player's IP address will be checked at every play. This means users can play only once per device.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('retries','false'))
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'ip_used_error',
					__('IP already used error', 'mabel-wheel-of-fortune'),
					null,
					__("You've already played.", 'mabel-wheel-of-fortune'),
					__('Which error should appear when the IP address has already been used to play.','mabel-wheel-of-fortune'),
					array(new Option_Dependency('log_ips', 'true'))
				)),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'log',
					__('Log', 'mabel-wheel-of-fortune'),
					__('Log everything', 'mabel-wheel-of-fortune'),
					false,
					__('If enabled, every opt-in & play will be logged to your WP database. You can export the logs to CSV.', 'mabel-wheel-of-fortune')
				))
			);

			$behavior_settings = new Container_Option(null, __('Behavior setting', 'mabel-wheel-of-fortune'));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Choicepicker_Option(
				'appeartype',
				__('Show wheel','mabel-wheel-of-fortune'),
				array('exit'),
				array(
					'Pick from this list:' => array(
						'immediately'   => __('Immediately', 'mabel-wheel-of-fortune'),
						'delay'         => __('After a delay', 'mabel-wheel-of-fortune'),
						'scroll'        => __('Upon scrolling an amount', 'mabel-wheel-of-fortune'),
						'click'         => __('When clicking an element', 'mabel-wheel-of-fortune'),
						'exit'          => __('Exit-intent', 'mabel-wheel-of-fortune'),
						'none'          => __('With a widget', 'mabel-wheel-of-fortune'),
					),),
				__("Pick when the wheel should appear on screen. You can pick multiple but the wheel won't show again once 1 condition is satisfied.",'mabel-wheel-of-fortune'),
				array( new Option_Dependency('usage', array('popup')) )
			)));

			$behavior_settings->options[] =	$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'hide_mobile',
					__('Hide on mobile','mabel-wheel-of-fortune'),
					__('Hide the wheel on mobile devices','mabel-wheel-of-fortune'),
					true,null,
					array( new Option_Dependency('usage', array('popup')), )
			));
			$behavior_settings->options[] =	$this->add_data_attribute_for_data_bind(new Checkbox_Option(
                'hide_tablet',
                __('Hide on tablet','mabel-wheel-of-fortune'),
                __('Hide the wheel on tablet devices.','mabel-wheel-of-fortune'),
                false,null,
				array( new Option_Dependency('usage', array('popup')), )
			));
			$behavior_settings->options[] = $this->add_data_attribute_for_data_bind(new Checkbox_Option(
                'hide_desktop',
                __('Hide on desktop','mabel-wheel-of-fortune'),
                __('Hide the wheel on desktop devices.','mabel-wheel-of-fortune'),
                false,null,
				array( new Option_Dependency('usage', array('popup')), )
			));
			$behavior_settings->options[] =	$this->add_data_attribute_for_data_bind(new Dropdown_Option(
				'user_inclusion',
				__('Show to users','mabel-wheel-of-fortune'),
				array(
					'0' => __('Both logged in and logged out users','mabel-wheel-of-fortune'),
					'1' => __('Only logged in users','mabel-wheel-of-fortune'),
					'2' => __('Only logged out users','mabel-wheel-of-fortune')
				),null,null,
				array( new Option_Dependency('usage', array('popup')), )
			));
			$behavior_settings->options[] =	$this->add_data_attribute_for_data_bind(new Choicepicker_Option(
				'show_on_pages',
				__('Show on pages','mabel-wheel-of-fortune'),
				array(),
				$this->get_all_pages_as_options(),
				__("On which pages or page-types should the notification appear?",'mabel-wheel-of-fortune'),
				array( new Option_Dependency('usage', array('popup')), )
			));

			if ( function_exists('icl_object_id') ) {
				array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Choicepicker_Option(
					'wpml_options',
					__('Show for languages','mabel-wheel-of-fortune'),
					array('-1'),
					$this->get_all_languages_as_options(),
					__("Show only when the page is in these languages.",'mabel-wheel-of-fortune'),
					array(
						new Option_Dependency('usage', array('popup')),
						new Option_Dependency('appeartype', array('immediately','delay','scroll','exit','none','click')),
					)
				)));
			}

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Number_Option(
				'appeardelay',
				__('Appearance delay','mabel-wheel-of-fortune'),
				5,
				null,null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype','delay')
				),
				__('Show popup after', 'mabel-wheel-of-fortune'),
				__('seconds', 'mabel-wheel-of-fortune')
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Number_Option(
				'appearscroll',
				__('Appearance after scrolling','mabel-wheel-of-fortune'),
				60,
				null,null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype','scroll')
				),
				__('Show popup after user scrolls', 'mabel-wheel-of-fortune'),
				__('percent down the page', 'mabel-wheel-of-fortune')
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Text_Option(
				'appearclass',
				__('Element selector', 'mabel-wheel-of-fortune')
				,'.YourClassName',null,
				__('Enter the ID or class of the element that will receive the click like this: .className or #idName','mabel-wheel-of-fortune'),
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype', 'click')
				)
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Dropdown_Option(
				'occurance',
				__('Occurance','mabel-wheel-of-fortune'),array(
					'session' => __('Show again at the next visit (next session)','mabel-wheel-of-fortune'),
					'page' => __('Show on every page refresh.','mabel-wheel-of-fortune'),
					'storage' => __('Never show again','mabel-wheel-of-fortune'),
					'delay' => __('Show again after a delay','mabel-wheel-of-fortune')
				),
				null,
				__("When the user has seen the popup, <b>but hasn't played</b>, should it be displayed again?", 'mabel-wheel-of-fortune'),
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype', array('immediately','delay','scroll','exit','none')),
				)
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Number_Option(
				'occurancedelay',
				__('Occurance delay','mabel-wheel-of-fortune'),
				5,
				null,null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('occurance','delay'),
					new Option_Dependency('appeartype', array('immediately','delay','scroll','exit','none')),
				),
				__('Show popup again after', 'mabel-wheel-of-fortune'),
				__('days', 'mabel-wheel-of-fortune')
			)));

			array_push($behavior_settings->options, $this->add_data_attribute_for_data_bind(new Dropdown_Option(
				'widget',
				__('Choose a widget','mabel-wheel-of-fortune'),
				array(
					'pullout' => __('Pull out','mabel-wheel-of-fortune'),
					'bubble' => __('Bubble','mabel-wheel-of-fortune'),
					'wheel' => __('Tiny wheel','mabel-wheel-of-fortune'),
				),
				'pullout',
				__('When the visitor clicks the widget, the wheel will appear.','mabel-wheel-of-fortune'),
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype', array('none')),
				)
			)));

			array_push($behavior_settings->options, $this->add_data_attribute_for_data_bind(new Dropdown_Option(
				'widget_position',
				__('Widget position','mabel-wheel-of-fortune'),
				array(
					'left' => __('Left','mabel-wheel-of-fortune'),
					'right' => __('Right','mabel-wheel-of-fortune')
				),
				'left',
				null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype',array('none'))
				)
			)));

			array_push($behavior_settings->options, $this->add_data_attribute_for_data_bind(new ColorPicker_Option(
				'widget_bgcolor',
				null,
				__('Widget background color','mabel-wheel-of-fortune'),
				null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('appeartype',array('none')),
					new Option_Dependency('widget',array('pullout','bubble'))
				)
			)));

			array_push($behavior_settings->options, $this->add_data_attribute_for_data_bind(
				new Text_Option(
					'widget_text',
					__('Widget text','mabel-wheel-of-fortune'),
					null,
					null,
					__("Add some text in a bubble next to the widget. Leave blank if you don't want a bubble.", 'mabel-wheel-of-fortune'),
					array(
						new Option_Dependency('usage', array('popup')),
						new Option_Dependency('appeartype',array('none'))
					)
				)
			));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Number_Option(
				'plays',
				__('Retries in the same game','mabel-wheel-of-fortune'),
				0,null,
				__("Immediately give users another chance (without opting-in again) <b>if they lost</b>.", 'mabel-wheel-of-fortune'),
				null,
				__("Allow to try again up to ",'mabel-wheel-of-fortune'),
				__(" times in the same game.",'mabel-wheel-of-fortune')
			)));
			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Checkbox_Option(
				'retries',
				__('Play again','mabel-wheel-of-fortune'),
				__("Users can play again, even after winning their first game.", 'mabel-wheel-of-fortune'),
				false,
				__('In this case, users can play multiple (unlimited) times.','mabel-wheel-of-fortune')
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Dropdown_Option(
					'occurance_after',
					__('Occurance after already played','mabel-wheel-of-fortune'),array(
					'session' => __('Show again at the next visit (next session)','mabel-wheel-of-fortune'),
					'page' => __('Show on every page refresh.','mabel-wheel-of-fortune'),
					'delay' => __('Show again after a delay','mabel-wheel-of-fortune')
				),
				null,
				__("When the user played <b>their previous game</b> (won or lost), when to display the wheel again?",'mabel-wheel-of-fortune'),
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('retries','true'),
					new Option_Dependency('appeartype', array('exit','scroll','immediately','delay','none'))
				)
			)));

			array_push($behavior_settings->options,$this->add_data_attribute_for_data_bind(new Number_Option(
				'occurance_after_delay',
				__('Occurance delay','mabel-wheel-of-fortune'),
				5,
				null,null,
				array(
					new Option_Dependency('usage', array('popup')),
					new Option_Dependency('occurance_after','delay',false),
					new Option_Dependency('retries','true',false),
					new Option_Dependency('appeartype', array('exit','scroll','immediately','delay','none'))
				),
				__('Show popup again after', 'mabel-wheel-of-fortune'),
				__('days', 'mabel-wheel-of-fortune')
			)));

			$animation_sound_settings = new Container_Option(null,__('Animation & sound','mabel-wheel-of-fortune'));
			$animation_sound_settings->options = array(
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'sound',
					__('Play sound','mabel-wheel-of-fortune'),
					__("Play a 'tick' sound when the wheel turns.",'mabel-wheel-of-fortune'),
					false
				)),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'confetti',
					__('Confetti','mabel-wheel-of-fortune'),
					__("Pop confetti when a player won.",'mabel-wheel-of-fortune'),
					false
				)),
			);

			$mailchimp_list_settings = new Container_Option(null, __('Mailchimp settings','mabel-wheel-of-fortune'));
			$mailchimp_list_settings->id = 'mailchimp_list_settings';
			$mailchimp_list_settings->options = array(
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'use_mailchimp_group',
					__('Mailchimp group', 'mabel-wheel-of-fortune'),
					__('Use a Mailchimp group', 'mabel-wheel-of-fortune'),
					false,
					null,
					array(new Option_Dependency('list_provider','mailchimp'))
				)),
				$this->add_data_attribute_for_data_bind(new Dropdown_Option(
					'mailchimp_group',
					__('Mailchimp group', 'mabel-wheel-of-fortune'),
					array('0' => __('Loading groups...','mabel-wheel-of-fortune')),
					null,
					__('Automatically add optins to this Mailchimp group', 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('use_mailchimp_group', 'true'))
				))
			);
			$form_builder_for_lists_settings = 	new Custom_Option(null,'form-builder-lists');
			$form_builder_for_others_settings = new Custom_Option(null,'form-builder-other');

			$global_win_chance = new Number_Option(
                'winning_chance',
                __('Global winning chance','mabel-wheel-of-fortune'),
                75,null,
                __("What's the chance your visitor will win something? If you want your visitor to always win, set this to 100%.", 'mabel-wheel-of-fortune'),
                null,' ',
                ' % '
            );
			$global_win_chance->min = 0;
			$global_win_chance->max = 100;

			$chance_settings = array(
				$this->add_data_attribute_for_data_bind($global_win_chance),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'limit_prizes',
					__('Limit prizes','mabel-wheel-of-fortune'),
					__('Limit the prize quantity.', 'mabel-wheel-of-fortune'),
					false,
					__("Limit the amount of time a prize can be won. This number is not shown to the user. When a prize reaches its limit, it is still visible on the wheel, but won't be won.", 'mabel-wheel-of-fortune')
				)),
			);

			$wc_coupon_settings = array(
				new Dropdown_Option(
					'wc_coupon_discount_type',
					__('Discount type','mabel-wheel-of-fortune'),
					array(
						'percent' => __('Percentage discount','mabel-wheel-of-fortune'),
						'fixed_cart' => __('Fixed cart discount','mabel-wheel-of-fortune'),
						'fixed_product' => __('Fixed product discount','mabel-wheel-of-fortune'),
					)
				),
				new Number_Option(
					'wc_coupon_min_spend',
					__('Minimum spend', 'mabel-wheel-of-fortune'),
					'',
					__('Set the minimum spend needed to make this coupon valid.','mabel-wheel-of-fortune')
				),
				new Number_Option(
					'wc_coupon_max_spend',
					__('Maximum spend', 'mabel-wheel-of-fortune'),
					'',
					__('Set the maximum spend allowed when using the coupon.','mabel-wheel-of-fortune')
				),
				new Checkbox_Option(
					'wc_coupon_exclude_sales',
					__('Exclude items on sales', 'mabel-wheel-of-fortune'),
					__('Check this box if the coupon should not apply to items on sale.','mabel-wheel-of-fortune')
				),
				new Autocomplete_Option(
					'wc_coupon_include_products',
					__('Include products','mabel-wheel-of-fortune'),
					'',
					'mb-wof-get-wc-product',
					__('Use this setting if you want the coupon to apply only on certain products.', 'mabel-wheel-of-fortune')
				),
				new Autocomplete_Option(
					'wc_coupon_exclude_products',
					__('Exclude products','mabel-wheel-of-fortune'),
					'',
					'mb-wof-get-wc-product',
					__("Use this setting for products you don't want the coupon applied to.", 'mabel-wheel-of-fortune')
				),
				new Autocomplete_Option(
					'wc_coupon_include_categories',
					__('Include categories','mabel-wheel-of-fortune'),
					'',
					'mb-wof-get-wc-categories',
					__("Use this setting for product categories you want the coupon applied to.", 'mabel-wheel-of-fortune')
				),
				new Autocomplete_Option(
					'wc_coupon_exclude_categories',
					__('Exclude categories','mabel-wheel-of-fortune'),
					'',
					'mb-wof-get-wc-categories',
					__("Use this setting for product categories you don't want the coupon applied to.", 'mabel-wheel-of-fortune')
				),
				new Checkbox_Option(
					'wc_free_shipping',
					__('Free shipping','mabel-wheel-of-fortune'),
					null,false,
					__('Check this box if the coupon grants free shipping. A <a href="https://docs.woocommerce.com/document/free-shipping/" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon".','mabel-wheel-of-fortune')
				)
			);

			$email_settings = new Container_Option(null, __('Email setting', 'mabel-wheel-of-fortune'));
			$email_settings->id = 'email_settings';
			$email_settings->options = array(


				$this->add_data_attribute_for_data_bind(
					new Checkbox_Option(
						'send_lost_email',
						__('Send email when lost', 'mabel-wheel-of-fortune'),
						__('Send an email to the user when they lost.', 'mabel-wheel-of-fortune'),
						false
					)
				),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_noprize_subject',
					__("No prize email subject", 'mabel-wheel-of-fortune'),
					null,
					__("Sorry you lost - here's a gift", 'mabel-wheel-of-fortune'),
					__("The subject line for the email when someone didn't win a prize.",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_lost_email', 'true'))
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_noprize_message',
					__("No prize email body", 'mabel-wheel-of-fortune'),
					null,
					__("Hi there,<br/><br/>We're sorry you didn't win this time. Grab another chance tomorrow!",'mabel-wheel-of-fortune'),
					__("The email message when someone didn't win a prize. You can use these special codes: {label},{email},{field.field_id}.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_lost_email', 'true')),
					null,null,null,true
				)),

				$this->add_data_attribute_for_data_bind(
					new Checkbox_Option(
						'send_emails',
						__('Send email when winning', 'mabel-wheel-of-fortune'),
						__('Send an email to the user when they won.', 'mabel-wheel-of-fortune'),
						false
					)
				),

				$this->add_data_attribute_for_data_bind(
					new Checkbox_Option(
						'winnings_only_in_email',
						__('Only inform via email', 'mabel-wheel-of-fortune'),
						__('Do not show winnings (coupon codes or link) on screen, but ONLY send them via email.', 'mabel-wheel-of-fortune'),
						false,
						__('Users will only get their coupon codes via email. If they gave a false email address, they will never be able to use the coupon.','mabel-wheel-of-fortune'),
						array(new Option_Dependency('send_emails', 'true'))
					)
				),

					$this->add_data_attribute_for_data_bind(new Text_Option(
						'email_coupon_subject',
						__("Coupon-winner email subject", 'mabel-wheel-of-fortune'),
						null,
						__('Congrats, you won!', 'mabel-wheel-of-fortune'),
						__('The subject line for the email when someone landed on a coupon slice. You can use these special codes: {field.field_id}','mabel-wheel-of-fortune'),
						array(new Option_Dependency('send_emails', 'true'))
					)),

					$this->add_data_attribute_for_data_bind(new Text_Option(
						'email_coupon_message',
						__("Coupon-winner email body", 'mabel-wheel-of-fortune'),
						null,
						__("Hi there,<br/><br/>Congratulations, you won {label}! Here's your code:<br/>{coupon}<br/><br/>Enjoy shopping!",'mabel-wheel-of-fortune'),
						__('The email message when someone landed a coupon slice. You can use these special codes: {label},{coupon},{email},{field.field_id}.', 'mabel-wheel-of-fortune'),
						array(new Option_Dependency('send_emails', 'true')),
						null,null,null,true
					)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_link_subject',
					__("'Plain link'-winner email subject", 'mabel-wheel-of-fortune'),
					null,
					__('Congrats, you won!', 'mabel-wheel-of-fortune'),
					__("The subject line for the email when someone landed on a 'plain link' slice. You can use these special codes: {field.field_id}",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true'))
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_link_message',
					__("'Plain link'-winner email body", 'mabel-wheel-of-fortune'),
					null,
					__("Hi there,<br/><br/>Congratulations, you won a free ebook! Here's the link to download it:<br/>{link}<br/><br/>Enjoy!",'mabel-wheel-of-fortune'),
					__("The email message when someone landed on a 'plain link' slice. You can use these special codes: {label},{link},{email},{field.field_id}.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true')),
					null,null,null,true
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_redirect_subject',
					__("'Redirect'-winner email subject", 'mabel-wheel-of-fortune'),
					null,
					__('Congrats, you won!', 'mabel-wheel-of-fortune'),
					__("The subject line for the email when someone landed on a 'redirect' slice. You can use these special codes: {field.field_id}",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true'))
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_redirect_message',
					__("'Redirect'-winner email body", 'mabel-wheel-of-fortune'),
					null,
					__("Hi there,<br/><br/>Congratulations, you won a free ebook! Here's the link to download it:<br/>{link}<br/><br/>Enjoy!",'mabel-wheel-of-fortune'),
					__("The email message when someone landed on a 'redirect' slice. You can use these special codes: {label},{link},{email},{field.field_id}.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true')),
					null,null,null,true
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_html_subject',
					__("'Text/HTML'-winner email subject", 'mabel-wheel-of-fortune'),
					null,
					__('Congrats, you won!', 'mabel-wheel-of-fortune'),
					__("The subject line for the email when someone landed on a 'Text/HTML' slice. You can use these special codes: {field.field_id}",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true'))
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'email_html_message',
					__("'Text/HTML'-winner email body", 'mabel-wheel-of-fortune'),
					null,
					__("Hi there,<br/><br/>This is an email to inform you you've won! Here's your prize:<br/>{value}<br/><br/>Enjoy!",'mabel-wheel-of-fortune'),
					__("The email message when someone landed on a 'Text/HTML' slice. You can use these special codes: {label},{value},{email},{field.field_id}.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('send_emails', 'true')),
					null,null,null,true
				)),

				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'notify',
					__("Get notified",'mabel-wheel-of-fortune'),
					__("Receive an email when someone played.",'mabel-wheel-of-fortune'),
					false
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'notify_subject',
					__("Email subject",'mabel-wheel-of-fortune'),
					null,
					__("Someone played the wheel",'mabel-wheel-of-fortune'),
					__("The subject of the notification email.",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('notify','true'))
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'notify_message',
					__("Email body", 'mabel-wheel-of-fortune'),
					null,
					__("Someone just played the wheel '{wheel_name}'. Here's where they landed on:<br/>{slice_data}<br/>And here's what they filled out on the form:<br/>{all_fields}",'mabel-wheel-of-fortune'),
					__("The email notification when someone wins. You can use these special codes: {wheel_name}, {wheel_id}, {all_fields}, {slice_data}. <b>Note</b> if you're using no integration or a Facebook integration, {all_fields} can't be used.", 'mabel-wheel-of-fortune'),
					array(new Option_Dependency('notify', 'true')),
					null,null,null,true
				)),

				$this->add_data_attribute_for_data_bind(new Text_Option(
					'notify_email',
					__("Who should be notified?",'mabel-wheel-of-fortune'),
					null,
					__("you@yourdomain.com",'mabel-wheel-of-fortune'),
					__("Who should we send notifications to? Separate multiple addresses with a comma.",'mabel-wheel-of-fortune'),
					array(new Option_Dependency('notify','true'))
				)),

			);


			$gdpr_settings = new Container_Option(null, __('GDPR', 'mabel-wheel-of-fortune'));
			$gdpr_settings->name = 'gdpr_settings';
			$gdpr_custom_setting = new Custom_Option(__('Send data to email list','mabel-wheel-of-fortune'),'gdpr-settings');
			$gdpr_settings->options = array( $gdpr_custom_setting );

			$integration_settings = new Container_Option(null, __('Integrations','mabel-wheel-of-fortune'));

			$integration_settings->options = array(
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'optin_webhook',
					__('Optin webhook', 'mabel-wheel-of-fortune'),
					null,'https://hooks.zapier.com/hooks/catch/688325/ivttbz/',
					__('Need to do some extra processing when a user opts in? You can define that here.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Text_Option(
					'play_webhook',
					__('Play webhook', 'mabel-wheel-of-fortune'),
					null,'https://hooks.zapier.com/hooks/catch/232989/xxncvv/',
					__('Need to do some extra processing when a user wins or loses ( = plays)? You can define that here.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'enable_fb',
					__('Enable Facebook opt-in','mabel-wheel-of-fortune'),
					__('Enable Facebook opt-in.','mabel-wheel-of-fortune'),
					false,
					__('Enabling Facebook opt-in will show the Facebook "Send to Messenger" opt-in checkbox.','mabel-wheel-of-fortune')
				)),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'fb_obligated',
					__('Opt-in obligated','mabel-wheel-of-fortune'),
					__('Facebook opt-in is obligated before playing','mabel-wheel-of-fortune'),
					__('Check this if the Facebook checkbox should be checked before being able to play the wheel.','mabel-wheel-of-fortune'),
					null,
					array(new Option_Dependency('enable_fb','true'))
				))
			);

			$extra_coupon_settings = new Container_Option(null, __('More coupon settings','mabel-wheel-of-fortune'));
			$extra_coupon_settings->id = "coupon_bar_settings";
			$extra_coupon_settings->options = array(
                $this->add_data_attribute_for_data_bind( new Checkbox_Option(
                    'woo_auto_apply',
                    __('Auto. apply coupons', 'mabel-wheel-of-fortune'),
                    __("Automatically apply coupons to the user's cart", 'mabel-wheel-of-fortune'),
                    false
                )),
				$this->add_data_attribute_for_data_bind(new Checkbox_Option(
					'coupon_bar',
					__('Coupon bar','mabel-wheel-of-fortune'),
					__('Use a coupon bar','mabel-wheel-of-fortune'),
					false,
					__('Show a coupon bar at the bottom of the page when a visitor won a coupon. This bar will contain a countdown timer until the coupon expires. This adds an extra sense of urgency.','mabel-wheel-of-fortune')
				)),
                $this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'bar_bgcolor',
					null,
					__('Bar background color','mabel-wheel-of-fortune'),
					null,
					array(new Option_Dependency('coupon_bar','true'))
				)),
                $this->add_data_attribute_for_data_bind(new ColorPicker_Option(
					'bar_fgcolor',
					null,
					__('Bar text color','mabel-wheel-of-fortune'),
					null,
					array(new Option_Dependency('coupon_bar', 'true'))
				)),
                $this->add_data_attribute_for_data_bind(new Text_Option(
					'bar_text',
					__('Coupon bar text','mabel-wheel-of-fortune'),
					null,
					__('Your coupon code {code} is valid for {countdown}','mabel-wheel-of-fortune'),
					__('The text that should appear on the coupon bar. Use {code} as dynamic coupon code, {countdown} for the countdown timer.','mabel-wheel-of-fortune'),
					array(new Option_Dependency('coupon_bar', 'true'))
				)),
                $this->add_data_attribute_for_data_bind(new Text_Option(
					'bar_days',
					__("'Days' text",'mabel-wheel-of-fortune'),
					null,
					'd',
					null,
					array(new Option_Dependency('coupon_bar', 'true'))
				)),
                $this->add_data_attribute_for_data_bind(new Text_Option(
					'bar_hours',
					__("'Hours' text",'mabel-wheel-of-fortune'),
					null,
					'h',
					null,
					array(new Option_Dependency('coupon_bar', 'true'))
				)),
                $this->add_data_attribute_for_data_bind(new Text_Option(
					'bar_minutes',
					__("'Minutes' text",'mabel-wheel-of-fortune'),
					null,
					'm',
					null,
					array(new Option_Dependency('coupon_bar', 'true'))
				)),
                $this->add_data_attribute_for_data_bind(new Text_Option(
					'bar_seconds',
					__("'Seconds' text",'mabel-wheel-of-fortune'),
					null,
					's',
					null,
					array(new Option_Dependency('coupon_bar', 'true'))
				))
			);

			$integrations_extra_settings = [];
			$integrations = Integrations_Service::get_integrations();

            foreach ($integrations as $integration) {
                if(!empty($integration->settings)) {
                    $container = new Container_Option(
                        null,
                        $integration->title .' '. __('Settings','mabel-wheel-of-fortune')
                    );
                    foreach($integration->settings as $s){
                        $container->options[] = $this->add_data_attribute_for_data_bind($s);
                    }
                    $container->id = $integration->id .'_settings';
                    $integrations_extra_settings[] = $container;
                }
            }

			$wheel = new Wheel_Model();
			$wheel->id = 'preview_wheel';
			$wheel->list_provider = 'wordpress';
			$wheel->disclaimer = __('Our in-house rules:<ul><li>One game per user</li><li>Cheaters will be disqualified.</li></ul>','mabel-wheel-of-fortune');
			$wheel->explainer = __('This is your chance to win amazing discounts. Press the button below and let the wheel decide your chance to wine a prize!','mabel-wheel-of-fortune');
			$wheel->fields = array(
				(object) array(
					'id' => 'primary_email',
					'placeholder' => __('Your email','mabel-wheel-of-fortune'),
					'required' => true,
					'type' => 'text'
				), (object) array(
					'id' => 'name',
					'placeholder' => __('Your name','mabel-wheel-of-fortune'),
					'type' => 'text'
				)
			);

			$preview_theme = Theming_Service::get_theme('blue');
			$wheel->slices = array(
				(object) array('id' => 1, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][0],'fg' => $preview_theme['slices']['fg'][0]),
				(object) array('id' => 2, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][1],'fg' => $preview_theme['slices']['fg'][1]),
				(object) array('id' => 3, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][2],'fg' => $preview_theme['slices']['fg'][2]),
				(object) array('id' => 4, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][3],'fg' => $preview_theme['slices']['fg'][3]),
				(object) array('id' => 5, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][4],'fg' => $preview_theme['slices']['fg'][4]),
				(object) array('id' => 6, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][5],'fg' => $preview_theme['slices']['fg'][5]),
				(object) array('id' => 7, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][6],'fg' => $preview_theme['slices']['fg'][6]),
				(object) array('id' => 8, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][7],'fg' => $preview_theme['slices']['fg'][7]),
				(object) array('id' => 9, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][8],'fg' => $preview_theme['slices']['fg'][8]),
				(object) array('id' => 10, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][9],'fg' => $preview_theme['slices']['fg'][9]),
				(object) array('id' => 11, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][10],'fg' => $preview_theme['slices']['fg'][10]),
				(object) array('id' => 12, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][11],'fg' => $preview_theme['slices']['fg'][11]),
				(object) array('id' => 13, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][12],'fg' => $preview_theme['slices']['fg'][12]),
				(object) array('id' => 14, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][13],'fg' => $preview_theme['slices']['fg'][13]),
				(object) array('id' => 15, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][14],'fg' => $preview_theme['slices']['fg'][14]),
				(object) array('id' => 16, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][15],'fg' => $preview_theme['slices']['fg'][15]),
				(object) array('id' => 17, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][16],'fg' => $preview_theme['slices']['fg'][16]),
				(object) array('id' => 18, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][17],'fg' => $preview_theme['slices']['fg'][17]),
				(object) array('id' => 19, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][18],'fg' => $preview_theme['slices']['fg'][18]),
				(object) array('id' => 20, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][19],'fg' => $preview_theme['slices']['fg'][19]),
				(object) array('id' => 21, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][20],'fg' => $preview_theme['slices']['fg'][20]),
				(object) array('id' => 22, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][21],'fg' => $preview_theme['slices']['fg'][21]),
				(object) array('id' => 23, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][22],'fg' => $preview_theme['slices']['fg'][22]),
				(object) array('id' => 24, 'label' => 'Text','bg' => $preview_theme['slices']['bg'][23],'fg' => $preview_theme['slices']['fg'][23]),
			);
			$wheel->title = __('Get your chance to <em>win a prize</em>!','mabel-wheel-of-fortune');
			$wheel->is_preview = true;

			$vm = new Wheel_Shortcode_VM();
			$vm->wheel = $wheel;

			return array(
				'wheels_vm' => $vm,
				'base_url' => Config_Manager::$url,
				'default_name' => ucfirst(get_bloginfo('name')) .' Wheel',
				'theme_setting' => $theme_setting,
				'design_settings' => $design_settings,
				'slices_design_settings' => $design_slices_setting,
				'slices' => $slices,
				'chance_settings' => $chance_settings,
				'form_builder_for_lists' => $form_builder_for_lists_settings,
				'form_builder_for_other' => $form_builder_for_others_settings,
				'settings' => array(
					$content_settings, $behavior_settings, $animation_sound_settings,$security_settings, $mailchimp_list_settings, $email_settings, $gdpr_settings, $integration_settings, $extra_coupon_settings
				),
				'woo_active' => is_plugin_active( 'woocommerce/woocommerce.php'),
				'woo_coupon_settings' => $wc_coupon_settings,
                'integration_settings' => $integrations_extra_settings
			);

		}

		private function get_all_languages_as_options() {
			if(!function_exists('icl_get_languages'))
				return array();

			$languages = icl_get_languages('skip_missing=0&orderby=code');

			$languages = Enumerable::from($languages)->select(function($x){
				return array(
					'key' => isset($x['code']) ? $x['code'] : $x['language_code'],
					'value' => $x["native_name"]
				);
			})->toArray();

			$options = array('-1' => __('All languages', 'mabel-wheel-of-fortune'));

			foreach($languages as $l){
				$options[$l['key']] = $l['value'];
			}

			return array(
				'Languages' => $options,
			);

		}

		private function get_all_pages_as_options() {
			$options = array(
				'WordPress pages' => array(
					'-1' => __('Everywhere', 'mabel-wheel-of-fortune'),
					'-2' => __('Front page', 'mabel-wheel-of-fortune'),
					'-3' => __('Posts page', 'mabel-wheel-of-fortune'),
					'-4' => __('All blog posts', 'mabel-wheel-of-fortune'),
				),
			);

			if(is_plugin_active( 'woocommerce/woocommerce.php')) {
				$options['WordPress pages']['-5'] = __('All product pages', 'mabel-wheel-of-fortune');

				$woo_product_categories = WC_Service::get_categories();
				if(!empty($woo_product_categories)) {
					$options['Product categories'] = array();
					foreach($woo_product_categories as $k=>$v){
						$options['Product categories']['wcpc-'.$k] = $v;
					}
				}

				$options['WooCommerce'] = array(
					'-6' => __('"Order recevied" page', 'mabel-wheel-of-fortune'),
					'-7' => __('"View order" page', 'mabel-wheel-of-fortune'),
				);

			}

			$cpts = get_post_types(array(
				'public' => true,
				'_builtin' => false
			));

			if(sizeof($cpts) > 0){
				$options['Custom post types'] = array();
				foreach ($cpts as $k => $v){
					$options['Custom post types']['cpt-'.$k] = $v;
				}
			}

			$all_pages = get_pages(array('post_type' => 'page'));
			foreach($all_pages as $page) {
				$options['Individual pages'][$page->ID] = $page->post_title;
			}

			return $options;
		}

		private function add_data_attribute_for_data_bind(Option $option)
		{
			$option->data_attributes['key'] = $option->id;
			if(substr($option->id, -strlen('_list')) === '_list')
				$option->data_attributes['optin-list'] = '';
			return $option;
		}

	}
}