(function ($) {
	var PPSliderUpdate = function ($, $carousel) {
		var $containers = {
			".pp-advanced-tabs": ".pp-advanced-tabs-title",
			".pp-toggle-container": ".pp-toggle-switch",
			".elementor-tabs": ".elementor-tab-title",
		};

		$.each($containers, function (main_parent, click_element) {
			if ($($carousel).closest(main_parent).length > 0) {
				$($carousel)
					.closest(main_parent)
					.find(click_element)
					.on("click", function () {
						setTimeout(function () {
							$carousel.slick("setPosition");
						}, 100);
					});
			}
		});
	};

	var RegisterPPQuickView = function ($scope, $) {
		var scope_id = $scope.data("id");
		var quick_view_btn = $scope.find(".pp-quick-view-btn");
		var modal_wrap = $scope.find(".pp-quick-view-" + scope_id);

		modal_wrap.appendTo(document.body);

		var pp_quick_view_bg = modal_wrap.find(".pp-quick-view-bg"),
			pp_qv_modal = modal_wrap.find("#pp-quick-view-modal"),
			pp_qv_content = pp_qv_modal.find("#pp-quick-view-content"),
			pp_qv_close_btn = pp_qv_modal.find("#pp-quick-view-close"),
			pp_qv_wrapper = pp_qv_modal.find(".pp-content-main-wrapper"),
			pp_qv_wrapper_w = pp_qv_wrapper.width(),
			pp_qv_wrapper_h = pp_qv_wrapper.height();

		$scope
			.off("click", ".pp-quick-view-btn")
			.on("click", ".pp-quick-view-btn", function (e) {
				e.preventDefault();

				var $this = $(this);
				var wrap = $this.closest("li.product");
				var product_id = $this.data("product_id");

				if (!pp_qv_modal.hasClass("loading")) {
					pp_qv_modal.addClass("loading");
				}

				if (!pp_quick_view_bg.hasClass("pp-quick-view-bg-ready")) {
					pp_quick_view_bg.addClass("pp-quick-view-bg-ready");
				}

				$(document).trigger("pp_quick_view_loading");

				pp_qv_ajax_call($this, product_id);
			});

		var pp_qv_ajax_call = function (t, product_id) {
			pp_qv_modal.css("opacity", 0);

			$.ajax({
				url: pp_woo_products_script.ajax_url,
				data: {
					action: "pp_woo_quick_view",
					product_id: product_id,
				},
				dataType: "html",
				type: "POST",
				success: function (data) {
					pp_qv_content.html(data);
					pp_qv_content_height();
				},
			});
		};

		var pp_qv_content_height = function () {
			// Variation Form
			var form_variation = pp_qv_content.find(".variations_form");

			form_variation.trigger("check_variations");
			form_variation.trigger("reset_image");

			if (!pp_qv_modal.hasClass("open")) {
				pp_qv_modal.removeClass("loading").addClass("open");

				var scrollbar_width = pp_get_scrollbar_width();
				var $html = $("html");

				$html.css("margin-right", scrollbar_width);
				$html.addClass("pp-quick-view-is-open");
			}

			var var_form = pp_qv_modal.find(".variations_form");
			if (
				var_form.length > 0 &&
				"function" === typeof var_form.wc_variation_form
			) {
				var_form.wc_variation_form();
				var_form.find("select").change();
			}

			pp_qv_content.imagesLoaded(function (e) {
				var image_slider_wrap = pp_qv_modal.find(".pp-qv-image-slider");

				if (image_slider_wrap.find("li").length > 1) {
					image_slider_wrap.flexslider({
						animation: "slide",
						start: function (slider) {
							setTimeout(function () {
								pp_update_summary_height(true);
							}, 300);
						},
					});
				} else {
					setTimeout(function () {
						pp_update_summary_height(true);
					}, 300);
				}
			});

			// stop loader
			$(document).trigger("pp_quick_view_loader_stop");
		};

		var pp_qv_close_modal = function () {
			// Close box by click overlay
			pp_qv_wrapper.on("click", function (e) {
				if (this === e.target) {
					pp_qv_close();
				}
			});

			// Close box with esc key
			$(document).keyup(function (e) {
				if (e.keyCode === 27) {
					pp_qv_close();
				}
			});

			// Close box by click close button
			pp_qv_close_btn.on("click", function (e) {
				e.preventDefault();
				pp_qv_close();
			});

			var pp_qv_close = function () {
				pp_quick_view_bg.removeClass("pp-quick-view-bg-ready");
				pp_qv_modal.removeClass("open").removeClass("loading");
				$("html").removeClass("pp-quick-view-is-open");
				$("html").css("margin-right", "");

				setTimeout(function () {
					pp_qv_content.html("");
				}, 600);
			};
		};

		var pp_update_summary_height = function (update_css) {
			var quick_view = pp_qv_content,
				img_height = quick_view
					.find(".product .pp-qv-image-slider")
					.first()
					.height(),
				summary = quick_view.find(".product .summary.entry-summary"),
				content = summary.css("content");

			if (
				"undefined" != typeof content &&
				544 == content.replace(/[^0-9]/g, "") &&
				0 != img_height &&
				null !== img_height
			) {
				summary.css("height", img_height);
			} else {
				summary.css("height", "");
			}

			if (true === update_css) {
				pp_qv_modal.css("opacity", 1);
			}
		};

		var pp_get_scrollbar_width = function () {
			var div = $(
				'<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>'
			);
			// Append our div, do our calculation and then remove it
			$("body").append(div);
			var w1 = $("div", div).innerWidth();
			div.css("overflow-y", "scroll");
			var w2 = $("div", div).innerWidth();
			$(div).remove();

			return w1 - w2;
		};

		pp_qv_close_modal();
		//pp_update_summary_height();

		window.addEventListener("resize", function (event) {
			pp_update_summary_height();
		});

		/* Add to cart ajax */
		/**
		 * pp_add_to_cart_ajax class.
		 */
		var pp_add_to_cart_ajax = function () {
			modal_wrap
				.off(
					"click",
					"#pp-quick-view-content .single_add_to_cart_button"
				)
				.off("pp_added_to_cart")
				.on(
					"click",
					"#pp-quick-view-content .single_add_to_cart_button",
					this.onAddToCart
				)
				.on("pp_added_to_cart", this.updateButton);
		};

		/**
		 * Handle the add to cart event.
		 */
		pp_add_to_cart_ajax.prototype.onAddToCart = function (e) {
			e.preventDefault();

			var $thisbutton = $(this),
				product_id = $(this).val(),
				variation_id = $('input[name="variation_id"]').val() || "",
				quantity = $('input[name="quantity"]').val();

			if ($thisbutton.is(".single_add_to_cart_button")) {
				$thisbutton.removeClass("added");
				$thisbutton.addClass("loading");

				// Ajax action.
				if (variation_id != "") {
					jQuery.ajax({
						url: pp_woo_products_script.ajax_url,
						type: "POST",
						data:
							"action=pp_add_cart_single_product&product_id=" +
							product_id +
							"&variation_id=" +
							variation_id +
							"&quantity=" +
							quantity,

						success: function (results) {
							// Trigger event so themes can refresh other areas.
							$(document.body).trigger("wc_fragment_refresh");
							$(document.body).trigger("pp_added_to_cart", [
								$thisbutton,
							]);
							$thisbutton.removeClass("loading");
							$thisbutton.addClass("added");
						},
					});
				} else {
					jQuery.ajax({
						url: pp_woo_products_script.ajax_url,
						type: "POST",
						data:
							"action=pp_add_cart_single_product&product_id=" +
							product_id +
							"&quantity=" +
							quantity,

						success: function (results) {
							// Trigger event so themes can refresh other areas.
							$(document.body).trigger("wc_fragment_refresh");
							modal_wrap.trigger("pp_added_to_cart", [
								$thisbutton,
							]);
						},
					});
				}
			}
		};

		/**
		 * Update cart page elements after add to cart events.
		 */
		pp_add_to_cart_ajax.prototype.updateButton = function (e, button) {
			button = typeof button === "undefined" ? false : button;

			if ($(button)) {
				$(button).removeClass("loading");
				$(button).addClass("added");

				// View cart text.
				/*if (!pp_woo_products_script.is_cart && $(button).parent().find('.added_to_cart').length === 0 && pp_woo_products_script.is_single_product) {
					$(button).after(' <a href="' + pp_woo_products_script.cart_url + '" class="added_to_cart wc-forward" title="' +
						pp_woo_products_script.view_cart + '">' + pp_woo_products_script.view_cart + '</a>');
				}*/
			}
		};

		/**
		 * Init pp_add_to_cart_ajax.
		 */
		new pp_add_to_cart_ajax();
	};

	var RegisterPPAddCart = function ($scope, $) {
		//
		$layout = $scope.data("element_type");

		if (
			"pp-woo-products.skin-2" !== $layout &&
			"pp-woo-products-slider.slider-modern" !== $layout
		) {
			return;
		}

		/* Add to cart for styles */
		var style_add_to_cart = function () {
			//fa-spinner

			$(document.body)
				.off(
					"click",
					".pp-product-actions .pp-add-to-cart-btn.product_type_simple"
				)
				.off("pp_product_actions_added_to_cart")
				.on(
					"click",
					".pp-product-actions .pp-add-to-cart-btn.product_type_simple",
					this.onAddToCart
				)
				.on("pp_product_actions_added_to_cart", this.updateButton);
		};

		/**
		 * Handle the add to cart event.
		 */
		style_add_to_cart.prototype.onAddToCart = function (e) {
			e.preventDefault();

			var $thisbutton = $(this),
				product_id = $thisbutton.data("product_id"),
				quantity = 1,
				cart_icon = $thisbutton.find("pp-action-item");

			$thisbutton.removeClass("added");
			$thisbutton.addClass("loading");

			jQuery.ajax({
				url: pp_woo_products_script.ajax_url,
				type: "POST",
				data:
					"action=pp_add_cart_single_product&product_id=" +
					product_id +
					"&quantity=" +
					quantity,

				success: function (results) {
					// Trigger event so themes can refresh other areas.
					$(document.body).trigger("wc_fragment_refresh");
					$(document.body).trigger(
						"pp_product_actions_added_to_cart",
						[$thisbutton]
					);
				},
			});
		};

		/**
		 * Update cart page elements after add to cart events.
		 */
		style_add_to_cart.prototype.updateButton = function (e, button) {
			button = typeof button === "undefined" ? false : button;

			if ($(button)) {
				$(button).removeClass("loading");
				$(button).addClass("added");

				// Show view cart notice.
				if (
					!pp_woo_products_script.is_cart &&
					$(button).parent().find(".added_to_cart").length === 0 &&
					pp_woo_products_script.is_single_product
				) {
					$(button).after(
						' <a href="' +
							pp_woo_products_script.cart_url +
							'" class="added_to_cart wc-forward" title="' +
							pp_woo_products_script.view_cart +
							'">' +
							pp_woo_products_script.view_cart +
							"</a>"
					);
				}
			}
		};

		/**
		 * Init style_add_to_cart.
		 */
		new style_add_to_cart();
	};
	/**
	 * Function for Product Grid.
	 *
	 */
	var WidgetPPWooProducts = function ($scope, $, $panel) {
		if ("undefined" == typeof $scope) {
			return;
		}

		/* Slider */
		var slider_wrapper = $scope.find(".pp-woo-products-slider");

		if (slider_wrapper.length > 0) {
			var slider_selector = slider_wrapper.find("ul.products"),
				slider_options = JSON.parse(
					slider_wrapper.attr("data-woo_slider")
				);

			slider_selector.slick(slider_options);

			PPSliderUpdate($, slider_selector);
		}

		if (!elementorFrontend.isEditMode()) {
			/* Common */
			RegisterPPQuickView($scope, $);
			/* Style specific cart button */
			RegisterPPAddCart($scope, $);
		}

		if (elementorFrontend.isEditMode()) {
			RegisterPPQuickView($scope, $);
		}

		$scope
			.find(".pp-post-filter")
			.off("click")
			.on("click", function () {
				$(this).siblings().removeClass("pp-filter-current");
				$(this).addClass("pp-filter-current");
				count = 1;

				_postsFilterAjax($scope, $(this));
			});
	};

	var _postsFilterAjax = function ($scope, $this) {
		$scope
			.find(".pp-posts-grid .pp-grid-item-wrap")
			.last()
			.after(
				'<div class="pp-posts-loader-wrap"><div class="pp-loader"></div><div class="pp-loader-overlay"></div></div>'
			);

		var $args = {
			page_id: $scope.find(".pp-woo-products-inner").data("page"),
			widget_id: $scope.data("id"),
			filter: $this.data("filter"),
			skin: $scope.find(".pp-woo-products-inner").data("skin"),
			page_number: 1,
		};

		_callAjax($scope, $args);
	};

	var _callAjax = function ($scope, $obj, $append) {
		var loader = $scope.find(".pp-posts-loader");

		$.ajax({
			url: pp_woo_products_script.ajax_url,
			data: {
				action: "pp_get_product",
				page_id: $obj.page_id,
				widget_id: $obj.widget_id,
				category: $obj.filter,
				skin: $obj.skin,
				page_number: $obj.page_number,
			},
			dataType: "json",
			type: "POST",
			success: function (data) {
				//$scope.find( '.pp-posts-loader' ).remove();

				var sel = $scope.find(".products");

				if (true == $append) {
					var html_str = data.data.html;
					//html_str = html_str.replace( 'pp-post-wrapper-featured', '' );
					sel.append(html_str);
				} else {
					sel.html(data.data.html);
				}

				$scope
					.find(".pp-posts-pagination-wrap")
					.html(data.data.pagination);

				var layout = $scope.find(".pp-posts-grid").data("layout"),
					selector = $scope.find(".pp-posts-grid");

				if ("masonry" == layout) {
					$scope.imagesLoaded(function () {
						selector.isotope("destroy");
						selector.isotope({
							layoutMode: layout,
							itemSelector: ".pp-grid-item-wrap",
						});
					});
				}

				//	Complete the process 'loadStatus'
				loadStatus = true;
				if (true == $append) {
					loader.hide();
					$scope.find(".pp-post-load-more").show();
				}

				/*if( count == total ) {
					$scope.find( '.pp-post-load-more' ).hide();
				}*/
			},
		});
	};

	/**
	 * Function for Product Grid.
	 *
	 */
	var WidgetPPWooAddToCart = function ($scope, $) {
		$("body")
			.off("added_to_cart.pp_cart")
			.on("added_to_cart.pp_cart", function (
				e,
				fragments,
				cart_hash,
				this_button
			) {
				if (
					$(this_button)
						.parent()
						.parent()
						.parent()
						.hasClass("elementor-widget-pp-woo-add-to-cart")
				) {
					$btn = $(this_button);

					if ($btn.length > 0) {
						// View cart text.
						if (
							!pp_woo_products_script.is_cart &&
							$btn.hasClass("added")
						) {
							setTimeout(function () {
								window.location =
									pp_woo_products_script.cart_url;
							}, 500);
						}
					}
				}
			});
	};

	/**
	 * Function for Product Categories.
	 *
	 */
	var WooOffcanvasCartHandler = function ($scope, $) {
		var container = $scope.find(".pp-offcanvas-cart-container");

		if ($(container).length > 0) {
			new PPOffcanvasContent($scope);
		}
	};

	/**
	 * Function for Product Categories.
	 *
	 */
	var WooCategoriesHandler = function ($scope, $) {
		var $carousel = $scope.find(".pp-woo-categories-carousel").eq(0);

		if ($carousel.length > 0) {
			var $carousel_selector = $carousel.find(
				".products.pp-slick-slider"
			);
			var $carousel_options = JSON.parse(
				$carousel.attr("data-cat-carousel-options")
			);
			$carousel_selector.slick($carousel_options);

			PPSliderUpdate($, $carousel_selector);
		}
	};

	/**
	 * Function for Woo Product Tabs
	 */

	var WooProductTabsHandler = function ($scope) {
		var $tabs = $scope.find(".wc-tabs-wrapper, .woocommerce-tabs, #rating");

		// Trigger WooCommerce's Single Product JS manually on widget load

		$tabs.trigger("init");
	};

	/**
	 * Function for Woo Add to Cart Notification
	 */
	var WooAddToCartNotificationHandler = function ($scope, $) {
		var cartForm = $(".single-product .cart");
		var stickyCartBtnArea = $(".pp-add-to-cart-sticky");

		if (stickyCartBtnArea.length <= 0 || cartForm.length <= 0) {
			return;
		}

		var totalOffset = cartForm.offset().top + cartForm.outerHeight();

		var addToCartStickyToggler = function () {
			var windowScroll = $(window).scrollTop();
			var windowHeight = $(window).height();
			var documentHeight = $(document).height();

			if (
				totalOffset < windowScroll &&
				windowScroll + windowHeight != documentHeight
			) {
				stickyCartBtnArea.addClass("pp-sticky-shown");
			} else if (
				windowScroll + windowHeight == documentHeight ||
				totalOffset > windowScroll
			) {
				stickyCartBtnArea.removeClass("pp-sticky-shown");
			}
		};
		addToCartStickyToggler();
		$(window).scroll(addToCartStickyToggler);

		// If Variations Product
		$(".pp-sticky-add-to-cart").on("click", function (e) {
			e.preventDefault();
			$("html, body").animate(
				{
					scrollTop: $(".single-product .cart").offset().top - 30,
				},
				500
			);
		});
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-products.skin-1",
			WidgetPPWooProducts
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-products.skin-2",
			WidgetPPWooProducts
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-products.skin-3",
			WidgetPPWooProducts
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-products.skin-4",
			WidgetPPWooProducts
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-products.skin-5",
			WidgetPPWooProducts
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-add-to-cart.default",
			WidgetPPWooAddToCart
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-categories.default",
			WooCategoriesHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-offcanvas-cart.default",
			WooOffcanvasCartHandler
		);
		//elementorFrontend.hooks.addAction('frontend/element_ready/pp-woo-product-tabs.default', WooProductTabsHandler);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/pp-woo-add-to-cart-notification.default",
			WooAddToCartNotificationHandler
		);
	});
})(jQuery);
