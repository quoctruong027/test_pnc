<?php

if ( isset( $_GET['data'] ) && 1 === absint( $_GET['data'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
	$automation = isset( $_GET['automation'] ) ? sanitize_text_field( $_GET['automation'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification

	if ( empty( $automation ) ) {
		$json = wp_json_encode( array() );
	}

	if ( 'all' === $automation ) {
		global $wpdb;
		$automation_table = $wpdb->prefix . 'bwfan_automations';
		$all_automations  = $wpdb->get_results( "SELECT ID FROM $automation_table", ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $all_automations ) ) {
			return;
		}

		$automations_data = array();
		foreach ( $all_automations as $key => $auto ) {
			$automation_meta                  = BWFAN_Core()->automations->get_automation_data_meta( $auto['ID'] );
			$automations_data[ $key ]['data'] = array(
				'source' => $automation_meta['source'],
				'event'  => $automation_meta['event'],
			);
			$automations_data[ $key ]['meta'] = array(
				'title'           => isset( $automation_meta['title'] ) ? $automation_meta['title'] : '',
				'event_meta'      => isset( $automation_meta['event_meta'] ) ? $automation_meta['event_meta'] : '',
				'actions'         => isset( $automation_meta['actions'] ) ? $automation_meta['actions'] : '',
				'a_track_id'      => 0,
				'condition'       => isset( $automation_meta['condition'] ) ? $automation_meta['condition'] : '',
				'run_count'       => 0,
				'ui'              => isset( $automation_meta['ui'] ) && ! empty( $automation_meta['ui'] ) ? $automation_meta['ui'] : '',
				'requires_update' => isset( $automation_meta['requires_update'] ) ? $automation_meta['requires_update'] : '',
				'uiData'          => isset( $automation_meta['uiData'] ) ? $automation_meta['uiData'] : '',
			);
		}

		$json = wp_json_encode( $automations_data );
	} else { // getting single automation id
		$automation_meta            = BWFAN_Core()->automations->get_automation_data_meta( $automation );
		$json_data_array            = array(
			'data' => array(),
			'meta' => array(),
		);
		$json_data_array[0]['data'] = array(
			'source' => $automation_meta['source'],
			'event'  => $automation_meta['event'],
		);
		$json_data_array[0]['meta'] = array(
			'title'           => isset( $automation_meta['title'] ) ? $automation_meta['title'] : '',
			'event_meta'      => isset( $automation_meta['event_meta'] ) ? $automation_meta['event_meta'] : '',
			'actions'         => isset( $automation_meta['actions'] ) ? $automation_meta['actions'] : '',
			'a_track_id'      => 0,
			'condition'       => isset( $automation_meta['condition'] ) ? $automation_meta['condition'] : '',
			'run_count'       => 0,
			'ui'              => isset( $automation_meta['ui'] ) ? $automation_meta['ui'] : '',
			'requires_update' => isset( $automation_meta['requires_update'] ) ? $automation_meta['requires_update'] : '',
			'uiData'          => isset( $automation_meta['uiData'] ) ? $automation_meta['uiData'] : '',
		);

		$json = wp_json_encode( $json_data_array );
	}

	header( 'Content-disposition: attachment; filename=autonami-automations.json' );
	header( 'Content-type: application/json' );

	ob_clean();

	echo $json;

	exit;
}
?>
<div id="poststuff">
    <div class="inside">
        <div class="bwfan_highlight_center">
            <div class="bwfan_heading"><?php esc_html_e( 'Export all Automations to a JSON file', 'wp-marketing-automations' ); ?></div>
            <div class="bwfan_clear_20"></div>
            <div class="bwfan_content">
                <p><?php esc_html_e( 'This tool allows you to generate and download a JSON file containing all automations.', 'wp-marketing-automations' ); ?></p>
            </div>
            <div class="bwfan_clear_30"></div>
            <div class="bwfan-export-button">
                <button class="bwfan_btn_blue_big export-automation" id="bwfan-exp-aut"><?php esc_html_e( 'Export', 'wp-marketing-automations' ); ?></button>
            </div>
        </div>
    </div>
</div>
