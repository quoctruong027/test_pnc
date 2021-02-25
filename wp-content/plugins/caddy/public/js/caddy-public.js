(function( $ ) {
	'use strict';

	var ccWindow = $( '.cc-window' );

	jQuery( document ).ready( function( $ ) {

		// Tab usability
		$( '.cc-nav ul li a' ).mousedown( function() {
			$( this ).addClass( 'using-mouse' );
		} );
		$( 'body' ).keydown( function() {
			$( '.cc-nav ul li a' ).removeClass( 'using-mouse' );
		} );

		// cc-window tabbing
		var tabs = new Tabby( '[data-tabs]' );
		var productAddedFlag = 'no';

		// Clicking outside of mini cart
		$( document ).mouseup( function( e ) {
			var container = $( '.cc-window.visible, .cc-compass' );

			// if the target of the click isn't the container nor a descendant of the container
			if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
				if ( ccWindow.hasClass( 'visible' ) ) {

					$( '.cc-compass' ).toggleClass( 'cc-compass-open' );
					$( 'body' ).toggleClass( 'cc-window-open' );

					$( '.cc-overlay' ).hide();
					ccWindow.animate( { 'right': '-1000px' }, 'slow' ).removeClass( 'visible' );
				}
			}
		} );

		// toggle .cc-window with .cc-compass
		$( document ).on( 'click', '.cc-compass', function() {

			$( this ).toggleClass( 'cc-compass-open' );
			$( 'body' ).toggleClass( 'cc-window-open' );

			if ( 'yes' != productAddedFlag ) {
				$( '.cc-pl-info-container' ).hide();
				$( '.cc-window-wrapper' ).show();
			}

			// Show or hide cc-window
			if ( ccWindow.hasClass( 'visible' ) ) {
				$( '.cc-overlay' ).hide();
				ccWindow.animate( { 'right': '-1000px' }, 'slow' ).removeClass( 'visible' );
			} else {
				$( '.cc-overlay' ).show();

				// Activate tabby cart tab
				tabs.toggle( '#cc-cart' );

				ccWindow.animate( { 'right': '0' }, 'slow' ).addClass( 'visible' );

			}

			productAddedFlag = 'no';

		} );

		// .cc-window close button
		$( document ).on( 'click', '.ccicon-x', function() {
			$( '.cc-overlay' ).hide();
			// Show or hide cc-window
			ccWindow.animate( { 'right': '-1000px' }, 'slow' ).removeClass( 'visible' );
			$( '.cc-compass' ).toggleClass( 'cc-compass-open' );
			$( 'body' ).toggleClass( 'cc-window-open' );
		} );

		// Remove cart item
		$( document ).on( 'click', '.cc-cart-product-list .cc-cart-product a.remove_from_cart_button', function() {
			var button = $( this );
			remove_item_from_cart( button );
		} );

		// Remove from save for later
		$( document ).on( 'click', 'a.remove_from_sfl_button', function() {
			var button = $( this );
			remove_item_from_save_for_later( button );
		} );

		$( 'body' ).on( 'added_to_cart', function( e, fragments, cart_hash, this_button ) {
			productAddedFlag = 'yes';
		} );

		// Custom add to cart functionality
		$( document ).on( 'click', '.single_add_to_cart_button, .add_to_cart_button', function( e ) {

			//If the button is disabled don't allow this to fire.
			if ( $( this ).hasClass( 'disabled' ) ) {
				return;
			}

			//If the product is not simple on the shop page.
			if ( $( this ).hasClass( 'product_type_variable' ) || $( this ).hasClass( 'product_type_bundle' ) ) {
				return;
			}

			var $button = $( this );
			if ( $( 'form.cart' ).length > 0 && !$( this ).hasClass( 'add_to_cart_button' ) ) {
				var $form = $button.closest( 'form.cart' ),
					product_id = $form.find( 'input[name=add-to-cart]' ).val() || $button.val();
			} else {
				var product_id = $( this ).data( 'product_id' );
			}

			if ( !product_id ) {
				return;
			}

			e.preventDefault();

			var data = {
				action: 'cc_add_to_cart',
				nonce: cc_ajax_script.nonce,
				'add-to-cart': product_id,
			};

			if ( $( 'form.cart' ).length > 0 && !$( this ).hasClass( 'add_to_cart_button' ) ) {
				$form.serializeArray().forEach( function( element ) {
					data[ element.name ] = element.value;
				} );
			} else {
				data[ 'quantity' ] = $( this ).data( 'quantity' );
			}

			$( document.body ).trigger( 'adding_to_cart', [$button, data] );

			$.ajax( {
				type: 'post',
				url: cc_ajax_script.ajaxurl,
				data: data,
				beforeSend: function( response ) {

					// Replace compass icon with loader icon
					$( '.cc-compass' ).find( '.licon' ).hide();
					$( '.cc-compass' ).find( '.cc-loader' ).show();

					if ( $( 'form.cart' ).length > 0 ) {
						$button.removeClass( 'added' ).addClass( 'loading' );
					}
				},
				complete: function( response ) {

					if ( $( 'form.cart' ).length > 0 ) {
						$button.addClass( 'added' ).removeClass( 'loading' );
					}
				},
				success: function( response ) {

					if ( response.error & response.product_url ) {

						window.location = response.product_url;
						return;

					} else {

						// Call to product added info screen
						product_added_screen( $form, data, $button );

						if ( !$button.hasClass( 'add_to_cart_button' ) ) {
							$( document.body ).trigger( 'added_to_cart', [response.fragments, response.cart_hash, $button] );
							$( '.woocommerce-notices-wrapper' ).empty().append( response.notices );
						}

					}
				},
			} );

			return false;

		} );

		// Product added view cart button
		$( document ).on( 'click', '.cc-pl-info .cc-pl-actions .cc-view-cart', function() {
			// Activate tabby cart tab
			tabs.toggle( '#cc-cart' );
		} );

		// Item quantity update
		$( document ).on( 'click', '.cc_item_quantity_update', function() {
			cc_quantity_update_buttons( $( this ) );
		} );

		// Save for later button
		$( document ).on( 'click', '.save_for_later_btn', function() {
			cc_save_for_later( $( this ) );
		} );

		// Move to cart button
		$( document ).on( 'click', '.cc_cart_from_sfl', function() {
			cc_move_to_cart( $( this ) );
		} );

		// Move to cart button
		$( document ).on( 'click', '.cc_back_to_cart', function() {
			cc_back_to_cart();
		} );

		// View cart button clicked
		$( document ).on( 'click', '.added_to_cart.wc-forward', function( e ) {
			e.preventDefault();
			cc_view_cart_button();
		} );

		// Saved items list button clicked
		$( document ).on( 'click', '.cc_saved_items_list', function() {
			cc_saved_item_list();
		} );

		// Cart items list button clicked
		$( document ).on( 'click', '.cc_cart_items_list', function() {
			cc_cart_item_list();
		} );

		// Add product to save for later directly
		$( document ).on( 'click', '.cc_add_product_to_sfl', function() {
			cc_add_product_to_sfl( $( this ) );
		} );

		// Clicks on a view saved items
		$( document ).on( 'click', '.cc-view-saved-items', function() {

			// Activate tabby saves tab
			var tabs = new Tabby( '[data-tabs]' );
			tabs.toggle( '#cc-saves' );

		} );

		if ( $( '.variations_form' ).length > 0 ) {

			$( '.cc_add_product_to_sfl' ).addClass( 'disabled' );
			$( this ).each( function() {

				// when variation is found, do something
				$( this ).on( 'found_variation', function( event, variation ) {
					$( '.cc_add_product_to_sfl' ).removeClass( 'disabled' );
				} );

				$( this ).on( 'reset_data', function() {
					$( '.cc_add_product_to_sfl' ).addClass( 'disabled' );
				} );

			} );

		}

		$( document ).on( 'submit', '#apply_coupon_form', function( e ) {
			e.preventDefault();
			cc_coupon_code_applied_from_cart_screen();
		} );

		$( document ).on( 'click', '.cc-applied-coupon .cc-remove-coupon', function() {
			cc_coupon_code_removed_from_cart_screen( $( this ) );
		} );

		if ( 1 == $( '.cc-window' ).length ) {
			cc_load_window_content();
		}

	} ); // end ready

	/* Load cart screen */
	function cc_cart_screen( productAdded = '' ) {

		// AJAX Request for window data
		var data = {
			action: 'cc_update_window_data',
		};
		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {
				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-cart' );

				if ( 'yes' == productAdded ) {
					$( '.cc-window-wrapper' ).hide();
				}

				if ( 'move_to_cart' === productAdded ) {
					$( '.cc_cart_from_sfl' ).removeClass( 'cc_hide_btn' );
					$( '.cc_cart_from_sfl' ).parent().find( '.cc-loader' ).hide();
				}

			}
		} );

	}

	/* Load product added screen */
	function product_added_screen( $form, inputdata, $button ) {

		var product_id = 0,
			final_quantity = 1,
			formData = {};

		if ( $form ) {
			product_id = $form.find( 'input[name=add-to-cart]' ).val() || $button.val();
			$form.serializeArray().forEach( function( element ) {
				formData[ element.name ] = element.value;
			} );
			final_quantity = formData.quantity;
		} else {
			product_id = inputdata[ 'add-to-cart' ];
			final_quantity = inputdata[ 'quantity' ];
		}

		var data = {
			action: 'cc_product_added_info',
			security: cc_ajax_script.nonce,
			product_id: product_id,
			pro_quantity: final_quantity
		};

		if ( $form ) {
			data[ 'variation_id' ] = formData.variation_id;
		}

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			complete: function( response ) {

				// Replace loader icon with compass close icon
				$( '.cc-compass' ).find( '.cc-loader' ).hide();
				$( '.cc-compass' ).find( '.licon' ).show();

			},
			success: function( response ) {

				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				if ( $( '.cc-pl-info-container' ).is( ':hidden' ) ) {
					$( '.cc-pl-info-container' ).show();
				}

				// Trigger cc-compass event
				if ( !ccWindow.hasClass( 'visible' ) ) {
					$( '.cc-compass' ).trigger( 'click' );
				}
				var productAdded = 'yes';
				cc_cart_screen( productAdded );

			},
		} );

	}

	var cc_quanity_update_send = true;

	/* Quantity update in cart screen */
	function cc_quantity_update_buttons( el ) {
		if ( cc_quanity_update_send ) {
			cc_quanity_update_send = false;
			var wrap = $( el ).parents( '.cc-cart-product-list' );
			var input = $( wrap ).find( '.cc_item_quantity' );
			var key = $( input ).data( 'key' );
			var number = parseInt( $( input ).val() );
			var type = $( el ).data( 'type' );
			if ( 'minus' == type ) {
				number --;
			} else {
				number ++;
			}
			if ( number < 1 ) {
				number = 1;
			}
			$( input ).val( number );
			var data = {
				action: 'cc_quantity_update',
				key: key,
				number: number,
				security: cc_ajax_script.nonce
			};

			$.ajax( {
				type: 'post',
				url: cc_ajax_script.ajaxurl,
				data: data,
				success: function( response ) {

					var fragments = response.fragments;
					// Replace fragments
					if ( fragments ) {
						$.each( fragments, function( key, value ) {
							$( key ).replaceWith( value );
						} );
					}

					cc_quanity_update_send = true;

					// Activate tabby cart tab
					var tabs = new Tabby( '[data-tabs]' );
					tabs.toggle( '#cc-cart' );

				}
			} );

		}
	}

	/* Move to save for later */
	function cc_save_for_later( $button ) {
		var product_id = $button.data( 'product_id' );
		var cart_item_key = $button.data( 'cart_item_key' );

		// AJAX Request for add item to wishlist
		var data = {
			action: 'cc_save_for_later',
			security: cc_ajax_script.nonce,
			product_id: product_id,
			cart_item_key: cart_item_key
		};

		$.ajax( {
			type: 'post',
			dataType: 'json',
			url: cc_ajax_script.ajaxurl,
			data: data,
			beforeSend: function( response ) {
				$button.addClass( 'cc_hide_btn' );
				$button.parent().find( '.cc-loader' ).show();
			},
			complete: function( response ) {
				$button.removeClass( 'cc_hide_btn' );
				$button.parent().find( '.cc-loader' ).hide();
			},
			success: function( response ) {
				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Activate tabby saves tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-saves' );

			}
		} );

	}

	/* Move to cart from save for later */
	function cc_move_to_cart( $button ) {
		var product_id = $button.data( 'product_id' );

		// AJAX Request for add item to cart from wishlist
		var data = {
			action: 'cc_move_to_cart',
			security: cc_ajax_script.nonce,
			product_id: product_id,
		};

		$.ajax( {
			type: 'post',
			dataType: 'json',
			url: cc_ajax_script.ajaxurl,
			data: data,
			beforeSend: function( response ) {
				$button.addClass( 'cc_hide_btn' );
				$button.parent().find( '.cc-loader' ).show();
			},
			success: function( response ) {
				cc_cart_screen( 'move_to_cart' );
			}
		} );

	}

	/* Remove item from the cart */
	function remove_item_from_cart( button ) {

		var cartItemKey = button.data( 'cart_item_key' ),
			productName = button.data( 'product_name' );

		// AJAX Request for remove product from the cart
		var data = {
			action: 'cc_remove_item_from_cart',
			nonce: cc_ajax_script.nonce,
			cart_item_key: cartItemKey
		};

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {

				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-cart' );

			}
		} );

	}

	/* Remove item from save for later */
	function remove_item_from_save_for_later( button ) {

		var productID = button.data( 'product_id' );

		// AJAX Request for remove product from the cart
		var data = {
			action: 'cc_remove_item_from_sfl',
			nonce: cc_ajax_script.nonce,
			product_id: productID
		};

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {
				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Change to empty heart icon after removing the product
				if ( $( button ).has( 'i.ccicon-heart-filled' ) ) {
					$( button ).find( 'i' ).removeClass( 'ccicon-heart-filled' ).addClass( 'ccicon-heart-empty' );
					var sfl_btn_text = $( button ).find( 'span' ).text();
					if ( sfl_btn_text.length > 0 ) {
						$( button ).find( 'span' ).text( 'Save for later' );
					}
					$( button ).removeClass( 'remove_from_sfl_button' ).addClass( 'cc_add_product_to_sfl' );
				}

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-saves' );

			}

		} );

	}

	/* Back to cart link */
	function cc_back_to_cart() {
		$( '.cc-pl-info-container' ).hide();
		$( '.cc-window-wrapper' ).show();
	}

	/* View cart button clicked */
	function cc_view_cart_button() {
		if ( !ccWindow.hasClass( 'visible' ) ) {
			$( '.cc-compass' ).trigger( 'click' );
		}
	}

	/* Saved item list button clicked */
	function cc_saved_item_list() {

		$( '.cc-compass' ).toggleClass( 'cc-compass-open' );
		$( 'body' ).toggleClass( 'cc-window-open' );

		$( '.cc-pl-info-container' ).hide();
		$( '.cc-window-wrapper' ).show();

		// Show or hide cc-window
		$( '.cc-overlay' ).show();

		// Activate tabby saves tab
		var tabs = new Tabby( '[data-tabs]' );
		tabs.toggle( '#cc-saves' );

		ccWindow.animate( { 'right': '0' }, 'slow' ).addClass( 'visible' );
	}

	/* Cart item list button clicked */
	function cc_cart_item_list() {
		if ( !ccWindow.hasClass( 'visible' ) ) {
			$( '.cc-compass' ).trigger( 'click' );
		}
	}

	/* Add product to save for later directly */
	function cc_add_product_to_sfl( $button ) {
		var product_id = $button.data( 'product_id' ),
			product_type = $button.data( 'product_type' );

		if ( 'variable' == product_type ) {
			product_id = $button.parent().find( '.variation_id' ).val();
		}

		if ( 0 !== product_id && (!$button.hasClass( 'disabled' )) ) {
			// AJAX Request for add product to save for later
			var data = {
				action: 'add_product_to_sfl_action',
				nonce: cc_ajax_script.nonce,
				product_id: product_id
			};

			$.ajax( {
				type: 'post',
				url: cc_ajax_script.ajaxurl,
				data: data,
				success: function( response ) {
					var fragments = response.fragments;
					// Replace fragments
					if ( fragments ) {
						$.each( fragments, function( key, value ) {
							$( key ).replaceWith( value );
						} );
					}

					// Change to filled heart icon after saving the product
					if ( $( $button ).has( 'i.ccicon-heart-empty' ) ) {
						$( $button ).find( 'i' ).removeClass( 'ccicon-heart-empty' ).addClass( 'ccicon-heart-filled' );
						var sfl_btn_text = $( $button ).find( 'span' ).text();
						if ( sfl_btn_text.length > 0 ) {
							$( $button ).find( 'span' ).text( 'Saved' );
						}
						$( $button ).removeClass( 'cc_add_product_to_sfl' ).addClass( 'remove_from_sfl_button' );
					}

					cc_saved_item_list();
				}

			} );
		}

	}

	/* Apply coupon code from the cart screen */
	function cc_coupon_code_applied_from_cart_screen() {

		var coupon_code = $( '.cc-coupon-form #cc_coupon_code' ).val();

		// AJAX Request to apply coupon code to the cart
		var data = {
			action: 'cc_apply_coupon_to_cart',
			nonce: cc_ajax_script.nonce,
			coupon_code: coupon_code
		};

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {
				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-cart' );

			}

		} );

	}

	/* Remove coupon code from the cart screen */
	function cc_coupon_code_removed_from_cart_screen( $remove_code ) {

		var coupon_code_to_remove = $remove_code.parent( '.cc-applied-coupon' ).find( '.cc_applied_code' ).text();

		// AJAX Request to apply coupon code to the cart
		var data = {
			action: 'cc_remove_coupon_code',
			nonce: cc_ajax_script.nonce,
			coupon_code_to_remove: coupon_code_to_remove
		};

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {
				var fragments = response.fragments,
					fs_remaining_amount = response.fs_remaining_amount,
					fs_bar_amount = response.fs_bar_amount;

				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				$( '.cc-fs-amount' ).text( ' ' + cc_ajax_script.wc_currency_symbol + fs_remaining_amount );
				$( '.cc-fs-meter-used' ).css( 'width', fs_bar_amount + '%' );

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-cart' );

			}

		} );

	}

	/* Load window content when page loads */
	function cc_load_window_content() {

		// AJAX Request to load window content
		var data = {
			action: 'cc_load_window_content',
			nonce: cc_ajax_script.nonce,
		};

		$.ajax( {
			type: 'post',
			url: cc_ajax_script.ajaxurl,
			data: data,
			success: function( response ) {

				var fragments = response.fragments;
				// Replace fragments
				if ( fragments ) {
					$.each( fragments, function( key, value ) {
						$( key ).replaceWith( value );
					} );
				}

				// Activate tabby cart tab
				var tabs = new Tabby( '[data-tabs]' );
				tabs.toggle( '#cc-cart' );

			}

		} );

	}

})( jQuery );