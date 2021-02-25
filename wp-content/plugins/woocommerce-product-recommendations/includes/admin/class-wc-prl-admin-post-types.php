<?php
/**
 * WC_PRL_Admin_Post_Types class
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
 * Product Recommendations Admin Post Types Class.
 *
 * Registers custom post types admin settings.
 *
 * @class    WC_PRL_Admin_Post_Types
 * @version  1.0.0
 */
class WC_PRL_Admin_Post_Types {

	/**
	 * Hacky prop to track 'Publish' post box title changes.
	 *
	 * @var boolean
	 */
	private static $post_box_title_changed = false;

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'current_screen', array( __CLASS__, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( __CLASS__, 'setup_screen' ) );
		add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'engine_extra_publish_options' ) );

		add_filter( 'post_updated_messages', array( __CLASS__, 'post_updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'bulk_post_updated_messages' ), 10, 2 );

		add_filter( 'enter_title_here', array( __CLASS__, 'enter_title_here' ), 1, 2 );
		add_filter( 'screen_options_show_screen', array( __CLASS__, 'hide_screen_options' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'engine_body_classes' ) );
	}

	/**
	 * Setup post type screen.
	 */
	public static function setup_screen( $current_screen ) {

		if ( $current_screen instanceof WP_Screen && 'prl_engine' === $current_screen->post_type ) {

			if ( 'post' === $current_screen->base ) {
				add_filter( 'gettext', array( __CLASS__, 'translate_publish_engine_status' ), 10, 2 );
			}
		}

		remove_action( 'current_screen', array( __CLASS__, 'setup_screen' ) );
	}

	/**
	 * Print extra in the Publish metabox.
	 */
	public static function engine_extra_publish_options() {
		global $post;

		if ( 'prl_engine' !== $post->post_type ) {
			return;
		}
	}

	/**
	 * Change action button 'Publish' to 'Create' and rename post box title to 'Actions'.
	 *
	 * @param  string $translation
	 * @param  string $text
	 * @return string
	 */
	public static function translate_publish_engine_status( $translation, $text ) {

		global $post;

		if ( 'Publish' === $text ) {
			if ( false === self::$post_box_title_changed ) {
				self::$post_box_title_changed = true;
				return __( 'Actions', 'woocommerce-product-recommendations' );
			} elseif ( 'auto-draft' === $post->post_status ) {
				return __( 'Create', 'woocommerce-product-recommendations' );
			}
		}

		return $translation;
	}

	/**
	 * Change title boxes in admin.
	 *
	 * @param  string $text Text to shown.
	 * @param  WP_Post $post Current post object.
	 * @return string
	 */
	public static function enter_title_here( $text, $post ) {

		switch ( $post->post_type ) {
			case 'prl_engine':
				$text = esc_html__( 'Engine title', 'woocommerce-product-recommendations' );
				break;
		}

		return $text;
	}

	/**
	 * Specify custom action messages.
	 *
	 * @param  array $messages Existing post update messages.
	 * @return array
	 */
	public static function post_updated_messages( $messages ) {

		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[ 'prl_engine' ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Engine updated.', 'woocommerce-product-recommendations' ),
			4  => __( 'Engine updated.', 'woocommerce-product-recommendations' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET[ 'revision' ] ) ? sprintf( __( 'Engine restored to revision from %s', 'woocommerce-product-recommendations' ), wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
			6  => __( 'Engine created.', 'woocommerce-product-recommendations' ),
			7  => __( 'Engine saved.', 'woocommerce-product-recommendations' ),
			8  => __( 'Engine submitted.', 'woocommerce-product-recommendations' ),
			10 => __( 'Engine draft updated.', 'woocommerce-product-recommendations' )
		);

		return $messages;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 *
	 * @param  array $bulk_messages Array of messages.
	 * @param  array $bulk_counts Array of how many objects were updated.
	 * @return array
	 */
	public static function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {

		$bulk_messages[ 'prl_engine' ] = array(
			/* translators: %s: engine count */
			'deleted'   => _n( '%s engine permanently deleted.', '%s engines permanently deleted.', $bulk_counts[ 'deleted' ], 'woocommerce-product-recommendations' ),
			/* translators: %s: engine count */
			'trashed'   => _n( '%s engine moved to Trash.', '%s engines moved to Trash.', $bulk_counts[ 'trashed' ], 'woocommerce-product-recommendations' ),
			/* translators: %s: engine count */
			'untrashed' => _n( '%s engine restored from Trash.', '%s engines restored from Trash.', $bulk_counts[ 'untrashed' ], 'woocommerce-product-recommendations' ),
		);

		return $bulk_messages;
	}

	/**
	 * Hide Engines cpt screen options.
	 *
	 * @return bool
	 */
	public static function hide_screen_options() {

		$screen          = get_current_screen();
		$hide_in_screens = array( 'prl_engine', 'woocommerce_page_prl_locations', 'woocommerce_page_prl_performance' );

		if ( $screen && in_array( $screen->id, $hide_in_screens ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Engine body classes.
	 *
	 * @return string
	 */
	public static function engine_body_classes( $classes ) {

		$screen          = get_current_screen();
		$hide_in_screens = array( 'prl_engine' );

		if ( $screen && in_array( $screen->id, $hide_in_screens ) ) {
			global $post;
			if ( 'publish' === $post->post_status ) {
				return "{$classes} prl-engine-active";
			}
		}

		return $classes;
	}
}

WC_PRL_Admin_Post_Types::init();
