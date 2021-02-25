<?php

/**
 * Order delivery date pro tyche
 * Class WFACP_Compatibility_Order_Delivery_Date_Tyche_lite
 */
class WFACP_Compatibility_Order_Delivery_Date_Tyche_lite {
	public function __construct() {
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_field' ], 20 );
		add_action( 'wfacp_header_print_in_head', [ $this, 'enqueue_js' ] );
		add_filter( 'wfacp_html_fields_oddt', '__return_false' );
		add_action( 'process_wfacp_html', [ $this, 'call_birthday_addon_hook' ], 10, 3 );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 10, 2 );
	}

	public function add_field( $fields ) {
		if ( $this->is_enable() ) {
			$fields['oddt'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'wfacp-col-full', 'wfacp-form-control-wrapper', 'aw_addon_wrap', 'oddt' ],
				'id'         => 'oddt',
				'field_type' => 'advanced',
				'label'      => __( 'Delivery Date', 'woofunnels-aero-checkout' ),
			];
		}

		return $fields;
	}

	private function is_enable() {
		if ( class_exists( 'Order_Delivery_Date_Lite' ) ) {
			if ( get_option( 'orddd_lite_enable_delivery_date' ) === 'on' ) {
				return true;
			}
		}

		return false;
	}

	public function enqueue_js() {
		if ( $this->is_enable() ) {
			$instance = WFACP_Common::remove_actions( ORDDD_LITE_SHOPPING_CART_HOOK, 'Order_Delivery_Date_Lite', 'orddd_lite_front_scripts_js' );
			if ( $instance instanceof Order_Delivery_Date_Lite ) {
				add_action( 'wfacp_internal_css', [ $instance, 'orddd_lite_front_scripts_js' ] );
			}
		}
	}

	public function call_birthday_addon_hook( $field, $key, $args ) {
		if ( ! empty( $key ) && 'oddt' === $key && $this->is_enable() ) {
			Orddd_Lite_Process::orddd_lite_my_custom_checkout_field();
		}
	}

	public function add_default_wfacp_styling( $args, $key ) {
		if ( $key == 'e_deliverydate' ) {
			$args['input_class'] = array_merge( $args['input_class'], [ 'wfacp-form-control' ] );
			$args['label_class'] = array_merge( $args['label_class'], [ 'wfacp-form-control-label' ] );
			$args['class']       = array_merge( $args['class'], [ 'wfacp-col-full', 'wfacp-form-control-wrapper', 'aw_addon_wrap', 'oddt' ] );
		}

		return $args;
	}
}


WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Order_Delivery_Date_Tyche_lite(), 'oddtl' );

/**
 * Order delivery date pro tyche
 * Class WFACP_Compatibility_Order_Delivery_Date_Tyche_Pro
 */
class WFACP_Compatibility_Order_Delivery_Date_Tyche_Pro {
	/**
	 * @var orddd_process
	 */
	private $order_instance = null;
	private $orddd_locations_instance = null;

	public function __construct() {
		add_filter( 'wfacp_advanced_fields', [ $this, 'add_field' ], 20 );
		add_action( 'wfacp_header_print_in_head', [ $this, 'enqueue_js' ] );
		add_filter( 'wfacp_html_fields_oddt', '__return_false' );
		add_filter( 'wfacp_html_fields_orddd_time_slot', '__return_false' );
		add_filter( 'wfacp_html_fields_orddd_locations', '__return_false' );
		add_action( 'process_wfacp_html', [ $this, 'call_birthday_addon_hook' ], 10, 3 );
		add_action( 'wfacp_checkout_page_found', [ $this, 'actions' ] );
		add_filter( 'woocommerce_form_field_args', [ $this, 'add_default_wfacp_styling' ], 50, 2 );

		add_action( 'wfacp_internal_css', [ $this, 'wfacp_internal_css' ] );
	}

	public function actions() {
		add_action( 'wp_footer', [ $this, 'add_js' ] );
	}

	public function add_js() {
		if ( ! $this->is_enable() ) {
			return '';
		}
		?>
        <script>
            window.addEventListener('load', function () {
                (function ($) {
                    add_aero_class();
                    var $this = $("#e_deliverydate_field");
                    setTimeout(function () {
                        add_aero_class();
                        add_anim_class();
                    }, 5000);
                    $(document.body).on('update_checkout', function () {
                        add_aero_class();
                        add_anim_class();
                    });
                    $(document.body).on('updated_checkout', function () {
                        add_aero_class();
                        add_anim_class();
                    });

                    function add_aero_class() {
                        if ($("#e_deliverydate_field").length > 0) {
                            var $this = $("#e_deliverydate_field");
                            if (!$this.hasClass('wfacp-col-full')) {
                                $this.addClass("wfacp-col-full");
                                $this.addClass("wfacp-form-control-wrapper");
                            }
                        }
                        if ($("#orddd_time_slot_field").length > 0) {
                            $("#orddd_time_slot_field").addClass("wfacp-col-full");
                            $("#orddd_time_slot_field").addClass("wfacp-form-control-wrapper");
                            $("#orddd_time_slot_field > label").addClass("wfacp-form-control-label");
                            $("#orddd_time_slot").addClass("wfacp-form-control");
                        }
                    }

                    function add_anim_class() {
                        $("#e_deliverydate").on('click', function () {
                            if (!$("#e_deliverydate").parents("p").hasClass("wfacp-anim-wrap")) {
                                $("#e_deliverydate").parents("p").addClass("wfacp-anim-wrap");
                            }
                        });
                    }

                    $(document.body).on('updated_checkout', function () {
                        setTimeout(function () {
                            if (typeof orddd_update_delivery_session == "function") {
                                orddd_update_delivery_session();
                            }

                        }, 500);
                    });

                })(jQuery);
            });
        </script>
		<?php
	}

