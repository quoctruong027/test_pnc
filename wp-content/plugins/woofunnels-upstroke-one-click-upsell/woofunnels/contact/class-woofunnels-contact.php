<?php
/**
 * Contact Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooFunnels_Contact
 *
 *
 */
class WooFunnels_Contact {
	/**
	 * public db_operations $db_operations
	 */
	public $db_operations;

	/**
	 * public id $id
	 */
	public $id;

	/**
	 * public ud $uid
	 */
	public $uid;

	/**
	 * public email $email
	 */
	public $email;

	/**
	 * public wp_id $wp_id
	 */
	public $wp_id;

	/**
	 * public meta $meta
	 */
	public $meta;

	/**
	 * public customer $customer
	 */
	public $children;


	/**
	 * @var mixed $db_contact
	 */
	public $db_contact;

	/**
	 * Get the contact details for the email passed if this email exits other create a new contact with this email
	 *
	 * @param  $wp_id
	 * @param  $email
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 */
	public function __construct( $wp_id, $email ) {
		$this->db_operations = WooFunnels_DB_Operations::get_instance();

		if ( ! isset( $this->children ) ) {
			$this->children = new stdClass();
		}


		if ( ! isset( $this->meta ) ) {
			$this->meta = new stdClass();
		}


		if ( empty( $wp_id ) && empty( $email ) ) {
			return;
		}

		$this->email      = $email;
		$this->wp_id      = $wp_id;
		$this->db_contact = new stdClass();

		if ( ! empty( $wp_id ) && $wp_id > 0 ) {
			$this->db_contact = $this->get_contact_by_wpid( $wp_id );
		}

		if ( ! isset( $this->db_contact->id ) && ! empty( $email ) && is_email( $email ) ) {
			$this->db_contact = $this->get_contact_by_email( $email );
		}

		if ( isset( $this->db_contact->id ) && $this->db_contact->id > 0 ) {
			$this->id = $this->db_contact->id;
		}


		if ( isset( $this->id ) && ! empty( $this->id ) ) {
			$this->email  = $this->db_contact->email;
			$contact_meta = $this->db_operations->get_contact_metadata( $this->id );
			foreach ( is_array( $contact_meta ) ? $contact_meta : array() as $meta ) {
				$this->meta->{$meta->meta_key} = maybe_unserialize( $meta->meta_value );
			}
		}

		$bwf_contacts = BWF_Contacts::get_instance();

		$uid = $this->get_uid();
		if ( ! empty( $uid ) && ! isset( $bwf_contacts->contact_objs[ $uid ] ) ) {
			$bwf_contacts->contact_objs[ $this->get_uid() ] = $this;
		}


	}

	/**
	 * Get contact by wp_id
	 *
	 * @param $wp_id
	 *
	 * @return mixed
	 */
	public function get_contact_by_wpid( $wp_id ) {
		return $this->db_operations->get_contact_by_wpid( $wp_id );
	}

	/**
	 * Get contact by email
	 *
	 * @param $email
	 *
	 * @return mixed
	 */
	public function get_contact_by_email( $email ) {
		return $this->db_operations->get_contact_by_email( $email );
	}

	/**
	 * Get contact uid
	 */
	public function get_uid() {
		$uid = ( isset( $this->uid ) && ! empty( $this->uid ) ) ? $this->uid : '';

		$db_uid = ( isset( $this->db_contact->uid ) && ! empty( $this->db_contact->uid ) ) ? $this->db_contact->uid : '';

		return empty( $uid ) ? $db_uid : $uid;
	}

	/**
	 * Set contact uid
	 *
	 * @param $uid
	 */
	public function set_uid( $uid ) {
		$this->uid = empty( $uid ) ? $this->get_uid() : $uid;
	}

	/**
	 * Implementing magic function for calling other contact's actor(like customer) functions
	 *
	 * @param $name
	 * @param $args
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		$keys_arr       = explode( '_', $name );
		$action         = ( is_array( $keys_arr ) && count( $keys_arr ) > 0 ) ? $keys_arr[0] : '';
		$child          = ( is_array( $keys_arr ) && count( $keys_arr ) > 1 ) ? $keys_arr[1] : '';
		$function       = str_replace( $child . '_', '', $name );
		$child_entities = BWF_Contacts::get_registerd_child_entities();

		if ( 'set_child' === $function && ! isset( $this->children->{$child} ) ) {
			if ( isset( $child_entities[ $child ] ) ) {
				$object_child             = $child_entities[ $child ];
				$this->children->{$child} = new $object_child( $this );
			}
		} elseif ( isset( $this->children ) && ! empty( $this->children ) && ! empty( $child ) && isset( $this->children->{$child} ) && 'set_child' !== $function ) {

			if ( is_array( $args ) && count( $args ) > 0 ) {
				$result = $this->children->{$child}->{$function}( $args[0] );
			}

			if ( ! is_array( $args ) || ( is_array( $args ) && 0 === count( $args ) ) ) {
				$result = $this->children->{$child}->{$function}();
			}
			if ( 'get' === $action ) {
				return $result;
			}
		} elseif ( ! isset( $this->children->{$child} ) ) {
			BWF_Logger::get_instance()->log( "Magic Function $name is not defined for child function: $function", 'woofunnels_indexing' );
		}
	}

	/**
	 * Get marketing status
	 */
	public function get_marketing_status() {
		return $this->get_status();
	}

