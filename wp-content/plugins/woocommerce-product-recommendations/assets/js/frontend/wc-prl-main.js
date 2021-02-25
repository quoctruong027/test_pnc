/* global WC_PRL */

;( function( $, window ) {

	// Namespace.
	WC_PRL         = window.WC_PRL || {};
	// Caches.
	WC_PRL.$window = $( window );
	WC_PRL.params  = window.wc_prl_params;

	// Modules.
	/* global WC_PRL */

	WC_PRL.cookies = WC_PRL.cookies || {};

	WC_PRL.cookies.model = ( function() {

		var session_timeout = WC_PRL.params.shopping_session_seconds * 1000; // Transform to ms.

		function set( cname, cvalue, timeout ) {

			var d,
				expires;

			if ( typeof timeout === 'undefined' ) {
				timeout = session_timeout;
			}

			// Calc expiration.
			d = new Date();
			d.setTime( d.getTime() + timeout );
			expires = 'expires=' + d.toUTCString();

			// Set cookie.
			document.cookie = cname + '=' + cvalue + '; ' + expires + '; path=/';
		}

		function remove( cname ) {
			document.cookie = cname + '=; expires=Thu, Jan 01 1970 00:00:00 UTC;path=/';
		}

		function get( cname ) {

			var name          = cname + '=',
				decodedCookie = decodeURIComponent( document.cookie ),
				ca            = decodedCookie.split( ';' ),
				i;

			for ( i = 0; i < ca.length; i++ ) {

				var c = ca[ i ];

				while ( ' ' === c.charAt( 0 ) ) {
					c = c.substring( 1 );
				}

				if ( 0 === c.indexOf( name ) ) {
					return c.substring( name.length, c.length );
				}
			}

			return '';
		}

		function refresh( cname ) {
			var value = get( cname );
			set( cname, value );
		}

		return {
			set: set,
			get: get,
			remove: remove,
			refresh: refresh
		};

	} )();

	WC_PRL.cookies.views = ( function( model ) {

		// Cached instance.
		var instance;

		/**
		 * Deployment Views singleton.
		 *
		 * Holds a static instance that returns the same object.
		 */
		function WC_PRL_Deployment_Views_Cookie() {

			// Make sure is called as a constructor.
			if ( ! ( this instanceof WC_PRL_Deployment_Views_Cookie ) ) {
				return new WC_PRL_Deployment_Views_Cookie();
			}

			if ( instance ) {
				return instance;
			}

			// The instance.
			instance = this;

			// The properties.
			this.cookie_raw = '';
			this.viewed_ids = [];
		}

		WC_PRL_Deployment_Views_Cookie.prototype.init = function() {
			// Copy value to memory.
			this.cookie_raw = model.get( 'wc_prl_deployments_viewed' );

			if ( '' !== this.cookie_raw ) {
				this.viewed_ids = this.cookie_raw.split( ',' );
				// Refresh the shopping session.
				model.set( 'wc_prl_deployments_viewed', this.viewed_ids.join( ',' ) );
			}
		};

		WC_PRL_Deployment_Views_Cookie.prototype.is_viewed = function( id ) {
			return ( -1 !== this.viewed_ids.indexOf( id ) );
		};

		WC_PRL_Deployment_Views_Cookie.prototype.add_deployment = function( id ) {

			// Search if already exists.
			if ( -1 === this.viewed_ids.indexOf( id ) ) {

				if ( this.viewed_ids.length > WC_PRL.params.views_max_cookie_num - 1 ) {
					this.viewed_ids.splice( 0, 1 );
				}

				// If it's new, add to memory and update cookie.
				this.viewed_ids.push( id );

				// Refresh shopping session.
				model.set( 'wc_prl_deployments_viewed', this.viewed_ids.join( ',' ) );
			}
		};

		return WC_PRL_Deployment_Views_Cookie;

	} )( WC_PRL.cookies.model );

	WC_PRL.cookies.recently_viewed = ( function( model ) {

		// Cached instance.
		var instance,
			limit = WC_PRL.params.recently_views_max_cookie_num;

		/**
		 * Recently Viewed Cookie singleton.
		 *
		 * Holds a static instance that returns the same object.
		 */
		function WC_PRL_Recently_Viewed_Cookie() {

			// Make sure is called as a constructor.
			if ( ! ( this instanceof WC_PRL_Recently_Viewed_Cookie ) ) {
				return new WC_PRL_Recently_Viewed_Cookie();
			}

			if ( instance ) {
				return instance;
			}

			// The instance.
			instance = this;

			// The properties.
			this.cookie_raw     = '';
			this.viewed_ids     = [];
			this.viewed_cat_ids = [];
			this.viewed_tag_ids = [];
		}

		WC_PRL_Recently_Viewed_Cookie.prototype.init = function() {

			// Copy value to memory.
			this.cookie_raw = model.get( 'wc_prl_recently_viewed' );

			if ( '' !== this.cookie_raw ) {

				// De-construct cookie.
				// product_ids|...,cat_ids|...,tag_ids|...
				var parts = this.cookie_raw.split( ',' );

				if ( parts.length ) {

					this.viewed_ids = $.map( parts[ 0 ].split( '|' ), function( value ) {
						return parseInt( value, 10 );
					} );

					// If categories.
					if ( parts.length > 1 ) {
						this.viewed_cat_ids = $.map( parts[ 1 ].split( '|' ), function( value ) {
							return parseInt( value, 10 );
						} );
					}

					// If tags.
					if ( parts.length > 2 ) {
						this.viewed_tag_ids = $.map( parts[ 2 ].split( '|' ), function( value ) {
							return parseInt( value, 10 );
						} );
					}
				}

				// Refresh the shopping session.
				this.save();
			}
		};

		WC_PRL_Recently_Viewed_Cookie.prototype.add_product_id = function( id ) {

			if ( this.viewed_ids.length > limit ) {
				this.viewed_ids.shift();
			}

			id = parseInt( id, 10 );

			// Search index.
			var index = this.viewed_ids.indexOf( id );

			if ( index > -1 ) {
				// Remove item.
				this.viewed_ids.splice( index, 1);
			}

			this.viewed_ids.push( id );
		}

		WC_PRL_Recently_Viewed_Cookie.prototype.add_category_id = function( id ) {

			if ( this.viewed_cat_ids.length > limit ) {
				this.viewed_cat_ids.shift();
			}

			id = parseInt( id, 10 );

			// Search index.
			var index = this.viewed_cat_ids.indexOf( id );

			if ( index > -1 ) {
				// Remove item.
				this.viewed_cat_ids.splice( index, 1);
			}

			this.viewed_cat_ids.push( id );
		}

		WC_PRL_Recently_Viewed_Cookie.prototype.add_tag_id = function( id ) {

			if ( this.viewed_tag_ids.length > limit ) {
				this.viewed_tag_ids.shift();
			}

			id = parseInt( id, 10 );

			// Search index.
			var index = this.viewed_tag_ids.indexOf( id );

			if ( index > -1 ) {
				// Remove item.
				this.viewed_tag_ids.splice( index, 1);
			}

			this.viewed_tag_ids.push( id );
		}

		WC_PRL_Recently_Viewed_Cookie.prototype.save = function() {

			var viewed_ids = this.viewed_ids.join( '|' ),
				cat_ids    = this.viewed_cat_ids.join( '|' ),
				tag_ids    = this.viewed_tag_ids.join( '|' );

			this.cookie_raw = viewed_ids;
			if ( cat_ids ) {
				this.cookie_raw += ',' + cat_ids;
			}
			if ( tag_ids ) {
				this.cookie_raw += ',' + tag_ids;
			}

			model.set( 'wc_prl_recently_viewed', this.cookie_raw );
		}

		return WC_PRL_Recently_Viewed_Cookie;

	} )( WC_PRL.cookies.model );

	/* global WC_PRL */

	WC_PRL.storage = ( function() {

		var ls_support = false,
			cookies    = WC_PRL.cookies.model;

		if ( localStorage ) {
			ls_support = true;
		}

		function set( key, value ) {

			if ( typeof value !== 'undefined' && value !== null ) {

				// Convert object values to JSON
				if ( typeof value === 'object' ) {
					value = JSON.stringify( value );
				}

				if ( ls_support ) { // Native support
					localStorage.setItem( key, value );
				} else { // Use Cookie
					cookies.set( key, value, 2147483647 );
				}

			} else {
				remove( key );
			}
		}

		function get( key ) {
			var data;

			if ( ls_support ) { // Native support
				data = localStorage.getItem( key );
			} else { // Use cookie
				data = cookies.get( key );
			}

			// Try to parse JSON...
			try {
				data = JSON.parse( data );
			} catch ( e ) {
				data = data;
			}

			return data;
		}

		function remove( key ) {

			if ( ls_support ) { // Native support
				localStorage.removeItem( key );
			} else { // Use cookie
				cookies.remove( key );
			}
		}

		return {
			set: set,
			get: get,
			remove: remove,
		};

	} )();

	/* global WC_PRL */

	WC_PRL.utilities = WC_PRL.utilities || {};

	WC_PRL.utilities.throttle = function( fn, wait ) {

		var time = Date.now();

		return function() {
			if ( ( time + wait - Date.now() ) < 0 ) {
				fn();
				time = Date.now();
			}
		};
	};

	WC_PRL.utilities.url = ( function() {

		function handle_tracking( url ) {

			var fragment  = url.split( '#' ),
				url_parts = fragment[ 0 ].split( '?'),
				tracking  = '';

			if ( url_parts.length >= 2 ) {

				var urlBase     = url_parts.shift(),
					queryString = url_parts.join( '?' ),
					prefix      = 'prl_track=',
					args        = queryString.split( /[&;]/g ),
					i;

				i = args.length;
				while( i-- ) {
					if ( args[ i ].lastIndexOf( prefix, 0 ) !== -1 ) {
						tracking = args[ i ].split( '=' ).pop();
						args.splice( i, 1 );
					}
				}

				url = urlBase + ( args.length > 0 ? '?' + args.join( '&' ) : '' );

				// Add hash back.
				if ( fragment[ 1 ] ) {
					url += '#' + fragment[ 1 ];
				}
			}

			return {
				url: url,
				tracking: tracking
			};
		}

		return {
			handle_tracking: handle_tracking
		};

	} )();


	WC_PRL.utilities.timer = ( function() {

		/**
		 * Deployment Clock helper.
		 *
		 * Used to keep track of the time that a deployment element is being `watched`.
		 *
		 * @param  WC_PRL.controllers.Deployment  deployment
		 * @param  int     time_limit How many points before it stops (>=$time_limit).
		 * @param  int     interval_milliseconds How many miliseconds to increase a point.
		 */
		function Timer( deployment, time_limit, interval_milliseconds ) {

			// Make sure is called as a constructor.
			if ( ! ( this instanceof Timer ) ) {
				return new Timer( deployment, time_limit, interval_milliseconds );
			}

			// Instance of WC_PRL.controllers.Deployment.
			this.deployment  = deployment;

			// Props.
			this.is_running  = false;
			this.interval    = null;
			this.interval_ms = interval_milliseconds;
			this.time        = 1;
			this.time_limit  = time_limit;
		}

		Timer.prototype.start = function() {

			if ( ! this.is_running ) {

				this.interval = setInterval( function() {

					this.time = this.time + 1;

					if ( this.time >= this.time_limit && ! this.deployment.is_viewed ) {
						this.stop();
						this.deployment.viewed();
					}

				}.bind( this ), this.interval_ms );

				// Mark as active.
				this.is_running = true;
			}
		};

		Timer.prototype.stop = function() {
			clearTimeout( this.interval );
			this.is_running = false;
			this.interval   = null;
			this.time       = 1;
		};

		return Timer;

	} )();

	WC_PRL.utilities.tracking = ( function() {

		var params              = WC_PRL.params,
			storage             = WC_PRL.storage,
			viewOffsetTop       = 200, // How much offset before it leaves the screen from top.
			viewOffsetBottom    = 200, // How much offset to enter from the bottom.
			boundingRectSupport = typeof Element.prototype.getBoundingClientRect === 'function'; // Init-time Branching.

		function nativeTrack( t, partial, clientSize, direction ) {

			var $w       = WC_PRL.$window,
				vpWidth  = $w.width(),
				vpHeight = $w.height(),
				rec      = t.getBoundingClientRect(),
				tViz     = rec.top    >= 0 && ( rec.top + viewOffsetBottom ) < vpHeight,
				bViz     = rec.bottom >  viewOffsetTop && rec.bottom <= vpHeight,
				mViz     = rec.top < 0 && rec.bottom > vpHeight,
				lViz     = rec.left   >= 0 && rec.left   <  vpWidth,
				rViz     = rec.right  >  0 && rec.right  <= vpWidth,
				vVisible = partial ? tViz || bViz || mViz : tViz && bViz,
				hVisible = partial ? lViz || rViz : lViz && rViz;

			if ( direction === 'both' ) {
				return clientSize && vVisible && hVisible;
			} else if ( direction === 'vertical' ) {
				return clientSize && vVisible;
			} else if ( direction === 'horizontal' ) {
				return clientSize && hVisible;
			}
		}

		function customTrack( $t, partial, clientSize, direction ) {

			var $w              = WC_PRL.$window,
				vpWidth         = $w.width(),
				vpHeight        = $w.height(),
				viewTop         = $w.scrollTop() + viewOffsetBottom,
				viewBottom      = viewTop + vpHeight - viewOffsetBottom - viewOffsetTop,
				viewLeft        = $w.scrollLeft(),
				viewRight       = viewLeft + vpWidth,
				offset          = $t.offset(),
				_top            = offset.top,
				_bottom         = _top + $t.height(),
				_left           = offset.left,
				_right          = _left + $t.width(),
				compareTop      = partial === true ? _bottom : _top,
				compareBottom   = partial === true ? _top : _bottom,
				compareLeft     = partial === true ? _right : _left,
				compareRight    = partial === true ? _left : _right;

			if ( direction === 'both' ) {
				return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) ) && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
			} else if ( direction === 'vertical' ) {
				return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) );
			} else if ( direction === 'horizontal' ) {
				return !!clientSize && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
			}
		}

		function is_in_viewport( $t, partial, hidden, direction ) {

			// Init vars.
			var	t             = $t.get(0),
				clientSize    = hidden === true ? t.offsetWidth * t.offsetHeight : true;

			// Force partial mode if there are any offsets.
			if ( ! partial && ( viewOffsetTop > 0 || viewOffsetBottom > 0 ) ) {
				partial = true;
			}

			direction = (direction) ? direction : 'vertical';

			if ( boundingRectSupport ) {
				// Use this native browser method, if available.
				return nativeTrack( t, partial, clientSize, direction );
			} else {
				return customTrack( $t, partial, clientSize, direction );
			}
		}

		function store_click( click_track_info ) {

			var remaining_tracking = storage.get( 'wc_prl_click_tracking' );

			if ( remaining_tracking ) {
				remaining_tracking = remaining_tracking + ',';
			} else {
				remaining_tracking = '';
			}

			storage.set( 'wc_prl_click_tracking', remaining_tracking + click_track_info );
		}

		function log_clicks() {

			var clicks_str = storage.get( 'wc_prl_click_tracking' );
			if ( ! clicks_str ) {
				return;
			}

			var clicks = clicks_str.split( ',' );

			if ( clicks.length ) {

				var i,
					click,
					value,
					ajax_data = {
						clicks: [],
						security: params.security_click_event_nonce
					},
					cookies = WC_PRL.cookies.model;

				// Check cookie.
				var cookie_clicks = cookies.get( 'wc_prl_deployments_clicked' );
				cookie_clicks     = cookie_clicks.split( ',' );

				for ( i in clicks ) {

					click = clicks[ i ].split( '_' );

					// Transform clicks to check in cookie.
					value = click[ 0 ] + '_' + click[ 3 ];
					if ( click.length === 5 ) {
						value += '_' + click[ 4 ]; // Add source.
					}

					if ( Array.isArray( cookie_clicks ) && cookie_clicks.indexOf( value ) === -1 && ajax_data.clicks.indexOf( clicks[ i ] ) === -1 ) {
						ajax_data.clicks.push( clicks[ i ] );
					}
				}

				if ( ajax_data.clicks.length ) {

					// Ajax.
					$.post( woocommerce_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'woocommerce_prl_log_click_event' ), ajax_data, function( response ) {

						if ( 'failure' === response.result ) {
							window.console.error( 'PRL Click Tracking Error: ', response );
							return;
						} else {
							// Re-Init storage.
							storage.remove( 'wc_prl_click_tracking' );
						}

					} );
				}
			}
		}

		return {
			is_in_viewport: is_in_viewport,
			store_click: store_click,
			log_clicks: log_clicks
		};

	} )();

	/* global WC_PRL */

	WC_PRL.controllers = WC_PRL.controllers || {};

	WC_PRL.controllers.Deployment = ( function() {

		// Localize scope.
		var params         = WC_PRL.params,
			is_in_viewport = WC_PRL.utilities.tracking.is_in_viewport || null,
			Timer          = WC_PRL.utilities.timer || null,
			Views_Cookie   = new WC_PRL.cookies.views();


		/**
		 * Deployment View Controller.
		 *
		 * @param jQuery $deployment
		 */
		function Deployment( $deployment ) {

			// Make sure is called as a constructor.
			if ( ! ( this instanceof Deployment ) ) {
				return new Deployment( $deployment );
			}

			// Base Properties.
			this.id             = null;
			this.engine_id      = null;
			this.location_hash  = null;
			this.source_hash    = null;

			// Run-time properties.
			this.is_viewed      = false;

			// Ajax.
			this.xhr            = false;
			this.ajax_url       = woocommerce_params.wc_ajax_url;

			// Internal timer.
			this.timer          = Timer( this, params.deployment_interest_time, 1000 );

			// Holds the jQuery instance.
			this.$deployment    = $deployment;
		}

		/*----------------------------------------------------------------*/
		/*  Controller methods.                                            */
		/*-----------------------------------------------------------------*/

		/**
		 * Getters.
		 */
		Deployment.prototype.get_id = function() {

			if ( ! this.id ) {
				var dom_id    = this.$deployment.attr( 'id' ),
					reg_id    = /([0-9]+)$/g,
					id        = parseInt( dom_id.match( reg_id ), 10 );
				this.id       = id;
			}

			return this.id;
		};

		Deployment.prototype.get_engine_id = function() {

			if ( ! this.engine_id ) {
				var engine_id  = parseInt( this.$deployment.data( 'engine' ), 10 );
				this.engine_id = engine_id;
			}

			return this.engine_id;
		};

		Deployment.prototype.get_location_hash = function() {

			if ( ! this.location_hash ) {
				var location_hash = this.$deployment.data( 'location-hash' );
				// Cache it.
				this.location_hash = location_hash;
			}

			return this.location_hash;
		};

		Deployment.prototype.get_source_hash = function() {

			if ( ! this.source_hash ) {
				var source_hash  = this.$deployment.data( 'source-hash' );
				this.source_hash = source_hash;
			}

			return this.source_hash;
		};

		/**
		 * Ajax URL.
		 */
		Deployment.prototype.get_ajax_url = function( action ) {
			return this.ajax_url.toString().replace( '%%endpoint%%', action );
		};

		/**
		 * Stops all tracking settings and sends a view event.
		 */
		Deployment.prototype.viewed = function() {

			this.is_viewed = true;
			this.timer     = null;
			if ( 'yes' === params.script_debug ) {
				this.$deployment.css( 'background', 'green' );
			}

			this.add_view_event();
		};

		/**
		 * Adds a new view event in the database through AJAX.
		 */
		Deployment.prototype.add_view_event = function() {

			var id            = this.get_id(),
				engine_id     = this.get_engine_id(),
				location_hash = this.get_location_hash(),
				source_hash   = this.get_source_hash();

			if ( ! id ) {
				return;
			}

			// Update views cookie.
			var cookie_key = source_hash ? id + '_' + source_hash : id;
			Views_Cookie.add_deployment( cookie_key );

			if ( 'yes' === params.script_debug ) {
				window.console.info( 'PRL Recording View Event: ', cookie_key );
			}

			// Store.
			if ( this.xhr ) {
				this.xhr.abort();
			}

			var data = {
				'id': id,
				'engine_id': engine_id,
				'location_hash': location_hash,
				'source_hash' : source_hash,
				'security': params.security_view_event_nonce
			};

			this.xhr = $.post( this.get_ajax_url( 'woocommerce_prl_log_view_event' ), data, function( response ) {

				if ( 'failure' === response.result ) {
					window.console.error( 'PRL View Tracking Error: ', response );
				} else if ( 'yes' === params.script_debug ) {
					window.console.info( 'PRL Recorded View Event.' );
				}

			} );
		};

		/**
		 * Checks whether the element is in viewport or not and updates the tracking settings.
		 * Runs on init and it's hooked under `scroll` event.
		 */
		Deployment.prototype.do_viewport_tracking = function() {

			if ( this.is_viewed ) {
				return;
			}

			if ( ! this.$deployment ) {
				return;
			}

			// Track.
			if ( is_in_viewport( this.$deployment, true, false, 'vertical' ) ) {
				if ( 'yes' === params.script_debug ) {
					this.$deployment.css( 'background', 'red' );
				}
				this.timer.start();
			} else {
				this.timer.stop();
				if ( 'yes' === params.script_debug ) {
					this.$deployment.css( 'background', 'transparent' );
				}
			}
		};

		return Deployment;

	} )();

	/* global WC_PRL */

	WC_PRL.update_product_history = ( function() {

		var model = WC_PRL.cookies.recently_viewed();

		function log_single_product() {

			// Check if is product single.
			var $product_container = $( 'body.single-product' );
			if ( $product_container.length ) {

				var $cart_form = $( 'form.cart' ).first(),
					$cart_btn  = $cart_form.find( '[name="add-to-cart"]' ),
					i; // Iterator.

				// Try to parse product ID.
				var product_id = parseInt( $cart_btn.val(), 10 );

				if ( ! product_id ) {
					window.console.warn( 'Could not parse the product id. Tracking bypassed...' );
					return;
				}

				// Parse class.
				var $product_wrap = $( '.product#product-' + product_id );
				if ( $product_wrap.length ) {

					model.init();

					// Get container class.
					var product_class = $product_wrap.attr( 'class' );

					// Log categories.
					var cat_regex = /wc-prl-cat-([0-9-]+)\s?/g;
					var cat_ids_match  = product_class.match( cat_regex );
					if ( cat_ids_match && cat_ids_match instanceof Array ) {
						var cat_ids = cat_ids_match.pop().replace( 'wc-prl-cat-', '' ).trim().split( '-' );
						for ( i in cat_ids ) {
							model.add_category_id( cat_ids[ i ] );
						}
					}

					// Log tags.
					var tag_regex = /wc-prl-tag-([0-9-]+)\s?/g;
					var tag_ids_match  = product_class.match( tag_regex );
					if ( tag_ids_match && tag_ids_match instanceof Array ) {
						var tag_ids = tag_ids_match.pop().replace( 'wc-prl-tag-', '' ).trim().split( '-' );
						for ( i in tag_ids ) {
							model.add_tag_id( tag_ids[ i ] );
						}
					}

					model.add_product_id( product_id );
					model.save();
				}
			}
		}

		return log_single_product;

	} )();

	/* global WC_PRL */

	WC_PRL.deployments = ( function() {

		var Views_Cookie = new WC_PRL.cookies.views(),
			store_click  = WC_PRL.utilities.tracking.store_click || null,
			$w           = WC_PRL.$window;


		function track( $deployments, ajax ) {

			if ( WC_PRL.params.tracking_enabled === 'no' ) {
				return;
			}

			$deployments = $deployments ? $deployments : $( '.wc-prl-recommendations:not(.placeholder)' );
			if ( $deployments.length ) {

				ajax = ( ajax === 'true' ) ? true : false;

				// Localized lookups.
				var Deployment       = WC_PRL.controllers.Deployment,
					throttle         = WC_PRL.utilities.throttle,
					handle_tracking  = WC_PRL.utilities.url.handle_tracking,
					is_checkout_page = $( document.body ).hasClass( 'woocommerce-checkout' );

				$deployments.each( function() {

					var $deployment = $( this ),
						deployment  = Deployment( $deployment ),
					    cookie_key  = deployment.get_source_hash() ? deployment.get_id() + '_' + deployment.get_source_hash() : deployment.get_id();

					// Track Views.
					if ( ! Views_Cookie.is_viewed( String( cookie_key ) ) ) {
						// Run once on init.
						deployment.do_viewport_tracking();
						// Throttle tracking in scrolling.
						$w.scroll( throttle( deployment.do_viewport_tracking.bind( deployment ), 200 ) );
					}

					// Track clicks when in AJAX.
					if ( ajax ) {
						$deployment.on( 'click', 'a', function( e ) {
							e.preventDefault();

							var url  = $( this ).attr( 'href' );
							if ( ! url ) {
								return;
							}

							var info = handle_tracking( url );
							store_click( info.tracking );

							var add_to_cart_link = -1 !== url.indexOf( 'add-to-cart' );
							if ( ! add_to_cart_link || 'yes' !== WC_PRL.params.ajax_add_to_cart ) {
								window.location.href = info.url;
							}
						} );
					}

					if ( is_checkout_page ) {
						// For each deployment add-to-cart in Checkout update the table.
						$deployment.on( 'click', 'a.add_to_cart_button', function( e ) {
							$( document.body ).one( 'added_to_cart', function() {
								$( document.body ).trigger( 'update_checkout' );
							} );
						} );
					}
				} );
			}
		}

		function render( $cached_locations ) {

			$cached_locations = $cached_locations ? $cached_locations : $( '.wc-prl-ajax-placeholder' );

			if ( $cached_locations.length ) {

				var locations = ( function() {

					var hooks = [];

					$cached_locations.each( function() {
						var hook = $( this ).attr( 'id' );
						hooks.push( hook );
					} );

					return hooks;

				} )();

				var env = ( function() {

					var env;

					$cached_locations.each( function() {
						var e;
						e = $( this ).attr( 'data-env' );

						// Try to parse JSON...
						try {
							env = JSON.parse( e );
						} catch ( e ) {
							env = false;
						}

						if ( env ) {
							return false; // Break;
						}

					} );

					return env;

				} )();

				var data = {
					'locations': locations.join( ',' ),
					'product': env.product ? env.product : '',
					'archive': env.archive ? env.archive : '',
					'order': env.order ? env.order : '',
					'current_url': window.location.href
				};

				$w.trigger( 'wc_prl_deployments_before_render' );

				$.post( woocommerce_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'woocommerce_prl_print_location' ), data, function( response ) {

					if ( 'failure' === response.result ) {
						window.console.error( 'PRL Deployment Render Error: ', response );
					}

					var i;
					for ( i in response.html ) {

						if ( ! response.html[ i ] ) {
							continue;
						}

						$cached_locations.each( function() {
							var $this = $( this );
							if ( i == $this.attr( 'id' ) ) {
								$this.replaceWith( response.html[ i ] );
								return false;
							}
						} );
					}

					$w.trigger( 'wc_prl_deployments_after_render' );
					WC_PRL.deployments.track( null, 'true' );

				} );
			}
		}

		return {
			track: track,
			render: render
		};

	} )();


	// Refresh shopping session for cookies.
	var refresh_cookie = WC_PRL.cookies.model.refresh;
	if ( WC_PRL.params.tracking_enabled === 'yes' ) {
		// Init view cookie.
		WC_PRL.cookies.views().init();
		// Log clicks in the storage queue.
		WC_PRL.utilities.tracking.log_clicks();
		// Refresh session for clicked deployments.
		refresh_cookie( 'wc_prl_deployments_clicked' );
	}

	// Refresh session for recently viewed items.
	refresh_cookie( 'wc_prl_recently_viewed' );

	// Main.
	$( function() {

		// Keep track of browsing history.
		WC_PRL.update_product_history();

		// HTML Cached Deployments.
		var $cached_locations = $( '.wc-prl-ajax-placeholder' );
		if ( $cached_locations.length ) {
			WC_PRL.deployments.render( $cached_locations );
		} else {
			WC_PRL.deployments.track();
		}

	} );

} )( jQuery, window );
