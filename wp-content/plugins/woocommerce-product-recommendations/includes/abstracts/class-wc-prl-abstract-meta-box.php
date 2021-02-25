<?php
/**
 * WC_PRL_Meta_Box class
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
 * Abstract meta-box handler.
 *
 * @class    WC_PRL_Meta_Box
 * @version  1.3.0
 */
abstract class WC_PRL_Meta_Box {


	/**
	* Meta box id.
	*
	* @var string
	*/
	protected $id;

	/**
	* Meta box context.
	*
	* @var string
	*/
	protected $context = 'normal';

	/**
	 * Meta box priority.
	 *
	 * @var string
	 */
	protected $priority = 'default';

	/**
	 * Meta box list of supported screen IDs.
	 *
	 * @var array
	 */
	protected $screens = array();

	/**
	 * Meta box list of additional postbox classes.
	 *
	 * @var array
	 */
	protected $postbox_classes = array( 'wc-prl', 'woocommerce' );

	/**
	 * Current post where the meta box appears.
	 *
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * Meta box constructor.
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		if ( method_exists( $this, 'enqueue_scripts_and_styles' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_scripts_and_styles' ) );
		}

		if ( method_exists( $this, 'update' ) ) {
			add_action( 'save_post', array( $this, 'save_post' ), 5, 2 );
		}
	}


	/**
	 * Returns the meta box title.
	 *
	 * @return string
	 */
	abstract public function get_title();


	/**
	 * Returns the meta box ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Returns the meta box ID, with underscores instead of dashes.
	 *
	 * @return string
	 */
	protected function get_id_underscored() {
		return str_replace( '-', '_', $this->id );
	}


	/**
	 * Returns the nonce name for the current meta box.
	 *
	 * @return string
	 */
	protected function get_nonce_name() {
		return '_' . $this->get_id_underscored() . '_nonce';
	}


	/**
	 * Returns the nonce action for the current meta box.
	 *
	 * @return string
	 */
	protected function get_nonce_action() {
		return 'update-' . $this->id;
	}


	/**
	 * Returns the post object.
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Enqueues scripts & styles for the meta box, if conditions are met.
	 *
	 * @return void
	 */
	public function maybe_enqueue_scripts_and_styles() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ! in_array( $screen_id, $this->screens, true ) ) {
			return;
		}

		$this->enqueue_scripts_and_styles();
	}


	/**
	 * Enqueues scripts and styles for the meta box.
	 *
	 * @return void
	 */
	public function enqueue_scripts_and_styles() {
		// no-op, implement in child classes
	}


	/**
	 * Adds the meta box to the supported screen(s).
	 *
	 * @return void
	 */
	public function add_meta_box() {
		global $post;

		// Sanity check.
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $this->screens, true ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		add_meta_box(
			$this->id,
			$this->get_title(),
			array( $this, 'do_output' ),
			$screen->id,
			$this->context,
			$this->priority
		);

		add_filter( "postbox_classes_{$screen->id}_{$this->id}", array( $this, 'postbox_classes' ) );
	}


	/**
	 * Adds a CSS class to the meta box.
	 *
	 * @param  array $classes
	 * @return array
	 */
	public function postbox_classes( $classes ) {
		return array_merge( $classes, $this->postbox_classes );
	}


	/**
	 * Outputs the basic meta box contents.
	 *
	 * @return void
	 */
	public function do_output() {
		global $post;

		// Add a nonce field
		if ( method_exists( $this, 'update' ) ) {
			wp_nonce_field( $this->get_nonce_action(), $this->get_nonce_name() );
		}

		// output implementation-specific HTML
		$this->output( $post );
	}


	/**
	 * Outputs meta box contents.
	 *
	 * @param WP_Post $post
	 */
	abstract public function output( WP_Post $post );

	/**
	 * Processes and saves meta box data
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function save_post( $post_id, WP_Post $post ) {

		// Check security.
		if ( ! isset( $_POST[ $this->get_nonce_name() ] ) || ! wp_verify_nonce( $_POST[ $this->get_nonce_name() ], $this->get_nonce_action() ) ) {
			return;
		}

		// If this is an autosave don't do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! in_array( $post->post_type, $this->screens, true ) ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST[ 'post_type' ] ) && 'page' === $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Child update data.
		if ( method_exists( $this, 'update' ) ) {
			$this->update( $post_id, $post );
		}

		/**
		 * Fires upon saving a meta box.
		 *
		 * @param array   $_POST
		 * @param string  $meta_box_id
		 * @param int     $post_id
		 * @param WP_Post $post WP_Post
		 */
		do_action( 'woocommerce_prl_save_meta_box', $_POST, $this->id, $post_id, $post );
	}
}
