<?php
/**
 * WC_PRL_Filters class
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
 * Filters Collection class.
 *
 * @class    WC_PRL_Filters
 * @version  1.3.0
 */
class WC_PRL_Filters {

	/**
	 * Fitlers.
	 *
	 * @var array
	 */
	protected $filters;

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-recommendations' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'instantiate_filters' ), 9 );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'include_prl_meta_query' ), 10, 2 );

		/*---------------------------------------------------*/
		/*  Print filters JS templates in footer.            */
		/*---------------------------------------------------*/

		add_action( 'admin_footer', array( $this, 'print_filters_field_scripts' ) );
	}

	public function instantiate_filters() {

		$load_filters = apply_filters( 'woocommerce_prl_filters', array(
			'WC_PRL_Filter_Attribute',
			'WC_PRL_Filter_Category',
			'WC_PRL_Filter_Freshness',
			'WC_PRL_Filter_Attribute_Context',
			'WC_PRL_Filter_Category_Context',
			'WC_PRL_Filter_Tag_Context',
			'WC_PRL_Filter_Featured',
			'WC_PRL_Filter_Sales',
			'WC_PRL_Filter_Price',
			'WC_PRL_Filter_Product',
			'WC_PRL_Filter_Recently_Viewed',
			'WC_PRL_Filter_Price_Context',
			'WC_PRL_Filter_Stock_Status',
			'WC_PRL_Filter_Tag',
		) );

		foreach ( $load_filters as $filter ) {
			$filter = new $filter();
			$this->filters[ $filter->get_id() ] = $filter;
		}
	}

	/**
	 * Get filter class by id.
	 *
	 * @param  string  $filter_id
	 * @return WC_PRL_Filter|false
	 */
	public function get_filter( $filter_id ) {

		if ( ! empty( $this->filters[ $filter_id ] ) ) {
			return $this->filters[ $filter_id ];
		}

		return false;
	}

	/**
	 * Get filters by supported engine type.
	 *
	 * @param  string $engine_type
	 * @return array
	 */
	public function get_supported_filters( $engine_type = '' ) {

		$filters = array();

		foreach ( $this->filters as $id => $filter ) {
			if ( $engine_type === '' || $filter->has_engine_type( $engine_type ) ) {
				$filters[ $id ] = $filter;
			}
		}

		return apply_filters( 'woocommerce_prl_get_supported_filters', $filters, $engine_type );
	}


	/**
	 * Get filters fields for admin engine metabox.
	 *
	 * @param  WC_PRL_Engine $engine
	 * @param  int    $index
	 * @param  array  $options
	 * @return str
	 */
	public function get_admin_filters_html( $engine, $options = array() ) {

		$filters = $this->get_supported_filters( $engine->get_type() );

		if ( empty( $filters ) ) {
			return false;
		}

		?>
		<div class="sw-hr-section">
			<?php
			echo __( 'Filters', 'woocommerce-product-recommendations' );
			echo wc_help_tip( __( 'Add filters to narrow down recommendation results.', 'woocommerce-product-recommendations' ) );
			?>
		</div>
		<?php
		$filters_data  = empty( $options[ 'filters' ] ) ? $engine->get_filters_data() : $options[ 'filters' ];
		$filters_count = count( $filters_data );
		?><div id="os-container" class="widefat wc-prl-filters-container" data-os_count="<?php echo $filters_count; ?>">
			<div class="os_boarding<?php echo $filters_count ? '' : ' active'; ?>">
				<div class="icon">
					<i class="prl-icon prl-filter"></i>
				</div>
				<div class="text"><?php _e( 'No filters found. Add one now?', 'woocommerce-product-recommendations' ); ?></div>
			</div>
			<div id="os-list"<?php echo $filters_count ? '' : ' class="hidden"'; ?>><?php

				if ( $filters_count ) {
					foreach ( $filters_data as $filter_index => $filter_data ) {

						if ( isset( $filter_data[ 'id' ] ) ) {

							$filter_id = $filter_data[ 'id' ];

							if ( array_key_exists( $filter_id, $filters ) ) {

								?><div class="os_row" data-os_index="<?php echo $filter_index; ?>">

									<div class="os_select">
										<div class="sw-enhanced-select"><?php
											$this->get_filters_dropdown( $engine->get_type(), $filters, $filter_id );
										?></div>
									</div>
									<div class="os_content"><?php
										$filters[ $filter_id ]->get_admin_fields_html( null, $filter_index, $filter_data );
									?></div>
									<div class="os_remove column-wc_actions">
										<a href="#" data-tip="<?php echo __( 'Remove', 'woocommerce-product-recommendations' ) ?>" class="button wc-action-button trash help_tip"></a>
									</div>
								</div><?php
							}
						}
					}
				}
			?>
			</div>
			<div class="os_add os_row<?php echo $filters_count ? '' : ' os_add--boarding'; ?>">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_filters_dropdown( $engine->get_type(), $filters, null, [ 'add' => __( 'Add filter', 'woocommerce-product-recommendations' ) ] ); ?>
					</div>
				</div>
				<div class="os_content">
					<div class="os--placeholder os--disabled"></div>
				</div>
				<div class="os_remove">
				</div>
			</div>
		</div><?php
	}

	/**
	 * Admin filters select dropdown.
	 *
	 * @param  string $engine_type
	 * @param  array  $filters
	 * @param  string $selected_id
	 * @param  array  $additional_options
	 * @return void
	 */
	private function get_filters_dropdown( $engine_type, $filters, $selected_id, $additional_options = array() ) {

		?><select class="os_type"><?php

			if ( ! empty( $additional_options ) ) {
				$selected_id = null;
				foreach ( $additional_options as $key => $value ) {
					?><option value="<?php echo $key ?>" selected="selected"><?php echo $value ?></option><?php
				}
			}

			// Hint: Contextual filters need to support in general mode (non-context) the current engine type in order to show up properly.
			foreach ( $filters as $filter_id => $filter ) {
				?><option value="<?php echo $filter_id ?>" <?php echo $filter_id === $selected_id ? 'selected="selected"' : ''; ?>><?php
					echo $filter->get_title();
				?></option><?php
			}
		?></select><?php
	}

	/**
	 * Print filter JS templates in footer.
	 */
	public function print_filters_field_scripts() {

		// Get admin screen ID.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( in_array( $screen_id, array( 'edit-prl_engine', 'prl_engine' ) ) ) {
			$this->print_js_templates();
		}
	}

	/**
	 * Prints JS condition templates in footer.
	 *
	 * @param  string $scope
	 * @return void
	 */
	private function print_js_templates() {

		$types = wc_prl_get_engine_types();

		foreach ( $types as $type => $label ) {

			$filters = $this->get_supported_filters( $type );
			?>

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_filter_row">
				<div class="os_row" data-os_index="{{{ data.os_index }}}">
					<div class="os_select">
						<div class="sw-enhanced-select"><?php
							$this->get_filters_dropdown( $type, $filters, '' );
						?></div>
					</div>
					<div class="os_content">
						{{{ data.os_content }}}
					</div>
					<div class="os_remove column-wc_actions">
						<a href="#" class="button wc-action-button trash help_tip" data-tip="<?php echo __( 'Remove', 'woocommerce-product-recommendations' ) ?>"></a>
					</div>
				</div>
			</script>

			<script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_filter_add_content">
				<div class="os_select">
					<div class="sw-enhanced-select">
						<?php $this->get_filters_dropdown( $type, $filters, null, [ 'add' => __( 'Add filter', 'woocommerce-product-recommendations' ) ] ); ?>
					</div>
				</div>
				<div class="os_content">
					<div class="os--placeholder os--disabled"></div>
				</div>
				<div class="os_remove">
				</div>
			</script>

			<?php
			foreach ( $filters as $filter_id => $filter ) {
				// Generating for {{{ data.os_content }}}.
				?><script type="text/template" id="tmpl-wc_prl_engine_<?php echo $type ?>_filter_<?php echo esc_attr( $filter_id ); ?>_content"><?php

				$filter->get_admin_fields_html( null, '{{{ data.os_index }}}', array() );

				?></script><?php
			}
		}
	}

	/**
	 * Pass keys from the Woo query builder to the WP_Query object
	 *
	 * @param  array $query The WP_Query args
	 * @param  array $query_args
	 * @return array
	 */
	public function include_prl_meta_query( $query, $query_args ) {

		// Add custom query.
		if ( isset( $query_args[ 'prl_meta_query' ] ) && is_array( $query_args[ 'prl_meta_query' ] ) ) {
			$query[ 'meta_query' ] = $query_args[ 'prl_meta_query' ];
			// Fix the relation.
			$query[ 'meta_query' ][ 'relation' ] = 'AND';
		}

		if ( isset( $query_args[ 'prl_tax_query' ] ) && is_array( $query_args[ 'prl_tax_query' ] ) ) {
			$query[ 'tax_query' ] = $query_args[ 'prl_tax_query' ];
			// Fix the relation.
			$query[ 'tax_query' ][ 'relation' ] = 'AND';
		}

		return $query;
	}
}
