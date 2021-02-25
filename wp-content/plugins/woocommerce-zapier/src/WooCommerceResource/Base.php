<?php

namespace OM4\WooCommerceZapier\WooCommerceResource;

use OM4\WooCommerceZapier\Exception\InvalidImplementationException;
use OM4\WooCommerceZapier\Trigger\Trigger;

defined( 'ABSPATH' ) || exit;


/**
 * Base Resource Type definition.
 *
 * @since 2.0.0
 */
abstract class Base implements Definition {
	/**
	 * Resource key.
	 *
	 * Must be a-z lowercase characters only, and in singular (non plural) form.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Resource Name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Whether or not this resource type supports WordPress metaboxes.
	 *
	 * @var bool
	 */
	protected $supports_metaboxes = false;

	/**
	 * The name of this resource's Custom Post Type (if the Resource is a custom post type).
	 *
	 * @var string
	 */
	protected $custom_post_type_name = '';

	/**
	 * List of Triggers that this resource has.
	 *
	 * @var Trigger[]
	 */
	protected $triggers;

	/**
	 * Constructor.
	 *
	 * @throws InvalidImplementationException If id or name aren't set.
	 */
	public function __construct() {
		if ( empty( $this->key ) ) {
			throw new InvalidImplementationException( '`id` needs to be set', 1 );
		}

		if ( empty( $this->name ) ) {
			throw new InvalidImplementationException( '`name` needs to be set', 1 );
		}

		if ( $this->is_enabled() ) {
			$this->get_triggers();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_controller_name() {
		return substr( get_class( $this ), 0, (int) strrpos( get_class( $this ), '\\' ) ) . '\\Controller';
	}

	/**
	 * Get this Resource's key.
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get this Resource's Name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return bool
	 */
	public function supports_metaboxes() {
		return $this->supports_metaboxes;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return string
	 */
	public function get_post_type_name() {
		return $this->custom_post_type_name;
	}
}
