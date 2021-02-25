<?php
/**
 * Smart Offers Rules
 *
 * @author      StoreApps
 * @since       3.10.7
 * @version     1.1.2
 *
 * @package     smart-offers/includes/admin
 */

global $wpdb, $post, $sa_smart_offers;
$param_name = 'wc_enhanced_select_params';

// To fix DB collation issue.
$results = $wpdb->get_results( "SHOW FULL COLUMNS FROM {$wpdb->prefix}term_taxonomy", 'ARRAY_A' );
$taxonomy_collattion = $wpdb->collate;
if( count( $results ) > 0 ) {
	foreach ( $results as $column ) {
		if( $column['Field'] == 'taxonomy' ) {
			$taxonomy_collattion = $column['Collation'];
			break;
		}
	}
}

$query = "SELECT wat.attribute_label AS attribute_label, tt.taxonomy AS taxonomy
		  FROM {$wpdb->prefix}woocommerce_attribute_taxonomies AS wat
		  LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON ( tt.taxonomy COLLATE ". $taxonomy_collattion ." = CONCAT( 'pa_', wat.attribute_name ) COLLATE ". $taxonomy_collattion ." ) ";
$attribute_results = $wpdb->get_results( $query, 'ARRAY_A' );
?>
<style type="text/css" id="so-offer-rules-before-load">
	/* Added to hide offer rules div before document load */
	div#so-offer-data div#offers_options {
		display: none;
	}