	/**
	 * Get marketing status
	 */
	public function get_status() {
		$status = ( isset( $this->status ) && '' !== $this->status ) ? $this->status : '';

		$db_status = ( isset( $this->db_contact->status ) && '' !== $this->db_contact->status ) ? $this->db_contact->status : 1;

		return '' !== $status ? $status : $db_status;
	}

	/**
	 * Get meta value for a given meta key from current contact object
	 *
	 * @param string $meta_key meta key to get value against
	 * @param bool $is_primary_column_check whether to check primary properties or not
	 *
	 * @return mixed|string
	 */
	public function get_meta( $meta_key, $is_primary_column_check = true ) {

		if ( $is_primary_column_check ) {
			$primary_columns = $this->get_primary_properties();
			if ( in_array( $meta_key, $primary_columns, true ) ) {
				return call_user_func( array( $this, 'get_' . $meta_key ) );
			}
		}
		if ( isset( $this->meta->{$meta_key} ) ) {
			return maybe_unserialize( $this->meta->{$meta_key} );
		}

		return '';
	}

	/**
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function set_meta( $meta_key, $meta_value ) {
		$this->meta->{$meta_key} = empty( $meta_value ) ? $this->meta->{$meta_key} : $meta_value;
	}

	public function get_primary_properties() {
		return array( 'id', 'email', 'uid', 'email', 'f_name', 'l_name', 'creation_date', 'contact_no', 'country', 'state', 'timezone', 'type', 'source', 'points', 'last_modified', 'status' );
	}

	/**
	 * Set contact fname
	 *
	 * @param $email
	 */
	public function set_f_name( $fname ) {
		$this->f_name = empty( $fname ) ? $this->get_f_name() : $fname;
	}

	/**
	 * Get contact fname
	 */
	public function get_f_name() {
		$fname    = ( isset( $this->f_name ) && ! empty( $this->f_name ) ) ? $this->f_name : '';
		$db_email = ( isset( $this->db_contact->f_name ) && ! empty( $this->db_contact->f_name ) ) ? $this->db_contact->f_name : '';

		return empty( $fname ) ? $db_email : $fname;
	}

	/**
	 * Set contact lname
	 *
	 * @param $email
	 */
	public function set_l_name( $lname ) {
		$this->l_name = empty( $lname ) ? $this->get_l_name() : $lname;
	}

	/**
	 * Get contact lname
	 */
	public function get_l_name() {
		$lname     = ( isset( $this->l_name ) && ! empty( $this->l_name ) ) ? $this->l_name : '';
		$l_db_name = ( isset( $this->db_contact->l_name ) && ! empty( $this->db_contact->l_name ) ) ? $this->db_contact->l_name : '';


		return empty( $lname ) ? $l_db_name : $lname;
	}

	/**
	 * Set contact created date
	 *
	 * @param $date
	 */
	public function set_last_modified( $date ) {
		$this->last_modified = empty( $date ) ? $this->get_last_modified() : $date;
	}

	/**
	 * Get contact fname
	 */
	public function get_last_modified() {

		$last_mod         = ( isset( $this->last_modified ) && ! empty( $this->last_modified ) ) ? $this->last_modified : '';
		$db_last_modified = ( isset( $this->db_contact->last_modified ) && ! empty( $this->db_contact->last_modified ) ) ? $this->db_contact->last_modified : '';


		return empty( $last_mod ) ? $db_last_modified : $last_mod;
	}

	/**
	 * Set contact created date
	 *
	 * @param $date
	 */
	public function set_creation_date( $date ) {
		$this->creation_date = empty( $date ) ? $this->get_creation_date() : $date;
	}

