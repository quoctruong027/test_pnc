( function( $ ) {
	$( document ).ready( function() {

		// Run DOM-manipulation functions on page-load
		cptpro_check_license();
		disable_elements();
		maybe_show_tab_ordering_options();

		$( '#cptpro-settings-save' ).click( cptpro_settings_save );

		$( '#cptpro-license-activate' ).click( cptpro_activate_license );

		$( '#cptpro-license-deactivate' ).click( cptpro_deactivate_license );

		$( '#disable-description, #disable-additional-information, #disable-reviews' ).on( 'ifChanged', function() {
			const element = $( this );
			toggle_order_visibility( element.prop( 'checked' ), element.data( 'tab' ) );
		});

		$( '#enable-ordering' ).on( 'ifChanged', function() {
			maybe_show_tab_ordering_options();
		});
	});


	function cptpro_settings_save() {

		// Show the settings spinner gif
		$( '.settings-spinner-gif' ).show();
		$( '.yikes-custom-notice-success, .yikes-custom-notice-failure' ).fadeOut();

		// Hide Tab Title
		const hide_tab_title                 = $( '#hide-tab-title' ).prop( 'checked' );
		const search_wordpress               = $( '#search-wordpress' ).prop( 'checked' );
		const search_woo                     = $( '#search-woo' ).prop( 'checked' );
		const enable_ordering                = $( '#enable-ordering' ).prop( 'checked' );
		const description_order              = $( '#description-order' ).val();
		const additional_information_order   = $( '#additional-information-order' ).val();
		const reviews_order                  = $( '#reviews-order' ).val();
		const disable_description            = $( '#disable-description' ).prop( 'checked' );
		const disable_additional_information = $( '#disable-additional-information' ).prop( 'checked' );
		const disable_reviews                = $( '#disable-reviews' ).prop( 'checked' );
		const disable_sslverify              = $( '#disable-sslverify' ).prop( 'checked' );
		const disable_the_content            = $( '#disable-the-content' ).prop( 'checked' ) ? 'true' : 'false';

		const data = {
			action                        : settings_data.save_settings_action,
			hide_tab_title                : hide_tab_title,
			search_wordpress              : search_wordpress,
			search_woo                    : search_woo,
			enable_ordering               : enable_ordering,
			description_order             : description_order,
			additional_information_order  : additional_information_order,
			reviews_order                 : reviews_order,
			disable_description           : disable_description,
			disable_additional_information: disable_additional_information,
			disable_reviews               : disable_reviews,
			disable_sslverify             : disable_sslverify,
			disable_the_content           : disable_the_content,
			nonce                         : settings_data.save_settings_nonce
		};

		$.post( window.ajaxurl, data, function( response ) {
			if ( typeof response === 'object' && response.success === true ) {
				$( '.settings-spinner-gif' ).fadeOut( 'fast', function() { $( '.yikes-custom-notice-success' ).fadeIn(); });
			} else {
				$( '.settings-spinner-gif' ).fadeOut( 'fast', function() { $( '.yikes-custom-notice-failure' ).fadeIn(); });
			}
		});
	}

	function cptpro_check_license() {
		
		const license = $( '#cptpro-license' ).val();

		if ( license.length === 0 ) {
			return;
		}

		// Show spinner gif
		cptpro_license_load_show_spinner_gif();

		const data = {
			license: license,
			action: settings_data.check_license_action,
			nonce: settings_data.check_license_nonce
		};

		$.post( window.ajaxurl, data, function( response ) {
			if ( typeof response.success !== 'undefined' && response.success === true ) {
				cptpro_handle_active_license( response.data );
			} else {
				cptpro_license_load_hide_spinner_gif_failure();
				if ( typeof response.data !== 'undefined' ) {
					alert( response.data );
				}
			}
		});

	}

	function cptpro_activate_license() {

		const license = $( '#cptpro-license' ).val();

		remove_license_feedback_messages();

		if ( license.length === 0 ) {
			return;
		}

		// Show spinner gif
		cptpro_license_load_show_spinner_gif();

		const data = {
			license: license,
			action: settings_data.activate_license_action,
			nonce: settings_data.activate_license_nonce
		};

		$.post( window.ajaxurl, data, function( response ) {
			if ( typeof response.success !== 'undefined' && response.success === true ) {
				$( '.yikes-custom-notice-license-success' ).fadeIn();
				cptpro_handle_active_license( response.data );
			} else {
				$( '.yikes-custom-notice-license-failure' ).fadeIn();
				cptpro_handle_deactivate_license();
			}
		});
	}

	function cptpro_handle_active_license( license_data ) {

		// Show the thumbs up
		$( '.license-spinner-gif' ).fadeOut( 'slow', function() {
			$( '.license-active' ).fadeIn();
		});

		// Show the "Deactivate" license button
		$( '#cptpro-license-activate' ).fadeOut( 'slow', function() {
			$( '#cptpro-license-deactivate' ).fadeIn();
		});

		// Add our customer license details data to the HTML
		const customer_name  = license_data.customer_name;
		const customer_email = license_data.customer_email;
		const expiration     = license_data.expires;
		const license_limit  = license_data.license_limit;

		$( '.cptpro-license-customer-value' ).text( customer_name + ' / ' + customer_email );
		$( '.cptpro-license-expires-value' ).text( expiration );
		$( '.cptpro-license-limit-value' ).text( license_limit );

		// Show the customer license details section
		$( '.cptpro-license-details' ).fadeIn();

	}

	function cptpro_deactivate_license() {

		const license = $( '#cptpro-license' ).val();

		remove_license_feedback_messages();

		if ( license.length === 0 ) {
			return;
		}

		// Show spinner gif
		cptpro_license_load_show_spinner_gif();

		const data = {
			license: license,
			action: settings_data.deactivate_license_action,
			nonce: settings_data.deactivate_license_nonce
		};

		$.post( window.ajaxurl, data, function( response ) {
			if ( typeof response.success !== 'undefined' && response.success === true ) {
				cptpro_handle_deactivate_license();
			} else {
				cptpro_license_load_hide_spinner_gif_failure();
				if ( typeof response.data !== 'undefined' ) {
					alert( response.data );
				}
			}
		});
	}

	function maybe_show_tab_ordering_options() {
		if ( $( '#enable-ordering' ).prop( 'checked' ) ) {
			$( '#cptpro-ordering-subfields' ).fadeIn();
		} else {
			$( '#cptpro-ordering-subfields' ).fadeOut();
		}
	}

	function disable_elements() {
		$( '#disable-description, #disable-additional-information, #disable-reviews' ).each( function() {
			const element = $( this );
			toggle_order_visibility( element.prop( 'checked' ), element.data( 'tab' ) );
		});
	}

	function toggle_order_visibility( checked, tab ) {
		if ( checked === true ) {
			$( '.field-' + tab + '-order' ).addClass( 'cptpro-faded' );
		} else {
			$( '.field-' + tab + '-order' ).removeClass( 'cptpro-faded' );
		}
	}

	function cptpro_handle_deactivate_license() {

		// Hide the customer license details section
		$( '.cptpro-license-details' ).fadeOut();

		// Show the thumbs down
		$( '.license-spinner-gif' ).fadeOut( 'slow', function() {
			$( '.license-inactive' ).fadeIn();
		});

		// Show the "Activate" license button
		$( '#cptpro-license-deactivate' ).fadeOut( 'slow', function() {
			$( '#cptpro-license-activate' ).fadeIn();
		});
	}

	function remove_license_feedback_messages() {
		// Remove success/error messages.
		$( '.yikes-custom-notice-license-success, .yikes-custom-notice-license-failure' ).fadeOut();
	}

	function cptpro_license_load_show_spinner_gif() {
		$( '.license-active, .license-inactive' ).hide();
		$( '.license-spinner-gif' ).show();
	}

	function cptpro_license_load_hide_spinner_gif_failure() {
		$( '.license-inactive' ).show();
		$( '.license-spinner-gif' ).hide();
	}
})( jQuery );