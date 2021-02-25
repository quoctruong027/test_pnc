<?php

if ( ! function_exists( 'bwf_get_remote_rest_args' ) ) {
	function bwf_get_remote_rest_args( $data = '', $method = 'POST' ) {
		return apply_filters( 'bwf_get_remote_rest_args', [
			'method'    => $method,
			'body'      => $data,
			'timeout'   => 0.01,
			'sslverify' => false,
		] );
	}
}