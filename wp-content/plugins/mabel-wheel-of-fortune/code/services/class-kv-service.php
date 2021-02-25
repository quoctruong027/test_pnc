<?php

namespace MABEL_WOF\Code\Services {

	use MABEL_WOF\Core\Common\Linq\Enumerable;
	use MABEL_WOF\Core\Common\Managers\Settings_Manager;
    use WP_Error;

	class KV_Service {

		public static function add_to_list($list_id, $email, $fields = array()) {

			$values = array(
				'email' => $email,
				'confirm_optin' => 'false',
			);

			if(!empty($fields)){
				$values['properties'] = array();
				foreach($fields as $f){
					$values['properties'][$f->id] = $f->value;
				}
				$values['properties'] = json_encode($values['properties']);
			}

			$values = apply_filters('wof_klaviyo_values',$values);

			$response = self::request('list/'.$list_id.'/members', $values);

			if($response === null || $response->status !== 200)
				return "Could not add email to list.";

			return true;
		}

		public static function get_fields_from_list() {
			return array(
				array('id' => '$first_name', 'title' => 'First name', 'type' => 'text'),
				array('id' => '$last_name', 'title' => 'Last name', 'type' => 'text'),
				array('id' => '$phone_number', 'title' => 'Phone number', 'type' => 'text'),
				array('id' => '$title', 'title' => 'Title', 'type' => 'text'),
				array('id' => '$organization', 'title' => 'Organization', 'type' => 'text'),
				array('id' => '$city', 'title' => 'City', 'type' => 'text'),
				array('id' => '$region', 'title' => 'Region', 'type' => 'text'),
				array('id' => '$country', 'title' => 'Country', 'type' => 'text'),
				array('id' => '$zip', 'title' => 'Zip code', 'type' => 'text'),
			);
		}

		public static function get_email_lists() {

			$response = self::request('lists',null,'get');

			if($response->status === 200) {
				return Enumerable::from($response->body->data)->where(function($x){
					return $x->list_type === 'list';
				})->select(function($x){
					return array(
						'id' => $x->id,
						'title' => $x->name
					);
				})->toArray();
			}

			return new WP_Error();

		}

		public static function is_in_list($list_id, $email) {
			$response = self::request('list/'.$list_id.'/members',array('email' => $email),'get');

			if($response->status === 200) {
				if(isset($response->body->data) && empty($response->body->data))
					return false;
				if(isset($response->body->data) && count($response->body->data) === 1)
					return $response->body->data[0];
			}

			return false; 
		}

		private static function request($action, array $body = null, $method = 'post') {
			$base = 'https://a.klaviyo.com/api/v1/';
			$url =  $base . $action .'?api_key='. Settings_Manager::get_setting('kv_api');

			if($method === 'get' && sizeof($body) > 0)
				$url .= '&'.Enumerable::from($body)->join(function($v, $k){
						return urlencode($k).'='.urlencode($v);
					}, '&');

			$headers = array(
				'Content-Type' => $method === 'get' ? 'application/json' : 'application/x-www-form-urlencoded'
			);

			$options = array(
				'timeout' => 15,
				'headers' => $headers,
				'method' => strtoupper($method)
			);

			if($body != null && $method === 'post')
				$options['body'] = $body;

			$response = $method === 'post' ? wp_remote_post( $url, $options) : wp_remote_get($url,$options);

			if(is_wp_error($response)) return null;

			return (object) array(
				'status' => $response['response']['code'],
				'body' => json_decode(wp_remote_retrieve_body($response))
			);
		}
	}
}