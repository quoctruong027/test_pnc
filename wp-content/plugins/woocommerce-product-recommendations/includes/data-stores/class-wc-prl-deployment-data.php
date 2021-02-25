<?php
/**
 * WC_PRL_Deployment_Data class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deployment Data model class.
 *
 * @class    WC_PRL_Deployment_Data
 * @version  1.0.0
 */
class WC_PRL_Deployment_Data {

	/**
	 * Data array, with defaults.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'              => 0,
		'active'          => 'on',
		'engine_id'       => 0,
		'engine_type'     => '',
		'title'           => '',
		'description'     => '',
		'display_order'   => 0,
		'columns'         => 4,
		'limit'           => 4,
		'location_id'     => '',
		'hook'            => '',
		'conditions_data' => array()
	);

	/**
	 * Stores meta data, defaults included.
	 * Meta keys are assumed unique by default. No meta is internal.
	 *
	 * @var array
	 */
	protected $meta_data = array();

	/**
	 * Constructor.
	 *
	 * @param  int|object|array  $item  ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $deployment = 0 ) {
		if ( $deployment instanceof WC_PRL_Deployment_Data ) {
			$this->set_all( $deployment->get_data() );
		} elseif ( is_array( $deployment ) ) {
			$this->set_all( $deployment );
		} else {
			$this->read( $deployment );
		}
	}

	/*---------------------------------------------------*/
	/*  Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data() {
		return array_merge( $this->data, array( 'meta_data' => $this->get_meta_data() ) );
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->data[ 'id' ] );
	}

	/**
	 * Get engine id.
	 *
	 * @return int
	 */
	public function get_engine_id() {
		return absint( $this->data[ 'engine_id' ] );
	}

	/**
	 * Get engine type.
	 *
	 * @return string
	 */
	public function get_engine_type() {
		return $this->data[ 'engine_type' ];
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->data[ 'title' ];
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->data[ 'description' ];
	}

	/**
	 * Get display order.
	 *
	 * @return int
	 */
	public function get_display_order() {
		return absint( $this->data[ 'display_order' ] );
	}

	/**
	 * Get columns count.
	 *
	 * @return int
	 */
	public function get_columns() {
		return absint( $this->data[ 'columns' ] );
	}

	/**
	 * Get max number of products.
	 *
	 * @return int
	 */
	public function get_limit() {
		return absint( $this->data[ 'limit' ] );
	}

	/**
	 * Get location id.
	 *
	 * @return string
	 */
	public function get_location_id() {
		return $this->data[ 'location_id' ];
	}

	/**
	 * Get hook name.
	 *
	 * @return string
	 */
	public function get_hook() {
		return $this->data[ 'hook' ];
	}

	/**
	 * Get conditions data.
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_conditions_data( $context = 'view' ) {
		return is_array( $this->data[ 'conditions_data' ] ) ? $this->data[ 'conditions_data' ] : array();
	}

	/**
	 * Get All Meta Data.
	 *
	 * @return array
	 */
	public function get_meta_data() {
		return array_filter( $this->meta_data, array( $this, 'has_meta_value' ) );
	}

