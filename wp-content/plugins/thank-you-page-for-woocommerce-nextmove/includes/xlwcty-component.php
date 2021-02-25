<?php
defined( 'ABSPATH' ) || exit;

abstract class XLWCTY_Component {

	public static $order_meta_data = array();
	public static $component_css = array();
	private static $instance = null;
	public $viewpath = '';
	public $campaign_data = array();
	public $instance_campaign_data;
	public $data = '';
	public $is_multiple = false;
	public $fields = array();
	public $current_index = false;
	protected $slug = '';
	public static $multiple_components = [];
	public $component_limit = 0;

	public function __construct( $order = false ) {
		$this->data = new stdClass();
		$this->initiate_fields();
		add_action( 'xlwcty_page_meta_setup_completed', array( $this, 'prepare_out_put_data' ) );

		add_action( 'xlwcty_after_components_loaded', array( $this, 'setup_fields' ) );

		add_action( 'wp', function () {
			$query_object = get_queried_object();

			if ( ! $query_object instanceof WP_Post ) {
				return;
			}

			$page_id   = $query_object->ID;
			$post_type = $query_object->post_type;

			$thank_you_page_id = XLWCTY_Common::get_thankyou_page_id( $page_id );

			if ( XLWCTY_Common::get_thank_you_page_post_type_slug() === $post_type ) {
				$thank_you_page_id = $page_id;
			}

			$slug = $this->get_slug();
			$slug = substr( $slug, 1 );

			if ( $this->has_multiple_fields() ) {
				$component_limit = $this->component_limit;
				if ( $component_limit > 0 ) {
					for ( $i = 1; $i <= $component_limit; $i ++ ) {
						if ( ! isset( self::$multiple_components[ $slug . '_' . $i ] ) ) {
							self::$multiple_components[ $slug . '_' . $i ] = $slug . '_' . $i;
							new Xlwcty_Dynamic_Component( $slug . '_' . $i, $thank_you_page_id );
						}
					}
				}
			} else {
				add_shortcode( $slug, array( $this, 'display_component_shortcode_view' ) );
			}
		}, 12 );
	}

	public function initiate_fields() {
		$fields = $this->fields;

		foreach ( $fields as $key => $val ) {
			$this->data->{$key} = '';
		}
	}

	public function get_slug() {
		return $this->slug;
	}

	public function set_slug( $slug = '' ) {
		$this->slug = $slug;
	}

	public static function get_multiple_components( $page_id ) {
		if ( empty( $page_id ) ) {
			return '';
		}
		$thankyou_component = json_decode( get_post_meta( $page_id, '_xlwcty_builder_layout', true ), true );
		if ( empty( $thankyou_component ) ) {
			return '';
		}
		$components                  = array();
		$multiple_components         = self::$multiple_components;
		$thankyou_component_template = get_post_meta( $page_id, '_xlwcty_builder_template', true );

		/** Checking first column components */
		if ( isset( $thankyou_component[ $thankyou_component_template ]['first'] ) ) {
			foreach ( $thankyou_component[ $thankyou_component_template ]['first'] as $com => $thankc ) {
				$slug = ltrim( $thankc['slug'], '_' );
				if ( isset( $multiple_components[ $slug ] ) ) {
					$components[ $slug ] = $thankc;
				}
			}
		}

		/** Checking second column components */
		if ( isset( $thankyou_component[ $thankyou_component_template ]['second'] ) ) {
			foreach ( $thankyou_component[ $thankyou_component_template ]['second'] as $com => $thankc ) {
				$slug = ltrim( $thankc['slug'], '_' );
				if ( isset( $multiple_components[ $slug ] ) ) {
					$components[ $slug ] = $thankc;
				}
			}
		}

		/** Checking third column components */
		if ( isset( $thankyou_component[ $thankyou_component_template ]['third'] ) ) {
			foreach ( $thankyou_component[ $thankyou_component_template ]['third'] as $com => $thankc ) {
				$slug = ltrim( $thankc['slug'], '_' );
				if ( isset( $multiple_components[ $slug ] ) ) {
					$components[ $slug ] = $thankc;
				}
			}
		}


		return $components;
	}

