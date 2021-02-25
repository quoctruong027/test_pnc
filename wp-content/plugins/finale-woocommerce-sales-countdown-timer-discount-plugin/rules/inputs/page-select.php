<?php

class WCCT_Input_Page_Select extends WCCT_Input_Text {

	public function __construct() {
		// vars
		$this->type = 'Page_Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => 'ajax_chosen_select_products',
		);
	}

	public function render( $field, $value = null ) {

		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}

		$args = array(
			'name'             => $field['name'],
			'id'               => $field['id'],
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => ' ',
			'class'            => 'chosen_select',
			'echo'             => false,
			'selected'         => absint( $value ),
		);

		$defaults = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => 0,
			'echo'                  => 1,
			'name'                  => 'page_id',
			'id'                    => '',
			'class'                 => '',
			'show_option_none'      => '',
			'show_option_no_change' => '',
			'option_none_value'     => '',
			'value_field'           => 'ID',
		);

		$r = wp_parse_args( $args, $defaults );

		$pages  = get_pages( $r );
		$output = '';
		// Back-compat with old system where both id and name were based on $name argument
		if ( empty( $r['id'] ) ) {
			$r['id'] = $r['name'];
		}

		if ( ! empty( $pages ) ) {
			$class = '';
			if ( ! empty( $r['class'] ) ) {
				$class = " class='" . esc_attr( $r['class'] ) . "'";
			}
			$placeholder = ( isset( $field['placeholder'] ) ? $field['placeholder'] : __( 'Search...', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ) );

			$output = "<select multiple='multiple' name='" . esc_attr( $r['name'] ) . "[]'" . $class . " id='" . esc_attr( $r['id'] ) . "' data-placeholder='" . $placeholder . "'>\n";
			if ( $r['show_option_no_change'] ) {
				$output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
			}
			if ( $r['show_option_none'] ) {
				$output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
			}

			foreach ( $pages as $page ) {
				$selected = '';

				if ( $value && in_array( $page->ID, $value ) ) {
					$selected = "selected='selected'";
				}
				$output .= "\t<option " . $selected . ' value="' . esc_attr( $page->ID ) . '">' . $page->post_title . "</option>\n";

			}

			//  $output .= walk_page_dropdown_tree( $pages, $r['depth'], $r );
			$output .= "</select>\n";

			echo $output;

		}

	}
}