	/*---------------------------------------------------*/
	/*  Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set all data based on input array.
	 *
	 * @param  array  $data
	 */
	public function set_all( $data ) {
		foreach ( $data as $key => $value ) {
			if ( is_callable( array( $this, "set_$key" ) ) ) {
				$this->{"set_$key"}( $value );
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	/**
	 * Set Deployment ID.
	 *
	 * @param  int
	 */
	public function set_id( $value ) {
		$this->data[ 'id' ] = absint( $value );
	}

	/**
	 * Set active.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_active( $value ) {
		$this->data[ 'active' ] = 'on' === $value ? 'on' : 'off';
	}

	/**
	 * Set engine id.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_engine_id( $value ) {
		$this->data[ 'engine_id' ] = absint( $value );
	}

	/**
	 * Set engine type.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_engine_type( $value ) {
		$this->data[ 'engine_type' ] = $value;
	}

	/**
	 * Set title.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_title( $value ) {
		$this->data[ 'title' ] = $value;
	}

	/**
	 * Set description.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_description( $value ) {
		$this->data[ 'description' ] = $value;
	}

	/**
	 * Set display order.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_display_order( $value ) {
		$this->data[ 'display_order' ] = absint( $value );
	}

	/**
	 * Set columns number.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_columns( $value ) {
		$this->data[ 'columns' ] = absint( $value );
	}

	/**
	 * Set max number of products.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_limit( $value ) {
		$this->data[ 'limit' ] = absint( $value );
	}

	/**
	 * Set location id.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_location_id( $value ) {
		$this->data[ 'location_id' ] = $value;
	}

	/**
	 * Set hook.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_hook( $value ) {
		$this->data[ 'hook' ] = $value;
	}

	/**
	 * Set conditions data.
	 *
	 * @param  array
	 * @return void
	 */
	public function set_conditions_data( $value ) {
		$this->data[ 'conditions_data' ] = maybe_unserialize( $value );
	}

	/**
	 * Set all meta data from array.
	 *
	 * @param array $data
	 */
	public function set_meta_data( $data ) {
		if ( ! empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->meta_data[ $key ] = $this->sanitize_meta_value( $value, $key );
			}
		}
	}

	/*---------------------------------------------------*/
	/*  CRUD.                                            */
	/*---------------------------------------------------*/

	/**
	 * Insert data into the database.
	 */
	private function create() {
		global $wpdb;

		$data = array(
			'engine_id'       => $this->get_engine_id(),
			'engine_type'     => $this->get_engine_type(),
			'active'          => $this->is_active() ? 'on' : 'off',
			'title'           => $this->get_title(),
			'description'     => $this->get_description(),
			'display_order'   => $this->get_display_order(),
			'columns'         => $this->get_columns(),
			'limit'           => $this->get_limit(),
			'location_id'     => $this->get_location_id(),
			'conditions_data' => maybe_serialize( $this->get_conditions_data() ),
			'hook'            => $this->get_hook()
		);

		$wpdb->insert( $wpdb->prefix . 'woocommerce_prl_deployments', $data, array( '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' ) );

		$this->set_id( $wpdb->insert_id );
	}

	/**
	 * Update data in the database.
	 */
	private function update() {
		global $wpdb;

		$data = array(
			'engine_id'       => $this->get_engine_id(),
			'engine_type'     => $this->get_engine_type(),
			'active'          => $this->is_active() ? 'on' : 'off',
			'title'           => $this->get_title(),
			'description'     => $this->get_description(),
			'display_order'   => $this->get_display_order(),
			'columns'         => $this->get_columns(),
			'limit'           => $this->get_limit(),
			'location_id'     => $this->get_location_id(),
			'conditions_data' => maybe_serialize( $this->get_conditions_data() ),
			'hook'            => $this->get_hook()
		);

		$updated = $wpdb->update( $wpdb->prefix . 'woocommerce_prl_deployments', $data, array( 'id' => $this->get_id() ), array( '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' ) );

		// Clear caches.
		WC_PRL()->db->deployment->clear_caches( array( $this->get_id() ) );

		do_action( 'woocommerce_prl_update_deployment', $this );

		return $updated;
	}

	/**
	 * Delete data from the database.
	 */
	public function delete() {

		if ( $this->get_id() ) {
			global $wpdb;

			do_action( 'woocommerce_prl_before_delete_deployment', $this );

			// Delete and clean up.
			$wpdb->delete( $wpdb->prefix . 'woocommerce_prl_deployments', array( 'id' => $this->get_id() ) );
			$wpdb->delete( $wpdb->prefix . 'woocommerce_prl_deploymentmeta', array( 'prl_deployment_id' => $this->get_id() ) );

			do_action( 'woocommerce_prl_delete_deployment', $this );
		}
	}

	/**
	 * Save data to the database.
	 *
	 * @return int
	 */
	public function save() {

		$this->validate();

		if ( ! $this->get_id() ) {
			$this->create();
		} else {
			$this->update();
		}

		$this->save_meta_data();

		return $this->get_id();
	}

	/**
	 * Read from DB deployment object using ID.
	 *
	 * @param  int $deployment
	 * @return void
	 */
	public function read( $deployment ) {
		global $wpdb;

		if ( is_numeric( $deployment ) && ! empty( $deployment ) ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_prl_deployments WHERE id = %d LIMIT 1;", $deployment ) );
		} elseif ( ! empty( $deployment->id ) ) {
			$data = $deployment;
		} else {
			$data = false;
		}

		if ( $data ) {
			$this->set_all( $data );
			$this->read_meta_data();
		}
	}

	/**
	 * Validates before saving for sanity.
	 */
	public function validate() {

		// Conditions sanity.
		$conditions_data = $this->get_conditions_data();
		foreach ( $conditions_data as $index => $data ) {

			$condition = WC_PRL()->conditions->get_condition( $data[ 'id' ] );

			if ( ! $condition ) {
				unset( $conditions_data[ $index ] );
			}

			$has_value = isset( $data[ 'value' ] ) && ( ! empty( $data[ 'value' ] ) || $data[ 'value' ] === '0' );
			if ( $condition->needs_value && ! $has_value ) {
				unset( $conditions_data[ $index ] );
			}
		}

		$this->set_conditions_data( $conditions_data );
	}

	/*---------------------------------------------------*/
	/*  Meta CRUD.                                       */
	/*---------------------------------------------------*/

	/**
	 * Read meta data from the database.
	 */
	protected function read_meta_data() {

		$this->meta_data = array();

		if ( ! $this->get_id() ) {
			return;
		}

		global $wpdb;
		$raw_meta_data = $wpdb->get_results( $wpdb->prepare( "
			SELECT meta_id, meta_key, meta_value
			FROM {$wpdb->prefix}woocommerce_prl_deploymentmeta
			WHERE prl_deployment_id = %d ORDER BY meta_id
		", $this->get_id() ) );

		foreach ( $raw_meta_data as $meta ) {
			$this->meta_data[ $meta->meta_key ] = $this->sanitize_meta_value( $meta->meta_value, $meta->meta_key );
		}
	}

	/**
	 * Update Meta Data in the database.
	 */
	protected function save_meta_data() {

		global $wpdb;

		$raw_meta_data = $wpdb->get_results( $wpdb->prepare( "
			SELECT meta_id, meta_key, meta_value
			FROM {$wpdb->prefix}woocommerce_prl_deploymentmeta
			WHERE prl_deployment_id = %d ORDER BY meta_id
		", $this->get_id() ) );

		$updated_meta_keys = array();

		// Update or delete meta from the db.
		if ( ! empty( $raw_meta_data ) ) {

			// Update or delete meta from the db depending on their presence.
			foreach ( $raw_meta_data as $meta ) {
				if ( isset( $this->meta_data[ $meta->meta_key ] ) && null !== $this->meta_data[ $meta->meta_key ] && ! in_array( $meta->meta_key, $updated_meta_keys ) ) {
					update_metadata_by_mid( 'prl_deployment', $meta->meta_id, $this->meta_data[ $meta->meta_key ], $meta->meta_key );
					$updated_meta_keys[] = $meta->meta_key;
				} else {
					delete_metadata_by_mid( 'prl_deployment', $meta->meta_id );
				}
			}
		}

		// Add any meta that weren't updated.
		$add_meta_keys = array_diff( array_keys( $this->meta_data ), $updated_meta_keys );

		foreach ( $add_meta_keys as $meta_key ) {
			if ( null !== $this->meta_data[ $meta_key ] ) {
				add_metadata( 'prl_deployment', $this->get_id(), $meta_key, $this->meta_data[ $meta_key ], true );
			}
		}

		$this->read_meta_data();
	}

	/**
	 * Get Meta by Key.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function get_meta( $key ) {

		$value = null;

		if ( isset( $this->meta_data[ $key ] ) ) {
			$value = $this->meta_data[ $key ];
		}

		return $value;
	}

	/**
	 * Add meta data.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 */
	public function add_meta( $key, $value ) {
		$this->update_meta( $key, $value );
	}

	/**
	 * Add meta data.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 */
	public function update_meta( $key, $value ) {
		if ( is_null( $value ) ) {
			$this->delete_meta( $key );
		} else {
			$this->meta_data[ $key ] = $this->sanitize_meta_value( $value, $key );
		}
	}

	/**
	 * Delete meta data.
	 *
	 * @param  array  $key
	 */
	public function delete_meta( $key ) {
		$this->meta_data[ $key ] = null;
	}

	/*---------------------------------------------------*/
	/*  Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Is deployment active.
	 *
	 * @return int
	 */
	public function is_active() {
		return 'on' === $this->data[ 'active' ];
	}

	/**
	 * Cleans null value meta when getting.
	 *
	 * @param  mixed  $value
	 * @return boolean
	 */
	private function has_meta_value( $value ) {
		return ! is_null( $value );
	}

	/**
	 * Meta value type sanitization on the way in.
	 *
	 * @param  mixed   $meta_value
	 * @param  string  $meta_key
	 */
	private function sanitize_meta_value( $meta_value, $meta_key ) {

		// Always attempt to unserialize on the way in.
		$meta_value = maybe_unserialize( $meta_value );

		return $meta_value;
	}
}