	public function add_field( $fields ) {
		if ( $this->is_enable() ) {
			$fields['oddt']            = [
				'type'       => 'wfacp_html',
				'class'      => [ 'wfacp-col-full', 'wfacp-form-control-wrapper', 'wfacp_anim_wrap', 'oddt' ],
				'id'         => 'oddt',
				'field_type' => 'advanced',
				'label'      => __( 'Delivery Date', 'woofunnels-aero-checkout' ),
			];
			$fields['orddd_time_slot'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'wfacp-col-full', 'wfacp-form-control-wrapper', 'wfacp_anim_wrap', 'orddd_time_slot' ],
				'id'         => 'orddd_time_slot',
				'field_type' => 'advanced',
				'label'      => __( 'Time Slot', 'woofunnels-aero-checkout' ),
			];
			$fields['orddd_locations'] = [
				'type'       => 'wfacp_html',
				'class'      => [ 'wfacp-col-full', 'wfacp-form-control-wrapper', 'wfacp_anim_wrap', 'orddd_locations' ],
				'id'         => 'orddd_locations',
				'field_type' => 'advanced',
				'label'      => __( 'Pickup Location', 'woofunnels-aero-checkout' ),
			];
		}

		return $fields;

	}

	private function is_enable() {
		if ( class_exists( 'order_delivery_date' ) ) {
			if ( get_option( 'orddd_enable_delivery_date' ) === 'on' ) {
				return true;
			}
		}

		return false;
	}

	public function enqueue_js() {
		if ( $this->is_enable() ) {
			if ( class_exists( 'orddd_common' ) ) {
				$orddd_shopping_cart_hook       = orddd_common::orddd_get_shopping_cart_hook();
				$this->order_instance           = WFACP_Common::remove_actions( $orddd_shopping_cart_hook, 'orddd_process', 'orddd_date_after_checkout_billing_form' );
				$this->orddd_locations_instance = WFACP_Common::remove_actions( $orddd_shopping_cart_hook, 'orddd_locations', 'orddd_locations_after_checkout_billing_form' );;
				if ( $this->order_instance instanceof orddd_process ) {
					remove_action( $orddd_shopping_cart_hook, array( $this->order_instance, 'orddd_time_slot_after_checkout_billing_form' ) );
					remove_action( $orddd_shopping_cart_hook, array( $this->order_instance, 'orddd_text_block_after_checkout_billing_form' ) );
				}
			}
			$instance = WFACP_Common::remove_actions( 'wp_enqueue_scripts', 'orddd_scripts', 'orddd_front_scripts_css' );
			if ( $instance instanceof orddd_scripts ) {
				add_action( 'wfacp_internal_css', [ $instance, 'orddd_front_scripts_css' ] );
			}
		}
	}

	public function call_birthday_addon_hook( $field, $key, $args ) {
		if ( $this->is_enable() && ! empty( $key ) && $this->order_instance instanceof orddd_process ) {
			if ( 'oddt' === $key ) {
				$this->order_instance->orddd_date_after_checkout_billing_form();
			}
			if ( 'orddd_time_slot' === $key ) {
				$this->order_instance->orddd_time_slot_after_checkout_billing_form();
			}
			if ( 'orddd_locations' === $key ) {
				$this->order_instance->orddd_text_block_after_checkout_billing_form();
				$this->orddd_locations_instance->orddd_locations_after_checkout_billing_form();
			}
		}
	}

	public function add_default_wfacp_styling( $args, $key ) {
		if ( $key == 'e_deliverydate' || $key == 'orddd_time_slot' || $key == 'orddd_locations' ) {
			$args['input_class'] = array_merge( $args['input_class'], [ 'wfacp-form-control' ] );
			$args['label_class'] = array_merge( $args['label_class'], [ 'wfacp-form-control-label' ] );
			$args['class']       = array_merge( $args['class'], [ 'wfacp-col-full', 'wfacp-form-control-wrapper' ] );
		}

		return $args;
	}

	public function wfacp_internal_css() {

		if ( ! $this->is_enable() ) {
			return '';
		}
		?>

        <style>
            p#orddd_time_slot_field {
                padding: 0 7px;
            }
        </style>
		<?php
	}
}

WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_Order_Delivery_Date_Tyche_Pro(), 'oddtp' );