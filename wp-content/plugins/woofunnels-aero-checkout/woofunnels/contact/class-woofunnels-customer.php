<?php
/**
 * Customer Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_Customer
 */
class WooFunnels_Customer {
	/**
	 * public db_operations $db_operations
	 */
	public $db_operations;

	/**
	 * public id $id
	 */
	public $id;

	/**
	 * public cid $cid
	 */
	public $cid;

	/**
	 * public contact $contact
	 */
	public $contact;

	/**
	 * public changes $changes
	 */
	public $changes;

	/**
	 * @var $db_customer
	 */
	public $db_customer;

	/**
	 * Get the customer details for the contact object passed if this contact id exits otherwise create a new customer
	 * WooFunnels_Customer constructor.
	 *
	 * @param $cid
	 *
	 */
	public function __construct( $contact ) {
		$this->db_operations = WooFunnels_DB_Operations::get_instance();

		$this->cid = $contact->get_id();

		$this->contact = $contact;

		if ( empty( $this->cid ) || ( $this->cid < 1 ) ) {
			return;
		}

		$this->db_customer = $this->get_customer_by_cid( $this->cid );

		if ( isset( $this->db_customer->id ) && $this->db_customer->id > 0 ) {
			$this->id = $this->db_customer->id;
		}

		$contact_meta = $this->db_operations->get_contact_metadata( $this->cid );
		if ( ! isset( $this->contact->meta ) ) {
			$this->contact->meta = new stdClass();
		}

		foreach ( is_array( $contact_meta ) ? $contact_meta : array() as $meta ) {
			$this->contact->meta->{$meta->meta_key} = $meta->meta_value;
		}

		$changes = empty( $this->contact->meta->changes ) ? array() : json_decode( $this->contact->meta->changes, true );

		$customer_changes = isset( $changes['customer'] ) ? $changes['customer'] : array();

		if ( ! isset( $this->changes ) ) {
			$this->changes = new stdClass();
		}
		foreach ( $customer_changes as $meta_key => $change ) {
			$this->changes->{$meta_key} = $change;
		}
	}

	/**
	 * Get customer by cid
	 *
	 * @param $wpid
	 *
	 * @return mixed
	 */
	public function get_customer_by_cid( $cid ) {
		return $this->db_operations->get_customer_by_cid( $cid );
	}

