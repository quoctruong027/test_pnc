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
	 * @var $changes
	 */
	public $changes;

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

		if ( ! isset( $this->changes ) ) {
			$this->changes = new stdClass();
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
			$this->email = $this->db_contact->email;
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

		$changes         = empty( $this->meta->changes ) ? array() : json_decode( $this->meta->changes, true );
		$contact_changes = isset( $changes['contact'] ) ? $changes['contact'] : array();

		if ( ! isset( $this->changes ) ) {
			$this->changes = new stdClass();
		}
		foreach ( $contact_changes as $meta_key => $change ) {
			$this->changes->{$meta_key} = $change;
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
		$db_uid = ( isset( $this->db_contact->uid ) && ! empty( $this->db_contact->uid ) ) ? $this->db_contact->uid : '';

		return ( isset( $this->changes->uid ) && ! empty( $this->changes->uid ) ) ? $this->changes->uid : $db_uid;
	}

	/**
	 * Set contact uid
	 *
	 * @param $uid
	 */
	public function set_uid( $uid ) {
		$this->uid          = empty( $uid ) ? $this->get_uid() : $uid;
		$this->changes->uid = $this->uid;
	}

	/**
	 * Implementing magic function for calling other contact's actor(like customer) functions
	 *
	 * @param $name
	 * @param $args
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Magic Function $name is not defined for child function: $function", 'woofunnels_indexing' );
		}
	}

	/**
	 * Get marketing status
	 */
	public function get_marketing_status() {
		$db_marketing_status = ( isset( $this->meta->marketing_status ) && ! empty( $this->meta->marketing_status ) ) ? $this->meta->marketing_status : 0;

		return ( isset( $this->changes->marketing_status ) && ! empty( $this->changes->marketing_status ) ) ? $this->changes->marketing_status : $db_marketing_status;
	}

	/**
	 * Get contact country
	 */
	public function get_country() {
		$db_country = ( isset( $this->meta->country ) && ! empty( $this->meta->country ) ) ? $this->meta->country : '';

		return ( isset( $this->changes->country ) && ! empty( $this->changes->country ) ) ? $this->changes->country : $db_country;
	}

	/**
	 * Get contact city
	 */
	public function get_city() {
		$db_city = ( isset( $this->meta->city ) && ! empty( $this->meta->city ) ) ? $this->meta->city : '';

		return ( isset( $this->changes->city ) && ! empty( $this->changes->city ) ) ? $this->changes->city : $db_city;
	}

	/**
	 * Get contact state
	 */
	public function get_state() {
		$db_state = ( isset( $this->meta->state ) && ! empty( $this->meta->state ) ) ? $this->meta->state : '';

		return ( isset( $this->changes->state ) && ! empty( $this->changes->state ) ) ? $this->changes->state : $db_state;
	}

	/**
	 * Get first order dates
	 */
	public function get_first_order_date() {
		$db_first_order_date = ( isset( $this->meta->first_order_date ) && ! empty( $this->meta->first_order_date ) ) ? $this->meta->first_order_date : '';

		return ( isset( $this->changes->first_order_date ) && ! empty( $this->changes->first_order_date ) ) ? $this->changes->first_order_date : $db_first_order_date;
	}

	/**
	 * Get meta value for a given meta key from current contact object
	 */
	public function get_meta( $meta_key ) {
		if ( isset( $this->meta->{$meta_key} ) ) {
			return maybe_unserialize( $this->meta->{$meta_key} );
		}

		return '';
	}

	/**
	 * Set meta value for a given meta key in current contact object
	 */
	public function set_meta( $meta_key, $meta_value ) {
		$this->meta->{$meta_key} = empty( $meta_value ) ? $this->meta->{$meta_key} : $meta_value;
	}

	/**
	 * Set contact fname
	 *
	 * @param $email
	 */
	public function set_f_name( $fname ) {
		$this->f_name = empty( $fname ) ? $this->get_f_name() : $fname;

		$this->changes->f_name = $this->f_name;
	}

	/**
	 * Get contact fname
	 */
	public function get_f_name() {
		$db_f_name = ( isset( $this->db_contact->f_name ) && ! empty( $this->db_contact->f_name ) ) ? $this->db_contact->f_name : '';

		return ( isset( $this->changes->f_name ) && ! empty( $this->changes->f_name ) ) ? $this->changes->f_name : $db_f_name;
	}

	/**
	 * Set contact lname
	 *
	 * @param $email
	 */
	public function set_l_name( $lname ) {
		$this->l_name = empty( $lname ) ? $this->get_l_name() : $lname;

		$this->changes->l_name = $this->l_name;
	}

	/**
	 * Set contact created date
	 *
	 * @param $date
	 */
	public function set_creation_date( $date ) {
		$this->creation_date          = empty( $date ) ? $this->get_creation_date() : $date;
		$this->changes->creation_date = $this->creation_date;
	}

	/**
	 * Get contact created date
	 */
	public function get_creation_date() {
		$db_creation_date = ( isset( $this->db_contact->creation_date ) && ! empty( $this->db_contact->creation_date ) ) ? $this->db_contact->creation_date : gmdate( 'Y-m-d H:i:s' );

		return ( isset( $this->changes->creation_date ) && ! empty( $this->changes->creation_date ) ) ? $this->changes->creation_date : $db_creation_date;
	}

	/**
	 * Updating contact table with set data
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
	 *
	 */
	public function save( $force ) {
		if ( false !== $force ) {
			$force = true;
		}
		$contact = array();

		if ( $this->get_id() > 0 && $force ) {
			if ( isset( $this->changes->wpid ) ) {
				$contact['wpid'] = $this->changes->wpid;
			}

			if ( isset( $this->changes->uid ) ) {
				$contact['uid'] = $this->changes->uid;
			}

			if ( isset( $this->changes->email ) ) {
				$contact['email'] = $this->changes->email;
			}

			if ( isset( $this->changes->f_name ) ) {
				$contact['f_name'] = $this->changes->f_name;
			}

			if ( isset( $this->changes->l_name ) ) {
				$contact['l_name'] = $this->changes->l_name;
			}

			if ( isset( $this->changes->creation_date ) ) {
				$contact['creation_date'] = $this->changes->creation_date;
			}

			if ( count( $contact ) > 0 ) {
				$contact['id'] = $this->get_id();
				$this->db_operations->update_contact( $contact );
			}
		} elseif ( empty( $this->get_id() ) ) {
			$contact['wpid']          = $this->get_wpid() > 0 ? $this->get_wpid() : 0;
			$contact['email']         = $this->get_email();
			$contact['uid']           = md5( $this->email . $this->wp_id );
			$contact['f_name']        = $this->get_f_name();
			$contact['l_name']        = $this->get_l_name();
			$contact['creation_date'] = $this->get_creation_date();
			$this->set_uid($contact['uid']);
			$contact_id = $this->db_operations->insert_contact( $contact );

			$this->id = $contact_id;

			$force = true;
			/**$db_contact = $this->get_contact_by_contact_id( $contact_id );
			 *
			 * foreach ( ( is_array( $db_contact ) || is_object( $db_contact ) ) ? $db_contact : array() as $key => $contact_data ) {
			 * $this->{'set_' . $key}( $contact_data );
			 * }*/
		}
		$have_child = false;
		if ( isset( $this->children ) && ! empty( $this->children ) ) {
			$have_child = true;
			foreach ( $this->children as $child_actor ) {
				$child_actor->set_cid( $this->get_id() );
				$child_actor->save( $force );
			}
		}

		if ( ! $have_child ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "No child actor set yet in contact save, force: $force", 'woofunnels_indexing' );
		}

		$bwf_contacts = BWF_Contacts::get_instance();
		$uid          = $this->get_uid();
		if ( ! empty( $uid ) && ! isset( $bwf_contacts->contact_objs[ $uid ] ) ) {
			$bwf_contacts->contact_objs[ $uid ] = $this;
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Contact objects set for uid $uid in contact save function: " . print_r( array_keys( $bwf_contacts->contact_objs ), true ), 'woofunnels_indexing' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		$this->save_changes( $force );
	}

	/**
	 * Get contact id
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function get_id() {
		$changed_id = ( isset( $this->changes->id ) && $this->changes->id > 0 ) ? $this->changes->id : 0;
		$id         = ( isset( $this->id ) && $this->id > 0 ) ? $this->id : 0;
		$db_id      = ( isset( $this->db_contact->id ) && ( $this->db_contact->id > 0 ) ) ? $this->db_contact->id : 0;

		$result = $changed_id > 0 ? $changed_id : $id;

		return ( $result > 0 ) ? $result : $db_id;
	}

	/**
	 * Set contact id
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->id          = empty( $id ) ? $this->get_id() : $id;
		$this->changes->id = $this->id;
	}

	/**
	 * Get contact wp_id
	 */
	public function get_wpid() {

		$changed_wpid = ( isset( $this->changes->wpid ) && $this->changes->wpid > 0 ) ? $this->changes->wpid : 0;
		$wp_id        = ( isset( $this->wp_id ) && $this->wp_id > 0 ) ? $this->wp_id : 0;
		$db_wpid      = ( isset( $this->db_contact->wpid ) && $this->db_contact->wpid > 0 ) ? $this->db_contact->wpid : 0;

		$result = $changed_wpid > 0 ? $changed_wpid : $wp_id;

		return $result > 0 ? $result : $db_wpid;
	}

	/**
	 * Set contact wpid
	 *
	 * @param $wp_id
	 */
	public function set_wpid( $wp_id ) {
		$this->wp_id         = empty( $wp_id ) ? $this->get_wpid() : $wp_id;
		$this->changes->wpid = $this->wp_id;
	}

	/**
	 * Get contact email
	 */
	public function get_email() {

		$changed_email = ( isset( $this->changes->email ) && ! empty( $this->changes->email ) ) ? $this->changes->email : '';
		$email         = ( isset( $this->email ) && ! empty( $this->email ) ) ? $this->email : '';
		$db_email      = ( isset( $this->db_contact->email ) && ! empty( $this->db_contact->email ) ) ? $this->db_contact->email : '';

		$result = ! empty( $changed_email ) ? $changed_email : $email;

		return empty( $result ) ? $db_email : $result;
	}

	/**
	 * Set contact email
	 *
	 * @param $email
	 */
	public function set_email( $email ) {
		$this->email          = empty( $email ) ? $this->get_email() : $email;
		$this->changes->email = $this->email;
	}

	/**
	 * Get contact lname
	 */
	public function get_l_name() {
		$db_l_name = ( isset( $this->db_contact->l_name ) && ! empty( $this->db_contact->l_name ) ) ? $this->db_contact->l_name : '';

		return ( isset( $this->changes->l_name ) && ! empty( $this->changes->l_name ) ) ? $this->changes->l_name : $db_l_name;
	}

	/**
	 * @param $force
	 */
	public function save_changes( $force ) {

		$db_changes = $this->get_contact_meta( 'changes' );
		$changes    = ! empty( $db_changes ) ? json_decode( $db_changes, true ) : array();

		$is_changed = false;
		if ( empty( $force ) ) {
			$is_changed         = true;
			$changes['contact'] = $this->changes;
			foreach ( $this->children as $child_name => $child_object ) {
				$changes[ $child_name ] = $child_object->changes;
				unset( $child_object->changes );
			}
		}
		if ( empty( $is_changed ) ) {
			$changes = array();
		}
		$this->update_meta( 'changes', $changes );
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
	}

	/**
	 * Set marketing status
	 *
	 * @param $status
	 */
	public function set_marketing_status( $status ) {
		$this->meta->marketing_status    = empty( $status ) ? $this->meta->marketing_status : $status;
		$this->changes->marketing_status = $this->meta->marketing_status;
	}

	/**
	 * Set contact country
	 *
	 * @param $country
	 */
	public function set_country( $country ) {
		$this->meta->country    = empty( $country ) ? $this->meta->country : $country;
		$this->changes->country = $this->meta->country;
	}

	/**
	 * Set contact city
	 *
	 * @param $city
	 */
	public function set_city( $city ) {
		$this->meta->city    = empty( $city ) ? $this->meta->city : $city;
		$this->changes->city = $this->meta->city;
	}

	/**
	 * Set contact state
	 *
	 * @param $state
	 */
	public function set_state( $state ) {
		$this->meta->state    = empty( $state ) ? $this->meta->state : $state;
		$this->changes->state = $this->meta->state;
	}

	/**
	 * Set first order date
	 *
	 * @param $date
	 */
	public function set_first_order_date( $date ) {
		$this->meta->first_order_date    = empty( $date ) ? $this->meta->first_order_date : $date;
		$this->changes->first_order_date = $this->meta->first_order_date;
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
