<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SO_Admin_Deactivation' ) ) {

	class SO_Admin_Deactivation {

		public static $so_deactivation_string;

		/**
		* @var string Plugin name
		* @access public 
		*/
		public static $plugin_name = '';

		/**
		 * @var string Plugin file name
		 * @access public
		 */
		public static $sa_plugin_file_name = '';

		/**
		 * @var string Plugin URL
		 * @access public
		 */
		public static $sa_plugin_url = '';

		function __construct( $sa_plugin_file_name = '', $sa_plugin_name = '' ) {

			self::$sa_plugin_file_name = $sa_plugin_file_name;
			self::$plugin_name         = $sa_plugin_name;
			self::$sa_plugin_url       = untrailingslashit( plugin_dir_path ( __FILE__ ) );

			self::sa_load_all_str();
			add_action( 'admin_footer', array( $this, 'maybe_load_deactivate_options' ) );
			add_action( 'wp_ajax_so_submit_survey', array( $this, 'sa_submit_deactivation_reason_action' ) );

			add_filter( 'plugin_action_links_' . self::$sa_plugin_file_name, array( $this, 'sa_plugin_settings_link' ) );

		}

		/**
		 * Settings link on Plugins page
		 * 
		 * @access public
		 * @param array $links 
		 * @return array
		 */
		public static function sa_plugin_settings_link( $links ) {
			
			if ( isset ( $links['deactivate'] ) ) {
				$links['deactivate'] .= '<i class="sa-so-slug" data-slug="' . self::$sa_plugin_file_name  . '"></i>';
			}
			return $links;
		}

		/**
		 * Localizes all the string used
		 */
		public static function sa_load_all_str() {
			self::$so_deactivation_string = array(
				'deactivation-headline'		               => __( 'Quick Feedback for Smart Offers plugin', 'smart-offers' ),
				'deactivation-share-reason'                => __( 'Take a moment to let us know why you are deactivating', 'smart-offers' ),
				'deactivation-modal-button-submit'         => __( 'Submit & Deactivate', 'smart-offers' ),
				'deactivation-modal-button-deactivate'     => __( 'Deactivate', 'smart-offers' ), // ?
				'deactivation-modal-button-cancel'         => __( 'Skip & Deactivate', 'smart-offers' ),
				'deactivation-modal-button-confirm'        => __( 'Yes - Deactivate', 'smart-offers' ),
				'deactivation-modal-skip-deactivate'       => __( 'Submit a reason to deactivate', 'smart-offers' ),
				'deactivation-modal-error'       		   => __( 'Please select an option', 'smart-offers' ),
			);
		}

		/**
		 * Checking current page and pushing html, js and css for this task
		 * @global string $pagenow current admin page
		 * @global array $vars global vars to pass to view file
		 */
		public static function maybe_load_deactivate_options() {
			global $pagenow;
			if ( $pagenow == 'plugins.php' ) {
				global $vars;
				$vars = array( 'slug' => "asvbsd", 'reasons' => self::so_deactivate_options() );
				include_once self::$sa_plugin_url . '/class-so-admin-deactivation-modal.php';
			}
		}

		/**
		 * deactivation reasons in array format
		 * @return array reasons array
		 * @since 3.7.1
		 */
		public static function so_deactivate_options() {

			$reasons = array();
			$reasons = array(
							array(
									'id'                => 1,
									'text'              => __( 'This plugin is not working for me.' , 'smart-offers' ),
									'input_type'        => 'textarea',
									'input_placeholder' => __( 'Kindly share what did not work for you so we can fix it...', 'smart-offers' )
								),
							array(
									'id'                => 2,
									'text'              => __( 'This plugin is not compatible / conflicting with another plugin that I use.' , 'smart-offers' ),
									'input_type'        => 'textarea',
									'input_placeholder' => __( 'Kindly let us know with which other plugin it is the not compatible / conflicting with...', 'smart-offers' )
								),
							array(
									'id'                => 3,
									'text'              => __( 'The plugin is great, but I need specific feature that you don\'t support.' , 'smart-offers' ),
									'input_type'        => 'textarea',
									'input_placeholder' => __( 'Kindly let us know what specific feature you need.', 'smart-offers' )
								),
							array(
									'id'                => 4,
									'text'              => __( 'I found another plugin for my needs.' , 'smart-offers' ),
									'input_type'        => 'textfield',
									'input_placeholder' => __( 'Kindly let us know which is that other plugin.', 'smart-offers' )
								),
							array(
									'id'                => 5,
									'text'              => __( 'It is a temporary deactivation. I am just debugging an issue.' , 'smart-offers' ),
									'input_type'        => '',
									'input_placeholder' => ''
								),
							array(
									'id'                => 6,
									'text'              => __( 'Other' , 'smart-offers' ),
									'input_type'        => 'textarea',
									'input_placeholder' => __( 'Please mention...', 'smart-offers' )
								)
						);

			$uninstall_reasons['default'] = $reasons;

			return $uninstall_reasons;
		}

		/**
		 * get exact str against the slug
		 *
		 * @param type $slug
		 *
		 * @return type
		 */
		public static function load_str( $slug ) {
			return self::$so_deactivation_string[ $slug ];
		}

		/**
		 * Called after the user has submitted his reason for deactivating the plugin.
		 *
		 * @since 3.7.1
		 */
		public static function sa_submit_deactivation_reason_action() {
			if ( ! isset( $_POST[ 'reason_id' ] ) ) {
				exit;
			}

			$api_url = 'https://www.storeapps.org/wp-admin/admin-ajax.php';
			global $wpdb;

			// Plugin specific options should be added from here
			$so_post_type = 'smart_offers';
			$so_count_query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s GROUP BY post_status";
			$so_results = (array) $wpdb->get_results( $wpdb->prepare( $so_count_query, $so_post_type ), ARRAY_A );
			$so_counts = array();
			foreach ( $so_results as $row ) {
				$so_counts[ $row['post_status'] ] = $row['num_posts'];
			}

			if( !empty( $_POST ) ) {
				$plugin_data = $_POST;
				$plugin_data['so_count'] = $so_counts;
				$plugin_data['domain'] = home_url();
				$plugin_data['action'] = 'submit_survey';
			} else {
				exit();
			}

			$method = 'POST';
			$qs = http_build_query( $plugin_data );
			$options = array(
				'timeout' => 45,
				'method' => $method
			);
			if ( $method == 'POST' ) {
				$options['body'] = $qs;
			} else {
				if ( strpos( $api_url, '?' ) !== false ) {
					$api_url .= '&'.$qs;
				} else {
					$api_url .= '?'.$qs;
				}
			}

			$response = wp_remote_request( $api_url, $options );

			if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
				$data = json_decode( $response['body'], true );

				if ( empty( $data['error'] ) ) {
					if( !empty( $data ) && !empty( $data['success'] ) ) {
						echo 1;
					}
					echo ( json_encode( $data ) );
					exit();     
				}
			}
			// Print '1' for successful operation.
			echo 1;
			exit();
		}

	} // End of Class

}

new SO_Admin_Deactivation( SO_PLUGIN_BASE_NM, 'Smart Offers' );