	/**
	 * Get customer created date
	 */
	public function get_creation_date() {
		return isset( $this->db_customer->creation_date ) && ! empty( $this->db_customer->creation_date ) ? $this->db_customer->creation_date : '0000-00-00';
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_l_order_date( $date ) {
		$this->l_order_date = empty( $date ) ? $this->get_l_order_date() : $date;
		if ( ! isset( $this->changes ) ) {
			$this->changes = new stdClass();
		}

		$this->changes->l_order_date = $this->l_order_date;
	}

	/**
	 * Get customer last order date
	 */
	public function get_l_order_date() {
		$changes = ( isset( $this->changes->l_order_date ) && ! empty( $this->changes->l_order_date ) ) ? $this->changes->l_order_date : '0000-00-00';
		$db_data = ( isset( $this->db_customer->l_order_date ) && ! empty( $this->db_customer->l_order_date ) ) ? $this->db_customer->l_order_date : '0000-00-00';

		return ( '0000-00-00' !== $changes ) ? $changes : $db_data;
	}

	/**
	 * Set total order count
	 *
	 * @param $count
	 */
	public function set_total_order_count( $count ) {
		$this->total_order_count = ( $count >= 0 ) ? $count : $this->get_total_order_count();
		if ( ! isset( $this->changes ) ) {
			$this->changes = new stdClass();
		}
		$this->changes->total_order_count = $this->total_order_count;
	}

	/**
	 * Get total order count
	 */
	public function get_total_order_count() {
		$changes = ( isset( $this->changes->total_order_count ) && ! empty( $this->changes->total_order_count ) ) ? $this->changes->total_order_count : 0;
		$db_data = ( isset( $this->db_customer->total_order_count ) && ! empty( $this->db_customer->total_order_count ) ) ? $this->db_customer->total_order_count : 0;

		return ( $changes > 0 ) ? $changes : $db_data;
	}

	/**
	 * Set total order value
	 *
	 * @param $value
	 */
	public function set_total_order_value( $value ) {
		$this->total_order_value = ( $value >= 0 ) ? $value : $this->get_total_order_value();

		$this->changes->total_order_value = $this->total_order_value;

	}

	/**
	 * Get total order value
	 */
	public function get_total_order_value() {
		$changes = ( isset( $this->changes->total_order_value ) && ! empty( $this->changes->total_order_value ) ) ? $this->changes->total_order_value : 0;
		$db_data = ( isset( $this->db_customer->total_order_value ) && ! empty( $this->db_customer->total_order_value ) ) ? $this->db_customer->total_order_value : 0;

		return ( $changes > 0 ) ? $changes : $db_data;
	}

	/**
	 * Set purchased products
	 *
	 * @param $products
	 */
	public function set_purchased_products( $products ) {
		$this->purchased_products = empty( $products ) ? $this->get_purchased_products() : $products;

		$this->changes->purchased_products = $this->purchased_products;
	}

	/**
	 * Get purchased products
	 *
	 */
	public function get_purchased_products() {

		$changes = isset( $this->changes ) && isset( $this->changes->purchased_products ) ? $this->changes->purchased_products : array();
		$changes = ! empty( $changes ) && ! is_array( $changes ) ? json_decode( $changes, true ) : $changes;

		$products = isset( $this->db_customer->purchased_products ) ? $this->db_customer->purchased_products : array();
		$products = ! empty( $products ) && ! is_array( $products ) ? json_decode( $products, true ) : $products;
		$products = is_array( $products ) ? $products : array();

		return ( is_array( $changes ) && count( $changes ) > 0 ) ? $changes : $products;
	}

	/**
	 * Set purchased product categories
	 *
	 * @param $cats
	 */
	public function set_purchased_products_cats( $cats ) {
		$this->purchased_products_cats = empty( $cats ) ? $this->get_purchased_products_cats() : $cats;

		$this->changes->purchased_products_cats = $this->purchased_products_cats;
	}

	/**
	 * Get purchased product categories
	 *
	 */
	public function get_purchased_products_cats() {

		$changes = isset( $this->changes ) && isset( $this->changes->purchased_products_cats ) ? $this->changes->purchased_products_cats : array();
		$changes = ! empty( $changes ) && ! is_array( $changes ) ? json_decode( $changes, true ) : $changes;

		$cats = isset( $this->db_customer->purchased_products_cats ) ? $this->db_customer->purchased_products_cats : array();
		$cats = ! empty( $cats ) && ! is_array( $cats ) ? json_decode( $cats, true ) : $cats;
		$cats = is_array( $cats ) ? $cats : array();

		return ( is_array( $changes ) && count( $changes ) > 0 ) ? $changes : $cats;

	}

	/**
	 * Set purchased product tags
	 *
	 * @param $tags
	 */
	public function set_purchased_products_tags( $tags ) {
		$this->purchased_products_tags = empty( $tags ) ? $this->get_purchased_products_tags() : $tags;

		$this->changes->purchased_products_tags = $this->purchased_products_tags;
	}

	/**
	 * Get purchased product tags
	 *
	 */
	public function get_purchased_products_tags() {

		$changes = isset( $this->changes ) && isset( $this->changes->purchased_products_tags ) ? $this->changes->purchased_products_tags : array();
		$changes = ! empty( $changes ) && ! is_array( $changes ) ? json_decode( $changes, true ) : $changes;

		$tags = isset( $this->db_customer->purchased_products_tags ) ? $this->db_customer->purchased_products_tags : array();
		$tags = ! empty( $tags ) && ! is_array( $tags ) ? json_decode( $tags, true ) : $tags;
		$tags = is_array( $tags ) ? $tags : array();

		return ( is_array( $changes ) && count( $changes ) > 0 ) ? $changes : $tags;

	}

	/**
	 * Set used coupons
	 *
	 * @param $state
	 */
	public function set_used_coupons( $coupons ) {
		$this->used_coupons = empty( $coupons ) ? $this->get_used_coupons() : $coupons;

		$this->changes->used_coupons = $this->used_coupons;

	}

	/**
	 * Get customer used coupons
	 */
	public function get_used_coupons() {

		$changes = isset( $this->changes ) && isset( $this->changes->used_coupons ) ? $this->changes->used_coupons : array();
		$changes = ! empty( $changes ) && ! is_array( $changes ) ? json_decode( $changes, true ) : $changes;

		$coupons = isset( $this->db_customer->used_coupons ) ? $this->db_customer->used_coupons : array();
		$coupons = ! empty( $coupons ) && ! is_array( $coupons ) ? json_decode( $coupons, true ) : $coupons;
		$coupons = is_array( $coupons ) ? $coupons : array();

		return ( is_array( $changes ) && count( $changes ) > 0 ) ? $changes : $coupons;

	}

	/**
	 * Set customer created date
	 *
	 * @param $date
	 */
	public function set_creation_date( $date ) {
		$this->creation_date = $date;
	}

	/**
	 * Updating customer table with set data
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function save( $force = false ) {

		$customer = array();

		if ( ( $this->get_id() > 0 ) && $force ) {
			if ( isset( $this->changes->l_order_date ) ) {
				$customer['l_order_date'] = $this->changes->l_order_date;
			}
			if ( isset( $this->changes->total_order_count ) ) {
				$customer['total_order_count'] = $this->changes->total_order_count;
			}
			if ( isset( $this->changes->total_order_value ) ) {
				$customer['total_order_value'] = $this->changes->total_order_value;
			}
			if ( isset( $this->changes->purchased_products ) ) {
				$customer['purchased_products'] = wp_json_encode( $this->changes->purchased_products );
			}
			if ( isset( $this->changes->purchased_products_cats ) ) {
				$customer['purchased_products_cats'] = wp_json_encode( $this->changes->purchased_products_cats );
			}
			if ( isset( $this->changes->purchased_products_tags ) ) {
				$customer['purchased_products_tags'] = wp_json_encode( $this->changes->purchased_products_tags );
			}
			if ( isset( $this->changes->used_coupons ) ) {
				$customer['used_coupons'] = wp_json_encode( $this->changes->used_coupons );
			}

			if ( count( $customer ) > 0 ) {
				$customer['id'] = $this->get_id();
				$this->db_operations->update_customer( $customer );
			}
		} elseif ( empty( $this->get_id() ) ) {
			$customer['cid']                     = $this->get_cid();
			$customer['l_order_date']            = empty( $this->get_l_order_date() ) ? '0000-00-00' : $this->get_l_order_date();
			$customer['total_order_count']       = empty( $this->get_total_order_count() ) ? 0 : $this->get_total_order_count();
			$customer['total_order_value']       = empty( $this->get_total_order_value() ) ? 0 : $this->get_total_order_value();
			$customer['purchased_products']      = wp_json_encode( $this->get_purchased_products() );
			$customer['purchased_products_cats'] = wp_json_encode( $this->get_purchased_products_cats() );
			$customer['purchased_products_tags'] = wp_json_encode( $this->get_purchased_products_tags() );
			$customer['used_coupons']            = wp_json_encode( $this->get_used_coupons() );

			$this->id = $this->db_operations->insert_customer( $customer );
		}
	}

	/**
	 * Get customer id
	 */
	public function get_id() {

		$changed_id = ( isset( $this->changes->id ) && $this->changes->id > 0 ) ? $this->changes->id : 0;
		$id         = ( isset( $this->id ) && $this->id > 0 ) ? $this->id : 0;
		$db_id      = ( isset( $this->db_customer->id ) && ( $this->db_customer->id > 0 ) ) ? $this->db_customer->id : 0;

		$result = $changed_id > 0 ? $changed_id : $id;

		return ( $result > 0 ) ? $result : $db_id;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_id( $id ) {
		$this->id = empty( $id ) ? $this->id : $id;
	}

	/**
	 * Get customer cid
	 */
	public function get_cid() {

		$db_cid = ( isset( $this->db_customer->cid ) && ( $this->db_customer->cid > 0 ) ) ? $this->db_customer->cid : 0;

		return ( isset( $this->changes->cid ) && ( $this->changes->cid > 0 ) ) ? $this->changes->cid : $db_cid;
	}

	/**
	 * Set customer last order date
	 *
	 * @param $date
	 */
	public function set_cid( $cid ) {
		$this->cid          = empty( $cid ) ? $this->get_cid() : $cid;
		$this->changes->cid = $this->cid;
	}

	/**
	 * Get customer by id
	 *
	 * @param $customer_id
	 *
	 * @return mixed
	 */
	public function get_customer_by_customer_id( $customer_id ) {
		return $this->db_operations->get_customer_by_customer_id( $customer_id );
	}

}
