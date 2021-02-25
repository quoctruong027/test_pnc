<?php

if ( $this->is_enable() ) {
	$order_id = XLWCTY_Compatibility::get_order_id( $order_data );

	/**
	 * Record Timestamp in order meta if user comes from the track link
	 */
	if ( isset( $_REQUEST['ts'] ) && 1 == $_REQUEST['ts'] ) {
		$timestamp  = current_time( 'timestamp', 1 );
		$timestamps = get_post_meta( $order_id, '_xlwcty_track_load', true );
		if ( is_array( $timestamps ) && ! empty( $timestamps ) ) {
			$timestamps[] = $timestamp;
		} else {
			$timestamps   = array();
			$timestamps[] = $timestamp;
		}
		update_post_meta( $order_id, '_xlwcty_track_load', $timestamps );
	}

	/**
	 * Show track timeline
	 */
	$html_to_show = $this->xlwcty_show_track_order_details_on_thankyoupage( $order_id );
	echo $html_to_show;
}
