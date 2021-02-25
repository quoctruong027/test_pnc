(function(){
	'use strict';
	jQuery(document).ready(function(){

		jQuery("form.variations_form div.images").addClass("so_product_image");
		jQuery("div.images").addClass("so_product_image");

		function hide_add_to_cart( form_id ) {
			if( form_id != "" ) {
				var str_length = ("so_addtocart_").length;
				var offer_id = form_id.substr(str_length);

				setTimeout(function() {
					var product_id = jQuery("form#"+form_id).attr("data-product_id");
					var variation_id = jQuery("form#"+form_id + " input[name=variation_id]").val();
					var all_variations = jQuery("form#"+form_id).data( "product_variations" );
					var form_qty_id = "so_qty_"+ offer_id;

					if( variation_id ) {
						// Fallback to window property if not set - backwards compat
						if ( ! all_variations )
								all_variations = window[ "product_variations_" + product_id ];

						jQuery.each(all_variations, function(i, obj) {

							jQuery("div#so-entry-content-"+ offer_id + " div.so_skip").show();
							jQuery("div#so-entry-content-"+ offer_id + " div.so_skip a[href*='so_action=skip']").show();
							jQuery("div#so-entry-content-"+ offer_id + " .so-order-bump-checkbox,div#so-entry-content-"+ offer_id + " button.so-order-bump-cta").removeAttr('disabled');
							if( obj.variation_id == variation_id ){

								jQuery("div#so-entry-content-"+ offer_id + " div.so_product_image a.woocommerce-main-image img").attr( "src", obj.image_src );

								if ( ! obj.is_in_stock && ! obj.backorders_allowed ) {
										jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").hide();
										jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*='so_action=accept']").hide();
										jQuery("div#so-entry-content-"+ offer_id + " .so-order-bump-checkbox:not(:checked),div#so-entry-content-"+ offer_id + " button.so-order-bump-cta").attr('disabled',true); // Disable only not checked checkbox so that user will be able to uncheck order bump in case of variation.
								} else {
									jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").show();
									jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*='so_action=accept']").show();
									jQuery("div#so-entry-content-"+ offer_id + " .so-order-bump-checkbox,div#so-entry-content-"+ offer_id + " button.so-order-bump-cta").removeAttr('disabled');
								}

							}

						});

						if( jQuery("form#"+ form_qty_id).length > 0 && jQuery("form#"+ form_qty_id).is(".allow_change") ) {
							jQuery("form#"+ form_qty_id).show();
						}
					} else {
						jQuery("div#so-entry-content-"+ offer_id + " div.so_accept").hide();
						jQuery("div#so-entry-content-"+ offer_id + " .so-order-bump-checkbox:not(:checked),div#so-entry-content-"+ offer_id + " button.so-order-bump-cta").attr('disabled',true); // Disable only not checked checkbox so that user will be able to uncheck order bump in case of variation.
						jQuery("div#so-entry-content-"+ offer_id + " div.so_accept a[href*=\'so_action=accept\']").hide();

						if( jQuery("form#"+ form_qty_id).length > 0 && jQuery("form#"+ form_qty_id).is(":visible")){
							jQuery("form#"+ form_qty_id).hide();
							jQuery("form#"+ form_qty_id).addClass("allow_change");
						}
					}
				}, 100 );
			}
		}

		function handle_variation_form() {
			var variation_form = jQuery("form").closest("div[id^='so-entry-content-'] .variations_form");

			if( variation_form.length > 0 ) {
				jQuery.each( variation_form, function( key, value ) {
					var form_id = jQuery(value).attr("id");
					hide_add_to_cart( form_id );
				});

				jQuery(".variations select").change(function(){
					var selected_form_id = jQuery(this).closest(".variations_form").attr("id");
					hide_add_to_cart( selected_form_id );
				});
			}

			jQuery("div[id^=so-entry-content] div.so_product_image a.zoom").on("click", function(e){
				e.preventDefault();
				return false;
			});
		}

		jQuery('form.checkout').on('show_variation hide_variation','form.variations_form',function(e){
			let offer_content_elem = jQuery(this).closest('.so-offer-content');
			if(jQuery(offer_content_elem).hasClass('order_bump')) {
				let order_bump_elem = jQuery(offer_content_elem).find('.so-order-bump');
				if(jQuery(order_bump_elem).hasClass('so-order-bump-style-4') || jQuery(order_bump_elem).hasClass('so-order-bump-style-2') ) {
					let variation_price_html = '';
					if( 'show_variation' === e.type ) {
						variation_price_html = jQuery(this).find('.so-show-offer-price').html(); // Get variation price html.
					}
					jQuery(this).closest('.so-order-bump').find('.so-order-bump-product-price').html(variation_price_html);
				}
			}
		});

		handle_variation_form();

		function handle_ajax_variation_form() {
			// Variation Form
            var form_variation = jQuery('div[id^="so-entry-content-"] .variations_form');

            if( 0 === jQuery(form_variation).length ) {
            	return false;
            }

            if( 'undefined' !== typeof jQuery( this ).wc_variation_form ) {
            	form_variation.each( function() {
	                jQuery( this ).wc_variation_form();
	            });
	            form_variation.trigger( 'check_variations' );
	            handle_variation_form();
            }
		}

		let messages_container = jQuery( '.woocommerce-notices-wrapper:first' ) || jQuery( '.cart-empty' ).closest( '.woocommerce' ) || jQuery( '.woocommerce-cart-form' );
		let so_error_messages = '';

		// If default woocommmerce message container not found then use .entry-content element
		if( 0 === messages_container.length ) {
			messages_container = jQuery('.entry-content');
		}

		jQuery('body').on('click change','[id^=so-entry-content-] [href*="so_action"],form.checkout .so-order-bump-checkbox', function(e){
			e.preventDefault();

			let current_elem = jQuery(this); // Current element object i.e. Accept/Reject Button or checkbox.
			let current_offer = jQuery(this).closest('[id^=so-entry-content-]');
			let current_offer_container = jQuery(current_offer).closest('.so-offer-content');
			let current_offer_container_id = jQuery(current_offer_container).attr('id');
			let source = '';

		    // Add loader.
		    current_offer.block({
		        message: null,
		        overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
		    });

			let request_url = so_frontend_data.ajax_url + '?action=so_process_offer_action';

			// If this is order bump then get offer data from checkbox element
			if( jQuery(current_elem).hasClass('so-order-bump-checkbox') ) {
				let so_offer_id = jQuery(current_elem).val();
				let so_action = jQuery(current_elem).is(':checked') ? 'accept' : 'skip';
				request_url += '&so_action=' + so_action + '&so_offer_id=' + so_offer_id + '&so_offer_type=order_bump';
			} else {
				let so_action_link = jQuery(this).attr( 'href' );
				let url_vars = get_url_vars( so_action_link );
				let var_value = '';
				
				for (let url_var in url_vars) {
					if (url_vars.hasOwnProperty(url_var)) {
						var_value = url_vars[url_var];
						request_url += '&' + url_var + '=' + var_value;
						if( 'source' === url_var ) {
							source = var_value; // store source info
						}
					}
				}
			}

			let form_data = [];
			let selected_offer_id = jQuery(this).parent().closest("div.entry-content").find("input#so-offer-id").val();
			let current_form_id = jQuery(this).parent().closest("div.entry-content").find("form#so_addtocart_"+selected_offer_id).length;
			let current_qty_form_id = jQuery(this).parent().closest("div.entry-content").find("form#so_qty_"+selected_offer_id).length;

			if( current_form_id > 0 ){
				form_data = jQuery("form#so_addtocart_"+selected_offer_id).serializeArray();
			}

			if( current_qty_form_id > 0 ){
				if( current_form_id > 0 ){
					let form_id = "so_hidden_form_" + selected_offer_id;
					jQuery("<div id="+form_id+"></div>"  ).appendTo("#so_qty_"+selected_offer_id).hide();
					jQuery("#so_addtocart_"+selected_offer_id + " input" ).appendTo("#so_hidden_form_"+selected_offer_id);
					jQuery("#so_addtocart_"+selected_offer_id + " select" ).appendTo("#so_hidden_form_"+selected_offer_id);
				}
				form_data = jQuery("form#so_qty_"+selected_offer_id).serializeArray();
			}
			
			form_data.push({name: 'so_actions_security', value: so_frontend_data.so_actions_security}); // Add security nonce.

			jQuery(messages_container).find('.so-error-messages').remove(); // Remove error messages if any.
			let so_error_flag = 0;

			jQuery.ajax({
				url: request_url,
				dataType: 'json',
				data: form_data,
				type: 'POST',
				success: function(response) {
					if( null !== response ) {
						if( 'undefined' !== typeof response.result ) {
							if( 'success' === response.result ) {
								if( 'undefined' !== typeof response.redirect  ) {
									// If redirect url and current url are same then don't redirect
									if( window.location.href === response.redirect ) {
										if( 'undefined' !== typeof response.data && null !== response.data ) {
											// Show next offers if available
											if( 'undefined' !== typeof response.data.linked_offers_html && null !== response.data.linked_offers_html && '' !== response.data.linked_offers_html ) {
												let so_linked_offer = jQuery.parseHTML( response.data.linked_offers_html );
												let so_offfer_display_as = 'inline';
												if( jQuery(so_linked_offer).hasClass('so-popup') || 'home' === so_frontend_data.where || ( ( 'checkout' ===  so_frontend_data.where && 'so_post_checkout' === source ) && 'undefined' !== typeof jQuery.magnificPopup && jQuery.magnificPopup.instance.isOpen )  ) {
													so_offfer_display_as = 'popup'; // On home page all linked offers should be displayed as poupup
												}
												
												jQuery(so_linked_offer).find('div.images').addClass('so_product_image');
												jQuery(so_linked_offer).show();
												if( 'popup' === so_offfer_display_as ) {
													jQuery(so_linked_offer).addClass('white-popup');

													if( 'undefined' === typeof jQuery.magnificPopup ) {
														jQuery.getScript( so_frontend_data.so_plugin_url + '/assets/js/jquery.magnific-popup.js', function( data, status, jqxhr ) {
														if( 'success' === status ) {

															  	jQuery("<link/>", {
																   rel: "stylesheet",
																   type: "text/css",
																   href: so_frontend_data.so_plugin_url + '/assets/css/magnific-popup.css'
																}).appendTo("head");

															  	// Opening popup here ensures it run only after magnific popup js loaded
																jQuery.magnificPopup.open({
																	items: {
																		  src: so_linked_offer
																		},
																	type: 'inline',
																	modal: true,
																	tError: so_frontend_data.ajax_error
																});
															}
														});
													} else {
														// If popup already open then add current offer to opened popup.
														if( jQuery.magnificPopup.instance.isOpen && 'checkout' === so_frontend_data.where ) {
															let existing_offers = new Array();

															jQuery(current_offer_container).remove();
															if( jQuery('.mfp-content').html() ) {
																existing_offers = jQuery.parseHTML( jQuery('.mfp-content').html() );
																so_linked_offer = existing_offers.concat(so_linked_offer);	
															}
														} 

														jQuery.magnificPopup.open({
															items: {
																  src: so_linked_offer
																},
															type: 'inline',
															modal: true,
															tError: so_frontend_data.ajax_error,
														});
													}
												} else {
													// On offer is related to post_checkout_page attached linked offer to .smart-offers-post-action element since on page load inline offers shown in this element
													if( 'checkout' === so_frontend_data.where && 'post_checkout_page' === source ) {
														jQuery('.smart-offers-post-action').first().prepend(so_linked_offer);
													} else {
														jQuery('.entry-content').prepend(so_linked_offer);	
													}
												}

												if( 'undefined' !== typeof response.data.so_actions_security ) {
													so_frontend_data.so_actions_security = response.data.so_actions_security; // Update security nonce for next request
												}
											}
										}
										if( ! jQuery(current_offer_container).hasClass('order_bump') ) {
											jQuery(current_offer_container).remove();
										}
										
										if( 0 === jQuery('.mfp-content .so-offer-content').length && 'undefined' !== typeof jQuery.magnificPopup && jQuery.magnificPopup.instance.isOpen ) {
											// If there are no offer in popup then close popup.
											jQuery.magnificPopup.close();
											jQuery('.so-offer-content.mfp-hide').remove();
										}

										if( 'cart' ===  so_frontend_data.where ) {
											if( 0 === jQuery('.woocommerce-cart-form').length ) {
												// This is to prevent cart page reload since if WooCommerce reloads the cart page if does not find .woocommerce-cart-form element in page.
												// Ref. woocommerce/assets/js/frontend/cart.js:73 $( '.woocommerce-cart-form' ).length === 0
												jQuery('.entry-content .woocommerce').append('<form class="woocommerce-cart-form"></form><div class="cart-collaterals"><div class="cart_totals"</div></div>');
											}
											if( 'undefined' !== typeof response.is_cart_empty && false === response.is_cart_empty ) {
												jQuery('.cart-empty, .return-to-shop').remove(); // Remove empty cart html since cart is not empty now.
											}
										}
									} else {
										window.location.href = response.redirect;
									}
								} 
							} else if( 'failure' === response.result ) {
								so_error_flag = 1;
								if( 'undefined' !== typeof response.data && null !== response.data  ) {
									let data = response.data;
									if( 'undefined' !== typeof data.messages && null !== data.messages ) {
										so_error_messages = data.messages;
									}
								} else if( 'undefined' !== typeof response.messages && null !== response.messages && '' !== response.messages ) {
									// error messages from woocommerce
									so_error_messages = jQuery(response.messages).html();
								}
							} else {
								so_error_flag = 1;
							}
						}
					} else {
						so_error_flag = 1;
					}
				},
				error: function(jqXHR, exception) {
					so_error_flag = 1;
				}
			}).always(function(){
				setTimeout(function() {
					handle_ajax_variation_form();	
					jQuery( document.body ).trigger( 'wc_update_cart' ).trigger( 'update_checkout' );
				},0);

				if( 1 === so_error_flag ) {
					jQuery(current_offer_container).remove();

					if( jQuery(current_offer_container).hasClass('so-popup') && 0 === jQuery('.mfp-content .so-offer-content').length ) {
						// Close popup only when one offer is in popup.
						jQuery.magnificPopup.close();
						jQuery('.so-offer-content.mfp-hide').remove();
					}
				}

				// If it is post checkout offer.
				if( 'checkout' === so_frontend_data.where ) {
					jQuery('.smart-offers-post-action').each(function(){
						// If no more offers to be shown then remove parent .smart-offers-post-action div to unbind Smart Offers place order' click function
						if( 0 === jQuery(this).find('.so-offer-content').length || 1 === jQuery(this).find('.mfp-hide').length ) {
							jQuery(this).remove();
						}
					});
					
					// This block removes any accepted/rejected offer html from current page except order bump offer.
					// TODO - Handle it from offers's session
					if( jQuery( '#' + current_offer_container_id + ':not(.order_bump)').length > 0 ) {
						// Remove any html from current page related to this offer to avoid shown multiple times
						jQuery( '#' + current_offer_container_id + ':not(.order_bump)').remove();
					}
				}

				if( 1 === jQuery( 'form.checkout' ).length && jQuery( 'form.checkout' ).is(":hidden") && 0 === jQuery('.so-offer-content').length ) {
					jQuery( 'form.checkout' ).show();
				}

				// Remove loader.
				jQuery(current_offer).unblock();

				if( 1 === jQuery('.so-offer-content').length && jQuery('.so-offer-content').hasClass('so-popup') ) {
					let so_offer = jQuery('.so-offer-content');
					show_offer_as_popup(so_offer);
				}
			});
		});

		jQuery( document.body ).on( 'updated_wc_div updated_checkout', function(){
			if( '' !== so_error_messages ) {
				jQuery(messages_container).prepend( '<div class="so-error-messages"><ul class="woocommerce-error" role="alert"><li>' + so_error_messages + '</li></ul></div>' );

				jQuery.scroll_to_notices( jQuery( '[role="alert"]' ) );
				so_error_messages = ''; // Empty out error messages.
			}
			handle_ajax_variation_form(); // This is to activate woocommerce variation handling code since order bump offers are also refreshed after page load.
		});
		
		jQuery('body').on('click','form.checkout .so-order-bump-cta', function(e){
			e.preventDefault();
			let cta_elem = jQuery(this);
			jQuery(cta_elem).closest('.so-order-bump').find('.so-order-bump-checkbox').trigger('click');
		});
		function get_url_vars( url ) {
			let vars = [], hash;
			let hashes = url.slice(url.indexOf('?') + 1).split('&');
			for(let i = 0; i < hashes.length; i++) {
				hash = hashes[i].split('=');
				vars[hash[0]] = hash[1];
			}
			return vars;
		}

		function show_offer_as_popup( so_offer ) {
			jQuery(so_offer).addClass('white-popup');

			if( 'undefined' === typeof jQuery.magnificPopup ) {
				jQuery.getScript( so_frontend_data.so_plugin_url + '/assets/js/jquery.magnific-popup.js', function( data, status, jqxhr ) {
				if( 'success' === status ) {
					  	jQuery("<link/>", {
						   rel: "stylesheet",
						   type: "text/css",
						   href: so_frontend_data.so_plugin_url + '/assets/css/magnific-popup.css'
						}).appendTo("head");

					  	// Opening popup here ensures it run only after magnific popup js loaded
						jQuery.magnificPopup.open({
							items: {
								  src: so_offer
								},
							type: 'inline',
							modal: true,
							tError: so_frontend_data.ajax_error
						});
					}
				});
			} else {
				jQuery.magnificPopup.open({
					items: {
						  src: so_offer
						},
					type: 'inline',
					modal: true,
					tError: so_frontend_data.ajax_error,
				});
			}
		}
	});
})(jQuery);
