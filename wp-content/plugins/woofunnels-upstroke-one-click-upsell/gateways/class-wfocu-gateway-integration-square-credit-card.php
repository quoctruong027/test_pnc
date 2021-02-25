<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SkyVerge\WooCommerce\PluginFramework\v5_4_0 as Framework;
use SquareConnect\Api\OrdersApi;
use SquareConnect\Api\TransactionsApi;
use SquareConnect\Model as SquareModel;
use SquareConnect\Model\Address;
use SquareConnect\Model\ChargeRequest;
use WooCommerce\Square\Handlers\Product;
use WooCommerce\Square\Utilities\Money_Utility;

/**
 * Class WFOCU_Gateway_Integration_Square_Credit_Card
 */
class WFOCU_Gateway_Integration_Square_Credit_Card extends WFOCU_Gateway {
	protected static $ins = null;
	public $key = 'square_credit_card';
	public $token = false;
	public $apiConfig;
	public $access_token = '';
	public $location_id = '';
	public $apiClient = '';

	/**
	 * WFOCU_Square_Gateway_Credit_Cards constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wc_' . $this->key . '_payment_form_tokenization_forced', [ $this, 'wfocu_square_enable_force_tokenization' ], 10 );

		add_filter( 'wc_payment_gateway_' . $this->key . '_get_order', [ $this, 'square_get_order' ], 10, 2 );
		add_filter( 'wc_payment_gateway_' . $this->key . '_process_payment', [ $this, 'add_square_token' ], 10, 3 );

		add_filter( 'wfocu_subscriptions_get_supported_gateways', array( $this, 'enable_subscription_upsell_support' ), 10, 1 );

		//Copying _wc_square_credit_card_payment_token in renewal offer for Subscriptions upsell
		add_filter( 'wfocu_order_copy_meta_keys', array( $this, 'set_square_payment_token_keys_to_copy' ), 10, 2 );

		add_action( 'wfocu_subscription_created_for_upsell', array( $this, 'save_square_payment_token_to_subscription' ), 10, 3 );

		add_action( 'wcs_create_subscription', [ $this, 'wfcou_square_update_token_in_user_meta' ], 10, 1 );;
	}

	/**
	 * @return WFOCU_Gateway_Integration_Square_Credit_Card|null
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Try and get the payment token saved by the gateway
	 *
	 * @param WC_Order $order
	 *
	 * @return bool|true
	 */
	public function has_token( $order ) {
		$this->token = $order->get_meta( '_wc_square_credit_card_payment_token', true );

		if ( empty( $this->token ) ) {
			$order_id    = WFOCU_WC_Compatibility::get_order_id( $order );
			$this->token = get_post_meta( $order_id, '_wc_square_credit_card_payment_token', true );
		}
		WFOCU_Core()->log->log( "WFOCU Square: Token is: {$this->token} " );

		if ( ! empty( $this->token ) && $this->is_enabled( $order ) && ( $this->get_key() === $order->get_payment_method() ) ) {
			return true;
		}
		WFOCU_Core()->log->log( "WFOCU Square: Square token is missing or invalid gateway. {$this->token}, config:" . print_r( $this->apiConfig, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		WFOCU_Core()->log->log( "WFOCU Square: Access Token is: {$this->access_token}, ApiClient: " . print_r( $this->apiClient, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		return false;
	}

	/**
	 * Charging the card for which token is saved.
	 *
	 * @param WC_Order $order
	 *
	 * @return true
	 */
	public function process_charge( $order ) {
		$is_successful = false;
		$get_offer_id  = WFOCU_Core()->data->get( 'current_offer' );
		$order_id      = WFOCU_WC_Compatibility::get_order_id( $order );

		$result = $this->generate_square_charge( $order, $get_offer_id );

		if ( isset( $result['failed'] ) && $result['failed'] ) {
			WFOCU_Core()->log->log( "WFOCU Square: Order id: #$order_id Payment for offer $get_offer_id using square credit card failed." );
		}

		$response = isset( $result['response'] ) ? $result['response'] : array();

		if ( is_array( $response ) && count( $response ) > 0 && 200 === absint( $response[1] ) ) {
			$is_successful = true;
			if ( isset( $result['transaction_id'] ) ) {
				WFOCU_Core()->data->set( '_transaction_id', $result['transaction_id'] );
			}
			WFOCU_Core()->log->log( "WFOCU Square: Payment for offer $get_offer_id using Square credit card is successful with transaction id: {$result['transaction_id']}." );
		}

		return $this->handle_result( $is_successful );
	}

	/**
	 * @param WC_Order $order
	 * @param $get_offer_id
	 *
	 * @return array
	 */
	public function generate_square_charge( $order, $get_offer_id ) {
		$get_package = WFOCU_Core()->data->get( '_upsell_package' );
		$result      = array();
		try {
			$this->set_square_gateway_config();
			$get_order               = $this->get_order( $order, $get_offer_id, $get_package );
			$order                   = ( $get_order instanceof WC_Order ) ? $get_order : $order;
			$this->location_id       = empty( $this->location_id ) ? WFOCU_WC_Compatibility::get_order_data( $order, '_wc_square_credit_card_square_location_id' ) : $this->location_id;
			$result['location_id_1'] = $this->location_id;
			$result['location_id_2'] = $this->location_id;
			$result['api_config']    = $this->apiConfig;

			$result['api_client'] = $this->apiClient;
			$charge_request_data  = $this->wfocu_get_square_charge_request( $order, $get_offer_id, $get_package );
			$result['request']    = $charge_request_data;

			$square_trns_api = new TransactionsApi( $this->apiClient );

			$result['response'] = $square_trns_api->chargeWithHttpInfo( $this->location_id, $charge_request_data );

			if ( is_array( $result['response'] ) && count( $result['response'] ) > 0 ) {
				$response                 = $result['response'][0];
				$transaction              = $response->getTransaction();
				$result['transaction']    = $transaction;
				$result['transaction_id'] = $transaction->getId();
			}

		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Token Payment Failed due to exception: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$result['failed'] = true;
		}
		WFOCU_Core()->log->log( "WFOCU Square: Token payment result: " . print_r( $result, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		return $result;
	}

	/**
	 * Gets the order object with offer payment information added.
	 *
	 * @param $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return bool
	 */
	public function get_order( $order, $get_offer_id, $get_package ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}

		$result = array();

		$order_id           = WFOCU_WC_Compatibility::get_order_id( $order );
		$result['order_id'] = $order_id;

		$order = $this->get_wc_gateway()->get_order( $order );

		$order->square_customer_id    = WFOCU_WC_Compatibility::get_order_data( $order, '_wc_square_credit_card_customer_id' );
		$result['square_customer_id'] = $order->square_customer_id;

		$sq_token = empty( $this->token ) ? $order->get_meta( '_wc_square_credit_card_payment_token' ) : $this->token;
		$sq_token = empty( $sq_token ) ? get_post_meta( $order_id, '_wc_square_credit_card_payment_token', true ) : $sq_token;

		$order->payment        = isset( $order->payment ) ? $order->payment : new stdClass();
		$order->payment->token = ( isset( $order->payment->token ) && ! empty( $order->payment->token ) ) ? $order->payment->token : $sq_token;

		$result['payment_obj'] = $order->payment;

		try {
			$this->set_square_gateway_config();
			$create_order_data = $this->wfocu_create_square_order_data( $order, $get_offer_id, $get_package );
			$square_orders_api = new OrdersApi( $this->apiClient );

			$result['response'] = $square_orders_api->createOrderWithHttpInfo( $this->location_id, $create_order_data );

			if ( is_array( $result['response'] ) && count( $result['response'] ) > 0 ) {
				$response        = $result['response'][0];
				$responseOrder   = $response->getOrder();
				$square_order_id = $responseOrder->getID();

				$square_order_total           = $responseOrder->getTotalMoney();
				$result['square_order_id']    = $square_order_id;
				$result['square_order_total'] = $square_order_total;

				if ( ! empty( $square_order_id ) && ! empty( $square_order_total ) ) {
					$order->square_order_id    = $square_order_id;
					$order->square_order_total = $square_order_total;
				}
				WFOCU_Core()->log->log( "WFOCU Square: created square order id: {$order->square_order_id}, and Total object: " . print_r( $order->square_order_total, true ) );  //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Exception in creating square order: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			WFOCU_Core()->log->log( "WFOCU Square: Final exception result for creating square order " . print_r( $result, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			return $order;
		}
		WFOCU_Core()->log->log( "WFOCU Square: Final result for creating square order " . print_r( $result, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		return $order;
	}

	/**
	 * Sets the data for creating a square order.
	 *
	 * @param WC_Order $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return array|SquareModel\CreateOrderRequest
	 */
	public function wfocu_create_square_order_data( \WC_Order $order, $get_offer_id, $get_package ) {
		$result = array();
		try {
			$this->set_square_gateway_config();
			$square_request = new SquareModel\CreateOrderRequest();
			$order_model    = new SquareModel\Order();
			$order_model->setReferenceId( $this->get_order_number( $order ) );

			$line_items = array_merge( $this->get_product_line_items( $order, $get_offer_id, $get_package ), $this->get_fee_line_items( $order, $get_offer_id, $get_package ), $this->get_shipping_line_items( $order, $get_offer_id, $get_package ) );
			WFOCU_Core()->log->log( "WFOCU Square: Order model line_items for offer id: $get_offer_id is: " . print_r( $line_items, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			$taxes = $this->get_order_taxes( $order, $get_offer_id, $get_package );

			WFOCU_Core()->log->log( "WFOCU Square: Order model taxes for offer id: $get_offer_id is: " . print_r( $taxes, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			$this->apply_taxes( $taxes, $get_offer_id, $line_items );
			WFOCU_Core()->log->log( "WFOCU Square: Taxes applied." );

			$order_model->setLineItems( $line_items );
			WFOCU_Core()->log->log( "WFOCU Square: Line Items set." );

			$order_model->setTaxes( $taxes );
			WFOCU_Core()->log->log( "WFOCU Square: Taxes set." );

			$shipping_cost = ( isset( $get_package['shipping'] ) && isset( $get_package['shipping']['diff'] ) && $get_package['shipping']['diff']['cost'] ) ? $get_package['shipping']['diff']['cost'] : 0;

			if ( $shipping_cost < 0 ) {
				$order_model->setDiscounts( [
					new SquareModel\OrderLineItemDiscount( [
						'name'         => __( 'Shipping Refunded', 'woocommerce-square' ),
						'type'         => 'FIXED_AMOUNT',
						'amount_money' => Money_Utility::amount_to_money( abs( $shipping_cost ), $order->get_currency() ),
						'scope'        => 'ORDER',
					] )
				] );
			}

			$square_request->setIdempotencyKey( wc_square()->get_idempotency_key( $order->unique_transaction_ref ) );
			$square_request->setOrder( $order_model );

			WFOCU_Core()->log->log( "WFOCU Square: Request data in create order for offer id: $get_offer_id is: " . print_r( $square_request, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			return $square_request;

		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Square Exception in setting create order request data for offer id: $get_offer_id: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $result;
	}

	/**
	 * Gets Square line item objects for an offer package items.
	 *
	 * @param WC_Order $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return SquareModel\OrderLineItem[]
	 */
	public function get_product_line_items( \WC_Order $order, $get_offer_id, $get_package ) {
		$line_items = [];
		try {
			$this->set_square_gateway_config();
			foreach ( $get_package['products'] as $item ) {
				WFOCU_Core()->log->log( "Offer product id: {$item['id']}, price: {$item['price']} and qty: {$item['qty']} " );
				$line_item = new SquareModel\OrderLineItem();
				$line_item->setQuantity( (string) $item['qty'] );
				$item_price = ( $item['qty'] > 1 ) ? ( $item['price'] / $item['qty'] ) : $item['price'];
				$line_item->setBasePriceMoney( Money_Utility::amount_to_money( $item_price, $order->get_currency() ) );

				$product   = wc_get_product( $item['id'] );
				$is_synced = false;
				if ( $product instanceof WC_Product ) {
					$is_synced = Product::is_synced_with_square( $product );
				}
				$square_catalog_id = get_post_meta( $item['id'], Product::SQUARE_VARIATION_ID_META_KEY, true );
				WFOCU_Core()->log->log( "Offer item Square catalog id: $square_catalog_id for item id: {$item['id']} and is_synced: $is_synced" );

				if ( $is_synced && ! empty( $square_catalog_id ) && strlen( $square_catalog_id ) > 0 ) {
					$line_item->setCatalogObjectId( $square_catalog_id );
				} else {
					$line_item->setName( $item['_offer_data']->name );
				}
				$line_items[] = $line_item;
			}
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Square Exception in get_product_line_items for offer id: $get_offer_id is: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $line_items;
	}

	/**
	 * Gets Square line item objects for an order's fee items.
	 *
	 * @param WC_Order $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return array
	 */
	public function get_fee_line_items( \WC_Order $order, $get_offer_id, $get_package ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$line_items = [];
		try {
			$this->set_square_gateway_config();
			foreach ( $order->get_fees() as $item ) {

				if ( ! $item instanceof \WC_Order_Item_Fee ) {
					continue;
				}

				$line_item = new SquareModel\OrderLineItem();
				$line_item->setQuantity( (string) 1 );

				$line_item->setName( $item->get_name() );
				$line_item->setBasePriceMoney( Money_Utility::amount_to_money( $item->get_total(), $order->get_currency() ) );

				$line_items[] = $line_item;
			}
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Exception in get_fee_line_items for offer id: $get_offer_id is: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $line_items;
	}

	/**
	 * Gets Square line item objects for an order's shipping items.
	 *
	 * @param WC_Order $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return array
	 */
	public function get_shipping_line_items( \WC_Order $order, $get_offer_id, $get_package ) {
		$line_items = [];

		$shipping_cost = ( isset( $get_package['shipping'] ) && isset( $get_package['shipping']['diff'] ) && $get_package['shipping']['diff']['cost'] ) ? $get_package['shipping']['diff']['cost'] : 0;
		WFOCU_Core()->log->log( "WFOCU Square: Shipping Cost for offer id: $get_offer_id is: $shipping_cost" );

		if ( $shipping_cost > 0 ) {
			WFOCU_Core()->log->log( "WFOCU Square: Adding shipping amount: $shipping_cost" );
			$this->set_square_gateway_config();
			try {
				$line_item = new SquareModel\OrderLineItem();
				$line_item->setQuantity( (string) 1 );
				$line_item->setName( $get_package['shipping']['label'] );
				$line_item->setBasePriceMoney( Money_Utility::amount_to_money( $shipping_cost, $order->get_currency() ) );
				$line_items[] = $line_item;

			} catch ( Exception $e ) {
				WFOCU_Core()->log->log( "WFOCU Square: Exception in get_shipping_line_items for offer id: $get_offer_id is: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
		}

		return $line_items;
	}

	/**
	 * Gets the tax line items for an order.
	 *
	 * @param WC_Order $order
	 * @param $get_offer_id
	 * @param $get_package
	 *
	 * @return array
	 */
	public function get_order_taxes( \WC_Order $order, $get_offer_id, $get_package ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$taxes = [];
		try {
			$this->set_square_gateway_config();
			foreach ( $order->get_taxes() as $tax ) {
				$tax_item = new SquareModel\OrderLineItemTax( [
					'uid'   => uniqid(),
					'name'  => $tax->get_name(),
					'type'  => 'ADDITIVE',
					'scope' => 'LINE_ITEM',
				] );

				$pre_tax_total = (float) $order->get_total() - (float) $order->get_total_tax();
				$total_tax     = (float) $tax->get_tax_total() + (float) $tax->get_shipping_tax_total();

				$percentage = ( $total_tax / $pre_tax_total ) * 100;
				$tax_item->setPercentage( Framework\SV_WC_Helper::number_format( $percentage ) );

				$taxes[] = $tax_item;
			}
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Exception in get_order_taxes for offer Id: $get_offer_id is: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $taxes;
	}

	/**
	 * Applies taxes on each Square line item.
	 *
	 * @param SquareModel\OrderLineItemTax[] $taxes
	 * @param $get_offer_id
	 * @param SquareModel\OrderLineItem[] $line_items
	 */
	public function apply_taxes( $taxes, $get_offer_id, $line_items ) {
		try {
			$this->set_square_gateway_config();
			foreach ( $line_items as $line_item ) {
				$applied_taxes = [];
				foreach ( $taxes as $tax ) {
					$applied_taxes[] = new SquareModel\OrderLineItemAppliedTax( [
						'tax_uid' => $tax->getUid(),
					] );
				}
				$line_item->setAppliedTaxes( $applied_taxes );
			}
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Exception in apply_taxes for offer id: $get_offer_id is: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * @param $order
	 * @param $offer_id
	 * @param $offer_package
	 *
	 * @return ChargeRequest
	 */
	public function wfocu_get_square_charge_request( $order, $offer_id, $offer_package ) {
		$this->set_square_gateway_config();
		$square_charge_request = new ChargeRequest();

		$order_id = WFOCU_WC_Compatibility::get_order_id( $order );

		$square_charge_request->setIdempotencyKey( wc_square()->get_idempotency_key( $order->unique_transaction_ref ) );
		$square_charge_request->setReferenceId( $this->get_order_number( $order ) );

		$description = $order->description . sprintf( __( ' Offer id: %s', 'upstroke-woocommerce-one-click-upsell-square' ), $offer_id );
		$square_charge_request->setNote( Framework\SV_WC_Helper::str_truncate( $description, 60 ) );

		$square_charge_request->setDelayCapture( false );

		if ( ! empty( $order->square_customer_id ) ) {
			$square_charge_request->setCustomerId( $order->square_customer_id );
		}

		// payment token (card ID) or card nonce (from JS)
		if ( ! empty( $order->payment->token ) ) {
			$square_charge_request->setCustomerCardId( $order->payment->token );
		}

		$billing_address = new Address();
		$billing_address->setFirstName( $order->get_billing_first_name() );
		$billing_address->setLastName( $order->get_billing_last_name() );
		$billing_address->setOrganization( $order->get_billing_company() );
		$billing_address->setAddressLine1( $order->get_billing_address_1() );
		$billing_address->setAddressLine2( $order->get_billing_address_2() );
		$billing_address->setLocality( $order->get_billing_city() );
		$billing_address->setAdministrativeDistrictLevel1( $order->get_billing_state() );
		$billing_address->setPostalCode( $order->get_billing_postcode() );
		$billing_address->setCountry( $order->get_billing_country() );

		$square_charge_request->setBillingAddress( $billing_address );

		if ( Framework\SV_WC_Order_Compatibility::has_shipping_address( $order ) ) {

			$shipping_address = new Address();
			$shipping_address->setFirstName( $order->get_shipping_first_name() );
			$shipping_address->setLastName( $order->get_shipping_last_name() );
			$shipping_address->setAddressLine1( $order->get_shipping_address_1() );
			$shipping_address->setAddressLine2( $order->get_shipping_address_2() );
			$shipping_address->setLocality( $order->get_shipping_city() );
			$shipping_address->setAdministrativeDistrictLevel1( $order->get_shipping_state() );
			$shipping_address->setPostalCode( $order->get_shipping_postcode() );
			$shipping_address->setCountry( $order->get_shipping_country() );

			$square_charge_request->setShippingAddress( $shipping_address );
		}

		$square_charge_request->setBuyerEmailAddress( $order->get_billing_email() );

		if ( isset( $order->square_order_id ) && ! empty( $order->square_order_id ) && isset( $order->square_order_total ) && ! empty( $order->square_order_total ) ) {
			$square_charge_request->setAmountMoney( $order->square_order_total );
			$square_charge_request->setOrderId( $order->square_order_id );
			WFOCU_Core()->log->log( "WFOCU Square: For wc order id: $order_id, Remote Square order id: {$order->square_order_id} has been set in square charge request with remote square order total: {$order->square_order_total}" );
		} else {
			$amount = isset( $offer_package['total'] ) ? $offer_package['total'] : 0;
			WFOCU_Core()->log->log( "WFOCU Square: upsell package total: $amount for wc order id: $order_id has been set in square charge request." );
			$square_charge_request->setAmountMoney( Money_Utility::amount_to_money( $amount, $order->get_currency() ) );
		}

		return $square_charge_request;
	}

	public function wfocu_square_enable_force_tokenization( $forced ) {
		if ( false !== $this->should_tokenize() ) {

			return true;
		}

		return $forced;
	}

	public function add_square_token( $process_payment, $order_id, $gateway ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedParameter
		$order = wc_get_order( $order_id );
		if ( $this->get_key() === $order->get_payment_method() && $this->is_enabled( $order ) ) {
			$this->set_square_gateway_config();
			WFOCU_Core()->log->log( "WFOCU Square: Order payment object before creating token for order_id: $order_id is: " . print_r( $order->payment, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$order = $this->get_wc_gateway()->get_order( $order );
			if ( empty( $order->payment->token ) && $order->get_customer_id() < 1 ) {
				try {
					$this->get_wc_gateway()->get_payment_tokens_handler()->create_token( $order );
				} catch ( Exception $e ) {
					WFOCU_Core()->log->log( "WFOCU Square: Exception in creating token in primary order payment: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				}
			}

			WFOCU_Core()->log->log( "WFOCU Square: Order payment object after creating token: $process_payment for order_id: $order_id: " . print_r( $order->payment, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $process_payment;
	}

	/**
	 * @param $order
	 * @param WC_Payment_Gateway $gateway
	 *
	 * @return mixed
	 */
	public function square_get_order( $order, $gateway ) {
		if ( $this->get_key() === $gateway->id ) {
			$this->set_square_gateway_config();
			$order_id = WFOCU_WC_Compatibility::get_order_id( $order );
			WFOCU_Core()->log->log( "WFOCU Square: Order payment object before get_order for order id: $order_id is: " . print_r( $order->payment, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			if ( empty( $order->payment->token ) ) {
				$order->payment->token = get_post_meta( $order_id, '_wc_square_credit_card_payment_token', true );
			}
			WFOCU_Core()->log->log( "WFOCU Square: Order payment object after adding token: " . print_r( $order->payment, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

		return $order;
	}

	/**
	 * Adding this gateway as Subscriptions upsell supported gateway
	 *
	 * @param $gateways
	 *
	 * @return array
	 */
	public function enable_subscription_upsell_support( $gateways ) {
		if ( is_array( $gateways ) ) {
			$gateways[] = $this->get_key();
		}

		return $gateways;
	}

	/**
	 * Adding keys to copy to renewal orders
	 *
	 * @param $meta_keys
	 * @param WC_Order $order
	 *
	 * @return mixed
	 */
	public function set_square_payment_token_keys_to_copy( $meta_keys, $order = null ) {

		if ( $order instanceof WC_Order ) {
			$payment_method = $order->get_payment_method();
			if ( $payment_method === $this->get_key() ) {
				array_push( $meta_keys, '_wc_square_credit_card_payment_token', '_wc_square_credit_card_customer_id' );
			}
		} else {
			array_push( $meta_keys, '_wc_square_credit_card_payment_token', '_wc_square_credit_card_customer_id' );
		}

		return $meta_keys;
	}

	/**
	 * @param WC_Subscription $subscription
	 * @param $key
	 * @param WC_Order $order
	 */
	public function save_square_payment_token_to_subscription( $subscription, $key, $order ) {

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$get_token      = $order->get_meta( '_wc_square_credit_card_payment_token', true );
		$sq_customer_id = $order->get_meta( '_wc_square_credit_card_customer_id', true );

		if ( ! empty( $get_token ) ) {
			$subscription->update_meta_data( '_wc_square_credit_card_payment_token', $get_token );
			$subscription->update_meta_data( '_wc_square_credit_card_customer_id', $sq_customer_id );
			$subscription->save();
		}
	}

	/**
	 * @param WC_Subscription $subscription
	 */
	public function wfcou_square_update_token_in_user_meta( $subscription ) {
		$customer_id     = get_post_meta( $subscription->get_id(), '_customer_user', true );
		$parent_order    = $subscription->get_parent();
		$parent_order_id = WFOCU_WC_Compatibility::get_order_id( $parent_order );
		if ( $customer_id > 0 && $this->get_key() === $parent_order->get_payment_method() ) {
			WFOCU_Core()->log->log( "WFOCU Square: Updating token for Customer id: $customer_id in subscription with id: {$subscription->get_id()} and parent order id: $parent_order_id" );
			$sq_token_id    = wcs_get_objects_property( $parent_order, '_wc_square_credit_card_payment_token' );
			$exp_month_year = wcs_get_objects_property( $parent_order, '_wc_square_credit_card_card_expiry_date' );
			$exp_month_year = explode( '-', $exp_month_year );
			$exp_month      = ( is_array( $exp_month_year ) && count( $exp_month_year ) > 1 ) ? $exp_month_year[1] : '';
			$exp_year       = empty( $exp_month ) ? '' : $exp_month_year[0];
			$sq_token_data  = array();

			$sq_token_data[ $sq_token_id ] = array(
				'type'      => 'credit_card',
				'card_type' => wcs_get_objects_property( $parent_order, '_wc_square_credit_card_card_type' ),
				'last_four' => wcs_get_objects_property( $parent_order, '_wc_square_credit_card_account_four' ),
				'exp_month' => $exp_month,
				'exp_year'  => $exp_year,
			);
			WFOCU_Core()->log->log( "WFOCU Square: Current token: $sq_token_id and all token obj: " . print_r( $sq_token_data, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			update_user_meta( $customer_id, '_wc_square_credit_card_payment_tokens', $sq_token_data ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.user_meta_update_user_meta
		}
	}

	public function set_square_gateway_config() {
		try {
			$this->apiConfig    = SquareConnect\Configuration::getDefaultConfiguration();
			$this->access_token = $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_access_token();
			if ( $this->get_wc_gateway()->get_plugin()->get_settings_handler()->is_sandbox_setting_enabled() ) {
				$this->apiConfig->setHost( 'https://connect.squareupsandbox.com' );
				$this->access_token = empty( $this->access_token ) ? $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_option( 'sandbox_token' ) : $this->access_token;
			}
			$this->apiConfig->setAccessToken( $this->access_token );
			$this->location_id = $this->get_wc_gateway()->get_plugin()->get_settings_handler()->get_location_id();
			$this->apiClient   = new SquareConnect\ApiClient( $this->apiConfig );
		} catch ( Exception $e ) {
			WFOCU_Core()->log->log( "WFOCU Square: Exception in setting apiClient: " . print_r( $e->getMessage(), true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}
}

WFOCU_Gateway_Integration_Square_Credit_Card::get_instance();