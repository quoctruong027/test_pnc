<?php

namespace OM4\WooCommerceZapier\WooCommerceResource;

defined( 'ABSPATH' ) || exit;


/**
 * Represents a resource type that is based on a WordPress Custom Post Type.
 *
 * @since 2.0.0
 */
abstract class CustomPostTypeResource extends Base {

	/**
	 * Whether or not this resource type supports WordPress metaboxes.
	 *
	 * @var bool
	 */
	protected $supports_metaboxes = true;

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $resource_id Resource ID.
	 *
	 * @return string
	 */
	public function get_url( $resource_id ) {
		return admin_url( "post.php?post={$resource_id}&action=edit" );
	}

}
