<?php

namespace OM4\WooCommerceZapier\WooCommerceResource;

use OM4\WooCommerceZapier\Trigger\TriggerDefinition;

defined( 'ABSPATH' ) || exit;


/**
 * Interface for Resource Definitions
 *
 * @since 2.0.0
 */
interface Definition {
	/**
	 * Whether or not this Resource Type is enabled/available.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Get the edit URL to an individual resource object/record.
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string
	 */
	public function get_url( $resource_id );

	/**
	 * Get the description of an individual resource object/record.
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string|null Description or null if resource not found.
	 */
	public function get_description( $resource_id );

	/**
	 * Get the trigger definitions that this resource supports.
	 *
	 * @return TriggerDefinition[]
	 */
	public function get_triggers();

	/**
	 * Get the fully qualified class name of the REST API Controller for this resource.
	 *
	 * This class name must be extend a WP_REST_Controller
	 *
	 * @return string
	 */
	public function get_controller_name();

	/**
	 * Whether or not this resource supports WordPress wp-admin metaboxes.
	 *
	 * @return bool
	 */
	public function supports_metaboxes();

	/**
	 * Get the name of this Resource's Custom Post Type.
	 *
	 * @return string
	 */
	public function get_post_type_name();

}
