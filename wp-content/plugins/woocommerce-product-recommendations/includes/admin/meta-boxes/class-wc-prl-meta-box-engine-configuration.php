<?php
/**
 * WC_PRL_Meta_Box_Engine_Configuration class
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Product Recommendations
 * @since    1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Engine details meta-box.
 *
 * @class    WC_PRL_Meta_Box_Engine_Configuration
 * @version  1.3.2
 */
class WC_PRL_Meta_Box_Engine_Configuration extends WC_PRL_Meta_Box {

	 /**
	  * Constructor.
	  */
	public function __construct() {

		$this->id              = 'wc-prl-engine-configuration';
		$this->context         = 'normal';
		$this->priority        = 'high';
		$this->screens         = array( 'prl_engine' ); // Only in `prl_engine` post_type.
		$this->postbox_classes = array( 'wc-prl', 'wc-prl-plain-metabox', 'woocommerce' );

		parent::__construct();

		// Add a notice if engine not deployed.
		add_action( 'admin_notices', array( $this, 'maybe_add_non_deployed_notice' ), 0 );
	}

	/**
	 * Adds a notice to deploy an engine, if not deployed already.
	 *
	 * @return void
	 */
	public function maybe_add_non_deployed_notice() {

		// Get admin screen ID.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ! in_array( $screen_id, $this->screens, true ) ) {
			return;
		}

		if ( 'add' === $screen->action ) {
			return;
		}

		global $post;

		$engine = new WC_PRL_Engine( $post->ID );

		if ( ! $engine->is_active() ) {
			return;
		}

		$current_deployments = WC_PRL()->db->deployment->query( array( 'return' => 'ids', 'engine_id' => $engine->get_id() ) );

