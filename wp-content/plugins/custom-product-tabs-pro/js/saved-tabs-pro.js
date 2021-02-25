jQuery( document ).ready( function() {

	// Initialize our input fields
	cptpro_init_tag_autocomplete();

	// Add overlay to taxonomy section if tab is global
	cptpro_overlay_taxonomies();

	// Grab the products using this tab (in batches)
	get_num_products_using_this_tab( cptpro_admin_data.tab_id );

	jQuery( '#global-checkbox' ).click( cptpro_overlay_taxonomies );

	// Remove a tag when clicked
	jQuery( '.cptpro-dashicons-dismiss' ).click( function() {
		jQuery( this ).siblings( 'input[type="hidden"]' ).removeClass( 'selected' );
		jQuery( this ).parents( 'label' ).fadeOut();
	});

});

function cptpro_overlay_taxonomies() {
	if ( jQuery( '#global-checkbox').prop( 'checked' ) === true ) {
		jQuery( '.cptpro-taxonomies' ).css( { 'opacity': '.5', 'pointer-events': 'none' } );
	} else {
		jQuery( '.cptpro-taxonomies' ).css( { 'opacity': '1.0', 'pointer-events': 'initial' } );
	}
}

function cptpro_init_tag_autocomplete() {

	jQuery( '.taxonomy-label' ).each( function() {
		var taxonomy = jQuery( this ).data( 'taxonomy' );
		var terms    = []; 

		jQuery( 'input[name="' + taxonomy + '[]"]' ).each( function() {
			var obj = {};
			obj['label']      = jQuery( this ).data( 'term-name' );
			obj['value']      = jQuery( this ).data( 'term-name' );
			obj['unique_id']  = jQuery( this ).data( 'term-unique-id' );
			terms.push( obj );
		});

		jQuery( 'input.' + taxonomy ).autocomplete({ 
			source: terms,
			select: function( event, ui ) { 
				jQuery( 'label.' + ui.item.unique_id ).show();
				jQuery( 'label.' + ui.item.unique_id ).children( 'input[type="hidden"]' ).addClass( 'selected' );
				jQuery( this ).val( '' );
				return false;
			}
		});
	});	
}

function cptpro_show_products_using_this_tab( tab_id ) {

	var data = {
		'action': 'display_products_using_this_tab_ajax',
		'tab_id': tab_id,
		'nonce' : cptpro_admin_data.products_using_this_tab_nonce
	};

	jQuery.post( cptpro_admin_data.ajaxurl, data, function( response ) {
		jQuery( '.yikes_woo_saved_tab_products' ).replaceWith( response );
		get_num_products_using_this_tab( tab_id );
	});
}

function get_num_products_using_this_tab( tab_id ) {

	var data = {
		'action': 'get_num_products_using_this_tab',
		'tab_id': tab_id,
		'nonce' : cptpro_admin_data.num_products_using_this_tab_nonce
	};

	jQuery.post( cptpro_admin_data.ajaxurl, data, function( response ) {

		if ( typeof response !== 'undefined' && response.success === true ) {

			var num_products = parseInt( response.data.num_products );

			if ( num_products > 0 ) {

				// Always start with the first page.
				var paged = 1;
				
				while( num_products > 0 ) {
					
					cptpro_get_products_using_this_tab( tab_id, paged );

					// Decrease the number of products by 100.
					num_products = num_products - 100;

					// Increment the page number by 1
					paged += 1;
				}

			}
		}
	});
}

function cptpro_get_products_using_this_tab( tab_id, paged ) {

	var data = {
		'action': 'get_products_using_this_tab',
		'paged' : paged,
		'tab_id': tab_id,
		'nonce' : cptpro_admin_data.get_products_using_this_tab_nonce
	};

	jQuery.post( cptpro_admin_data.ajaxurl, data, function( response ) {
		jQuery( '.products-using-tab-ul' ).append( response );
	});
}