	public static function push_css( $component, $component_css ) {
		if ( '' !== $component && '' !== $component_css ) {
			if ( ! isset( self::$component_css[ $component ] ) ) {
				self::$component_css[ $component ] = array();
			}
			self::$component_css[ $component ] = $component_css;
		}
	}

	public static function get_css( $component = '' ) {
		if ( array_key_exists( $component, self::$component_css ) ) {
			return self::$component_css[ $component ];
		}

		return self::$component_css;
	}

	public static function save_original_content( $original_value, $args, $cmb2_field ) {
		return $original_value; // Unsanitized value.
	}

	public function display_component_shortcode_view() {
		ob_start();
		echo '<div class="xlwcty_wrap xlwcty_shortcode" data-component="' . $this->get_slug() . '">';
		$this->get_view();
		echo '</div>';

		return ob_get_clean();
	}

	public function get_view() {
		$order_data = $this->get_view_data();
		if ( isset( $order_data['order_id'] ) && 0 === $order_data['order_id'] ) {
			return;
		}
		extract( $order_data );
		if ( file_exists( $this->viewpath ) ) {
			$index = 0;
			if ( false !== $this->current_index ) {
				$index = $this->current_index;
			}
			if ( $this->is_enable( $index ) ) {
				$slug = $this->get_slug();
				$slug = substr( $slug, 8 );

				do_action( 'xlwcty_woocommerce_before_' . $slug, $order_data, $this );

				include $this->viewpath;

				do_action( 'xlwcty_woocommerce_after_' . $slug, $order_data, $this );
			}
		}
	}

	public function get_view_data( $key = 'order' ) {
		$order = XLWCTY_Core()->data->get_order();
		if ( $order instanceof WC_Order ) {
			return array(
				'campaign_data' => $this->instance_campaign_data,
				'order_data'    => $order,
			);
		} else {
			return array(
				'order_id' => 0,
			);
		}
	}

	final public function wc_version() {
		return WC()->version;
	}

	public function component_script() {

	}

	public function setup_fields() {

	}

	public function is_enable( $index = 0 ) {
		if ( ! $this->has_multiple_fields() ) {
			if ( XLWCTY_Core()->data->get_meta( $this->get_slug() . '_enable', 'raw' ) == '1' ) {

				if ( wp_is_mobile() && XLWCTY_Core()->data->get_meta( $this->get_slug() . '_hide_mobile', 'raw' ) == 'on' ) {
					return false;
				}
				if ( ! wp_is_mobile() && XLWCTY_Core()->data->get_meta( $this->get_slug() . '_hide_desktop', 'raw' ) == 'on' ) {
					return false;
				}
				$is_enabled = apply_filters( 'xlwcty_is_component_enabled', true, $this->get_slug(), false );
				if ( $is_enabled === false ) {
					return false;
				}

				return true;
			}
		} else {
			if ( XLWCTY_Core()->data->get_meta( $this->get_slug() . '_enable_' . $index, 'raw' ) == '1' ) {

				if ( wp_is_mobile() && XLWCTY_Core()->data->get_meta( $this->get_slug() . '_hide_mobile_' . $index, 'raw' ) == 'on' ) {
					return false;
				}
				if ( ! wp_is_mobile() && XLWCTY_Core()->data->get_meta( $this->get_slug() . '_hide_desktop_' . $index, 'raw' ) == 'on' ) {
					return false;
				}
				$is_enabled = apply_filters( 'xlwcty_is_component_enabled', true, $this->get_slug(), $index );
				if ( $is_enabled === false ) {
					return false;
				}

				return true;
			}
		}

		return false;
	}

	public function has_multiple_fields() {
		if ( isset( $this->_component['fields']['is_multiple'] ) && $this->_component['fields']['is_multiple'] === true && isset( $this->_component['fields']['count'] ) && $this->_component['fields']['count'] > 0 ) {
			return true;
		}

		return false;
	}

