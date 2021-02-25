<?php
/**
 * File to handle privacy [GDPR] for the plugin
 *
 * @author      StoreApps
 * @since       1.0.0
 * @version     1.4.0
 *
 * @package     save-for-later-for-woocommerce/includes/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

if ( ! class_exists( 'SA_Save_For_Later_Privacy' ) ) {

	/**
	 * Class to handle privacy [GDPR] for the plugin
	 */
	class SA_Save_For_Later_Privacy extends WC_Abstract_Privacy {

		/**
		 * Variable to hold instance of SA_Save_For_Later_Privacy
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * The plugin data
		 *
		 * @var $plugin_data
		 */
		public $plugin_data = array();

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->plugin_data = SA_Save_For_Later::get_plugin_data( SA_SFL_PLUGIN_FILE );

			parent::__construct( $this->plugin_data['Name'] );

			/* translators: Plugin's name */
			$this->add_exporter( SA_SFL_PLUGIN_DIRNAME . '-saved-items-exporter', sprintf( __( '%s - Saved Items Exporter', 'save-for-later-for-woocommerce' ), $this->plugin_data['Name'] ), array( $this, 'sfl_saved_items_exporter' ) );

			/* translators: Plugin's name */
			$this->add_eraser( SA_SFL_PLUGIN_DIRNAME . '-saved-items-eraser', sprintf( __( '%s - Saved Items Eraser', 'save-for-later-for-woocommerce' ), $this->plugin_data['Name'] ), array( $this, 'sfl_saved_items_eraser' ) );
		}

		/**
		 * Get single instance of SA_Save_For_Later_Privacy
		 *
		 * @return SA_Save_For_Later_Privacy Singleton object of SA_Save_For_Later_Privacy
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Returns a list of Saved For Later Items based on email.
		 *
		 * @param string $email_address The email address of the customer.
		 * @param int    $page The page.
		 *
		 * @return array
		 */
		protected function get_sfl_saved_items( $email_address, $page ) {

			$user = get_user_by( 'email', $email_address );

			if ( function_exists( 'get_user_attribute' ) ) {
				$sfl_saved_items = get_user_attribute( $user->ID, '_sa_sfl_saved_items' );
			} else {
				$sfl_saved_items = get_user_meta( $user->ID, '_sa_sfl_saved_items', true ); // phpcs:ignore
			}

			$data = array();

			foreach ( $sfl_saved_items as $sfl_saved_items ) {
				$data[] = array(
					'product_name' => get_the_title( $sfl_saved_items['product_id'] ),
					'price'        => $sfl_saved_items['price'],
					'user_id'      => $user->ID,
				);
			}

			return $data;
		}

		/**
		 * Gets the message of the privacy to display.
		 */
		public function get_privacy_message() {

			$content = '<h2>' . __( 'Save For Later (on Cart)', 'save-for-later-for-woocommerce' ) . '</h2>
						<strong>' . __( 'What we store?', 'save-for-later-for-woocommerce' ) . '</strong>
						<p>' . __( 'When you are on the Cart page and move products from "Cart" to "Saved For Later" list (using the button "Save For Later"), we store product ids with their prices', 'save-for-later-for-woocommerce' ) . '</p>
						<strong>' . __( 'Where we store?', 'save-for-later-for-woocommerce' ) . '</strong>
						<ul>
							<li>' . __( 'If you are logged in: We store it in database & map it to your account', 'save-for-later-for-woocommerce' ) . '</li>
							<li>' . __( 'If you are a visitor: We store it in database & map it to your cookies', 'save-for-later-for-woocommerce' ) . '</li>
						</ul>
						<strong>' . __( 'Export/Delete Personal Data', 'save-for-later-for-woocommerce' ) . '</strong>
						<p>' . __( 'Exporting personal data is available for the registered user only. Export/delete of personal data for registered user will be processed only after receiving confirmation about it. For deleting personal data, a visitor can simply delete cookies from his/her browser.', 'save-for-later-for-woocommerce' ) . '</p>';

			return $content;

		}

		/**
		 * Handle exporting data for Saved For Later Items.
		 *
		 * @param string $email_address E-mail address to export.
		 * @param int    $page          Pagination of data.
		 *
		 * @return array
		 */
		public function sfl_saved_items_exporter( $email_address, $page = 0 ) {
			$done           = false;
			$data_to_export = array();

			$sfl_saved_items = $this->get_sfl_saved_items( $email_address, (int) $page );

			if ( 0 < count( $sfl_saved_items ) ) {
				$data  = array();
				$index = 0;
				foreach ( $sfl_saved_items as $sfl_saved_item ) {
					$index++;
					$data[] = array(
						/* translators: The serial number or position of the saved item */
						'name'  => sprintf( __( 'Saved Item %d', 'save-for-later-for-woocommerce' ), $index ),
						'value' => $sfl_saved_item['product_name'] . ' - ' . wc_price( $sfl_saved_item['price'] ),
					);
				}
				$data_to_export[] = array(
					'group_id'    => 'sa_save_for_later',
					'group_label' => __( 'Saved For Later Items', 'save-for-later-for-woocommerce' ),
					'item_id'     => 'sfl-saved-item-' . sanitize_title( $email_address ),
					'data'        => $data,
				);
				$done             = 10 > count( $sfl_saved_items );
			} else {
				$done = true;
			}

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Finds and erases Saved Item by email address.
		 *
		 * @param string $email_address The user email address.
		 * @param int    $page  Page.
		 * @return array An array of personal data in name value pairs
		 */
		public function sfl_saved_items_eraser( $email_address, $page ) {

			$sfl_saved_items = $this->get_sfl_saved_items( $email_address, (int) $page );

			$items_removed  = false;
			$items_retained = false;
			$messages       = array();

			list( $removed, $retained, $msgs ) = $this->maybe_handle_saved_items( $sfl_saved_items );
			$items_removed                    |= $removed;
			$items_retained                   |= $retained;
			$messages                          = array_merge( $messages, $msgs );

			// Tell core if we have more items to work on still.
			$done = count( $sfl_saved_items ) < 10;

			return array(
				'items_removed'  => $items_removed,
				'items_retained' => $items_retained,
				'messages'       => $messages,
				'done'           => $done,
			);
		}

		/**
		 * Handle eraser of Saved Item data
		 *
		 * @param array $sfl_saved_items The saved items.
		 * @return array
		 */
		protected function maybe_handle_saved_items( $sfl_saved_items ) {
			if ( empty( $sfl_saved_items ) ) {
				return array( false, false, array() );
			}

			$user_ids      = wp_list_pluck( $sfl_saved_items, 'user_id' );
			$user_ids      = array_unique( $user_ids );
			$product_names = wp_list_pluck( $sfl_saved_items, 'product_name' );
			$prices        = wp_list_pluck( $sfl_saved_items, 'price' );
			$prices        = array_map( 'wc_price', $prices );

			$saved_items_description = '';

			if ( ! empty( $product_names ) && ! empty( $prices ) ) {
				$saved_items_description = array_map(
					function ( $product_name, $price ) {
							return '<br>[' . $product_name . ' (' . $price . ')]';
					},
					$product_names,
					$prices
				);
				$saved_items_description = implode( ', ', $saved_items_description );
			}

			if ( ! empty( $user_ids ) ) {
				foreach ( $user_ids as $user_id ) {
					if ( function_exists( 'delete_user_attribute' ) ) {
						delete_user_attribute( $user_id, '_sa_sfl_saved_items' );
					} else {
						delete_user_meta( $user_id, '_sa_sfl_saved_items' ); // phpcs:ignore
					}
				}
				/* translators: 1. The plugin's name   2. Saved item's descriptions */
				return array( true, false, array( sprintf( __( '%1$s - Removed Saved Item:%2$s', 'save-for-later-for-woocommerce' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', $saved_items_description ) ) );
			}

			return array( false, false, array() );

		}
	}

}

SA_Save_For_Later_Privacy::get_instance();
