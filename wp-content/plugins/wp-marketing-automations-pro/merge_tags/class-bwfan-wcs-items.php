<?php

class BWFAN_WCS_Items extends Merge_Tag_Abstract_Product_Display {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'subscription_items';
		$this->tag_description = __( 'Subscription Items', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_subscription_items', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			$subscription = BWFAN_Merge_Tag_Loader::get_data( 'wc_subscription' );
			$items        = $subscription->get_items();
			$products     = [];
			foreach ( $items as $item ) {
				$products[] = $item->get_product();
			}
			$this->products = $products;
		}

		$output = $this->process_shortcode( $attr );

		return $this->parse_shortcode_output( $output, $attr );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() && bwfan_is_woocommerce_subscriptions_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_subscription', 'BWFAN_WCS_Items' );
}
