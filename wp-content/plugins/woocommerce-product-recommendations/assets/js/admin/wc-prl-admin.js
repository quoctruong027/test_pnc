/* global Options_Stack */

;( function( $, window, document ) {

	$.fn.prl_scripts = function() {

		var $this = $( this );

		$this.sw_select2();
		$this.sw_enhanced_weight();
		$this.sw_enhanced_attribute();

		$this.find( '.woocommerce-help-tip, .help_tip, .tips' ).tipTip( {
			'attribute' : 'data-tip',
			'fadeIn' : 50,
			'fadeOut' : 50,
			'delay' : 200
		} );
	};

	$.fn.maybe_disable_weight_selector = function() {

		var $container = $( this ),
			$options   = $container.find( '.os_row' );

		if ( $options.length === 2 ) { // 1 + 1 add
			$options.first().find( '.sw-enhanced-weight' ).addClass( 'disabled' );
		} else {
			$options.find( '.sw-enhanced-weight' ).removeClass( 'disabled' );
		}
	};

	$.fn.sw_enhanced_weight = function() {

		var $weight_selects = $( this ).find( '.sw-enhanced-weight' );

		if ( ! $weight_selects.length ) {
			return;
		}

		$weight_selects.each( function() {

			var $container        = $( this ),
				$points_container = $container.find( '.points' ),
				$points           = $container.find( 'span' ),
				$weight           = $points_container.find( 'input' ),
				weight            = parseInt( $weight.val(), 10 );

			if ( ! $weight ) {
				return;
			}

			var $dec_btn = $container.find( '.dec' ),
				$inc_btn = $container.find( '.inc' );

			$dec_btn.on( 'click', function() {

				if ( weight > 1 ) {
					weight = weight - 1;
					$points.each( function( index ) {
						if ( weight >= index + 1 ) {
							$( this ).addClass( 'active' );
						} else {
							$( this ).removeClass( 'active' );
						}
					} );
				}

				$weight.val( weight );
			} );

			$inc_btn.on( 'click', function() {

				if ( weight < 5 ) {
					weight = weight + 1;
					$points.each( function( index ) {
						if ( weight >= index + 1 ) {
							$( this ).addClass( 'active' );
						} else {
							$( this ).removeClass( 'active' );
						}
					} );
				}
				$weight.val( weight );
			} );

		} );
	};

	$.fn.sw_enhanced_attribute = function() {
		var $attribute_containers = $( this ).find( '.os_value--attribute' );

		if ( ! $attribute_containers.length ) {
			return;
		}

		var block_params = {
			message: 	null,
			overlayCSS: {
				background: '#fff',
				opacity: 	0.6,
				borderRadius: '4px'
			}
		};

		$attribute_containers.each( function() {
			var $attribute_container = $( this ),
				$container           = $attribute_container.closest( '.os_row' ),
				$terms_selector      = $container.find( '.os_value--attribute select.multiselect' ),
				current_attribute    = $attribute_container.find( 'select.prl_attribute_selector' ).val();

			$attribute_container

				.on( 'change', 'select.prl_attribute_selector', function() {

					var data = {
						action: 	'woocommerce_prl_json_get_attribute_terms',
						taxonomy:   $( this ).val(),
						security:   wc_prl_admin_params.attributes_form_nonce
					};

					$container.block( block_params );

					$.ajax( {
						url: wc_prl_admin_params.wc_ajax_url,
						data: data,
						method: 'GET',
						dataType: 'json',
						success: function( response ) {

							$terms_selector.children().remove();
							$terms_selector.selectSW( {
								data: response
							} );

							$container.unblock();
						},
						error: function() {
							alert( wc_prl_admin_params.i18n_attributes_form_session_expired );
							$container.unblock();
						}
					} );

				} )

				// Select all/none.
				.on( 'click', '.os_value--tools .select_all', function() {
					var $select_field = $( this ).closest( '.os_value' );
					$select_field.find( 'select.multiselect option' ).prop( 'selected', 'selected' );
					$select_field.find( 'select.multiselect' ).trigger( 'change' );
					return false;
				} )

				.on( 'click', '.os_value--tools .select_none', function() {
					var $select_field = $( this ).closest( '.os_value' );
					$select_field.find( 'select.multiselect option' ).removeAttr( 'selected' );
					$select_field.find( 'select.multiselect' ).trigger( 'change' );
					return false;
				} )
		} );
	};

	function Options_Stack( $type_select, $data_container, templates ) {
		var os = this;

		// Validate input.
		if ( ! $type_select.length || ! $data_container.length ) {
			return;
		}

		// This is a Class, it needs to be instanciated.
		if ( ! ( this instanceof Options_Stack ) ) {
			return new Options_Stack( $type_select, $data_container, templates );
		}

		// TEMPLATE NAMES.
		this.templates = $.extend( {
			row: 'wc_prl_engine_%%type%%_filter_row',
			add: 'wc_prl_engine_%%type%%_filter_add_content',
			row_content: 'wc_prl_engine_%%type%%_filter_%%os_id%%_content',
		}, templates );

		// TEMPLATE CACHES.
		this.os_add_templates         = {};
		this.os_row_templates         = {};
		this.os_row_content_templates = {};

		// DOM CACHES.
		this.$type_select    = $type_select;
		this.$data_container = $data_container.length > 1 ? $data_container.first() : $data_container;
		this.$add_container  = this.$data_container.find( '.os_add' );
		this.$add_os_select  = this.$data_container.find( '.os_add select.os_type' );
		this.$list           = this.$data_container.find( '#os-list' );
		this.$boarding       = this.$data_container.find( '.os_boarding' );

		// Runtime.
		this.options_count   = parseInt( this.$list.find( '.os_row' ).length, 10 );

		// Check the DOM.
		if ( ! this.$add_container.length || ! this.$add_os_select.length || ! this.$list.length ) {
			return;
		}

		// Init list.
		this.$list.prl_scripts();

		this.$data_container.on( 'change_type', function() {

			var engine_type      = os.$type_select.val(),
				os_add_template  = os.get_os_add_template( engine_type ),
				$os_add_template = os_add_template();

			// Replace add content.
			os.$add_container.html('').append( $os_add_template );

			// Reset list.
			os.reset_options( $data_container );
		} );

		this.$data_container

			// Clear tiptip.
			.on( 'mousedown', '.os_remove .trash', function () {
				$( this ).triggerHandler( 'mouseleave' );
			} )

			// Option Remove.
			.on( 'click', '.os_remove .trash', function ( e ) {
				e.preventDefault();
				$( this ).closest( '.os_row' ).remove();
				os.$data_container.trigger( 'os_remove' );
				os.options_count--;
				os.maybe_add_boarding();
				return true;
			} )

			// Modifier change.
			.on( 'change', '.os_modifier select', function () {

				var $modifier      = $( this ),
					modifier       = $modifier.val();
					$parent        = $modifier.closest( '.os_row_inner' ),
					$value_options = $parent.find( '.os_value div[data-modifiers]' );

				if ( $value_options.length ) {
					// This value depends on modifier select.

					$value_options.hide();
					$value_options.each( function() {

						var $value_option = $( this ),
							modifiers     = $value_option.data( 'modifiers' ).split( ',' );

						if ( modifiers && -1 !== modifiers.indexOf( modifier ) ) {
							$value_option.show();
						}
					} );
				}
			} )

			// Option Add.
			.on( 'change', '.os_add select.os_type', function () {

				var $selector   = $( this ),
					os_id       = $selector.val(),
					engine_type = os.$type_select.val();

				if ( 'add' === os_id ) {
					return false;
				}

				var os_index                = parseInt( os.$data_container.attr( 'data-os_count' ), 10 ) + 1,
					os_post_name            = os.$data_container.attr( 'data-os_post_name' ),
					os_row_template         = os.get_os_row_template( engine_type ),
					os_row_content_template = os.get_os_row_content_template( engine_type, os_id );

				if ( ! os_row_template || ! os_row_content_template ) {
					return false;
				}

				var $new_os_row_content = os_row_content_template( {
					os_post_name: os_post_name, // This will silently fail if there is no need for multi edits.
					os_index:     os_index
				} );

				var $new_os_row = os_row_template( {
					os_index:     os_index,
					os_content:   $new_os_row_content
				} );

				os.$data_container.attr( 'data-os_count', os_index );

				os.$list.append( $new_os_row );

				var $added = os.$list.find( '.os_row' ).last();

				// We have to make the appropriate condition_id selected in the condition_type select.
				$added.find( '.os_type option[value="' + os_id + '"]' ).prop( 'selected', 'selected' );

				os.$data_container.trigger( 'os_added' );
				$added.prl_scripts();
				os.options_count++;
				os.maybe_add_boarding();

				// Change add_filter select back to placeholder.
				$selector.find( 'option[value="add"]' ).prop( 'selected', 'selected' );

				return false;
			} );

		this.$list
			// Option Change.
			.on( 'change', 'select.os_type', function () {

				var $selector    = $( this ),
					os_id        = $selector.val(),
					$option      = $selector.closest( '.os_row' ),
					os_post_name = os.$data_container.attr( 'data-os_post_name' ),
					os_index     = $option.data( 'os_index' ),
					engine_type  = os.$type_select.val();

				var os_row_content_template = os.get_os_row_content_template( engine_type, os_id );

				if ( ! os_row_content_template ) {
					return false;
				}

				var $new_os_row_content = os_row_content_template( {
					os_post_name: os_post_name, // This will silently fail if there is no need for multi edits.
					os_index:     os_index
				} );

				$option.find( '.os_content' ).html( $new_os_row_content );

				os.$data_container.trigger( 'os_changed' );
				$option.prl_scripts();

				return false;
			} )

			// Select all/none.
			.on( 'click', '.os_select_all', function() {
				$( this ).closest( '.select-field' ).find( '> select option' ).prop( 'selected', 'selected' );
				$( this ).closest( '.select-field' ).find( '> select' ).trigger( 'change' );
				return false;
			} )

			.on( 'click', '.os_select_none', function() {
				$( this ).closest( '.select-field' ).find( '> select option' ).removeAttr( 'selected' );
				$( this ).closest( '.select-field' ).find( '> select' ).trigger( 'change' );
				return false;
			} );
	}

	Options_Stack.prototype = ( function() {

		var reset_options = function() {
			this.$list.html('');
			this.$data_container.attr( 'data-filters_count', 0 );
			this.options_count = 0;
			this.maybe_add_boarding();
		};

		var maybe_add_boarding = function() {

			if ( this.options_count == 0 ) {
				this.$list.addClass( 'hidden' );
				this.$boarding.addClass( 'active' );
				this.$add_container.addClass( 'os_add--boarding' );
			} else {
				this.$list.removeClass( 'hidden' );
				this.$boarding.removeClass( 'active' );
				this.$add_container.removeClass( 'os_add--boarding' );
			}
		};

		var get_os_row_template = function( type ) {

			var template = false;

			type = type.toLowerCase();

			if ( typeof( this.os_row_templates[ type ] ) === 'function' ) {
				template = this.os_row_templates[ type ];
			} else {

				var name = this.templates.row;

				name     = name.replace( '%%type%%', type );
				template = wp.template( name );

				this.os_row_templates[ type ] = template;
			}

			return template;
		};

		var get_os_row_content_template = function( type, os_id ) {

			var template = false;

			type = type.toLowerCase();

			if ( typeof( this.os_row_content_templates[ type ] ) === 'object' && typeof( this.os_row_content_templates[ type ][ os_id ] ) === 'function' ) {
				template = this.os_row_content_templates[ type ][ os_id ];
			} else {

				var name = this.templates.row_content;

				name     = name.replace( '%%type%%', type );
				name     = name.replace( '%%os_id%%', os_id );
				template = wp.template( name );

				if ( typeof( this.os_row_content_templates[ type ] ) === 'undefined' ) {
					this.os_row_content_templates[ type ] = {};
				}
				this.os_row_content_templates[ type ][ os_id ] = template;
			}

			return template;
		};

		var get_os_add_template = function( type ) {

			var template = false;

			type = type.toLowerCase();

			if ( typeof( this.os_add_templates[ type ] ) === 'function' ) {
				template = this.os_add_templates[ type ];
			} else {

				var name = this.templates.add;

				name     = name.replace( '%%type%%', type );
				template = wp.template( name );

				this.os_add_templates[ type ] = template;
			}

			return template;
		};

		return {
			reset_options: reset_options,
			get_os_row_template: get_os_row_template,
			get_os_add_template: get_os_add_template,
			get_os_row_content_template: get_os_row_content_template,
			maybe_add_boarding: maybe_add_boarding
		};

	} () );


	// Run it on document ready.
	$( function() {

		try {

			$( document.body )

				.on( 'sw-select2-init', function() {

					// Ajax Engine search.
					$( '.wc-engine-search' ).filter( ':not(.enhanced)' ).each( function() {

						var $this            = $( this ),
							$type_select     = $this.closest( '.sw-form-content' ).find( 'input.locations_type_select' ),
							$conditions_list = $this.closest( '.sw-form' ).find( '#os-list' );

						var select2_args = {
							allowClear:  $this.data( 'allow_clear' ) ? true : false,
							placeholder: $this.data( 'placeholder' ),
							minimumInputLength: $this.data( 'minimum_input_length' ) ? $this.data( 'minimum_input_length' ) : '3',
							escapeMarkup: function( m ) {
								return m;
							},
							ajax: {
								url:         wc_prl_admin_params.wc_ajax_url,
								dataType:    'json',
								delay:       250,
								data:        function( params ) {
									return {
										term         : params.term,
										action       : $this.data( 'action' ) || 'woocommerce_prl_json_search_engines',
										security     : wc_prl_admin_params.search_engine_nonce,
										exclude      : $this.data( 'exclude' ),
										filter_type  : $this.data( 'filter_type' ),
										include      : $this.data( 'include' ),
										limit        : $this.data( 'limit' ),
										display_stock: $this.data( 'display_stock' )
									};
								},
								processResults: function( data ) {
									var terms = [];
									if ( data ) {
										$.each( data, function( id, info ) {
											terms.push( {
												id: id,
												text: info.text,
												engine_type: info.type
											} );
										} );
									}
									return {
										results: terms
									};
								},
								cache: true
							}
						};

						$this
							.selectSW( select2_args )
							.addClass( 'enhanced' )
							// Add warning on changing type.
							.on( 'select2:selecting', function( e ) {

								if ( ! $type_select.length ) {
									return;
								}

								var data = e.params.args.data;

								if ( data.engine_type !== $type_select.val() ) {
									if ( $.trim( $conditions_list.html() ) ) {
										if ( ! window.confirm( wc_prl_admin_params.i18n_change_type_conditions_warning ) ) {
											return false;
										}
									}
								}
							} )
							.on( 'select2:select', function( e ) {

								if ( ! $type_select.length ) {
									return;
								}

								var data = e.params.data;

								if ( data.engine_type !== $type_select.val() ) {
									$type_select.val( data.engine_type ).trigger( 'change' );
								}

							} );

					} );
				} )

				// Init -- Chain.
				.trigger( 'sw-select2-init' );

		} catch( err ) {
			// If select2 failed (conflict?) log the error but don't stop other scripts breaking.
			window.console.log( err );
		}

		// Engine list table.
		var $engine_list = $( 'body.post-type-prl_engine.edit-php' );

		if ( $engine_list.length ) {

			$( '.button-disabled' ).on( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
			} );

			$( '.wc-action-button-regenerate:not(.button-disabled)' ).on( 'click', function( e ) {
				e.preventDefault();

				var $this = $( this );

				var data = {
					action: 	'woocommerce_prl_regenerate_engine',
					engine_id:  parseInt( $this.attr( 'id' ), 10 ),
					security:   wc_prl_admin_params.regenerate_engine_nonce
				};

				$.ajax( {
					url: wc_prl_admin_params.wc_ajax_url,
					data: data,
					method: 'POST',
					dataType: 'json',
					complete: function() {
						// ...
					},
					success: function( response ) {

						if ( response.errors.length > 0 ) {
							window.alert( response.errors.join( '\n\n' ) );
						} else {
							window.alert( wc_prl_admin_params.i18n_engine_regeneration );
						}

					},
					error: function() {
						// Session expired (Returns 403).
						window.alert( wc_prl_admin_params.i18n_toggle_session_expired );
					}
				} );
			} );
		}

		// Engine CPT config.
		var $engine_data = $( '#wc-prl-engine-data' ),
			$type_select,
			templates;

		if ( $engine_data.length ) {

			var options_stacks = {}, i, changed;

			$type_select = $( '#prl_engine_type' );

			/*
			 * Filters
			 */
			var $filters_data_container = $( '.wc-prl-filters-container' );

			templates = {
				row: 'wc_prl_engine_%%type%%_filter_row',
				add: 'wc_prl_engine_%%type%%_filter_add_content',
				row_content: 'wc_prl_engine_%%type%%_filter_%%os_id%%_content'
			};
			// Init OS.
			options_stacks[ 'filters' ] = new Options_Stack( $type_select, $filters_data_container, templates );

			/*
			 * Amplifiers
			 */
			var $amps_data_container = $( '.wc-prl-amplifiers-container' );

			templates = {
				row: 'wc_prl_engine_%%type%%_amplifier_row',
				add: 'wc_prl_engine_%%type%%_amplifier_add_content',
				row_content: 'wc_prl_engine_%%type%%_amplifier_%%os_id%%_content'
			};
			// Init OS.
			options_stacks[ 'amplifiers' ] = new Options_Stack( $type_select, $amps_data_container, templates );

			// Init our weight selector.
			options_stacks[ 'amplifiers' ].$data_container.sw_enhanced_weight();
			options_stacks[ 'amplifiers' ].$data_container.maybe_disable_weight_selector();
			options_stacks[ 'amplifiers' ].$data_container.on( 'os_remove os_added os_changed', function() {
				$( this ).maybe_disable_weight_selector();
			} );

			/*
			 * Type select callbacks.
			 */
			var current_type    = $type_select.val(),
				$type_assistant = $( '.engine_type_assistant' ),
				$locations      = $type_assistant.length ? $type_assistant.find( '.engine_type_assistant__locations span' ) : false;

			if ( $locations && $locations.length ) {
				$locations.each( function() {
					var $loc = $( this );
					if ( -1 !== $loc.data( 'engine_type' ).indexOf( current_type ) ) {
						$loc.fadeIn( 'fast' );
					} else {
						$loc.hide();
					}
				} );
			}

			$type_select.on( 'change', function() {

				var engine_type = $( this ).val();

				if ( current_type === engine_type ) {
					return false;
				}

				// Confirm if there are filters or amps.
				changed = true;
				for ( i in options_stacks ) {
					if ( $.trim( options_stacks[ i ].$list.html() ) ) {
						if ( ! window.confirm( wc_prl_admin_params.i18n_change_type_warning ) ) {
							$( this ).val( current_type );
							changed = false;
						}
						break;
					}
				}

				if ( changed ) {
					current_type = engine_type;
					for ( i in options_stacks ) {
						options_stacks[ i ].$data_container.trigger( 'change_type' );
					}

					if ( $locations && $locations.length ) {
						$locations.each( function() {
							var $loc = $( this );
							if ( -1 !== $loc.data( 'engine_type' ).indexOf( engine_type ) ) {
								$loc.fadeIn( 'fast' );
							} else {
								$loc.hide();
							}
						} );
					}
				}

			} );

		} // End of engine data.

		// Boarding deploy.
		var $deploy_data = $( '#wc-prl-deploy-data' );

		if ( $deploy_data.length ) {

			var conditions_options_stack;

			$type_select = $( '#prl_engine_type' );

			/*
			 * Conditions on single deploy.
			 */
			var $conditions_data_container = $( '.wc-prl-conditions-container' );

			templates = {
				row: 'wc_prl_engine_%%type%%_condition_row',
				add: 'wc_prl_engine_%%type%%_condition_add_content',
				row_content: 'wc_prl_engine_%%type%%_condition_%%os_id%%_content'
			};

			// Init OS.
			conditions_options_stack = new Options_Stack( $type_select, $conditions_data_container, templates );

			// Disable save button on click.
			var $save_button = $deploy_data.find( '#sw-button-primary' );
			$save_button.on( 'click', function() {
				var $this = $( this );
				setTimeout( function() {
					$this.attr( 'disabled', 'true' );
				}, 0 );
				return true;
			} );

		} // End of deploy data.

		var $quick_deploy = $( '.quick-deploy__search' );

		if ( $quick_deploy.length ) {

			var action         = $quick_deploy.data( 'action' ),
				$engine_select = $quick_deploy.find( 'select' );

			$engine_select.change( function() {

				// Parse selected value.
				var engine_id = parseInt( $( this ).val(), 10 );

				// Redirect.
				action          = action.replace( '%%engine_id%%', engine_id );
				window.location = action;
			} );
		}

		var $deployments_data = $( '#deployments-table' );

		if ( $deployments_data.length ) {

			$( '.wc-action-button.delete, .row-actions .delete' ).on( 'click', function( e ) {
				if ( ! window.confirm( wc_prl_admin_params.i18n_delete_deployment_warning ) ) {
					e.preventDefault();
					return false;
				}
			} );

			$( '.wc-action-button-regenerate, .row-actions .regenerate a' ).on( 'click', function( e ) {
				e.preventDefault();

				var $this = $( this );

				var data = {
					action: 	'woocommerce_prl_regenerate_deployment',
					engine_id:  parseInt( $this.attr( 'id' ), 10 ),
					security:   wc_prl_admin_params.regenerate_deployment_nonce
				};

				$.ajax( {
					url: wc_prl_admin_params.wc_ajax_url,
					data: data,
					method: 'POST',
					dataType: 'json',
					complete: function() {
						// ...
					},
					success: function( response ) {

						if ( response.errors.length > 0 ) {
							window.alert( response.errors.join( '\n\n' ) );
						} else {
							window.alert( wc_prl_admin_params.i18n_deployment_regeneration );
						}

					},
					error: function() {
						// Session expired (Returns 403).
						window.alert( wc_prl_admin_params.i18n_toggle_session_expired );
					}
				} );
			} );
		}

		var $deployments_locations = $( '.wc-prl-deployments' );

		if ( $deployments_locations.length ) {

			var $deployments_wrapper = $( '.wc-metaboxes' ),
				$deployments_toolbar = $deployments_locations.find( '.toolbar' ),
				$deployments_buttons = $deployments_locations.find( '.wc-prl-deployments__list__buttons' ),
				$boarding_info       = $deployments_locations.find( '.wc-prl-deployments__boarding' ),
				$deployments_tools   = $deployments_toolbar.find( '.bulk_toggle_wrapper' ),
				$count               = $( '.wc-prl-hooks .wc-prl-hooks__tab--active .current_count' ),
				os_templates         = {
					row: 'wc_prl_engine_%%type%%_condition_row',
					add: 'wc_prl_engine_%%type%%_condition_add_content',
					row_content: 'wc_prl_engine_%%type%%_condition_%%os_id%%_content'
				};

			// Init an OS instance foreach deployment in the list.
			$deployments_wrapper.find( '.wc-metabox' ).each( function() {

				var $this                      = $( this ),
					$type_select               = $this.find( '.locations_type_select' ),
					$conditions_data_container = $this.find( '.wc-prl-conditions-container' );

				$type_select.on( 'change', function() {
					$conditions_data_container.trigger( 'change_type' );
				} );

				// Init OS.
				// TODO: This will create zombie instances when editing deployments.
				new Options_Stack( $type_select, $conditions_data_container, os_templates );

			} );


			// TOOLBAR.
			$deployments_toolbar

				.on( 'click', '.expand_all', function() {
					$deployments_wrapper.find( '.wc-metabox' ).each( function() {

						var $this = $( this );

						$this.find( '.wc-metabox-content' ).show();
						$this.addClass( 'open' ).removeClass( 'closed' );
					} );

					return false;
				} )

				.on( 'click', '.close_all', function() {
					$deployments_wrapper.find( '.wc-metabox' ).each( function() {

						var $this = $( this );

						$this.find( '.wc-metabox-content' ).hide();
						$this.addClass( 'closed' ).removeClass( 'open' );
					} );

					return false;
				} );

			$deployments_locations

				.on( 'click', '.wc-metabox > h3', function( e ) {

					if ( e.target.id === 'active-toggle' || e.target.id === 'remove_row' ) {
						return false;
					}

					var $this = $( this );
					$this.parent( '.wc-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );
					$this.next( '.wc-metabox-content' ).stop().slideToggle( 300 );
				} )

				.on( 'click', '#remove_row', function( e ) {

					var $this   = $( this ),
						$parent = $this.closest( '.wc-prl-deployments__row' );

					if ( ! $parent.hasClass( 'wc-prl-deployments__row--added' ) ) {

						if ( ! window.confirm( wc_prl_admin_params.i18n_delete_deployment_warning ) ) {
							e.preventDefault();
							return false;
						}
					}

					// AJAX auto save.
					var deployment_id = $parent.data( 'deployment_id' ),
						block_params  = {
						message: 	null,
						overlayCSS: {
							background: '#fff',
							opacity: 	0.6
						}
					};

					$deployments_wrapper.block( block_params );

					var data = {
						action: 		'wc_prl_delete_deployment',
						deployment_id:  deployment_id,
						security: 		wc_prl_admin_params.delete_deployment_nonce
					};

					$.ajax( {
						url: wc_prl_admin_params.wc_ajax_url,
						data: data,
						method: 'POST',
						dataType: 'json',
						complete: function() {
							$deployments_wrapper.unblock();
						},
						success: function( response ) {

							if ( response.errors.length > 0 ) {
								window.alert( response.errors.join( '\n\n' ) );
							} else {
								$parent.remove();
								update_row_indexes();
								update_status_count( false );

								// Toggle boarding if needed.
								$last_deployment = $deployments_wrapper.find( '.wc-prl-deployments__row' ).last();
								if ( ! $last_deployment.length ) {
									// No deployments.
									$boarding_info.removeClass( 'wc-prl-deployments__boarding--hidden' );
									$deployments_buttons.addClass( 'wc-prl-deployments__list__buttons--empty' );
								}
							}

						},
						error: function() {
							// Session expired (Returns 403).
							window.alert( wc_prl_admin_params.i18n_toggle_session_expired );
						}
					} );

				} )

				.on( 'click', '#active-toggle', function() {

					var $toggler      = $( this ),
						$active_input = $toggler.closest( '.wc-metabox' ).find( '.wc-metabox-content > input.form_active' );

					// AJAX auto save.
					var $parent           = $toggler.closest( '.wc-prl-deployments__row' ),
						deployment_id     = $parent.data( 'deployment_id' );

					// New deployment after load.
					if ( $parent[0].className.indexOf( 'wc-prl-deployments__row--added' ) > -1 ) {
						toggleActiveSwitch( $toggler, $active_input );
						return false;
					}

					$toggler.addClass( 'woocommerce-input-toggle--loading' );

					var data = {
						action: 		'wc_prl_toggle_deployment',
						value:          $active_input.val(),
						deployment_id:  deployment_id,
						security: 		wc_prl_admin_params.toggle_deployment_nonce
					};

					$.ajax( {
						url: wc_prl_admin_params.wc_ajax_url,
						data: data,
						method: 'POST',
						dataType: 'json',
						complete: function() {
							$toggler.removeClass( 'woocommerce-input-toggle--loading' );
						},
						success: function( response ) {

							if ( response.errors.length > 0 ) {
								window.alert( response.errors.join( '\n\n' ) );
							} else {

								// Change the input val and update classes.
								toggleActiveSwitch( $toggler, $active_input );
							}

						},
						error: function() {
							// Session expired (Returns 403).
							window.alert( wc_prl_admin_params.i18n_toggle_session_expired );
						}
					} );

				return false;
			} )

			.on( 'click', 'button.wc-prl-deployments__add', function( e ) {

				// Do not submit.
				e.preventDefault();

				// Init vars.
				var filter_type = $deployments_wrapper.data( 'filter_type' ),
					form_index,
					index,
					$last_deployment = $deployments_wrapper.find( '.wc-prl-deployments__row' ).last();

				if ( ! $last_deployment.length ) {
					// No deployments.
					form_index = 0;
					index      = 0;
				} else {
					form_index = parseInt( $last_deployment.find( 'input.form_index' ).val(), 10 ) + 1;
					index      = parseInt( $last_deployment.attr( 'data-index' ), 10 );
				}

				var block_params = {
					message: 	null,
					overlayCSS: {
						background: '#fff',
						opacity: 	0.6
					}
				};

				$deployments_wrapper.block( block_params );

				var data = {
					action:      'wc_prl_add_deployment',
					form_index:  form_index,
					index:       index,
					filter_type: filter_type,
					security:    wc_prl_admin_params.add_deployment_nonce
				};

				$.ajax( {
					url: wc_prl_admin_params.wc_ajax_url,
					data: data,
					method: 'POST',
					dataType: 'json',
					complete: function() {
						$deployments_wrapper.unblock();
					},
					success: function( response ) {

						if ( response.errors.length > 0 ) {
							window.alert( response.errors.join( '\n\n' ) );
						} else {

							var $new_deployment = $( response.markup );

							// Hide boarding if shown.
							if ( ! $boarding_info.hasClass( 'wc-prl-deployments__boarding--hidden' ) ) {
								$boarding_info.addClass( 'wc-prl-deployments__boarding--hidden' );
								$deployments_buttons.removeClass( 'wc-prl-deployments__list__buttons--empty' );
							}

							$deployments_wrapper.append( $new_deployment );
							$new_deployment.prl_scripts();

							// Init OS.
							var $type_select               = $new_deployment.find( '.locations_type_select' ),
								$conditions_data_container = $new_deployment.find( '.wc-prl-conditions-container' ),
								$engine_select             = $new_deployment.find( '.wc-engine-search' );

							// Remove no-engine boarding.
							$engine_select.one( 'change', function() {
								$new_deployment.find( '.sw-form' ).removeClass( 'sw-form--no-engine' );
							} );

							// Clear conditions on type change.
							$type_select.on( 'change', function() {
								$conditions_data_container.trigger( 'change_type' );
							} );

							new Options_Stack( $type_select, $conditions_data_container, os_templates );

							update_status_count( true );
						}

					},
					error: function() {
						// Session expired (Returns 403).
						window.alert( wc_prl_admin_params.i18n_toggle_session_expired );
					}
				} );

			} );

			$deployments_wrapper
				.on( 'keyup', 'input.form_deployment_title', function() {
					var $this = $( this );
					$this.closest( '.wc-metabox' ).find( 'h3 .deployment_title_inner' ).text( $this.val() );
				} );

			// Component ordering.
			$deployments_wrapper.sortable( {
				items: '.wc-prl-deployments__row',
				cursor: 'move',
				axis: 'y',
				handle: '.sort-item',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'wc-metabox-sortable-placeholder',
				start: function( event, ui ) {
					ui.item.css( 'background-color','#f6f6f6' );
				},
				stop: function( event, ui ) {
					ui.item.removeAttr( 'style' );
					update_row_indexes();
				}
			} );

			/**
			 * Update row indexes.
			 */
			var update_row_indexes = function() {
				$deployments_wrapper.find( '.wc-prl-deployments__row' ).each( function( index, el ) {
					var i = index + 1;
					$( el ).attr( 'data-index', i );
					$( '.form_display_order', el ).val( i );
					$( '.deployment_title_index', el ).html( i );
				} );
			};

			/**
			 * Update count.
			 */
			var update_status_count = function( inc ) {
				var count = parseInt( $count.html(), 10 );

				if ( true === inc ) {
					count++;
				} else {
					count--;
				}

				if ( count > 0 ) {
					$deployments_tools.removeClass( 'disabled' );
				} else {
					$deployments_tools.addClass( 'disabled' );
				}

				$count.html( count );
			};

			/**
			 * Toggle switch class and input value.
			 */
			var toggleActiveSwitch = function( $toggler, $input ) {

				if ( 'on' === $input.val() ) {
					// Disable restriction.
					$input.val( 'off' );
					$toggler.removeClass( 'woocommerce-input-toggle--enabled' ).addClass( 'woocommerce-input-toggle--disabled' );
				} else {
					// Enable restriction.
					$input.val( 'on' );
					$toggler.removeClass( 'woocommerce-input-toggle--disabled' ).addClass( 'woocommerce-input-toggle--enabled' );
				}
			};
		}

	} );

} )( jQuery, window, document );
