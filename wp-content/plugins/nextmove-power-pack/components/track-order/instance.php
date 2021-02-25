<?php

/**
 * Class Xlwcty_Track_Order
 * This is the main file of this component
 * Component in edit components screen is added from here
 */
class Xlwcty_Track_Order extends XLWCTY_Component {

	private static $instance = null;
	public $viewpath = '';
	private $from = '';
	private $to = '';

	public function __construct( $order = false ) {
		parent::__construct();
		$this->viewpath = __DIR__ . '/views/view.php';

		add_action( 'xlatc_after_components_loaded', array( $this, 'setup_fields' ) );
		add_action( 'xlwcty_after_component_data_setup_xlwcty_track_order', array( $this, 'setup_style' ), 10, 1 );

		add_action( 'woocommerce_order_status_changed', array( $this, 'xlwcty_flag_wc_order_status_changed' ), 10, 4 );
		add_filter( 'woocommerce_new_order_note_data', array( $this, 'xlwcty_add_meta_for_wc_order_status_changed' ), 10, 2 );

		add_filter( 'xlwcty_show_order_created_in_track_order', array( $this, 'xlwcty_show_order_created_in_track_order' ), 10, 2 );
		add_filter( 'xlwcty_fetch_all_order_notes', array( $this, 'xlwcty_fetch_custom_order_notes' ), 10, 2 );

	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Function to setup fields on thankyou page
	 * Overrides the parent function
	 */
	public function setup_fields() {
		$this->fields = array(
			'heading'            => $this->get_slug() . '_heading',
			'heading_font_size'  => $this->get_slug() . '_heading_font_size',
			'heading_color'      => $this->get_slug() . '_heading_color',
			'heading_alignment'  => $this->get_slug() . '_heading_alignment',
			'order_creation'     => $this->get_slug() . '_order_creation',
			'customer_note'      => $this->get_slug() . '_customer_note',
			'order_status'       => $this->get_slug() . '_order_status',
			'order_content'      => $this->get_slug() . '_order_content',
			'custom_content'     => $this->get_slug() . '_custom_content',
			'hide_status'        => $this->get_slug() . '_hide_status',
			'button_text'        => $this->get_slug() . '_button_text',
			'border_style'       => $this->get_slug() . '_border_style',
			'border_width'       => $this->get_slug() . '_border_width',
			'border_color'       => $this->get_slug() . '_border_color',
			'component_bg_color' => $this->get_slug() . '_component_bg_color',
		);
	}

	/**
	 * @param $slug
	 * Function to add styling of the component on thanyou page
	 */
	public function setup_style( $slug ) {
		if ( $this->is_enable() ) {
			$style = array();

			if ( '' != $this->data->heading_font_size ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component .xlwcty_title']['font-size']   = $this->data->heading_font_size . 'px';
				$style['.xlwcty_wrap .xlwcty-track-order-component .xlwcty_title']['line-height'] = ( $this->data->heading_font_size + 4 ) . 'px';
			}
			if ( '' != $this->data->heading_color ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component .xlwcty_title']['color'] = $this->data->heading_color;
			}
			if ( '' != $this->data->heading_alignment ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component .xlwcty_title']['text-align'] = $this->data->heading_alignment;
			}
			if ( '' != $this->data->border_style ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component']['border-style'] = $this->data->border_style;
			}
			if ( (int) $this->data->border_width >= 0 ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component']['border-width'] = (int) $this->data->border_width . 'px';
			}
			if ( '' != $this->data->border_color ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component']['border-color'] = $this->data->border_color;
			}
			if ( '' != $this->data->component_bg_color ) {
				$style['.xlwcty_wrap .xlwcty-track-order-component']['background-color'] = $this->data->component_bg_color;
			}

			parent::push_css( $slug, $style );
		}
	}

	/**
	 * @param $order_id
	 * Function to display track your order component on the thankyou page
	 */
	public function xlwcty_show_track_order_details_on_thankyoupage( $order_id ) {

		if ( 'yes' != $this->data->order_creation && 'yes' != $this->data->customer_note && 'yes' != $this->data->order_status ) {
			return;
		}

		$all_notes = array();
		$type      = apply_filters( 'xlwcty_fetch_all_order_notes', '', $order_id );

		/** Get all notes for the order */
		if ( false !== $type ) {
			$all_notes = wc_get_order_notes( array(
				'limit'         => '',
				'order_id'      => $order_id,
				'order__not_in' => '',
				'orderby'       => 'date_created',
				'order'         => 'ASC',
				'type'          => $type,
			) );
		}

		$time_format = get_option( 'time_format', 'g:i a' );

		/** Display Order Creation event */
		$show_order_created = apply_filters( 'xlwcty_show_order_created_in_track_order', true, $order_id );
		ob_start();
		if ( true === $show_order_created ) {
			$order      = wc_get_order( $order_id );
			$date_obj   = XLWCTY_Compatibility::get_order_date( $order );
			$order_date = XLWCTY_Compatibility::get_formatted_date( $date_obj );
			$order_time = XLWCTY_Compatibility::get_formatted_date( $date_obj, $time_format );

			echo '<tr>
                          <td>' . $order_date . ' ' . $order_time . '</td>
                          <td>' . __( 'Order', 'nextmove-power-pack' ) . '</td>
                          <td>' . __( 'Order Placed', 'nextmove-power-pack' ) . '</td>
                      </tr>';
		}
		$timeline_rows = ob_get_clean();

		$order_statuses = XLWCTY_PP_Common::get_wc_order_statuses();

		/** Order timeline rows */
		ob_start();
		if ( is_array( $all_notes ) && count( $all_notes ) > 0 ) {
			foreach ( $all_notes as $note ) {
				if ( 1 == $note->customer_note ) {
					/** Display Customer note event */
					$note_date = XLWCTY_Compatibility::get_formatted_date( $note->date_created );
					$note_time = XLWCTY_Compatibility::get_formatted_date( $note->date_created, $time_format );

					echo '<tr>
                                  <td>' . $note_date . ' ' . $note_time . '</td>
                                  <td>' . __( 'Note', 'nextmove-power-pack' ) . '</td>
                                  <td>' . $note->content . '</td>
                              </tr>';

				} else {
					/** Display Order Status Changed event */
					$to = get_comment_meta( $note->id, 'comment_xlwcty_pp_status_to', true );
					if ( empty( $this->data->hide_status ) ) {
						$this->data->hide_status = array();
					}
					if ( ! empty( $to ) && ! in_array( $to, $this->data->hide_status ) ) {

						$note_date = XLWCTY_Compatibility::get_formatted_date( $note->date_created );
						$note_time = XLWCTY_Compatibility::get_formatted_date( $note->date_created, $time_format );

						echo '<tr>
                                      <td>' . $note_date . ' ' . $note_time . '</td>
                                      <td>' . __( 'Status Changed', 'nextmove-power-pack' ) . '</td>';
						if ( 'custom' == $this->data->order_content ) {
							$content = str_replace( '{{current_status}}', $order_statuses[ $to ], $this->data->custom_content );
							echo '<td>' . $content . '</td>';
						} else {
							echo '<td>' . $note->content . '</td>';
						}
						echo '</tr>';
					}
				}
			}
		}
		$timeline_rows .= ob_get_clean();

		/** Return if no rows to display */
		if ( empty( $timeline_rows ) ) {
			return;
		}

		/** Show track order component */
		echo '<div class="xlwcty_Box xlwcty-track-order-component">';
		/** Display Component heading */
		if ( ! empty( $this->data->heading ) ) {
			echo '<div class="xlwcty_title">' . XLWCTY_Common::maype_parse_merge_tags( $this->data->heading, $this ) . '</div>';
		}
		?>
        <table>
            <tr>
                <th><?php echo __( 'Date & Time', 'nextmove-power-pack' ); ?></th>
                <th><?php echo __( 'Event', 'nextmove-power-pack' ); ?></th>
                <th><?php echo __( 'Event Details', 'nextmove-power-pack' ); ?></th>
            </tr>
			<?php echo $timeline_rows; ?>
        </table>
		<?php
		echo '</div>';
	}

	/**
	 * @param $id
	 * @param $from
	 * @param $to
	 * @param $obj
	 *
	 * Function to store order status changed from and to event temporarily
	 * Hooked over woocommerce order status changed event
	 */
	public function xlwcty_flag_wc_order_status_changed( $id, $from, $to, $obj ) {
		$order_statuses = XLWCTY_PP_Common::get_wc_order_statuses();
		if ( array_key_exists( $from, $order_statuses ) && array_key_exists( $to, $order_statuses ) ) {
			$this->from = $from;
			$this->to   = $to;
		}
	}

	/**
	 * @param $note_data
	 * @param $data
	 *
	 * @return mixed
	 * Function to save status changed from and to in the note meta
	 * Hooked over the woocommerce inserting note in the DB
	 */
	public function xlwcty_add_meta_for_wc_order_status_changed( $note_data, $data ) {
		if ( ! empty( $this->to ) ) {
			$note_data['comment_meta'] = array(
				'comment_xlwcty_pp_status_from' => $this->from,
				'comment_xlwcty_pp_status_to'   => $this->to,
			);
			$this->from                = '';
			$this->to                  = '';
		}

		return $note_data;
	}

	/**
	 * @param $flag
	 * @param $order_id
	 *
	 * @return bool
	 * Function to display order creation event on thankyou page
	 */
	public function xlwcty_show_order_created_in_track_order( $flag, $order_id ) {
		if ( 'yes' != $this->data->order_creation ) {
			return false;
		}

		return $flag;
	}

	/**
	 * @param $type
	 * @param $order_id
	 *
	 * @return bool|string
	 * Function to display customer note / order changed event on thankyou page
	 */
	function xlwcty_fetch_custom_order_notes( $type, $order_id ) {
		if ( 'yes' == $this->data->customer_note && 'yes' == $this->data->order_status ) {
			return $type;
		}

		if ( 'yes' != $this->data->customer_note && 'yes' != $this->data->order_status ) {
			return false;
		}

		if ( 'yes' == $this->data->customer_note ) {
			return 'customer';
		}

		if ( 'yes' == $this->data->order_status ) {
			return 'internal';
		}

		return $type;
	}

}

return Xlwcty_Track_Order::get_instance();
