<?php
/** 
 * @package Next-Cart
 * 
 */

class NCF_Template {
	
	
	public static function add_base_pixel() {
        ?>
		<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '450591282464778');
		
		document.addEventListener('DOMContentLoaded', function() {
		  jQuery && jQuery(function($){
			$('body').on('added_to_cart', function(event) {
			  // Ajax action.
			  $.get('?wc-ajax=nc_add_to_cart_event', function(data) {
				$('head').append(data);
			  });
			});
		  });
		}, false);
		</script>
		<?php
    }
	
	public static function nc_add_to_cart_event() {

		ob_start();

		echo '<script>';
		
		$product_ids = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$shopify_id = get_post_meta( $cart_item['product_id'], '_shopify_id', true );
				$product_id = $shopify_id ? $shopify_id : $cart_item['product_id'];
				$product_ids[] = $product_id;
			}
		}
		$checkout_total = WC()->cart->get_total('number');

		//$product_ids = $this->get_content_ids_from_cart(WC()->cart->get_cart());

		echo "fbq('track', 'AddToCart', {
			content_ids: ".json_encode($product_ids).",
			content_type: 'product_group',
			currency: 'USD',
			value: ".$checkout_total."
		});";
		echo '</script>';

		$pixel = ob_get_clean();

		wp_send_json($pixel);
	}
	
	public static function nc_echo_add_to_cart_event() {

		//$code = '<script>';
		
		$product_ids = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$shopify_id = get_post_meta( $cart_item['product_id'], '_shopify_id', true );
				$product_id = $shopify_id ? $shopify_id : $cart_item['product_id'];
				$product_ids[] = $product_id;
			}
		}
		$checkout_total = WC()->cart->get_total('number');

		//$product_ids = $this->get_content_ids_from_cart(WC()->cart->get_cart());

		$code = "fbq('track', 'AddToCart', {
			content_ids: ".json_encode($product_ids).",
			content_type: 'product_group',
			currency: 'USD',
			value: ".$checkout_total."
		});";
		//$code .= '</script>';
		global $wc_queued_js;
		if (function_exists('wc_enqueue_js') && empty($wc_queued_js)) {
			wc_enqueue_js($code);
		  } else {
			$wc_queued_js = $code."\n".$wc_queued_js;
		  }

	}
	
	public static function product_page() {
		global $product;
        ?>
		<script>
			<?php
			//$children_ids = $product->get_children();
			//$children_id = !empty($children_ids) ? reset($children_ids) : $product->get_id();
			$shopify_id = get_post_meta( $product->get_id(), '_shopify_id', true );
			$product_price = $product->get_price();
			if (!$product_price && count($product->get_children()) > 0) {
				$product_price = get_post_meta( reset($product->get_children()), '_price', true );
			}
			?>
			dataLayer.push({
				'nc_productGroupId': <?php echo $shopify_id ? $shopify_id : $product->get_id(); ?>,
				'nc_productPrice': <?php echo $product_price; ?>
			});
		</script>
		<?php
    }
	
	public static function product_quickview() {
		global $product;
        ?>
		<input type="hidden" name="shopify_id" id="shopify_id" value="<?php echo get_post_meta( $product->get_id(), '_shopify_id', true ) ? get_post_meta( $product->get_id(), '_shopify_id', true ) : $product->get_id(); ?>">
		<input type="hidden" name="shopify_price" id="shopify_price" value="<?php echo $product->get_price() ? $product->get_price() : get_post_meta( reset($product->get_children()), '_price', true ); ?>">
		<?php
    }
	
	public static function cart_page() {
        ?>
		<script>
			<?php
			$products = array();
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$shopify_id = get_post_meta( $cart_item['product_id'], '_shopify_id', true );
					$product_id = $shopify_id ? $shopify_id : $cart_item['product_id'];
					$products[] = array(
						'id' => $product_id,
						'name' => $_product->get_name(),
						'sku' => $_product->get_sku(),
						'price' => $_product->get_price(),
						'quantity' => $cart_item['quantity']
					);
				}
			}
			$checkout_total = WC()->cart->get_total('number');
			?>
			dataLayer.push({
				'checkoutTotal': <?php echo $checkout_total; ?>,
				'checkoutProducts': <?php echo json_encode($products); ?>
			});
		</script>
		<?php
    }
	
	public static function checkout_page() {
        ?>
		<script>
			<?php
			$products = array();
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$shopify_id = get_post_meta( $cart_item['product_id'], '_shopify_id', true );
					$product_id = $shopify_id ? $shopify_id : $cart_item['product_id'];
					$products[] = array(
						'id' => $product_id,
						'name' => $_product->get_name(),
						'sku' => $_product->get_sku(),
						'price' => $_product->get_price(),
						'quantity' => $cart_item['quantity']
					);
				}
			}
			$checkout_total = WC()->cart->get_total('number');
			?>
			dataLayer.push({
				'checkoutTotal': <?php echo $checkout_total; ?>,
				'checkoutProducts': <?php echo json_encode($products); ?>
			});
		</script>
		<?php
    }
	
	public static function thankyou_page($order_id) {
		$order = wc_get_order( $order_id );
        ?>
		<script>
			<?php
			$products = array();
			foreach ($order->get_items() as $item) {
				$_product = $order->get_product_from_item( $item );
				$shopify_id = get_post_meta( $item['product_id'], '_shopify_id', true );
				$product_id = $shopify_id ? $shopify_id : $item['product_id'];
				$products[] = array(
					'id' => $product_id,
					'name' => $_product->get_name(),
					'sku' => $_product->get_sku(),
					'price' => $item['total'],
					'quantity' => $item['quantity']
				);
			?>
			<?php } ?>
			dataLayer.push({
				'transactionTotal': <?php echo $order->get_total(); ?>,
				'transactionId': <?php echo $order->get_id(); ?>,
				'transactionProducts': <?php echo json_encode($products); ?>
			});
		</script>
		<?php
    }
}