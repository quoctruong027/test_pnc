(function( $ ) {
	'use strict';

	$( document ).on( 'click', '.cc-welcome-notice .notice-dismiss', function() {
		cc_dismiss_welcome_notice();
	} );

	/* Dismiss welcome notice screen */
	function cc_dismiss_welcome_notice() {

		// AJAX Request to dismiss the welcome notice
		var data = {
			action: 'dismiss_welcome_notice',
			nonce: caddyAjaxObject.nonce,
		};

		$.ajax( {
			type: 'post',
			url: caddyAjaxObject.ajaxurl,
			data: data,
			success: function( response ) {
			}

		} );

	}

})( jQuery );
