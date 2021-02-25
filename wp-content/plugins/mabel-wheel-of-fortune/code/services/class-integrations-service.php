<?php

namespace MABEL_WOF\Code\Services {

	use MABEL_WOF\Core\Common\Linq\Enumerable;
	use MABEL_WOF\Core\Common\Managers\Config_Manager;
	use MABEL_WOF\Core\Common\Managers\Settings_Manager;
	use MABEL_WOF\Core\Models\Checkbox_Option;
	use MABEL_WOF\Core\Models\Info_Option;
    use MABEL_WOF\Core\Models\Number_Option;
    use MABEL_WOF\Core\Models\Text_Option;

	class Integrations_Service {

		public static function get_integrations_by_id($id){
			$integrations = self::get_integrations();
			return Enumerable::from($integrations)->firstOrDefault(function($x) use ($id){
				return $x->id === $id;
			});
		}

		public static function get_integrations() {

			$name = Config_Manager::$settings_key.'[%]';
			$url = Config_Manager::$url;

			$integrations = array(
				(object)array(
					'id' => 'none',
					'title' => __('None. I don\'t want to capture data.','mabel-wheel-of-fortune'),
					'isListProvider' => false,
					'showListsSetting' => false,
					'installed' => true,
					'hideFormBuilder' => true,
					'needsEmail' => false,
					'isFbOptin' => false,
					'card' => array()
				),
				(object) array(
					'id' => 'wordpress',
					'title' => 'WordPress',
					'isListProvider' => false,
                    'showListSetting' => false,
					'hideFormBuilder' => false,
					'installed' => true,
					'needsEmail' => true,
					'isFbOptin' => false,
					'card' => array(
						'classes' => 'wof-integration-m',
						'img' => $url .'admin/img/integrations/wordpress.png',
						'background' => '#00749b',
						'settings' => array(
							new Info_Option(null,__('WordPress does not require any additional settings :-).','mabel-wheel-of-fortune'))
						)
					)
				),
				(object) array(
					'id' => 'zapier',
					'title' => 'Zapier',
					'isListProvider' => false,
                    'showListSetting' => false,
					'hideFormBuilder' => false,
					'installed' => true,
					'needsEmail' => true,
					'isFbOptin' => false,
					'card' => array(
						'img' => $url .'admin/img/integrations/zapier.png',
						'background' => '#ff6b2e',
						'classes' => 'wof-integration-s',
						'settings' => array(
							new Info_Option(null,__('Zapier does not require any additional settings :-).','mabel-wheel-of-fortune'))
						)
					),

				),
				(object) array(
					'id' => 'mailchimp',
					'title' => 'MailChimp',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('mailchimp_api'),
					'keys' => array('mailchimp_api'),
					'card' => array(
						'classes' => 'wof-integration-m',
						'img' => $url . 'admin/img/integrations/mailchimp.png',
						'background' => '#ffbc79',
						'settings' => array (
							new Text_Option(
								str_replace('%','mailchimp_api',$name),
								__('MailChimp API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('mailchimp_api'),
								null,
								__('If you want to use Mailchimp for email optin, enter your API Key here.', 'mabel-wheel-of-fortune')
							),
							new Checkbox_Option(
								str_replace('%','mailchimp_double_optin',$name),
								__('Use double opt-in', 'mabel-wheel-of-fortune'),
								__('Use double opt-in', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('mailchimp_double_optin')
							),
						)
					)

				),
				(object) array(
					'id' => 'ac',
					'title' => 'ActiveCampaign',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('ac_api') && Settings_Manager::has_setting('ac_url'),
					'keys' => array('ac_api','ac_url'),
					'card' => array(
						'img' => $url . 'admin/img/integrations/activecampaign.png',
						'background' => '#4073B5',
						'settings' => array(
							new Text_Option(
								str_replace('%','ac_api',$name),
								__('ActiveCampaign API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('ac_api'),
								null,
								__('If you want to use ActiveCampaign for email optin, enter your API Key here. It can be found under My Settings > Developer.', 'mabel-wheel-of-fortune')
							),
							new Text_Option(
								str_replace('%','ac_url',$name),
								__('ActiveCampaign URL', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('ac_url'),
								'https://account.api-us1.com',
								__('This is needed to connect to the ActiveCampaign API. It can be found under My Settings > Developer.', 'mabel-wheel-of-fortune')
							)
						)
					)

				),(object) array(
					'id' => 'gr',
					'title' => 'GetResponse',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('gr_api'),
					'keys' => array('gr_api'),
					'card' => array(
						'img' => $url . 'admin/img/integrations/getresponse.PNG',
						'background' => '#323232',
						'classes' => 'wof-integration-m',
						'settings' => array(
							new Text_Option(
								str_replace('%','gr_api',$name),
								__('GetResponse API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('gr_api'),
								null,
								__('Find your API key <a href="https://app.getresponse.com/api" target=\"_blank\">here in GetResponse</a>.', 'mabel-wheel-of-fortune')
							)
						)
					)
				),
				(object) array(
					'id' => 'cm',
					'title' => 'Campaign Monitor',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('cm_api') && Settings_Manager::has_setting('cm_client'),
					'keys' => array('cm_api','cm_client'),
					'card' => array(
						'img' => $url . 'admin/img/integrations/campaignmonitor.png',
						'background' => '#73d2ff',
						'classes' => 'wof-integration-xl',
						'settings' => array(
							new Text_Option(
								str_replace('%','cm_api',$name),
								__('Campaign Monitor API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('cm_api'),
								null,
								__('If you want to use Campaign Monitor for email optin, enter your API Key here.', 'mabel-wheel-of-fortune')
							),
							new Text_Option(
								str_replace('%','cm_client',$name),
								__('Campaign Monitor Client ID', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('cm_client'),
								null,
								__('If you want to use Campaign Monitor, enter your Client ID here.', 'mabel-wheel-of-fortune')
							)
						)
					)
				),
				(object) array(
					'id' => 'ml',
					'title' => 'MailerLite',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('ml_api'),
					'keys' => array('ml_api'),
					'card' => array(
						'img' => $url . 'admin/img/integrations/mailerlite.png',
						'background' => '#0FA252',
						'classes' => 'wof-integration-m',
						'settings' => array(
							new Text_Option(
								str_replace('%','ml_api',$name),
								__('MailerLite API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('ml_api'),
								null,
								__('If you want to use MailerLite for email optin, enter your API Key here.', 'mabel-wheel-of-fortune')
							)
						)
					)
				), (object) array(
					'id' => 'kv',
					'title' => 'Klaviyo',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => Settings_Manager::has_setting('kv_api'),
					'keys' => array('kv_api'),
					'card' => array(
						'classes' => 'wof-integration-s',
						'img' => $url .'admin/img/integrations/klaviyo.JPG',
						'background' => '#24CE7B',
						'settings' => array(
							new Text_Option(
								str_replace('%','kv_api',$name),
								__('Klaviyo API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('kv_api'),
								null,
								__('Find or create a private API key in your Klaviyo account under Settings > API Keys.', 'mabel-wheel-of-fortune')
							)
						)
					)
				), (object) array(
					'id' => 'mailster',
					'title' => 'Mailster',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'needsEmail' => true,
					'isFbOptin' => false,
					'installed' => function_exists('mailster'),
					'card' => array(
						'classes' => 'wof-integration-s',
						'img' => $url .'admin/img/integrations/mailster.jpg',
						'background' => '#2ab3e7',
						'settings' => array(
							new Info_Option(null,function_exists('mailster') ? 'Mailster is up & running!' : 'This extension needs the Mailster plugin to work.')
						)
					)
				),
				(object) array(
					'id' => 'ck',
					'title' => 'ConvertKit',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'installed' => Settings_Manager::has_setting('ck_key'),
					'needsEmail' => true,
					'isFbOptin' => false,
					'keys' => array('ck_key','ck_secret'),
					'card' => array(
						'classes' => 'wof-integration-s',
						'img' => $url .'admin/img/integrations/convertkit.jpg',
						'background' => '#3390D5',
						'settings' => array(
							new Text_Option(
								str_replace('%','ck_key',$name),
								__('API Key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('ck_key'),
								null,
								__("You can find your ConvertKit API key on the 'Account settings' page.",'mabel-wheel-of-fortune')
							),
							new Text_Option(
								str_replace('%','ck_secret',$name),
								__('API Secret', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('ck_secret'),
								null,
								__("You can find your ConvertKit API Secret on the 'Account settings' page.",'mabel-wheel-of-fortune')
							)
						)
					)
				),
				(object) array(
                    'id' => 'rm',
                    'title' => 'Remarketly',
                    'isListProvider' => true,
                    'showListSetting' => true,
                    'hideFormBuilder' => false,
                    'installed' => Settings_Manager::has_setting('rm_key'),
                    'needsEmail' => true,
                    'isFbOptin' => false,
                    'keys' => array('rm_key'),
                    'card' => array(
                        'classes' => 'wof-integration-m',
                        'img' => $url .'admin/img/integrations/remarkety.PNG',
                        'background' => '#F5F5F5',
                        'settings' => array(
                            new Text_Option(
                                str_replace('%','rm_key',$name),
                                __('Store ID', 'mabel-wheel-of-fortune'),
                                Settings_Manager::get_setting('rm_key'),
                                null,
                                __('Find your store ID by going to Settings > API keys in your Remarkety account.','mabel-wheel-of-fortune')
                            )
                        )
                    )
                ),
				(object) array(
					'id' => 'chatfuel',
					'title' => 'ChatFuel',
					'isListProvider' => false,
					'showListSetting' => false,
					'hideFormBuilder' => true,
					'installed' => Settings_Manager::has_setting('chatfuel_page_id') && Settings_Manager::has_setting('chatfuel_app_id'),
					'needsEmail' => false,
					'isFbOptin' => true,
					'keys' => array('chatfuel_page_id','chatfuel_app_id'),
					'card' => array(
						'classes' => 'wof-integration-m',
						'img' => $url .'admin/img/integrations/chatfuel.jpg',
						'background' => '#053748',
						'settings' => array(
							new Text_Option(
								str_replace('%','chatfuel_page_id',$name),
								__('Page ID', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('chatfuel_page_id'),
								null,
								__("<a target=\"_blank\" href=\"https://studiowombat.com/kb-article/setup-facebook-messenger-opt-in-with-chatfuel/\">Follow this guide.</a>",'mabel-wheel-of-fortune')
							),
							new Text_Option(
								str_replace('%','chatfuel_app_id',$name),
								__('App ID', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('chatfuel_app_id'),
								null,
								__("<a target=\"_blank\" href=\"https://studiowombat.com/kb-article/setup-facebook-messenger-opt-in-with-chatfuel/\">Follow this guide.</a>",'mabel-wheel-of-fortune')							)
						)
					)
				),
				(object) array(
					'id' => 'newsletter2go',
					'title' => 'Newsletter2Go',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'installed' => Settings_Manager::has_setting('nl2go_authkey') && Settings_Manager::has_setting('nl2go_u') && Settings_Manager::has_setting('nl2go_pw'),
					'needsEmail' => true,
					'isFbOptin' => false,
					'keys' => array('nl2go_authkey','nl2go_u','nl2go_pw'),
					'card' => array(
						'classes' => 'wof-integration-s',
						'img' => $url .'admin/img/integrations/newsletter2go.png',
						'background' => 'white',
						'settings' => array(
							new Text_Option(
								str_replace('%','nl2go_u',$name),
								__('Username', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('nl2go_u')
							),
							new Text_Option(
								str_replace('%','nl2go_pw',$name),
								__('Password', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('nl2go_pw')
							),
							new Text_Option(
								str_replace('%','nl2go_authkey',$name),
								__('Auth key', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('nl2go_authkey'),
								null,
								__('Find your <a href="https://ui.newsletter2go.com/api-client" target="_blank">auth key here</a>.', 'mabel-wheel-of-fortune')
							),
						)
					)
				),
				(object) array(
					'id' => 'sib',
					'title' => 'SendInBlue',
					'isListProvider' => true,
					'showListSetting' => true,
					'hideFormBuilder' => false,
					'installed' => Settings_Manager::has_setting('sib_apiv3') ,
					'needsEmail' => true,
					'isFbOptin' => false,
					'keys' => array('sib_apiv3'),
					'card' => array(
						'classes' => 'wof-integration-m',
						'img' => $url .'admin/img/integrations/sendinblue.JPG',
						'background' => '#2B82C5',
						'settings' => array(
							new Text_Option(
								str_replace('%','sib_apiv3',$name),
								__('Api key (v3)', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('sib_apiv3'),
								null,
								__('Find your <a href="https://account.sendinblue.com/advanced/api" target="_blank">API key here</a>.', 'mabel-wheel-of-fortune')
							),
							new Text_Option(
								str_replace('%','sib_api',$name),
								__('Api key (v2)', 'mabel-wheel-of-fortune'),
								Settings_Manager::get_setting('sib_api'),
								null,
								__("<b>V2 api will be deprecated soon</b>. If you are using v2, you should create a v3 API key instead and input it above.", 'mabel-wheel-of-fortune')
							),
						)
					)
				),
                (object) array(
                    'id' => 'drip',
                    'title' => 'Drip',
                    'isListProvider' => true,
                    'showListSetting' => true,
                    'hideFormBuilder' => false,
                    'installed' => Settings_Manager::has_setting('drip_api') && Settings_Manager::has_setting('drip_account'),
                    'needsEmail' => true,
                    'isFbOptin' => false,
                    'keys' => array('drip_api','drip_account'),
                    'card' => array(
                        'classes' => 'wof-integration-m',
                        'img' => $url .'admin/img/integrations/drip.png',
                        'background' => '#110211',
                        'settings' => array(
                            new Text_Option(
                                str_replace('%','drip_api',$name),
                                __('Api token', 'mabel-wheel-of-fortune'),
                                Settings_Manager::get_setting('drip_api'),
                                null,
                                __('Find your API token in your <a href="https://www.getdrip.com/user/edit" target="_blank">Drip user settings</a>.', 'mabel-wheel-of-fortune')
                            ),
                            new Text_Option(
                                str_replace('%','drip_account',$name),
                                __('Account id', 'mabel-wheel-of-fortune'),
                                Settings_Manager::get_setting('drip_account'),
                                null,
                                __('Find your account number by clicking the 3 dots and going to "Account" in Drip.', 'mabel-wheel-of-fortune')
                            ),
                        )
                    )
                )
			);

			$integrations = apply_filters('wof-add-integrations',$integrations);

			return $integrations;

		}
	}
}