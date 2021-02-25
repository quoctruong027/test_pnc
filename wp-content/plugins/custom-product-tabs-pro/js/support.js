jQuery( document ).ready( function() {

	jQuery( '#cptpro-support-request' ).click( cptpro_send_support_request );

});

function cptpro_send_support_request() {

	var name  = jQuery( '#cptpro-name' ).val();
	var email = jQuery( '#cptpro-email' ).val();
	var topic = jQuery( '#cptpro-topic' ).val();
	var issue = tinymce.get( 'cptpro_issue' ).getContent();

	var data = { 
		"name": name,
		"email": email,
		"topic": topic,
		"issue": issue
	};

	if ( cptpro_handle_empty( data ) === false ) {
		return false;
	}

	data['priority'] = jQuery( 'input[name="cptpro-priority"]:checked' ).val();
	data['license']  = jQuery( '#cptpro-license' ).val();
	data['nonce']    = support_data.nonce;
	data['action']   = support_data.action;

	jQuery.post( support_data.ajax_url, data, function( response ) {console.log( response );

		if ( typeof response.success !== 'undefined' && response.success === true ) {

			if ( typeof response.data === 'object' && typeof response.data.redirect_url === 'string' ) {

				window.location.replace( response.data.redirect_url );
			}
		} else {

			if ( typeof response.data === 'object' && typeof response.data.message === 'string' ) {

				alert( response.data.message );
			} else {

				alert( 'Something went wrong. Please email us directly at support@yikesinc.com.' );
			}
		}

	});
}

function cptpro_handle_empty( data ) {

	var return_val = true;

	jQuery( '.cptpro-error-icons, .yikes-support-notice-failure, .yikes-custom-notice-success' ).hide();

	jQuery.each( data, function( label, value ) {
		if ( value.length === 0 ) {
			jQuery( '.cptpro-' + label + '-error' ).fadeIn();
			jQuery( '.yikes-custom-notice-failure' ).fadeIn();
			return_val = false;
		}
	});

	return return_val;
}