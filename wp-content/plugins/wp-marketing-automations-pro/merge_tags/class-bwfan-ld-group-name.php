<?php

class BWFAN_LD_Group_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'ld_group_name';
		$this->tag_description = __( 'Group Name', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_ld_group_name', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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

		$group_id = BWFAN_Merge_Tag_Loader::get_data( 'group_id' );
		$group_name = ! empty( $group_id ) ? esc_html__( get_the_title( $group_id ), 'autonami-automations-pro' ) : '';

		return $this->parse_shortcode_output( $group_name, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return 'Dummy LearnDash Group Name';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_learndash_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'learndash_group', 'BWFAN_LD_Group_Name' );
}