</style>
<script type="text/javascript">
	jQuery(function() {
		if ( typeof getEnhancedSelectFormatString == "undefined" ) {
			function getEnhancedSelectFormatString() {
				var formatString = {
					noResults: function() {
						return wc_enhanced_select_params.i18n_no_matches;
					},
					errorLoading: function() {
						return wc_enhanced_select_params.i18n_ajax_error;
					},
					inputTooShort: function( args ) {
						var remainingChars = args.minimum - args.input.length;

						if ( 1 === remainingChars ) {
							return wc_enhanced_select_params.i18n_input_too_short_1;
						}

						return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
					},
					inputTooLong: function( args ) {
						var overChars = args.input.length - args.maximum;

						if ( 1 === overChars ) {
							return wc_enhanced_select_params.i18n_input_too_long_1;
						}

						return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
					},
					maximumSelected: function( args ) {
						if ( args.maximum === 1 ) {
							return wc_enhanced_select_params.i18n_selection_too_long_1;
						}

						return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
					},
					loadingMore: function() {
						return wc_enhanced_select_params.i18n_load_more;
					},
					searching: function() {
						return wc_enhanced_select_params.i18n_searching;
					}
				};

				var language = { 'language' : formatString };

				return language;
			}
		}

		var bindProductOnlyVariationsSelect2 = function() {
			jQuery( ':input.so-product-and-only-variations-search' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = {
					allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
					placeholder: jQuery( this ).data( 'placeholder' ),
					minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         '<?php echo admin_url("admin-ajax.php"); ?>',
						dataType:    'json',
						quietMillis: 250,
						data: function( params, page ) {
							return {
								term:     params.term,
								action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_only_variations',
								security: '<?php echo wp_create_nonce("search-products-and-only-variations"); ?>'
							};
						},
						processResults: function( data, page ) {
							var terms = [];
							if ( data ) {
								terms.push( { id: 'all', text: '<?php echo __( "All Products", 'smart-offers' ); ?>' } );
								jQuery.each( data, function( id, text ) {
									terms.push( { id: id, text: text } );
								});
							}

							return { results: terms };
						},
						cache: true
					}
				};

				select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});
		};

		bindProductOnlyVariationsSelect2();

		var bindCategorySelect2 = function() {
			jQuery( ':input.so-product-category-search' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = {
					allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
					placeholder: jQuery( this ).data( 'placeholder' ),
					minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         '<?php echo admin_url("admin-ajax.php"); ?>',
						dataType:    'json',
						quietMillis: 250,
						data: function( params, page ) {
							return {
								term:     params.term,
								action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_prod_category',
								security: '<?php echo wp_create_nonce("so-search-product-category"); ?>'
							};
						},
						processResults: function( data, page ) {
							var terms = [];
							if ( data ) {
								jQuery.each( data, function( id, text ) {
									terms.push( { id: id, text: text } );
								});
							}
							return { results: terms };
						},
						cache: true
					}
				};

				select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});
		};

		bindCategorySelect2();

		var bindAttributesSelect2 = function() {
			jQuery( ':input.so-product-attribute-search' ).filter( ':not(.enhanced)' ).each( function() {

				var select2_args = {
					allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
					placeholder: jQuery( this ).data( 'placeholder' ),
					minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '1',
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         '<?php echo admin_url("admin-ajax.php"); ?>',
						dataType:    'json',
						quietMillis: 250,
						data: function( params, page ) {
							var loopNumber = jQuery(this).attr('id').split('_').pop();
							var key = jQuery('#cart_prod_attribute_'+loopNumber).find(":selected").val();
							return {
								term:     params.term,
								key:      key,
								action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_product_attribute',
								security: '<?php echo wp_create_nonce("so-search-product-attribute"); ?>'
							};
						},
						processResults: function( data, page ) {
							var terms = [];
							if ( data ) {
								jQuery.each( data, function( id, text ) {
									terms.push( { id: id, text: text } );
								});
							}
							return { results: terms };
						},
						cache: true
					}
				};

				select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});
		};

		bindAttributesSelect2();

		var loop;
		var group_loop;
		var last_index;
		var group_last_index;

		jQuery('#offer_rules').on('click', 'a.add_new_rule, button.and_rule_option', function() {
			last_index = jQuery('.woo_offer_rules .woo_offer_rule').length - 1;
			group_last_index = jQuery('.woo_offer_rules .so-offer-rule-group').length - 1;

			var current_element = jQuery(this);

			var triggered_by = '';
			var and_or = '';

			if ( jQuery(this).hasClass('and_rule_option') ) {
				triggered_by = 'and_rule_option';
				and_or = 'and';
			} else {
				triggered_by = 'add_new_rule';
				and_or = 'or';
			}

			var size_of_rules = jQuery('.woo_offer_rules .woo_offer_rule').length;
			if ( loop ) {
				loop = loop + 1;
			} else {
				if ( size_of_rules == 0 ) {
					loop = 0;
				} else {
					loop = last_index + 1;
				}
			}
			
			var size_of_group_rules = jQuery('.woo_offer_rules .so-offer-rule-group').length;
			if ( 'add_new_rule' == triggered_by ) {
				if ( group_loop ) {
					group_loop = group_loop + 1;
				} else {
					if ( size_of_group_rules == 0 ) {
						group_loop = 0;
					} else {
						group_loop = group_last_index + 1;
					}
				}
			}

			var productSearchHtml = productCategorySearchHtml = '';
			productSearchHtml = '<select class="wc-product-search" multiple="multiple" style="width: 42%;" name="search_product_ids_' + loop + '[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_products_and_variations"></select>';

			productCategorySearchHtml = '<select class="so-product-category-search" style="width: 42%;" name="search_category_ids_' + loop + '[]" data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_prod_category"></select>';

			attributeTermSearchHtml = '<select class="so-product-attribute-search" style="width: 42%; margin-left: 8.4% !important;" allow-clear="true" name="cart_prod_attribute_term_' + loop + '[]" data-placeholder="<?php esc_attr_e( 'Search for a term&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_product_attribute"></select>';

			var html = '<div class="woo_offer_rule" >\
							<p class="type">\
								<label class="hidden"><?php _e( 'Type:', 'smart-offers' ); ?></label>\
								<select class="role" id="role" name="offer_type[' + loop + ']">\
										<option value="cartorder"><?php _e( 'Cart/Order', 'smart-offers' ); ?></option>\
										<option value="user"><?php _e('User', 'smart-offers'); ?></option>\
										<option value="offer_valid_between"><?php _e( 'Offer Valid', 'smart-offers' ); ?></option>\
								</select>\
								<label class="hidden"><?php _e( 'Action:', 'smart-offers' ); ?></label>\
								<select class="action" id="action" name="offer_action[' + loop + ']">\
										<option value="cart_contains" name="cartorder" id="contains-product"><?php _e( 'Contains products', 'smart-offers' ); ?></option>\
										<option value="cart_doesnot_contains" name="cartorder" id="does-not-contains-products"><?php _e( 'Does not contains products', 'smart-offers' ); ?></option>\
										<option value="cart_total_less" name="cartorder" id="total-less-than-or-equal-to"><?php _e( 'Total is less than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_total_more" name="cartorder" id="total-more-than-or-equal-to"><?php _e( 'Total is more than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_grand_total_less" name="cartorder" id="grand-total-less-than-or-equal-to"><?php _e( 'Grand total is less than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_grand_total_more" name="cartorder" id="grand-total-more-than-or-equal-to"><?php _e( 'Grand total is more than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_product_count_less" name="cartorder" id="contains-num-products-less-than-or-equal-to"><?php _e( 'Contains number of products less than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_product_count_more" name="cartorder" id="contains-num-products-more-than-or-equal-to"><?php _e( 'Contains number of products more than or equal to', 'smart-offers' ); ?></option>\
										<option value="cart_prod_categories_is" name="cartorder" id="contain-product-from-category"><?php _e( 'Contain product from category', 'smart-offers' ); ?></option>\
										<option value="cart_prod_categories_not_is" name="cartorder" id="does-not-contain-product-from-category"><?php _e( 'Does not contain product from category', 'smart-offers' ); ?></option>\
										<option value="cart_prod_attribute_is" name="cartorder" id="contains-product-with-attribute"><?php _e( 'Contains product with attribute', 'smart-offers' ); ?></option>\
										<option value="cart_prod_attribute_not_is" name="cartorder" id="does-not-contain-product-with-attribute"><?php _e( 'Does not contain product with attribute', 'smart-offers' ); ?></option>\
										<option value="has_bought" name="user" id="has-purchased"><?php _e( 'Has purchased', 'smart-offers' ); ?></option>\
										<option value="not_bought" name="user" id="has-not-purchased"><?php _e( 'Has not purchased', 'smart-offers' ); ?></option>\
										<option value="registered_user" name="user" id="is"><?php _e( 'Is', 'smart-offers' ); ?></option>\
										<option value="user_role" name="user" id="is-a"><?php _e( 'Is a', 'smart-offers' ); ?></option>\
										<option value="user_role_not" name="user" id="is-not-a"><?php _e( 'Is not a', 'smart-offers' ); ?></option>\
										<option value="registered_period" name="user" id="is-registered-for"><?php _e( 'Is registered for', 'smart-offers' ); ?></option>\
										<option value="total_ordered_less" name="user" id="has-previously-purchased-less-than-or-equal-to"><?php _e( 'Has previously purchased less than or equal to', 'smart-offers' ); ?></option>\
										<option value="total_ordered_more" name="user" id="has-previously-purchased-more-than-or-equal-to"><?php _e( 'Has previously purchased more than or equal to', 'smart-offers' ); ?></option>\
										<option value="has_bought_product_categories" name="user" id="has-previously-purchased-from-category"><?php _e( 'Has previously purchased from category', 'smart-offers' ); ?></option>\
										<option value="has_not_bought_product_categories" name="user" id="has-not-previously-purchased-from-category"><?php _e( 'Has not previously purchased from category', 'smart-offers' ); ?></option>\
										<option value="has_placed_num_orders_less" name="user" id="has-num-orders-less-than-or-equal-to"><?php _e( 'Has placed number of orders less than or equal to', 'smart-offers' ); ?></option>\
										<option value="has_placed_num_orders_more" name="user" id="has-num-orders-more-than-or-equal-to"><?php _e( 'Has placed number of orders more than or equal to', 'smart-offers' ); ?></option>\
								</select>\
								<input class="price" type="number" step="any" size="5" min="1" name="price[' + loop + ']" placeholder="Enter price" />\
								<input class="product_count" type="number" step="1" size="5" min="1" name="product_count[' + loop + ']" placeholder="Enter number of products" />\
								<input class="orders_count" type="number" step="1" size="5" min="1" name="orders_count[' + loop + ']" placeholder="Enter number of orders" />\
								<span id="search_product_ids_' + loop + '">\
									'+ productSearchHtml +'\
								</span>\
								<span id="search_category_ids_' + loop + '">\
									'+ productCategorySearchHtml +'\
								</span>\
								<label class="hidden"><?php _e( 'registered user action:', 'smart-offers' ); ?></label>\
								<select class="registered_user_action_' + loop + '" id="registered_user_action_' + loop + '" name="registered_user_action_' + loop + '">\
										<option value="yes"><?php _e( 'Registered', 'smart-offers' ); ?></option>\
										<option value="no"><?php _e( 'A visitor', 'smart-offers' ); ?></option>\
								</select>\
								<label class="hidden"><?php _e( 'registered period action:', 'smart-offers' ); ?></label>\
								<select class="registered_period_action_' + loop + '" id="registered_period_action_' + loop + '" name="registered_period_action_' + loop + '">\
										<option value="one_month" name="registered_period_one_month" ><?php _e( 'Less than 1 month', 'smart-offers' ); ?></option>\
										<option value="three_month" name="registered_period_three_month"><?php _e( 'Less than 3 months', 'smart-offers' ); ?></option>\
										<option value="six_month" name="registered_period_six_month"><?php _e( 'Less than 6 months', 'smart-offers' ); ?></option>\
										<option value="less_than_1_year" name="registered_period_less_than_1_yr"><?php _e( 'Less than 1 year', 'smart-offers' ); ?></option>\
										<option value="more_than_1_year" name="registered_period_more_than_1_yr"><?php _e( 'More than 1 year', 'smart-offers' ); ?></option>\
								</select>\
								<select class="user_role_' + loop + '" id="user_role_' + loop + '" name="user_role_' + loop + '">\
									<?php
										if (!isset($wp_roles)) {
											$wp_roles = new WP_Roles();
										}
										$all_roles = $wp_roles->roles;

										foreach ($all_roles as $role_id => $role) {
											echo '<option value="' . $role_id . '" name="' . $role_id . '" >' . esc_html($role['name']) . '</option>';
										}
									?>\
								</select>\
								<span class="offer_dates_fields" name="offer_valid_between_' + loop + '" id="offer_valid_between_' + loop + '" ><label class="hidden"><?php _e( 'offer_valid_between:', 'smart-offers' ); ?></label>\
								<input type="text" class="short date-picker" name="_offer_valid_from_' + loop + '" id="_offer_valid_from_' + loop + '" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'placeholder', 'smart-offers' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />\
								<input type="text" class="short date-picker" name="_offer_valid_till_' + loop + '" id="_offer_valid_till_' + loop + '" value="" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'placeholder', 'smart-offers' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />\
								</span>\
								<span class="so-button-group button-group">\
									<button type="button" class="and_rule_option button button-small" id="' + loop + '" ><?php echo esc_html__( 'AND', 'smart-offers' ); ?></button>\
									<button type="button" class="remove_rule_option button button-small" id="' + loop + '" >x</button>\
								</span>\
								<select class="cart_prod_attribute_' + loop + '" id="cart_prod_attribute_' + loop + '" name="cart_prod_attribute_' + loop + '">\
									<?php
										$attributes = array();
										$attributes_to_terms = array();
										foreach ( $attribute_results as $attribute_result ) {
											if ( !in_array( $attribute_result['attribute_label'], $attributes, true ) ) {
												$attributes[$attribute_result['taxonomy']] = $attribute_result['attribute_label'];
											}
											if ( !isset( $attributes_to_terms[$attribute_result['taxonomy']] ) ) {
												$attributes_to_terms[$attribute_result['taxonomy']] = array();
											}
										}
										if ( empty( $attributes_to_terms ) ) {
											echo '<option value="" name="">' . __( 'No attributes found...', 'smart-offers' ) . '</option>';
										} else {
											foreach ( $attributes_to_terms as $attributes_slug => $attribute_terms ) {
												echo '<option value="' . $attributes_slug . '" name="' . $attributes_slug . '" >' . esc_html(wc_attribute_label($attributes_slug)) . '</option>';
											}
										}
									?>
								</select>\
							</p>\
							<p class="category_total_' + loop + '">\
								<select id="category_total_' + loop + '" name="category_total_' + loop + '" style="width: 34%; margin-left: 105px; margin-right: 7px;">\
									<option value="category_total_more"><?php _e( 'Subtotal is more than or equal to (of products in this category)', 'smart-offers' ); ?></option>\
									<option value="category_total_less"><?php _e( 'Subtotal is less than or equal to (of products in this category)', 'smart-offers' ); ?></option>\
								</select>\
								<input type="number" class="category_amount" id="category_amount_' + loop + '" step="any" size="5" min="1" name="category_amount_' + loop + '" placeholder="<?php echo __( 'Enter price(Optional)', 'smart-offers' ) ?>" style="width: 20%;">\
							</p>\
							<p class="quantity_total_' + loop + '">\
								<select id="quantity_total_' + loop + '" name="quantity_total_' + loop + '" style="margin-left: 105px;margin-right: 7px;">\
									<option value="quantity_total_more"><?php _e( 'Quantity is more than or equal to', 'smart-offers' ); ?></option>\
									<option value="quantity_total_less"><?php _e( 'Quantity is less than or equal to', 'smart-offers' ); ?></option>\
								</select>\
								<input type="number" class="cart_quantity" id="cart_quantity_' + loop + '" step="1" size="5" min="1" name="cart_quantity_' + loop + '" placeholder="<?php echo __( 'Enter Quantity(Optional)', 'smart-offers' ) ?>" style="width: 25%;">\
							</p>\
							<p class="cart_prod_attribute_term_' + loop + '" id="cart_prod_attribute_term_' + loop + '" style="width: 78%; margin-left: 8.4%;">\
								'+ attributeTermSearchHtml +'\
							</p>\
							<input type="hidden" name="and_or[' + loop + ']" value="' + and_or + '">\
						</div>';

			if ( 'and_rule_option' == triggered_by ) {
				current_element.closest('.woo_offer_rule').after( html );
			} else {
				var or_html = '';
				var group_count = jQuery('#offer_rules .woo_offer_rules .so-offer-rule-group').length;
				if ( group_count > 0 ) {
					or_html += '<span class="so-or-break-container">\
									<span class="so-or-break"><?php echo esc_html__( 'OR', 'smart-offers' ); ?></span>\
								</span>';
				}
				or_html += '<div class="so-offer-rule-group so-offer-rule-group-' + group_loop + '">' + html + '</div>';
				jQuery('.woo_offer_rules').append( or_html );
			}

			jQuery( ':input.wc-product-search' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = {
					allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
					placeholder: jQuery( this ).data( 'placeholder' ),
					minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         '<?php echo admin_url("admin-ajax.php"); ?>',
						dataType:    'json',
						quietMillis: 250,
						data: function( params, page ) {
							return {
								term:     params.term,
								action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
								security: <?php echo $param_name . ".search_products_nonce"; ?>
							};
						},
						processResults: function( data, page ) {
							var terms = [];
							if ( data ) {
								jQuery.each( data, function( id, text ) {
									terms.push( { id: id, text: text } );
								});
							}
							return { results: terms };
						},
						cache: true
					}
				};

				select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});

			var bindAttributesSelect2 = function() {

				jQuery( ':input.so-product-attribute-search' ).filter( ':not(.enhanced)' ).each( function() {
					var select2_args = {
						allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
						placeholder: jQuery( this ).data( 'placeholder' ),
						minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '1',
						escapeMarkup: function( m ) {
							return m;
						},
						ajax: {
							url:         '<?php echo admin_url("admin-ajax.php"); ?>',
							dataType:    'json',
							quietMillis: 250,
							data: function( params, page ) {
								var key = jQuery('#cart_prod_attribute_' + loop + '').find(":selected").val();
								return {
									term:     params.term,
									key:      key,
									action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_product_attribute',
									security: '<?php echo wp_create_nonce("so-search-product-attribute"); ?>'
								};
							},
							processResults: function( data, page ) {
								var terms = [];
								if ( data ) {
									jQuery.each( data, function( id, text ) {
										terms.push( { id: id, text: text } );
									});
								}
								return { results: terms };
							},
							cache: true
						}
					};

					select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

					jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
				});

			};

			bindProductOnlyVariationsSelect2();
			bindCategorySelect2();
			bindAttributesSelect2();

			jQuery(".date-picker").datepicker({
				minDate: 0,
				changeMonth: true,
				changeYear: true,
				defaultDate: '',
				dateFormat: "yy-mm-dd",
				numberOfMonths: 1,
				showButtonPanel: true,
				showOn: "focus",
				buttonImageOnly: true
			});

			jQuery('select.role[name="offer_type[' + loop + ']"]').trigger('change');

			return false; // to stay on that area of page
		});

		jQuery('#offer_rules').on('change', 'select.role', function() {
			// Hiding all element at first
			jQuery(this).closest('.woo_offer_rule').find('select[name*="offer_action"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('input[name*="price"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('select[name*="search_product_ids_"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('span[id*="search_product_ids_"]').css('display', 'none');
//			jQuery(this).closest('.woo_offer_rule').find('span[id*="search_category_ids_"]').css('display', 'none');			//not displaying category in WC 3.0, hence commenting it. Check how it works in WC 2.6 & below

			jQuery(this).closest('.woo_offer_rule').find('select[name*="registered_period_action_"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('select[name*="registered_user_action_"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('select[name*="user_role_"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('span[name*="offer_valid_between_"]').css('display', 'none');

			jQuery(this).closest('.woo_offer_rule').find('p[class*="category_total_"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
			jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			var id = jQuery(this).val();
			var name = jQuery(this).attr('name');
			var loop = name.split("[")[1].split("]")[0];

			if ( id == 'offer_valid_between' ) {
				jQuery(this).closest('.woo_offer_rule').find('span[id*="search_category_ids_"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('p.quantity_total_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('p.cart_prod_attribute_term_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span[name="offer_valid_between_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input[name="_offer_valid_from_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input[name="_offer_valid_till_' + loop + '"]').css('display', 'inline');
			} else {
				if ( jQuery(this).data('options') == undefined ) {
					/*Taking an array of all options-2 and kind of embedding it on the select1*/
					jQuery(this).data('options', jQuery('#action[name="offer_action[' + loop + ']"] option').clone());
				}

				jQuery(this).closest('.woo_offer_rule').find('select[name="offer_action[' + loop + ']"]').css('display', 'inline');
				var options = jQuery(this).data('options').filter('[name=' + id + ']');
				jQuery('select[name="offer_action[' + loop + ']"]').html(options);

				jQuery('select.action[name="offer_action[' + loop + ']"]').trigger('change');
			}
		});

		jQuery('#offer_rules').on('change', 'select.action', function() {
			var name = jQuery(this).attr('name');
			var loop = name.split("[")[1].split("]")[0];
			var id = jQuery('select[name="offer_action[' + loop + ']"] option:selected').attr('id');

			// Return if select action is hidden
			if ( jQuery(this).closest('.woo_offer_rule select[name="offer_action[' + loop + ']"]').is(":visible") == false ) {
				return false;
			}

			if ( id == 'contains-product' ) {
				jQuery('p.quantity_total_' + loop + '').css('display', 'block');
			} else {
				jQuery('p.quantity_total_' + loop + '').css('display', 'none');
			}

			if ( id == 'contain-product-from-category' ) {
				jQuery('p.category_total_' + loop + '').css('display', 'block');
			} else {
				jQuery('p.category_total_' + loop + '').css('display', 'none');
			}

			if ( id == 'contains-product-with-attribute' || id == 'does-not-contain-product-with-attribute' ) {
				jQuery('p.cart_prod_attribute_term_' + loop + '').css('display', 'block');
			} else {
				jQuery('p.cart_prod_attribute_term_' + loop + '').css('display', 'none');
			}

			if ( id == 'total-less-than-or-equal-to' || id == 'total-more-than-or-equal-to' || id == 'grand-total-less-than-or-equal-to' || id == 'grand-total-more-than-or-equal-to' || id == 'has-previously-purchased-less-than-or-equal-to' || id == 'has-previously-purchased-more-than-or-equal-to' ) {

				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'is' ) {

				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'is-registered-for' ) {

				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'is-a' || id == 'is-not-a' ) {

				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'contain-product-from-category' || id == 'does-not-contain-product-from-category' || id == 'has-previously-purchased-from-category' || id == 'has-not-previously-purchased-from-category' ) {

				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
//				limit_category(loop);
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'contains-product' || id == 'does-not-contains-products' || id == 'has-purchased' || id == 'has-not-purchased' ) {

				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'contains-num-products-less-than-or-equal-to' || id == 'contains-num-products-more-than-or-equal-to' ) {

				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');

			} else if ( id == 'has-num-orders-less-than-or-equal-to' || id == 'has-num-orders-more-than-or-equal-to' ) {

				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'none');
			
			} else if ( id == 'contains-product-with-attribute' || id == 'does-not-contain-product-with-attribute' ) {

				jQuery(this).closest('.woo_offer_rule').find('select[name="cart_prod_attribute_' + loop + '"]').css('display', 'inline');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="product_count"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input.price').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_product_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_user_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="registered_period_action_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('select[name="user_role_' + loop + '"]').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('span#search_category_ids_' + loop + '').css('display', 'none');
				jQuery(this).closest('.woo_offer_rule').find('input[name*="orders_count"]').css('display', 'none');

			}

			return false;
		});

		jQuery('#offer_rules').on('change', 'select[id^="search_category_ids_"]', function() {
			var id = jQuery(this).attr('id');
			var loop = id.split("search_category_ids_")[1];
			// limit_category(loop);
		});

		// to remove rule
		jQuery(document).on('click', 'button.remove_rule_option', function() {
			var rule_id = jQuery(this).attr('id');

			if ( jQuery("input[name='price[" + rule_id + "]']").val().trim().length == 0 && jQuery('span#search_product_ids_' + rule_id + ' .select2-container ul.select2-choices li.select2-search-choice').length == 0 && !( jQuery("select[name='offer_type[" + rule_id + "]']").val() == "user" && ( jQuery("select[name='offer_action[" + rule_id + "]']").val() == "registered_user" || jQuery("select[name='offer_action[" + rule_id + "]']").val() == "registered_period" ) ) ) {
				answer = true;
			} else {
				answer = confirm('<?php _e( 'Are you sure you want delete this rule?', 'smart-offers' ); ?>');
			}

			if (answer) {
				jQuery(this).closest('div').remove();
				handle_add_rule_button();
				cleanup_rule_groups();
			}

			return false;
		});

		// To hide/display offer options based on tab selected - SO-v3.3.0
		jQuery( '#so-offer-data' ).find( '#so_whats_offer_panel' ).css( 'display','block' );

		jQuery('#so-offer-data #offers_options').css('display','block');

		jQuery( '#so-offer-data li' ).on( 'click', function() {
			jQuery( '#so-offer-data' ).find( '.'+this.id ).css( 'display','block' );
		});

		function handle_add_rule_button() {
			let rule_count = jQuery('#offer_rules .woo_offer_rules .woo_offer_rule').length;
			if ( rule_count && rule_count > 0 ) {
				jQuery('#add_new_rule_button .add_new_rule').text('<?php echo esc_html__( 'OR', 'smart-offers' ); ?>');
			} else {
				jQuery('#add_new_rule_button .add_new_rule').text('<?php echo esc_html__( '+ Add New Rule', 'smart-offers' ); ?>');
			}
		}

		function cleanup_rule_groups() {
			jQuery.each( jQuery('#offer_rules .woo_offer_rules .so-offer-rule-group'), function( index, value ){
				let element = jQuery(value);
				let count = element.find('.woo_offer_rule').length;
				if ( count <= 0 ) {
					if ( element.prev().hasClass( 'so-or-break-container' ) ) {
						element.prev().remove();
					}
					element.remove();
				}
			});
			let first_or = jQuery('#offer_rules .woo_offer_rules .so-or-break-container').first();
			let first_group_index = jQuery('#offer_rules .woo_offer_rules .so-offer-rule-group').first().index();
			let first_or_index = first_or.index();
			if ( first_or_index <= 0 || first_or_index < first_group_index ) {
				first_or.remove();
			}
		}

		jQuery('#offers_options').on('load change', '.woo_offer_rules', function(){
			handle_add_rule_button();
		});
	});
</script>

<?php
wp_nonce_field('woocommerce_save_data', 'woocommerce_meta_nonce');
?>

<div id="offers_options" class="show_when panel woocommerce_options_panel">
	<div class="options_group">
		<p class="form-field">
			<label id="offer_display_rules" class="so_label_text" for="offer_display_rules">
				<strong><?php echo __( 'When to show this offer?', 'smart-offers' ); ?></strong>
				<?php echo __( '(This offer will be shown only if the rules mentioned below are satisfied)', 'smart-offers' ); ?>
			</label>
		</p>
		<div id="offer_rules" class="show_when panel">
			<div class="show_when woo_offer_rules">
				<?php
				$offer_rules_group = SO_Admin_Save_Offer::get_offer_rules( $post->ID );

				$loop = 0;
				$and_or = '';

				if ( ! empty( $offer_rules_group ) ) {
					foreach ( $offer_rules_group as $group_id => $offer_rules ) {
						$and_or = 'or';

						if ( is_array( $offer_rules ) && sizeof( $offer_rules ) > 0 ) {
							if ( $group_id > 0 ) {
								?>
								<span class="so-or-break-container">
									<span class="so-or-break"><?php echo esc_html__( 'OR', 'smart-offers' ); ?></span>
								</span>
								<?php
							}
							?>
							<div class="so-offer-rule-group so-offer-rule-group-<?php echo esc_attr( $group_id ); ?>">
								<?php
									foreach ( $offer_rules as $key => $value ) {
										?>
										<div class="woo_offer_rule">
											<p class="type">
												<label class="hidden"><?php _e( 'Type:', 'smart-offers' ); ?></label>
												<select class="role" id="role" name="offer_type[<?php echo $loop; ?>]">
													<option <?php selected('cartorder', $value ['offer_type']); ?> value="cartorder"><?php _e( 'Cart/Order', 'smart-offers' ); ?></option>
													<option <?php selected('user', $value ['offer_type']); ?> value="user"><?php _e( 'User', 'smart-offers' ); ?></option>
													<option <?php selected('offer_valid_between', $value ['offer_type']); ?> value="offer_valid_between"><?php _e( 'Offer Valid ', 'smart-offers' ); ?></option>
												</select> 
												<label class="hidden"><?php _e( 'Action:', 'smart-offers' ); ?></label>
												<select class="action" id="action" name="offer_action[<?php echo $loop; ?>]">
													<option <?php selected('cart_contains', $value ['offer_action']); ?> value="cart_contains" name="cartorder" id="contains-product"><?php _e( 'Contains products', 'smart-offers' ); ?></option>
													<option <?php selected('cart_doesnot_contains', $value ['offer_action']); ?> value="cart_doesnot_contains" name="cartorder" id="does-not-contains-products"><?php _e( 'Does not contains products', 'smart-offers' ); ?></option>
													<option <?php selected('cart_total_less', $value ['offer_action']); ?> value="cart_total_less" name="cartorder" id="total-less-than-or-equal-to"><?php _e( 'Total is less than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_total_more', $value ['offer_action']); ?> value="cart_total_more" name="cartorder" id="total-more-than-or-equal-to"><?php _e( 'Total is more than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_grand_total_less', $value ['offer_action']); ?> value="cart_grand_total_less" name="cartorder" id="grand-total-less-than-or-equal-to"><?php _e( 'Grand total is less than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_grand_total_more', $value ['offer_action']); ?> value="cart_grand_total_more" name="cartorder" id="grand-total-more-than-or-equal-to"><?php _e( 'Grand total is more than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_product_count_less', $value ['offer_action']); ?> value="cart_product_count_less" name="cartorder" id="contains-num-products-less-than-or-equal-to"><?php _e( 'Contains number of products less than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_product_count_more', $value ['offer_action']); ?> value="cart_product_count_more" name="cartorder" id="contains-num-products-more-than-or-equal-to"><?php _e( 'Contains number of products more than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('cart_prod_categories_is', $value ['offer_action']); ?> value="cart_prod_categories_is" name="cartorder" id="contain-product-from-category"><?php _e('Contain product from category', 'smart-offers'); ?></option>
													<option <?php selected('cart_prod_categories_not_is', $value ['offer_action']); ?> value="cart_prod_categories_not_is" name="cartorder" id="does-not-contain-product-from-category"><?php _e( 'Does not contain product from category', 'smart-offers' ); ?></option>
													<option <?php selected('cart_prod_attribute_is', $value ['offer_action']); ?> value="cart_prod_attribute_is" name="cartorder" id="contains-product-with-attribute"><?php _e( 'Contains product with attribute', 'smart-offers' ); ?></option>
													<option <?php selected('cart_prod_attribute_not_is', $value ['offer_action']); ?> value="cart_prod_attribute_not_is" name="cartorder" id="does-not-contain-product-with-attribute"><?php _e( 'Does not contain product with attribute', 'smart-offers' ); ?></option>
													<option <?php selected('has_bought', $value ['offer_action']); ?> value="has_bought" name="user" id="has-purchased"><?php _e( 'Has purchased', 'smart-offers' ); ?></option>
													<option <?php selected('not_bought', $value ['offer_action']); ?> value="not_bought" name="user" id="has-not-purchased"><?php _e( 'Has not purchased', 'smart-offers' ); ?></option>
													<option <?php selected('registered_user', $value ['offer_action']); ?> value="registered_user" name="user" id="is"><?php _e( 'Is', 'smart-offers' ); ?></option>
													<option <?php selected('user_role', $value ['offer_action']); ?> value="user_role" name="user" id="is-a"><?php _e( 'Is a', 'smart-offers' ); ?></option>
													<option <?php selected('user_role_not', $value ['offer_action']); ?> value="user_role_not" name="user" id="is-not-a"><?php _e( 'Is not a', 'smart-offers' ); ?></option>
													<option <?php selected('registered_period', $value ['offer_action']); ?> value="registered_period" name="user" id="is-registered-for"><?php _e( 'Is registered for', 'smart-offers' ); ?></option>
													<option <?php selected('total_ordered_less', $value ['offer_action']); ?> value="total_ordered_less" name="user" id="has-previously-purchased-less-than-or-equal-to"><?php _e( 'Has previously purchased less than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('total_ordered_more', $value ['offer_action']); ?> value="total_ordered_more" name="user" id="has-previously-purchased-more-than-or-equal-to"><?php _e( 'Has previously purchased more than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('has_bought_product_categories', $value ['offer_action']); ?> value="has_bought_product_categories" name="user" id="has-previously-purchased-from-category"><?php _e( 'Has previously purchased from category', 'smart-offers' ); ?></option>
													<option <?php selected('has_not_bought_product_categories', $value ['offer_action']); ?> value="has_not_bought_product_categories" name="user" id="has-not-previously-purchased-from-category"><?php _e( 'Has not previously purchased from category', 'smart-offers' ); ?></option>
													<option <?php selected('has_placed_num_orders_less', $value ['offer_action']); ?> value="has_placed_num_orders_less" name="user" id="has-num-orders-less-than-or-equal-to"><?php _e( 'Has placed number of orders less than or equal to', 'smart-offers' ); ?></option>
													<option <?php selected('has_placed_num_orders_more', $value ['offer_action']); ?> value="has_placed_num_orders_more" name="user" id="has-num-orders-more-than-or-equal-to"><?php _e( 'Has placed number of orders more than or equal to', 'smart-offers' ); ?></option>
												</select> 
												<input 
													value="<?php
															if ( $value ['offer_action'] == 'cart_total_less' || $value ['offer_action'] == 'cart_total_more' || $value ['offer_action'] == 'cart_grand_total_less' || $value ['offer_action'] == 'cart_grand_total_more' || $value ['offer_action'] == 'total_ordered_less' || $value ['offer_action'] == 'total_ordered_more' ) {
																echo $value ['offer_rule_value'];
															} else {
																echo "";
															}
															?>"
													class="price" type="number" step="any" size="5" min="1" name="price[<?php echo $loop; ?>]" placeholder="Enter price" />
												<input 
													value="<?php
															if ( $value ['offer_action'] == 'cart_product_count_less' || $value ['offer_action'] == 'cart_product_count_more' ) {
																echo $value ['offer_rule_value'];
															} else {
																echo "";
															}
															?>"
													class="product_count" type="number" step="1" size="5" min="1" name="product_count[<?php echo $loop; ?>]" placeholder="Enter number of products" />
												<input 
													value="<?php
															if ( $value ['offer_action'] == 'has_placed_num_orders_less' || $value ['offer_action'] == 'has_placed_num_orders_more' ) {
																echo $value ['offer_rule_value'];
															} else {
																echo "";
															}
															?>"
													class="orders_count" type="number" step="1" size="5" min="1" name="orders_count[<?php echo $loop; ?>]" placeholder="Enter number of orders" /> 
												<span id="<?php echo 'search_product_ids_' . $loop; ?>">
													<select class="wc-product-search" style="width: 42%;" multiple="multiple" id="<?php echo 'search_product_ids_' . $loop; ?>" name="<?php echo 'search_product_ids_' . $loop . '[]'; ?>" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-offers' ); ?>" data-action="woocommerce_json_search_products_and_variations">
														<?php
															if ( $value ['offer_action'] == 'cart_contains' || $value ['offer_action'] == 'has_bought' || $value ['offer_action'] == 'not_bought' || $value ['offer_action'] == 'cart_doesnot_contains' ) {
																$offer_rule_product_ids = explode( ',', $value ['offer_rule_value'] );

																foreach ( $offer_rule_product_ids as $offer_rule_product_id ) {

																	if ( $offer_rule_product_id ) {
																		$product = wc_get_product( $offer_rule_product_id );
																		if ( $product instanceof WC_Product ) {
																			$title = $product->get_formatted_name();

																			if ( !$title )
																				continue;

																			echo '<option value="' . $offer_rule_product_id . '"' . selected( true, true, false ) . '>' . $title . '</option>';
																		}
																	}
																}
															} else {
																echo '<option value="" ></option>';
															}

														?>
													</select>
												</span> 
												<span id="<?php echo 'search_category_ids_' . $loop; ?>">
													<select class="so-product-category-search" style="width: 42%;" id="<?php echo 'search_category_ids_' . $loop; ?>" name="<?php echo 'search_category_ids_' . $loop . '[]'; ?>" data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'smart-offers' ); ?>">
														<?php
															if ( $value ['offer_action'] == 'cart_prod_categories_is' || $value ['offer_action'] == 'cart_prod_categories_not_is' || $value ['offer_action'] == 'has_bought_product_categories' || $value ['offer_action'] == 'has_not_bought_product_categories' ) {
																	$offer_rule_product_category_ids = explode(',', $value ['offer_rule_value']);
																	foreach ($offer_rule_product_category_ids as $offer_rule_product_category_id) {

																		if ( !empty( $offer_rule_product_category_id ) ) {

																			$category = get_term($offer_rule_product_category_id, 'product_cat');

																			if (!$category)
																				continue;

																			echo '<option value="' . $offer_rule_product_category_id . '"' . selected( true, true, false ) . '>' . $category->name . '</option>';
																		}
																	}
																} else {
																	echo '<option value="" ></option>';
																}
														?>
													</select>
												</span>
												<label class="hidden"><?php _e( 'registered user action:', 'smart-offers' ); ?></label>
												<select class="<?php echo 'registered_user_action_' . $loop; ?>" id="<?php echo 'registered_user_action_' . $loop; ?>" name="<?php echo 'registered_user_action_' . $loop; ?>">
													<?php
														if ( !empty( $value['offer_rule_value'] ) && is_string( $value['offer_rule_value'] ) ) {
															?>
															<option value="yes" <?php selected('yes', $value ['offer_rule_value']); ?>><?php _e( 'Registered', 'smart-offers' ); ?></option>
															<option value="no" <?php selected('no', $value ['offer_rule_value']); ?>><?php _e( 'A Visitor', 'smart-offers' ); ?></option>
															<?php
														}
													?>
												</select> 
												<label class="hidden"><?php _e( 'registered period action:', 'smart-offers' ); ?></label>
												<select class="<?php echo 'registered_period_action_' . $loop; ?>" id="<?php echo 'registered_period_action_' . $loop; ?>" name="<?php echo 'registered_period_action_' . $loop; ?>">
													<option <?php if ($value ['offer_rule_value'] == 'one_month') echo 'selected="selected"'; ?> value="one_month" name="registered_period_one_month"><?php _e( 'Less than 1 month', 'smart-offers' ); ?></option>
													<option <?php if ($value ['offer_rule_value'] == 'three_month') echo 'selected="selected"'; ?> value="three_month" name="registered_period_three_month"><?php _e( 'Less than 3 months', 'smart-offers' ); ?></option>
													<option <?php if ($value ['offer_rule_value'] == 'six_month') echo 'selected="selected"'; ?> value="six_month" name="registered_period_six_month"><?php _e( 'Less than 6 months', 'smart-offers' ); ?></option>
													<option <?php if ($value ['offer_rule_value'] == 'less_than_1_year') echo 'selected="selected"'; ?> value="less_than_1_year" name="registered_period_less_than_1_yr"><?php _e( 'Less than 1 year', 'smart-offers' ); ?></option>
													<option <?php if ($value ['offer_rule_value'] == 'more_than_1_year') echo 'selected="selected"'; ?> value="more_than_1_year" name="registered_period_more_than_1_yr"><?php _e( 'More than 1 year', 'smart-offers' ); ?></option>
												</select>
												<select class="<?php echo 'user_role_' . $loop; ?>" id="<?php echo 'user_role_' . $loop; ?>" name="<?php echo 'user_role_' . $loop; ?>">
													<?php
														if ( ! isset( $wp_roles ) ) {
															$wp_roles = new WP_Roles();
														}
														$all_roles = $wp_roles->roles;

														foreach ( $all_roles as $role_id => $role ) {
															if ( !empty( $value['offer_rule_value'] ) && is_string( $value['offer_rule_value'] ) ) {
																echo '<option value="' . $role_id . '" name="' . $role_id . '" ' . selected(esc_attr($value ['offer_rule_value']), esc_attr($role_id), false) . '>' . esc_html($role['name']) . '</option>';
															}
														}
													?>
												</select>
												<select class="<?php echo 'cart_prod_attribute_' . $loop; ?>" id="<?php echo 'cart_prod_attribute_' . $loop; ?>" name="<?php echo 'cart_prod_attribute_' . $loop; ?>">
													<?php
															$attributes = array();
															$attributes_to_terms = array();
															foreach ( $attribute_results as $attribute_result ) {
																if ( !in_array( $attribute_result['attribute_label'], $attributes, true ) ) {
																	$attributes[$attribute_result['taxonomy']] = $attribute_result['attribute_label'];
																}
																if ( !isset( $attributes_to_terms[$attribute_result['taxonomy']] ) ) {
																	$attributes_to_terms[$attribute_result['taxonomy']] = array();
																}
															}
															if ( empty( $attributes_to_terms ) ) {
																echo '<option value="" name="">' . __( 'No attributes found...', 'smart-offers' ) . '</option>';
															} else {
																foreach ( $attributes_to_terms as $attributes_slug => $attribute_terms ) {
																	if ( !empty( $value['offer_rule_value'] ) && is_string( $value['offer_rule_value'] ) ) {
																		echo '<option value="' . $attributes_slug . '" name="' . $attributes_slug . '" ' . selected(esc_attr($value ['offer_rule_value']), esc_attr($attributes_slug), false) . '>' . esc_html(wc_attribute_label($attributes_slug)) . '</option>';
																	}
																}
															}
													?>
												</select>
												<span class="offer_dates_fields" name="<?php echo 'offer_valid_between_' . $loop; ?>" id="<?php echo 'offer_valid_between_' . $loop; ?>">
													<label class="hidden"><?php _e( 'offer_valid_between:', 'smart-offers' ); ?></label>
													<input type="text" class="short date-picker" name="<?php echo '_offer_valid_from_' . $loop; ?>" id="<?php echo '_offer_valid_from_' . $loop; ?>" 
														   value="<?php
																		if ( is_array( $value ['offer_rule_value']) && isset($value ['offer_rule_value']['offer_valid_from'] ) ) {
																			echo !empty($value ['offer_rule_value']['offer_valid_from']) ? date_i18n('Y-m-d', $value ['offer_rule_value']['offer_valid_from']) : '';
																		}
																	?>"
														   placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'placeholder', 'smart-offers' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />
													<input type="text" class="short date-picker" name="<?php echo '_offer_valid_till_' . $loop; ?>" id="<?php echo '_offer_valid_till_' . $loop; ?>" 
														   value="<?php
																	   if (is_array($value ['offer_rule_value']) && isset($value ['offer_rule_value']['offer_valid_till'])) {
																		   echo (!empty($value ['offer_rule_value']['offer_valid_till']) && $value ['offer_rule_value']['offer_valid_till'] != '') ? date_i18n('Y-m-d', $value ['offer_rule_value']['offer_valid_till']) : '';
																	   }
																   ?>" 
														   placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'placeholder', 'smart-offers' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"  />
												</span>
												<span class="so-button-group button-group">
													<button type="button" class="and_rule_option button button-small" id="<?php echo $loop; ?>"><?php echo esc_html__( 'AND', 'smart-offers' ); ?></button>
													<button type="button" class="remove_rule_option button button-small" id="<?php echo $loop; ?>">x</button>
												</span>
											</p>
											<?php if ( $value ['offer_action'] == 'cart_prod_categories_is' ) { ?>
												<p class="<?php echo 'category_total_' . $loop; ?>">
													<select id="<?php echo 'category_total_' . $loop; ?>" name="<?php echo 'category_total_' . $loop; ?>" style="width: 34%; margin-left: 105px; margin-right: 7px;">
														<option value="category_total_more" <?php selected('category_total_more', $value ['category_total']); ?>><?php _e( 'Subtotal is more than or equal to (of products in this category)', 'smart-offers' ); ?></option>
														<option value="category_total_less" <?php selected('category_total_less', $value ['category_total']); ?>><?php _e( 'Subtotal is less than or equal to (of products in this category)', 'smart-offers' ); ?></option>
													</select>
													<input type="number" step="any" size="5" min="1" class="<?php echo 'category_amount'; ?>" id="<?php echo 'category_amount_' . $loop; ?>" value="<?php echo $value ['category_amount']; ?>" name="<?php echo 'category_amount_' . $loop; ?>" placeholder="<?php echo __( 'Enter price (Optional)', 'smart-offers' ); ?>" style="width: 20%;">
												</p>
											<?php }
											if ( $value ['offer_action'] == 'cart_contains' ) { ?>
												<p class="<?php echo 'quantity_total_' . $loop; ?>">
													<select id="<?php echo 'quantity_total_' . $loop; ?>" name="<?php echo 'quantity_total_' . $loop; ?>" style="margin-left: 105px;margin-right: 7px;">
														<option value="quantity_total_more" <?php selected('quantity_total_more', $value ['quantity_total']); ?>><?php _e( 'Quantity is more than or equal to', 'smart-offers' ); ?></option>
														<option value="quantity_total_less" <?php selected('quantity_total_less', $value ['quantity_total']); ?>><?php _e( 'Quantity is less than or equal to', 'smart-offers' ); ?></option>
													</select>
													<input type="number" step="1" size="5" min="1" class="<?php echo 'cart_quantity'; ?>" id="<?php echo 'cart_quantity_' . $loop; ?>" value="<?php echo $value ['cart_quantity']; ?>" name="<?php echo 'cart_quantity_' . $loop; ?>" placeholder="<?php echo __( 'Enter quantity (Optional)', 'smart-offers' ); ?>" style="width: 25%;">
												</p>
											<?php } ?>
											<p class="<?php echo 'cart_prod_attribute_term_' . $loop; ?>" id="<?php echo 'cart_prod_attribute_term_' . $loop; ?>" style="width: 78%; margin-left: 8.4%;">
												<select class="so-product-attribute-search" style="width: 42%; margin-left: 8.4% !important;" allow-clear="true" id="<?php echo 'cart_prod_attribute_term_' . $loop; ?>" name="<?php echo 'cart_prod_attribute_term_' . $loop . '[]'; ?>" data-placeholder="<?php esc_attr_e( 'Search for a term&hellip;', 'smart-offers' ); ?>">
													<?php
														if ( $value ['offer_action'] == 'cart_prod_attribute_is' || $value ['offer_action'] == 'cart_prod_attribute_not_is' ) {
															$offer_rule_product_attributes_slug = explode(',', $value ['offer_rule_value']);
															$offer_rule_product_attribute_terms = explode(',', $value ['cart_prod_attribute_term']);

															foreach ( $offer_rule_product_attributes_slug as $key => $value ) {
																if ( !empty( $value ) ) {
																	foreach ( $offer_rule_product_attribute_terms as $t_key => $t_value ) {
																		$args = array(
																						'search' => $t_value,
																						'hide_empty' => 0
																					);
																		$attribute = get_term_by( 'slug', $t_value, $value );
																		if ( empty( $attribute ) && !is_object( $attribute ) ) {
																			continue;
																		}
																	}
																	if ( $attribute && is_object( $attribute ) ) {
																		echo '<option value="' . $attribute->slug . '"' . selected( true, true, false ) . '>' . $attribute->name . '</option>';
																	}
																}
															}
														} else {
															echo '<option value="" ></option>';
														}
													?>
												</select>
											</p>
											<input type="hidden" name="and_or[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $and_or ); ?>">
										</div>
										<?php
											$and_or = 'and';
											$loop ++;
									}
								?>
							</div>
							<?php
						}
					}
				}
				?>
				<script type="text/javascript">
					jQuery(function() {
						jQuery('select.role').trigger('change');
					});
				</script>
			</div>
			<p id="add_new_rule_button">
				<a href="#" class="add_new_rule button button-primary"><?php _e( '+ Add New Rule', 'smart-offers' ); ?></a>
			</p>
		</div>
	</div>
</div>