	/**
	 * Get contact created date
	 */
	public function get_creation_date() {
		$creation_date = ( isset( $this->creation_date ) && ! empty( $this->creation_date ) ) ? $this->creation_date : '';

		$db_creation_date = ( isset( $this->db_contact->creation_date ) && ! empty( $this->db_contact->creation_date ) ) ? $this->db_contact->creation_date : current_time( 'mysql' );

		return empty( $creation_date ) ? $db_creation_date : $creation_date;
	}

	public function set_type( $type ) {
		$this->type = empty( $type ) ? $this->get_type() : $type;
	}

	/**
	 * Get type the contact belongs to
	 * @return string
	 */
	public function get_type() {
		$type = ( isset( $this->type ) && ! empty( $this->type ) ) ? $this->type : '';

		$db_type = ( isset( $this->db_contact->type ) && ! empty( $this->db_contact->type ) ) ? $this->db_contact->type : '';

		return empty( $type ) ? $db_type : $type;

	}

	public function set_source( $source ) {
		$this->source = empty( $source ) ? $this->get_source() : $source;
	}

	/**
	 * Get source the contact generated from
	 * @return string
	 */
	public function get_source() {

		$source    = ( isset( $this->source ) && ! empty( $this->source ) ) ? $this->source : '';
		$db_source = ( isset( $this->db_contact->source ) && ! empty( $this->db_contact->source ) ) ? $this->db_contact->source : '';


		return empty( $source ) ? $db_source : $source;

	}

	public function set_points( $points ) {
		$this->points = empty( $points ) ? $this->get_points() : $points;
	}

	/**
	 * Get points the contact have
	 * @return string
	 */
	public function get_points() {
		$points = ( isset( $this->points ) && ! empty( $this->points ) ) ? $this->points : '';

		$db_points = ( isset( $this->db_contact->points ) && ! empty( $this->db_contact->points ) ) ? $this->db_contact->points : '';

		return empty( $points ) ? $db_points : $points;

	}

