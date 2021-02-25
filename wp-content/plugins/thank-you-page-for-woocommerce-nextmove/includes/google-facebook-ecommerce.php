<?php
defined( 'ABSPATH' ) || exit;

$xlwcty_fb_gb_event_fire = get_post_meta( $order_id, 'xlwcty_fb_gb_event_fire', true );
if ( 'yes' === $xlwcty_fb_gb_event_fire ) {
	return '';
}

$pixel_id                                  = $this->facebook_pixel_enabled();
$google_analytics_id                       = $this->google_analytics_enabled();
$facebook_tracking_event                   = XLWCTY_Core()->data->get_option( 'enable_fb_pageview_event' );
$facebook_purchase_event                   = XLWCTY_Core()->data->get_option( 'enable_fb_purchase_event' );
$facebook_purchase_event_conversion        = XLWCTY_Core()->data->get_option( 'enable_fb_purchase_event_conversion_val' );
$facebook_purchase_advanced_matching_event = XLWCTY_Core()->data->get_option( 'enable_fb_advanced_matching_event' );

if ( $pixel_id > 0 || '' !== $google_analytics_id ) {

	$order                = wc_get_order( $order_id );
	$items                = $order->get_items( 'line_item' );
	$products             = array();
	$gproducts            = array();
	$order_total          = $order->get_total();
	$order_shipping_total = $order->get_shipping_total();
	$order_tax            = $order->get_total_tax();

	if ( XLWCTY_Compatibility::is_wc_version_gte_3_0() ) {
		foreach ( $items as $item ) {
			$pid        = $item->get_product_id();
			$product    = wc_get_product( $pid );
			$item_price = 0;
			if ( $product instanceof WC_product ) {
				$item_price    = $product->get_price();
				$category      = $product->get_category_ids();
				$category_name = '';
				if ( is_array( $category ) && count( $category ) > 0 ) {
					$category_id = $category[0];
					if ( is_numeric( $category_id ) && $category_id > 0 ) {
						$cat_tearm = get_term_by( 'id', $category_id, 'product_cat' );

						if ( $cat_tearm ) {
							$category_name = $cat_tearm->name;
						}
					}
				}
				$products[ $pid ]  = array(
					'name'       => $product->get_title(),
					'category'   => $category_name,
					'id'         => $pid,
					'quantity'   => $item->get_quantity(),
					'item_price' => $item_price,
				);
				$gproducts[ $pid ] = array(
					'id'       => $pid,
					'sku'      => $product->get_sku(),
					'category' => $category_name,
					'name'     => $product->get_title(),
					'quantity' => $item->get_quantity(),
					'price'    => $item_price,
				);
			}
		}
	} else {
		foreach ( $items as $item ) {
			$pid        = $item['product_id'];
			$product    = wc_get_product( $pid );
			$item_price = 0;

			if ( $product instanceof WC_product ) {
				$item_price    = $product->get_price();
				$category      = wp_get_object_terms( $pid, 'product_cat' );
				$category_name = '';
				if ( is_array( $category ) && count( $category ) > 0 ) {
					$category      = current( $category );
					$category_name = $category->name;
				}
				$products[ $pid ]  = array(
					'name'       => $product->get_title(),
					'category'   => $category_name,
					'id'         => $pid,
					'quantity'   => $item['qty'],
					'item_price' => $item_price,
				);
				$gproducts[ $pid ] = array(
					'id'       => $pid,
					'sku'      => $product->get_sku(),
					'category' => $category_name,
					'name'     => $product->get_title(),
					'quantity' => $item['qty'],
					'price'    => $item_price,
				);
			}
		}
	}

	if ( is_array( $products ) && count( $products ) === 0 ) {
		return '';
	}

	if ( $pixel_id > 0 ) {
		if ( 'on' === $facebook_purchase_advanced_matching_event ) {
			$fb_pa         = array();
			$billing_email = XLWCTY_Compatibility::get_order_data( $order, 'billing_email' );
			if ( ! empty( $billing_email ) ) {
				$fb_pa['em'] = $billing_email;
			}
			$billing_phone = XLWCTY_Compatibility::get_order_data( $order, 'billing_phone' );
			if ( ! empty( $billing_phone ) ) {
				$fb_pa['ph'] = $billing_phone;
			}
			$shipping_first_name = XLWCTY_Compatibility::get_order_data( $order, 'shipping_first_name' );
			if ( ! empty( $shipping_first_name ) ) {
				$fb_pa['fn'] = $shipping_first_name;
			}
			$shipping_last_name = XLWCTY_Compatibility::get_order_data( $order, 'shipping_last_name' );
			if ( ! empty( $shipping_last_name ) ) {
				$fb_pa['ln'] = $shipping_last_name;
			}
			$shipping_city = XLWCTY_Compatibility::get_order_data( $order, 'shipping_city' );
			if ( ! empty( $shipping_city ) ) {
				$fb_pa['ct'] = $shipping_city;
			}
			$shipping_state = XLWCTY_Compatibility::get_order_data( $order, 'shipping_state' );
			if ( ! empty( $shipping_state ) ) {
				$fb_pa['st'] = $shipping_state;
			}
			$shipping_postcode = XLWCTY_Compatibility::get_order_data( $order, 'shipping_postcode' );
			if ( ! empty( $shipping_postcode ) ) {
				$fb_pa['zp'] = $shipping_postcode;
			}
		}
		update_post_meta( $order_id, 'xlwcty_fb_gb_event_fire', 'yes' );
	}
}
?>
    <script>
        var xlwcty_fab_ecom = {
            'pixel_id': '<?php echo isset( $pixel_id ) ? $pixel_id : ''; ?>',
            'fb_pa_count': '<?php echo isset( $fb_pa ) ? count( $fb_pa ) : ''; ?>',
            'fb_pa_data': '<?php echo isset( $fb_pa ) ? wp_json_encode( $fb_pa ) : ''; ?>',
            'facebook_tracking_event': '<?php echo isset( $facebook_tracking_event ) ? $facebook_tracking_event : ''; ?>',
            'facebook_purchase_event': '<?php echo isset( $facebook_purchase_event ) ? $facebook_purchase_event : ''; ?>',
            'facebook_purchase_advanced_matching_event': '<?php echo isset( $facebook_purchase_advanced_matching_event ) ? $facebook_purchase_advanced_matching_event : ''; ?>',
            'facebook_purchase_event_conversion': '<?php echo isset( $facebook_purchase_event_conversion ) ? $facebook_purchase_event_conversion : ''; ?>',
            'google_analytics_id': '<?php echo isset( $google_analytics_id ) ? $google_analytics_id : ''; ?>',
            'products': '<?php echo isset( $products ) ? wp_json_encode( $products ) : ''; ?>',
            'order_id': '<?php echo isset( $order_id ) ? $order_id : ''; ?>',
            'order_total': '<?php echo isset( $order_total ) ? $order_total : ''; ?>',
            'currency': '<?php echo get_woocommerce_currency(); ?>',
            'shipping_total': '<?php echo isset( $order_shipping_total ) ? $order_shipping_total : ''; ?>',
            'order_tax': '<?php echo isset( $order_tax ) ? $order_tax : ''; ?>',
            'affiliation': '<?php bloginfo( 'name' ); ?>',
            'gproducts': '<?php echo isset( $gproducts ) ? wp_json_encode( $gproducts ) : ''; ?>',
        };
    </script>
<?php
