( function( $ ) {
 
 	const table        = $( '#yikes-woo-saved-tabs-list-table' );
	const table_tbody  = $( '#yikes-woo-saved-tabs-list-tbody' );
	const table_row    = '.yikes_woo_saved_tabs_row';
	let default_order  = order();

	// Doc.ready.
	$( function() {

		// Init drag and drop.
		if ( typeof cptpro_list_table === 'object' && cptpro_list_table.ordering_enabled === '1' ) {

			// Control this with JS so we can easily add/remove depending on the ordering_enabled setting.
			$( table_row ).css( { 'cursor': 'grab' } );

			// Setup the tabs as drag-and-droppable.
			table_tbody.sortable({
				cursor: 'grabbing',
				stop: function( event, ui ) {

					// Update the data, get the tab order.
					const tabs = order();
					
					// Check if the tab order has changed.
					if ( JSON.stringify( default_order ) !== JSON.stringify( tabs ) ) {

						// Fire off the AJAX call.
						update_tab_order( tabs );	
					}

					// Update the current order.
					default_order = tabs;
				}
			});
		}
	});

	/**
	 * Update the tab's data-order element, create an object of { 'tab_id': 'tab_order' }
	 */
	function order() {
		const tabs = {};
		$( table_row ).each( function( index, element ) {
			const tab    = $( element );
			const order  = parseInt( index ) + 1;
			const tab_id = parseInt( tab.data( 'tab-id' ) );
			tab.data( 'order', order );
			tabs[ tab_id ] = order;
		});
		return tabs;
	}

	/**
	 * AJAX call to update the tab order.
	 */
	function update_tab_order( tabs ) {
		const data = {
			tabs: tabs,
			action: cptpro_list_table.reorder_tabs_action,
			nonce: cptpro_list_table.reorder_tabs_nonce
		};

		$.post( window.ajaxurl, data, function( response ) {
			console.log( response );
		});
	}

})( jQuery );