	/**
	 * Saves the data in the properties.
	 * This method is responsible for any db operation inside the contact table and sibling tables
	 * Updating contact table with set data
	 *
	 * @param bool $force used to detect if properties to be saved inside changes meta of directly to respective tables.
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 *
	 */
	public function save() {

		$contact                = array();
		$get_primary_properties = $this->get_primary_properties();
		foreach ( $get_primary_properties as $property ) {
			$contact[ $property ] = call_user_func( array( $this, 'get_' . $property ) );
		}
		$contact['last_modified'] = current_time( 'mysql' );
		if ( $this->get_id() > 0 ) {
			$contact['id'] = $this->get_id();
			$this->db_operations->update_contact( $contact );

		} elseif ( empty( $this->get_id() ) ) {


			$contact['uid']  = md5( $this->email . $this->wp_id );
			$contact['wpid'] = $this->get_wpid() > 0 ? $this->get_wpid() : 0;
			$this->set_uid( $contact['uid'] );
			$contact_id = $this->db_operations->insert_contact( $contact );

			$this->id = $contact_id;
		}

		if ( isset( $this->children ) && ! empty( $this->children ) ) {

			foreach ( $this->children as $child_actor ) {
				$child_actor->set_cid( $this->get_id() );
				$child_actor->save();
			}
		}


		$bwf_contacts = BWF_Contacts::get_instance();
		$uid          = $this->get_uid();
		if ( ! empty( $uid ) && ! isset( $bwf_contacts->contact_objs[ $uid ] ) ) {
			$bwf_contacts->contact_objs[ $uid ] = $this;
			BWF_Logger::get_instance()->log( "Contact objects set for uid $uid in contact save function: " . print_r( array_keys( $bwf_contacts->contact_objs ), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Get contact id
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function get_id() {
		$id    = ( isset( $this->id ) && $this->id > 0 ) ? $this->id : 0;
		$db_id = ( isset( $this->db_contact->id ) && ( $this->db_contact->id > 0 ) ) ? $this->db_contact->id : 0;

		return ( $id > 0 ) ? $id : $db_id;
	}

	/**
	 * Set contact id
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id = empty( $id ) ? $this->get_id() : $id;
	}

	/**
	 * Get contact wp_id
	 */
	public function get_wpid() {

		$wp_id   = ( isset( $this->wp_id ) && $this->wp_id > 0 ) ? $this->wp_id : 0;
		$db_wpid = ( isset( $this->db_contact->wpid ) && $this->db_contact->wpid > 0 ) ? $this->db_contact->wpid : 0;


		return $wp_id > 0 ? $wp_id : $db_wpid;
	}

	/**
	 * Set contact wpid
	 *
	 * @param $wp_id
	 */
	public function set_wpid( $wp_id ) {
		$this->wp_id = empty( $wp_id ) ? $this->get_wpid() : $wp_id;
	}

	/**
	 * Get contact email
	 */
	public function get_email() {

		$email    = ( isset( $this->email ) && ! empty( $this->email ) ) ? $this->email : '';
		$db_email = ( isset( $this->db_contact->email ) && ! empty( $this->db_contact->email ) ) ? $this->db_contact->email : '';


		return empty( $email ) ? $db_email : $email;
	}

	/**
	 * Set contact email
	 *
	 * @param $email
	 */
	public function set_email( $email ) {
		$this->email = empty( $email ) ? $this->get_email() : $email;
	}

	/**
	 * Get meta value for a given meta key from DB
	 */
	public function get_contact_meta( $meta_key ) {
		return $this->db_operations->get_contact_meta_value( $this->get_id(), $meta_key );
	}

	/**
	 * Set meta value for a given meta key
	 *
	 * @param $meta_key
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function update_meta( $meta_key, $meta_value ) {
		return $this->db_operations->update_contact_meta( $this->get_id(), $meta_key, $meta_value );
	}

	/**
	 * Updating contact meta table with set data
	 */
	public function save_meta() {
		$this->db_operations->save_contact_meta( $this->id, $this->meta );
		$contact                  = [];
		$contact['id']            = $this->get_id();
		$contact['last_modified'] = current_time( 'mysql' );
		$this->db_operations->update_contact( $contact );
	}

	/**
	 * Set marketing status
	 *
	 * @param $status
	 */
	public function set_marketing_status( $status ) {
		$this->set_status( $status );
	}

	/**
	 * Set marketing status
	 *
	 * @param $status
	 */
	public function set_status( $status ) {
		$this->status = ( '' === $status ) ? $this->get_status() : $status;
	}

	/**
	 * Set contact country
	 *
	 * @param $country
	 */
	public function set_country( $country ) {
		$this->country = empty( $country ) ? $this->get_country() : $country;
	}

	/**
	 * Get contact country
	 */
	public function get_country() {

		$country = ( isset( $this->country ) && ! empty( $this->country ) ) ? $this->country : '';

		$db_country = ( isset( $this->db_contact->country ) && ! empty( $this->db_contact->country ) ) ? $this->db_contact->country : '';

		return empty( $country ) ? $db_country : $country;
	}

	public function set_timezone( $timezone ) {
		$this->timezone = empty( $timezone ) ? $this->get_timezone() : $timezone;
	}

	/**
	 * Get contact timezone
	 *
	 * @return string
	 */
	public function get_timezone() {
		$timezone = ( isset( $this->timezone ) && ! empty( $this->timezone ) ) ? $this->timezone : '';

		$db_timezone = ( isset( $this->db_contact->timezone ) && ! empty( $this->db_contact->timezone ) ) ? $this->db_contact->timezone : '';

		return empty( $timezone ) ? $db_timezone : $timezone;
	}

	public function set_contact_no( $contact_no ) {
		$this->contact_no = empty( $contact_no ) ? $this->get_contact_no() : $contact_no;
	}

	public function get_contact_no() {
		$no = ( isset( $this->contact_no ) && ! empty( $this->contact_no ) ) ? $this->contact_no : '';

		$db_contact_no = ( isset( $this->db_contact->contact_no ) && ! empty( $this->db_contact->contact_no ) ) ? $this->db_contact->contact_no : '';

		return empty( $no ) ? $db_contact_no : $no;

	}


	/**
	 * Set contact state
	 *
	 * @param $state
	 */
	public function set_state( $state ) {
		$this->state = empty( $state ) ? $this->get_state() : $state;
	}

	/**
	 * Get contact state
	 */
	public function get_state() {
		$state = ( isset( $this->state ) && ! empty( $this->state ) ) ? $this->state : '';

		$db_state = ( isset( $this->db_contact->state ) && ! empty( $this->db_contact->state ) ) ? $this->db_contact->state : '';

		return empty( $state ) ? $db_state : $state;

	}


	/**
	 * Get contact by id
	 *
	 * @param $contact_id
	 *
	 * @return mixed
	 */
	public function get_contact_by_contact_id( $contact_id ) {
		return $this->db_operations->get_contact_by_contact_id( $contact_id );
	}

	/**
	 * Deleting a meta key from contact meta table
	 *
	 * @param $meta_key
	 */
	public function delete_meta( $meta_key ) {
		$this->db_operations->delete_contact_meta( $this->id, $meta_key );
	}
}
