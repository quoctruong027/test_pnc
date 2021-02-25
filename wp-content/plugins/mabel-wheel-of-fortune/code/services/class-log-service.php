<?php

namespace MABEL_WOF\Code\Services {

	use MABEL_WOF\Code\Models\Wheel_Model;

	class Log_Service {

		public static function type_of_logging(Wheel_Model $wheel){
			if($wheel->log)
				return 'full';
			if($wheel->limit_prizes)
				return 'limit';
			return 'minimal';
		}

		public static function drop_logs(){
			global $wpdb;
			$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix.'wof_optins' );
		}

		public static function delete_all_logs_from_db($wheel_id) {
			global $wpdb;

			$wpdb->delete( $wpdb->prefix.'wof_optins', array( 'wheel_id' => intval($wheel_id) ) );
		}

		public static function log_play_to_db($wheel_id, $email, $winning, $segment,$segment_text,$prize,$segment_type) {
			global $wpdb;

			$result = $wpdb->insert($wpdb->prefix .'wof_optins', array(
				'wheel_id' => $wheel_id,
				'email' => $email,
				'created_date' => current_time('Y-m-d H:i:s',true),
				'unique_hash' => hash('md5',Helper_Service::get_visitor_ip()),
				'segment' => $segment,
				'segment_text' => $segment_text,
				'prize' => $segment_type == 4 ? '[custom text/html]' : $prize,
				'winning' => $winning,
				'type' => 1 
			));
		}

		public static function update_optin_in_db($id,$wheel_id,$email,$fields) {
			global $wpdb;

			$result = $wpdb->update($wpdb->prefix .'wof_optins', array(
				'wheel_id' => $wheel_id,
				'email' => $email,
				'created_date' => current_time('Y-m-d'),
				'fields' => json_encode($fields)
			),array(
				'id' => $id
			));

			return $result;
		}

		#region GDPR functions
		public static function anonymize_logs($email){
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix.'wof_optins',
				array(
					'fields' => wp_privacy_anonymize_data('text'),
					'segment_text' => wp_privacy_anonymize_data('text'),
					'prize' => wp_privacy_anonymize_data('text'),
					'created_date' => wp_privacy_anonymize_data('date'),
					'winning' => -1,
				),
				array(
					'email' => $email
				)
			);
		}

		public static function get_logs_of_email($email){
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM ". $wpdb->prefix. "wof_optins WHERE email = %s",
					array(
						$email,
					)
				)
			);

			return $results;
		}
		#endregion

		public static function log_optin_to_db($wheel_id, $email, $fields = null ) {
			global $wpdb;

			$insert_array =  array(
				'wheel_id' => $wheel_id,
				'email' => $email,
				'created_date' => current_time('Y-m-d H:i:s',true),
				'unique_hash' => hash('md5',Helper_Service::get_visitor_ip()),
				'type' => 0 
			);
			if($fields != null)
				$insert_array['fields'] = json_encode($fields);

			$result = $wpdb->insert($wpdb->prefix .'wof_optins', $insert_array);

			return $result;
		}

		public static function get_last_logs($wheel_id) {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare("
					SELECT wheel_id,created_date,fields,email,segment,winning,segment_text,prize,
					CASE WHEN type != 1 THEN 'opt-in' ELSE 'play' END AS type_description 
					FROM ". $wpdb->prefix. "wof_optins 
					WHERE wheel_id = %d
					ORDER BY id
					ASC LIMIT 30",
					array(
						$wheel_id,
					)
				)
			);
			return $results;
		}

		public static function get_all_optins($wheel_id) {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare('SELECT * FROM '. $wpdb->prefix. 'wof_optins WHERE wheel_id = %d and type != 1 ORDER BY id ASC', array(
					$wheel_id,
				))
			);
			return $results;
		}

		public static function get_all_plays($wheel_id) {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare('SELECT * FROM '. $wpdb->prefix. 'wof_optins WHERE wheel_id = %d and type = 1 ORDER BY id ASC', array(
					$wheel_id,
				))
			);

			return $results;
		}

		public static function has_played_yet(Wheel_Model $wheel,$provider_obj,$mail = '', &$out_checked_with = null) {
			global $wpdb;

			if(!$provider_obj->needsEmail && !$wheel->log_ips && !$provider_obj->isFbOptin)
				return false;

			$where = 'wheel_id = %d AND type = 0';

			$check_with = 'mail';

			if($wheel->log_ips){
				if($provider_obj->needsEmail)
					$check_with = 'mail+ip';
				else $check_with = 'ip';
			}

			if($provider_obj->isFbOptin){
				$check_with = 'ip';
			}

			$where_vars = array($wheel->id);
			switch($check_with){
				case 'mail':
					$where .= ' AND email = %s';
					$where_vars[] = $mail;
					break;
				case 'ip':
					$where .= ' AND unique_hash = %s';
					$where_vars[] = hash('md5',Helper_Service::get_visitor_ip());
					break;
				case 'mail+ip':
					$where .= ' AND (email = %s OR unique_hash = %s)';
					$where_vars[] = $mail;
					$where_vars[] = hash('md5',Helper_Service::get_visitor_ip());
					break;
			}
			$out_checked_with = $check_with;

			$results = $wpdb->get_results(
				$wpdb->prepare('SELECT unique_hash, email FROM '.$wpdb->prefix.'wof_optins WHERE '.$where, $where_vars)
			);

			return count($results) > 0;

		}

		public static function has_ip_played_yet($wheel_id,$ip) {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare('SELECT id FROM '.$wpdb->prefix.'wof_optins WHERE wheel_id = %d and type = 1 AND ip = %s', array(
				$wheel_id,
				$ip
			)));

			return count($results) > 0;
		}
	}

}