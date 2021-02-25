<?php
if ( ! function_exists( 'wfocu_rest_update_missing_gateways' ) ) {
	function wfocu_rest_update_missing_gateways() {
		global $wpdb;
		/**
		 * get all the orders which still do not have payment_gateway updated.
		 */
		$query_select = $wpdb->prepare( "SELECT order_id from `" . $wpdb->prefix . "wfocu_session` WHERE `gateway` = %s", '' );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.

		$orders = $wpdb->get_results( $query_select, ARRAY_A );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.

		if ( $orders && is_array( $orders ) && count( $orders ) > 0 ) {

			$get_unique_orders = array_unique( wp_list_pluck( $orders, 'order_id' ) );
			$post__in          = implode( ',', $get_unique_orders );
			/**
			 * get payment_gateways for existing orders
			 */
			$query_select_2 = $wpdb->prepare( "SELECT meta_value,post_id from `" . $wpdb->prefix . "postmeta` WHERE `meta_key` = %s AND `post_id` IN ($post__in)", '_payment_method' );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.
			$results        = $wpdb->get_results( $query_select_2, ARRAY_A );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.
			if ( is_array( $results ) && count( $results ) > 0 ) {
				foreach ( $results as $result ) {
					$order_id = $result['post_id'];
					$gateway  = $result['meta_value'];
					/**
					 * Update session table for rest of the orders
					 */
					$query = $wpdb->prepare( "UPDATE`" . $wpdb->prefix . "wfocu_session` SET `gateway` = %s WHERE `order_id` = %s", $gateway, $order_id );


					$wpdb->query( $query );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.
				}
			}


		}
	}
}


if ( ! function_exists( 'wfocu_maybe_update_sessions_on_2_0' ) ) {


	/**
	 * @hooked `shutdown`
	 * As we moved to DB version 1.0 with the new table structure.
	 * We need to ensure the data for the upsell gross amount get stored in the order meta.
	 *
	 */
	function wfocu_maybe_update_sessions_on_2_0() {


		global $wpdb;

		/**
		 * Fetch payment method and creation time for the order where upsell got accepted.
		 */
		$query = $wpdb->prepare( "SELECT b.meta_value as 'payment_method', a.meta_value as 'total', a.post_id as 'order_id', c.post_date as 'created_date'
FROM `" . $wpdb->prefix . "postmeta` a 
INNER JOIN `" . $wpdb->prefix . "postmeta` b 
INNER JOIN `" . $wpdb->prefix . "posts` c 
ON a.post_id = b.post_id AND a.post_id = c.ID
WHERE a.`meta_key` LIKE %s AND a.`meta_value` != '' AND b.meta_key LIKE %s  
ORDER BY `order_id` ASC", '_wfocu_upsell_amount', '_payment_method' );

		$results = $wpdb->get_results( $query, ARRAY_A );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.

		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				$order_id   = $result['order_id'];
				$upsell_val = $result['total'];
				$gateway    = $result['payment_method'];
				$timestamp  = $result['created_date'];

				/**
				 * Update session table with the data
				 */
				$query = $wpdb->prepare( "UPDATE`" . $wpdb->prefix . "wfocu_session` SET `gateway` = %s,`timestamp` = %s, `total` = %s WHERE `order_id` = %s", $gateway, $timestamp, $upsell_val, $order_id );

				$wpdb->query( $query );  //db call ok; no-cache ok; WPCS: unprepared SQL ok.
			}
		}
	}
}

if ( ! function_exists( 'wfocu_update_fullwidth_page_template' ) ) {
	function wfocu_update_fullwidth_page_template() {
		$args   = array(
			'post_type'        => WFOCU_Common::get_offer_post_type_slug(),
			'posts_per_page'   => - 1,
			'fields'           => 'ids',
			'suppress_filters' => false
		);
		$offers = get_posts( $args ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts

		foreach ( $offers as $offer_id ) {
			$wfocu_settings = get_post_meta( $offer_id, '_wfocu_setting', true );
			if ( ! empty( $wfocu_settings ) && isset( $wfocu_settings->template_group ) && 'elementor' === $wfocu_settings->template_group ) {
				update_post_meta( $offer_id, '_wp_page_template', 'wfocu-canvas.php' );
			}
		}
	}
}

if ( ! function_exists( 'wfocu_update_general_setting_fields' ) ) {

	function wfocu_update_general_setting_fields() {

		$db_setting     = array(
			'fb_pixel_key'            => 'fb_pixel_key',
			'ga_key'                  => 'ga_key',
			'gad_key'                 => 'gad_key',
			'gad_conversion_label'    => 'gad_conversion_label',
			'is_fb_purchase_event'    => 'is_fb_purchase_event',
			'is_fb_synced_event'      => 'is_fb_synced_event',
			'is_fb_advanced_event'    => 'is_fb_advanced_event',
			'content_id_value'        => 'content_id_value',
			'content_id_variable'     => 'content_id_variable',
			'content_id_prefix'       => 'content_id_prefix',
			'content_id_suffix'       => 'content_id_suffix',
			'track_traffic_source'    => 'track_traffic_source',
			'exclude_from_total'      => 'exclude_from_total',
			'enable_general_event'    => 'enable_general_event',
			'general_event_name'      => 'GeneralEvent',
			'custom_aud_opt_conf'     => 'custom_aud_opt_conf',
			'is_ga_purchase_event'    => 'is_ga_purchase_event',
			'is_gad_purchase_event'   => 'is_gad_purchase_event',
			'ga_track_traffic_source' => 'ga_track_traffic_source',
			'gad_exclude_from_total'  => 'gad_exclude_from_total',
			'id_prefix_gad'           => 'id_prefix_gad',
			'id_suffix_gad'           => 'id_suffix_gad',
			'offer_post_type_slug'  =>  'wfocu_page_base',
		);

		$db_setting = apply_filters( 'wfocu_migrate_general_setting_field', $db_setting );

		$global_op   = get_option( 'wfocu_global_settings', [] );
		$general_op  = get_option( 'bwf_gen_config', [] );

		foreach ( $db_setting as $key => $value ){
			if( isset( $global_op[ $key ] ) && ( ! isset( $general_op[ $value ] ) || empty( $general_op[ $value ] ) ) ) {
				$general_op[ $value ] = $global_op[ $key ];

			}
		}

		update_option( 'bwf_gen_config', $general_op, true );
	}

}


if ( ! function_exists( 'wfocu_migrate_public_images' ) ) {
	function wfocu_migrate_public_images() {
		$files  = array(
			array(
				'base'    => WFOCU_CONTENT_ASSETS_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => WFOCU_CONTENT_ASSETS_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => WFOCU_CONTENT_ASSETS_DIR . '/admin/assets/img/',
				'file'    => 'index.html',
				'content' => '',
			),
		);
		$status = false;
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'wb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
				$status = true;
			}
		}

		if ( false !== $status ) {
			wfocu_recurse_copy();
		}
	}

	function wfocu_recurse_copy() {
		$img_path        = WFOCU_PLUGIN_DIR . '/admin/assets/img/';
		$cont_img_path   = WFOCU_CONTENT_ASSETS_DIR . '/admin/assets/img/';

		$images = array(
			$img_path . 'no_image.jpg'  => $cont_img_path . 'no_image.jpg',
		);

		foreach ( $images as $src => $dst ) {

			if ( file_exists( $src ) ) {
				copy( $src, $dst );
			}
		}

	}

}
