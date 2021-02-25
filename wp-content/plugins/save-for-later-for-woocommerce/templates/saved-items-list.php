<?php
/**
 * Template for saved item list
 *
 * @author      StoreApps
 * @since       1.0.0
 * @version     1.4.1
 *
 * @package     save-for-later-for-woocommerce/templates/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $sa_save_for_later;

?>
<style type="text/css">
	.sa_saved_items_list_container table tbody tr td {
		border-bottom: 1pt solid #D3D3D3;
		border-top: 1pt solid #D3D3D3;
	}
	.sa_saved_items_list_container table .product-thumbnail {
		width: 12em;
	}
	.sa_saved_items_list_container table .product-name {
		width: 60%;
	}
	.sa_saved_items_list_container table .product-name a {
		font-weight: bold;
		font-size: x-large;
		color: #0000cc;
	}
	.sa_saved_items_list_container table .product-name p {
		margin: 0em 0em 1em 0em;
	}
	.sa_saved_items_list_container table .product-name p.sa_product_meta {
		font-size: medium;
	}
	.sa_saved_items_list_container table .product-name p.sa_saved_item_actions a:first-child {
		margin-left: -1em;
	}
	.sa_saved_items_list_container table .product-name p.sa_saved_item_actions a {
		font-weight: normal;
		font-size: small;
		padding: 0em 1em;
	}
	.sa_saved_items_list_container table .product-price {
		font-weight: bolder;
		font-size: x-large;
		color: #cc0000;
	}
	.sa_saved_items_list_wrapper .sa_saved_item_actions a.sa_delete_saved_item {
		cursor: pointer;
	}
	@media only screen and (max-width: 760px), (min-device-width: 768px) and (max-device-width: 1024px)  {
		.sa_saved_items_list_container table .product-thumbnail {
			width: auto;
		}
		.sa_saved_items_list_container table .product-thumbnail:before {
			content: "";
		}
		.sa_saved_items_list_container table .product-name {
			width: auto;
		}
		.sa_saved_items_list_container table tbody tr td {
			border: none;
		}
	}
</style>
<div class="sa_saved_items_list_wrapper">
	<div class="sa_saved_items_list_container">
		<?php /* translators: number of saved items in list */ ?>
		<h2><?php echo esc_html__( 'Saved for later', 'save-for-later-for-woocommerce' ) . ' (<span class="sa_saved_item_count">' . count( $cart_items ) . '</span>' . esc_html( _n( ' item', ' items', count( $cart_items ), 'save-for-later-for-woocommerce' ) ) . ')'; ?></h2>
		<table class="shop_table shop_table_responsive" cellspacing="0">
			<tbody>
				<?php
				foreach ( $cart_items as $cart_item_key => $cart_item ) {
					$_product  = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					?>
					<tr>
						<td class="product-thumbnail">
							<?php
							if ( ! $_product->is_visible() ) {
								echo $thumbnail; // phpcs:ignore
							} else {
								printf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink( $cart_item ) ), $thumbnail ); // phpcs:ignore
							}
							?>
						</td>
						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'save-for-later-for-woocommerce' ); ?>">
							<?php
							if ( ! $_product->is_visible() ) {
								echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;'; // phpcs:ignore
							} else {
								echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink( $cart_item ) ), $_product->get_title() ), $cart_item, $cart_item_key ); // phpcs:ignore
							}

							// Meta data.
							if ( SA_Save_For_Later::is_wc_gte_33() ) {
								$meta_data = wc_get_formatted_cart_item_data( $cart_item, $flat = true );
							} else {
								$meta_data = WC()->cart->get_item_data( $cart_item, $flat = true );
							}
							if ( ! empty( $meta_data ) ) {
								echo '<p class="sa_product_meta">' . esc_html( $meta_data ) . '</p>';
							}

							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'save-for-later-for-woocommerce' ) . '</p>';
							} else {
								$availability      = $_product->get_availability();
								$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';
								echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $_product ); // phpcs:ignore
							}

							if ( $sa_save_for_later->is_wc_gte_30() ) {
								$product_id = $_product->get_id();
							} else {
								$product_id = $_product->id;
							}

								$saved_item_action_links = array(
									sprintf(
										'<a class="sa_delete_saved_item" title="%s" data-product_id="%s">%s</a>',
										esc_attr__( 'Delete this item from Saved for later list', 'save-for-later-for-woocommerce' ),
										esc_attr( $product_id ),
										esc_html__( 'Delete', 'save-for-later-for-woocommerce' )
									),
									sprintf(
										'<a href="%s" class="sa_move_to_cart" title="%s" data-product_id="%s">%s</a>',
										esc_url( $sa_save_for_later->get_move_to_cart_url( $cart_item_key ) ),
										esc_attr__( 'Move this item to cart', 'save-for-later-for-woocommerce' ),
										esc_attr( $product_id ),
										esc_html__( 'Move to cart', 'save-for-later-for-woocommerce' )
									),
								);

								$saved_item_action_links = apply_filters( 'saved_item_action_links', $saved_item_action_links, $_product );

								echo '<p class="sa_saved_item_actions">' . implode( ' | ', $saved_item_action_links ) . '</p>'; // phpcs:ignore

								?>
						</td>
						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'save-for-later-for-woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore
							?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