		if ( is_array( $current_deployments ) && count( $current_deployments ) == 0 ) {

			ob_start();

			?><p class="sw-notice-box">
				<span class="sw-notice-block left"><?php esc_html_e( 'This engine has not been deployed to any locations yet. Deploy this engine now?', 'woocommerce-product-recommendations' ); ?></span>
				<span class="sw-notice-block sw-notice-block--actioned right"><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=prl_locations&section=deploy&engine=' . $engine->get_id() ) ?>"><?php esc_html_e( 'Deploy', 'woocommerce-product-recommendations' ); ?></a></span>
			</p><?php

			$notice = ob_get_clean();
			WC_PRL_Admin_Notices::add_notice( $notice, 'info' );
		}
	}

	/**
	 * Returns the meta box title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Engine Configuration', 'woocommerce-product-recommendations' );
	}

	/**
	 * Displays the engine details meta box.
	 *
	 * @param WP_Post $post
	 */
	public function output( WP_Post $post ) {

		// Prepare.
		$this->post = $post;

		if ( ! $post ) {
			return;
		}

		$types  = wc_prl_get_engine_types();
		$engine = new WC_PRL_Engine( $this->get_post()->ID );

		// If engine type is empty, set the type to be the first one in the list.
		if ( ! $engine->get_type() ) {
			$type_keys           = array_flip( $types );
			$default_engine_type = reset( $type_keys );
			$engine->set_type( $default_engine_type );
		}

		// Disable the engine type if it's not new engine.
		$disabled_type = '';
		$screen        = get_current_screen();
		$is_new_engine = 'add' === $screen->action;

		if ( ! $is_new_engine ) {
			$disabled_type = ' disabled="disabled"';
		}
		?>
		<div id="wc-prl-engine-data">
			<div class="wc-prl-field wc-prl-field--half">

				<h3 class="wc-prl-field_heading"><?php esc_html_e( 'Engine Type', 'woocommerce-product-recommendations' ) ?></h3>
				<div class="wc-prl-field_subheading"><?php esc_html_e( 'Each engine type is designed to generate recommendations at specific locations of your store.', 'woocommerce-product-recommendations' ) ?></div>

				<div class="wc-prl-field_content">
					<div class="sw-enhanced-select">
						<select name="prl_engine[type]" id="prl_engine_type"<?php echo $disabled_type; ?>>
							<?php
							foreach ( $types as $slug => $type ) {
								$selected = $slug === $engine->get_type() ? ' selected="selected"' : '';
								echo '<option value="' . wc_clean( $slug ) . '"' . $selected . '>' . esc_html( $type ) . '</option>';
							}
							?>
						</select>
					</div>
					<?php echo wc_help_tip( __( 'Choose an engine type to see a list of supported locations. When you deploy this engine, you will be able to choose its precise location.', 'woocommerce-product-recommendations' ) ); ?>
				</div>
			</div>

			<?php
			if ( $is_new_engine ) {
				$locations = WC_PRL()->locations->get_locations();

			?><div class="wc-prl-field">

				<div class="wc-prl-field_content">

					<div class="engine_type_assistant">
						<div class="engine_type_assistant__label">
							<i class="prl-icon prl-deploy"></i>
							<span><?php esc_html_e( 'Supported locations:', 'woocommerce-product-recommendations' ); ?></span>
						</div>
						<div class="engine_type_assistant__locations">
							<?php foreach ( $locations as $id => $location ) {
								$hooks          = $location->get_hooks();
								$location_types = array();
								foreach ( $hooks as $hook => $data ) {
									$location_types = array_merge( $location_types, (array) $data[ 'engine_type' ] );
								}
								$location_types = array_unique( $location_types );
								echo '<span data-engine_type="' . implode( ',', $location_types ) . '">' . $location->get_title() . '</span>';
							} ?>
						</div>
					</div>
				</div>

			</div><?php
			}

			?><div class="wc-prl-field">
				<h3 class="wc-prl-field_heading"><?php esc_html_e( 'Engine Configuration', 'woocommerce-product-recommendations' ) ?></h3>
				<div class="wc-prl-field_subheading"><?php esc_html_e( 'Configure filters and amplifiers to generate product recommendations. Add filters to narrow down results. Use amplifiers to control their order.', 'woocommerce-product-recommendations' ) ?></div>

				<div class="wc-prl-field_content">
					<div class="postbox">
						<?php
						// Print filters options Stack. (OS)
						WC_PRL()->filters->get_admin_filters_html( $engine );
						WC_PRL()->amplifiers->get_admin_filters_html( $engine );
						$deploy_label = $is_new_engine ? esc_html__( 'Create and deploy', 'woocommerce-product-recommendations' ) : esc_html__( 'Update and deploy', 'woocommerce-product-recommendations' );

						if ( ! $is_new_engine && $engine->is_active() ) { ?>
							<button name="save_and_deploy" class="button wc_prl_save_and_deploy"><?php echo $deploy_label; ?></button>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handles the request data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public function update( $post_id, $post ) {

		$engine           = new WC_PRL_Engine( $post_id );
		$post_engine_data = isset( $_POST[ 'prl_engine' ] ) ? $_POST[ 'prl_engine' ] : array( 'filters' => array(), 'amplifiers' => array() );

		if ( ! $post_engine_data ) {
			return;
		}

		if ( isset( $post_engine_data[ 'type' ] ) ) {
			$engine->set_type( $post_engine_data[ 'type' ] );
		}

		if ( ! isset( $post_engine_data[ 'filters' ] ) || ! is_array( $post_engine_data[ 'filters' ] ) ) {
			$post_engine_data[ 'filters' ] = array();
		}

		$engine->set_filters_data( array_values( $post_engine_data[ 'filters' ] ) ); // Use array_values to reset to zero-indexed.

		if ( ! isset( $post_engine_data[ 'amplifiers' ] ) || ! is_array( $post_engine_data[ 'amplifiers' ] ) ) {
			$post_engine_data[ 'amplifiers' ] = array();
		}

		$engine->set_amplifiers_data( array_values( $post_engine_data[ 'amplifiers' ] ) ); // Use array_values to reset to zero-indexed.

		$engine->save();

		if ( isset( $_POST[ 'save_and_deploy' ] ) ){
			wp_safe_redirect( admin_url( 'admin.php?page=prl_locations&section=deploy&engine=' . $engine->get_id() ) );
			exit;
		}
	}
}
