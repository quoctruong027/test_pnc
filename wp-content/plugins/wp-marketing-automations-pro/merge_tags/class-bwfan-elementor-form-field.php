<?php

class BWFAN_Elementor_Form_Field extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'elementor_form_field';
		$this->tag_description = __( 'Form Field', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_elementor_form_field', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		?>
        <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Field', 'autonami-automations-pro' ); ?></label>
        <select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select bwfan_elementor_form_fields" name="field"></select>
		<?php
		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$entry       = BWFAN_Merge_Tag_Loader::get_data( 'entry' );
		$field_value = '';
		if ( isset( $attr['field'] ) && isset( $entry[ $attr['field'] ] ) ) {
			$field_value = is_array( $entry[ $attr['field'] ] ) ? implode( ' ', $entry[ $attr['field'] ] ) : $entry[ $attr['field'] ];
		}

		return $this->parse_shortcode_output( $field_value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'Test';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_elementorpro_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'elementor-forms', 'BWFAN_Elementor_Form_Field' );
}
