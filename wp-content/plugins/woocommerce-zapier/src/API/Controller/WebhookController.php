<?php

namespace OM4\WooCommerceZapier\API\Controller;

use OM4\WooCommerceZapier\API\API;
use WC_REST_Webhooks_Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Exposes WooCommerce's REST API v3 Webhooks endpoint via the WooCommerce Zapier endpoint namespace.
 *
 * @since 2.0.0
 */
class WebhookController extends WC_REST_Webhooks_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = API::REST_NAMESPACE;
}