	public function prepare_out_put_data() {
		$get_values = XLWCTY_Core()->data->get_meta( $this->get_slug() );
		if ( is_array( $get_values ) && count( $get_values ) > 0 ) {
			if ( $this->has_multiple_fields() ) {
				foreach ( $get_values as $key => $values ) {
					$this->data->{$key} = new stdClass();
					foreach ( $values as $key_meta => $val ) {
						$this->data->{$key}->{$key_meta} = maybe_unserialize( $val );
					}
					do_action( 'xlwcty_after_component_data_setup' . $this->get_slug(), $this->get_slug(), $key );
				}
			} else {
				foreach ( $get_values as $key => $val ) {
					$this->data->{$key} = maybe_unserialize( $val );
				}
				do_action( 'xlwcty_after_component_data_setup' . $this->get_slug(), $this->get_slug() );
			}
		}
	}

	public function get_opengraph( $url ) {
		if ( ! $url ) {
			return false;
		}
		require_once __DIR__ . '/xlwcty-opengraph.php';
		$data  = array();
		$graph = XLWCTY_OpenGraph::fetch( $url );
		if ( is_object( $graph ) ) {
			foreach ( $graph as $key => $value ) {
				$data[ (string) $key ] = $value;
			}
		}

		return $data;
	}

	public function get_highest_order_product() {
		$max_product = array();
		$maxs        = array();
		$product     = array();
		$order_data  = $order = XLWCTY_Core()->data->get_order();
		if ( $order instanceof WC_Order ) {
			foreach ( $order_data->get_items() as $key => $val ) {
				$pro                 = XLWCTY_Compatibility::get_product_from_item( $order_data, $val );
				$pid                 = ( $pro ) ? $pro->get_id() : 0;
				$product[ $pid ]     = $pro;
				$max_product[ $pid ] = XLWCTY_Compatibility::get_item_subtotal( $order_data, $val );
			}
			if ( is_array( $max_product ) && count( $max_product ) > 0 ) {
				$maxs = array_keys( $max_product, max( $max_product ) );
			}
		}

		return $maxs;
	}

	public function get_wp_date_format() {
		return get_option( 'date_format', 'Y-m-d' );
	}

	public function render_view( $slug ) {
		if ( $slug !== $this->get_slug() && $this->has_multiple_fields() ) {
			$this->current_index = str_replace( $this->get_slug() . '_', '', $slug );
		}

		$this->get_view();
	}

	public function get_data( $key = 'order' ) {
		if ( '' !== $key ) {
			return self::$order_meta_data[ $key ];
		}

		return self::$order_meta_data;
	}

	public function get_defaults() {

		if ( false === $this->_component ) {
			return array();
		}

		return ( isset( $this->_component['default'] ) ? $this->_component['default'] : array() );
	}

	public function set_component( $component ) {
		$this->_component = $component;
	}

	public function get_title() {
		return $this->get_component_property( 'title' );
	}

	public function get_component_property( $property ) {
		$component = $this->get_component();

		return isset( $component[ $property ] ) ? $component[ $property ] : '';
	}

	public function get_component() {
		return $this->_component;
	}

	public final function get_thumbnail_size() {
		return XLWCTY_Core()->data->get_option( 'shop_thumbnail_size' );
	}

	public final function wc_placeholder_img( $size = 0 ) {
		$width_str = '';
		/**
		 * Trying to get placeholder image width same as the width found in the chosen option of size
		 */
		if ( 0 !== $size ) {
			$dimensions = wc_get_image_size( $size );
			$width_str  = 'width="' . $dimensions['width'] . '"';
		}

		return '<img src="' . wc_placeholder_img_src() . '" alt="' . esc_attr__( 'Placeholder', 'woocommerce' ) . '" class="woocommerce-placeholder wp-post-image" ' . $width_str . '/>';

	}

}
